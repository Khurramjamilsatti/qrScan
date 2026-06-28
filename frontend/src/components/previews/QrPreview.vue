<template>
  <div class="qr-preview-card">
    <div class="qr-frame" :class="{ 'has-bg-image': backgroundImage }" :style="frameStyle">
      <img v-if="dataUrl" :src="dataUrl" :alt="t('previews.qrPreviewAlt')" class="qr-image" />
      <div v-else class="qr-placeholder">
        <svg class="qr-placeholder__icon" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z"/></svg>
        <p>{{ t('previews.enterUrlToPreview') }}</p>
      </div>
    </div>
    <div v-if="name" class="qr-meta">
      <div class="qr-meta__name">{{ name }}</div>
      <div v-if="scanUrl" class="qr-meta__scan">{{ scanUrl }}</div>
      <div v-if="domainLabel" class="qr-meta__domain">{{ domainLabel }}</div>
    </div>
    <div v-if="destination" class="qr-dest">
      <span class="qr-dest__label">{{ t('previews.redirectsTo') }}</span>
      <div class="qr-dest__url">{{ destination }}</div>
    </div>
    <div class="qr-badges">
      <span class="badge">{{ t('common.sizePx', { size }) }}</span>
      <span class="badge">EC: {{ errorCorrection }}</span>
      <span v-if="logoUrl" class="badge badge-accent">{{ t('common.logo') }}</span>
      <span v-if="backgroundImage" class="badge badge-accent">{{ t('qrCodes.backgroundImage') }}</span>
      <span v-if="isActive !== undefined" class="badge" :class="isActive ? 'badge-active' : 'badge-paused'">
        {{ isActive ? t('common.active') : t('common.paused') }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQrPreview } from '../../composables/useQrPreview'

const { t } = useI18n()

const props = defineProps({
  content: { type: String, default: '' },
  name: String,
  destination: String,
  scanUrl: String,
  domainLabel: String,
  foreground: { type: String, default: '#000000' },
  background: { type: String, default: '#ffffff' },
  logoUrl: String,
  backgroundImage: String,
  size: { type: Number, default: 280 },
  margin: { type: Number, default: 2 },
  errorCorrection: { type: String, default: 'M' },
  qrShape: { type: String, default: 'square' },
  dotStyle: { type: String, default: 'square' },
  cornerStyle: { type: String, default: 'sharp' },
  frameStyle: { type: String, default: 'none' },
  scanOptimized: { type: Boolean, default: false },
  isActive: { type: Boolean, default: undefined },
})

const dataUrl = ref(null)
const { generateDataUrl } = useQrPreview()

const frameStyle = computed(() => {
  if (props.backgroundImage) return {}
  return { background: props.background }
})

async function render() {
  dataUrl.value = await generateDataUrl(props.content || 'https://qrscan.digital', {
    foreground: props.foreground,
    background: props.background,
    size: props.size,
    margin: props.margin,
    errorCorrectionLevel: props.logoUrl ? 'H' : props.errorCorrection,
    logoUrl: props.logoUrl,
    backgroundImageUrl: props.backgroundImage,
    qr_shape: props.qrShape,
    dot_style: props.dotStyle,
    corner_style: props.cornerStyle,
    frame_style: props.frameStyle,
    scanOptimized: props.scanOptimized,
  })
}

watch(
  () => [props.content, props.foreground, props.background, props.size, props.margin, props.errorCorrection, props.logoUrl, props.backgroundImage, props.qrShape, props.dotStyle, props.cornerStyle, props.frameStyle, props.scanOptimized],
  render,
  { immediate: true }
)
</script>

<style scoped>
.qr-preview-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}
.qr-frame {
  border-radius: 1rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  transition: background 0.3s;
  overflow: hidden;
}
.qr-image { width: 100%; max-width: 220px; border-radius: 0.5rem; }
.qr-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-muted);
  font-size: 0.875rem;
}
.qr-placeholder__icon { width: 3rem; height: 3rem; }
.qr-meta { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
.qr-meta__name { font-weight: 600; color: var(--text-primary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.qr-meta__scan { font-size: 0.75rem; color: var(--brand); font-family: monospace; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 0.25rem; }
.qr-meta__domain { font-size: 0.75rem; color: var(--purple); margin-top: 0.125rem; }
.qr-dest { margin-top: 0.75rem; }
.qr-dest__label { font-size: 0.75rem; color: var(--text-muted); }
.qr-dest__url { font-size: 0.875rem; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.qr-badges { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
.badge {
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  background: var(--bg-subtle);
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border: 1px solid var(--border);
}
.badge-active { background: var(--brand-muted); color: var(--brand); border-color: color-mix(in srgb, var(--brand) 30%, transparent); }
.badge-paused { background: var(--gold-muted); color: #92680a; border-color: color-mix(in srgb, var(--gold) 40%, transparent); }
.badge-accent { background: var(--purple-muted); color: var(--purple); border-color: color-mix(in srgb, var(--purple) 25%, transparent); }
</style>
