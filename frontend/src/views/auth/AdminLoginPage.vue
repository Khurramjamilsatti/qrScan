<template>
  <div class="min-h-screen flex bg-page">
    <div class="hidden lg:flex lg:w-1/2 auth-panel relative overflow-hidden items-center justify-center p-12">
      <div class="auth-glow" aria-hidden="true"></div>
      <div class="relative text-white max-w-md z-10">
        <h2 class="font-display text-4xl mb-4">{{ t('auth.adminPortal') }}</h2>
        <p class="text-white/70 leading-relaxed">{{ t('auth.adminPortalDesc') }}</p>
      </div>
    </div>
    <div class="flex-1 flex items-center justify-center p-8 relative">
      <div class="absolute top-6 right-6 flex items-center gap-2">
        <LanguageSwitcher />
        <ThemeToggle />
      </div>
      <div class="w-full max-w-md">
        <router-link to="/" class="inline-flex items-center gap-2 mb-8 text-secondary hover:text-brand-500 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          {{ t('common.backToHome') }}
        </router-link>
        <h1 class="text-3xl font-display text-primary mb-2">{{ t('auth.adminSignIn') }}</h1>
        <p class="text-secondary mb-8">{{ t('auth.adminCredentialsOnly') }}</p>
        <form @submit.prevent="handleLogin" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('common.email') }}</label>
            <input v-model="form.email" type="email" required class="input-field" :placeholder="t('auth.adminEmailPlaceholder')" />
          </div>
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('common.password') }}</label>
            <input v-model="form.password" type="password" required class="input-field" :placeholder="t('auth.passwordPlaceholder')" />
          </div>
          <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
          <button type="submit" :disabled="auth.adminLoading" class="btn-primary w-full py-3.5">
            {{ auth.adminLoading ? t('common.signingIn') : t('auth.adminSignIn') }}
          </button>
        </form>
        <p class="mt-6 text-center text-secondary text-sm">
          {{ t('auth.customerAccount') }}
          <router-link to="/login" class="text-brand-500 font-medium hover:underline">{{ t('auth.appLogin') }}</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import { useApiError } from '../../composables/useApiError'
import ThemeToggle from '../../components/ui/ThemeToggle.vue'
import LanguageSwitcher from '../../components/ui/LanguageSwitcher.vue'

const { t } = useI18n()
const { firstError } = useApiError()
const auth = useAuthStore()
const router = useRouter()
const form = ref({ email: '', password: '' })
const error = ref('')

async function handleLogin() {
  error.value = ''
  try {
    await auth.adminLogin(form.value.email, form.value.password)
    router.push('/admin')
  } catch (e) {
    error.value = firstError(e, 'auth.invalidAdminCredentials')
  }
}
</script>

<style scoped>
.auth-panel { background: var(--sidebar-bg); }
.auth-glow {
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at bottom left, rgba(107,79,160,0.35), transparent 60%),
              radial-gradient(ellipse at top right, rgba(232,101,90,0.2), transparent 50%);
}
</style>
