<template>
  <div class="min-h-screen bg-page flex">
    <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-40 lg:hidden" @click="sidebarOpen = false"></div>

    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-top">
        <router-link to="/app" class="flex items-center gap-2">
          <div class="logo-mark">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z"/></svg>
          </div>
          <span class="font-bold sidebar-brand">QR<span class="text-gold">Scan</span></span>
        </router-link>
        <button class="lg:hidden sidebar-muted-text" @click="sidebarOpen = false">✕</button>
      </div>
      <nav class="sidebar-nav">
        <router-link v-for="link in navLinks" :key="link.to" :to="link.to"
          class="sidebar-link" :class="{ active: $route.name === link.name }" @click="sidebarOpen = false">
          <component :is="link.icon" class="w-5 h-5" />
          {{ link.label }}
        </router-link>
      </nav>
      <div class="sidebar-bottom">
        <div class="plan-badge">
          <div class="text-xs sidebar-muted-text uppercase tracking-wider mb-1">{{ t('common.plan') }}</div>
          <div class="font-semibold capitalize text-gold">{{ auth.user?.plan || 'free' }}</div>
          <div v-if="auth.usage" class="usage-mini mt-2">
            <div class="usage-bar"><div class="usage-fill" :style="{ width: scanPct + '%' }"></div></div>
            <div class="text-xs sidebar-muted-text mt-1">{{ t('common.scansUsed', { count: auth.usage.scans_this_month || 0 }) }}</div>
          </div>
        </div>
        <button type="button" @click="handleLogout" class="sidebar-link w-full text-start text-red-400 hover:text-red-300 mt-2">
          {{ t('common.signOut') }}
        </button>
      </div>
    </aside>

    <div class="main-content">
      <header class="app-header">
        <button class="menu-btn lg:hidden" @click="sidebarOpen = true">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <h1 class="text-lg font-semibold text-gold capitalize">{{ pageTitle }}</h1>
        <div class="flex items-center gap-3 ms-auto">
          <LanguageSwitcher compact on-dark />
          <ThemeToggle on-dark />
          <div class="user-menu-wrap">
            <button type="button" class="user-menu-trigger" @click="menuOpen = !menuOpen">
              <span class="text-sm header-user hidden sm:inline">{{ auth.user?.name }}</span>
              <div class="avatar">{{ initials }}</div>
            </button>
            <div v-if="menuOpen" class="user-menu">
              <router-link to="/app/settings" class="user-menu-item" @click="menuOpen = false">{{ t('common.profileSettings') }}</router-link>
              <button type="button" class="user-menu-item danger" @click="handleLogout">{{ t('common.signOut') }}</button>
            </div>
          </div>
        </div>
      </header>
      <main class="app-main app-main-bg">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, h, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../stores/auth'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import LanguageSwitcher from '../components/ui/LanguageSwitcher.vue'
import api from '../services/api'

const { t } = useI18n()

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)
const menuOpen = ref(false)

const IconDash = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z' })]) }
const IconQr = { render: () => h('svg', { fill: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { d: 'M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z' })]) }
const IconLink = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1' })]) }
const IconCard = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' })]) }
const IconPage = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })]) }
const IconMenu = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 6h16M4 12h16M4 18h7' })]) }
const IconBadge = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' })]) }
const IconTicket = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z' })]) }
const IconWin = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7' })]) }
const IconBill = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' })]) }
const IconDomain = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9' })]) }
const IconSettings = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' }), h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z' })]) }

const navLinks = computed(() => [
  { to: '/app', name: 'dashboard', label: t('nav.overview'), icon: IconDash },
  { to: '/app/qr-codes', name: 'qr-codes', label: t('nav.qrCodes'), icon: IconQr },
  { to: '/app/short-links', name: 'short-links', label: t('nav.shortLinks'), icon: IconLink },
  { to: '/app/business-cards', name: 'business-cards', label: t('nav.businessCards'), icon: IconCard },
  { to: '/app/digital-pages', name: 'digital-pages', label: t('nav.digitalPages'), icon: IconPage },
  { to: '/app/digital-menus', name: 'digital-menus', label: t('nav.digitalMenus'), icon: IconMenu },
  { to: '/app/digital-badges', name: 'digital-badges', label: t('nav.digitalBadges'), icon: IconBadge },
  { to: '/app/digital-tickets', name: 'digital-tickets', label: t('nav.digitalTickets'), icon: IconTicket },
  { to: '/app/scan-to-win', name: 'scan-to-win', label: t('nav.scanToWin'), icon: IconWin },
  { to: '/app/domains', name: 'domains', label: t('nav.customDomains'), icon: IconDomain },
  { to: '/app/billing', name: 'billing', label: t('nav.billing'), icon: IconBill },
  { to: '/app/settings', name: 'settings', label: t('nav.profile'), icon: IconSettings },
])

const pageTitle = computed(() => {
  const link = navLinks.value.find(l => l.name === route.name)
  return link?.label || t('nav.overview')
})
const initials = computed(() => auth.user?.name?.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() || '?')
const scanPct = computed(() => {
  const limit = auth.limits?.scans
  if (!limit || limit === -1) return 10
  return Math.min(100, ((auth.usage?.scans_this_month || 0) / limit) * 100)
})

function closeMenu(e) {
  if (!e.target.closest('.user-menu-wrap')) menuOpen.value = false
}

onMounted(() => document.addEventListener('click', closeMenu))
onUnmounted(() => document.removeEventListener('click', closeMenu))

async function handleLogout() {
  menuOpen.value = false
  try { await api.post('/logout') } catch {}
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar {
  width: 16rem;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  position: fixed;
  inset-block: 0;
  inset-inline-start: 0;
  z-index: 50;
  height: 100vh;
  transform: translateX(-100%);
  transition: transform 0.3s;
}
html[dir="rtl"] .sidebar { transform: translateX(100%); }
.sidebar.open { transform: translateX(0); }
@media (min-width: 1024px) {
  .sidebar,
  html[dir="rtl"] .sidebar { transform: translateX(0); }
}
.sidebar-top {
  flex-shrink: 0;
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.sidebar-nav {
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.sidebar-bottom {
  flex-shrink: 0;
  padding: 1rem;
  border-top: 1px solid rgba(255,255,255,0.1);
}
.main-content { flex: 1; min-width: 0; }
@media (min-width: 1024px) {
  .main-content { margin-inline-start: 16rem; }
}
.app-header {
  background: var(--header-bg);
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 0.875rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  position: sticky;
  top: 0;
  z-index: 30;
  color: var(--sidebar-text);
}
.app-main { padding: 1.5rem; background: var(--bg-page); min-height: calc(100vh - 4rem); }
@media (min-width: 768px) { .app-main { padding: 2rem; } }
.menu-btn { color: var(--sidebar-muted); padding: 0.25rem; }
.header-user { color: var(--sidebar-muted); }
.avatar {
  width: 2.25rem; height: 2.25rem; border-radius: 50%;
  background: var(--brand);
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-weight: 700; color: white;
}
.logo-mark {
  width: 2rem; height: 2rem; border-radius: 0.625rem;
  background: var(--brand);
  display: flex; align-items: center; justify-content: center;
}
.sidebar-brand { color: var(--sidebar-text); }
.sidebar-muted-text { color: var(--sidebar-muted); }
.plan-badge { padding: 0.75rem 1rem; border-radius: 0.75rem; background: rgba(255,255,255,0.05); }
.usage-bar { height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; overflow: hidden; }
.usage-fill { height: 100%; background: var(--brand); border-radius: 2px; transition: width 0.5s; }
.user-menu-wrap { position: relative; }
.user-menu-trigger {
  display: flex; align-items: center; gap: 0.75rem;
  background: none; border: none; cursor: pointer; color: inherit;
}
.user-menu {
  position: absolute;
  inset-inline-end: 0;
  top: calc(100% + 0.5rem);
  min-width: 11rem;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  box-shadow: var(--shadow-md);
  overflow: hidden;
  z-index: 50;
}
.user-menu-item {
  display: block; width: 100%; text-align: start;
  padding: 0.75rem 1rem; font-size: 0.875rem;
  color: var(--text-primary); text-decoration: none;
  background: none; border: none; cursor: pointer;
}
.user-menu-item:hover { background: var(--bg-subtle); }
.user-menu-item.danger { color: #ef4444; }
</style>
