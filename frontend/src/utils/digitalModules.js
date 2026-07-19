import { i18n } from '../i18n'
import { AR_BADGE_DEFAULTS, AR_SCAN_TO_WIN_DEFAULTS } from '../locales/defaults-ar'

export const CARD_TEMPLATE_CATEGORIES = [
  { id: 'all', labelKey: 'templates.categories.all' },
  { id: 'business', labelKey: 'templates.categories.business' },
  { id: 'creative', labelKey: 'templates.categories.creative' },
  { id: 'personal', labelKey: 'templates.categories.personal' },
]

export const CARD_TEMPLATES = [
  { id: 'classic', category: 'business', layout: 'classic', label: 'Classic', labelKey: 'templates.card.classic.label', descriptionKey: 'templates.card.classic.description', icon: '👤', description: 'Header banner with overlapping avatar', thumbnail: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=480&h=300&fit=crop', popular: true },
  { id: 'executive', category: 'business', layout: 'classic', label: 'Executive', labelKey: 'templates.card.executive.label', descriptionKey: 'templates.card.executive.description', icon: '💼', description: 'Polished layout for senior professionals', thumbnail: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=480&h=300&fit=crop', popular: true },
  { id: 'corporate', category: 'business', layout: 'classic', label: 'Corporate', labelKey: 'templates.card.corporate.label', descriptionKey: 'templates.card.corporate.description', icon: '🏢', description: 'Structured brand-forward card', thumbnail: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=480&h=300&fit=crop' },
  { id: 'modern', category: 'creative', layout: 'modern', label: 'Modern', labelKey: 'templates.card.modern.label', descriptionKey: 'templates.card.modern.description', icon: '✨', description: 'Soft gradient with circular photo', thumbnail: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=480&h=300&fit=crop', popular: true },
  { id: 'creative', category: 'creative', layout: 'modern', label: 'Creative', labelKey: 'templates.card.creative.label', descriptionKey: 'templates.card.creative.description', icon: '🎨', description: 'Centered layout for designers', thumbnail: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=480&h=300&fit=crop' },
  { id: 'gradient', category: 'creative', layout: 'modern', label: 'Gradient', labelKey: 'templates.card.gradient.label', descriptionKey: 'templates.card.gradient.description', icon: '🌈', description: 'Vibrant gradient header band', thumbnail: 'https://images.unsplash.com/photo-1557683316-973673baf926?w=480&h=300&fit=crop' },
  { id: 'photo', category: 'creative', layout: 'modern', label: 'Photo Focus', labelKey: 'templates.card.photo.label', descriptionKey: 'templates.card.photo.description', icon: '📸', description: 'Large photo, minimal text', thumbnail: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=480&h=300&fit=crop' },
  { id: 'bold', category: 'business', layout: 'bold', label: 'Bold', labelKey: 'templates.card.bold.label', descriptionKey: 'templates.card.bold.description', icon: '⚡', description: 'Full-width color bar and large name', thumbnail: 'https://images.unsplash.com/photo-1556761175-5973dc0f32e7?w=480&h=300&fit=crop' },
  { id: 'split', category: 'creative', layout: 'bold', label: 'Split', labelKey: 'templates.card.split.label', descriptionKey: 'templates.card.split.description', icon: '◧', description: 'Strong header with accent stripe', thumbnail: 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?w=480&h=300&fit=crop' },
  { id: 'sidebar', category: 'business', layout: 'bold', label: 'Sidebar', labelKey: 'templates.card.sidebar.label', descriptionKey: 'templates.card.sidebar.description', icon: '▌', description: 'Bold bar with side logo placement', thumbnail: 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?w=480&h=300&fit=crop' },
  { id: 'minimal', category: 'personal', layout: 'minimal', label: 'Minimal', labelKey: 'templates.card.minimal.label', descriptionKey: 'templates.card.minimal.description', icon: '◻️', description: 'Clean layout without header band', thumbnail: 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=480&h=300&fit=crop', popular: true },
  { id: 'compact', category: 'personal', layout: 'minimal', label: 'Compact', labelKey: 'templates.card.compact.label', descriptionKey: 'templates.card.compact.description', icon: '📇', description: 'Dense contact-focused layout', thumbnail: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=480&h=300&fit=crop' },
]

export function getCardTemplateLayout(templateId) {
  const tpl = CARD_TEMPLATES.find((t) => t.id === templateId)
  return tpl?.layout || templateId || 'classic'
}

export const CERTIFICATE_TEMPLATES = [
  { id: 'classic', label: 'Classic', labelKey: 'templates.certificate.classic.label', descriptionKey: 'templates.certificate.classic.description', icon: '📜', description: 'Traditional bordered certificate', thumbnail: 'https://images.unsplash.com/photo-1588196749597-9ff075ee6b5b?w=480&h=300&fit=crop', popular: true },
  { id: 'formal', label: 'Formal', labelKey: 'templates.certificate.formal.label', descriptionKey: 'templates.certificate.formal.description', icon: '🎓', description: 'Serif typography, academic style', thumbnail: 'https://images.unsplash.com/photo-1434030214721-735e3686a28e?w=480&h=300&fit=crop', popular: true },
  { id: 'modern', label: 'Modern', labelKey: 'templates.certificate.modern.label', descriptionKey: 'templates.certificate.modern.description', icon: '✨', description: 'Clean card with shadow', thumbnail: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=480&h=300&fit=crop' },
  { id: 'elegant', label: 'Elegant', labelKey: 'templates.certificate.elegant.label', descriptionKey: 'templates.certificate.elegant.description', icon: '🏛️', description: 'Minimal formal frame', thumbnail: 'https://images.unsplash.com/photo-1606761568499-6d2451b23fc6?w=480&h=300&fit=crop' },
]

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

export const CERTIFICATE_FONTS = [
  { id: 'instrument-serif', labelKey: 'digitalCertificates.fonts.instrumentSerif', css: "'Instrument Serif', Georgia, serif", pdfFamily: 'DejaVu Serif, serif' },
  { id: 'georgia', labelKey: 'digitalCertificates.fonts.georgia', css: "Georgia, 'Times New Roman', serif", pdfFamily: 'DejaVu Serif, serif' },
  { id: 'times', labelKey: 'digitalCertificates.fonts.times', css: "'Times New Roman', Times, serif", pdfFamily: 'DejaVu Serif, serif' },
  { id: 'dm-sans', labelKey: 'digitalCertificates.fonts.dmSans', css: "'DM Sans', system-ui, sans-serif", pdfFamily: 'DejaVu Sans, sans-serif' },
  { id: 'palatino', labelKey: 'digitalCertificates.fonts.palatino', css: "Palatino, 'Palatino Linotype', Georgia, serif", pdfFamily: 'DejaVu Serif, serif' },
  { id: 'arabic', labelKey: 'digitalCertificates.fonts.arabic', css: "'IBM Plex Sans Arabic', 'DM Sans', sans-serif", pdfFamily: 'DejaVu Sans, sans-serif' },
]

export function certificateFontCss(fontId) {
  return CERTIFICATE_FONTS.find((f) => f.id === fontId)?.css || CERTIFICATE_FONTS[0].css
}

export function certificateFontPdfFamily(fontId) {
  return CERTIFICATE_FONTS.find((f) => f.id === fontId)?.pdfFamily || CERTIFICATE_FONTS[0].pdfFamily
}

export function defaultCertificateSettings() {
  return {
    show_dates: true,
    show_certificate_id: true,
    show_qr: true,
    font_family: 'instrument-serif',
    text_color: '#1a1333',
    background_color: '#fdfbf7',
  }
}

export function defaultCertificateForm() {
  return {
    slug: '',
    title: 'Certificate of Completion',
    template: 'classic',
    recipient_name: '',
    recipient_email: '',
    award_title: '',
    issuer_name: '',
    description: '',
    completion_date: '',
    issue_date: new Date().toISOString().slice(0, 10),
    expiry_date: '',
    settings: defaultCertificateSettings(),
    theme_color: '#1a1333',
    logo_path: '',
    seal_path: '',
    instructor_signature_path: '',
    organization_signature_path: '',
    background_image_path: '',
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
    custom_domain_id: null,
  }
}

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
