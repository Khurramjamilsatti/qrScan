<template>
  <div class="admin-page">
    <router-link to="/admin/users" class="back-link">← {{ t('admin.backToUsers') }}</router-link>

    <div v-if="loading" class="text-muted">{{ t('admin.loadingUserAnalytics') }}</div>
    <div v-else-if="error" class="empty-card">{{ t('admin.userNotFound') }}</div>
    <template v-else>
      <div class="welcome-banner">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="user-avatar lg">{{ initials(data.user?.name) }}</div>
          <div>
            <h2 class="welcome-title">{{ data.user?.name }}</h2>
            <p class="welcome-sub">{{ data.user?.email }}</p>
          </div>
        </div>
        <div class="banner-actions">
          <select v-model="data.user.plan" class="input-field plan-select capitalize" @change="updatePlan">
            <option value="free">{{ t('billing.plans.free') }}</option>
            <option value="starter">{{ t('billing.plans.starter') }}</option>
            <option value="pro">{{ t('billing.plans.pro') }}</option>
            <option value="business">{{ t('billing.plans.business') }}</option>
          </select>
          <span class="summary-chip capitalize">{{ t('admin.planLabel', { plan: data.user?.plan }) }}</span>
        </div>
      </div>

      <div class="stats-grid">
        <div v-for="stat in statCards" :key="stat.label" class="stat-card" :style="{ '--accent': stat.color }">
          <div class="stat-icon">{{ stat.icon }}</div>
          <div class="stat-value">{{ formatNum(stat.value) }}</div>
          <div class="stat-label">{{ stat.label }}</div>
          <div v-if="stat.limit != null" class="stat-limit">{{ stat.used }} / {{ stat.limit === -1 ? '∞' : stat.limit }}</div>
        </div>
      </div>

      <div class="two-col">
        <div class="panel-card">
          <h3>{{ t('admin.eventsByType') }}</h3>
          <div v-if="!eventTypes.length" class="empty-note">{{ t('admin.noEventsYet') }}</div>
          <div v-else class="bar-list">
            <div v-for="item in eventTypes" :key="item.label" class="bar-row">
              <span class="bar-label capitalize">{{ item.label }}</span>
              <div class="bar-track"><div class="bar-fill" :style="{ width: item.pct + '%' }"></div></div>
              <span class="bar-value">{{ item.value }}</span>
            </div>
          </div>
        </div>
        <div class="panel-card">
          <h3>{{ t('analytics.topCountries') }}</h3>
          <div v-if="!topCountries.length" class="empty-note">{{ t('admin.noGeoData') }}</div>
          <div v-else class="bar-list">
            <div v-for="item in topCountries" :key="item.label" class="bar-row">
              <span class="bar-label">{{ item.label }}</span>
              <div class="bar-track"><div class="bar-fill" :style="{ width: item.pct + '%' }"></div></div>
              <span class="bar-value">{{ item.value }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="panel-card mb-6">
        <h3>{{ t('admin.recentActivity') }}</h3>
        <div v-if="!data.recent_activity?.length" class="empty-note">{{ t('admin.noActivityRecorded') }}</div>
        <div v-else class="activity-list">
          <div v-for="event in data.recent_activity" :key="event.id" class="activity-item">
            <div class="activity-icon">{{ eventIcon(event.event_type) }}</div>
            <div class="flex-1">
              <div class="activity-type capitalize">{{ event.event_type }}</div>
              <div class="activity-meta">{{ event.country || t('admin.unknown') }} · {{ formatDate(event.created_at) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div v-for="section in resourceSections" :key="section.key" class="panel-card resource-section">
        <h3>{{ section.label }}</h3>
        <div v-if="!section.items.length" class="empty-note">{{ t('common.noneCreated') }}</div>
        <div v-else class="resource-table-wrap">
          <table class="resource-table">
            <thead>
              <tr>
                <th>{{ t('admin.tableName') }}</th>
                <th>{{ t('admin.tableSlugCode') }}</th>
                <th>{{ t('admin.tableEngagement') }}</th>
                <th>{{ t('admin.tableStatus') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in section.items" :key="item.id">
                <td>{{ section.name(item) }}</td>
                <td class="mono">{{ section.slug(item) }}</td>
                <td>{{ section.metric(item) }}</td>
                <td>
                  <span class="status-pill" :class="{ active: section.active(item) }">
                    {{ section.active(item) ? t('common.active') : t('common.inactive') }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'

const { t } = useI18n()
const route = useRoute()
const data = ref({ user: {}, stats: {}, limits: {}, analytics: {}, resources: {}, recent_activity: [] })
const loading = ref(true)
const error = ref(false)

const statCards = computed(() => {
  const s = data.value.stats || {}
  const l = data.value.limits || {}
  return [
    { icon: '▦', label: t('dashboard.statQrCodes'), value: s.qr_codes, used: s.qr_codes, limit: l.qr_codes, color: '#e8655a' },
    { icon: '🔗', label: t('dashboard.statShortLinks'), value: s.short_links, used: s.short_links, limit: l.short_links, color: '#e8b84a' },
    { icon: '👤', label: t('dashboard.statCards'), value: s.business_cards, used: s.business_cards, limit: l.business_cards, color: '#6b4fa0' },
    { icon: '📄', label: t('dashboard.statPages'), value: s.digital_pages, used: s.digital_pages, limit: l.digital_pages, color: '#e8b84a' },
    { icon: '🍽', label: t('dashboard.statMenus'), value: s.digital_menus, used: s.digital_menus, limit: l.digital_menus, color: '#e8655a' },
    { icon: '🏅', label: t('dashboard.statBadges'), value: s.digital_badges, used: s.digital_badges, limit: l.digital_badges, color: '#6b4fa0' },
    { icon: '🎫', label: t('dashboard.statTickets'), value: s.digital_tickets, used: s.digital_tickets, limit: l.digital_tickets, color: '#e8655a' },
    { icon: '🎰', label: t('dashboard.statScanToWin'), value: s.scan_to_win, used: s.scan_to_win, limit: l.scan_to_win, color: '#e8b84a' },
    { icon: '📝', label: t('dashboard.statForms'), value: s.forms, used: s.forms, limit: l.forms, color: '#673ab7' },
    { icon: '📈', label: t('dashboard.statTotalScans'), value: s.total_scans, color: '#e8655a' },
    { icon: '🖱', label: t('admin.statTotalClicks'), value: s.total_clicks, color: '#e8b84a' },
    { icon: '👁', label: t('admin.statTotalViews'), value: s.total_views, color: '#6b4fa0' },
    { icon: '📅', label: t('admin.statScansThisMonth'), value: s.scans_this_month, used: s.scans_this_month, limit: l.scans, color: '#e8655a' },
  ]
})

const eventTypes = computed(() => barItems(data.value.analytics?.by_type))
const topCountries = computed(() => barItems(data.value.analytics?.by_country))

const resourceSections = computed(() => {
  const r = data.value.resources || {}
  return [
    { key: 'qr_codes', label: t('dashboard.statQrCodes'), items: r.qr_codes || [], name: i => i.name, slug: i => i.code, metric: i => t('admin.metricScans', { count: i.scan_count }), active: i => i.is_active },
    { key: 'short_links', label: t('dashboard.statShortLinks'), items: r.short_links || [], name: i => i.title || i.slug, slug: i => i.slug, metric: i => t('admin.metricClicks', { count: i.click_count }), active: i => i.is_active },
    { key: 'business_cards', label: t('nav.businessCards'), items: r.business_cards || [], name: i => i.full_name, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count }), active: i => i.is_active },
    { key: 'digital_pages', label: t('dashboard.statPages'), items: r.digital_pages || [], name: i => i.title, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count }), active: i => i.is_active },
    { key: 'digital_menus', label: t('dashboard.statMenus'), items: r.digital_menus || [], name: i => i.title, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count }), active: i => i.is_active },
    { key: 'digital_badges', label: t('dashboard.statBadges'), items: r.digital_badges || [], name: i => i.title, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count }), active: i => i.is_active },
    { key: 'digital_tickets', label: t('dashboard.statTickets'), items: r.digital_tickets || [], name: i => i.title, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count }), active: i => i.is_active },
    { key: 'scan_to_win', label: t('dashboard.statScanToWin'), items: r.scan_to_win || [], name: i => i.name || i.title, slug: i => i.slug, metric: i => t('admin.metricViews', { count: i.view_count || 0 }), active: i => i.is_active },
    { key: 'forms', label: t('dashboard.statForms'), items: r.forms || [], name: i => i.title, slug: i => i.slug, metric: i => t('forms.responseCount', { count: i.submission_count || 0 }), active: i => i.is_active },
  ].filter(s => s.items.length > 0)
})

function barItems(obj) {
  const entries = Object.entries(obj || {}).sort((a, b) => b[1] - a[1])
  const max = entries[0]?.[1] || 1
  return entries.map(([label, value]) => ({ label, value, pct: Math.max(8, (value / max) * 100) }))
}

function formatNum(n) { return Number(n || 0).toLocaleString() }
function formatDate(d) { return new Date(d).toLocaleDateString() }
function initials(name) { return name?.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() || '?' }
function eventIcon(type) { return { scan: '▦', click: '🔗', view: '👁' }[type] || '•' }

async function load() {
  loading.value = true
  error.value = false
  try {
    const { data: res } = await adminApi.get(`/users/${route.params.id}`)
    data.value = res
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

async function updatePlan() {
  await adminApi.put(`/users/${data.value.user.id}/plan`, { plan: data.value.user.plan })
}

watch(() => route.params.id, load)
onMounted(load)
</script>

<style scoped>
.admin-page { color: var(--text-primary); }
.back-link {
  display: inline-block; margin-bottom: 1rem; font-size: 0.875rem;
  color: var(--brand); text-decoration: none; font-weight: 600;
}
.welcome-banner {
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
  background: var(--sidebar-bg); border-radius: 1.25rem; padding: 1.5rem 2rem; color: var(--sidebar-text);
  margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.08);
}
.welcome-title { font-size: 1.375rem; font-weight: 700; }
.welcome-sub { color: var(--sidebar-muted); font-size: 0.875rem; margin-top: 0.25rem; }
.banner-actions { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.summary-chip {
  background: var(--brand); color: white; padding: 0.375rem 1rem;
  border-radius: 9999px; font-size: 0.8125rem; font-weight: 600;
}
.plan-select { min-width: 8rem; padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
.user-avatar {
  width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--brand);
  color: white; font-size: 0.75rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.user-avatar.lg { width: 3rem; height: 3rem; font-size: 0.875rem; }
.stats-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;
}
@media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stat-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem;
  padding: 1.25rem; position: relative; overflow: hidden;
}
.stat-card::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--accent);
}
.stat-icon { font-size: 1.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--brand); margin-top: 0.5rem; }
.stat-label { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.25rem; }
.stat-limit { font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.25rem; }
.two-col { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (min-width: 1024px) { .two-col { grid-template-columns: 1fr 1fr; } }
.panel-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem;
}
.panel-card h3 { font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; font-size: 0.9375rem; }
.resource-section { margin-bottom: 1rem; }
.bar-list { display: flex; flex-direction: column; gap: 0.625rem; }
.bar-row { display: grid; grid-template-columns: 5rem 1fr 2.5rem; gap: 0.5rem; align-items: center; }
.bar-label { font-size: 0.8125rem; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bar-track { height: 6px; background: var(--bg-subtle); border-radius: 9999px; overflow: hidden; }
.bar-fill { height: 100%; background: var(--brand); border-radius: 9999px; }
.bar-value { font-size: 0.8125rem; font-weight: 600; color: var(--text-primary); text-align: right; }
.activity-list { display: flex; flex-direction: column; gap: 0.5rem; max-height: 320px; overflow-y: auto; }
.activity-item {
  display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem;
  border-radius: 0.75rem; background: var(--bg-subtle);
}
.activity-icon {
  width: 2rem; height: 2rem; border-radius: 0.5rem; background: var(--brand-muted);
  display: flex; align-items: center; justify-content: center; font-size: 0.875rem;
}
.activity-type { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.activity-meta { font-size: 0.75rem; color: var(--text-muted); }
.resource-table-wrap { overflow-x: auto; }
.resource-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.resource-table th {
  text-align: left; padding: 0.625rem 0.75rem; font-size: 0.6875rem;
  text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);
  border-bottom: 1px solid var(--border);
}
.resource-table td {
  padding: 0.75rem; border-bottom: 1px solid var(--border); color: var(--text-secondary);
}
.resource-table td.mono { font-family: ui-monospace, monospace; font-size: 0.75rem; }
.status-pill {
  font-size: 0.6875rem; font-weight: 600; padding: 0.2rem 0.5rem;
  border-radius: 9999px; background: var(--bg-subtle); color: var(--text-muted);
}
.status-pill.active { background: var(--brand-muted); color: var(--brand); }
.empty-card, .empty-note { font-size: 0.875rem; color: var(--text-muted); }
.empty-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 2rem; text-align: center;
}
.mb-6 { margin-bottom: 1.5rem; }
</style>
