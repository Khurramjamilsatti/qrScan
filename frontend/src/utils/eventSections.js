import { i18n } from '../i18n'

export const EVENT_SECTIONS = [
  { id: 'countdown', labelKey: 'digitalEvents.sections.countdown', icon: '⏳' },
  { id: 'schedule', labelKey: 'digitalEvents.sections.schedule', icon: '📋' },
  { id: 'location', labelKey: 'digitalEvents.sections.location', icon: '📍' },
  { id: 'rsvp', labelKey: 'digitalEvents.sections.rsvp', icon: '✅' },
  { id: 'calendar', labelKey: 'digitalEvents.sections.calendar', icon: '📅' },
  { id: 'registry', labelKey: 'digitalEvents.sections.registry', icon: '🎁' },
  { id: 'gallery', labelKey: 'digitalEvents.sections.gallery', icon: '🖼' },
  { id: 'guestbook', labelKey: 'digitalEvents.sections.guestbook', icon: '💬' },
  { id: 'livestream', labelKey: 'digitalEvents.sections.livestream', icon: '📺' },
  { id: 'video', labelKey: 'digitalEvents.sections.video', icon: '🎬' },
  { id: 'gift', labelKey: 'digitalEvents.sections.gift', icon: '🎉' },
]

export const DEFAULT_EVENT_SECTIONS_ORDER = [
  'countdown', 'schedule', 'location', 'rsvp', 'calendar', 'registry', 'gallery', 'guestbook', 'livestream', 'video', 'gift',
]

export function sectionLabel(id, t) {
  const section = EVENT_SECTIONS.find((s) => s.id === id)
  if (!section) return id
  return t ? t(section.labelKey) : section.id
}

export function defaultEventContent(eventType = 'general') {
  const t = (key) => i18n.global.t(key)
  const weddingSchedule = eventType === 'wedding' ? [
    { name: 'Mehndi', date: '', time: '', location: '', description: '' },
    { name: 'Barat', date: '', time: '', location: '', description: '' },
    { name: 'Walima', date: '', time: '', location: '', description: '' },
  ] : [{ name: '', date: '', time: '', location: '', description: '' }]

  return {
    sections_order: [...DEFAULT_EVENT_SECTIONS_ORDER],
    countdown: { enabled: true, target: '' },
    schedule: { enabled: true, title: t('digitalEvents.scheduleTitle'), items: weddingSchedule },
    location: { enabled: true, address: '', maps_url: '', venue_note: '' },
    rsvp: { enabled: true, title: t('digitalEvents.rsvpTitle'), url: '', email: '', phone: '', deadline: '', note: '' },
    calendar: { enabled: true, title: t('digitalEvents.calendarTitle') },
    registry: { enabled: false, title: t('digitalEvents.registryTitle'), items: [{ label: '', url: '' }] },
    gallery: { enabled: false, title: t('digitalEvents.galleryTitle'), items: [] },
    guestbook: { enabled: false, title: t('digitalEvents.guestbookTitle'), messages: [{ name: '', message: '' }] },
    livestream: { enabled: false, title: t('digitalEvents.livestreamTitle'), url: '', label: t('digitalEvents.watchLive') },
    video: { enabled: false, title: t('digitalEvents.videoTitle'), url: '' },
    gift: { enabled: false, title: t('digitalEvents.giftTitle'), message: '', reveal_text: t('digitalEvents.scratchReveal'), cash_link: '' },
  }
}

export function normalizeEventContent(content = {}, eventType = 'general') {
  const defaults = defaultEventContent(eventType)
  const merged = { ...defaults, ...content }
  for (const key of Object.keys(defaults)) {
    if (key === 'sections_order') continue
    merged[key] = { ...defaults[key], ...(content[key] || {}) }
  }
  if (!merged.sections_order?.length) merged.sections_order = [...DEFAULT_EVENT_SECTIONS_ORDER]
  return merged
}

export function isEventSectionEnabled(sectionId, content) {
  const c = normalizeEventContent(content)
  return !!c[sectionId]?.enabled
}

export function isEventSectionVisible(sectionId, content, livePreview = false) {
  const c = normalizeEventContent(content)
  if (!c[sectionId]?.enabled) return false
  if (livePreview) return true

  switch (sectionId) {
    case 'countdown':
      return !!(c.countdown.target || c.countdown.target === 0)
    case 'schedule':
      return (c.schedule.items || []).some((i) => i.name || i.date || i.time)
    case 'location':
      return !!(c.location.address || c.location.maps_url)
    case 'rsvp':
      return !!(c.rsvp.url || c.rsvp.email || c.rsvp.phone)
    case 'calendar':
      return true
    case 'registry':
      return (c.registry.items || []).some((i) => i.url || i.label)
    case 'gallery':
      return (c.gallery.items || []).length > 0
    case 'guestbook':
      return (c.guestbook.messages || []).some((m) => m.name || m.message)
    case 'livestream':
      return !!c.livestream.url
    case 'video':
      return !!c.video.url
    case 'gift':
      return !!(c.gift.message || c.gift.cash_link || c.gift.reveal_text)
    default:
      return false
  }
}

export const EVENT_CONTENT_KEYS = [
  'sections_order', 'countdown', 'schedule', 'location', 'rsvp', 'calendar',
  'registry', 'gallery', 'guestbook', 'livestream', 'video', 'gift',
]

export function defaultEventForm(templateId = 'simple-invite') {
  const eventType = templateId.includes('wedding') || templateId === 'desi-wedding' ? 'wedding'
    : templateId.includes('birthday') ? 'birthday'
    : templateId === 'digital-gift-card' ? 'gift'
    : 'general'

  return {
    slug: '',
    template: templateId,
    event_type: eventType,
    title: '',
    subtitle: '',
    hosts: '',
    event_date: '',
    event_end_date: '',
    venue_name: '',
    dress_code: '',
    cover_image_path: '',
    theme_color: eventType === 'wedding' ? '#c9a227' : '#e8655a',
    content: defaultEventContent(eventType),
    custom_domain_id: null,
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
  }
}
