<template>
  <div class="cert-preview">
    <div v-if="certificateUrl" class="cert-preview__url">
      <span class="url-label">{{ t('digitalCertificates.publicUrl') }}</span>
      <code>{{ certificateUrl }}</code>
    </div>
    <CertificateRenderer
      :title="title"
      :template="template"
      :recipient-name="recipientName"
      :award-title="awardTitle"
      :issuer-name="issuerName"
      :certificate-id="certificateId"
      :description="description"
      :completion-date="completionDate"
      :issue-date="issueDate"
      :settings="settings"
      :theme-color="themeColor"
      :logo="logo"
      :seal="seal"
      :instructor-signature="instructorSignature"
      :organization-signature="organizationSignature"
      :background-image="backgroundImage"
    />
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import CertificateRenderer from '../certificates/CertificateRenderer.vue'

defineProps({
  title: String,
  template: String,
  recipientName: String,
  awardTitle: String,
  issuerName: String,
  certificateId: String,
  description: String,
  completionDate: String,
  issueDate: String,
  settings: Object,
  themeColor: String,
  logo: String,
  seal: String,
  instructorSignature: String,
  organizationSignature: String,
  backgroundImage: String,
  certificateUrl: String,
})

const { t } = useI18n()
</script>

<style scoped>
.cert-preview__url { margin-bottom: 0.75rem; font-size: 0.75rem; }
.url-label { display: block; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem; }
.cert-preview__url code { word-break: break-all; color: var(--brand); }
</style>
