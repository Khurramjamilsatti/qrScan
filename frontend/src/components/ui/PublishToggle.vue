<template>
  <label class="publish-toggle" :class="{ active: modelValue, loading }">
    <span class="toggle-label">{{ modelValue ? activeLabelText : inactiveLabelText }}</span>
    <button
      type="button"
      role="switch"
      class="toggle-track"
      :aria-checked="modelValue"
      :disabled="loading"
      @click.prevent="$emit('update:modelValue', !modelValue)"
    >
      <span class="toggle-thumb"></span>
    </button>
  </label>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  loading: { type: Boolean, default: false },
  activeLabel: { type: String, default: undefined },
  inactiveLabel: { type: String, default: undefined },
})
defineEmits(['update:modelValue'])

const { t } = useI18n()

const activeLabelText = computed(() => props.activeLabel ?? t('publish.published'))
const inactiveLabelText = computed(() => props.inactiveLabel ?? t('publish.draft'))
</script>

<style scoped>
.publish-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}
.publish-toggle.loading { opacity: 0.6; pointer-events: none; }
.toggle-label {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  min-width: 4.5rem;
}
.publish-toggle.active .toggle-label { color: var(--brand); }
.publish-toggle:not(.active) .toggle-label { color: var(--purple); }
.toggle-track {
  position: relative;
  width: 2.5rem;
  height: 1.375rem;
  border-radius: 9999px;
  border: none;
  background: var(--border-strong);
  cursor: pointer;
  transition: background 0.25s;
  padding: 0;
  flex-shrink: 0;
}
.publish-toggle:not(.active) .toggle-track {
  background: color-mix(in srgb, var(--purple) 20%, var(--border-strong));
}
.publish-toggle.active .toggle-track {
  background: linear-gradient(135deg, var(--purple), var(--brand));
}
.toggle-thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  background: white;
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
  transition: transform 0.25s;
}
.publish-toggle.active .toggle-thumb {
  transform: translateX(1.125rem);
}
</style>
