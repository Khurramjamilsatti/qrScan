<template>
  <div class="max-w-2xl">
    <div class="dashboard-card mb-6">
      <h2 class="text-xl font-semibold text-primary mb-1">{{ t('profile.title') }}</h2>
      <p class="text-secondary text-sm">{{ t('profile.subtitle') }}</p>
    </div>

    <div class="dashboard-card mb-6">
      <h3 class="font-semibold text-primary mb-4">{{ t('profile.account') }}</h3>
      <form @submit.prevent="saveProfile" class="grid gap-4 max-w-md">
        <div>
          <label class="block text-sm font-medium text-secondary mb-1.5">{{ t('common.name') }}</label>
          <input v-model="profileForm.name" class="input-field" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary mb-1.5">{{ t('common.email') }}</label>
          <input v-model="profileForm.email" type="email" class="input-field" required />
        </div>
        <p v-if="profileMessage" class="text-sm text-brand-500 font-medium">{{ profileMessage }}</p>
        <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ saving ? t('common.saving') : t('profile.saveProfile') }}</button>
      </form>
    </div>

    <div class="dashboard-card mb-6">
      <h3 class="font-semibold text-primary mb-4">{{ t('profile.passwordSection') }}</h3>
      <form @submit.prevent="savePassword" class="grid gap-4 max-w-md">
        <div>
          <label class="block text-sm font-medium text-secondary mb-1.5">{{ t('profile.currentPassword') }}</label>
          <input v-model="passwordForm.current_password" type="password" class="input-field" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary mb-1.5">{{ t('profile.newPassword') }}</label>
          <input v-model="passwordForm.password" type="password" class="input-field" required minlength="8" />
        </div>
        <div>
          <label class="block text-sm font-medium text-secondary mb-1.5">{{ t('profile.confirmNewPassword') }}</label>
          <input v-model="passwordForm.password_confirmation" type="password" class="input-field" required />
        </div>
        <p v-if="passwordMessage" class="text-sm" :class="passwordError ? 'text-red-500' : 'text-brand-500 font-medium'">{{ passwordMessage }}</p>
        <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ saving ? t('common.updating') : t('profile.updatePassword') }}</button>
      </form>
    </div>

    <div class="dashboard-card">
      <h3 class="font-semibold text-primary mb-2">{{ t('profile.legalSupport') }}</h3>
      <p class="text-secondary text-sm mb-3">{{ t('profile.legalSupportDesc') }}</p>
      <div class="flex flex-wrap gap-3">
        <router-link to="/support" class="text-sm text-brand-500 font-medium hover:underline">{{ t('nav.support') }}</router-link>
        <router-link to="/contact" class="text-sm text-brand-500 font-medium hover:underline">{{ t('nav.contact') }}</router-link>
        <router-link to="/privacy" class="text-sm text-brand-500 font-medium hover:underline">{{ t('nav.privacy') }}</router-link>
        <router-link to="/terms" class="text-sm text-brand-500 font-medium hover:underline">{{ t('nav.terms') }}</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const { t } = useI18n()
const auth = useAuthStore()
const saving = ref(false)
const profileMessage = ref('')
const passwordMessage = ref('')
const passwordError = ref(false)

const profileForm = ref({ name: '', email: '' })
const passwordForm = ref({ current_password: '', password: '', password_confirmation: '' })

onMounted(() => {
  profileForm.value = {
    name: auth.user?.name || '',
    email: auth.user?.email || '',
  }
})

async function saveProfile() {
  saving.value = true
  profileMessage.value = ''
  try {
    const { data } = await api.put('/profile', profileForm.value)
    auth.updateUser(data.user)
    profileMessage.value = t('profile.profileUpdated')
  } finally {
    saving.value = false
  }
}

async function savePassword() {
  saving.value = true
  passwordMessage.value = ''
  passwordError.value = false
  try {
    await api.put('/profile/password', passwordForm.value)
    passwordMessage.value = t('profile.passwordUpdated')
    passwordForm.value = { current_password: '', password: '', password_confirmation: '' }
  } catch (e) {
    passwordError.value = true
    passwordMessage.value = e.response?.data?.message
      || e.response?.data?.errors?.current_password?.[0]
      || t('profile.couldNotUpdatePassword')
  } finally {
    saving.value = false
  }
}
</script>
