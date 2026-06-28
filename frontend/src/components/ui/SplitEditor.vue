<template>
  <div class="split-editor">
    <div class="split-editor__form">
      <slot name="form" />
    </div>
    <div class="split-editor__preview">
      <div class="preview-sticky">
        <div class="preview-label">
          <span class="preview-dot"></span>
          {{ t('common.livePreview') }}
        </div>
        <slot name="preview" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
.split-editor {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
}
@media (min-width: 1024px) {
  .split-editor {
    grid-template-columns: 1fr 380px;
    gap: 2rem;
    align-items: start;
  }
}
.preview-sticky {
  position: sticky;
  top: 5rem;
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
