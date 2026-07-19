<template>
  <div class="routing-editor">
    <div class="routing-header">
      <div>
        <h4>{{ t('smartQr.routing.title') }}</h4>
        <p class="hint">{{ t('smartQr.routing.subtitle') }}</p>
      </div>
      <button type="button" class="btn-secondary text-sm" @click="addRule">{{ t('smartQr.routing.addRule') }}</button>
    </div>

    <div v-if="!rules.length" class="empty-hint">{{ t('smartQr.routing.empty') }}</div>

    <div v-for="(rule, idx) in rules" :key="idx" class="rule-card">
      <div class="rule-top">
        <label class="toggle-label">
          <input v-model="rule.enabled" type="checkbox" @change="emitUpdate" />
          {{ t('smartQr.routing.rule', { n: idx + 1 }) }}
        </label>
        <button type="button" class="btn-ghost text-xs danger" @click="removeRule(idx)">{{ t('common.delete') }}</button>
      </div>

      <div class="form-group">
        <label>{{ t('smartQr.routing.destination') }}</label>
        <input v-model="rule.destination_url" type="url" class="input-field" :placeholder="t('smartQr.routing.destinationPlaceholder')" @input="emitUpdate" />
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>{{ t('smartQr.routing.priority') }}</label>
          <input v-model.number="rule.priority" type="number" min="1" max="99" class="input-field" @input="emitUpdate" />
        </div>
        <div class="form-group">
          <label>{{ t('smartQr.routing.device') }}</label>
          <select v-model="deviceSelection[idx]" class="input-field" @change="updateDevice(idx)">
            <option value="">{{ t('smartQr.routing.any') }}</option>
            <option value="mobile">{{ t('smartQr.routing.mobile') }}</option>
            <option value="tablet">{{ t('smartQr.routing.tablet') }}</option>
            <option value="desktop">{{ t('smartQr.routing.desktop') }}</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>{{ t('smartQr.routing.country') }}</label>
          <input v-model="countryInput[idx]" class="input-field" :placeholder="t('smartQr.routing.countryPlaceholder')" @change="updateCountry(idx)" />
        </div>
        <div class="form-group">
          <label>{{ t('smartQr.routing.language') }}</label>
          <input v-model="langInput[idx]" class="input-field" :placeholder="t('smartQr.routing.languagePlaceholder')" @change="updateLang(idx)" />
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>{{ t('smartQr.routing.timeStart') }}</label>
          <input v-model="rule.conditions.time_start" type="time" class="input-field" @input="emitUpdate" />
        </div>
        <div class="form-group">
          <label>{{ t('smartQr.routing.timeEnd') }}</label>
          <input v-model="rule.conditions.time_end" type="time" class="input-field" @input="emitUpdate" />
        </div>
      </div>

      <div class="form-group">
        <label>{{ t('smartQr.routing.audience') }}</label>
        <select v-model="rule.conditions.audience" class="input-field" @change="emitUpdate">
          <option value="">{{ t('smartQr.routing.any') }}</option>
          <option value="new">{{ t('smartQr.routing.newVisitors') }}</option>
          <option value="returning">{{ t('smartQr.routing.returningVisitors') }}</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
})
const emit = defineEmits(['update:modelValue'])
const { t } = useI18n()

const rules = ref([])
const deviceSelection = ref([])
const countryInput = ref([])
const langInput = ref([])

function defaultRule() {
  return {
    enabled: true,
    priority: 10,
    destination_url: '',
    conditions: { device: [], country: [], language: [], time_start: '', time_end: '', audience: '', days: [] },
  }
}

function syncFromProps(val) {
  rules.value = (val || []).map(r => ({
    ...defaultRule(),
    ...r,
    conditions: { ...defaultRule().conditions, ...(r.conditions || {}) },
  }))
  deviceSelection.value = rules.value.map(r => (r.conditions.device || [])[0] || '')
  countryInput.value = rules.value.map(r => (r.conditions.country || []).join(', '))
  langInput.value = rules.value.map(r => (r.conditions.language || []).join(', '))
}

watch(() => props.modelValue, syncFromProps, { immediate: true, deep: true })

function emitUpdate() {
  emit('update:modelValue', rules.value.map(r => ({ ...r })))
}

function addRule() {
  rules.value.push(defaultRule())
  deviceSelection.value.push('')
  countryInput.value.push('')
  langInput.value.push('')
  emitUpdate()
}

function removeRule(idx) {
  rules.value.splice(idx, 1)
  deviceSelection.value.splice(idx, 1)
  countryInput.value.splice(idx, 1)
  langInput.value.splice(idx, 1)
  emitUpdate()
}

function updateDevice(idx) {
  const val = deviceSelection.value[idx]
  rules.value[idx].conditions.device = val ? [val] : []
  emitUpdate()
}

function updateCountry(idx) {
  const raw = countryInput.value[idx] || ''
  rules.value[idx].conditions.country = raw.split(',').map(s => s.trim().toUpperCase()).filter(Boolean)
  emitUpdate()
}

function updateLang(idx) {
  const raw = langInput.value[idx] || ''
  rules.value[idx].conditions.language = raw.split(',').map(s => s.trim().toLowerCase()).filter(Boolean)
  emitUpdate()
}
</script>

<style scoped>
.routing-editor { border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; background: var(--bg-subtle); }
.routing-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 0.75rem; }
.routing-header h4 { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); }
.hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.125rem; }
.empty-hint { font-size: 0.8125rem; color: var(--text-muted); padding: 0.5rem 0; }
.rule-card { background: var(--surface); border: 1px solid var(--border); border-radius: 0.625rem; padding: 0.875rem; margin-top: 0.625rem; }
.rule-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.625rem; }
.toggle-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 600; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.625rem; }
.btn-ghost.danger { color: #ef4444; }
</style>
