<template>
  <div class="module-preview">
    <ScanToWinRenderer
      :name="name"
      :description="description"
      :template="template"
      :starts-at="startsAt"
      :ends-at="endsAt"
      :max-plays-per-day="maxPlaysPerDay"
      :prizes="prizes"
      :terms="terms"
      :theme-color="themeColor"
      :logo="logo"
      :background-image="backgroundImage"
    />
    <div v-if="campaignUrl && !compact" class="module-preview__url">
      <span class="module-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="module-preview__link">{{ campaignUrl }}</span>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import ScanToWinRenderer from '../scan-to-win/ScanToWinRenderer.vue'

const { t } = useI18n()

defineProps({
  name: String,
  description: String,
  template: { type: String, default: 'instant' },
  startsAt: String,
  endsAt: String,
  maxPlaysPerDay: { type: Number, default: 1 },
  prizes: { type: Array, default: () => [] },
  terms: String,
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
  campaignUrl: String,
  compact: { type: Boolean, default: false },
})
</script>

<style scoped>
.module-preview__url { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
.module-preview__label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.module-preview__link { display: block; font-size: 0.75rem; font-family: monospace; color: var(--brand); margin-top: 0.25rem; word-break: break-all; }
</style>
