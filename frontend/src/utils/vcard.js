/**
 * Download a business card vCard file by slug.
 */
export async function downloadVcard(slug, filename) {
  const res = await fetch(`/api/card/${slug}/vcard`)
  if (!res.ok) throw new Error('Could not generate vCard')

  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename || `${slug}.vcf`
  document.body.appendChild(a)
  a.click()
  a.remove()
  URL.revokeObjectURL(url)
}
