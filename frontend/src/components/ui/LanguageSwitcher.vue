<template>
  <div class="lang-switcher" :class="{ compact }">
    <button
      v-for="loc in locales"
      :key="loc.code"
      type="button"
      class="lang-btn"
      :class="{ active: current === loc.code }"
      :aria-label="loc.label"
      @click="setLocale(loc.code)"
    >
      {{ loc.native }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { i18n, setAppLocale } from '../../i18n'

defineProps({
  compact: { type: Boolean, default: false },
})

const locales = [
  { code: 'en', label: 'English', native: 'English' },
  { code: 'ar', label: 'Arabic', native: 'العربية' },
]

const current = computed(() => i18n.global.locale.value)

function setLocale(code) {
  setAppLocale(code)
}
</script>

<style scoped>
.lang-switcher {
  display: inline-flex;
  align-items: center;
  gap: 0.125rem;
  padding: 0.125rem;
  border-radius: 0.5rem;
  background: var(--bg-subtle, rgba(255,255,255,0.08));
  border: 1px solid var(--border, rgba(255,255,255,0.12));
}
.lang-btn {
  min-width: 2rem;
  padding: 0.25rem 0.625rem;
  border: none;
  border-radius: 0.375rem;
  background: transparent;
  color: var(--text-secondary, var(--sidebar-muted));
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}
.lang-btn:hover { color: var(--text-primary, var(--sidebar-text)); }
.lang-btn.active {
  background: var(--brand, #e8655a);
  color: #fff;
}
.lang-switcher.compact .lang-btn {
  min-width: 1.75rem;
  padding: 0.2rem 0.5rem;
}
</style>
