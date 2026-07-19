<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('forms.title') }}</h2>
        <p class="page-sub">{{ t('forms.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('forms.newForm') }}</button>
    </div>

    <!-- Editor -->
    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('forms.editForm') : t('forms.createForm') }}</h3>
        <button type="button" @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>

      <SplitEditor
        :preview-mode="tab === 'qr' ? 'qr' : 'content'"
        :class="{ 'split-editor--single': tab === 'settings' || tab === 'responses' }"
      >
        <template #form>
          <form novalidate @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: tab === 'content' }" @click="tab = 'content'">{{ t('forms.tabs.content') }}</button>
              <button type="button" :class="{ active: tab === 'appearance' }" @click="tab = 'appearance'">{{ t('forms.tabs.appearance') }}</button>
              <button type="button" :class="{ active: tab === 'qr' }" @click="tab = 'qr'">{{ t('forms.tabs.qrDesign') }}</button>
              <button type="button" :class="{ active: tab === 'settings' }" @click="tab = 'settings'">{{ t('forms.tabs.settings') }}</button>
              <button v-if="editId" type="button" :class="{ active: tab === 'responses' }" @click="openResponses">{{ t('forms.tabs.responses') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="tab === 'content'" class="tab-panel">
                <DomainSelect v-model="form.custom_domain_id" />
                <div class="form-group">
                  <label>{{ t('forms.formUrlSlug') }}</label>
                  <div class="slug-input">
                    <span>{{ formHost }}/form/</span>
                    <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="customer-feedback" />
                  </div>
                </div>
                <div class="form-group">
                  <label>{{ t('forms.formTitle') }}</label>
                  <input v-model="form.title" required class="input-field" />
                </div>
                <div class="form-group">
                  <label>{{ t('common.description') }}</label>
                  <textarea v-model="form.description" class="input-field" rows="2"></textarea>
                </div>

                <div class="builder-layout">
                  <aside class="field-palette">
                    <div class="palette-title">{{ t('forms.addField') }}</div>
                    <button
                      v-for="ft in fieldTypes"
                      :key="ft.id"
                      type="button"
                      class="palette-btn"
                      @click="addField(ft.id)"
                    >
                      <span class="palette-icon">{{ ft.icon }}</span>
                      <span>{{ t(ft.labelKey) }}</span>
                    </button>
                  </aside>

                  <div class="fields-list">
                    <FormFieldEditor
                      v-for="(field, i) in form.fields"
                      :key="field.id"
                      :field="field"
                      :index="i"
                      :total="form.fields.length"
                      :active="activeFieldId === field.id"
                      @select="activeFieldId = field.id"
                      @move-up="moveField(i, -1)"
                      @move-down="moveField(i, 1)"
                      @duplicate="duplicateField(i)"
                      @remove="removeField(i)"
                    />
                    <button type="button" class="add-field-btn" @click="addField('short_text')">+ {{ t('forms.addQuestion') }}</button>
                  </div>
                </div>
              </div>

              <div v-show="tab === 'appearance'" class="tab-panel">
                <ImageAssetField v-model="form.header_image_path" :label="t('forms.headerImage')" folder="forms" ai-context="form-header" ai-placeholder="abstract banner header" />
                <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="form-logo" />
                <div class="form-row">
                  <div class="form-group">
                    <label>{{ t('common.themeColor') }}</label>
                    <input v-model="form.theme_color" type="color" class="color-input" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('forms.backgroundColor') }}</label>
                    <input v-model="form.background_color" type="color" class="color-input" />
                  </div>
                </div>
                <ImageAssetField v-model="form.background_image_path" :label="t('common.background')" folder="backgrounds" ai-context="form-background" />
              </div>

              <div v-show="tab === 'qr'" class="tab-panel">
                <QrStyleFields
                  v-model:qr-shape="form.qr_shape"
                  v-model:dot-style="form.dot_style"
                  v-model:corner-style="form.corner_style"
                  v-model:frame-style="form.frame_style"
                />
              </div>

              <div v-show="tab === 'settings'" class="tab-panel">
                <div class="settings-section">
                  <h4>{{ t('forms.responseSettings') }}</h4>
                  <label class="toggle-row">
                    <input v-model="form.settings.collect_email" type="checkbox" />
                    {{ t('forms.collectEmail') }}
                  </label>
                  <label class="toggle-row">
                    <input v-model="form.settings.show_progress_bar" type="checkbox" />
                    {{ t('forms.showProgressBar') }}
                  </label>
                  <label class="toggle-row">
                    <input v-model="form.settings.show_submit_another" type="checkbox" />
                    {{ t('forms.showSubmitAnother') }}
                  </label>
                  <label class="toggle-row">
                    <input v-model="form.settings.shuffle_questions" type="checkbox" />
                    {{ t('forms.shuffleQuestions') }}
                  </label>
                </div>

                <div class="form-group">
                  <label>{{ t('forms.confirmationMessage') }}</label>
                  <textarea v-model="form.settings.confirmation_message" class="input-field" rows="2" :placeholder="t('forms.defaultConfirmation')" />
                </div>
                <div class="form-group">
                  <label>{{ t('forms.redirectUrl') }}</label>
                  <input v-model="form.settings.redirect_url" type="text" class="input-field" placeholder="/app/forms" />
                </div>

                <div class="settings-section">
                  <h4>{{ t('forms.limits') }}</h4>
                  <div class="form-row">
                    <div class="form-group">
                      <label>{{ t('forms.closesAt') }}</label>
                      <input v-model="form.closes_at" type="datetime-local" class="input-field" />
                    </div>
                    <div class="form-group">
                      <label>{{ t('forms.maxSubmissions') }}</label>
                      <input v-model.number="form.max_submissions" type="number" min="0" class="input-field" />
                      <span class="hint">{{ t('forms.zeroUnlimited') }}</span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>{{ t('forms.maxPerRespondent') }}</label>
                    <input v-model.number="form.max_submissions_per_respondent" type="number" min="0" max="100" class="input-field" />
                  </div>
                </div>
              </div>

              <div v-show="tab === 'responses'" class="tab-panel tab-panel--flush">
                <FormResponses
                  :submissions="responses"
                  :summary="responseSummary"
                  :fields="inputFields"
                  :total="responseTotal"
                  :loading="responsesLoading"
                  @export="exportResponses"
                  @delete="deleteResponse"
                />
              </div>

              <p v-if="error" class="error-text">{{ error }}</p>
            </div>

            <div v-if="tab !== 'responses'" class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <QrPreview
            v-if="tab === 'qr' && form.slug"
            minimal
            :content="previewFormUrl"
            :name="form.title"
            :foreground="form.theme_color"
            :logo-url="form.logo_path"
            :background-image="form.background_image_path"
            :size="220"
            :qr-shape="form.qr_shape"
            :dot-style="form.dot_style"
            :corner-style="form.corner_style"
            :frame-style="form.frame_style"
          />
          <FormPreview
            v-else-if="tab === 'content' || tab === 'appearance'"
            :title="form.title"
            :description="form.description"
            :fields="form.fields"
            :settings="form.settings"
            :theme-color="form.theme_color"
            :background-color="form.background_color"
            :header-image="form.header_image_path"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
          />
        </template>
      </SplitEditor>
    </div>

    <!-- List -->
    <template v-if="!editing">
      <div v-if="successMessage" class="success-banner" role="status">
        {{ successMessage }}
      </div>
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!forms.length" class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>{{ t('forms.emptyTitle') }}</h3>
        <p>{{ t('forms.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('forms.emptyCta') }}</button>
      </div>
      <div v-else class="forms-list-wrap">
        <div class="forms-grid">
        <article v-for="item in forms" :key="item.id" class="form-card" :class="{ draft: !item.is_active }">
          <div class="form-card__stack">
            <div v-if="!item.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
            <div class="form-card__head">
              <h3 class="form-card__title">{{ item.title || t('forms.untitledForm') }}</h3>
              <p class="form-card__slug">/form/{{ item.slug }}</p>
            </div>
            <div class="form-card__preview item-preview-scroll">
              <FormPreview
                compact
              :title="item.title"
              :description="item.description"
              :fields="item.fields || []"
              :settings="item.settings"
              :theme-color="item.theme_color"
              :background-color="item.background_color"
              :header-image="item.header_image_path"
              :logo="item.logo_path"
            />
          </div>
          </div>
          <div class="form-card__footer">
            <div class="form-card__stats">
              <PublishToggle
                :model-value="!!item.is_active"
                :loading="togglingId === item.id"
                :active-label="t('publish.published')"
                :inactive-label="t('publish.draft')"
                @update:model-value="togglePublish(item)"
              />
              <span class="view-stat">
                <span class="view-stat__num">{{ item.submission_count || 0 }}</span>
                <span class="view-stat__label">{{ t('forms.responses') }}</span>
              </span>
              <span class="view-stat">
                <span class="view-stat__num">{{ item.view_count || 0 }}</span>
                <span class="view-stat__label">{{ t('common.views') }}</span>
              </span>
            </div>
            <div class="form-card__actions">
              <CopyButton :text="item.form_url" :label="t('common.copy')" />
              <button @click="openEdit(item)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="openResponsesFor(item)" class="action-btn">{{ t('forms.responses') }}</button>
              <button @click="showAnalytics(item)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteForm(item)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </article>
        </div>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsForm" class="drawer-overlay" @click.self="analyticsForm = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsForm.title }) }}</h3>
          <button type="button" @click="analyticsForm = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="forms" :id="analyticsForm.id" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useDomainsStore } from '../../stores/domains'
import SplitEditor from '../../components/ui/SplitEditor.vue'
import FormPreview from '../../components/previews/FormPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import FormFieldEditor from '../../components/forms/FormFieldEditor.vue'
import FormResponses from '../../components/forms/FormResponses.vue'
import { useDialog } from '../../composables/useDialog'
import { useApiError } from '../../composables/useApiError'
import { FIELD_TYPES, defaultForm, createField, isInputField } from '../../utils/formFieldTypes'
import { resolveStorageUrl } from '../../utils/storageUrl'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()
const { firstError } = useApiError()

const fieldTypes = FIELD_TYPES

const forms = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const successMessage = ref('')
const loadError = ref('')
const tab = ref('content')
const activeFieldId = ref(null)
const analyticsForm = ref(null)
const togglingId = ref(null)

const responses = ref([])
const responseSummary = ref([])
const responseTotal = ref(0)
const responsesLoading = ref(false)

const form = ref(defaultForm())

const formHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const previewFormUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/form/${form.value.slug}` : `${base}/form/...`
})

const inputFields = computed(() => (form.value.fields || []).filter(f => isInputField(f.type)))

function toDatetimeLocal(value) {
  if (!value) return ''
  try {
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return value
    const pad = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
  } catch {
    return value
  }
}

function addField(type) {
  const field = createField(type)
  form.value.fields.push(field)
  activeFieldId.value = field.id
}

function moveField(index, dir) {
  const target = index + dir
  if (target < 0 || target >= form.value.fields.length) return
  const fields = [...form.value.fields]
  const [item] = fields.splice(index, 1)
  fields.splice(target, 0, item)
  form.value.fields = fields
}

function duplicateField(index) {
  const copy = JSON.parse(JSON.stringify(form.value.fields[index]))
  copy.id = createField(copy.type).id
  form.value.fields.splice(index + 1, 0, copy)
}

function removeField(index) {
  form.value.fields.splice(index, 1)
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  tab.value = 'content'
  editing.value = true
  error.value = ''
  successMessage.value = ''
}

function openEdit(item) {
  editId.value = item.id
  form.value = {
    ...item,
    fields: JSON.parse(JSON.stringify(item.fields || [])),
    settings: { ...defaultForm().settings, ...(item.settings || {}) },
    theme_color: item.theme_color || '#673ab7',
    background_color: item.background_color || '#f3f0ff',
    qr_shape: item.qr_shape || 'square',
    dot_style: item.dot_style || 'square',
    corner_style: item.corner_style || 'sharp',
    frame_style: item.frame_style || 'none',
    closes_at: toDatetimeLocal(item.closes_at),
  }
  tab.value = 'content'
  editing.value = true
  error.value = ''
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

async function load() {
  loadError.value = ''
  try {
    const { data } = await api.get('/forms')
    forms.value = Array.isArray(data) ? data : []
  } catch (e) {
    forms.value = []
    loadError.value = e.response?.data?.message || t('forms.loadFailed')
  }
}

function slugify(text) {
  return String(text || '')
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 50)
}

function preparePayload() {
  const payload = JSON.parse(JSON.stringify(form.value))

  if (!payload.slug && payload.title) {
    payload.slug = slugify(payload.title)
  }

  if (!payload.slug) {
    throw new Error(t('forms.slugRequired'))
  }

  if (!payload.settings || typeof payload.settings !== 'object') {
    payload.settings = defaultForm().settings
  }

  if (!payload.settings.redirect_url) {
    payload.settings.redirect_url = '/app/forms'
  } else {
    payload.settings.redirect_url = payload.settings.redirect_url.trim() || '/app/forms'
  }

  if (!payload.settings.confirmation_message) {
    payload.settings.confirmation_message = null
  }

  payload.closes_at = payload.closes_at || null
  payload.description = payload.description || null
  payload.header_image_path = resolveStorageUrl(payload.header_image_path) || null
  payload.logo_path = resolveStorageUrl(payload.logo_path) || null
  payload.background_image_path = resolveStorageUrl(payload.background_image_path) || null
  payload.background_color = payload.background_color || null
  payload.custom_domain_id = payload.custom_domain_id || null

  delete payload.id
  delete payload.user_id
  delete payload.is_active
  delete payload.form_url
  delete payload.domain_label
  delete payload.view_count
  delete payload.submission_count
  delete payload.analytics_events
  delete payload.created_at
  delete payload.updated_at

  return payload
}

async function save() {
  saving.value = true
  error.value = ''
  successMessage.value = ''
  try {
    const payload = preparePayload()
    const isCreate = !editId.value
    if (editId.value) {
      await api.put(`/forms/${editId.value}`, payload)
    } else {
      await api.post('/forms', { ...payload, is_active: false })
    }
    await load()
    closeEditor()
    successMessage.value = isCreate ? t('forms.createSuccess') : t('forms.updateSuccess')
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => { successMessage.value = '' }, 5000)
  } catch (e) {
    error.value = e.message === t('forms.slugRequired')
      ? t('forms.slugRequired')
      : firstError(e, 'errors.failedToSave')
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    saving.value = false
  }
}

async function togglePublish(item) {
  togglingId.value = item.id
  try {
    const { data } = await api.patch(`/forms/${item.id}/publish`)
    const idx = forms.value.findIndex(f => f.id === item.id)
    if (idx !== -1) forms.value[idx] = data
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('forms.updateFailed'), variant: 'danger' })
  } finally {
    togglingId.value = null
  }
}

async function deleteForm(item) {
  const ok = await dialog.confirm({
    title: t('forms.deleteTitle'),
    message: t('forms.deleteMessage', { name: item.title }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/forms/${item.id}`)
  await load()
}

function showAnalytics(item) { analyticsForm.value = item }

async function loadResponses(formId) {
  responsesLoading.value = true
  try {
    const { data } = await api.get(`/forms/${formId}/submissions`)
    responses.value = data.submissions?.data || []
    responseSummary.value = data.summary || []
    responseTotal.value = data.submissions?.total || responses.value.length
  } catch {
    responses.value = []
    responseSummary.value = []
    responseTotal.value = 0
  } finally {
    responsesLoading.value = false
  }
}

function openResponses() {
  tab.value = 'responses'
  if (editId.value) loadResponses(editId.value)
}

function openResponsesFor(item) {
  openEdit(item)
  openResponses()
}

async function exportResponses() {
  if (!editId.value) return
  try {
    const { data } = await api.get(`/forms/${editId.value}/submissions/export`, { responseType: 'blob' })
    const url = URL.createObjectURL(data)
    const a = document.createElement('a')
    a.href = url
    a.download = `form-${form.value.slug}-responses.csv`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('forms.exportFailed'), variant: 'danger' })
  }
}

async function deleteResponse(sub) {
  const ok = await dialog.confirm({
    title: t('forms.deleteResponseTitle'),
    message: t('forms.deleteResponseMessage'),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok || !editId.value) return
  await api.delete(`/forms/${editId.value}/submissions/${sub.id}`)
  await loadResponses(editId.value)
  await load()
}

onMounted(async () => {
  domains.fetch()
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; box-shadow: var(--shadow-sm); }
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.editor-panel__header h3 { font-weight: 700; font-size: 1.125rem; }
:deep(.split-editor--single) { grid-template-columns: 1fr !important; }
:deep(.split-editor--single .split-editor__preview) { display: none; }
.tab-panel--flush { gap: 0; }
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.builder-layout { display: grid; grid-template-columns: 180px 1fr; gap: 1rem; }
@media (max-width: 768px) { .builder-layout { grid-template-columns: 1fr; } }
.field-palette { display: flex; flex-direction: column; gap: 0.25rem; }
.palette-title { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 0.25rem; }
.palette-btn {
  display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.5rem;
  border: 1px solid var(--border); border-radius: 0.375rem; background: var(--surface);
  font-size: 0.75rem; cursor: pointer; text-align: start;
}
.palette-btn:hover { border-color: var(--brand); color: var(--brand); }
.palette-icon { font-size: 0.875rem; width: 1.25rem; text-align: center; }
.fields-list { display: flex; flex-direction: column; gap: 0.75rem; }
.add-field-btn {
  padding: 0.75rem; border: 2px dashed var(--border); border-radius: 0.75rem;
  background: transparent; color: var(--text-secondary); cursor: pointer; font-weight: 600;
}
.add-field-btn:hover { border-color: var(--brand); color: var(--brand); }
.qr-section, .settings-section {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;
}
.section-title { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; }
.settings-section h4 { font-weight: 700; margin-bottom: 0.75rem; }
.toggle-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; margin-bottom: 0.5rem; cursor: pointer; }
.hint { font-size: 0.6875rem; color: var(--text-muted); }
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.editor-feedback { margin-bottom: 1rem; }
.success-banner {
  margin-bottom: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  background: color-mix(in srgb, #10b981 12%, var(--surface));
  border: 1px solid color-mix(in srgb, #10b981 35%, var(--border));
  color: #047857;
  font-size: 0.875rem;
  font-weight: 600;
}
.forms-list-wrap {
  overflow-x: auto;
  overflow-y: visible;
  padding-bottom: 0.25rem;
  -webkit-overflow-scrolling: touch;
}
.forms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.25rem;
  align-items: stretch;
  min-width: min(100%, 300px);
}
.form-card {
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 420px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.2s, border-color 0.2s;
}
.form-card:hover {
  box-shadow: var(--shadow-md);
  border-color: color-mix(in srgb, var(--brand) 25%, var(--border));
}
.form-card.draft { opacity: 0.92; }
.form-card__stack {
  position: relative;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
.form-card__head {
  flex-shrink: 0;
  padding: 0.875rem 1rem 0.5rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-subtle);
}
.form-card__title {
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.form-card__slug {
  font-family: ui-monospace, monospace;
  font-size: 0.6875rem;
  color: var(--brand);
  margin-top: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.form-card__preview {
  flex: 1 1 auto;
  min-height: 200px;
  max-height: 260px;
  overflow-y: auto;
  overflow-x: hidden;
  background: var(--bg-page);
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}
.form-card__preview::-webkit-scrollbar { width: 6px; }
.form-card__preview::-webkit-scrollbar-thumb {
  background: var(--border);
  border-radius: 999px;
}
.draft-ribbon {
  position: absolute; top: 0.5rem; right: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.form-card__footer {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}
.form-card__stats {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}
.form-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem;
  border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; }
.drawer-overlay { position: fixed; inset: 0; background: rgba(26,19,51,0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
</style>
