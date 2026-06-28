/**
 * Pretty share URL (/r/slug) vs direct scan URL (/api/r/slug).
 * QR codes must encode scan_url — same pattern as /api/qr/{code}.
 */

export function shortLinkShareUrl(domains, slug, customDomainId, link) {
  if (link?.short_url) return link.short_url
  if (!slug) return ''
  return `${domains.baseUrlFor(customDomainId)}/r/${slug}`
}

export function shortLinkScanUrl(domains, slug, customDomainId, link) {
  if (link?.scan_url) return link.scan_url
  if (!slug) return ''
  return `${domains.baseUrlFor(customDomainId)}/api/r/${slug}`
}
