<template>
  <div class="admin-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('admin.usersTitle') }}</h2>
        <p class="page-sub">{{ t('admin.usersSubtitle') }}</p>
      </div>
      <input v-model="search" type="search" class="input-field search-input" :placeholder="t('admin.searchPlaceholder')" @input="debouncedLoad" />
    </div>

    <div v-if="loading" class="text-muted">{{ t('admin.loadingUsers') }}</div>
    <template v-else>
      <div class="users-grid">
        <div v-for="user in users" :key="user.id" class="user-card">
          <div class="user-card-top">
            <div class="user-avatar">{{ initials(user.name) }}</div>
            <div class="flex-1 min-w-0">
              <div class="user-name">{{ user.name }}</div>
              <div class="user-email">{{ user.email }}</div>
            </div>
            <span v-if="user.is_admin" class="admin-badge">{{ t('admin.adminBadge') }}</span>
          </div>

          <div class="usage-grid">
            <div class="usage-item"><span>{{ user.qr_codes_count }}</span> {{ t('admin.usageQr') }}</div>
            <div class="usage-item"><span>{{ user.short_links_count }}</span> {{ t('admin.usageLinks') }}</div>
            <div class="usage-item"><span>{{ user.business_cards_count }}</span> {{ t('admin.usageCards') }}</div>
            <div class="usage-item"><span>{{ user.digital_pages_count || 0 }}</span> {{ t('admin.usagePages') }}</div>
            <div class="usage-item"><span>{{ user.digital_menus_count || 0 }}</span> {{ t('admin.usageMenus') }}</div>
            <div class="usage-item"><span>{{ user.digital_badges_count || 0 }}</span> {{ t('admin.usageBadges') }}</div>
            <div class="usage-item"><span>{{ user.digital_tickets_count || 0 }}</span> {{ t('admin.usageTickets') }}</div>
            <div class="usage-item"><span>{{ user.scan_to_win_campaigns_count || 0 }}</span> {{ t('admin.usageWin') }}</div>
          </div>

          <div class="user-card-actions">
            <select v-model="user.plan" class="input-field plan-select capitalize" @change="updatePlan(user)">
              <option value="free">{{ t('billing.plans.free') }}</option>
              <option value="starter">{{ t('billing.plans.starter') }}</option>
              <option value="pro">{{ t('billing.plans.pro') }}</option>
              <option value="business">{{ t('billing.plans.business') }}</option>
            </select>
            <router-link :to="`/admin/users/${user.id}`" class="btn-secondary detail-btn">{{ t('admin.viewAnalytics') }}</router-link>
          </div>
        </div>
      </div>

      <div v-if="!users.length" class="empty-card">{{ t('admin.noUsersMatch') }}</div>

      <div v-if="pagination.last_page > 1" class="pagination">
        <button type="button" class="btn-secondary" :disabled="pagination.current_page <= 1" @click="goPage(pagination.current_page - 1)">{{ t('common.previous') }}</button>
        <span class="page-info">{{ t('common.pageOf', { current: pagination.current_page, total: pagination.last_page }) }}</span>
        <button type="button" class="btn-secondary" :disabled="pagination.current_page >= pagination.last_page" @click="goPage(pagination.current_page + 1)">{{ t('common.next') }}</button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'

const { t } = useI18n()

const users = ref([])
const loading = ref(true)
const search = ref('')
const pagination = ref({ current_page: 1, last_page: 1 })
let searchTimer = null

function initials(name) {
  return name?.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() || '?'
}

async function load(page = 1) {
  loading.value = true
  try {
    const { data } = await adminApi.get('/users', {
      params: { page, search: search.value || undefined },
    })
    users.value = data.data || []
    pagination.value = {
      current_page: data.current_page || 1,
      last_page: data.last_page || 1,
    }
  } finally {
    loading.value = false
  }
}

function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(1), 300)
}

function goPage(page) { load(page) }

async function updatePlan(user) {
  await adminApi.put(`/users/${user.id}/plan`, { plan: user.plan })
}

onMounted(() => load())
</script>

<style scoped>
.admin-page { color: var(--text-primary); }
.page-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;
}
.page-title { font-size: 1.375rem; font-weight: 700; }
.page-sub { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem; }
.search-input { max-width: 18rem; }
.users-grid {
  display: grid; gap: 1rem;
  grid-template-columns: 1fr;
}
@media (min-width: 768px) { .users-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .users-grid { grid-template-columns: repeat(3, 1fr); } }
.user-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem;
  padding: 1.25rem; transition: box-shadow 0.2s;
}
.user-card:hover { box-shadow: var(--shadow-md); }
.user-card-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.user-avatar {
  width: 2.5rem; height: 2.5rem; border-radius: 50%; background: var(--brand);
  color: white; font-size: 0.75rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.user-name { font-weight: 600; color: var(--text-primary); }
.user-email { font-size: 0.8125rem; color: var(--text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.admin-badge {
  font-size: 0.6875rem; font-weight: 600; padding: 0.2rem 0.5rem;
  border-radius: 9999px; background: var(--gold-muted); color: var(--gold);
}
.usage-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.375rem; margin-bottom: 1rem;
}
.usage-item {
  font-size: 0.6875rem; color: var(--text-muted); text-align: center;
  padding: 0.375rem; border-radius: 0.5rem; background: var(--bg-subtle);
}
.usage-item span { display: block; font-size: 0.9375rem; font-weight: 700; color: var(--brand); }
.user-card-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.plan-select { flex: 1; min-width: 6rem; padding: 0.5rem 0.75rem; font-size: 0.8125rem; }
.detail-btn { flex: 1; min-width: 8rem; padding: 0.5rem 1rem; font-size: 0.8125rem; text-align: center; }
.empty-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1rem;
  padding: 2rem; text-align: center; color: var(--text-muted);
}
.pagination {
  display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 1.5rem;
}
.page-info { font-size: 0.875rem; color: var(--text-secondary); }
</style>
