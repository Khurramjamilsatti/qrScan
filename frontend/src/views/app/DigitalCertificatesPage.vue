<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalCertificates.title') }}</h2>
        <p class="page-sub">{{ t('digitalCertificates.subtitle') }}</p>
      </div>
      <div class="header-actions">
        <button type="button" class="btn-secondary text-sm" @click="showBulk = true">{{ t('digitalCertificates.bulkImport') }}</button>
        <button type="button" class="btn-primary text-sm" @click="openCreate">+ {{ t('digitalCertificates.newCertificate') }}</button>
      </div>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalCertificates.editCertificate') : t('digitalCertificates.createCertificate') }}</h3>
        <button type="button" class="btn-ghost text-sm" @click="closeEditor">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="editorTab">
        <template #form>
          <form class="editor-form" @submit.prevent="save">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('digitalCertificates.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('digitalCertificates.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('digitalCertificates.tabs.qrDesign') }}</button>
            </div>

            <div class="editor-form__scroll">
            <div v-show="editorTab === 'content'" class="tab-panel">
              <div class="template-section">
                <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
                <TemplateGallery
                  v-model="form.template"
                  :templates="certificateTemplates"
                  :columns="2"
                />
              </div>
              <DomainSelect v-model="form.custom_domain_id" />
              <div class="form-group">
                <label>{{ t('digitalCertificates.certificateUrlSlug') }}</label>
                <div class="slug-input">
                  <span>{{ certHost }}/certificate/</span>
                  <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" />
                </div>
              </div>
              <div class="form-group">
                <label>{{ t('digitalCertificates.certificateTitle') }}</label>
                <input v-model="form.title" required class="input-field" />
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>{{ t('digitalCertificates.recipientName') }}</label>
                  <input v-model="form.recipient_name" required class="input-field" />
                </div>
                <div class="form-group">
                  <label>{{ t('digitalCertificates.recipientEmail') }}</label>
                  <input v-model="form.recipient_email" type="email" class="input-field" />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>{{ t('digitalCertificates.awardTitle') }}</label>
                  <input v-model="form.award_title" class="input-field" :placeholder="t('digitalCertificates.awardPlaceholder')" />
                </div>
                <div class="form-group">
                  <label>{{ t('businessCards.company') }}</label>
                  <input v-model="form.issuer_name" class="input-field" />
                </div>
              </div>
              <div class="form-group">
                <label>{{ t('common.description') }}</label>
                <textarea v-model="form.description" class="input-field" rows="3" />
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>{{ t('digitalCertificates.completionDate') }}</label>
                  <input v-model="form.completion_date" type="date" class="input-field" />
                </div>
                <div class="form-group">
                  <label>{{ t('digitalCertificates.issueDate') }}</label>
                  <input v-model="form.issue_date" type="date" class="input-field" />
                </div>
              </div>
              <ImageAssetField v-model="form.instructor_signature_path" :label="t('digitalCertificates.instructorSignature')" folder="signatures" />
              <ImageAssetField v-model="form.organization_signature_path" :label="t('digitalCertificates.orgSignature')" folder="signatures" />
            </div>

            <div v-show="editorTab === 'appearance'" class="tab-panel">
              <ImageAssetField v-model="form.logo_path" :label="t('digitalCertificates.orgLogo')" folder="logos" ai-context="certificate-logo" />
              <ImageAssetField v-model="form.seal_path" :label="t('digitalCertificates.companySeal')" folder="seals" ai-context="certificate-seal" />
              <ImageAssetField v-model="form.background_image_path" :label="t('common.background')" folder="backgrounds" ai-context="certificate-background" />
              <div class="form-group">
                <label>{{ t('digitalCertificates.fontFamily') }}</label>
                <select v-model="form.settings.font_family" class="input-field">
                  <option v-for="font in certificateFonts" :key="font.id" :value="font.id">{{ font.label }}</option>
                </select>
              </div>
              <div class="color-row">
                <div class="form-group">
                  <label>{{ t('digitalCertificates.textColor') }}</label>
                  <input v-model="form.settings.text_color" type="color" class="color-input" />
                </div>
                <div class="form-group">
                  <label>{{ t('digitalCertificates.backgroundColor') }}</label>
                  <input v-model="form.settings.background_color" type="color" class="color-input" />
                </div>
                <div class="form-group">
                  <label>{{ t('digitalCertificates.accentColor') }}</label>
                  <input v-model="form.theme_color" type="color" class="color-input" />
                </div>
              </div>
            </div>

            <div v-show="editorTab === 'qr'" class="tab-panel">
              <div class="template-section">
                <div class="section-title">{{ t('digitalCertificates.tabs.qrDesign') }}</div>
                <QrStyleFields v-model:qr-shape="form.qr_shape" v-model:dot-style="form.dot_style" v-model:corner-style="form.corner_style" v-model:frame-style="form.frame_style" />
              </div>
            </div>

            <p v-if="error" class="error-text">{{ error }}</p>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeEditor">{{ t('common.cancel') }}</button>
              <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <QrPreview
            v-if="editorTab === 'qr' && previewVerifyUrl"
            minimal
            :content="previewVerifyUrl"
            :foreground="form.theme_color"
            :logo-url="form.logo_path"
            :size="220"
            :qr-shape="form.qr_shape"
            :dot-style="form.dot_style"
            :corner-style="form.corner_style"
            :frame-style="form.frame_style"
          />
          <CertificatePreview v-else v-bind="previewProps" :certificate-url="previewCertUrl" />
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!certificates.length" class="empty-state">
        <div class="empty-icon">📜</div>
        <h3>{{ t('digitalCertificates.emptyTitle') }}</h3>
        <p>{{ t('digitalCertificates.emptyDesc') }}</p>
        <button type="button" class="btn-primary" @click="openCreate">{{ t('digitalCertificates.emptyCta') }}</button>
      </div>
      <div v-else class="cert-grid">
        <div v-for="cert in certificates" :key="cert.id" class="cert-item" :class="{ draft: !cert.is_active, revoked: cert.status === 'revoked' }">
          <CertificatePreview v-bind="certPreviewProps(cert)" :certificate-url="cert.certificate_url" />
          <div class="cert-item__meta">
            <code>{{ cert.certificate_id }}</code>
            <PublishToggle :model-value="!!cert.is_active" :loading="togglingId === cert.id" :active-label="t('publish.published')" :inactive-label="t('publish.draft')" @update:model-value="togglePublish(cert)" />
          </div>
          <div class="cert-item__actions">
            <CopyButton :text="cert.verify_url" :label="t('digitalCertificates.copyVerify')" />
            <button type="button" class="action-btn" @click="downloadPdf(cert)">{{ t('digitalCertificates.pdf') }}</button>
            <button type="button" class="action-btn" @click="openEdit(cert)">{{ t('common.edit') }}</button>
            <button v-if="cert.status !== 'revoked'" type="button" class="action-btn" @click="revokeCert(cert)">{{ t('digitalCertificates.revoke') }}</button>
            <button type="button" class="action-btn" @click="showAnalytics(cert)">{{ t('common.stats') }}</button>
            <button type="button" class="action-btn danger" @click="deleteCert(cert)">{{ t('common.delete') }}</button>
          </div>
        </div>
      </div>
    </template>

    <div v-if="showBulk" class="drawer-overlay" @click.self="showBulk = false">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('digitalCertificates.bulkImport') }}</h3>
          <button type="button" class="btn-ghost" @click="showBulk = false">✕</button>
        </div>
        <p class="bulk-hint">{{ t('digitalCertificates.bulkHint') }}</p>
        <textarea v-model="csvText" class="input-field bulk-csv" rows="8" :placeholder="t('digitalCertificates.csvPlaceholder')" />
        <p v-if="bulkError" class="error-text">{{ bulkError }}</p>
        <button type="button" class="btn-primary w-full" :disabled="bulkSaving" @click="runBulkImport">{{ bulkSaving ? t('common.saving') : t('digitalCertificates.importCsv') }}</button>
      </div>
    </div>

    <div v-if="analyticsCert" class="drawer-overlay" @click.self="analyticsCert = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsCert.title }) }}</h3>
          <button type="button" class="btn-ghost" @click="analyticsCert = null">✕</button>
        </div>
        <AnalyticsPanel type="digital-certificates" :id="analyticsCert.id" />
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
import CertificatePreview from '../../components/previews/CertificatePreview.vue'
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
import { CERTIFICATE_TEMPLATES, CERTIFICATE_FONTS, defaultCertificateForm, defaultCertificateSettings } from '../../utils/digitalModules'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()

const certificates = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const togglingId = ref(null)
const analyticsCert = ref(null)
const showBulk = ref(false)
const csvText = ref('')
const bulkSaving = ref(false)
const bulkError = ref('')
const editorTab = ref('content')

const certificateTemplates = computed(() => translateList(CERTIFICATE_TEMPLATES, t))
const certificateFonts = computed(() => CERTIFICATE_FONTS.map((font) => ({
  id: font.id,
  label: t(font.labelKey),
})))
const form = ref(defaultCertificateForm())

const certHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const previewCertUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/certificate/${form.value.slug}` : ''
})

const previewVerifyUrl = computed(() => {
  if (editId.value) {
    const c = certificates.value.find(x => x.id === editId.value)
    return c?.verify_url || ''
  }
  return `${domains.baseUrlFor(form.value.custom_domain_id)}/verify/CERT-...`
})

const previewProps = computed(() => ({
  title: form.value.title,
  template: form.value.template,
  recipientName: form.value.recipient_name,
  awardTitle: form.value.award_title,
  issuerName: form.value.issuer_name,
  certificateId: editId.value ? certificates.value.find(c => c.id === editId.value)?.certificate_id : 'CERT-2026-000001',
  description: form.value.description,
  completionDate: form.value.completion_date,
  issueDate: form.value.issue_date,
  settings: form.value.settings,
  themeColor: form.value.theme_color,
  logo: form.value.logo_path,
  seal: form.value.seal_path,
  instructorSignature: form.value.instructor_signature_path,
  organizationSignature: form.value.organization_signature_path,
  backgroundImage: form.value.background_image_path,
}))

function certPreviewProps(cert) {
  return {
    title: cert.title,
    template: cert.template,
    recipientName: cert.recipient_name,
    awardTitle: cert.award_title,
    issuerName: cert.issuer_name,
    certificateId: cert.certificate_id,
    description: cert.description,
    completionDate: cert.completion_date,
    issueDate: cert.issue_date,
    settings: cert.settings || {},
    themeColor: cert.theme_color,
    logo: cert.logo_path,
    seal: cert.seal_path,
    instructorSignature: cert.instructor_signature_path,
    organizationSignature: cert.organization_signature_path,
    backgroundImage: cert.background_image_path,
  }
}

function openCreate() {
  editId.value = null
  editorTab.value = 'content'
  form.value = defaultCertificateForm()
  form.value.settings = defaultCertificateSettings()
  editing.value = true
  error.value = ''
}

function openEdit(cert) {
  editId.value = cert.id
  editorTab.value = 'content'
  form.value = {
    ...cert,
    settings: { ...defaultCertificateSettings(), ...(cert.settings || {}) },
    theme_color: cert.theme_color || '#1a1333',
  }
  editing.value = true
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

async function load() {
  const { data } = await api.get('/digital-certificates')
  certificates.value = data
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    delete payload.is_active
    delete payload.certificate_url
    delete payload.verify_url
    delete payload.pdf_path
    delete payload.certificate_id
    delete payload.view_count
    if (editId.value) await api.put(`/digital-certificates/${editId.value}`, payload)
    else await api.post('/digital-certificates', { ...payload, is_active: true })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

function parseCsv(text) {
  return text.trim().split('\n').map(line => {
    const [recipient_name, recipient_email, award_title, completion_date] = line.split(',').map(s => s.trim())
    return { recipient_name, recipient_email: recipient_email || null, award_title: award_title || null, completion_date: completion_date || null }
  }).filter(r => r.recipient_name && r.recipient_name.toLowerCase() !== 'recipient_name')
}

async function runBulkImport() {
  bulkSaving.value = true
  bulkError.value = ''
  try {
    const recipients = parseCsv(csvText.value)
    if (!recipients.length) throw new Error(t('digitalCertificates.csvEmpty'))
    const template = { ...(editing.value ? form.value : defaultCertificateForm()) }
    delete template.slug
    const { data } = await api.post('/digital-certificates/bulk-import', { template, recipients })
    showBulk.value = false
    csvText.value = ''
    await load()
    dialog.alert({ title: t('common.notice'), message: t('digitalCertificates.bulkSuccess', { count: data.created }) })
  } catch (e) {
    bulkError.value = e.response?.data?.message || e.message || t('errors.failedToSave')
  } finally {
    bulkSaving.value = false
  }
}

async function togglePublish(cert) {
  togglingId.value = cert.id
  try {
    const { data } = await api.patch(`/digital-certificates/${cert.id}/publish`)
    const idx = certificates.value.findIndex(c => c.id === cert.id)
    if (idx !== -1) certificates.value[idx] = data
  } finally {
    togglingId.value = null
  }
}

async function revokeCert(cert) {
  const ok = await dialog.confirm({ title: t('digitalCertificates.revokeTitle'), message: t('digitalCertificates.revokeMessage', { id: cert.certificate_id }), confirmText: t('digitalCertificates.revoke'), variant: 'danger' })
  if (!ok) return
  await api.patch(`/digital-certificates/${cert.id}/revoke`)
  await load()
}

async function downloadPdf(cert) {
  try {
    const { data } = await api.get(`/digital-certificates/${cert.id}/download-pdf`, { responseType: 'blob' })
    const url = URL.createObjectURL(data)
    const a = document.createElement('a')
    a.href = url
    a.download = `${cert.certificate_id}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('digitalCertificates.pdfFailed'), variant: 'danger' })
  }
}

async function deleteCert(cert) {
  const ok = await dialog.confirm({ title: t('digitalCertificates.deleteTitle'), message: t('digitalCertificates.deleteMessage', { title: cert.title }), confirmText: t('common.delete'), variant: 'danger' })
  if (!ok) return
  await api.delete(`/digital-certificates/${cert.id}`)
  await load()
}

function showAnalytics(cert) { analyticsCert.value = cert }

onMounted(async () => {
  domains.fetch()
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; }
.header-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.page-title { font-size: 1.5rem; font-weight: 700; }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; }
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--text-muted); }
.template-section { background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; }
.section-title { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.75rem; color: var(--text-secondary); }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; border: 1px solid var(--border); }
.color-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; }
@media (max-width: 640px) { .color-row { grid-template-columns: 1fr; } }
.form-actions { display: flex; gap: 0.75rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.cert-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.cert-item { display: flex; flex-direction: column; gap: 0.5rem; }
.cert-item.draft { opacity: 0.88; }
.cert-item.revoked { opacity: 0.75; }
.cert-item__meta { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; font-size: 0.75rem; }
.cert-item__meta code { color: var(--brand); }
.cert-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.action-btn { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle); cursor: pointer; }
.action-btn.danger:hover { color: #ef4444; }
.drawer-overlay { position: fixed; inset: 0; background: rgba(26,19,51,0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.bulk-hint { font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.75rem; }
.bulk-csv { font-family: monospace; font-size: 0.8125rem; }
.w-full { width: 100%; }
.share-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.5rem; }
.mt-4 { margin-top: 1rem; }
</style>
