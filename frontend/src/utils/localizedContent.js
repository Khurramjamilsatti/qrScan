export const LOCALES = ['en', 'ar']

export function isLocaleMap(value) {
  if (!value || typeof value !== 'object' || Array.isArray(value)) return false
  const keys = Object.keys(value)
  return keys.length > 0 && keys.every((k) => LOCALES.includes(k))
}

export function resolveLocale(value, locale = 'en') {
  if (value === null || value === undefined) return ''
  if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean') {
    return value
  }
  if (Array.isArray(value)) {
    return value.map((item) => resolveLocale(item, locale))
  }
  if (typeof value === 'object' && isLocaleMap(value)) {
    return value[locale] ?? value.en ?? Object.values(value)[0] ?? ''
  }
  if (typeof value === 'object') {
    const out = {}
    for (const [key, item] of Object.entries(value)) {
      out[key] = resolveLocale(item, locale)
    }
    return out
  }
  return value
}

export function extractObject(raw, locale = 'en') {
  const out = {}
  for (const [key, value] of Object.entries(raw || {})) {
    out[key] = resolveLocale(value, locale)
  }
  return out
}

export function mergeLocaleField(existing, value, locale = 'en') {
  if (isLocaleMap(existing)) {
    return { ...existing, [locale]: value }
  }
  if (typeof existing === 'string' && existing) {
    return { en: existing, [locale]: value }
  }
  return { [locale]: value }
}

export function mergeObject(raw, edited, locale = 'en') {
  const out = { ...(raw || {}) }
  for (const [key, value] of Object.entries(edited || {})) {
    out[key] = mergeLocaleField(out[key], value, locale)
  }
  return out
}

export function extractStats(rawStats, locale = 'en') {
  return (rawStats || []).map((stat) => ({
    label: resolveLocale(stat?.label, locale),
    value: stat?.value ?? '',
  }))
}

export function mergeStats(rawStats, editedStats, locale = 'en') {
  return (editedStats || []).map((stat, index) => ({
    label: mergeLocaleField(rawStats?.[index]?.label, stat.label, locale),
    value: stat.value ?? rawStats?.[index]?.value ?? '',
  }))
}
