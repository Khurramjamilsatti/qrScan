import { generateQrDataUrl, normalizeQrContent } from '../composables/useQrPreview'

function safeFilename(name) {
  return (name || 'qr-code').replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '-') || 'qr-code'
}

function triggerBlobDownload(blob, filename) {
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}

function qrRenderOptions(qr) {
  return {
    foreground: qr.foreground_color || '#1a1333',
    background: qr.background_color || '#ffffff',
    size: qr.size || 400,
    margin: qr.margin ?? 4,
    errorCorrectionLevel: qr.logo_path ? 'H' : (qr.error_correction || 'M'),
    logoUrl: qr.logo_path,
    backgroundImageUrl: qr.background_image_path,
    qr_shape: qr.qr_shape,
    dot_style: qr.dot_style,
    corner_style: qr.corner_style,
    frame_style: qr.frame_style,
  }
}

export async function downloadQrPng(qr, scanUrl) {
  const dataUrl = await generateQrDataUrl(scanUrl, qrRenderOptions(qr))
  if (!dataUrl) throw new Error('Could not generate PNG')
  const res = await fetch(dataUrl)
  const blob = await res.blob()
  triggerBlobDownload(blob, `${safeFilename(qr.name)}.png`)
}

/** Escape a data URL for safe use inside an SVG XML attribute. */
function xmlAttr(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
}

/**
 * SVG download wraps the same canvas render used by the live preview and PNG export.
 * This guarantees logo, background image, and custom styles appear correctly.
 */
export async function downloadQrSvg(qr, scanUrl) {
  const size = qr.size || 400
  const dataUrl = await generateQrDataUrl(normalizeQrContent(scanUrl), {
    ...qrRenderOptions(qr),
    size,
  })
  if (!dataUrl) throw new Error('Could not generate SVG')

  const href = xmlAttr(dataUrl)
  const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
  <image x="0" y="0" width="${size}" height="${size}" href="${href}" xlink:href="${href}" preserveAspectRatio="xMidYMid meet"/>
</svg>`

  triggerBlobDownload(new Blob([svg], { type: 'image/svg+xml;charset=utf-8' }), `${safeFilename(qr.name)}.svg`)
}
