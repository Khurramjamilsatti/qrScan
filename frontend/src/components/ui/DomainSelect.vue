<template>
  <div class="form-group">
    <label v-if="label">{{ label }}</label>
    <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value ? Number($event.target.value) : null)" class="input-field">
      <option :value="null">{{ t('common.defaultHost', { host: defaultHost }) }}</option>
      <option v-for="d in domains.verifiedDomains" :key="d.id" :value="d.id">
        {{ d.is_primary ? t('common.primaryDomain', { domain: d.domain }) : d.domain }}
      </option>
    </select>
    <p v-if="!domains.canUse" class="hint">
      <router-link to="/app/domains" class="text-brand-600">{{ t('common.upgradeToPro') }}</router-link>
      {{ upgradeHintSuffix }}
    </p>
    <p v-else-if="!domains.verifiedDomains.length" class="hint">
      <router-link to="/app/domains" class="text-brand-600">{{ t('common.addCustomDomain') }}</router-link>
    </p>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useDomainsStore } from '../../stores/domains'

defineProps({ modelValue: [Number, null], label: { type: String, default: 'Custom domain' } })
defineEmits(['update:modelValue'])

const { t } = useI18n()
const domains = useDomainsStore()
const defaultHost = computed(() => {
  try { return new URL(domains.defaultBaseUrl).host } catch { return 'localhost' }
})

const upgradeHintSuffix = computed(() => {
  const full = t('common.upgradeToProDomains')
  const prefix = t('common.upgradeToPro')
  return full.startsWith(prefix) ? full.slice(prefix.length) : ` — ${full}`
})

onMounted(() => { if (!domains.loaded) domains.fetch() })
</script>

<style scoped>
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: #475569; margin-bottom: 0.375rem; }
.hint { font-size: 0.75rem; color: #94a3b8; margin-top: 0.375rem; }
</style>
