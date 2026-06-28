<template>
  <Teleport to="body">
    <Transition name="dialog-fade">
      <div v-if="dialog.visible" class="dialog-overlay" @click.self="onCancel">
        <Transition name="dialog-scale" appear>
          <div
            v-if="dialog.visible"
            class="dialog-card"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="titleId"
          >
            <div class="dialog-icon" :class="`variant-${dialog.options.variant}`">
              <component :is="iconComponent" />
            </div>
            <h2 :id="titleId" class="dialog-title">{{ dialog.options.title }}</h2>
            <p v-if="dialog.options.message" class="dialog-message">{{ dialog.options.message }}</p>
            <div class="dialog-actions">
              <button
                v-if="dialog.options.type === 'confirm'"
                type="button"
                class="dialog-btn dialog-btn--ghost"
                @click="onCancel"
              >
                {{ dialog.options.cancelText }}
              </button>
              <button
                type="button"
                class="dialog-btn"
                :class="confirmBtnClass"
                @click="onConfirm"
              >
                {{ dialog.options.confirmText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, h, onMounted, onUnmounted, watch } from 'vue'
import { useDialogStore } from '../../stores/dialog'

const dialog = useDialogStore()
const titleId = 'app-dialog-title'

function onKeydown(e) {
  if (e.key === 'Escape' && dialog.visible) {
    dialog.cancelAction()
  }
}

watch(() => dialog.visible, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

onMounted(() => window.addEventListener('keydown', onKeydown))
onUnmounted(() => {
  window.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})

const IconInfo = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }),
  ]),
}
const IconWarning = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' }),
  ]),
}
const IconDanger = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' }),
  ]),
}
const IconSuccess = {
  render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }),
  ]),
}

const iconComponent = computed(() => ({
  info: IconInfo,
  warning: IconWarning,
  danger: IconDanger,
  success: IconSuccess,
  primary: IconInfo,
}[dialog.options.variant] || IconInfo))

const confirmBtnClass = computed(() => ({
  info: 'dialog-btn--brand',
  primary: 'dialog-btn--brand',
  warning: 'dialog-btn--gold',
  danger: 'dialog-btn--danger',
  success: 'dialog-btn--brand',
}[dialog.options.variant] || 'dialog-btn--brand'))

function onConfirm() {
  dialog.confirmAction()
}

function onCancel() {
  dialog.cancelAction()
}
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  background: rgba(26, 19, 51, 0.55);
  backdrop-filter: blur(6px);
}
.dialog-card {
  width: 100%;
  max-width: 22rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  padding: 1.75rem 1.5rem 1.5rem;
  text-align: center;
  box-shadow: var(--shadow-lg);
}
.dialog-icon {
  width: 3.25rem;
  height: 3.25rem;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}
.dialog-icon svg { width: 1.5rem; height: 1.5rem; }
.variant-info, .variant-primary {
  background: var(--brand-muted);
  color: var(--brand);
}
.variant-warning {
  background: var(--gold-muted);
  color: var(--gold);
}
.variant-danger {
  background: #fde8e6;
  color: #dc2626;
}
[data-theme="dark"] .variant-danger {
  background: rgba(220, 38, 38, 0.15);
  color: #f87171;
}
.variant-success {
  background: var(--teal-muted);
  color: var(--teal);
}
.dialog-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.5rem;
  line-height: 1.35;
}
.dialog-message {
  font-size: 0.875rem;
  color: var(--text-secondary);
  line-height: 1.6;
  margin: 0 0 1.5rem;
}
.dialog-actions {
  display: flex;
  gap: 0.625rem;
  justify-content: center;
}
.dialog-btn {
  flex: 1;
  max-width: 10rem;
  padding: 0.625rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}
.dialog-btn--ghost {
  background: var(--bg-subtle);
  color: var(--text-secondary);
  border: 1px solid var(--border);
}
.dialog-btn--ghost:hover {
  background: var(--surface-hover);
  color: var(--text-primary);
}
.dialog-btn--brand {
  background: var(--brand);
  color: white;
  box-shadow: 0 4px 14px var(--brand-glow);
}
.dialog-btn--brand:hover { background: var(--brand-hover); }
.dialog-btn--gold {
  background: var(--gold);
  color: #1a1333;
}
.dialog-btn--gold:hover { filter: brightness(1.05); }
.dialog-btn--danger {
  background: #dc2626;
  color: white;
  box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
}
.dialog-btn--danger:hover { background: #b91c1c; }

.dialog-fade-enter-active,
.dialog-fade-leave-active { transition: opacity 0.2s ease; }
.dialog-fade-enter-from,
.dialog-fade-leave-to { opacity: 0; }

.dialog-scale-enter-active { transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1); }
.dialog-scale-leave-active { transition: all 0.15s ease; }
.dialog-scale-enter-from { opacity: 0; transform: scale(0.92) translateY(8px); }
.dialog-scale-leave-to { opacity: 0; transform: scale(0.96); }
</style>
