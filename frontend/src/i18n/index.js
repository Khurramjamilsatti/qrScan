import { createI18n } from 'vue-i18n'
import en from '../locales/en.json'
import ar from '../locales/ar.json'

const STORAGE_KEY = 'qrscan-locale'
const SUPPORTED = ['en', 'ar']

function detectLocale() {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved && SUPPORTED.includes(saved)) return saved
  const browser = (navigator.language || 'en').split('-')[0]
  return SUPPORTED.includes(browser) ? browser : 'en'
}

export const i18n = createI18n({
  legacy: false,
  locale: detectLocale(),
  fallbackLocale: 'en',
  messages: { en, ar },
})

export function applyDocumentLocale(locale) {
  const isRtl = locale === 'ar'
  document.documentElement.lang = locale
  document.documentElement.dir = isRtl ? 'rtl' : 'ltr'
}

export function setAppLocale(locale) {
  if (!SUPPORTED.includes(locale)) return
  i18n.global.locale.value = locale
  localStorage.setItem(STORAGE_KEY, locale)
  applyDocumentLocale(locale)
}

applyDocumentLocale(i18n.global.locale.value)
