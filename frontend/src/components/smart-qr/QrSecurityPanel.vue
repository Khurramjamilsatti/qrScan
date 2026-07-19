<template>
  <div class="security-panel">
    <div class="panel-header">
      <h4>{{ t('smartQr.security.title') }}</h4>
      <p class="hint">{{ t('smartQr.security.subtitle') }}</p>
    </div>

    <label class="check-row">
      <input v-model="local.signed" type="checkbox" @change="emitUpdate" />
      <span>
        <strong>{{ t('smartQr.security.signed') }}</strong>
        <small>{{ t('smartQr.security.signedHint') }}</small>
      </span>
    </label>

    <label class="check-row">
      <input v-model="local.one_time_access" type="checkbox" @change="emitUpdate" />
      <span>
        <strong>{{ t('smartQr.security.oneTime') }}</strong>
        <small>{{ t('smartQr.security.oneTimeHint') }}</small>
      </span>
    </label>

    <label class="check-row">
      <input v-model="local.password_enabled" type="checkbox" @change="emitUpdate" />
      <span>
        <strong>{{ t('smartQr.security.password') }}</strong>
        <small>{{ t('smartQr.security.passwordHint') }}</small>
      </span>
    </label>

    <div v-if="local.password_enabled" class="form-group">
      <label>{{ t('smartQr.security.passwordValue') }}</label>
      <input v-model="local.password" type="password" class="input-field" :placeholder="t('smartQr.security.passwordPlaceholder')" @input="emitUpdate" />
      <p class="hint">{{ t('smartQr.security.passwordScanHint') }}</p>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>{{ t('smartQr.security.expiresAt') }}</label>
        <input v-model="expiresAt" type="datetime-local" class="input-field" @change="emitExpires" />
      </div>
      <div class="form-group">
        <label>{{ t('smartQr.security.maxScans') }}</label>
        <input v-model.number="maxScans" type="number" min="0" class="input-field" :placeholder="t('smartQr.security.unlimited')" @input="emitMaxScans" />
      </div>
    </div>

    <p v-if="signedUrl" class="signed-url">
      <span class="label">{{ t('smartQr.security.signedUrl') }}</span>
      <code>{{ signedUrl }}</code>
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  expiresAtValue: { type: String, default: '' },
  maxScansValue: { type: Number, default: 0 },
  signedUrl: { type: String, default: '' },
})
const emit = defineEmits(['update:modelValue', 'update:expiresAtValue', 'update:maxScansValue'])
const { t } = useI18n()

const local = ref({ signed: false, one_time_access: false, password_enabled: false, password: '' })
const expiresAt = ref('')
const maxScans = ref(0)

watch(() => props.modelValue, (v) => {
  local.value = {
    signed: !!v?.signed,
    one_time_access: !!v?.one_time_access,
    password_enabled: !!v?.password_enabled,
    password: v?.password || '',
  }
}, { immediate: true, deep: true })

watch(() => props.expiresAtValue, (v) => {
  if (!v) { expiresAt.value = ''; return }
  expiresAt.value = v.slice(0, 16)
}, { immediate: true })

watch(() => props.maxScansValue, (v) => { maxScans.value = v || 0 }, { immediate: true })

function emitUpdate() {
  emit('update:modelValue', { ...local.value })
}

function emitExpires() {
  emit('update:expiresAtValue', expiresAt.value ? new Date(expiresAt.value).toISOString() : null)
}

function emitMaxScans() {
  emit('update:maxScansValue', maxScans.value || 0)
}
</script>

<style scoped>
.security-panel { border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; background: var(--bg-subtle); }
.panel-header { margin-bottom: 0.75rem; }
.panel-header h4 { font-weight: 700; font-size: 0.9375rem; }
.hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.125rem; }
.check-row { display: flex; gap: 0.625rem; align-items: flex-start; margin-bottom: 0.625rem; font-size: 0.8125rem; }
.check-row strong { display: block; }
.check-row small { display: block; color: var(--text-muted); font-size: 0.6875rem; margin-top: 0.125rem; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.625rem; margin-top: 0.5rem; }
.signed-url { margin-top: 0.75rem; font-size: 0.75rem; }
.signed-url code { display: block; margin-top: 0.25rem; word-break: break-all; color: var(--brand); }
.signed-url .label { font-weight: 600; color: var(--text-secondary); }
</style>
