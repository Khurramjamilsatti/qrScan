<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalBadges.title') }}</h2>
        <p class="page-sub">{{ t('digitalBadges.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('digitalBadges.newBadge') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalBadges.editBadge') : t('digitalBadges.createBadge') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor>
        <template #form>
          <form @submit.prevent="save" class="form-stack">
            <DomainSelect v-model="form.custom_domain_id" />
            <div class="form-group">
              <label>{{ t('digitalBadges.badgeUrlSlug') }}</label>
              <div class="slug-input">
                <span>{{ badgeHost }}/badge/</span>
                <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="ux-design-2026" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('digitalBadges.badgeTitle') }}</label>
              <input v-model="form.title" required class="input-field" placeholder="UX Design Certificate" />
            </div>

            <div class="template-section">
              <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
              <div class="template-grid template-grid--3">
                <button
                  v-for="tpl in badgeTemplates"
                  :key="tpl.id"
                  type="button"
                  class="template-card"
                  :class="{ active: form.template === tpl.id }"
                  @click="form.template = tpl.id"
                >
                  <span class="template-card__icon">{{ tpl.icon }}</span>
                  <span class="template-card__label">{{ tpl.label }}</span>
                  <span class="template-card__desc">{{ tpl.description }}</span>
                </button>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalBadges.recipientName') }}</label>
                <input v-model="form.recipient_name" required class="input-field" :placeholder="t('auth.namePlaceholder')" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalBadges.recipientEmail') }}</label>
                <input v-model="form.recipient_email" type="email" class="input-field" :placeholder="t('auth.emailPlaceholder')" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('businessCards.company') }}</label>
                <input v-model="form.issuer_name" class="input-field" placeholder="Acme Academy" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalBadges.badgeId') }}</label>
                <input v-model="form.badge_id" class="input-field" placeholder="BADGE-2026-001" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('common.description') }}</label>
              <textarea v-model="form.description" class="input-field" rows="3" placeholder="Awarded for outstanding achievement..."></textarea>
            </div>

            <div class="content-section">
              <div class="repeater-head">
                <span class="section-title">{{ t('common.tags') }}</span>
                <button type="button" @click="addSkill" class="link-btn">+ {{ t('common.add') }}</button>
              </div>
              <div v-for="(skill, i) in form.skills" :key="i" class="repeater-row">
                <input v-model="form.skills[i]" class="input-field" :placeholder="t('common.name')" />
                <button type="button" @click="form.skills.splice(i, 1)" class="remove-btn">✕</button>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalBadges.issueDate') }}</label>
                <input v-model="form.issue_date" type="date" class="input-field" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalBadges.expiryDate') }}</label>
                <input v-model="form.expiry_date" type="date" class="input-field" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('digitalBadges.verifyUrl') }}</label>
              <input v-model="form.verify_url" type="url" class="input-field" placeholder="https://verify.example.com/badge/..." />
            </div>

            <div class="content-section">
              <div class="section-title">{{ t('digitalBadges.displaySettings') }}</div>
              <label class="checkbox-row">
                <input v-model="form.settings.show_skills" type="checkbox" />
                <span>{{ t('digitalBadges.showSkills') }}</span>
              </label>
              <label class="checkbox-row">
                <input v-model="form.settings.show_dates" type="checkbox" />
                <span>{{ t('digitalBadges.showDates') }}</span>
              </label>
              <label class="checkbox-row">
                <input v-model="form.settings.show_badge_id" type="checkbox" />
                <span>{{ t('digitalBadges.showBadgeId') }}</span>
              </label>
              <label class="checkbox-row">
                <input v-model="form.settings.show_verify_link" type="checkbox" />
                <span>{{ t('digitalBadges.showVerifyLink') }}</span>
              </label>
              <label class="checkbox-row">
                <input v-model="form.settings.show_qr" type="checkbox" />
                <span>{{ t('digitalBadges.showQrCode') }}</span>
              </label>
            </div>

            <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="badge-logo" ai-placeholder="organization logo emblem" />
            <ImageAssetField v-model="form.badge_image_path" :label="t('digitalBadges.badgeImage')" folder="badges" ai-context="achievement-badge" ai-placeholder="gold achievement badge icon" />
            <ImageAssetField v-model="form.background_image_path" :label="t('common.background')" folder="backgrounds" ai-context="certificate-background" ai-placeholder="elegant certificate background" />
            <div class="form-group">
              <label>{{ t('common.themeColor') }}</label>
              <input v-model="form.theme_color" type="color" class="color-input" />
            </div>

            <div class="qr-section">
              <div class="section-title">{{ t('qrCodes.qrCodeStyle') }}</div>
              <QrStyleFields
                v-model:qr-shape="form.qr_shape"
                v-model:dot-style="form.dot_style"
                v-model:corner-style="form.corner_style"
                v-model:frame-style="form.frame_style"
              />
            </div>

            <p v-if="error" class="error-text">{{ error }}</p>
            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <BadgePreview
            :title="form.title"
            :template="form.template"
            :recipient-name="form.recipient_name"
            :issuer-name="form.issuer_name"
            :badge-id="form.badge_id"
            :description="form.description"
            :skills="form.skills"
            :issue-date="form.issue_date"
            :expiry-date="form.expiry_date"
            :verify-url="form.verify_url"
            :settings="form.settings"
            :theme-color="form.theme_color"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :badge-image="form.badge_image_path"
            :badge-url="previewBadgeUrl"
          />
          <div class="mt-4 text-center">
            <QrPreview
              v-if="form.slug"
              :content="previewBadgeUrl"
              :name="form.title"
              :foreground="form.theme_color"
              :logo-url="form.logo_path"
              :background-image="form.background_image_path"
              :size="140"
              :qr-shape="form.qr_shape"
              :dot-style="form.dot_style"
              :corner-style="form.corner_style"
              :frame-style="form.frame_style"
            />
          </div>
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!badges.length" class="empty-state">
        <div class="empty-icon">🏅</div>
        <h3>{{ t('digitalBadges.emptyTitle') }}</h3>
        <p>{{ t('digitalBadges.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('digitalBadges.emptyCta') }}</button>
      </div>
      <div v-else class="badges-grid">
        <div v-for="badge in badges" :key="badge.id" class="badge-item" :class="{ draft: !badge.is_active }">
          <div v-if="!badge.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
          <BadgePreview
            :title="badge.title"
            :template="badge.template"
            :recipient-name="badge.recipient_name"
            :issuer-name="badge.issuer_name"
            :badge-id="badge.badge_id"
            :description="badge.description"
            :skills="badge.skills || []"
            :issue-date="badge.issue_date"
            :expiry-date="badge.expiry_date"
            :verify-url="badge.verify_url"
            :settings="badge.settings || {}"
            :theme-color="badge.theme_color"
            :logo="badge.logo_path"
            :background-image="badge.background_image_path"
            :badge-image="badge.badge_image_path"
            :badge-url="badge.badge_url"
          />
          <div class="badge-item__footer">
            <PublishToggle
              :model-value="!!badge.is_active"
              :loading="togglingId === badge.id"
              :active-label="t('publish.published')"
              :inactive-label="t('publish.draft')"
              @update:model-value="togglePublish(badge)"
            />
            <span class="view-stat">
              <span class="view-stat__num">{{ badge.view_count }}</span>
              <span class="view-stat__label">{{ t('common.views') }}</span>
            </span>
            <div class="badge-item__actions">
              <CopyButton :text="badge.badge_url" :label="t('common.copy')" />
              <button @click="openEdit(badge)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="showAnalytics(badge)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteBadge(badge)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsBadge" class="drawer-overlay" @click.self="analyticsBadge = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsBadge.title }) }}</h3>
          <button @click="analyticsBadge = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="digital-badges" :id="analyticsBadge.id" />
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
import BadgePreview from '../../components/previews/BadgePreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import { BADGE_TEMPLATES, defaultBadgeForm, defaultBadgeSettings } from '../../utils/digitalModules'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()

const badges = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const loadError = ref('')
const analyticsBadge = ref(null)
const togglingId = ref(null)

const badgeTemplates = computed(() => translateList(BADGE_TEMPLATES, t))

const badgeHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const form = ref(defaultBadgeForm())

const previewBadgeUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/badge/${form.value.slug}` : `${base}/badge/...`
})

function addSkill() {
  if (!form.value.skills) form.value.skills = []
  form.value.skills.push('')
}

function openCreate() {
  editId.value = null
  form.value = defaultBadgeForm()
  editing.value = true
  error.value = ''
}

function openEdit(badge) {
  editId.value = badge.id
  form.value = {
    ...badge,
    skills: JSON.parse(JSON.stringify(badge.skills || [])),
    settings: { ...defaultBadgeSettings(), ...(badge.settings || {}) },
    theme_color: badge.theme_color || '#e8655a',
    qr_shape: badge.qr_shape || 'square',
    dot_style: badge.dot_style || 'square',
    corner_style: badge.corner_style || 'sharp',
    frame_style: badge.frame_style || 'none',
  }
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
    const { data } = await api.get('/digital-badges')
    badges.value = Array.isArray(data) ? data : []
  } catch (e) {
    badges.value = []
    loadError.value = e.response?.data?.message || t('digitalBadges.loadFailed')
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = JSON.parse(JSON.stringify(form.value))
    delete payload.is_active
    delete payload.badge_url
    delete payload.domain_label
    delete payload.view_count
    if (editId.value) await api.put(`/digital-badges/${editId.value}`, payload)
    else await api.post('/digital-badges', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(badge) {
  togglingId.value = badge.id
  try {
    const { data } = await api.patch(`/digital-badges/${badge.id}/publish`)
    const idx = badges.value.findIndex(b => b.id === badge.id)
    if (idx !== -1) badges.value[idx] = data
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('digitalPages.updateFailedMessage'), variant: 'danger' })
  } finally {
    togglingId.value = null
  }
}

async function deleteBadge(badge) {
  const ok = await dialog.confirm({
    title: t('digitalBadges.deleteTitle'),
    message: t('digitalBadges.deleteMessage', { title: badge.title }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/digital-badges/${badge.id}`)
  await load()
}

function showAnalytics(badge) { analyticsBadge.value = badge }

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
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.editor-panel__header h3 { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); }
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.template-section, .content-section, .qr-section {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.section-title { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
.template-grid--3 { grid-template-columns: 1fr; }
@media (min-width: 640px) { .template-grid--3 { grid-template-columns: repeat(3, 1fr); } }
.template-card {
  display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.25rem;
  padding: 0.75rem 0.5rem; border-radius: 0.75rem; border: 2px solid var(--border);
  background: var(--surface); cursor: pointer; transition: all 0.15s;
}
.template-card:hover { border-color: color-mix(in srgb, var(--brand) 40%, var(--border)); }
.template-card.active { border-color: var(--brand); background: var(--brand-muted); }
.template-card__icon { font-size: 1.25rem; }
.template-card__label { font-size: 0.75rem; font-weight: 700; color: var(--text-primary); }
.template-card__desc { font-size: 0.625rem; color: var(--text-muted); line-height: 1.3; }
.repeater-head { display: flex; justify-content: space-between; align-items: center; }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; font-size: 0.8125rem; }
.repeater-row { display: grid; grid-template-columns: 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem; }
.checkbox-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--text-secondary); cursor: pointer; }
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.875rem; }
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.badges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.badge-item { position: relative; display: flex; flex-direction: column; gap: 0.75rem; }
.badge-item.draft { opacity: 0.88; }
.draft-ribbon {
  position: absolute; top: 0.5rem; left: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.badge-item__footer { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.badge-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
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
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
</style>
