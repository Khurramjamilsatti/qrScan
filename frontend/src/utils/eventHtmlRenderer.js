import { renderTemplate } from './htmlTemplate'
import { resolveStorageUrl } from './storageUrl'
import { getEventTemplateLayout } from './eventTemplates'
import { isEventSectionVisible, normalizeEventContent } from './eventSections'

const templates = import.meta.glob('../templates/events/*.html', { query: '?raw', import: 'default', eager: true })

const listEmbedStyles = `
  body { background: transparent !important; padding: 0 !important; margin: 0 !important; }
  .invite { max-width: 100% !important; margin: 0 !important; border-radius: 0 !important; box-shadow: none !important; }
  .footer { display: none !important; }
`.trim()

const publicEmbedStyles = `
  html, body {
    background: transparent !important;
    padding: 0 !important;
    margin: 0 !important;
    min-height: 0 !important;
    height: auto !important;
    overflow: hidden !important;
  }
  .invite {
    max-width: 100% !important;
    width: 100% !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
  }
  .footer { display: none !important; }
`.trim()

const TEMPLATE_VARIANT_STYLES = {
  'wedding-elegant': `
    .tpl-wedding-elegant .header { min-height: 180px; }
    .tpl-wedding-elegant h1 { font-size: 1.9rem; letter-spacing: 0.05em; }
    .tpl-wedding-elegant .ornament { font-size: 2rem; opacity: 1; }
    .tpl-wedding-elegant .meta { background: linear-gradient(180deg, #faf6f0, #fff); }
  `,
  'wedding-modern': `
    .tpl-wedding-modern .invite { border-radius: 0; }
    .tpl-wedding-modern h1 { font-family: "DM Sans", system-ui, sans-serif; font-style: normal; font-weight: 700; letter-spacing: -0.03em; }
    .tpl-wedding-modern .header .overlay { background: linear-gradient(180deg, rgba(26,19,51,.15), rgba(26,19,51,.72)); }
  `,
  'wedding-romantic': `
    .tpl-wedding-romantic .header .overlay { background: linear-gradient(180deg, rgba(190,24,93,.2), rgba(44,24,16,.58)); }
    .tpl-wedding-romantic .ornament::before { content: "♥"; }
    .tpl-wedding-romantic .ornament { font-size: 0; }
    .tpl-wedding-romantic .ornament::before { font-size: 1.75rem; }
  `,
  'wedding-minimal': `
    .tpl-wedding-minimal .header { min-height: 120px; padding: 2rem 1.5rem; }
    .tpl-wedding-minimal .ornament { display: none; }
    .tpl-wedding-minimal h1 { font-style: normal; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; font-size: 1.25rem; }
    .tpl-wedding-minimal .meta { border-bottom-style: solid; }
  `,
  'desi-wedding': `
    .tpl-desi-wedding .ornament::before { content: "🪔"; font-size: 2rem; }
    .tpl-desi-wedding .ornament { font-size: 0; }
    .tpl-desi-wedding .header { min-height: 165px; }
    .tpl-desi-wedding .header .overlay { background: linear-gradient(180deg, rgba(124,45,18,.1), rgba(68,20,6,.65)); }
  `,
  'birthday-kids': `
    .tpl-birthday-kids .header .overlay { background: linear-gradient(135deg, rgba(37,99,235,.25), rgba(244,114,182,.45)); }
    .tpl-birthday-kids .invite { border-radius: 24px; }
    .tpl-birthday-kids h1 { font-weight: 900; }
  `,
  'birthday-elegant': `
    .tpl-birthday-elegant .invite { box-shadow: 0 16px 40px rgba(30,58,95,.18); }
    .tpl-birthday-elegant .header { min-height: 150px; }
  `,
  'surprise-party': `
    .tpl-surprise-party .header .overlay { background: linear-gradient(135deg, rgba(124,58,237,.35), rgba(249,115,22,.45)); }
  `,
  'baby-shower': `
    .tpl-baby-shower .header .overlay { background: linear-gradient(180deg, rgba(252,231,243,.2), rgba(165,180,252,.45)); }
  `,
  'gender-reveal': `
    .tpl-gender-reveal .header .overlay { background: linear-gradient(90deg, rgba(96,165,250,.4), rgba(249,168,212,.4)); }
  `,
  'corporate-event': `
    .tpl-corporate-event .badge::before { content: "Executive Event"; }
    .tpl-corporate-event .header-inner { text-align: left; }
  `,
  'retirement': `
    .tpl-retirement .header .overlay { background: linear-gradient(180deg, rgba(120,53,15,.15), rgba(68,34,8,.55)); }
  `,
  'eid-greeting': `
    .tpl-eid-greeting .header .overlay { background: linear-gradient(180deg, rgba(20,83,45,.2), rgba(20,83,45,.6)); }
  `,
  'christmas-card': `
    .tpl-christmas-card .header .overlay { background: linear-gradient(180deg, rgba(20,83,45,.25), rgba(185,28,28,.45)); }
  `,
  'new-year': `
    .tpl-new-year .header .overlay { background: linear-gradient(180deg, rgba(30,27,75,.2), rgba(99,102,241,.55)); }
  `,
  'memorial': `
    .tpl-memorial h1 { font-style: italic; font-weight: 500; }
    .tpl-memorial .header .overlay { background: linear-gradient(180deg, rgba(55,65,81,.15), rgba(31,41,55,.65)); }
  `,
  'digital-gift-card': `
    .tpl-digital-gift-card .scratch-card__cover { background: linear-gradient(135deg, #be185d, #f472b6); }
    .tpl-digital-gift-card .header { min-height: 130px; }
  `,
}

function templateVariantStyles(templateId) {
  return TEMPLATE_VARIANT_STYLES[templateId] || ''
}

const FONT_IMPORT = `@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&display=swap');`

function typographyStyles(layout) {
  const sans = "'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
  const serif = "'Fraunces', Georgia, 'Times New Roman', serif"
  const heading = ['wedding', 'memorial'].includes(layout) ? serif : sans
  const bodySize = layout === 'corporate' ? '15px' : '16px'

  return `
    ${FONT_IMPORT}
    body { font-family: ${sans}; font-size: ${bodySize}; line-height: 1.55; -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
    .invite h1 { font-family: ${heading}; font-size: 1.75rem; font-weight: 600; line-height: 1.2; letter-spacing: -0.02em; }
    .invite .subtitle { font-size: 1rem; line-height: 1.5; font-weight: 400; }
    .invite .meta { font-size: 0.9375rem; line-height: 1.65; }
    .invite .meta p { font-size: inherit; margin: 0; }
    .invite .meta p + p { margin-top: 0.5rem; }
    .invite .meta-hosts { font-weight: 600; }
    .invite .meta-date, .invite .meta-venue, .invite .meta-dress {
      display: flex; align-items: flex-start; justify-content: center; gap: 0.4rem; text-align: center;
    }
    .invite .meta-icon { flex-shrink: 0; line-height: 1.5; }
    .invite .block-title { font-size: 0.8125rem; font-weight: 700; letter-spacing: 0.07em; }
    .invite .btn { font-size: 0.9375rem; font-weight: 600; letter-spacing: 0.01em; }
    .invite .countdown__num { font-size: 1.5rem; font-weight: 700; }
    .invite .countdown__label { font-size: 0.6875rem; font-weight: 600; letter-spacing: 0.05em; }
    .invite .schedule-item__head { font-size: 0.9375rem; }
    .invite .schedule-item__desc, .invite .location-address, .invite .block-desc, .invite .block-meta, .invite .block-contacts { font-size: 0.875rem; line-height: 1.55; }
    .invite .guest-msg { font-size: 0.9375rem; line-height: 1.55; }
    .invite .registry-link { font-size: 0.875rem; font-weight: 600; }
    .invite .scratch-card__inner { font-size: 1rem; line-height: 1.5; }
    .invite--wedding h1 { font-style: italic; font-weight: 500; }
    .invite--corporate h1 { font-family: ${sans}; font-weight: 700; font-style: normal; letter-spacing: -0.03em; }
    .invite--birthday h1 { font-family: ${sans}; font-weight: 800; letter-spacing: -0.03em; }
  `.trim()
}

function publicPageStyles(layout, themeColor) {
  const tc = themeColor || '#e8655a'
  const isDark = layout === 'corporate'

  return `
    .invite .header { padding: 2.5rem 1.5rem !important; min-height: 152px !important; }
    .invite .header-inner { text-align: center; }
    .invite .meta {
      padding: 1.125rem 1.5rem !important;
      text-align: center !important;
      background: ${isDark
    ? 'linear-gradient(180deg, color-mix(in srgb, ' + tc + ' 8%, #1e293b), #1e293b)'
    : 'linear-gradient(180deg, color-mix(in srgb, ' + tc + ' 6%, #fff), #fff)'} !important;
      border-bottom: 1px solid ${isDark ? '#334155' : `color-mix(in srgb, ${tc} 12%, #e8e4f0)`} !important;
    }
    .invite .meta-hosts {
      font-size: 0.75rem !important;
      font-weight: 700 !important;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: ${tc} !important;
      margin-bottom: 0.5rem !important;
    }
    .invite .sections {
      padding: 1.125rem 1.25rem 1.5rem !important;
      gap: 0.875rem !important;
    }
    .invite .block {
      background: ${isDark ? 'rgba(255,255,255,0.04)' : `color-mix(in srgb, ${tc} 4%, #fff)`};
      border: 1px solid ${isDark ? '#334155' : `color-mix(in srgb, ${tc} 10%, transparent)`};
      border-radius: 14px;
      padding: 1rem 0.875rem !important;
    }
    .invite .block-title {
      margin-bottom: 0.75rem !important;
      text-align: center;
    }
    .invite .block--rsvp, .invite .block--calendar, .invite .block--livestream { text-align: center; }
    .invite .countdown { gap: 0.375rem !important; }
    .invite .countdown__unit {
      background: ${isDark ? 'rgba(255,255,255,0.06)' : '#fff'};
      border-radius: 10px;
      padding: 0.5rem 0.25rem;
      box-shadow: 0 2px 8px ${isDark ? 'rgba(0,0,0,0.15)' : `color-mix(in srgb, ${tc} 8%, transparent)`};
    }
    .invite .schedule-item { padding: 0.625rem 0 !important; }
    .invite .schedule-item:last-child { border-bottom: none; }
    .invite .gallery-grid { gap: 6px !important; }
    .invite .btn { box-shadow: 0 3px 12px color-mix(in srgb, ${tc} 22%, transparent); }
    .invite .guest-msg { padding: 0.625rem 0.75rem !important; margin-bottom: 0.5rem; }
    .invite .guest-msg p { margin: 0.2rem 0 0; }
    .invite .guest-msg strong { display: block; line-height: 1.3; }
    .invite .guest-msg:last-child { margin-bottom: 0; }
    .invite .scratch-card__cover { padding: 1.75rem 1rem !important; }
  `.trim()
}

function editorPreviewStyles(livePreview, publicView, compact, layout, themeColor) {
  if (!livePreview || publicView || compact) return ''

  const tc = themeColor || '#e8655a'
  const layoutBg = {
    wedding: '#f8f4ee',
    simple: '#f4f2f8',
    birthday: '#fff8f0',
    celebration: '#faf5ff',
    corporate: '#0f172a',
    holiday: '#f0fdf4',
    gift: '#fdf2f8',
    memorial: '#f3f4f6',
  }
  const bodyBg = layoutBg[layout] || '#ffffff'
  const isDark = layout === 'corporate'

  return `
    html, body { background: ${bodyBg} !important; }
    .invite {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 !important;
      border: none !important;
      border-radius: 0 !important;
      box-shadow: none !important;
      overflow: hidden !important;
    }
    .invite .header {
      padding: 2.5rem 1.5rem !important;
      min-height: 156px !important;
    }
    .invite .header-inner { text-align: center; }
    .invite .ornament { margin-bottom: 0.625rem; }
    .invite .meta {
      padding: 1.25rem 1.5rem !important;
      text-align: center !important;
      background: ${isDark
    ? 'linear-gradient(180deg, color-mix(in srgb, ' + tc + ' 8%, #1e293b), #1e293b)'
    : 'linear-gradient(180deg, color-mix(in srgb, ' + tc + ' 7%, #fff), #fff)'} !important;
      border-bottom: 1px solid ${isDark ? '#334155' : `color-mix(in srgb, ${tc} 14%, #e8e4f0)`} !important;
    }
    .invite .meta-hosts {
      font-size: 0.75rem !important;
      font-weight: 700 !important;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: ${tc} !important;
      margin-bottom: 0.625rem !important;
    }
    .invite .meta-hosts::after {
      content: '';
      display: block;
      width: 2.5rem;
      height: 2px;
      margin: 0.625rem auto 0;
      border-radius: 999px;
      background: ${tc};
      opacity: 0.45;
    }
    .invite .meta-date, .invite .meta-venue, .invite .meta-dress, .invite .meta-end {
      color: ${isDark ? '#cbd5e1' : 'inherit'};
    }
    .invite .sections {
      padding: 1.35rem 1.25rem 2rem !important;
      gap: 1.125rem !important;
    }
    .invite .block {
      background: ${isDark ? 'rgba(255,255,255,0.04)' : `color-mix(in srgb, ${tc} 5%, #fff)`};
      border: 1px solid ${isDark ? '#334155' : `color-mix(in srgb, ${tc} 12%, transparent)`};
      border-radius: 16px;
      padding: 1.125rem 1rem !important;
    }
    .invite .block-title {
      margin-bottom: 0.875rem !important;
      text-align: center;
    }
    .invite .block--rsvp, .invite .block--calendar { text-align: center; }
    .invite .countdown {
      gap: 0.5rem !important;
    }
    .invite .countdown__unit {
      background: ${isDark ? 'rgba(255,255,255,0.06)' : '#fff'};
      border-radius: 12px;
      padding: 0.625rem 0.35rem;
      box-shadow: 0 2px 10px ${isDark ? 'rgba(0,0,0,0.2)' : `color-mix(in srgb, ${tc} 10%, transparent)`};
    }
    .invite .schedule-item {
      padding: 0.875rem 0 !important;
      ${isDark ? 'border-bottom-color: #334155 !important;' : ''}
    }
    .invite .schedule-item:last-child { border-bottom: none; }
    .invite .btn {
      margin-top: 0.5rem;
      box-shadow: 0 4px 14px color-mix(in srgb, ${tc} 25%, transparent);
    }
    .invite .guest-msg {
      border-radius: 12px;
      background: ${isDark ? 'rgba(255,255,255,0.05)' : `color-mix(in srgb, ${tc} 4%, #fff)`};
    }
    .footer { display: none !important; }
  `.trim()
}

function templateHtml(id) {
  const layout = getEventTemplateLayout(id)
  return templates[`../templates/events/${layout}.html`] || templates['../templates/events/simple.html']
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    if (Number.isNaN(d.getTime())) return dateStr
    return d.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
  } catch {
    return dateStr
  }
}

function formatTime(dateStr) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    if (Number.isNaN(d.getTime())) return ''
    return d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
  } catch {
    return ''
  }
}

function headerBgStyle(coverUrl, themeColor) {
  if (coverUrl) {
    return `background-image: url('${coverUrl}'); background-size: cover; background-position: center;`
  }
  return `background: linear-gradient(135deg, ${themeColor}, color-mix(in srgb, ${themeColor} 45%, #1a1333));`
}

function metaHtml({ hosts, eventDate, eventEndDate, venueName, dressCode }) {
  const parts = []
  if (hosts) parts.push(`<p class="meta-hosts">${hosts}</p>`)
  if (eventDate) {
    const dateLine = formatDate(eventDate)
    const timeLine = formatTime(eventDate)
    parts.push(`<p class="meta-date"><span class="meta-icon" aria-hidden="true">📅</span><span>${dateLine}${timeLine ? ` · ${timeLine}` : ''}</span></p>`)
  }
  if (eventEndDate) parts.push(`<p class="meta-end">Until ${formatDate(eventEndDate)}</p>`)
  if (venueName) parts.push(`<p class="meta-venue"><span class="meta-icon" aria-hidden="true">📍</span><span>${venueName}</span></p>`)
  if (dressCode) parts.push(`<p class="meta-dress"><span class="meta-icon" aria-hidden="true">👔</span><span>${dressCode}</span></p>`)
  if (!parts.length) return ''
  return `<div class="meta">${parts.join('')}</div>`
}

function countdownHtml(target, livePreview) {
  const ts = target || (livePreview ? new Date(Date.now() + 7 * 86400000).toISOString() : '')
  if (!ts) return ''
  return `<section class="block block--countdown" data-countdown="${ts}">
    <h2 class="block-title">Countdown</h2>
    <div class="countdown" id="countdown">
      <div class="countdown__unit"><span class="countdown__num" data-unit="days">--</span><span class="countdown__label">Days</span></div>
      <div class="countdown__unit"><span class="countdown__num" data-unit="hours">--</span><span class="countdown__label">Hours</span></div>
      <div class="countdown__unit"><span class="countdown__num" data-unit="mins">--</span><span class="countdown__label">Mins</span></div>
      <div class="countdown__unit"><span class="countdown__num" data-unit="secs">--</span><span class="countdown__label">Secs</span></div>
    </div>
    <script>
    (function(){
      var el=document.getElementById('countdown'); if(!el) return;
      var target=new Date('${ts}').getTime();
      function tick(){
        var diff=Math.max(0,target-Date.now());
        var d=Math.floor(diff/86400000), h=Math.floor((diff%86400000)/3600000), m=Math.floor((diff%3600000)/60000), s=Math.floor((diff%60000)/1000);
        var map={days:d,hours:h,mins:m,secs:s};
        Object.keys(map).forEach(function(k){ var n=el.querySelector('[data-unit="'+k+'"]'); if(n) n.textContent=String(map[k]).padStart(2,'0'); });
      }
      tick(); setInterval(tick,1000);
    })();
    </script>
  </section>`
}

function scheduleHtml(block, livePreview) {
  const items = (block.items || []).filter((i) => livePreview || i.name || i.date || i.time)
  if (!items.length && !livePreview) return ''
  const list = (items.length ? items : [{ name: 'Ceremony', date: '', time: '4:00 PM', location: 'Main Hall', description: '' }])
    .map((item) => `<article class="schedule-item">
      <div class="schedule-item__head"><strong>${item.name || 'Event'}</strong>${item.time ? `<span>${item.time}</span>` : ''}</div>
      ${item.date ? `<div class="schedule-item__date">${item.date}</div>` : ''}
      ${item.location ? `<div class="schedule-item__loc">📍 ${item.location}</div>` : ''}
      ${item.description ? `<p class="schedule-item__desc">${item.description}</p>` : ''}
    </article>`).join('')
  return `<section class="block block--schedule"><h2 class="block-title">${block.title || 'Schedule'}</h2><div class="schedule-list">${list}</div></section>`
}

function locationHtml(block) {
  if (!block.address && !block.maps_url) return ''
  const mapLink = block.maps_url ? `<a class="btn btn--outline" href="${block.maps_url}" target="_blank" rel="noopener">Open in Maps</a>` : ''
  return `<section class="block block--location">
    <h2 class="block-title">Location</h2>
    ${block.address ? `<p class="location-address">${block.address}</p>` : ''}
    ${block.venue_note ? `<p class="location-note">${block.venue_note}</p>` : ''}
    ${mapLink}
  </section>`
}

function rsvpHtml(block) {
  const btn = block.url
    ? `<a class="btn btn--primary" href="${block.url}" target="_blank" rel="noopener">${block.title || 'RSVP'}</a>`
    : ''
  const contacts = [block.email && `✉ ${block.email}`, block.phone && `📞 ${block.phone}`].filter(Boolean).join(' · ')
  return `<section class="block block--rsvp">
    <h2 class="block-title">${block.title || 'RSVP'}</h2>
    ${block.note ? `<p class="block-desc">${block.note}</p>` : ''}
    ${block.deadline ? `<p class="block-meta">Please respond by ${block.deadline}</p>` : ''}
    ${btn}
    ${contacts ? `<p class="block-contacts">${contacts}</p>` : ''}
  </section>`
}

function calendarHtml(event, block) {
  if (!event.event_date && !event.eventDate) return ''
  const title = encodeURIComponent(event.title || 'Event')
  const start = (event.event_date || event.eventDate || '').replace(/[-:]/g, '').replace(/\.\d{3}/, '')
  const loc = encodeURIComponent(event.venue_name || event.venueName || '')
  const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${start}/${start}&location=${loc}`
  return `<section class="block block--calendar">
    <h2 class="block-title">${block.title || 'Add to Calendar'}</h2>
    <a class="btn btn--outline" href="${url}" target="_blank" rel="noopener">📅 Add to Calendar</a>
  </section>`
}

function registryHtml(block) {
  const items = (block.items || []).filter((i) => i.url || i.label)
  if (!items.length) return ''
  const links = items.map((i) => `<a class="registry-link" href="${i.url || '#'}" target="_blank" rel="noopener">${i.label || 'Registry'}</a>`).join('')
  return `<section class="block block--registry"><h2 class="block-title">${block.title || 'Gift Registry'}</h2><div class="registry-links">${links}</div></section>`
}

function galleryHtml(block) {
  const items = (block.items || []).map((i) => resolveStorageUrl(i.image_path || i.path || i)).filter(Boolean)
  if (!items.length) return ''
  const imgs = items.map((url) => `<div class="gallery-item" style="background-image:url('${url}')"></div>`).join('')
  return `<section class="block block--gallery"><h2 class="block-title">${block.title || 'Photos'}</h2><div class="gallery-grid">${imgs}</div></section>`
}

function guestbookHtml(block, livePreview) {
  const messages = (block.messages || []).filter((m) => livePreview || m.name || m.message)
  if (!messages.length && !livePreview) return ''
  const list = (messages.length ? messages : [{ name: 'Guest', message: 'Wishing you all the best!' }])
    .map((m) => `<article class="guest-msg"><strong>${m.name || 'Guest'}</strong><p>${m.message || ''}</p></article>`).join('')
  return `<section class="block block--guestbook"><h2 class="block-title">${block.title || 'Guest Messages'}</h2><div class="guest-list">${list}</div></section>`
}

function livestreamHtml(block) {
  if (!block.url) return ''
  return `<section class="block block--livestream">
    <h2 class="block-title">${block.title || 'Live Stream'}</h2>
    <a class="btn btn--primary" href="${block.url}" target="_blank" rel="noopener">${block.label || 'Watch Live'}</a>
  </section>`
}

function videoHtml(block) {
  if (!block.url) return ''
  let embed = ''
  const url = block.url
  if (url.includes('youtube.com') || url.includes('youtu.be')) {
    const id = url.match(/(?:v=|youtu\.be\/)([\w-]+)/)?.[1]
    if (id) embed = `<iframe class="video-embed" src="https://www.youtube.com/embed/${id}" allowfullscreen></iframe>`
  } else if (url.includes('vimeo.com')) {
    const id = url.match(/vimeo\.com\/(\d+)/)?.[1]
    if (id) embed = `<iframe class="video-embed" src="https://player.vimeo.com/video/${id}" allowfullscreen></iframe>`
  }
  if (!embed) embed = `<a class="btn btn--outline" href="${url}" target="_blank" rel="noopener">Watch Video</a>`
  return `<section class="block block--video"><h2 class="block-title">${block.title || 'Video Message'}</h2>${embed}</section>`
}

function giftHtml(block) {
  const cash = block.cash_link ? `<a class="btn btn--primary" href="${block.cash_link}" target="_blank" rel="noopener">Send a Gift</a>` : ''
  return `<section class="block block--gift">
    <h2 class="block-title">${block.title || 'Your Surprise'}</h2>
    <div class="scratch-card" onclick="this.classList.add('revealed')">
      <div class="scratch-card__cover">${block.reveal_text || 'Tap to reveal'}</div>
      <div class="scratch-card__inner">${block.message || 'Happy celebrations!'}</div>
    </div>
    ${cash}
  </section>`
}

const sectionRenderers = {
  countdown: (event, c, live) => countdownHtml(c.countdown?.target || event.event_date || event.eventDate, live),
  schedule: (_, c, live) => scheduleHtml(c.schedule, live),
  location: (_, c) => locationHtml(c.location),
  rsvp: (_, c) => rsvpHtml(c.rsvp),
  calendar: (event, c) => calendarHtml(event, c.calendar),
  registry: (_, c) => registryHtml(c.registry),
  gallery: (_, c) => galleryHtml(c.gallery),
  guestbook: (_, c, live) => guestbookHtml(c.guestbook, live),
  livestream: (_, c) => livestreamHtml(c.livestream),
  video: (_, c) => videoHtml(c.video),
  gift: (_, c) => giftHtml(c.gift),
}

function sectionsHtml(event, content, livePreview) {
  const c = normalizeEventContent(content, event.event_type || event.eventType)
  const order = c.sections_order || []
  return order.map((id) => {
    if (!isEventSectionVisible(id, c, livePreview)) return ''
    const render = sectionRenderers[id]
    return render ? render(event, c, livePreview) : ''
  }).join('')
}

export function renderEventHtml(event) {
  const template = event.template || 'simple-invite'
  const layout = getEventTemplateLayout(template)
  const themeColor = event.themeColor || event.theme_color || '#e8655a'
  const coverUrl = resolveStorageUrl(event.coverImage || event.cover_image_path)
  const title = event.title || 'You\'re Invited'
  const subtitle = event.subtitle || ''
  const livePreview = !!event.livePreview
  const inviteUrl = event.inviteUrl || event.invite_url || (event.slug ? `/invite/${event.slug}` : '')
  const content = event.content || {}
  const embedStyles = [
    typographyStyles(layout),
    event.publicView ? publicEmbedStyles : ((event.compact || event.listView) ? listEmbedStyles : ''),
    event.publicView ? publicPageStyles(layout, themeColor) : '',
    editorPreviewStyles(livePreview, event.publicView, event.compact || event.listView, layout, themeColor),
    templateVariantStyles(template),
  ].filter(Boolean).join('\n')

  const html = templateHtml(template)

  return renderTemplate(html, {
    title,
    subtitle,
    template,
    layout,
    themeColor,
    inviteUrl,
  }, {
    headerBgStyle: headerBgStyle(coverUrl, themeColor),
    metaHtml: metaHtml({
      hosts: event.hosts,
      eventDate: event.event_date || event.eventDate,
      eventEndDate: event.event_end_date || event.eventEndDate,
      venueName: event.venue_name || event.venueName,
      dressCode: event.dress_code || event.dressCode,
    }),
    sectionsHtml: sectionsHtml(event, content, livePreview),
    embedStyles,
  })
}
