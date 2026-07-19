<template>
  <router-view />
  <AppDialog />
  <div class="global-locale" aria-label="Language switcher">
    <LanguageSwitcher />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { applyDocumentLocale } from './i18n'
import { isPublicAppPath } from './utils/publicRoutes'
import AppDialog from './components/ui/AppDialog.vue'
import LanguageSwitcher from './components/ui/LanguageSwitcher.vue'

const auth = useAuthStore()

onMounted(() => {
  const saved = localStorage.getItem('qrscan-locale') || 'en'
  applyDocumentLocale(saved)
  if (!isPublicAppPath(window.location.pathname)) {
    auth.fetchUser()
  }
})
</script>

<style>
.global-locale {
  position: fixed;
  bottom: 1rem;
  inset-inline-start: 1rem;
  z-index: 9998;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  border-radius: 0.5rem;
}
</style>
