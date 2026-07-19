import { renderTemplate } from './htmlTemplate'
import { resolveStorageUrl } from './storageUrl'
import { getCardTemplateLayout } from './digitalModules'

const templates = import.meta.glob('../templates/cards/*.html', { query: '?raw', import: 'default', eager: true })

const publicEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .card { max-width: 100% !important; margin: 0 !important; border: none !important; border-radius: 0 !important; box-shadow: none !important; }
`.trim()

const listEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .footer { display: none !important; }
`.trim()

function templateHtml(id) {
  const layout = getCardTemplateLayout(id)
  return templates[`../templates/cards/${layout}.html`] || templates['../templates/cards/classic.html']
}

function roleLine(jobTitle, company) {
  if (jobTitle && company) return `${jobTitle} · ${company}`
  return jobTitle || company || ''
}

function avatarHtml(photoUrl, initial, themeColor) {
  if (photoUrl) return `<img src="${photoUrl}" alt="">`
  return initial || '?'
}

function logoHtml(logoUrl, className = 'logo') {
  if (!logoUrl) return ''
  return `<img src="${logoUrl}" alt="" class="${className}">`
}

function contactsHtml({ email, phone, website, address }) {
  const rows = []
  if (email) rows.push(`<div class="contact">✉ ${email}</div>`)
  if (phone) rows.push(`<div class="contact">📞 ${phone}</div>`)
  if (website) rows.push(`<div class="contact">🌐 ${cleanUrl(website)}</div>`)
  if (address) rows.push(`<div class="contact">📍 ${address}</div>`)
  if (!rows.length) rows.push('<div class="contact">✉ email@example.com</div>')
  return rows.join('')
}

function socialHtml(links = []) {
  const items = links.filter((s) => s?.url)
  if (!items.length) return ''
  const pills = items.map((s) => `<a class="pill" href="${s.url}">${s.platform || s.url}</a>`).join('')
  return `<div class="social">${pills}</div>`
}

function headerBgStyle(backgroundImage, themeColor) {
  if (backgroundImage) {
    return `background-image: url('${backgroundImage}'); background-size: cover; background-position: center;`
  }
  return `background: linear-gradient(135deg, ${themeColor}, color-mix(in srgb, ${themeColor} 65%, #fff));`
}

function cleanUrl(url) {
  return url?.replace(/^https?:\/\//, '') || ''
}

export function renderCardHtml(card) {
  const themeColor = card.themeColor || card.theme_color || '#e8655a'
  const template = card.template || 'classic'
  const photoUrl = resolveStorageUrl(card.photo || card.photo_path)
  const logoUrl = resolveStorageUrl(card.logo || card.logo_path)
  const bgUrl = resolveStorageUrl(card.backgroundImage || card.background_image_path)
  const fullName = card.fullName || card.full_name || 'Your Name'
  const initial = fullName.charAt(0)?.toUpperCase() || '?'

  const vars = {
    fullName,
    tagline: card.tagline || '',
    bio: card.bio || '',
    roleLine: roleLine(card.jobTitle || card.job_title, card.company),
    cardUrl: card.cardUrl || card.card_url || `/card/${card.slug || 'your-name'}`,
    themeColor,
  }

  const rawVars = {
    avatarHtml: avatarHtml(photoUrl, initial, themeColor),
    logoHtml: logoHtml(logoUrl),
    contactsHtml: contactsHtml({
      email: card.email,
      phone: card.phone,
      website: card.website,
      address: card.address,
    }),
    socialHtml: socialHtml(card.socialLinks || card.social_links || []),
    headerBgStyle: headerBgStyle(bgUrl, themeColor),
    embedStyles: card.listView ? listEmbedStyles : (card.publicView ? publicEmbedStyles : ''),
    tagline: vars.tagline,
    bio: vars.bio,
  }

  return renderTemplate(templateHtml(template), vars, rawVars)
}
