<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('qrCodes.title') }}</h2>
        <p class="page-sub">{{ t('qrCodes.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('qrCodes.newQrCode') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('qrCodes.editQrCode') : t('qrCodes.createQrCode') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="editorTab">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('qrCodes.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('qrCodes.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('qrCodes.tabs.qrDesign') }}</button>
              <button type="button" :class="{ active: editorTab === 'smart' }" @click="editorTab = 'smart'">{{ t('qrCodes.tabs.smart') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="editorTab === 'content'" class="tab-panel">
                <div class="form-group">
                  <label>{{ t('common.name') }}</label>
                  <input v-model="form.name" required class="input-field" :placeholder="t('qrCodes.namePlaceholder')" />
                </div>
                <div class="form-group">
                  <label>{{ t('qrCodes.destinationUrl') }}</label>
                  <input v-model="form.destination_url" type="url" required class="input-field" :placeholder="t('qrCodes.destinationPlaceholder')" />
                </div>
                <DomainSelect v-model="form.custom_domain_id" />
              </div>

              <div v-show="editorTab === 'appearance'" class="tab-panel">
                <div class="form-row">
                  <div class="form-group">
                    <label>{{ t('common.foreground') }}</label>
                    <input v-model="form.foreground_color" type="color" class="color-input" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('common.backgroundColor') }}</label>
                    <input v-model="form.background_color" type="color" class="color-input" />
                  </div>
                </div>
                <ImageAssetField v-model="form.logo_path" :label="t('qrCodes.centerLogo')" folder="logos" ai-context="qr-logo" ai-placeholder="minimal tech logo, flat icon" />
                <ImageAssetField v-model="form.background_image_path" :label="t('qrCodes.backgroundImage')" folder="backgrounds" ai-context="qr-background" ai-placeholder="abstract gradient pattern, minimal" />
                <div class="form-group">
                  <label>{{ t('common.sizeValue', { value: form.size }) }}</label>
                  <input v-model.number="form.size" type="range" min="200" max="600" step="20" class="range-input" />
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
                  :signed-url="previewSignedUrl"
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
          <QrPreview
            :content="previewScanContent"
            :name="form.name"
            :destination="form.destination_url"
            :scan-url="previewScanUrl"
            :domain-label="domains.labelFor(form.custom_domain_id)"
            :foreground="form.foreground_color"
            :background="form.background_color"
            :logo-url="form.logo_path"
            :background-image="form.background_image_path"
            :size="editorTab === 'qr' ? 220 : Math.min(form.size, 280)"
            :margin="form.margin"
            :error-correction="form.error_correction"
            :qr-shape="form.qr_shape"
            :dot-style="form.dot_style"
            :corner-style="form.corner_style"
            :frame-style="form.frame_style"
            :minimal="editorTab === 'qr'"
          />
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <div v-else-if="!qrCodes.length && !editing" class="empty-state">
      <div class="empty-icon">▦</div>
      <h3>{{ t('qrCodes.emptyTitle') }}</h3>
      <p>{{ t('qrCodes.emptyDesc') }}</p>
      <button @click="openCreate" class="btn-primary">{{ t('qrCodes.emptyCta') }}</button>
    </div>
    <div v-else class="item-grid">
      <div v-for="qr in qrCodes" :key="qr.id" class="qr-card" :class="{ inactive: !qr.is_active }">
        <div v-if="!qr.is_active" class="paused-ribbon">{{ t('common.paused') }}</div>

        <QrThumb
          class="qr-card__thumb"
          :content="scanUrlFor(qr)"
          :foreground="qr.foreground_color || '#1a1333'"
          :background="qr.background_color || '#ffffff'"
          :logo-url="qr.logo_path"
          :background-image="qr.background_image_path"
          :margin="qr.margin ?? 4"
          :error-correction="qr.error_correction || 'M'"
          :qr-shape="qr.qr_shape || 'square'"
          :dot-style="qr.dot_style || 'square'"
          :corner-style="qr.corner_style || 'sharp'"
          :frame-style="qr.frame_style || 'none'"
          :scan-optimized="false"
        />

        <div class="qr-card__body">
          <div class="qr-card__head">
            <div class="qr-card__info">
              <h3>{{ qr.name }}</h3>
              <p class="scan-url">{{ scanUrlFor(qr) }}</p>
            </div>
            <PublishToggle
              :model-value="!!qr.is_active"
              :loading="togglingId === qr.id"
              :active-label="t('common.active')"
              :inactive-label="t('common.paused')"
              @update:model-value="toggleActive(qr)"
            />
          </div>
          <p class="dest" :title="qr.destination_url">{{ qr.destination_url }}</p>
          <div class="qr-card__bottom">
            <div class="qr-meta">
              <span class="scan-stat">
                <span class="scan-stat__num">{{ qr.scan_count }}</span>
                <span class="scan-stat__label">{{ t('common.scans') }}</span>
              </span>
              <span>{{ t('common.sizePx', { size: qr.size || 400 }) }}</span>
              <span v-if="qr.domain_label">{{ qr.domain_label }}</span>
            </div>
            <div class="qr-card__actions">
              <CopyButton :text="scanUrlFor(qr)" :label="t('common.copy')" />
              <button @click="openEdit(qr)" class="action-btn">{{ t('common.edit') }}</button>
              <button :disabled="downloading === `${qr.id}-png`" @click="downloadQr(qr, 'png')" class="action-btn">
                {{ downloading === `${qr.id}-png` ? '…' : t('common.png') }}
              </button>
              <button :disabled="downloading === `${qr.id}-svg`" @click="downloadQr(qr, 'svg')" class="action-btn">
                {{ downloading === `${qr.id}-svg` ? '…' : t('common.svg') }}
              </button>
              <button @click="showAnalytics(qr)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteQr(qr)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    </template>

    <div v-if="analyticsQr" class="drawer-overlay" @click.self="analyticsQr = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsQr.name }) }}</h3>
          <button @click="analyticsQr = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="qr-codes" :id="analyticsQr.id" />
        <AiInsightsPanel type="qr-codes" :id="analyticsQr.id" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useDomainsStore } from '../../stores/domains'
import SplitEditor from '../../components/ui/SplitEditor.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import QrThumb from '../../components/ui/QrThumb.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrRoutingEditor from '../../components/smart-qr/QrRoutingEditor.vue'
import QrSecurityPanel from '../../components/smart-qr/QrSecurityPanel.vue'
import AiInsightsPanel from '../../components/smart-qr/AiInsightsPanel.vue'
import { useDialog } from '../../composables/useDialog'
import { downloadQrPng, downloadQrSvg } from '../../utils/qrDownload'

const { t } = useI18n()
const route = useRoute()
const domains = useDomainsStore()
const dialog = useDialog()

const qrCodes = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsQr = ref(null)
const downloading = ref(null)
const togglingId = ref(null)
const funnels = ref([])
const editorTab = ref('content')

const defaultForm = () => ({
  name: '', destination_url: '', foreground_color: '#1a1333', background_color: '#ffffff',
  logo_path: '', background_image_path: '', custom_domain_id: null,
  size: 400, error_correction: 'M', margin: 4,
  qr_shape: 'square', dot_style: 'square', corner_style: 'sharp', frame_style: 'none',
  is_active: true,
  funnel_id: null, routing_rules: [],
  security: { signed: false, one_time_access: false, password_enabled: false, password: '' },
  expires_at: null, max_scans: 0,
})
const form = ref(defaultForm())

const previewScanContent = computed(() => {
  if (editId.value) {
    const qr = qrCodes.value.find(q => q.id === editId.value)
    return qr?.scan_url || `${domains.baseUrlFor(form.value.custom_domain_id)}/api/qr/${qr?.code}`
  }
  return form.value.destination_url || 'https://qrscan.digital'
})

const previewScanUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  if (editId.value) {
    const qr = qrCodes.value.find(q => q.id === editId.value)
    return qr ? `${base}/api/qr/${qr.code}` : `${base}/api/qr/...`
  }
  return `${base}/api/qr/${t('common.generatedOnSave')}`
})

const previewSignedUrl = computed(() => {
  if (!form.value.security?.signed) return ''
  if (editId.value) {
    const qr = qrCodes.value.find(q => q.id === editId.value)
    return qr?.signed_scan_url || ''
  }
  return ''
})

function scanUrlFor(qr) {
  const url = qr.signed_scan_url && qr.security?.signed ? qr.signed_scan_url : (qr.scan_url || `${window.location.origin}/api/qr/${qr.code}`)
  return url
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function openEdit(qr) {
  editId.value = qr.id
  form.value = {
    ...qr,
    foreground_color: qr.foreground_color || '#1a1333',
    background_color: qr.background_color || '#ffffff',
    margin: qr.margin ?? 4,
    qr_shape: qr.qr_shape || 'square',
    dot_style: qr.dot_style || 'square',
    corner_style: qr.corner_style || 'sharp',
    frame_style: qr.frame_style || 'none',
    funnel_id: qr.funnel_id || null,
    routing_rules: qr.routing_rules || [],
    security: {
      signed: !!qr.security?.signed,
      one_time_access: !!qr.security?.one_time_access,
      password_enabled: !!qr.security?.password_enabled,
      password: qr.security?.password_hash ? '********' : '',
    },
    expires_at: qr.expires_at || null,
    max_scans: qr.max_scans || 0,
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
  const { data } = await api.get('/qr-codes')
  qrCodes.value = data
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    delete payload.is_active
    if (editId.value) {
      await api.put(`/qr-codes/${editId.value}`, payload)
    } else {
      await api.post('/qr-codes', { ...payload, is_active: true })
    }
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function toggleActive(qr) {
  togglingId.value = qr.id
  try {
    const { data } = await api.patch(`/qr-codes/${qr.id}/active`)
    const idx = qrCodes.value.findIndex(q => q.id === qr.id)
    if (idx !== -1) qrCodes.value[idx] = data
  } catch {
    dialog.alert({
      title: t('common.notice'),
      message: t('qrCodes.updateFailedMessage'),
      variant: 'danger',
    })
  } finally {
    togglingId.value = null
  }
}

async function deleteQr(qr) {
  const ok = await dialog.confirm({
    title: t('qrCodes.deleteTitle'),
    message: t('qrCodes.deleteMessage', { name: qr.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/qr-codes/${qr.id}`)
  await load()
}

async function downloadQr(qr, format) {
  const key = `${qr.id}-${format}`
  downloading.value = key
  try {
    const url = scanUrlFor(qr)
    if (format === 'png') {
      await downloadQrPng(qr, url)
    } else {
      await downloadQrSvg(qr, url)
    }
  } catch {
    dialog.alert({
      title: t('qrCodes.downloadFailedTitle'),
      message: t('qrCodes.downloadFailedMessage'),
      variant: 'danger',
    })
  } finally {
    downloading.value = null
  }
}

function showAnalytics(qr) { analyticsQr.value = qr }

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
  if (route.query.smart) {
    openCreate()
    if (route.query.name) form.value.name = route.query.name
    if (route.query.destination) form.value.destination_url = route.query.destination
  }
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
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.range-input { width: 100%; accent-color: var(--brand); }
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.item-grid { display: flex; flex-direction: column; gap: 0.625rem; }
.qr-card {
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
.qr-card:hover { box-shadow: var(--shadow-md); }
.qr-card.inactive { opacity: 0.88; }
.qr-card.inactive::after {
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
.qr-card__thumb { align-self: center; padding-top: 0.125rem; }
.qr-card__body { min-width: 0; display: flex; flex-direction: column; gap: 0.375rem; }
.qr-card__head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
}
.qr-card__info { flex: 1; min-width: 0; }
.qr-card h3 { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); line-height: 1.3; }
.scan-url { font-family: monospace; font-size: 0.8125rem; color: var(--brand); margin-top: 0.125rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.dest { font-size: 0.75rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.qr-card__bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 0.125rem;
}
.qr-meta { display: flex; flex-wrap: wrap; gap: 0.5rem; font-size: 0.6875rem; color: var(--text-muted); align-items: center; }
.scan-stat {
  display: inline-flex;
  align-items: baseline;
  gap: 0.2rem;
  padding: 0.15rem 0.5rem;
  border-radius: 9999px;
  background: var(--purple-muted);
  border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.scan-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.scan-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.qr-card__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem;
  border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer; transition: all 0.15s;
}
.action-btn:hover:not(:disabled) { border-color: var(--brand); color: var(--brand); background: var(--brand-muted); }
.action-btn:disabled { opacity: 0.5; cursor: wait; }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
@media (max-width: 640px) {
  .qr-card { grid-template-columns: 1fr; }
  .qr-card__thumb { justify-self: start; }
  .qr-card__bottom { flex-direction: column; align-items: flex-start; }
}
.drawer-overlay { position: fixed; inset: 0; background: rgba(26, 19, 51, 0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
.smart-section { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.875rem; }
.smart-section__title { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); padding-bottom: 0.25rem; border-bottom: 1px solid var(--border); }
</style>
