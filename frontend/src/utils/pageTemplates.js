import { defaultPageExtras, normalizePageContent, pickPageExtras } from './pageSections'
import { i18n } from '../i18n'
import { AR_PAGE_DEFAULTS, AR_MENU_SECTIONS } from '../locales/defaults-ar'

export { defaultPageExtras, normalizePageContent, pickPageExtras } from './pageSections'
export { PAGE_SECTIONS, DEFAULT_SECTIONS_ORDER, sectionLabel, isSectionEnabled, isSectionVisible, getExtraLinkItems } from './pageSections'

export const PAGE_TEMPLATE_CATEGORIES = [
  { id: 'all', labelKey: 'templates.categories.all' },
  { id: 'business', labelKey: 'templates.categories.business' },
  { id: 'creative', labelKey: 'templates.categories.creative' },
  { id: 'events', labelKey: 'templates.categories.events' },
  { id: 'personal', labelKey: 'templates.categories.personal' },
]

const sharedFeature = (title, description) => ({ title, description })

export const PAGE_TEMPLATES = {
  landing: {
    id: 'landing',
    category: 'business',
    layout: 'landing',
    label: 'Landing Page',
    labelKey: 'templates.page.landing.label',
    description: 'Hero headline, features, and call-to-action',
    descriptionKey: 'templates.page.landing.description',
    icon: '🚀',
    thumbnail: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=480&h=300&fit=crop',
    popular: true,
    defaults: {
      headline: 'Grow your business online',
      subheadline: 'Everything you need to launch, market, and scale.',
      cta_label: 'Get Started',
      cta_url: 'https://',
      features: [
        sharedFeature('Fast setup', 'Launch in minutes, not weeks.'),
        sharedFeature('Track results', 'See who visits and converts.'),
        sharedFeature('Share anywhere', 'One link for every channel.'),
      ],
    },
  },
  restaurant: {
    id: 'restaurant',
    category: 'business',
    layout: 'landing',
    label: 'Restaurant',
    labelKey: 'templates.page.restaurant.label',
    description: 'Menu highlights, hours, and reservations',
    descriptionKey: 'templates.page.restaurant.description',
    icon: '🍽',
    thumbnail: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=480&h=300&fit=crop',
    popular: true,
    defaults: {
      headline: 'The Garden Kitchen',
      subheadline: 'Farm-to-table dining · Open daily 11am–10pm',
      cta_label: 'Reserve a Table',
      cta_url: 'https://',
      features: [
        sharedFeature('Seasonal menu', 'Fresh ingredients from local farms.'),
        sharedFeature('Private dining', 'Events and celebrations welcome.'),
        sharedFeature('Takeaway', 'Order online for pickup.'),
      ],
    },
  },
  product: {
    id: 'product',
    category: 'business',
    layout: 'landing',
    label: 'Product Launch',
    labelKey: 'templates.page.product.label',
    description: 'Showcase a product with features and buy CTA',
    descriptionKey: 'templates.page.product.description',
    icon: '📦',
    thumbnail: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=480&h=300&fit=crop',
    defaults: {
      headline: 'Introducing Pro Headphones X1',
      subheadline: 'Studio-quality sound. All-day comfort.',
      cta_label: 'Buy Now — $199',
      cta_url: 'https://',
      features: [
        sharedFeature('40hr battery', 'Listen all week on one charge.'),
        sharedFeature('Active noise cancel', 'Block the world out.'),
        sharedFeature('Free shipping', 'Delivered in 2–3 business days.'),
      ],
    },
  },
  pricing: {
    id: 'pricing',
    category: 'business',
    layout: 'pricing',
    label: 'Pricing',
    labelKey: 'templates.page.pricing.label',
    description: 'Compare plans and drive sign-ups',
    descriptionKey: 'templates.page.pricing.description',
    icon: '💳',
    thumbnail: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=480&h=300&fit=crop',
    defaults: {
      headline: 'Simple, transparent pricing',
      subheadline: 'Choose the plan that fits your team.',
      plans: [
        { name: 'Starter', price: '$9/mo', description: 'For individuals', features: ['1 page', 'Basic analytics', 'Email support'] },
        { name: 'Pro', price: '$29/mo', description: 'For growing teams', features: ['Unlimited pages', 'Advanced analytics', 'Priority support'] },
        { name: 'Business', price: '$79/mo', description: 'For organizations', features: ['Custom domains', 'API access', 'Dedicated manager'] },
      ],
      cta_label: 'Start free trial',
      cta_url: 'https://',
    },
  },
  team: {
    id: 'team',
    category: 'business',
    layout: 'team',
    label: 'Team',
    labelKey: 'templates.page.team.label',
    description: 'Introduce your team members',
    descriptionKey: 'templates.page.team.description',
    icon: '👥',
    thumbnail: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=480&h=300&fit=crop',
    defaults: {
      headline: 'Meet our team',
      about: 'Passionate people building great products together.',
      members: [
        { name: 'Alex Morgan', role: 'CEO', bio: '10+ years in product leadership.', image_path: '' },
        { name: 'Sam Chen', role: 'CTO', bio: 'Engineer, architect, coffee enthusiast.', image_path: '' },
        { name: 'Jordan Lee', role: 'Design Lead', bio: 'Crafting delightful user experiences.', image_path: '' },
      ],
    },
  },
  portfolio: {
    id: 'portfolio',
    category: 'creative',
    layout: 'portfolio',
    label: 'Portfolio',
    labelKey: 'templates.page.portfolio.label',
    description: 'Showcase projects and your story',
    descriptionKey: 'templates.page.portfolio.description',
    icon: '🎨',
    thumbnail: 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?w=480&h=300&fit=crop',
    popular: true,
    defaults: {
      headline: 'Creative Portfolio',
      about: 'Designer & developer crafting digital experiences.',
      projects: [
        { title: 'Brand Redesign', description: 'Visual identity for a tech startup.', image_path: '' },
        { title: 'Mobile App', description: 'UI/UX for a fitness platform.', image_path: '' },
      ],
    },
  },
  announcement: {
    id: 'announcement',
    category: 'creative',
    layout: 'simple',
    label: 'Announcement',
    labelKey: 'templates.page.announcement.label',
    description: 'Bold news or update with a clear CTA',
    descriptionKey: 'templates.page.announcement.description',
    icon: '📣',
    thumbnail: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=480&h=300&fit=crop',
    defaults: {
      headline: 'We\'re launching something new',
      body: 'After months of work, we\'re excited to share our latest update with you. Stay tuned for more details.',
      cta_label: 'Learn more',
      cta_url: 'https://',
    },
  },
  video: {
    id: 'video',
    category: 'creative',
    layout: 'video',
    label: 'Video',
    labelKey: 'templates.page.video.label',
    description: 'Embed a video with title and description',
    descriptionKey: 'templates.page.video.description',
    icon: '🎬',
    thumbnail: 'https://images.unsplash.com/photo-1611162616475-46b635cb6868?w=480&h=300&fit=crop',
    defaults: {
      headline: 'Watch our story',
      video_url: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
      description: 'A short film about who we are and what we build.',
      cta_label: 'Subscribe',
      cta_url: 'https://',
    },
  },
  event: {
    id: 'event',
    category: 'events',
    layout: 'event',
    label: 'Event',
    labelKey: 'templates.page.event.label',
    description: 'Event details, date, and registration',
    descriptionKey: 'templates.page.event.description',
    icon: '📅',
    thumbnail: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=480&h=300&fit=crop',
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
    category: 'personal',
    layout: 'simple',
    label: 'Simple Page',
    labelKey: 'templates.page.simple.label',
    description: 'Clean title, body text, optional button',
    descriptionKey: 'templates.page.simple.description',
    icon: '📄',
    thumbnail: 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=480&h=300&fit=crop',
    popular: true,
    defaults: {
      headline: 'Welcome',
      body: 'Share updates, announcements, or any message with a clean, focused page.',
      cta_label: '',
      cta_url: '',
    },
  },
  links: {
    id: 'links',
    category: 'personal',
    layout: 'links',
    label: 'Link in Bio',
    labelKey: 'templates.page.links.label',
    description: 'Profile bio with stacked link buttons',
    descriptionKey: 'templates.page.links.description',
    icon: '🔗',
    thumbnail: 'https://images.unsplash.com/photo-1611162616305-c69b3fa7fbe0?w=480&h=300&fit=crop',
    popular: true,
    defaults: {
      headline: '@yourname',
      bio: 'Creator · Designer · Coffee lover ☕',
      profile_links: [
        { label: 'My Website', url: 'https://' },
        { label: 'Latest Project', url: 'https://' },
        { label: 'Book a Call', url: 'https://' },
      ],
    },
  },
  resume: {
    id: 'resume',
    category: 'personal',
    layout: 'resume',
    label: 'Resume',
    labelKey: 'templates.page.resume.label',
    description: 'CV with skills and experience',
    descriptionKey: 'templates.page.resume.description',
    icon: '📋',
    thumbnail: 'https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=480&h=300&fit=crop',
    defaults: {
      headline: 'Jane Developer',
      about: 'Full-stack engineer with 8 years building web products.',
      skills: ['JavaScript', 'Vue.js', 'Node.js', 'PostgreSQL', 'AWS'],
      experience: [
        { title: 'Senior Engineer', company: 'Tech Co', period: '2022 – Present', description: 'Led platform migration and API redesign.' },
        { title: 'Software Developer', company: 'Startup Inc', period: '2018 – 2022', description: 'Built customer-facing dashboard and mobile app.' },
      ],
      cta_label: 'Download PDF',
      cta_url: 'https://',
    },
  },
}

export const TEMPLATE_LIST = Object.values(PAGE_TEMPLATES)

export function getPageTemplateLayout(templateId) {
  return PAGE_TEMPLATES[templateId]?.layout || templateId
}

export function templatesByCategory(categoryId) {
  if (!categoryId || categoryId === 'all') return TEMPLATE_LIST
  return TEMPLATE_LIST.filter((t) => t.category === categoryId)
}

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
