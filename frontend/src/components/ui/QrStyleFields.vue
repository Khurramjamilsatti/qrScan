<template>
  <div class="qr-style-fields">
    <div v-for="group in groups" :key="group.key" class="style-group">
      <label class="style-label">{{ group.label }}</label>
      <div class="style-options">
        <button
          v-for="opt in group.options"
          :key="opt.value"
          type="button"
          class="style-pill"
          :class="{ active: models[group.key].value === opt.value }"
          @click="models[group.key].value = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
  translatedQrShapeOptions,
  translatedDotStyleOptions,
  translatedCornerStyleOptions,
  translatedFrameStyleOptions,
} from '../../utils/qrStyleOptions'

const { t } = useI18n()

const qrShape = defineModel('qrShape', { type: String, default: 'square' })
const dotStyle = defineModel('dotStyle', { type: String, default: 'square' })
const cornerStyle = defineModel('cornerStyle', { type: String, default: 'sharp' })
const frameStyle = defineModel('frameStyle', { type: String, default: 'none' })

const models = {
  qrShape,
  dotStyle,
  cornerStyle,
  frameStyle,
}

const groups = computed(() => [
  { key: 'qrShape', label: t('qrStyle.qrShape'), options: translatedQrShapeOptions(t) },
  { key: 'dotStyle', label: t('qrStyle.dotStyle'), options: translatedDotStyleOptions(t) },
  { key: 'cornerStyle', label: t('qrStyle.cornerStyle'), options: translatedCornerStyleOptions(t) },
  { key: 'frameStyle', label: t('qrStyle.frameStyle'), options: translatedFrameStyleOptions(t) },
])
</script>

<style scoped>
.qr-style-fields {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
}
.style-group { display: flex; flex-direction: column; gap: 0.375rem; }
.style-label {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-secondary);
}
.style-options { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.style-pill {
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 0.3rem 0.625rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
}
.style-pill:hover {
  border-color: var(--brand);
  color: var(--brand);
}
.style-pill.active {
  background: var(--brand-muted);
  border-color: var(--brand);
  color: var(--brand);
}
</style>
