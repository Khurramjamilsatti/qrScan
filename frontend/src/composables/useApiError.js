import { useI18n } from 'vue-i18n'

/** Extract the first user-facing error from an axios/Laravel API response */
export function useApiError() {
  const { t } = useI18n()

  function firstError(error, fallbackKey = 'errors.failedToSave') {
    const data = error?.response?.data
    if (!data) return t(fallbackKey)

    if (data.errors && typeof data.errors === 'object') {
      for (const messages of Object.values(data.errors)) {
        const msg = Array.isArray(messages) ? messages[0] : messages
        if (msg) return msg
      }
    }

    if (data.message && data.message !== 'The given data was invalid.') {
      return data.message
    }

    return t(fallbackKey)
  }

  return { firstError }
}
