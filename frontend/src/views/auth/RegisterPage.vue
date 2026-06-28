<template>
  <div class="min-h-screen flex bg-page">
    <div class="hidden lg:flex lg:w-1/2 register-panel relative overflow-hidden items-center justify-center p-12">
      <div class="relative text-white max-w-md z-10">
        <h2 class="font-display text-4xl mb-4">{{ t('auth.startForFree') }}</h2>
        <p class="text-white/80 leading-relaxed">{{ t('auth.registerPanelDesc') }}</p>
        <ul class="mt-8 space-y-3">
          <li v-for="item in perks" :key="item" class="flex items-center gap-3 text-sm">
            <svg class="w-5 h-5 text-teal-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ item }}
          </li>
        </ul>
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
        <h1 class="text-3xl font-display text-primary mb-2">{{ t('auth.createAccount') }}</h1>
        <p class="text-secondary mb-8">{{ t('auth.registerSubtitle') }}</p>
        <form @submit.prevent="handleRegister" class="space-y-5">
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('auth.fullName') }}</label>
            <input v-model="form.name" type="text" required class="input-field" :placeholder="t('auth.namePlaceholder')" />
          </div>
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('common.email') }}</label>
            <input v-model="form.email" type="email" required class="input-field" :placeholder="t('auth.emailPlaceholder')" />
          </div>
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('common.password') }}</label>
            <input v-model="form.password" type="password" required minlength="8" class="input-field" :placeholder="t('auth.minPasswordPlaceholder')" />
          </div>
          <div>
            <label class="block text-sm font-medium text-primary mb-1.5">{{ t('auth.confirmPassword') }}</label>
            <input v-model="form.password_confirmation" type="password" required class="input-field" />
          </div>
          <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
          <button type="submit" :disabled="auth.loading" class="btn-primary w-full py-3.5">
            {{ auth.loading ? t('common.creating') : t('auth.createFreeAccount') }}
          </button>
        </form>
        <p class="mt-6 text-center text-secondary text-sm">
          {{ t('auth.alreadyHaveAccount') }}
          <router-link to="/login" class="text-brand-500 font-medium hover:underline">{{ t('auth.signIn') }}</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
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
const form = ref({ name: '', email: '', password: '', password_confirmation: '' })
const error = ref('')
const perks = computed(() => [
  t('auth.perkQrCode'),
  t('auth.perkShortLinks'),
  t('auth.perkBusinessCard'),
  t('auth.perkScans'),
])

async function handleRegister() {
  error.value = ''
  if (form.value.password !== form.value.password_confirmation) {
    error.value = t('auth.passwordsDoNotMatch')
    return
  }
  try {
    await auth.register(form.value.name, form.value.email, form.value.password, form.value.password_confirmation)
    router.push('/app')
  } catch (e) {
    error.value = firstError(e, 'auth.registrationFailed')
  }
}
</script>

<style scoped>
.register-panel {
  background: var(--sidebar-bg);
  position: relative;
}
.register-panel::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at bottom left, rgba(232,101,90,0.35), transparent 55%),
              radial-gradient(ellipse at top right, rgba(232,184,74,0.2), transparent 50%);
}
</style>
