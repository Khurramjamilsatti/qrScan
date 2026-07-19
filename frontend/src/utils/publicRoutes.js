/** Paths that must stay accessible without a valid app auth token */
const PUBLIC_PATH_PATTERNS = [
  /^\/$/,
  /^\/form\//,
  /^\/page\//,
  /^\/card\//,
  /^\/menu\//,
  /^\/invite\//,
  /^\/badge\//,
  /^\/certificate\//,
  /^\/verify\//,
  /^\/ticket\//,
  /^\/win\//,
  /^\/support$/,
  /^\/contact$/,
  /^\/privacy$/,
  /^\/terms$/,
  /^\/r\//,
  /^\/login$/,
  /^\/register$/,
]

export function isPublicAppPath(path) {
  return PUBLIC_PATH_PATTERNS.some((pattern) => pattern.test(path))
}

export function isAuthPage(path) {
  return path.startsWith('/login') || path.startsWith('/register') || path.startsWith('/admin')
}
