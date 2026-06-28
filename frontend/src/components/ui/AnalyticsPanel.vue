<template>
  <div v-if="loading" class="text-muted text-sm py-4">{{ t('analytics.loading') }}</div>
  <div v-else-if="!data?.total" class="empty-activity">{{ t('analytics.noData') }}</div>
  <div v-else class="analytics-panel">
    <div class="stat-row">
      <div class="stat-box">
        <div class="stat-num">{{ data.total }}</div>
        <div class="stat-label">{{ t('analytics.totalEvents') }}</div>
      </div>
      <div class="stat-box">
        <div class="stat-num">{{ Object.keys(data.by_country || {}).length }}</div>
        <div class="stat-label">{{ t('analytics.countries') }}</div>
      </div>
    </div>
    <div v-if="Object.keys(data.by_day || {}).length" class="chart-section">
      <div class="chart-title">{{ t('analytics.activityLast7Days') }}</div>
      <div class="bar-chart">
        <div v-for="(count, day) in recentDays" :key="day" class="bar-col">
          <div class="bar" :style="{ height: barHeight(count) + '%' }"></div>
          <div class="bar-label">{{ formatDay(day) }}</div>
        </div>
      </div>
    </div>
    <div v-if="Object.keys(data.by_country || {}).length" class="chart-section">
      <div class="chart-title">{{ t('analytics.topCountries') }}</div>
      <div v-for="(count, country) in data.by_country" :key="country" class="country-row">
        <span>{{ country || 'Unknown' }}</span>
        <div class="country-bar-wrap"><div class="country-bar" :style="{ width: countryPct(count) + '%' }"></div></div>
        <span class="country-count">{{ count }}</span>
      </div>
    </div>
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

const maxDay = computed(() => Math.max(...Object.values(data.value?.by_day || { 0: 1 })))
const recentDays = computed(() => {
  const days = data.value?.by_day || {}
  return Object.fromEntries(Object.entries(days).slice(-7))
})

function barHeight(count) {
  return Math.max(8, (count / maxDay.value) * 100)
}

function countryPct(count) {
  const max = Math.max(...Object.values(data.value?.by_country || { 0: 1 }))
  return (count / max) * 100
}

function formatDay(d) {
  return new Date(d).toLocaleDateString('en', { weekday: 'short' })
}

async function load() {
  if (!props.id) return
  loading.value = true
  try {
    const { data: res } = await api.get(`/analytics/${props.type}/${props.id}`)
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
.analytics-panel { font-size: 0.875rem; }
.stat-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem; }
.stat-box {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 0.875rem;
  text-align: center;
}
.stat-num { font-size: 1.5rem; font-weight: 700; color: var(--brand); }
.stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.chart-section { margin-top: 1rem; }
.chart-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.625rem;
}
.bar-chart { display: flex; align-items: flex-end; gap: 0.375rem; height: 80px; }
.bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; }
.bar {
  width: 100%;
  background: linear-gradient(to top, var(--purple), var(--brand));
  border-radius: 4px 4px 0 0;
  min-height: 4px;
  transition: height 0.5s;
}
.bar-label { font-size: 0.625rem; color: var(--text-muted); margin-top: 0.25rem; }
.country-row {
  display: grid;
  grid-template-columns: 4rem 1fr 2rem;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.375rem;
  font-size: 0.8125rem;
  color: var(--text-secondary);
}
.country-bar-wrap { height: 6px; background: var(--bg-subtle); border-radius: 3px; overflow: hidden; border: 1px solid var(--border); }
.country-bar {
  height: 100%;
  background: linear-gradient(90deg, var(--brand), var(--gold));
  border-radius: 3px;
  transition: width 0.5s;
}
.country-count { text-align: right; color: var(--purple); font-weight: 700; }
</style>
