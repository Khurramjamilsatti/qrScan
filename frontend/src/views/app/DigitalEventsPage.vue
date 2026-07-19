<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalEvents.title') }}</h2>
        <p class="page-sub">{{ t('digitalEvents.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('digitalEvents.newEvent') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalEvents.editEvent') : t('digitalEvents.createEvent') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="previewMode" :preview-column-width="500">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('digitalEvents.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'sections' }" @click="editorTab = 'sections'">{{ t('digitalEvents.tabs.sections') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('digitalEvents.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('digitalEvents.tabs.qrDesign') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="editorTab === 'content'" class="tab-panel">
                <div class="editor-card">
                  <div class="editor-card__title">{{ t('digitalEvents.eventDetails') }}</div>
                  <div class="form-group">
                    <label>{{ t('digitalEvents.eventTitle') }}</label>
                    <input v-model="form.title" required class="input-field input-field--lg" :placeholder="t('digitalEvents.eventTitlePlaceholder')" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('digitalEvents.subtitleLabel') }}</label>
                    <input v-model="form.subtitle" class="input-field" :placeholder="t('digitalEvents.subtitlePlaceholder')" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('digitalEvents.hosts') }}</label>
                    <input v-model="form.hosts" class="input-field" :placeholder="t('digitalEvents.hostsPlaceholder')" />
                  </div>
                </div>

                <div class="editor-card">
                  <div class="editor-card__title">{{ t('digitalEvents.whenWhere') }}</div>
                  <div class="form-row">
                    <div class="form-group">
                      <label>{{ t('digitalEvents.eventDate') }}</label>
                      <input v-model="form.event_date" type="datetime-local" class="input-field" />
                    </div>
                    <div class="form-group">
                      <label>{{ t('digitalEvents.eventEndDate') }}</label>
                      <input v-model="form.event_end_date" type="datetime-local" class="input-field" />
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label>{{ t('digitalEvents.venueName') }}</label>
                      <input v-model="form.venue_name" class="input-field" :placeholder="t('digitalEvents.venuePlaceholder')" />
                    </div>
                    <div class="form-group">
                      <label>{{ t('digitalEvents.dressCode') }}</label>
                      <input v-model="form.dress_code" class="input-field" :placeholder="t('digitalEvents.dressCodePlaceholder')" />
                    </div>
                  </div>
                </div>

                <div class="editor-card">
                  <div class="editor-card__title">{{ t('digitalEvents.inviteLink') }}</div>
                  <DomainSelect v-model="form.custom_domain_id" />
                  <div class="form-group">
                    <label>{{ t('digitalEvents.inviteUrlSlug') }}</label>
                    <div class="slug-input slug-input--boxed">
                      <span>{{ inviteHost }}/invite/</span>
                      <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="khurram-zara" />
                    </div>
                  </div>
                </div>

                <div class="editor-card editor-card--template">
                  <div class="editor-card__title">{{ t('digitalPages.chooseTemplate') }}</div>
                  <TemplateGallery
                    v-model="form.template"
                    :templates="eventTemplates"
                    :categories="eventTemplateCategories"
                    :columns="3"
                    :can-use-premium="canUsePremium"
                    @premium-blocked="onPremiumBlocked"
                  />
                </div>
              </div>

              <div v-show="editorTab === 'sections'" class="tab-panel">
                <EventSectionsEditor :content="form.content" :sections="eventSectionDefs" />
              </div>

              <div v-show="editorTab === 'appearance'" class="tab-panel">
                <div class="editor-card">
                  <div class="editor-card__title">{{ t('digitalEvents.appearanceSettings') }}</div>
                  <ImageAssetField v-model="form.cover_image_path" :label="t('digitalEvents.coverImage')" folder="backgrounds" ai-context="event-invitation" ai-placeholder="elegant event invitation background" />
                  <div class="form-group">
                    <label>{{ t('common.themeColor') }}</label>
                    <div class="theme-picker">
                      <input v-model="form.theme_color" type="color" class="color-input" />
                      <div class="theme-swatches">
                        <button
                          v-for="swatch in themeSwatches"
                          :key="swatch"
                          type="button"
                          class="theme-swatch"
                          :style="{ background: swatch }"
                          :class="{ active: form.theme_color === swatch }"
                          @click="form.theme_color = swatch"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-show="editorTab === 'qr'" class="tab-panel">
                <QrStyleFields
                  v-model:qr-shape="form.qr_shape"
                  v-model:dot-style="form.dot_style"
                  v-model:corner-style="form.corner_style"
                  v-model:frame-style="form.frame_style"
                />
              </div>

              <p v-if="error" class="error-text">{{ error }}</p>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <div v-if="editorTab === 'qr'" class="preview-stage preview-stage--qr">
            <QrPreview
              minimal
              :content="previewInviteUrl"
              :name="form.title || t('digitalEvents.untitledEvent')"
              :foreground="form.theme_color"
              :logo-url="form.cover_image_path"
              :background-image="form.cover_image_path"
              :background="'#ffffff'"
              :size="240"
              :qr-shape="form.qr_shape"
              :dot-style="form.dot_style"
              :corner-style="form.corner_style"
              :frame-style="form.frame_style"
            />
            <p class="preview-qr-url">{{ previewInviteUrl }}</p>
          </div>
          <div v-else class="preview-stage preview-stage--device" :style="previewStageStyle">
            <EventHtmlPreview
              expandable
              live-preview
              device-frame
              :title="form.title"
              :subtitle="form.subtitle"
              :hosts="form.hosts"
              :event-date="form.event_date"
              :event-end-date="form.event_end_date"
              :venue-name="form.venue_name"
              :dress-code="form.dress_code"
              :cover-image="form.cover_image_path"
              :theme-color="form.theme_color"
              :content="form.content"
              :event-type="form.event_type"
              :slug="form.slug"
              :invite-url="previewInviteUrl"
              :template="form.template"
            />
          </div>
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!events.length" class="empty-state">
        <div class="empty-icon">🎊</div>
        <h3>{{ t('digitalEvents.emptyTitle') }}</h3>
        <p>{{ t('digitalEvents.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('digitalEvents.emptyCta') }}</button>
      </div>
      <div v-else class="events-grid">
        <article v-for="event in events" :key="event.id" class="event-item" :class="{ draft: !event.is_active }">
          <div class="event-item__stack">
            <div v-if="!event.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
            <div class="event-item__head">
              <h3 class="event-item__title">{{ event.title || t('digitalEvents.untitledEvent') }}</h3>
              <p class="event-item__slug">/invite/{{ event.slug }}</p>
            </div>
            <div class="event-item__preview">
              <EventHtmlPreview
                compact
                embedded
                :fixed-height="400"
                :title="event.title"
                :subtitle="event.subtitle"
                :hosts="event.hosts"
                :event-date="event.event_date"
                :event-end-date="event.event_end_date"
                :venue-name="event.venue_name"
                :dress-code="event.dress_code"
                :cover-image="event.cover_image_path"
                :theme-color="event.theme_color"
                :content="event.content || {}"
                :event-type="event.event_type"
                :slug="event.slug"
                :invite-url="event.invite_url"
                :domain-label="event.domain_label"
                :template="event.template || 'simple-invite'"
              />
            </div>
          </div>
          <div class="event-item__footer">
            <PublishToggle
              :model-value="!!event.is_active"
              :loading="togglingId === event.id"
              :active-label="t('publish.published')"
              :inactive-label="t('publish.draft')"
              @update:model-value="togglePublish(event)"
            />
            <span class="view-stat">
              <span class="view-stat__num">{{ event.view_count || 0 }}</span>
              <span class="view-stat__label">{{ t('common.views') }}</span>
            </span>
            <div class="event-item__actions">
              <CopyButton :text="event.invite_url" :label="t('common.copy')" />
              <button @click="openEdit(event)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="showAnalytics(event)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteEvent(event)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </article>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsEvent" class="drawer-overlay" @click.self="analyticsEvent = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsEvent.title }) }}</h3>
          <button @click="analyticsEvent = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="digital-events" :id="analyticsEvent.id" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useAuthStore } from '../../stores/auth'
import { useDomainsStore } from '../../stores/domains'
import EventSectionsEditor from '../../components/events/EventSectionsEditor.vue'
import SplitEditor from '../../components/ui/SplitEditor.vue'
import EventHtmlPreview from '../../components/previews/EventHtmlPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import TemplateGallery from '../../components/ui/TemplateGallery.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import { EVENT_TEMPLATES, EVENT_TEMPLATE_CATEGORIES, defaultEventTypeForTemplate } from '../../utils/eventTemplates'
import { EVENT_SECTIONS, defaultEventForm, normalizeEventContent } from '../../utils/eventSections'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()
const domains = useDomainsStore()
const dialog = useDialog()

const eventTemplates = computed(() => translateList(EVENT_TEMPLATES, t))
const eventTemplateCategories = computed(() => translateList(EVENT_TEMPLATE_CATEGORIES, t))
const eventSectionDefs = EVENT_SECTIONS
const canUsePremium = computed(() => auth.user?.plan && auth.user.plan !== 'free')

const events = ref([])
const loading = ref(true)
const loadError = ref('')
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsEvent = ref(null)
const togglingId = ref(null)
const editorTab = ref('content')
const themeSwatches = ['#e8655a', '#c9a227', '#6b4fa0', '#2563eb', '#0f766e', '#be185d', '#1a1333']

const inviteHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const form = ref(defaultEventForm())

const previewInviteUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/invite/${form.value.slug}` : `${base}/invite/preview`
})

const previewMode = computed(() => (editorTab.value === 'qr' ? 'qr' : 'content'))

const previewStageStyle = computed(() => {
  const tpl = EVENT_TEMPLATES.find((item) => item.id === form.value.template)
  const gradient = tpl?.thumbGradient
    || `linear-gradient(145deg, ${form.value.theme_color}, color-mix(in srgb, ${form.value.theme_color} 30%, #1a1333))`
  return {
    '--stage-accent': form.value.theme_color,
    '--stage-gradient': gradient,
  }
})

watch(() => form.value.template, (tpl) => {
  const eventType = defaultEventTypeForTemplate(tpl)
  form.value.event_type = eventType
  form.value.content = normalizeEventContent(form.value.content, eventType)
})

function onPremiumBlocked() {
  dialog.confirm({
    title: t('digitalEvents.premiumTemplateTitle'),
    message: t('digitalEvents.premiumTemplateMessage'),
    confirmText: t('digitalEvents.upgradePlan'),
    cancelText: t('common.cancel'),
  }).then((ok) => {
    if (ok) router.push('/app/billing')
  })
}

function openCreate() {
  editId.value = null
  form.value = defaultEventForm()
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function openEdit(event) {
  editId.value = event.id
  form.value = {
    ...event,
    template: event.template || 'simple-invite',
    content: normalizeEventContent(event.content, event.event_type),
    theme_color: event.theme_color || '#e8655a',
    event_date: event.event_date ? event.event_date.slice(0, 16) : '',
    event_end_date: event.event_end_date ? event.event_end_date.slice(0, 16) : '',
    qr_shape: event.qr_shape || 'square',
    dot_style: event.dot_style || 'square',
    corner_style: event.corner_style || 'sharp',
    frame_style: event.frame_style || 'none',
  }
  if (form.value.content.countdown && form.value.event_date && !form.value.content.countdown.target) {
    form.value.content.countdown.target = form.value.event_date
  }
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

async function load() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await api.get('/digital-events')
    events.value = data
  } catch {
    loadError.value = t('digitalEvents.loadFailed')
    events.value = []
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    if (!payload.content.countdown.target && payload.event_date) {
      payload.content = { ...payload.content, countdown: { ...payload.content.countdown, target: payload.event_date } }
    }
    delete payload.is_active
    delete payload.invite_url
    delete payload.domain_label
    delete payload.view_count
    if (editId.value) await api.put(`/digital-events/${editId.value}`, payload)
    else await api.post('/digital-events', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(event) {
  togglingId.value = event.id
  try {
    const { data } = await api.patch(`/digital-events/${event.id}/publish`)
    const idx = events.value.findIndex((e) => e.id === event.id)
    if (idx !== -1) events.value[idx] = data
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('digitalPages.updateFailedMessage'), variant: 'danger' })
  } finally {
    togglingId.value = null
  }
}

async function deleteEvent(event) {
  const ok = await dialog.confirm({
    title: t('digitalEvents.deleteTitle'),
    message: t('digitalEvents.deleteMessage', { name: event.title }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/digital-events/${event.id}`)
  await load()
}

function showAnalytics(event) {
  analyticsEvent.value = event
}

onMounted(async () => {
  domains.fetch()
  await load()
})
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; box-shadow: var(--shadow-sm); }
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.editor-panel__header h3 { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); }
.template-section, .content-section, .editor-card {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.875rem;
  padding: 1rem 1.125rem; display: flex; flex-direction: column; gap: 0.875rem;
}
.editor-card__title {
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.08em; color: var(--text-secondary);
}
.editor-card--template { padding-bottom: 0.75rem; }
.input-field--lg { font-size: 1rem; font-weight: 600; }
.slug-input--boxed {
  padding: 0.5rem 0.75rem; border-radius: 0.625rem;
  border: 1px solid var(--border); background: var(--surface);
}
.theme-picker { display: flex; flex-direction: column; gap: 0.625rem; }
.theme-swatches { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.theme-swatch {
  width: 1.75rem; height: 1.75rem; border-radius: 999px; border: 2px solid transparent;
  cursor: pointer; box-shadow: inset 0 0 0 1px rgba(0,0,0,.08);
}
.theme-swatch.active { border-color: var(--text-primary); transform: scale(1.08); }
.preview-stage {
  width: 100%;
  padding: 0.75rem 0.35rem 1rem;
  border-radius: 1.25rem;
  background:
    radial-gradient(ellipse at 50% -10%, color-mix(in srgb, var(--stage-accent, var(--brand)) 18%, transparent), transparent 58%),
    radial-gradient(ellipse at 80% 100%, color-mix(in srgb, var(--stage-accent, var(--brand)) 8%, transparent), transparent 50%),
    linear-gradient(180deg, var(--bg-subtle), var(--surface));
  border: 1px solid color-mix(in srgb, var(--stage-accent, var(--brand)) 12%, var(--border));
}
.preview-stage--device {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 0;
  overflow: visible;
  background:
    radial-gradient(ellipse at 50% 0%, color-mix(in srgb, var(--stage-accent, var(--brand)) 22%, transparent), transparent 62%),
    linear-gradient(165deg, color-mix(in srgb, var(--stage-accent, var(--brand)) 4%, var(--bg-subtle)), var(--surface));
}
.preview-stage--qr {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  min-height: 320px;
  padding: 1.5rem;
}
.preview-qr-url {
  font-size: 0.6875rem;
  font-family: ui-monospace, monospace;
  color: var(--text-muted);
  text-align: center;
  word-break: break-all;
  max-width: 260px;
}
.section-title { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
.slug-input { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; font-size: 0.8125rem; }
.item-block {
  padding: 0.75rem; background: var(--surface); border-radius: 0.5rem;
  border: 1px dashed var(--border); display: flex; flex-direction: column; gap: 0.5rem;
}
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.875rem; }
.remove-btn.block { font-size: 0.75rem; text-align: start; padding: 0; }
.form-actions { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.section-toggle {
  display: flex; align-items: center; gap: 0.5rem;
  font-weight: 700; font-size: 0.875rem; margin-bottom: 0.75rem; cursor: pointer;
}
.section-block {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem;
  padding: 0.875rem; margin-bottom: 0.75rem;
}
.section-block:last-child { margin-bottom: 0; }
.hint-text { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.4; }
.events-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.25rem;
  align-items: stretch;
}
.event-item {
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
.event-item:hover {
  box-shadow: var(--shadow-md);
  border-color: color-mix(in srgb, var(--brand) 25%, var(--border));
}
.event-item.draft { opacity: 0.92; }
.event-item__stack {
  position: relative;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
.event-item__head {
  flex-shrink: 0;
  padding: 0.875rem 1rem 0.5rem;
  border-bottom: 1px solid var(--border);
  background: var(--bg-subtle);
}
.event-item__title {
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.event-item__slug {
  font-family: ui-monospace, monospace;
  font-size: 0.6875rem;
  color: var(--brand);
  margin-top: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.item-preview-scroll,
.event-item__preview {
  flex: 1 1 auto;
  min-height: 280px;
  max-height: 400px;
  overflow: hidden;
  background: var(--bg-page);
}
.event-item__preview :deep(.html-doc-preview) {
  height: 100%;
  max-height: 100%;
}
.event-item__footer {
  flex-shrink: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}
.event-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
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
.draft-ribbon {
  position: absolute; top: 0.5rem; right: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.drawer-overlay { position: fixed; inset: 0; background: rgba(26,19,51,0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
</style>
