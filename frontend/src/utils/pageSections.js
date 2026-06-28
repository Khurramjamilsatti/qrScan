import { i18n } from '../i18n'

export const PAGE_SECTIONS = [
  { id: 'contact', label: 'Contact', labelKey: 'templates.sections.contact', icon: '✉' },
  { id: 'gallery', label: 'Photo gallery', labelKey: 'templates.sections.photoGallery', icon: '🖼' },
  { id: 'calendar', label: 'Calendar & events', labelKey: 'templates.sections.calendarEvents', icon: '📅' },
  { id: 'social', label: 'Social media', labelKey: 'templates.sections.socialMedia', icon: '🔗' },
  { id: 'extra_links', label: 'Extra links', labelKey: 'templates.sections.extraLinks', icon: '↗' },
]

export const DEFAULT_SECTIONS_ORDER = PAGE_SECTIONS.map((s) => s.id)

export const PAGE_SECTION_MAP = Object.fromEntries(PAGE_SECTIONS.map((s) => [s.id, s]))

export function sectionLabel(id, t) {
  const section = PAGE_SECTION_MAP[id]
  if (!section) return id
  if (t && section.labelKey) return t(section.labelKey)
  return section.label || id
}

export function isSectionEnabled(sectionId, content) {
  const c = normalizePageContent(content)
  switch (sectionId) {
    case 'gallery': return !!c.gallery?.enabled
    case 'calendar': return !!c.calendar?.enabled
    case 'contact': return !!c.contact?.enabled
    case 'social': return !!c.social?.enabled
    case 'extra_links': return !!c.extra_links?.enabled
    default: return false
  }
}

export function getExtraLinkItems(content = {}) {
  const c = normalizePageContent(content)
  const block = c.extra_links || {}
  const items = (block.items || []).filter((item) => (item?.url || '').trim())
  if (items.length) return items

  const sectionUrl = (block.url || '').trim()
  if (sectionUrl) {
    return [{
      label: (block.title || '').trim() || 'Link',
      url: sectionUrl,
      icon: 'link',
    }]
  }

  return []
}

export function isSectionVisible(sectionId, content) {
  const c = normalizePageContent(content)
  if (!isSectionEnabled(sectionId, c)) return false
  switch (sectionId) {
    case 'gallery':
      return (c.gallery.items?.length || 0) > 0
    case 'calendar':
      return !!(c.calendar.embed_url || c.calendar.events?.length)
    case 'contact':
      return !!(c.contact.name || c.contact.email || c.contact.phone || c.contact.address || c.contact.website)
    case 'social':
      return (c.social.links?.length || 0) > 0
    case 'extra_links':
      return getExtraLinkItems(c).length > 0
    default:
      return false
  }
}

export const PAGE_EXTRA_KEYS = ['sections_order', 'gallery', 'calendar', 'contact', 'social', 'extra_links']

export function defaultPageExtras() {
  const t = (key) => i18n.global.t(key)
  return {
    sections_order: [...DEFAULT_SECTIONS_ORDER],
    gallery: {
      enabled: false,
      title: t('templates.sections.galleryTitle'),
      layout: 'grid-3',
      items: [],
    },
    calendar: {
      enabled: false,
      title: t('templates.sections.calendarTitle'),
      embed_url: '',
      events: [],
    },
    contact: {
      enabled: false,
      name: '',
      email: '',
      phone: '',
      address: '',
      website: '',
    },
    social: {
      enabled: false,
      title: t('templates.sections.connectTitle'),
      links: [],
    },
    extra_links: {
      enabled: false,
      title: t('templates.sections.linksTitle'),
      url: '',
      items: [],
    },
  }
}

/** Migrate legacy content shapes and fill missing fields */
export function normalizePageContent(content = {}) {
  const c = { ...content }

  if (Array.isArray(c.social_links)) {
    c.social = {
      enabled: c.social?.enabled ?? c.social_links.length > 0,
      title: c.social?.title || 'Connect',
      links: c.social_links,
    }
    delete c.social_links
  }

  if (Array.isArray(c.extra_links)) {
    c.extra_links = {
      enabled: c.extra_links.length > 0,
      title: 'Links',
      url: '',
      items: c.extra_links,
    }
  }

  const defaults = defaultPageExtras()
  if (!c.social) c.social = { ...defaults.social }
  if (!c.extra_links || Array.isArray(c.extra_links)) {
    c.extra_links = { ...defaults.extra_links, ...(c.extra_links || {}) }
  } else {
    const legacyLinks = Array.isArray(c.extra_links.links) ? c.extra_links.links : null
    c.extra_links = {
      ...defaults.extra_links,
      ...c.extra_links,
      items: c.extra_links.items?.length
        ? c.extra_links.items
        : (legacyLinks || []),
    }
    delete c.extra_links.links
  }
  if (!c.gallery) c.gallery = { ...defaults.gallery }
  if (!c.calendar) c.calendar = { ...defaults.calendar }
  if (!c.contact) c.contact = { ...defaults.contact }

  const order = Array.isArray(c.sections_order) ? c.sections_order.filter(Boolean) : []
  const known = new Set(DEFAULT_SECTIONS_ORDER)
  const cleaned = order.filter((id) => known.has(id))
  for (const id of DEFAULT_SECTIONS_ORDER) {
    if (!cleaned.includes(id)) cleaned.push(id)
  }
  c.sections_order = cleaned

  return c
}

export function pickPageExtras(content = {}) {
  const normalized = normalizePageContent(content)
  const extras = {}
  for (const key of PAGE_EXTRA_KEYS) {
    extras[key] = normalized[key]
  }
  return extras
}
