<template>
  <div class="ai-insights">
    <div class="ai-header">
      <h4>{{ t('smartQr.insights.title') }}</h4>
      <span v-if="data?.score != null" class="score-badge" :class="scoreClass">{{ data.score }} — {{ data.score_label }}</span>
    </div>

    <div v-if="loading" class="text-muted text-sm py-2">{{ t('smartQr.insights.loading') }}</div>
    <div v-else-if="!data" class="text-muted text-sm py-2">{{ t('smartQr.insights.unavailable') }}</div>
    <template v-else>
      <p class="summary">{{ data.summary }}</p>

      <div v-if="data.highlights" class="highlights">
        <span v-if="data.highlights.top_country">{{ t('smartQr.insights.topCountry') }}: {{ data.highlights.top_country }}</span>
        <span v-if="data.highlights.trend">{{ t('smartQr.insights.trend') }}: {{ data.highlights.trend }}</span>
      </div>

      <div v-if="data.recommendations?.length" class="recs">
        <div class="recs-title">{{ t('smartQr.insights.recommendations') }}</div>
        <div v-for="(rec, i) in data.recommendations" :key="i" class="rec-card">
          <div class="rec-type">{{ rec.type }}</div>
          <div class="rec-title">{{ rec.title }}</div>
          <p class="rec-detail">{{ rec.detail }}</p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'

const props = defineProps({ type: String, id: Number })
const { t } = useI18n()
const data = ref(null)
const loading = ref(false)

const scoreClass = computed(() => {
  const s = data.value?.score ?? 0
  if (s >= 70) return 'good'
  if (s >= 40) return 'mid'
  return 'low'
})

async function load() {
  if (!props.id) return
  loading.value = true
  try {
    const { data: res } = await api.get(`/analytics/${props.type}/${props.id}/insights`)
    data.value = res
  } catch {
    data.value = null
  } finally {
    loading.value = false
  }
}

watch(() => props.id, load, { immediate: true })
</script>

<style scoped>
.ai-insights { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border); }
.ai-header { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.ai-header h4 { font-weight: 700; font-size: 0.9375rem; }
.score-badge { font-size: 0.6875rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 9999px; }
.score-badge.good { background: #dcfce7; color: #166534; }
.score-badge.mid { background: #fef9c3; color: #854d0e; }
.score-badge.low { background: #fee2e2; color: #991b1b; }
.summary { font-size: 0.8125rem; color: var(--text-secondary); line-height: 1.5; }
.highlights { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.625rem; font-size: 0.75rem; color: var(--text-muted); }
.recs { margin-top: 0.875rem; }
.recs-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 0.5rem; }
.rec-card { background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.625rem; margin-bottom: 0.5rem; }
.rec-type { font-size: 0.625rem; text-transform: uppercase; color: var(--purple); font-weight: 700; }
.rec-title { font-weight: 700; font-size: 0.8125rem; margin-top: 0.125rem; }
.rec-detail { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
</style>
