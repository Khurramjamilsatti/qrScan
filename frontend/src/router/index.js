import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'landing',
    component: () => import('../views/LandingPage.vue'),
  },
  {
    path: '/card/:slug',
    name: 'public-card',
    component: () => import('../views/PublicCardPage.vue'),
  },
  {
    path: '/page/:slug',
    name: 'public-page',
    component: () => import('../views/PublicDigitalPage.vue'),
  },
  {
    path: '/menu/:slug',
    name: 'public-menu',
    component: () => import('../views/PublicMenuPage.vue'),
  },
  {
    path: '/badge/:slug',
    name: 'public-badge',
    component: () => import('../views/PublicDigitalBadgePage.vue'),
  },
  {
    path: '/ticket/:slug',
    name: 'public-ticket',
    component: () => import('../views/PublicDigitalTicketPage.vue'),
  },
  {
    path: '/win/:slug',
    name: 'public-win',
    component: () => import('../views/PublicScanToWinPage.vue'),
  },
  {
    path: '/support',
    name: 'support',
    component: () => import('../views/PublicSitePage.vue'),
    meta: { pageSlug: 'support' },
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('../views/PublicSitePage.vue'),
    meta: { pageSlug: 'contact' },
  },
  {
    path: '/privacy',
    name: 'privacy',
    component: () => import('../views/PublicSitePage.vue'),
    meta: { pageSlug: 'privacy' },
  },
  {
    path: '/terms',
    name: 'terms',
    component: () => import('../views/PublicSitePage.vue'),
    meta: { pageSlug: 'terms' },
  },
  {
    path: '/r/:slug',
    name: 'short-link-redirect',
    component: () => import('../views/ShortLinkRedirect.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginPage.vue'),
    meta: { guest: true, appGuest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/auth/RegisterPage.vue'),
    meta: { guest: true, appGuest: true },
  },
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('../views/auth/AdminLoginPage.vue'),
    meta: { guest: true, adminGuest: true },
  },
  {
    path: '/app',
    component: () => import('../layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, appOnly: true },
    children: [
      { path: '', name: 'dashboard', component: () => import('../views/app/DashboardPage.vue') },
      { path: 'qr-codes', name: 'qr-codes', component: () => import('../views/app/QrCodesPage.vue') },
      { path: 'short-links', name: 'short-links', component: () => import('../views/app/ShortLinksPage.vue') },
      { path: 'business-cards', name: 'business-cards', component: () => import('../views/app/BusinessCardsPage.vue') },
      { path: 'digital-pages', name: 'digital-pages', component: () => import('../views/app/DigitalPagesPage.vue') },
      { path: 'digital-menus', name: 'digital-menus', component: () => import('../views/app/DigitalMenusPage.vue') },
      { path: 'digital-badges', name: 'digital-badges', component: () => import('../views/app/DigitalBadgesPage.vue') },
      { path: 'digital-tickets', name: 'digital-tickets', component: () => import('../views/app/DigitalTicketsPage.vue') },
      { path: 'scan-to-win', name: 'scan-to-win', component: () => import('../views/app/ScanToWinPage.vue') },
      { path: 'domains', name: 'domains', component: () => import('../views/app/CustomDomainsPage.vue') },
      { path: 'billing', name: 'billing', component: () => import('../views/app/BillingPage.vue') },
      { path: 'settings', name: 'settings', component: () => import('../views/app/ProfileSettingsPage.vue') },
    ],
  },
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAdminAuth: true },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('../views/admin/AdminDashboard.vue') },
      { path: 'landing', name: 'admin-landing', component: () => import('../views/admin/LandingEditor.vue') },
      { path: 'site-pages', name: 'admin-site-pages', component: () => import('../views/admin/SitePagesEditor.vue') },
      { path: 'users', name: 'admin-users', component: () => import('../views/admin/UsersPage.vue') },
      { path: 'users/:id', name: 'admin-user-detail', component: () => import('../views/admin/UserDetailPage.vue') },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to) {
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  const isAdminRoute = to.path.startsWith('/admin') && to.name !== 'admin-login'

  if (isAdminRoute || to.meta.adminGuest) {
    if (!auth.adminUser && localStorage.getItem('admin_token')) {
      await auth.fetchAdminUser()
    }
    if (to.meta.requiresAdminAuth && !auth.isAdminAuthenticated) {
      return '/admin/login'
    }
    if (to.meta.adminGuest && auth.isAdminAuthenticated) {
      return '/admin'
    }
    return
  }

  if (!auth.user && localStorage.getItem('token')) {
    await auth.fetchUser()
  }
  if (to.meta.appOnly && !auth.isAuthenticated) {
    return '/login'
  }
  if (to.meta.appGuest && auth.isAuthenticated) {
    return '/app'
  }
})

export default router
