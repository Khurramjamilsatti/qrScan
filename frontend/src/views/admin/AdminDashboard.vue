<template>
  <div class="admin-page">
    <div v-if="loading" class="text-muted">{{ t('admin.loadingPlatformAnalytics') }}</div>
    <template v-else>
      <div class="welcome-banner">
        <div>
          <h2 class="welcome-title">{{ t('admin.platformOverview') }}</h2>
          <p class="welcome-sub">{{ t('admin.platformOverviewSub') }}</p>
        </div>
        <div class="summary-chip">{{ t('admin.usersCount', { count: data.stats?.users || 0 }) }}</div>
      </div>

      <div class="stats-grid">
        <div v-for="stat in statCards" :key="stat.label" class="stat-card" :style="{ '--accent': stat.color }">
          <div class="stat-icon">{{ stat.icon }}</div>
          <div class="stat-value">{{ formatNum(stat.value) }}</div>
          <div class="stat-label">{{ stat.label }}</div>
        </div>
      </div>

      <div class="two-col">
        <div class="panel-card">
          <h3>{{ t('admin.usersByPlan') }}</h3>
          <div class="plan-grid">
            <div v-for="(count, plan) in data.plans" :key="plan" class="plan-item">
              <div class="plan-count">{{ count }}</div>
              <div class="plan-name capitalize">{{ plan }}</div>
            </div>
          </div>
        </div>
        <div class="panel-card">
          <h3>{{ t('admin.eventsByType') }}</h3>
          <div v-if="!eventTypes.length" class="empty-note">{{ t('admin.noEventsRecorded') }}</div>
          <div v-else class="bar-list">
            <div v-for="item in eventTypes" :key="item.label" class="bar-row">
              <span class="bar-label capitalize">{{ item.label }}</span>
              <div class="bar-track"><div class="bar-fill" :style="{ width: item.pct + '%' }"></div></div>
              <span class="bar-value">{{ item.value }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="two-col">
        <div class="panel-card">
          <h3>{{ t('admin.topCountries30') }}</h3>
          <div v-if="!topCountries.length" class="empty-note">{{ t('admin.noGeoData') }}</div>
          <div v-else class="bar-list">
            <div v-for="item in topCountries" :key="item.label" class="bar-row">
              <span class="bar-label">{{ item.label }}</span>
              <div class="bar-track"><div class="bar-fill" :style="{ width: item.pct + '%' }"></div></div>
              <span class="bar-value">{{ item.value }}</span>
            </div>
          </div>
        </div>
        <div class="panel-card">
          <h3>{{ t('admin.activityTrend30') }}</h3>
          <div v-if="!activityTrend.length" class="empty-note">{{ t('admin.noActivity') }}</div>
          <div v-else class="trend-chart">
            <div v-for="item in activityTrend" :key="item.day" class="trend-col" :title="`${item.day}: ${item.count}`">
              <div class="trend-bar" :style="{ height: item.pct + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="two-col">
        <div class="panel-card">
          <div class="panel-head">
            <h3>{{ t('admin.topUsersByAssets') }}</h3>
            <router-link to="/admin/users" class="panel-link">{{ t('common.viewAll') }}</router-link>
          </div>
          <div v-if="!data.top_users?.length" class="empty-note">{{ t('admin.noUsersYet') }}</div>
          <div v-else class="user-list">
            <router-link v-for="u in data.top_users" :key="u.id" :to="`/admin/users/${u.id}`" class="user-row">
              <div class="user-avatar">{{ initials(u.name) }}</div>
              <div class="flex-1 min-w-0">
                <div class="user-name">{{ u.name }}</div>
                <div class="user-email">{{ u.email }}</div>
              </div>
              <span class="plan-pill capitalize">{{ u.plan }}</span>
              <span class="asset-count">{{ u.total_assets }} {{ t('admin.assets') }}</span>
            </router-link>
          </div>
        </div>
        <div class="panel-card">
          <h3>{{ t('admin.recentPlatformActivity') }}</h3>
          <div v-if="!data.recent_activity?.length" class="empty-note">{{ t('admin.noRecentEvents') }}</div>
          <div v-else class="activity-list">
            <div v-for="event in data.recent_activity" :key="event.id" class="activity-item">
              <div class="activity-icon">{{ eventIcon(event.event_type) }}</div>
              <div class="flex-1 min-w-0">
                <div class="activity-type capitalize">{{ event.event_type }}</div>
                <div class="activity-meta">
                  {{ event.user?.name || t('admin.userFallback') }} · {{ event.country || t('admin.unknown') }} · {{ formatDate(event.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'

const { t } = useI18n()

const data = ref({ stats: {}, plans: {}, recent_activity: [], events_by_type: {}, events_by_day: {}, top_countries: {}, top_users: [] })
const loading = ref(true)

const statCards = computed(() => {
  const s = data.value.stats || {}
  return [
    { icon: '👥', label: t('admin.statUsers'), value: s.users, color: '#6b4fa0' },
    { icon: '▦', label: t('dashboard.statQrCodes'), value: s.qr_codes, color: '#e8655a' },
    { icon: '🔗', label: t('dashboard.statShortLinks'), value: s.short_links, color: '#e8b84a' },
    { icon: '👤', label: t('nav.businessCards'), value: s.business_cards, color: '#6b4fa0' },
    { icon: '📄', label: t('dashboard.statPages'), value: s.digital_pages, color: '#e8b84a' },
    { icon: '🍽', label: t('dashboard.statMenus'), value: s.digital_menus, color: '#e8655a' },
    { icon: '🏅', label: t('dashboard.statBadges'), value: s.digital_badges, color: '#6b4fa0' },
    { icon: '🎫', label: t('dashboard.statTickets'), value: s.digital_tickets, color: '#e8655a' },
    { icon: '🎰', label: t('dashboard.statScanToWin'), value: s.scan_to_win, color: '#e8b84a' },
    { icon: '📈', label: t('dashboard.statTotalScans'), value: s.total_scans, color: '#e8655a' },
    { icon: '🖱', label: t('admin.statTotalClicks'), value: s.total_clicks, color: '#e8b84a' },
    { icon: '👁', label: t('admin.statTotalViews'), value: s.total_views, color: '#6b4fa0' },
    { icon: '🎯', label: t('admin.statWinPlays'), value: s.scan_to_win_plays, color: '#e8655a' },
    { icon: '📊', label: t('admin.statAnalyticsEvents'), value: s.analytics_events, color: '#6b4fa0' },
    { icon: '📅', label: t('admin.statScansThisMonth'), value: s.scans_this_month, color: '#e8b84a' },
    { icon: '🌐', label: t('admin.statCustomDomains'), value: s.custom_domains, color: '#e8655a' },
  ]
})

const eventTypes = computed(() => barItems(data.value.events_by_type))
const topCountries = computed(() => barItems(data.value.top_countries))
const activityTrend = computed(() => {
  const entries = Object.entries(data.value.events_by_day || {})
  if (!entries.length) return []
  const max = Math.max(...entries.map(([, c]) => c), 1)
  return entries.slice(-14).map(([day, count]) => ({
    day: day.slice(5),
    count,
    pct: Math.max(8, (count / max) * 100),
  }))
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

onMounted(async () => {
  try {
    const { data: res } = await adminApi.get('/dashboard')
    data.value = res
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.admin-page { color: var(--text-primary); }
.welcome-banner {
  display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;
  background: var(--sidebar-bg); border-radius: 1.25rem; padding: 1.5rem 2rem; color: var(--sidebar-text);
  margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.08);
}
.welcome-title { font-size: 1.375rem; font-weight: 700; }
.welcome-sub { color: var(--sidebar-muted); font-size: 0.875rem; margin-top: 0.25rem; }
.summary-chip {
  background: var(--brand); color: white; padding: 0.375rem 1rem;
  border-radius: 9999px; font-size: 0.8125rem; font-weight: 600;
}
.stats-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem;
}
@media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1280px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stat-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem;
  padding: 1.25rem; position: relative; overflow: hidden;
}
.stat-card::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: var(--accent);
}
.stat-icon { font-size: 1.25rem; }
.stat-value { font-size: 1.75rem; font-weight: 800; color: var(--brand); margin-top: 0.5rem; }
.stat-label { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.25rem; }
.two-col { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (min-width: 1024px) { .two-col { grid-template-columns: 1fr 1fr; } }
.panel-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem;
}
.panel-card h3 { font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; font-size: 0.9375rem; }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.panel-head h3 { margin-bottom: 0; }
.panel-link { font-size: 0.8125rem; color: var(--brand); text-decoration: none; font-weight: 600; }
.plan-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
@media (min-width: 640px) { .plan-grid { grid-template-columns: repeat(4, 1fr); } }
.plan-item {
  text-align: center; padding: 1rem; border-radius: 0.75rem;
  background: var(--bg-subtle); border: 1px solid var(--border);
}
.plan-count { font-size: 1.75rem; font-weight: 800; color: var(--brand); }
.plan-name { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.25rem; }
.bar-list { display: flex; flex-direction: column; gap: 0.625rem; }
.bar-row { display: grid; grid-template-columns: 5rem 1fr 2.5rem; gap: 0.5rem; align-items: center; }
.bar-label { font-size: 0.8125rem; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.bar-track { height: 6px; background: var(--bg-subtle); border-radius: 9999px; overflow: hidden; }
.bar-fill { height: 100%; background: var(--brand); border-radius: 9999px; transition: width 0.5s; }
.bar-value { font-size: 0.8125rem; font-weight: 600; color: var(--text-primary); text-align: right; }
.trend-chart { display: flex; align-items: flex-end; gap: 0.375rem; height: 120px; padding-top: 0.5rem; }
.trend-col { flex: 1; display: flex; align-items: flex-end; height: 100%; }
.trend-bar {
  width: 100%; min-height: 4px; background: var(--brand); border-radius: 4px 4px 0 0;
  transition: height 0.5s;
}
.user-list { display: flex; flex-direction: column; gap: 0.5rem; }
.user-row {
  display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem;
  border-radius: 0.75rem; background: var(--bg-subtle); text-decoration: none; color: inherit;
  transition: background 0.15s;
}
.user-row:hover { background: var(--brand-muted); }
.user-avatar {
  width: 2.25rem; height: 2.25rem; border-radius: 50%; background: var(--brand);
  color: white; font-size: 0.6875rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.user-name { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.user-email { font-size: 0.75rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.plan-pill {
  font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.625rem;
  border-radius: 9999px; background: var(--brand-muted); color: var(--brand); flex-shrink: 0;
}
.asset-count { font-size: 0.75rem; color: var(--text-muted); flex-shrink: 0; }
.activity-list { display: flex; flex-direction: column; gap: 0.5rem; max-height: 360px; overflow-y: auto; }
.activity-item {
  display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem;
  border-radius: 0.75rem; background: var(--bg-subtle);
}
.activity-icon {
  width: 2rem; height: 2rem; border-radius: 0.5rem; background: var(--brand-muted);
  display: flex; align-items: center; justify-content: center; font-size: 0.875rem; flex-shrink: 0;
}
.activity-type { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.activity-meta { font-size: 0.75rem; color: var(--text-muted); }
.empty-note { font-size: 0.875rem; color: var(--text-muted); }
</style>
