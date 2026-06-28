<template>
  <router-link to="/" class="brand-link">
    <span class="brand-icon">▦</span>
    <span>{{ brandText }}</span>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  logoText: { type: String, default: '' },
})

const { t } = useI18n()
const brandText = computed(() => props.logoText || t('common.brand'))
</script>

<style scoped>
.brand-link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  color: var(--text-primary);
  text-decoration: none;
}
.brand-icon { color: var(--brand); }
</style>
