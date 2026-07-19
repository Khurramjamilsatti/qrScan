<template>
  <div class="responses-panel">
    <div class="responses-header">
      <div>
        <h3>{{ t('forms.responses') }}</h3>
        <p class="responses-count">{{ t('forms.responseCount', { count: total }) }}</p>
      </div>
      <div class="responses-actions">
        <button type="button" class="btn-secondary text-sm" :disabled="!total" @click="$emit('export')">
          {{ t('forms.exportCsv') }}
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>

    <template v-else-if="total">
      <!-- Summary charts -->
      <div v-if="summary?.length" class="summary-section">
        <h4>{{ t('forms.summary') }}</h4>
        <div v-for="item in summary" :key="item.id" class="summary-card">
          <div class="summary-card__title">{{ item.title || t('forms.untitledQuestion') }}</div>
          <div class="summary-card__meta">{{ t('forms.answers', { count: item.total_responses }) }}</div>

          <div v-if="item.distribution" class="distribution">
            <div v-if="item.type === 'rating' && item.average" class="rating-avg">
              {{ t('forms.averageRating', { avg: item.average }) }}
            </div>
            <div v-for="(count, label) in item.distribution" :key="label" class="dist-row">
              <span class="dist-label">{{ label }}</span>
              <div class="dist-bar-wrap">
                <div class="dist-bar" :style="{ width: distPct(count, item.total_responses) + '%' }"></div>
              </div>
              <span class="dist-count">{{ count }}</span>
            </div>
          </div>

          <div v-else-if="item.recent?.length" class="recent-answers">
            <div v-for="(ans, i) in item.recent" :key="i" class="recent-answer">{{ ans }}</div>
          </div>
        </div>
      </div>

      <!-- Individual responses -->
      <div class="list-section">
        <h4>{{ t('forms.individualResponses') }}</h4>
        <div v-for="sub in submissions" :key="sub.id" class="response-card">
          <div class="response-card__header">
            <span class="response-date">{{ formatDate(sub.created_at) }}</span>
            <span v-if="sub.respondent_email" class="response-email">{{ sub.respondent_email }}</span>
            <button type="button" class="icon-btn danger" @click="$emit('delete', sub)">✕</button>
          </div>
          <div class="response-fields">
            <div v-for="field in fields" :key="field.id" class="response-field">
              <span class="response-field__label">{{ field.title }}</span>
              <span class="response-field__value">{{ formatValue(sub.data?.[field.id], field) }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="empty-responses">
      <div class="empty-icon">📋</div>
      <p>{{ t('forms.noResponses') }}</p>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import { isInputField } from '../../utils/formFieldTypes'

defineProps({
  submissions: { type: Array, default: () => [] },
  summary: { type: Array, default: () => [] },
  fields: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
})

defineEmits(['export', 'delete'])

const { t } = useI18n()

function distPct(count, total) {
  if (!total) return 0
  return Math.round((count / total) * 100)
}

function formatDate(value) {
  if (!value) return ''
  try {
    return new Date(value).toLocaleString()
  } catch {
    return value
  }
}

function formatValue(value, field) {
  if (value === null || value === undefined || value === '') return '—'
  if (field.type === 'rating') {
    const max = field.rating_max || 5
    const n = Number(value)
    if (!n) return '—'
    return `${'★'.repeat(n)}${'☆'.repeat(max - n)} (${n}/${max})`
  }
  if (field.type === 'grid_multiple_choice' && typeof value === 'object') {
    return Object.entries(value).map(([k, v]) => `${k}: ${v}`).join(', ')
  }
  if (Array.isArray(value)) return value.join(', ')
  return String(value)
}
</script>

<style scoped>
.responses-panel { display: flex; flex-direction: column; gap: 1.25rem; }
.responses-header { display: flex; justify-content: space-between; align-items: flex-start; }
.responses-header h3 { font-weight: 700; font-size: 1.125rem; }
.responses-count { font-size: 0.8125rem; color: var(--text-secondary); }
.responses-actions { display: flex; gap: 0.5rem; }
.summary-section h4, .list-section h4 { font-size: 0.875rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.summary-card { background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem; margin-bottom: 0.75rem; }
.summary-card__title { font-weight: 600; margin-bottom: 0.25rem; }
.summary-card__meta { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; }
.rating-avg { font-size: 0.875rem; font-weight: 600; color: #f59e0b; margin-bottom: 0.5rem; }
.distribution { display: flex; flex-direction: column; gap: 0.375rem; }
.dist-row { display: grid; grid-template-columns: 1fr 2fr auto; gap: 0.5rem; align-items: center; font-size: 0.8125rem; }
.dist-bar-wrap { height: 8px; background: var(--border); border-radius: 4px; overflow: hidden; }
.dist-bar { height: 100%; background: var(--brand); border-radius: 4px; }
.dist-count { font-weight: 600; color: var(--text-secondary); min-width: 1.5rem; text-align: end; }
.recent-answers { display: flex; flex-direction: column; gap: 0.25rem; }
.recent-answer { font-size: 0.8125rem; padding: 0.375rem 0.5rem; background: var(--surface); border-radius: 0.375rem; }
.response-card { border: 1px solid var(--border); border-radius: 0.75rem; margin-bottom: 0.75rem; overflow: hidden; }
.response-card__header { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 1rem; background: var(--bg-subtle); border-bottom: 1px solid var(--border); }
.response-date { font-size: 0.75rem; color: var(--text-secondary); }
.response-email { font-size: 0.75rem; color: var(--brand); margin-inline-start: auto; }
.icon-btn { border: none; background: none; cursor: pointer; font-size: 0.75rem; color: var(--text-muted); }
.icon-btn.danger:hover { color: #ef4444; }
.response-fields { padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.5rem; }
.response-field { display: grid; grid-template-columns: 1fr 2fr; gap: 0.5rem; font-size: 0.8125rem; }
.response-field__label { font-weight: 600; color: var(--text-secondary); }
.response-field__value { color: var(--text-primary); word-break: break-word; }
.empty-responses { text-align: center; padding: 2rem; color: var(--text-muted); }
.empty-icon { font-size: 2rem; margin-bottom: 0.5rem; }
</style>
