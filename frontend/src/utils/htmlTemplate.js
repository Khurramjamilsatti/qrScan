export function escapeHtml(text) {
  return String(text ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

export function renderTemplate(template, vars = {}, rawVars = {}) {
  let html = template

  html = html.replace(/\{\{#if (\w+)\}\}([\s\S]*?)\{\{\/if\}\}/g, (_, key, block) => {
    const val = vars[key] ?? rawVars[key]
    return val ? block : ''
  })

  for (const [key, val] of Object.entries(rawVars)) {
    html = html.replaceAll(`{{{${key}}}}`, val ?? '')
  }

  for (const [key, val] of Object.entries(vars)) {
    html = html.replaceAll(`{{${key}}}`, escapeHtml(val))
  }

  return html
}
