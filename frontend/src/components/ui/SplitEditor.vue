<template>
  <div class="split-editor" :style="previewColumnStyle">
    <div class="split-editor__form">
      <slot name="form" />
    </div>
    <div class="split-editor__preview">
      <div class="preview-panel">
        <div class="preview-label">
          <span class="preview-dot"></span>
          {{ previewLabel }}
        </div>
        <div class="preview-panel__body">
          <slot name="preview" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  previewMode: { type: String, default: 'content' },
  previewColumnWidth: { type: Number, default: 400 },
})

const { t } = useI18n()

const previewColumnStyle = computed(() => ({
  '--preview-width': `${props.previewColumnWidth}px`,
}))

const previewLabel = computed(() =>
  props.previewMode === 'qr' ? t('qrCodes.qrCodeStyle') : t('common.livePreview')
)
</script>

<style scoped>
.split-editor {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  align-items: start;
}
@media (min-width: 1024px) {
  .split-editor {
    grid-template-columns: minmax(0, 1fr) var(--preview-width, 400px);
    gap: 2rem;
    align-items: stretch;
  }
  .split-editor__form {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 10rem);
  }
  .split-editor__preview {
    position: sticky;
    top: 5rem;
    align-self: start;
    max-height: none;
    overflow: visible;
    min-width: 0;
  }
}

.split-editor__form :deep(.editor-form) {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  gap: 0;
}

.split-editor__form :deep(.editor-tabs) {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem;
  flex-shrink: 0;
  border-bottom: 1px solid var(--border);
  padding-bottom: 0.5rem;
  margin-bottom: 0;
  background: var(--surface);
  z-index: 2;
}

@media (min-width: 1024px) {
  .split-editor__form :deep(.editor-tabs) {
    position: sticky;
    top: 0;
  }
  .split-editor__form :deep(.editor-form__scroll) {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    padding: 1rem 0.5rem 3rem 0;
    scroll-padding-bottom: 2rem;
  }
  .split-editor__form :deep(.form-actions) {
    flex-shrink: 0;
    padding-top: 1rem;
    margin-top: 0.25rem;
    border-top: 1px solid var(--border);
    background: var(--surface);
  }
}

.split-editor__form :deep(.editor-tabs button) {
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
  border-radius: 0.5rem;
}
.split-editor__form :deep(.editor-tabs button.active) {
  color: var(--brand);
  background: var(--brand-muted);
}

.split-editor__form :deep(.tab-panel) {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.split-editor__form :deep(.form-actions) {
  display: flex;
  gap: 0.75rem;
}
.preview-panel {
  display: flex;
  flex-direction: column;
  max-height: inherit;
  min-height: 0;
  overflow: visible;
}
.preview-panel__body {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  min-width: 0;
  width: 100%;
  flex: 1;
  min-height: 0;
}
.preview-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-secondary);
  margin-bottom: 1rem;
  flex-shrink: 0;
}
.preview-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--brand);
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
