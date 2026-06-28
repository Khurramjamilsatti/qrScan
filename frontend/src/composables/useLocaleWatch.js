import { watch } from 'vue'
import { useI18n } from 'vue-i18n'

/** Re-run callback when the user switches language */
export function useLocaleWatch(callback, { immediate = false } = {}) {
  const { locale } = useI18n()
  watch(locale, () => callback(), { immediate })
  return { locale }
}
