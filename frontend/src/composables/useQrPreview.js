import { resolveStorageUrl } from '../utils/storageUrl'
import {
  renderStyledQr,
  renderStandardQr,
  usesCustomQrRendering,
} from '../utils/renderStyledQr'
import { pickQrStyleFields } from '../utils/qrStyleOptions'

function loadImage(src) {
  return new Promise((resolve, reject) => {
    if (!src) return resolve(null)
    const resolved = resolveStorageUrl(src)
    const img = new Image()
    const sameOrigin = resolved.startsWith('/') || resolved.startsWith(window.location.origin) || resolved.startsWith('data:')
    if (!sameOrigin) img.crossOrigin = 'anonymous'
    img.onload = () => resolve(img)
    img.onerror = () => reject(new Error(`Failed to load image: ${resolved}`))
    img.src = resolved
  })
}

/** Ensure encoded QR payload is a valid absolute URL when possible */
export function normalizeQrContent(content) {
  if (!content || typeof content !== 'string') return content
  const trimmed = content.trim()
  if (/^https?:\/\//i.test(trimmed)) return trimmed
  if (/^\/\//.test(trimmed)) return `https:${trimmed}`
  if (/^[a-z0-9+.-]+:/i.test(trimmed)) return trimmed
  if (trimmed.startsWith('/')) {
    return `${window.location.origin.replace(/\/$/, '')}${trimmed}`
  }
  return trimmed
}

export async function generateQrDataUrl(text, options = {}) {
  const payload = normalizeQrContent(text)
  if (!payload) return null

  const {
    foreground = '#000000',
    background = '#ffffff',
    size = 280,
    margin = 4,
    errorCorrectionLevel = 'M',
    logoUrl = null,
    backgroundImageUrl = null,
    scanOptimized = false,
  } = options

  const styleFields = pickQrStyleFields(options)
  const renderOpts = {
    foreground,
    background,
    size,
    margin,
    errorCorrectionLevel,
    ...styleFields,
  }

  try {
    const [logoImage, backgroundImage] = await Promise.all([
      logoUrl ? loadImage(logoUrl).catch(() => null) : null,
      backgroundImageUrl ? loadImage(backgroundImageUrl).catch(() => null) : null,
    ])

    const withImages = { ...renderOpts, logoImage, backgroundImage }

    if (scanOptimized || !usesCustomQrRendering(renderOpts)) {
      return await renderStandardQr(payload, withImages)
    }

    return await renderStyledQr(payload, withImages)
  } catch {
    return null
  }
}

export function useQrPreview() {
  return { generateDataUrl: generateQrDataUrl, normalizeQrContent }
}
