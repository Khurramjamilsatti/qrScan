<template>
  <div class="min-h-screen bg-page flex">
    <div v-if="sidebarOpen" class="fixed inset-0 bg-black/40 z-40 lg:hidden" @click="sidebarOpen = false"></div>

    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-top">
        <router-link to="/admin" class="flex items-center gap-2">
          <div class="logo-mark">
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z"/></svg>
          </div>
          <span class="font-bold sidebar-brand">{{ t('nav.adminPanel') }}</span>
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
        <router-link to="/" class="sidebar-link">← {{ t('common.publicSite') }}</router-link>
        <button type="button" @click="handleLogout" class="sidebar-link w-full text-left text-red-400 hover:text-red-300 mt-1">
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
        <div class="flex items-center gap-3 ml-auto">
          <LanguageSwitcher compact on-dark />
          <ThemeToggle on-dark />
          <div class="user-menu-wrap">
            <button type="button" class="user-menu-trigger" @click="menuOpen = !menuOpen">
              <span class="text-sm header-user hidden sm:inline">{{ auth.adminUser?.name }}</span>
              <div class="avatar">{{ initials }}</div>
            </button>
            <div v-if="menuOpen" class="user-menu">
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
import adminApi from '../services/adminApi'

const { t } = useI18n()

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)
const menuOpen = ref(false)

const IconDash = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z' })]) }
const IconEdit = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' })]) }
const IconPage = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' })]) }
const IconUsers = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })]) }
const IconForms = { render: () => h('svg', { fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' })]) }

const navLinks = computed(() => [
  { to: '/admin', name: 'admin-dashboard', label: t('nav.adminDashboard'), icon: IconDash },
  { to: '/admin/landing', name: 'admin-landing', label: t('nav.adminLandingPage'), icon: IconEdit },
  { to: '/admin/site-pages', name: 'admin-site-pages', label: t('nav.adminSitePages'), icon: IconPage },
  { to: '/admin/forms', name: 'admin-forms', label: t('nav.adminForms'), icon: IconForms },
  { to: '/admin/users', name: 'admin-users', label: t('nav.adminUsers'), icon: IconUsers },
])

const pageTitle = computed(() => {
  const link = navLinks.value.find(l => l.name === route.name)
  return link?.label || t('nav.adminPanel')
})
const initials = computed(() => auth.adminUser?.name?.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() || 'A')

function closeMenu(e) {
  if (!e.target.closest('.user-menu-wrap')) menuOpen.value = false
}

onMounted(() => document.addEventListener('click', closeMenu))
onUnmounted(() => document.removeEventListener('click', closeMenu))

async function handleLogout() {
  menuOpen.value = false
  try { await adminApi.post('/logout') } catch {}
  auth.adminLogout()
  router.push('/admin/login')
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
