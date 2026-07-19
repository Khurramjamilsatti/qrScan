<template>
  <div class="module-preview">
    <BadgeRenderer
      :title="title"
      :template="template"
      :recipient-name="recipientName"
      :issuer-name="issuerName"
      :badge-id="badgeId"
      :description="description"
      :skills="skills"
      :issue-date="issueDate"
      :expiry-date="expiryDate"
      :verify-url="verifyUrl"
      :settings="settings"
      :theme-color="themeColor"
      :logo="logo"
      :background-image="backgroundImage"
      :badge-image="badgeImage"
    />
    <div v-if="badgeUrl && !compact" class="module-preview__url">
      <span class="module-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="module-preview__link">{{ badgeUrl }}</span>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import BadgeRenderer from '../badges/BadgeRenderer.vue'

const { t } = useI18n()

defineProps({
  title: String,
  template: { type: String, default: 'classic' },
  recipientName: String,
  issuerName: String,
  badgeId: String,
  description: String,
  skills: { type: Array, default: () => [] },
  issueDate: String,
  expiryDate: String,
  verifyUrl: String,
  settings: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
  badgeImage: String,
  badgeUrl: String,
  compact: { type: Boolean, default: false },
})
</script>

<style scoped>
.module-preview__url { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
.module-preview__label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.module-preview__link { display: block; font-size: 0.75rem; font-family: monospace; color: var(--brand); margin-top: 0.25rem; word-break: break-all; }
</style>
