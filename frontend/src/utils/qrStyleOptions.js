export const QR_STYLE_DEFAULTS = {
  qr_shape: 'square',
  dot_style: 'square',
  corner_style: 'sharp',
  frame_style: 'none',
}

export const QR_SHAPE_OPTIONS = [
  { value: 'square', label: 'Square', labelKey: 'qrStyle.shapes.square' },
  { value: 'rounded', label: 'Rounded', labelKey: 'qrStyle.shapes.rounded' },
  { value: 'circle', label: 'Circle', labelKey: 'qrStyle.shapes.circle' },
  { value: 'hexagon', label: 'Hexagon', labelKey: 'qrStyle.shapes.hexagon' },
  { value: 'diamond', label: 'Diamond', labelKey: 'qrStyle.shapes.diamond' },
]

export const DOT_STYLE_OPTIONS = [
  { value: 'square', label: 'Square', labelKey: 'qrStyle.dots.square' },
  { value: 'rounded', label: 'Rounded', labelKey: 'qrStyle.dots.rounded' },
  { value: 'dots', label: 'Dots', labelKey: 'qrStyle.dots.dots' },
  { value: 'classy', label: 'Classy', labelKey: 'qrStyle.dots.classy' },
  { value: 'extra-rounded', label: 'Extra Rounded', labelKey: 'qrStyle.dots.extraRounded' },
]

export const CORNER_STYLE_OPTIONS = [
  { value: 'sharp', label: 'Sharp', labelKey: 'qrStyle.corners.sharp' },
  { value: 'rounded', label: 'Rounded', labelKey: 'qrStyle.corners.rounded' },
  { value: 'dot', label: 'Dot', labelKey: 'qrStyle.corners.dot' },
  { value: 'extra-round', label: 'Extra Round', labelKey: 'qrStyle.corners.extraRound' },
]

export const FRAME_STYLE_OPTIONS = [
  { value: 'none', label: 'None', labelKey: 'qrStyle.frames.none' },
  { value: 'simple', label: 'Simple', labelKey: 'qrStyle.frames.simple' },
  { value: 'rounded', label: 'Rounded', labelKey: 'qrStyle.frames.rounded' },
  { value: 'banner-top', label: 'Banner Top', labelKey: 'qrStyle.frames.bannerTop' },
  { value: 'banner-bottom', label: 'Banner Bottom', labelKey: 'qrStyle.frames.bannerBottom' },
  { value: 'badge', label: 'Badge', labelKey: 'qrStyle.frames.badge' },
]

function translateOptions(options, t) {
  return options.map((opt) => ({
    ...opt,
    label: t(opt.labelKey || opt.label),
  }))
}

export function translatedQrShapeOptions(t) {
  return translateOptions(QR_SHAPE_OPTIONS, t)
}

export function translatedDotStyleOptions(t) {
  return translateOptions(DOT_STYLE_OPTIONS, t)
}

export function translatedCornerStyleOptions(t) {
  return translateOptions(CORNER_STYLE_OPTIONS, t)
}

export function translatedFrameStyleOptions(t) {
  return translateOptions(FRAME_STYLE_OPTIONS, t)
}

export function normalizeQrStyle(style = {}) {
  const dot = style.dot_style === 'round' ? 'rounded' : (style.dot_style || QR_STYLE_DEFAULTS.dot_style)
  return {
    qr_shape: style.qr_shape || QR_STYLE_DEFAULTS.qr_shape,
    dot_style: dot,
    corner_style: style.corner_style || QR_STYLE_DEFAULTS.corner_style,
    frame_style: style.frame_style || QR_STYLE_DEFAULTS.frame_style,
  }
}

export function pickQrStyleFields(source = {}) {
  return normalizeQrStyle({
    qr_shape: source.qr_shape,
    dot_style: source.dot_style,
    corner_style: source.corner_style,
    frame_style: source.frame_style,
  })
}
