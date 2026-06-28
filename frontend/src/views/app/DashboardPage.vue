<template>
  <div>
    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <template v-else>
      <!-- Welcome banner -->
      <div class="welcome-banner welcome-banner-light">
        <div>
          <h2 class="welcome-title">{{ t('dashboard.welcomeBack', { name: welcomeFirstName }) }}</h2>
          <p class="welcome-sub">{{ t('dashboard.welcomeSub') }}</p>
        </div>
        <div class="plan-chip capitalize">{{ t('billing.planTitle', { plan: data.plan }) }}</div>
      </div>

      <!-- Stats grid -->
      <div class="stats-grid">
        <div v-for="stat in statCards" :key="stat.label" class="stat-card stat-card-light" :style="{ '--accent': stat.color }">
          <div class="stat-icon" v-html="stat.icon"></div>
          <div class="stat-value">{{ stat.value }}</div>
          <div class="stat-label">{{ stat.label }}</div>
          <div class="stat-bar"><div class="stat-bar-fill" :style="{ width: stat.pct + '%' }"></div></div>
        </div>
      </div>

      <!-- Usage + quick create -->
      <div class="two-col">
        <div class="usage-card usage-card-light">
          <h3>{{ t('dashboard.monthlyScanUsage') }}</h3>
          <div class="usage-ring-wrap">
            <svg viewBox="0 0 120 120" class="usage-ring">
              <circle cx="60" cy="60" r="52" fill="none" stroke="var(--border)" stroke-width="10"/>
              <circle cx="60" cy="60" r="52" fill="none" stroke="var(--brand)" stroke-width="10"
                :stroke-dasharray="`${scanPct * 3.27} 327`" stroke-linecap="round"
                transform="rotate(-90 60 60)" class="usage-arc"/>
            </svg>
            <div class="usage-center">
              <div class="usage-num">{{ data.stats?.scans_this_month || 0 }}</div>
              <div class="usage-of">/ {{ scanLimitLabel }}</div>
            </div>
          </div>
        </div>
        <div class="quick-create quick-create-light">
          <h3>{{ t('dashboard.quickCreate') }}</h3>
          <router-link v-for="action in quickActions" :key="action.to" :to="action.to" class="quick-item">
            <span class="quick-icon" v-html="action.icon"></span>
            <div>
              <div class="quick-title">{{ action.title }}</div>
              <div class="quick-desc">{{ action.desc }}</div>
            </div>
            <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </router-link>
        </div>
      </div>

      <!-- Recent activity -->
      <div class="activity-card activity-card-light">
        <h3>{{ t('dashboard.recentActivity') }}</h3>
        <div v-if="!data.recent_activity?.length" class="empty-activity">
          {{ t('dashboard.emptyActivity') }}
        </div>
        <div v-else class="activity-list">
          <div v-for="event in data.recent_activity" :key="event.id" class="activity-item">
            <div class="activity-icon">{{ eventTypeIcon(event.event_type) }}</div>
            <div class="flex-1">
              <div class="activity-type capitalize">{{ event.event_type }}</div>
              <div class="activity-meta">{{ event.country || t('common.unknown') }} · {{ formatDate(event.created_at) }}</div>
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
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const { t } = useI18n()
const auth = useAuthStore()
const data = ref({ stats: {}, limits: {}, plan: 'free', recent_activity: [] })
const loading = ref(true)

const welcomeFirstName = computed(() =>
  auth.user?.name ? t('dashboard.welcomeBackFirstName', { name: auth.user.name.split(' ')[0] }) : ''
)

const statCards = computed(() => {
  const limits = data.value.limits || {}
  const stats = data.value.stats || {}
  return [
    { icon: '▦', label: t('dashboard.statQrCodes'), value: stats.qr_codes || 0, color: '#e8655a', pct: pct(stats.qr_codes, limits.qr_codes) },
    { icon: '🔗', label: t('dashboard.statShortLinks'), value: stats.short_links || 0, color: '#e8b84a', pct: pct(stats.short_links, limits.short_links) },
    { icon: '👤', label: t('dashboard.statCards'), value: stats.business_cards || 0, color: '#6b4fa0', pct: pct(stats.business_cards, limits.business_cards) },
    { icon: '📄', label: t('dashboard.statPages'), value: stats.digital_pages || 0, color: '#e8b84a', pct: pct(stats.digital_pages, limits.digital_pages) },
    { icon: '🍽', label: t('dashboard.statMenus'), value: stats.digital_menus || 0, color: '#e8655a', pct: pct(stats.digital_menus, limits.digital_menus) },
    { icon: '🏅', label: t('dashboard.statBadges'), value: stats.digital_badges || 0, color: '#6b4fa0', pct: pct(stats.digital_badges, limits.digital_badges) },
    { icon: '🎫', label: t('dashboard.statTickets'), value: stats.digital_tickets || 0, color: '#e8b84a', pct: pct(stats.digital_tickets, limits.digital_tickets) },
    { icon: '🎁', label: t('dashboard.statScanToWin'), value: stats.scan_to_win || 0, color: '#e8655a', pct: pct(stats.scan_to_win, limits.scan_to_win) },
    { icon: '📈', label: t('dashboard.statTotalScans'), value: stats.total_scans || 0, color: '#e8655a', pct: 50 },
  ]
})

function pct(used, limit) {
  if (!limit || limit === -1) return Math.min(used * 10, 100)
  return Math.min(100, (used / limit) * 100)
}

const scanPct = computed(() => {
  const limit = data.value.limits?.scans
  if (!limit || limit === -1) return 15
  return Math.min(100, ((data.value.stats?.scans_this_month || 0) / limit) * 100)
})

const scanLimitLabel = computed(() => {
  const limit = data.value.limits?.scans
  return limit === -1 ? '∞' : limit
})

const quickActions = computed(() => [
  { to: '/app/qr-codes', icon: '▦', title: t('dashboard.quickNewQrCode'), desc: t('dashboard.quickNewQrDesc') },
  { to: '/app/short-links', icon: '🔗', title: t('dashboard.quickShortenUrl'), desc: t('dashboard.quickShortenDesc') },
  { to: '/app/business-cards', icon: '👤', title: t('dashboard.quickBusinessCard'), desc: t('dashboard.quickBusinessCardDesc') },
  { to: '/app/digital-pages', icon: '📄', title: t('dashboard.quickDigitalPage'), desc: t('dashboard.quickDigitalPageDesc') },
  { to: '/app/digital-menus', icon: '🍽', title: t('dashboard.quickDigitalMenu'), desc: t('dashboard.quickDigitalMenuDesc') },
  { to: '/app/digital-badges', icon: '🏅', title: t('dashboard.quickDigitalBadge'), desc: t('dashboard.quickDigitalBadgeDesc') },
  { to: '/app/digital-tickets', icon: '🎫', title: t('dashboard.quickDigitalTicket'), desc: t('dashboard.quickDigitalTicketDesc') },
  { to: '/app/scan-to-win', icon: '🎁', title: t('dashboard.quickScanToWin'), desc: t('dashboard.quickScanToWinDesc') },
])

function eventTypeIcon(type) {
  return { scan: '▦', click: '🔗', view: '👤' }[type] || '•'
}
function formatDate(d) { return new Date(d).toLocaleDateString() }

onMounted(async () => {
  try {
    const { data: res } = await api.get('/dashboard')
    data.value = res
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.welcome-banner {
  display: flex; justify-content: space-between; align-items: center;
  background: var(--sidebar-bg);
  border-radius: 1.25rem; padding: 1.5rem 2rem; color: white; margin-bottom: 1.5rem;
  border: 1px solid rgba(255,255,255,0.08);
}
.welcome-title { font-size: 1.375rem; font-weight: 700; }
.welcome-sub { color: var(--sidebar-muted); font-size: 0.875rem; margin-top: 0.25rem; }
.plan-chip {
  background: var(--brand); color: white;
  padding: 0.375rem 1rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600;
}
.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (min-width: 1024px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1280px) { .stats-grid { grid-template-columns: repeat(6, 1fr); } }
.stat-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem;
  padding: 1.25rem; position: relative; overflow: hidden;
}
.stat-card::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
  background: var(--accent);
}
.stat-icon {
  font-size: 1.25rem; width: 2.5rem; height: 2.5rem; border-radius: 0.625rem;
  background: var(--brand-muted); display: flex; align-items: center; justify-content: center;
}
.stat-value { font-size: 2rem; font-weight: 800; color: var(--brand); margin-top: 0.5rem; }
.stat-label { font-size: 0.8125rem; color: var(--text-secondary); }
.stat-bar { height: 3px; background: var(--bg-subtle); border-radius: 2px; margin-top: 0.75rem; overflow: hidden; }
.stat-bar-fill { height: 100%; background: var(--brand); border-radius: 2px; transition: width 0.7s; }
.two-col { display: grid; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (min-width: 768px) { .two-col { grid-template-columns: 1fr 1fr; } }
.usage-card, .quick-create, .activity-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem;
}
.usage-card h3, .quick-create h3, .activity-card h3 {
  font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; font-size: 0.9375rem;
}
.usage-ring-wrap { position: relative; width: 140px; margin: 0 auto; }
.usage-ring { width: 140px; height: 140px; }
.usage-arc { transition: stroke-dasharray 0.7s; }
.usage-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.usage-num { font-size: 1.75rem; font-weight: 800; color: var(--brand); }
.usage-of { font-size: 0.75rem; color: var(--text-muted); }
.quick-item {
  display: flex; align-items: center; gap: 0.875rem; padding: 0.875rem;
  border-radius: 0.75rem; text-decoration: none; color: inherit;
  transition: background 0.15s; margin-bottom: 0.375rem;
}
.quick-item:hover { background: var(--bg-subtle); }
.quick-icon {
  font-size: 1.25rem; width: 2.5rem; height: 2.5rem; border-radius: 0.625rem;
  background: var(--brand-muted); display: flex; align-items: center; justify-content: center;
}
.quick-title { font-weight: 600; color: var(--text-primary); font-size: 0.875rem; }
.quick-desc { font-size: 0.75rem; color: var(--text-muted); }
.activity-list { display: flex; flex-direction: column; gap: 0.5rem; }
.activity-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.75rem; border-radius: 0.75rem; background: var(--bg-subtle);
}
.activity-icon {
  width: 2rem; height: 2rem; border-radius: 0.5rem; background: var(--brand-muted);
  display: flex; align-items: center; justify-content: center; font-size: 0.875rem;
}
.activity-type { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.activity-meta { font-size: 0.75rem; color: var(--text-muted); }
</style>
