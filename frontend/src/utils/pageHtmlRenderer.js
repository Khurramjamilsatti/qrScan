import { renderTemplate } from './htmlTemplate'
import { resolveStorageUrl } from './storageUrl'
import { normalizePageContent, isSectionVisible, getExtraLinkItems } from './pageSections'
import { getPageTemplateLayout } from './pageTemplates'

const templates = import.meta.glob('../templates/pages/*.html', { query: '?raw', import: 'default', eager: true })

function buildSectionStyles(themeColor) {
  return `
  .sec { margin-top: 20px; padding-top: 16px; border-top: 1px solid #e8e4f0; }
  .sec h3 { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: ${themeColor}; margin-bottom: 10px; }
  .sec p, .sec a, .sec div { font-size: 13px; color: #5c5470; line-height: 1.5; }
  .sec a { color: ${themeColor}; text-decoration: none; display: block; margin-top: 4px; }
  .gallery { display: grid; grid-template-columns: repeat(3,1fr); gap: 6px; }
  .gallery div { aspect-ratio: 1; border-radius: 8px; background-size: cover; background-position: center; background-color: #faf8fd; }
  .pill-row { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
  .pill { font-size: 11px; font-weight: 600; padding: 5px 10px; border-radius: 999px; border: 1px solid #e8e4f0; background: #faf8fd; color: #1a1333; text-decoration: none; }
  .event-row { padding: 8px 10px; background: #faf8fd; border-radius: 8px; border: 1px solid #e8e4f0; margin-top: 6px; }
  .link-btn { display: flex; align-items: center; gap: 8px; padding: 10px 12px; border-radius: 8px; border: 1px solid #e8e4f0; background: #faf8fd; text-decoration: none; color: #1a1333; font-size: 13px; font-weight: 600; margin-top: 6px; }
  `.trim()
}

const publicEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .page { max-width: 100% !important; margin: 0 !important; border: none !important; border-radius: 0 !important; box-shadow: none !important; }
`.trim()

const listEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
`.trim()

function templateHtml(id) {
  const layout = getPageTemplateLayout(id)
  return templates[`../templates/pages/${layout}.html`] || templates['../templates/pages/landing.html']
}

function headerBgStyle(backgroundImage, themeColor) {
  if (backgroundImage) {
    return `background-image: url('${backgroundImage}'); background-size: cover; background-position: center;`
  }
  return `background: linear-gradient(135deg, ${themeColor}, color-mix(in srgb, ${themeColor} 60%, #6b4fa0));`
}

function logoHtml(logoUrl) {
  if (!logoUrl) return ''
  return `<img class="logo" src="${logoUrl}" alt="">`
}

function ctaHtml(label, url) {
  if (!label || !url) return ''
  return `<a class="cta" href="${url}">${label}</a>`
}

function buildSectionsHtml(content, themeColor) {
  const normalized = normalizePageContent(content)
  const order = normalized.sections_order || []
  const parts = []

  for (const sectionId of order) {
    if (!isSectionVisible(sectionId, normalized)) continue

    if (sectionId === 'gallery') {
      const items = normalized.gallery?.items || []
      if (!items.length) continue
      const imgs = items.map((item) => {
        const url = item.image_path ? resolveStorageUrl(item.image_path) : ''
        return url ? `<div style="background-image:url('${url}')"></div>` : '<div></div>'
      }).join('')
      parts.push(`<section class="sec"><h3>${normalized.gallery?.title || 'Gallery'}</h3><div class="gallery">${imgs}</div></section>`)
    } else if (sectionId === 'calendar') {
      const events = normalized.calendar?.events || []
      if (!events.length && !normalized.calendar?.embed_url) continue
      let inner = ''
      if (normalized.calendar?.embed_url) {
        inner += `<p>📅 Calendar embedded</p>`
      }
      events.forEach((ev) => {
        inner += `<div class="event-row"><strong>${ev.title || ''}</strong>${ev.date ? `<div>📅 ${ev.date}${ev.time ? ` · ${ev.time}` : ''}</div>` : ''}${ev.location ? `<div>📍 ${ev.location}</div>` : ''}</div>`
      })
      parts.push(`<section class="sec"><h3>${normalized.calendar?.title || 'Calendar'}</h3>${inner}</section>`)
    } else if (sectionId === 'contact') {
      const c = normalized.contact || {}
      if (!c.name && !c.email && !c.phone && !c.address && !c.website) continue
      parts.push(`<section class="sec"><h3>Contact</h3>
        ${c.name ? `<div><strong>${c.name}</strong></div>` : ''}
        ${c.email ? `<a href="mailto:${c.email}">✉ ${c.email}</a>` : ''}
        ${c.phone ? `<a href="tel:${c.phone}">📞 ${c.phone}</a>` : ''}
        ${c.address ? `<div>📍 ${c.address}</div>` : ''}
        ${c.website ? `<a href="${c.website}">🌐 ${c.website}</a>` : ''}
      </section>`)
    } else if (sectionId === 'social') {
      const links = normalized.social?.links || []
      if (!links.length) continue
      const pills = links.map((s) => `<a class="pill" href="${s.url}">${s.platform || s.url}</a>`).join('')
      parts.push(`<section class="sec"><h3>${normalized.social?.title || 'Connect'}</h3><div class="pill-row">${pills}</div></section>`)
    } else if (sectionId === 'extra_links') {
      const items = getExtraLinkItems(normalized)
      if (!items.length) continue
      const links = items.map((l) => `<a class="link-btn" href="${l.url}">${l.label || 'Link'}</a>`).join('')
      parts.push(`<section class="sec"><h3>${normalized.extra_links?.title || 'Links'}</h3>${links}</section>`)
    }
  }

  return parts.join('')
}

export function renderPageHtml(page) {
  const template = page.template || 'landing'
  const themeColor = page.themeColor || page.theme_color || '#e8655a'
  const content = normalizePageContent(page.content || {})
  const logoUrl = resolveStorageUrl(page.logo || page.logo_path)
  const bgUrl = resolveStorageUrl(page.backgroundImage || page.background_image_path)
  const title = page.title || ''

  const sectionsHtml = buildSectionsHtml(content, themeColor)

  const vars = {
    hasSections: !!sectionsHtml,
    themeColor,
    headline: content.headline || title,
    subheadline: content.subheadline || '',
    about: content.about || '',
    eventName: content.event_name || title,
    date: content.date || '',
    location: content.location || '',
    description: content.description || '',
    body: content.body || '',
    bio: content.bio || '',
    video_url: content.video_url || '',
  }

  const rawVars = {
    logoHtml: logoHtml(logoUrl),
    headerBgStyle: headerBgStyle(bgUrl, themeColor),
    sectionStyles: buildSectionStyles(themeColor)
      + (page.listView ? `\n${listEmbedStyles}` : (page.publicView ? `\n${publicEmbedStyles}` : '')),
    ctaHtml: ctaHtml(content.cta_label, content.cta_url),
    sectionsHtml,
    subheadline: vars.subheadline,
    about: vars.about,
    date: vars.date,
    location: vars.location,
    description: vars.description,
    body: vars.body,
    bio: vars.bio,
  }

  if (template === 'landing' || getPageTemplateLayout(template) === 'landing') {
    const features = content.features || []
    rawVars.featuresHtml = features.length
      ? `<div class="features">${features.map((f) => `<div class="feature"><strong>${f.title || ''}</strong><span>${f.description || ''}</span></div>`).join('')}</div>`
      : ''
  }

  if (getPageTemplateLayout(template) === 'portfolio') {
    const projects = content.projects || []
    rawVars.projectsHtml = projects.length
      ? projects.map((p) => {
        const img = p.image_path ? resolveStorageUrl(p.image_path) : ''
        return `<div class="project">${img ? `<div class="project-img" style="background-image:url('${img}')"></div>` : ''}<div class="project-body"><div class="project-title">${p.title || ''}</div><div class="project-desc">${p.description || ''}</div></div></div>`
      }).join('')
      : ''
  }

  const layout = getPageTemplateLayout(template)
  if (layout === 'pricing') {
    const plans = content.plans || []
    rawVars.plansHtml = plans.length
      ? plans.map((p) => `<div class="plan"><div class="plan-name">${p.name || ''}</div><div class="plan-price">${p.price || ''}</div>${p.description ? `<div class="plan-desc">${p.description}</div>` : ''}${p.features?.length ? `<ul>${p.features.map((f) => `<li>${f}</li>`).join('')}</ul>` : ''}</div>`).join('')
      : ''
  }
  if (layout === 'team') {
    const members = content.members || []
    rawVars.membersHtml = members.length
      ? members.map((m) => {
        const img = m.image_path ? resolveStorageUrl(m.image_path) : ''
        return `<div class="member">${img ? `<div class="member-img" style="background-image:url('${img}')"></div>` : ''}<div class="member-body"><div class="member-name">${m.name || ''}</div><div class="member-role">${m.role || ''}</div>${m.bio ? `<div class="member-bio">${m.bio}</div>` : ''}</div></div>`
      }).join('')
      : ''
  }
  if (layout === 'video') {
    vars.videoUrl = content.video_url || ''
    rawVars.videoHtml = content.video_url
      ? `<div class="video-wrap"><iframe src="${content.video_url}" frameborder="0" allowfullscreen></iframe></div>`
      : ''
  }
  if (layout === 'links') {
    vars.bio = content.bio || ''
    const links = content.profile_links || []
    rawVars.linksHtml = links.length
      ? links.map((l) => `<a class="profile-link" href="${l.url || '#'}">${l.label || 'Link'}</a>`).join('')
      : ''
  }
  if (layout === 'resume') {
    const skills = content.skills || []
    rawVars.skillsHtml = skills.length ? `<div class="skills">${skills.map((s) => `<span class="skill">${s}</span>`).join('')}</div>` : ''
    const experience = content.experience || []
    rawVars.experienceHtml = experience.length
      ? experience.map((e) => `<div class="exp"><div class="exp-title">${e.title || ''} · ${e.company || ''}</div>${e.period ? `<div class="exp-period">${e.period}</div>` : ''}${e.description ? `<div class="exp-desc">${e.description}</div>` : ''}</div>`).join('')
      : ''
  }

  return renderTemplate(templateHtml(template), vars, rawVars)
}
