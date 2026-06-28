/** Social platform presets with display label and icon key */
export const SOCIAL_PLATFORMS = [
  { id: 'linkedin', label: 'LinkedIn', labelKey: 'social.linkedin', icon: 'linkedin' },
  { id: 'twitter', label: 'X / Twitter', labelKey: 'social.twitter', icon: 'twitter' },
  { id: 'instagram', label: 'Instagram', labelKey: 'social.instagram', icon: 'instagram' },
  { id: 'facebook', label: 'Facebook', labelKey: 'social.facebook', icon: 'facebook' },
  { id: 'youtube', label: 'YouTube', labelKey: 'social.youtube', icon: 'youtube' },
  { id: 'tiktok', label: 'TikTok', labelKey: 'social.tiktok', icon: 'tiktok' },
  { id: 'github', label: 'GitHub', labelKey: 'social.github', icon: 'github' },
  { id: 'whatsapp', label: 'WhatsApp', labelKey: 'social.whatsapp', icon: 'whatsapp' },
  { id: 'telegram', label: 'Telegram', labelKey: 'social.telegram', icon: 'telegram' },
  { id: 'email', label: 'Email', labelKey: 'social.email', icon: 'email' },
  { id: 'website', label: 'Website', labelKey: 'social.website', icon: 'website' },
]

export const GALLERY_LAYOUTS = [
  { id: 'grid-2', label: '2 columns', labelKey: 'galleryLayouts.grid2' },
  { id: 'grid-3', label: '3 columns', labelKey: 'galleryLayouts.grid3' },
  { id: 'grid-4', label: '4 columns', labelKey: 'galleryLayouts.grid4' },
  { id: 'masonry', label: 'Masonry', labelKey: 'galleryLayouts.masonry' },
]

export const EXTRA_LINK_ICONS = [
  { id: 'link', label: 'Link', labelKey: 'templates.sections.fallbackLink' },
  { id: 'download', label: 'Download' },
  { id: 'external', label: 'External' },
  { id: 'doc', label: 'Document' },
  { id: 'shop', label: 'Shop' },
]

export function platformLabel(platform, t) {
  const p = SOCIAL_PLATFORMS.find(x => x.id === (platform || '').toLowerCase())
  if (!p) return platform || (t ? t('social.fallback') : 'Link')
  if (t && p.labelKey) return t(p.labelKey)
  return p?.label || platform || 'Link'
}

export function platformIconKey(platform) {
  const p = SOCIAL_PLATFORMS.find(x => x.id === (platform || '').toLowerCase())
  return p?.icon || 'link'
}

/** Build Google Calendar "Add event" URL from manual event fields */
export function googleCalendarUrl(event) {
  if (!event?.title) return null
  const base = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
  const params = new URLSearchParams()
  params.set('text', event.title)
  if (event.location) params.set('location', event.location)
  if (event.description) params.set('details', event.description)
  if (event.date) {
    const start = parseEventDate(event.date, event.time)
    if (start) {
      params.set('dates', `${start}/${start}`)
    }
  }
  return `${base}&${params.toString()}`
}

function parseEventDate(dateStr, timeStr) {
  try {
    const combined = timeStr ? `${dateStr} ${timeStr}` : dateStr
    const d = new Date(combined)
    if (Number.isNaN(d.getTime())) return null
    return d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')
  } catch {
    return null
  }
}
