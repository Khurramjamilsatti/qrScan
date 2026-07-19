<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('shortLinks.title') }}</h2>
        <p class="page-sub">{{ t('shortLinks.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('shortLinks.newLink') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('shortLinks.editShortLink') : t('shortLinks.createShortLink') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="editorTab">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('shortLinks.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('shortLinks.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('shortLinks.tabs.qrDesign') }}</button>
              <button type="button" :class="{ active: editorTab === 'smart' }" @click="editorTab = 'smart'">{{ t('shortLinks.tabs.smart') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="editorTab === 'content'" class="tab-panel">
                <div class="form-group">
                  <label>{{ t('common.title') }}</label>
                  <input v-model="form.title" class="input-field" :placeholder="t('shortLinks.titlePlaceholder')" />
                </div>
                <div class="form-group">
                  <label>{{ t('common.description') }}</label>
                  <textarea v-model="form.description" class="input-field" rows="2" :placeholder="t('shortLinks.descriptionPlaceholder')"></textarea>
                </div>
                <DomainSelect v-model="form.custom_domain_id" />
                <div class="form-group">
                  <label>{{ t('shortLinks.customSlug') }}</label>
                  <div class="slug-input">
                    <span>{{ linkHost }}/r/</span>
                    <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" :placeholder="t('shortLinks.slugPlaceholder')" />
                  </div>
                </div>
                <div class="form-group">
                  <label>{{ t('qrCodes.destinationUrl') }}</label>
                  <input v-model="form.destination_url" type="url" required class="input-field" :placeholder="t('shortLinks.destinationPlaceholder')" />
                </div>
                <div class="utm-section">
                  <div class="utm-title">{{ t('shortLinks.utmParameters') }}</div>
                  <div class="form-row">
                    <input v-model="form.utm_source" class="input-field" placeholder="utm_source" />
                    <input v-model="form.utm_medium" class="input-field" placeholder="utm_medium" />
                  </div>
                  <div class="form-row">
                    <input v-model="form.utm_campaign" class="input-field" placeholder="utm_campaign" />
                    <input v-model="form.utm_term" class="input-field" placeholder="utm_term" />
                  </div>
                  <input v-model="form.utm_content" class="input-field" placeholder="utm_content" />
                </div>
              </div>

              <div v-show="editorTab === 'appearance'" class="tab-panel">
                <div class="form-row">
                  <div class="form-group">
                    <label>{{ t('common.foreground') }}</label>
                    <input v-model="form.foreground_color" type="color" class="color-input" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('common.background') }}</label>
                    <input v-model="form.background_color" type="color" class="color-input" />
                  </div>
                </div>
                <ImageAssetField v-model="form.logo_path" :label="t('qrCodes.centerLogo')" folder="logos" ai-context="qr-logo" ai-placeholder="minimal tech logo, flat icon" />
                <ImageAssetField v-model="form.background_image_path" :label="t('qrCodes.backgroundImage')" folder="backgrounds" ai-context="qr-background" ai-placeholder="abstract gradient pattern, minimal" />
                <div class="form-group">
                  <label>{{ t('common.sizeValue', { value: form.qr_size }) }}</label>
                  <input v-model.number="form.qr_size" type="range" min="200" max="600" step="20" class="range-input" />
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>{{ t('common.errorCorrection') }}</label>
                    <select v-model="form.error_correction" class="input-field">
                      <option value="L">{{ t('common.errorCorrectionLow') }}</option>
                      <option value="M">{{ t('common.errorCorrectionMedium') }}</option>
                      <option value="Q">{{ t('common.errorCorrectionQuartile') }}</option>
                      <option value="H">{{ t('common.errorCorrectionHigh') }}</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>{{ t('common.marginValue', { value: form.margin }) }}</label>
                    <input v-model.number="form.margin" type="range" min="0" max="8" class="range-input" />
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

              <div v-show="editorTab === 'smart'" class="tab-panel">
                <div class="form-group">
                  <label>{{ t('smartQr.funnels.attach') }}</label>
                  <select v-model="form.funnel_id" class="input-field">
                    <option :value="null">{{ t('smartQr.funnels.none') }}</option>
                    <option v-for="f in funnels" :key="f.id" :value="f.id">{{ f.name }}</option>
                  </select>
                </div>
                <QrRoutingEditor v-model="form.routing_rules" />
                <QrSecurityPanel
                  v-model="form.security"
                  v-model:expires-at-value="form.expires_at"
                  v-model:max-scans-value="form.max_scans"
                  :signed-url="editId ? (links.find(l => l.id === editId)?.signed_scan_url || '') : ''"
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
          <template v-if="editorTab === 'qr'">
            <QrPreview
              v-if="form.slug"
              minimal
              :content="previewScanUrl"
              :name="form.title || t('shortLinks.title')"
              :foreground="form.foreground_color"
              :background="form.background_color"
              :logo-url="form.logo_path"
              :background-image="form.background_image_path"
              :size="220"
              :margin="form.margin"
              :error-correction="form.error_correction"
              :qr-shape="form.qr_shape"
              :dot-style="form.dot_style"
              :corner-style="form.corner_style"
              :frame-style="form.frame_style"
              scan-optimized
            />
          </template>
          <template v-else>
            <LinkPreview
              :slug="form.slug"
              :base-url="linkHost + '/r/'"
              :title="form.title"
              :description="form.description"
              :destination="form.destination_url"
              :utm_source="form.utm_source"
              :utm_medium="form.utm_medium"
              :utm_campaign="form.utm_campaign"
              :utm_term="form.utm_term"
              :utm_content="form.utm_content"
              :expires-at="form.expires_at"
            />
          </template>
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <div v-else-if="!links.length && !editing" class="empty-state">
      <div class="empty-icon">🔗</div>
      <h3>{{ t('shortLinks.emptyTitle') }}</h3>
      <p>{{ t('shortLinks.emptyDesc') }}</p>
      <button @click="openCreate" class="btn-primary">{{ t('shortLinks.emptyCta') }}</button>
    </div>
    <div v-else class="item-grid">
      <div v-for="link in links" :key="link.id" class="link-card" :class="{ inactive: !link.is_active }">
        <div v-if="!link.is_active" class="paused-ribbon">{{ t('common.paused') }}</div>

        <QrThumb
          v-if="link.slug"
          class="link-card__qr"
          :content="shortLinkScanUrl(domains, link.slug, link.custom_domain_id, link)"
          :foreground="link.foreground_color || '#1a1333'"
          :background="link.background_color || '#ffffff'"
          :logo-url="link.logo_path"
          :background-image="link.background_image_path"
          :margin="link.margin || 4"
          :error-correction="link.error_correction || 'M'"
          :qr-shape="link.qr_shape || 'square'"
          :dot-style="link.dot_style || 'square'"
          :corner-style="link.corner_style || 'sharp'"
          :frame-style="link.frame_style || 'none'"
        />

        <div class="link-card__body">
          <div class="link-card__head">
            <div class="link-card__info">
              <h3>{{ link.title || link.slug }}</h3>
              <p class="short-url">{{ shortLinkShareUrl(domains, link.slug, link.custom_domain_id, link) }}</p>
            </div>
            <PublishToggle
              :model-value="!!link.is_active"
              :loading="togglingId === link.id"
              :active-label="t('common.active')"
              :inactive-label="t('common.paused')"
              @update:model-value="toggleActive(link)"
            />
          </div>
          <p class="dest" :title="link.destination_url">{{ link.destination_url }}</p>
          <div class="link-card__bottom">
            <div class="link-meta">
              <span class="click-stat">
                <span class="click-stat__num">{{ link.click_count }}</span>
                <span class="click-stat__label">{{ t('common.clicks') }}</span>
              </span>
              <span v-if="link.utm_campaign">{{ link.utm_campaign }}</span>
              <span v-if="link.expires_at">{{ formatDate(link.expires_at) }}</span>
            </div>
            <div class="link-card__actions">
              <CopyButton :text="shortLinkShareUrl(domains, link.slug, link.custom_domain_id, link)" :label="t('common.copy')" />
              <button @click="openEdit(link)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="showAnalytics(link)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteLink(link)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </template>

    <div v-if="analyticsLink" class="drawer-overlay" @click.self="analyticsLink = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsLink.title || analyticsLink.slug }) }}</h3>
          <button @click="analyticsLink = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="short-links" :id="analyticsLink.id" />
        <AiInsightsPanel type="short-links" :id="analyticsLink.id" />
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
import LinkPreview from '../../components/previews/LinkPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import QrThumb from '../../components/ui/QrThumb.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import QrRoutingEditor from '../../components/smart-qr/QrRoutingEditor.vue'
import QrSecurityPanel from '../../components/smart-qr/QrSecurityPanel.vue'
import AiInsightsPanel from '../../components/smart-qr/AiInsightsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import { useDialog } from '../../composables/useDialog'
import { shortLinkScanUrl, shortLinkShareUrl } from '../../utils/shortLinkUrls'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()
const linkHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})
const previewScanUrl = computed(() => shortLinkScanUrl(
  domains,
  form.value.slug,
  form.value.custom_domain_id,
  editId.value ? links.value.find(l => l.id === editId.value) : null,
))

const links = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsLink = ref(null)
const togglingId = ref(null)
const funnels = ref([])
const editorTab = ref('content')

const defaultForm = () => ({
  title: '', description: '', slug: '', destination_url: '', custom_domain_id: null,
  utm_source: '', utm_medium: '', utm_campaign: '', utm_term: '', utm_content: '',
  expires_at: '', is_active: true,
  foreground_color: '#1a1333', background_color: '#ffffff',
  logo_path: '', background_image_path: '',
  qr_size: 400, error_correction: 'M', margin: 4,
  qr_shape: 'square', dot_style: 'square', corner_style: 'sharp', frame_style: 'none',
  funnel_id: null, routing_rules: [],
  security: { signed: false, one_time_access: false, password_enabled: false, password: '' },
  max_scans: 0,
})
const form = ref(defaultForm())

function openCreate() { editId.value = null; form.value = defaultForm(); editorTab.value = 'content'; editing.value = true; error.value = '' }
function openEdit(link) {
  editId.value = link.id
  form.value = {
    ...link,
    expires_at: link.expires_at?.slice(0, 16) || '',
    foreground_color: link.foreground_color || '#1a1333',
    background_color: link.background_color || '#ffffff',
    qr_size: link.qr_size || 400,
    error_correction: link.error_correction || 'M',
    margin: link.margin ?? 4,
    qr_shape: link.qr_shape || 'square',
    dot_style: link.dot_style || 'square',
    corner_style: link.corner_style || 'sharp',
    frame_style: link.frame_style || 'none',
    funnel_id: link.funnel_id || null,
    routing_rules: link.routing_rules || [],
    security: {
      signed: !!link.security?.signed,
      one_time_access: !!link.security?.one_time_access,
      password_enabled: !!link.security?.password_enabled,
      password: link.security?.password_hash ? '********' : '',
    },
    max_scans: link.max_scans || 0,
  }
  editorTab.value = 'content'
  editing.value = true
}
function closeEditor() { editing.value = false; editId.value = null }

async function load() { const { data } = await api.get('/short-links'); links.value = data }

async function save() {
  saving.value = true; error.value = ''
  try {
    const payload = { ...form.value }
    if (payload.expires_at && !payload.expires_at.includes('T')) {
      payload.expires_at = new Date(payload.expires_at).toISOString()
    } else if (!payload.expires_at) {
      delete payload.expires_at
    }
    delete payload.is_active
    if (editId.value) await api.put(`/short-links/${editId.value}`, payload)
    else await api.post('/short-links', { ...payload, is_active: true })
    closeEditor(); await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally { saving.value = false }
}

async function toggleActive(link) {
  togglingId.value = link.id
  try {
    const { data } = await api.patch(`/short-links/${link.id}/active`)
    const idx = links.value.findIndex(l => l.id === link.id)
    if (idx !== -1) links.value[idx] = data
  } catch {
    dialog.alert({
      title: t('common.notice'),
      message: t('shortLinks.updateFailedMessage'),
      variant: 'danger',
    })
  } finally {
    togglingId.value = null
  }
}

async function deleteLink(link) {
  const ok = await dialog.confirm({
    title: t('shortLinks.deleteTitle'),
    message: t('shortLinks.deleteMessage', { slug: link.slug || link.destination_url }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/short-links/${link.id}`)
  await load()
}

function showAnalytics(link) { analyticsLink.value = link }
function formatDate(d) { return new Date(d).toLocaleDateString() }

async function loadFunnels() {
  try {
    const { data } = await api.get('/qr-funnels')
    funnels.value = data
  } catch {
    funnels.value = []
  }
}

onMounted(async () => {
  domains.fetch()
  loadFunnels()
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
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.range-input { width: 100%; accent-color: var(--brand); }
.item-grid { display: flex; flex-direction: column; gap: 0.625rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.utm-section, .qr-section {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.utm-title, .section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
.link-card {
  position: relative;
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 0.875rem;
  align-items: start;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 0.875rem 1rem;
  transition: box-shadow 0.2s;
}
.link-card:hover { box-shadow: var(--shadow-md); }
.link-card.inactive { opacity: 0.88; }
.link-card.inactive::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 1rem;
  pointer-events: none;
  border: 2px dashed color-mix(in srgb, var(--text-muted) 35%, transparent);
}
.paused-ribbon {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  z-index: 5;
  font-size: 0.5625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.15rem 0.4rem;
  border-radius: 0.25rem;
  background: var(--gold-muted);
  color: #92680a;
  border: 1px solid color-mix(in srgb, var(--gold) 40%, transparent);
}
.link-card__qr { align-self: center; padding-top: 0.125rem; }
.link-card__body { min-width: 0; display: flex; flex-direction: column; gap: 0.375rem; }
.link-card__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}
.link-card__info { flex: 1; min-width: 0; }
.link-card h3 { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); line-height: 1.3; }
.short-url { font-family: monospace; font-size: 0.8125rem; color: var(--brand); margin-top: 0.125rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dest { font-size: 0.75rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.link-card__bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 0.125rem;
}
.link-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; font-size: 0.6875rem; color: var(--text-muted); align-items: center; }
.click-stat {
  display: inline-flex;
  align-items: baseline;
  gap: 0.2rem;
  padding: 0.15rem 0.5rem;
  border-radius: 9999px;
  background: var(--purple-muted);
  border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.click-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.click-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.link-card__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem;
  border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer; transition: all 0.15s;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-muted); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
@media (max-width: 640px) {
  .link-card { grid-template-columns: 1fr; }
  .link-card__qr { justify-self: start; }
  .link-card__bottom { flex-direction: column; align-items: flex-start; }
}
.drawer-overlay { position: fixed; inset: 0; background: rgba(26, 19, 51, 0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
.smart-section { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.875rem; }
.smart-section__title { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); padding-bottom: 0.25rem; border-bottom: 1px solid var(--border); }
</style>
