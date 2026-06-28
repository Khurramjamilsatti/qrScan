<template>
  <button
    type="button"
    class="qr-thumb"
    :title="expanded ? 'Close QR' : 'View QR code'"
    @click="expanded = !expanded"
  >
    <span class="qr-thumb__frame" :style="frameStyle">
      <img v-if="dataUrl" :src="dataUrl" alt="" class="qr-thumb__img" />
      <span v-else class="qr-thumb__icon">▦</span>
    </span>
    <span class="qr-thumb__hint">QR</span>
  </button>

  <Teleport to="body">
    <div v-if="expanded" class="qr-lightbox" @click="expanded = false">
      <div class="qr-lightbox__panel" @click.stop>
        <button type="button" class="qr-lightbox__close" @click="expanded = false">✕</button>
        <p class="qr-lightbox__title">Scan to open link</p>
        <div class="qr-lightbox__frame" :style="frameStyle">
          <img v-if="dataUrl" :src="dataUrl" alt="QR code" class="qr-lightbox__img" />
        </div>
        <p v-if="content" class="qr-lightbox__url">{{ content }}</p>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useQrPreview } from '../../composables/useQrPreview'

const props = defineProps({
  content: { type: String, default: '' },
  foreground: { type: String, default: '#1a1333' },
  background: { type: String, default: '#ffffff' },
  logoUrl: String,
  backgroundImage: String,
  size: { type: Number, default: 64 },
  margin: { type: Number, default: 2 },
  errorCorrection: { type: String, default: 'M' },
  qrShape: { type: String, default: 'square' },
  dotStyle: { type: String, default: 'square' },
  cornerStyle: { type: String, default: 'sharp' },
  frameStyle: { type: String, default: 'none' },
  scanOptimized: { type: Boolean, default: true },
})

const dataUrl = ref(null)
const expanded = ref(false)
const { generateDataUrl } = useQrPreview()

const frameStyle = computed(() => {
  if (props.backgroundImage) return {}
  return { background: props.background }
})

async function render() {
  if (!props.content) {
    dataUrl.value = null
    return
  }
  dataUrl.value = await generateDataUrl(props.content, {
    foreground: props.foreground,
    background: props.background,
    size: Math.max(props.size * 5, 480),
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
.qr-thumb {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  padding: 0;
  border: none;
  background: none;
  cursor: pointer;
  flex-shrink: 0;
}
.qr-thumb__frame {
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 0.625rem;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid transparent;
  background:
    linear-gradient(var(--surface), var(--surface)) padding-box,
    linear-gradient(135deg, var(--purple), var(--brand)) border-box;
  box-shadow: var(--shadow-sm);
  transition: transform 0.15s, box-shadow 0.15s;
}
.qr-thumb:hover .qr-thumb__frame {
  transform: scale(1.05);
  box-shadow: var(--shadow-md);
}
.qr-thumb__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 0.25rem;
  display: block;
}
.qr-thumb__icon {
  font-size: 1.125rem;
  color: var(--text-muted);
  line-height: 1;
}
.qr-thumb__hint {
  font-size: 0.5625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--purple);
}

.qr-lightbox {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(26, 19, 51, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  backdrop-filter: blur(4px);
}
.qr-lightbox__panel {
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  padding: 1.5rem;
  max-width: 20rem;
  width: 100%;
  text-align: center;
  box-shadow: var(--shadow-lg);
}
.qr-lightbox__close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  border: none;
  background: var(--bg-subtle);
  color: var(--text-secondary);
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.75rem;
}
.qr-lightbox__title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
  margin-bottom: 1rem;
}
.qr-lightbox__frame {
  border-radius: 1rem;
  padding: 1rem;
  display: inline-flex;
  margin: 0 auto;
}
.qr-lightbox__img {
  width: 16rem;
  height: 16rem;
  object-fit: contain;
  display: block;
  image-rendering: pixelated;
}
.qr-lightbox__url {
  margin-top: 1rem;
  font-size: 0.75rem;
  font-family: monospace;
  color: var(--brand);
  word-break: break-all;
  line-height: 1.4;
}
</style>
