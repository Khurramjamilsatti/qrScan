import { i18n } from '../i18n'
import { AR_BADGE_DEFAULTS, AR_SCAN_TO_WIN_DEFAULTS } from '../locales/defaults-ar'

export const BADGE_TEMPLATES = [
  { id: 'classic', label: 'Classic', labelKey: 'templates.badge.classic.label', descriptionKey: 'templates.badge.classic.description', icon: '🏅', description: 'Rounded badge with ribbon footer' },
  { id: 'modern', label: 'Modern', labelKey: 'templates.badge.modern.label', descriptionKey: 'templates.badge.modern.description', icon: '✨', description: 'Clean card with accent stripe' },
  { id: 'certificate', label: 'Certificate', labelKey: 'templates.badge.certificate.label', descriptionKey: 'templates.badge.certificate.description', icon: '📜', description: 'Formal certificate layout' },
]

export const TICKET_TEMPLATES = [
  { id: 'concert', label: 'Concert', labelKey: 'templates.ticket.concert.label', descriptionKey: 'templates.ticket.concert.description', icon: '🎵', description: 'Live event ticket stub' },
  { id: 'conference', label: 'Conference', labelKey: 'templates.ticket.conference.label', descriptionKey: 'templates.ticket.conference.description', icon: '🎤', description: 'Professional pass layout' },
  { id: 'transit', label: 'Transit', labelKey: 'templates.ticket.transit.label', descriptionKey: 'templates.ticket.transit.description', icon: '🚆', description: 'Boarding pass style' },
  { id: 'sports', label: 'Sports', labelKey: 'templates.ticket.sports.label', descriptionKey: 'templates.ticket.sports.description', icon: '⚽', description: 'Stadium admission ticket' },
]

export const TICKET_TYPES = [
  { id: 'general', label: 'General admission', labelKey: 'templates.ticketTypes.general' },
  { id: 'vip', label: 'VIP', labelKey: 'templates.ticketTypes.vip' },
  { id: 'early-bird', label: 'Early bird', labelKey: 'templates.ticketTypes.earlyBird' },
  { id: 'backstage', label: 'Backstage', labelKey: 'templates.ticketTypes.backstage' },
  { id: 'student', label: 'Student', labelKey: 'templates.ticketTypes.student' },
  { id: 'other', label: 'Other', labelKey: 'templates.ticketTypes.other' },
]

export const SCAN_TEMPLATES = [
  { id: 'instant', label: 'Instant reveal', labelKey: 'templates.scan.instant.label', descriptionKey: 'templates.scan.instant.description', icon: '🎁', description: 'Tap to reveal win/lose' },
  { id: 'wheel', label: 'Spin wheel', labelKey: 'templates.scan.wheel.label', descriptionKey: 'templates.scan.wheel.description', icon: '🎡', description: 'Animated prize wheel' },
  { id: 'scratch', label: 'Scratch card', labelKey: 'templates.scan.scratch.label', descriptionKey: 'templates.scan.scratch.description', icon: '🎫', description: 'Scratch-to-reveal card' },
]

export function defaultBadgeSettings() {
  return {
    show_skills: true,
    show_dates: true,
    show_badge_id: true,
    show_verify_link: true,
    show_qr: true,
  }
}

export function defaultBadgeForm() {
  const locale = i18n.global.locale.value
  return {
    slug: '',
    title: locale === 'ar' ? AR_BADGE_DEFAULTS.title : 'Achievement Badge',
    template: 'classic',
    recipient_name: '',
    recipient_email: '',
    issuer_name: '',
    badge_id: '',
    description: '',
    skills: [],
    issue_date: '',
    expiry_date: '',
    verify_url: '',
    settings: defaultBadgeSettings(),
    theme_color: '#e8655a',
    logo_path: '',
    background_image_path: '',
    badge_image_path: '',
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
    custom_domain_id: null,
  }
}

export function defaultTicketForm() {
  return {
    slug: '',
    event_name: '',
    event_date: '',
    event_time: '',
    venue: '',
    holder_name: '',
    holder_email: '',
    ticket_type: 'general',
    seat_section: '',
    seat_row: '',
    seat_number: '',
    order_id: '',
    barcode: '',
    template: 'concert',
    terms: '',
    valid_from: '',
    valid_until: '',
    status: 'valid',
    theme_color: '#e8655a',
    logo_path: '',
    background_image_path: '',
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
    custom_domain_id: null,
  }
}

export function defaultScanToWinForm() {
  const locale = i18n.global.locale.value
  const ar = locale === 'ar'
  return {
    slug: '',
    name: '',
    description: '',
    template: 'instant',
    starts_at: '',
    ends_at: '',
    max_plays_per_day: 1,
    win_message: ar ? AR_SCAN_TO_WIN_DEFAULTS.win_message : 'Congratulations! You won!',
    lose_message: ar ? AR_SCAN_TO_WIN_DEFAULTS.lose_message : 'Better luck next time!',
    terms: '',
    prizes: [],
    theme_color: '#e8655a',
    logo_path: '',
    background_image_path: '',
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
    custom_domain_id: null,
  }
}

export function defaultPrize() {
  return { name: '', description: '', image_path: '', quantity: 10, remaining: 10, weight: 10 }
}

export function ticketTypeLabel(id, t) {
  const item = TICKET_TYPES.find((entry) => entry.id === id)
  if (!item) return id
  if (t && item.labelKey) return t(item.labelKey)
  return item.label || id
}

export function formatBadgeDate(value) {
  if (!value) return ''
  try {
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
  } catch {
    return value
  }
}
