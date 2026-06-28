/**
 * Normalize storage URLs to same-origin `/storage/...` paths so Vite proxy
 * serves them without CORS issues (required for canvas QR compositing).
 */
export function resolveStorageUrl(url) {
  if (!url) return ''
  if (url.startsWith('/storage/')) return url
  if (url.startsWith('data:') || url.startsWith('blob:')) return url

  try {
    const parsed = new URL(url, window.location.origin)
    const idx = parsed.pathname.indexOf('/storage/')
    if (idx !== -1) return parsed.pathname.slice(idx)
  } catch {
    /* keep original */
  }

  return url
}
