import { renderTemplate } from './htmlTemplate'
import { resolveStorageUrl } from './storageUrl'
import { formatPrice } from './pageTemplates'
import { getMenuTemplateLayout } from './menuTemplates'

const templates = import.meta.glob('../templates/menus/*.html', { query: '?raw', import: 'default', eager: true })

const listEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .footer { display: none !important; }
`.trim()

const publicEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .menu { max-width: 100% !important; margin: 0 !important; border: none !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; }
  .footer { display: none !important; }
  .info { flex-direction: column; align-items: flex-start; gap: 6px; padding: 10px 16px; font-size: 11px; line-height: 1.45; }
  .info span { display: block; width: 100%; word-break: break-word; }
  .sections { padding-bottom: 8px; }
  .section--grid .section-items { align-items: stretch; }
  .item--grid { display: flex; flex-direction: column; height: 100%; }
  .item--grid .item-body { flex: 1; display: flex; flex-direction: column; padding: 10px; }
  .item--grid .item-head { flex-direction: row; justify-content: space-between; align-items: flex-start; gap: 6px; }
  .item--grid .item-head strong { flex: 1; min-width: 0; font-size: 12px; line-height: 1.3; }
  .item--grid .price { flex-shrink: 0; font-size: 12px; }
  .item--grid .desc { flex: 1; min-height: 2.7em; }
  .item--grid .tags { margin-top: auto; padding-top: 6px; }
`.trim()

function templateHtml(id) {
  const layout = getMenuTemplateLayout(id)
  return templates[`../templates/menus/${layout}.html`] || templates['../templates/menus/classic.html']
}

function headerBgStyle(backgroundImage, themeColor) {
  if (backgroundImage) {
    return `background-image: url('${backgroundImage}'); background-size: cover; background-position: center;`
  }
  return `background: linear-gradient(135deg, ${themeColor}, color-mix(in srgb, ${themeColor} 55%, #1a1333));`
}

function logoHtml(logoUrl) {
  if (!logoUrl) return ''
  return `<img src="${logoUrl}" alt="" class="logo">`
}

function infoHtml({ location, phone, hours }) {
  const parts = []
  if (location) parts.push(`<span>📍 ${location}</span>`)
  if (phone) parts.push(`<span>📞 ${phone}</span>`)
  if (hours) parts.push(`<span>🕐 ${hours}</span>`)
  if (!parts.length) return ''
  return `<div class="info">${parts.join('')}</div>`
}

function tagHtml(tags = []) {
  if (!tags.length) return ''
  return tags.map((tag) => `<span class="tag">${tag}</span>`).join('')
}

function itemHtml(item, currency, layout) {
  const name = item.name || 'Menu item'
  const price = item.price ? formatPrice(item.price, currency) : ''
  const img = resolveStorageUrl(item.image_path)
  const tags = tagHtml(item.tags || [])
  const desc = item.description || ''

  if (layout === 'grid') {
    return `<article class="item item--grid">
      ${img ? `<div class="item-img" style="background-image:url('${img}')"></div>` : '<div class="item-img item-img--empty"></div>'}
      <div class="item-body">
        <div class="item-head"><strong>${name}</strong>${price ? `<span class="price">${price}</span>` : ''}</div>
        ${desc ? `<p class="desc">${desc}</p>` : ''}
        ${tags ? `<div class="tags">${tags}</div>` : ''}
      </div>
    </article>`
  }

  if (layout === 'minimal') {
    return `<article class="item item--minimal">
      <div class="item-head"><strong>${name}</strong>${price ? `<span class="price">${price}</span>` : ''}</div>
      ${desc ? `<p class="desc">${desc}</p>` : ''}
      ${tags ? `<div class="tags">${tags}</div>` : ''}
    </article>`
  }

  return `<article class="item">
    ${img ? `<div class="item-img" style="background-image:url('${img}')"></div>` : ''}
    <div class="item-body">
      <div class="item-head"><strong>${name}</strong>${price ? `<span class="price">${price}</span>` : ''}</div>
      ${desc ? `<p class="desc">${desc}</p>` : ''}
      ${tags ? `<div class="tags">${tags}</div>` : ''}
    </div>
  </article>`
}

function sectionsHtml(sections = [], currency = 'USD', layout = 'classic', livePreview = false) {
  const list = sections?.length ? sections : (livePreview ? [{ name: 'Starters', items: [{ name: 'Soup of the Day', description: 'Chef\'s seasonal selection', price: '8.00', tags: ['vegetarian'] }] }] : [])

  return list.map((section) => {
    const items = section.items?.length ? section.items : (livePreview ? [{ name: 'Sample dish', description: 'Description', price: '12.00' }] : [])
    const itemsHtml = items.map((item) => itemHtml(item, currency, layout)).join('')
    const gridClass = layout === 'grid' ? ' section--grid' : ''
    return `<section class="section${gridClass}">
      <h2 class="section-title">${section.name || 'Section'}</h2>
      <div class="section-items">${itemsHtml}</div>
    </section>`
  }).join('')
}

export function renderMenuHtml(menu) {
  const template = menu.template || 'classic'
  const layout = getMenuTemplateLayout(template)
  const themeColor = menu.themeColor || menu.theme_color || '#e8655a'
  const currency = menu.currency || 'USD'
  const logoUrl = resolveStorageUrl(menu.logo || menu.logo_path)
  const bgUrl = resolveStorageUrl(menu.backgroundImage || menu.background_image_path)
  const name = menu.name || 'Your Restaurant'
  const livePreview = !!menu.livePreview

  const vars = {
    name,
    description: menu.description || '',
    themeColor,
    menuUrl: menu.menuUrl || menu.menu_url || `/menu/${menu.slug || 'your-menu'}`,
  }

  const rawVars = {
    logoHtml: logoHtml(logoUrl),
    headerBgStyle: headerBgStyle(bgUrl, themeColor),
    infoHtml: infoHtml({ location: menu.location, phone: menu.phone, hours: menu.hours }),
    sectionsHtml: sectionsHtml(menu.sections || [], currency, layout, livePreview),
    embedStyles: menu.listView ? listEmbedStyles : (menu.publicView ? publicEmbedStyles : ''),
    description: vars.description,
  }

  return renderTemplate(templateHtml(template), vars, rawVars)
}
