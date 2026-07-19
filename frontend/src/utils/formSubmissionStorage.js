const PREFIX = 'qrscan:form-submitted:'

export function hasSubmitted(slug) {
  try {
    return localStorage.getItem(PREFIX + slug) !== null
  } catch {
    return false
  }
}

export function getSubmittedMessage(slug) {
  try {
    const raw = localStorage.getItem(PREFIX + slug)
    if (!raw) return null
    return JSON.parse(raw).message || null
  } catch {
    return null
  }
}

export function markSubmitted(slug, message) {
  try {
    localStorage.setItem(PREFIX + slug, JSON.stringify({ message, at: Date.now() }))
  } catch {
    // ignore quota / private mode
  }
}

export function clearSubmitted(slug) {
  try {
    localStorage.removeItem(PREFIX + slug)
  } catch {
    // ignore
  }
}
