<template>
  <div class="assistant-panel">
    <div class="assistant-header">
      <div>
        <h3>{{ t('smartQr.assistant.title') }}</h3>
        <p class="subtitle">{{ t('smartQr.assistant.subtitle') }}</p>
      </div>
      <span class="provider-badge" :class="status?.llm_configured ? 'ai' : 'rules'">
        {{ status?.llm_configured ? t('smartQr.assistant.aiPowered') : t('smartQr.assistant.rulesPowered') }}
      </span>
    </div>

    <div class="prompt-area">
      <textarea
        v-model="prompt"
        class="input-field prompt-input"
        rows="3"
        :placeholder="t('smartQr.assistant.placeholder')"
      />
      <button type="button" class="btn-primary" :disabled="loading || !prompt.trim()" @click="advise">
        {{ loading ? t('smartQr.assistant.thinking') : t('smartQr.assistant.ask') }}
      </button>
    </div>

    <div v-if="result" class="result-card">
      <p class="reply">{{ result.reply || result.summary }}</p>

      <div class="meta-grid">
        <div class="meta-item">
          <span class="meta-label">{{ t('smartQr.assistant.solution') }}</span>
          <span class="meta-value">{{ result.recommended_solution }}</span>
        </div>
        <div class="meta-item">
          <span class="meta-label">{{ t('smartQr.assistant.goal') }}</span>
          <span class="meta-value">{{ result.intent?.goal }}</span>
        </div>
      </div>

      <div v-if="result.next_steps?.length" class="steps">
        <div class="steps-title">{{ t('smartQr.assistant.nextSteps') }}</div>
        <ol>
          <li v-for="(step, i) in result.next_steps" :key="i">{{ step }}</li>
        </ol>
      </div>

      <div v-if="result.funnel_template" class="template-card">
        <div class="template-title">{{ t('smartQr.assistant.suggestedFunnel') }}: {{ result.funnel_template.name }}</div>
        <p class="template-desc">{{ result.funnel_template.description }}</p>
        <button type="button" class="btn-secondary text-sm" @click="$emit('apply-funnel', result.funnel_template)">
          {{ t('smartQr.assistant.useTemplate') }}
        </button>
      </div>

      <button type="button" class="btn-secondary text-sm mt-3" @click="$emit('apply-settings', result)">
        {{ t('smartQr.assistant.applyToQr') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'

defineEmits(['apply-funnel', 'apply-settings'])
const { t } = useI18n()

const prompt = ref('')
const loading = ref(false)
const result = ref(null)
const status = ref(null)

async function loadStatus() {
  try {
    const { data } = await api.get('/qr-assistant/status')
    status.value = data
  } catch {
    status.value = { llm_configured: false }
  }
}

async function advise() {
  loading.value = true
  result.value = null
  try {
    const { data } = await api.post('/qr-assistant/advise', { prompt: prompt.value })
    result.value = data
  } catch (e) {
    result.value = { reply: e.response?.data?.message || t('errors.failedToSave') }
  } finally {
    loading.value = false
  }
}

onMounted(loadStatus)
</script>

<style scoped>
.assistant-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 1.25rem; }
.assistant-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
.assistant-header h3 { font-weight: 700; font-size: 1.125rem; }
.subtitle { font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.25rem; }
.provider-badge { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; padding: 0.25rem 0.5rem; border-radius: 9999px; }
.provider-badge.ai { background: var(--purple-muted); color: var(--purple); }
.provider-badge.rules { background: var(--brand-muted); color: var(--brand); }
.prompt-area { display: flex; flex-direction: column; gap: 0.75rem; }
.prompt-input { resize: vertical; min-height: 5rem; }
.result-card { margin-top: 1rem; padding: 1rem; border-radius: 0.75rem; background: var(--bg-subtle); border: 1px solid var(--border); }
.reply { font-size: 0.9375rem; line-height: 1.5; color: var(--text-primary); }
.meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-top: 0.875rem; }
.meta-label { display: block; font-size: 0.6875rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; }
.meta-value { font-size: 0.8125rem; font-weight: 600; color: var(--brand); }
.steps { margin-top: 0.875rem; }
.steps-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 0.375rem; }
.steps ol { padding-left: 1.25rem; font-size: 0.8125rem; color: var(--text-secondary); }
.template-card { margin-top: 0.875rem; padding: 0.75rem; border-radius: 0.5rem; border: 1px dashed var(--border); }
.template-title { font-weight: 700; font-size: 0.8125rem; }
.template-desc { font-size: 0.75rem; color: var(--text-muted); margin: 0.25rem 0 0.5rem; }
.mt-3 { margin-top: 0.75rem; }
</style>
