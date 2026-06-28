import { defaultPageExtras, normalizePageContent, pickPageExtras } from './pageSections'
import { i18n } from '../i18n'
import { AR_PAGE_DEFAULTS, AR_MENU_SECTIONS } from '../locales/defaults-ar'

export { defaultPageExtras, normalizePageContent, pickPageExtras } from './pageSections'
export { PAGE_SECTIONS, DEFAULT_SECTIONS_ORDER, sectionLabel, isSectionEnabled, isSectionVisible, getExtraLinkItems } from './pageSections'

export const PAGE_TEMPLATES = {
  landing: {
    id: 'landing',
    label: 'Landing Page',
    labelKey: 'templates.page.landing.label',
    description: 'Hero headline, features, and call-to-action',
    descriptionKey: 'templates.page.landing.description',
    icon: '🚀',
    defaults: {
      headline: 'Grow your business online',
      subheadline: 'Everything you need to launch, market, and scale.',
      cta_label: 'Get Started',
      cta_url: 'https://',
      features: [
        { title: 'Fast setup', description: 'Launch in minutes, not weeks.' },
        { title: 'Track results', description: 'See who visits and converts.' },
        { title: 'Share anywhere', description: 'One link for every channel.' },
      ],
    },
  },
  portfolio: {
    id: 'portfolio',
    label: 'Portfolio',
    labelKey: 'templates.page.portfolio.label',
    description: 'Showcase projects and your story',
    descriptionKey: 'templates.page.portfolio.description',
    icon: '🎨',
    defaults: {
      headline: 'Creative Portfolio',
      about: 'Designer & developer crafting digital experiences.',
      projects: [
        { title: 'Brand Redesign', description: 'Visual identity for a tech startup.', image_path: '' },
        { title: 'Mobile App', description: 'UI/UX for a fitness platform.', image_path: '' },
      ],
    },
  },
  event: {
    id: 'event',
    label: 'Event',
    labelKey: 'templates.page.event.label',
    description: 'Event details, date, and registration',
    descriptionKey: 'templates.page.event.description',
    icon: '📅',
    defaults: {
      event_name: 'Annual Conference 2026',
      date: 'June 15, 2026 · 9:00 AM',
      location: 'San Francisco, CA',
      description: 'Join industry leaders for a day of talks, workshops, and networking.',
      cta_label: 'Register Now',
      cta_url: 'https://',
    },
  },
  simple: {
    id: 'simple',
    label: 'Simple Page',
    labelKey: 'templates.page.simple.label',
    description: 'Clean title, body text, optional button',
    descriptionKey: 'templates.page.simple.description',
    icon: '📄',
    defaults: {
      headline: 'Welcome',
      body: 'Share updates, announcements, or any message with a clean, focused page.',
      cta_label: '',
      cta_url: '',
    },
  },
}

export const TEMPLATE_LIST = Object.values(PAGE_TEMPLATES)

export function mergePageContent(templateId, existing = {}) {
  const normalized = normalizePageContent(existing)
  const defaults = defaultPageExtras()
  const tpl = PAGE_TEMPLATES[templateId] || PAGE_TEMPLATES.landing
  const locale = i18n.global.locale.value
  const tplDefaults = locale === 'ar' && AR_PAGE_DEFAULTS[templateId]
    ? AR_PAGE_DEFAULTS[templateId]
    : tpl.defaults

  return {
    ...JSON.parse(JSON.stringify(tplDefaults)),
    ...defaults,
    ...normalized,
    gallery: { ...defaults.gallery, ...(normalized.gallery || {}) },
    calendar: { ...defaults.calendar, ...(normalized.calendar || {}) },
    contact: { ...defaults.contact, ...(normalized.contact || {}) },
    social: {
      ...defaults.social,
      ...(normalized.social || {}),
      links: normalized.social?.links || [],
    },
    extra_links: {
      ...defaults.extra_links,
      ...(normalized.extra_links || {}),
      url: normalized.extra_links?.url || '',
      items: normalized.extra_links?.items || [],
    },
    sections_order: normalized.sections_order,
  }
}

export function defaultContentForTemplate(templateId) {
  return mergePageContent(templateId, {})
}

export function defaultMenuSections() {
  const locale = i18n.global.locale.value
  if (locale === 'ar') {
    return JSON.parse(JSON.stringify(AR_MENU_SECTIONS))
  }
  return [
    {
      name: 'Starters',
      items: [
        { name: 'Soup of the Day', description: 'Chef\'s seasonal selection', price: '8.00', image_path: '', tags: ['vegetarian'] },
        { name: 'Bruschetta', description: 'Tomato, basil, olive oil on toasted bread', price: '9.50', image_path: '', tags: ['vegetarian'] },
      ],
    },
    {
      name: 'Mains',
      items: [
        { name: 'Grilled Salmon', description: 'With lemon butter and seasonal vegetables', price: '22.00', image_path: '', tags: ['gluten-free'] },
        { name: 'Pasta Primavera', description: 'Fresh vegetables in garlic olive oil', price: '16.00', image_path: '', tags: ['vegetarian', 'vegan'] },
      ],
    },
  ]
}

export const CURRENCY_SYMBOLS = {
  USD: '$', EUR: '€', GBP: '£', CAD: 'C$', AUD: 'A$', JPY: '¥', INR: '₹',
}

export function formatPrice(amount, currency = 'USD') {
  const sym = CURRENCY_SYMBOLS[currency] || currency + ' '
  const num = parseFloat(amount)
  if (Number.isNaN(num)) return `${sym}${amount}`
  return `${sym}${num.toFixed(2)}`
}

export const DIETARY_TAGS = ['vegetarian', 'vegan', 'gluten-free', 'spicy', 'dairy-free', 'nut-free']
