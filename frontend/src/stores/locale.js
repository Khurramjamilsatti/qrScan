import { defineStore } from 'pinia'
import { computed } from 'vue'
import { i18n, setAppLocale } from '../i18n'

export const useLocaleStore = defineStore('locale', () => {
  const current = computed(() => i18n.global.locale.value)
  const isRtl = computed(() => current.value === 'ar')

  const locales = [
    { code: 'en', label: 'English', native: 'English' },
    { code: 'ar', label: 'Arabic', native: 'العربية' },
  ]

  function setLocale(code) {
    setAppLocale(code)
  }

  function toggleLocale() {
    setLocale(current.value === 'en' ? 'ar' : 'en')
  }

  return { current, isRtl, locales, setLocale, toggleLocale }
})
