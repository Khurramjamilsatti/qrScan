import QRCode from 'qrcode'
import { normalizeQrStyle } from './qrStyleOptions'

function roundRect(ctx, x, y, w, h, r) {
  const radius = Math.min(r, w / 2, h / 2)
  ctx.beginPath()
  ctx.moveTo(x + radius, y)
  ctx.arcTo(x + w, y, x + w, y + h, radius)
  ctx.arcTo(x + w, y + h, x, y + h, radius)
  ctx.arcTo(x, y + h, x, y, radius)
  ctx.arcTo(x, y, x + w, y, radius)
  ctx.closePath()
}

function isFinderModule(row, col, count) {
  const inTL = row < 7 && col < 7
  const inTR = row < 7 && col >= count - 7
  const inBL = row >= count - 7 && col < 7
  return inTL || inTR || inBL
}

/** ISO/IEC 18004 alignment pattern center coordinates by version (1-indexed) */
const ALIGNMENT_CENTERS = [
  [], [6], [6, 18], [6, 22], [6, 26], [6, 30], [6, 34], [6, 22, 38], [6, 24, 42],
  [6, 26, 46], [6, 28, 50], [6, 30, 54], [6, 32, 58], [6, 34, 62], [6, 26, 46, 66],
  [6, 26, 48, 70], [6, 26, 50, 74], [6, 30, 54, 78], [6, 30, 56, 82], [6, 30, 58, 86],
  [6, 34, 62, 90], [6, 28, 50, 72, 94], [6, 26, 50, 74, 98], [6, 30, 54, 78, 102],
  [6, 28, 54, 80, 106], [6, 32, 58, 84, 110], [6, 30, 58, 86, 114], [6, 34, 62, 90, 118],
  [6, 26, 50, 74, 98, 122], [6, 30, 54, 78, 102, 126], [6, 26, 52, 78, 104, 130],
  [6, 30, 56, 82, 108, 134], [6, 34, 60, 86, 112, 138], [6, 30, 58, 86, 114, 142],
  [6, 34, 62, 90, 118, 146], [6, 30, 54, 78, 102, 126, 150], [6, 24, 50, 76, 102, 128, 154],
  [6, 28, 54, 80, 106, 132, 158], [6, 32, 58, 84, 110, 136, 162], [6, 26, 54, 82, 110, 138, 166],
  [6, 30, 58, 86, 114, 142, 170],
]

function qrVersion(moduleCount) {
  return (moduleCount - 17) / 4 + 1
}

function isTimingModule(row, col, count) {
  if (row === 6 && col >= 8 && col <= count - 9) return true
  if (col === 6 && row >= 8 && row <= count - 9) return true
  return false
}

function isAlignmentModule(row, col, count) {
  const version = qrVersion(count)
  const centers = ALIGNMENT_CENTERS[version] || []
  for (const cy of centers) {
    for (const cx of centers) {
      if (isFinderModule(cy, cx, count)) continue
      if (Math.abs(row - cy) <= 2 && Math.abs(col - cx) <= 2) return true
    }
  }
  return false
}

function isStructuralModule(row, col, count) {
  return isFinderModule(row, col, count)
    || isTimingModule(row, col, count)
    || isAlignmentModule(row, col, count)
}

function drawHexagonPath(ctx, cx, cy, radius) {
  ctx.beginPath()
  for (let i = 0; i < 6; i++) {
    const angle = (Math.PI / 3) * i - Math.PI / 6
    const x = cx + radius * Math.cos(angle)
    const y = cy + radius * Math.sin(angle)
    if (i === 0) ctx.moveTo(x, y)
    else ctx.lineTo(x, y)
  }
  ctx.closePath()
}

function shapeLayout(shape) {
  switch (shape) {
    case 'circle': return { scale: 0.82, stroke: true }
    case 'hexagon': return { scale: 0.68, stroke: true }
    case 'diamond': return { scale: 0.62, stroke: true }
    case 'rounded': return { scale: 0.94, stroke: false }
    default: return { scale: 1, stroke: false }
  }
}

function traceShapePath(ctx, x, y, w, h, shape) {
  switch (shape) {
    case 'rounded':
      roundRect(ctx, x, y, w, h, w * 0.12)
      break
    case 'circle':
      ctx.arc(x + w / 2, y + h / 2, Math.min(w, h) / 2, 0, Math.PI * 2)
      break
    case 'hexagon':
      drawHexagonPath(ctx, x + w / 2, y + h / 2, Math.min(w, h) / 2 - 1)
      break
    case 'diamond': {
      const cx = x + w / 2
      const cy = y + h / 2
      const rx = w / 2 - 1
      const ry = h / 2 - 1
      ctx.moveTo(cx, cy - ry)
      ctx.lineTo(cx + rx, cy)
      ctx.lineTo(cx, cy + ry)
      ctx.lineTo(cx - rx, cy)
      ctx.closePath()
      break
    }
    default:
      ctx.rect(x, y, w, h)
  }
}

function drawShapeBackground(ctx, x, y, w, h, shape, fill, stroke) {
  if (shape === 'square') return
  ctx.save()
  ctx.beginPath()
  traceShapePath(ctx, x, y, w, h, shape)
  ctx.fillStyle = fill
  ctx.fill()
  if (stroke) {
    ctx.strokeStyle = stroke
    ctx.lineWidth = Math.max(2, w * 0.012)
    ctx.stroke()
  }
  ctx.restore()
}

/** Standard 7×7 finder — always nested squares (required for scanners) */
function drawFinderPattern(ctx, x, y, cell, fg, bg, cornerStyle) {
  const outer = cell * 7
  const mid = cell * 5
  const inner = cell * 3
  const pad1 = cell
  const pad2 = cell * 2

  ctx.fillStyle = fg
  if (cornerStyle === 'rounded' || cornerStyle === 'extra-round') {
    const r = cornerStyle === 'extra-round' ? cell * 1.4 : cell * 0.9
    roundRect(ctx, x, y, outer, outer, r)
    ctx.fill()
    ctx.fillStyle = bg
    roundRect(ctx, x + pad1, y + pad1, mid, mid, r * 0.65)
    ctx.fill()
    ctx.fillStyle = fg
    roundRect(ctx, x + pad2, y + pad2, inner, inner, r * 0.4)
    ctx.fill()
  } else if (cornerStyle === 'dot') {
    ctx.beginPath()
    ctx.arc(x + outer / 2, y + outer / 2, outer / 2 - cell * 0.05, 0, Math.PI * 2)
    ctx.fill()
    ctx.fillStyle = bg
    ctx.beginPath()
    ctx.arc(x + outer / 2, y + outer / 2, mid / 2, 0, Math.PI * 2)
    ctx.fill()
    ctx.fillStyle = fg
    ctx.beginPath()
    ctx.arc(x + outer / 2, y + outer / 2, inner / 2, 0, Math.PI * 2)
    ctx.fill()
  } else {
    ctx.fillRect(x, y, outer, outer)
    ctx.fillStyle = bg
    ctx.fillRect(x + pad1, y + pad1, mid, mid)
    ctx.fillStyle = fg
    ctx.fillRect(x + pad2, y + pad2, inner, inner)
  }
}

function drawDataModule(ctx, x, y, size, style, color) {
  ctx.fillStyle = color
  switch (style) {
    case 'dots':
      ctx.beginPath()
      ctx.arc(x + size / 2, y + size / 2, size * 0.46, 0, Math.PI * 2)
      ctx.fill()
      break
    case 'rounded':
      roundRect(ctx, x + size * 0.05, y + size * 0.05, size * 0.9, size * 0.9, size * 0.28)
      ctx.fill()
      break
    case 'extra-rounded':
      roundRect(ctx, x + size * 0.04, y + size * 0.04, size * 0.92, size * 0.92, size * 0.45)
      ctx.fill()
      break
    case 'classy':
      ctx.beginPath()
      ctx.moveTo(x, y + size * 0.3)
      ctx.arcTo(x, y, x + size * 0.3, y, size * 0.3)
      ctx.lineTo(x + size, y)
      ctx.lineTo(x + size, y + size)
      ctx.lineTo(x + size * 0.3, y + size)
      ctx.arcTo(x, y + size, x, y + size * 0.7, size * 0.3)
      ctx.closePath()
      ctx.fill()
      break
    default:
      ctx.fillRect(x, y, size, size)
  }
}

function framePadding(frameStyle) {
  switch (frameStyle) {
    case 'simple': return 14
    case 'rounded': return 18
    case 'banner-top':
    case 'banner-bottom': return 22
    case 'badge': return 20
    default: return 0
  }
}

function drawFrame(ctx, x, y, w, h, frameStyle, color) {
  if (frameStyle === 'none') return
  ctx.strokeStyle = color
  ctx.lineWidth = 3

  switch (frameStyle) {
    case 'simple':
      ctx.strokeRect(x + 1.5, y + 1.5, w - 3, h - 3)
      break
    case 'rounded':
      roundRect(ctx, x + 1.5, y + 1.5, w - 3, h - 3, 14)
      ctx.stroke()
      break
    case 'banner-top':
      roundRect(ctx, x + 1.5, y + 1.5, w - 3, h - 3, 10)
      ctx.stroke()
      ctx.fillStyle = color
      roundRect(ctx, x + 8, y + 8, w - 16, 18, 6)
      ctx.fill()
      break
    case 'banner-bottom':
      roundRect(ctx, x + 1.5, y + 1.5, w - 3, h - 3, 10)
      ctx.stroke()
      ctx.fillStyle = color
      roundRect(ctx, x + 8, y + h - 26, w - 16, 18, 6)
      ctx.fill()
      break
    case 'badge':
      roundRect(ctx, x + 1.5, y + 1.5, w - 3, h - 3, 12)
      ctx.stroke()
      ;[
        [x + 6, y + 6], [x + w - 6, y + 6],
        [x + 6, y + h - 6], [x + w - 6, y + h - 6],
      ].forEach(([bx, by]) => {
        ctx.beginPath()
        ctx.arc(bx, by, 5, 0, Math.PI * 2)
        ctx.fillStyle = color
        ctx.fill()
      })
      break
    default:
      break
  }
}

export async function renderStyledQr(text, options = {}) {
  if (!text) return null

  const {
    foreground = '#000000',
    background = '#ffffff',
    size = 280,
    margin = 4,
    errorCorrectionLevel = 'M',
    logoImage = null,
    backgroundImage = null,
    qr_shape = 'square',
    dot_style = 'square',
    corner_style = 'sharp',
    frame_style = 'none',
  } = { ...normalizeQrStyle(options), ...options }

  const quietZone = Math.max(margin, 4)
  const ecLevel = logoImage ? 'H' : errorCorrectionLevel
  const qr = QRCode.create(text, { errorCorrectionLevel: ecLevel })
  const modules = qr.modules
  const count = modules.size
  const framePad = framePadding(frame_style)
  const innerSize = size - framePad * 2
  const { scale: shapeScale, stroke: shapeStroke } = shapeLayout(qr_shape)
  const qrDrawSize = Math.round(innerSize * shapeScale)
  const totalCells = count + quietZone * 2
  const cellSize = qrDrawSize / totalCells
  const qrModuleOffset = framePad + (innerSize - qrDrawSize) / 2

  const canvas = document.createElement('canvas')
  canvas.width = size
  canvas.height = size
  const ctx = canvas.getContext('2d')

  if (backgroundImage) {
    ctx.drawImage(backgroundImage, 0, 0, size, size)
  } else {
    ctx.fillStyle = background
    ctx.fillRect(0, 0, size, size)
  }

  drawShapeBackground(
    ctx,
    framePad,
    framePad,
    innerSize,
    innerSize,
    qr_shape,
    background,
    shapeStroke ? foreground : null,
  )

  const qrCanvas = document.createElement('canvas')
  qrCanvas.width = qrDrawSize
  qrCanvas.height = qrDrawSize
  const qrCtx = qrCanvas.getContext('2d')
  qrCtx.fillStyle = backgroundImage ? 'rgba(255,255,255,0.85)' : background
  qrCtx.fillRect(0, 0, qrDrawSize, qrDrawSize)

  const finderCoords = [
    [0, 0],
    [count - 7, 0],
    [0, count - 7],
  ]

  for (const [col0, row0] of finderCoords) {
    drawFinderPattern(
      qrCtx,
      (col0 + quietZone) * cellSize,
      (row0 + quietZone) * cellSize,
      cellSize,
      foreground,
      backgroundImage ? 'rgba(255,255,255,0.85)' : background,
      corner_style,
    )
  }

  for (let row = 0; row < count; row++) {
    for (let col = 0; col < count; col++) {
      if (!modules.get(row, col)) continue
      if (isFinderModule(row, col, count)) continue
      const moduleStyle = isStructuralModule(row, col, count) ? 'square' : dot_style
      drawDataModule(
        qrCtx,
        (col + quietZone) * cellSize,
        (row + quietZone) * cellSize,
        cellSize,
        moduleStyle,
        foreground,
      )
    }
  }

  ctx.drawImage(qrCanvas, qrModuleOffset, qrModuleOffset)

  if (logoImage) {
    const logoSize = innerSize * 0.2
    const lx = (size - logoSize) / 2
    const ly = (size - logoSize) / 2
    ctx.fillStyle = '#ffffff'
    roundRect(ctx, lx - 6, ly - 6, logoSize + 12, logoSize + 12, 8)
    ctx.fill()
    ctx.drawImage(logoImage, lx, ly, logoSize, logoSize)
  }

  drawFrame(ctx, 0, 0, size, size, frame_style, foreground)

  return canvas.toDataURL('image/png')
}

/**
 * Fast path: standard QR via library (maximum scan reliability).
 * Used when no custom styling is applied.
 */
export async function renderStandardQr(text, options = {}) {
  if (!text) return null
  const {
    foreground = '#000000',
    background = '#ffffff',
    size = 280,
    margin = 4,
    errorCorrectionLevel = 'M',
    logoImage = null,
    backgroundImage = null,
  } = options

  const quietZone = Math.max(margin, 4)
  const ecLevel = logoImage ? 'H' : errorCorrectionLevel

  try {
    const canvas = document.createElement('canvas')
    canvas.width = size
    canvas.height = size
    const ctx = canvas.getContext('2d')

    if (backgroundImage) {
      ctx.drawImage(backgroundImage, 0, 0, size, size)
    } else {
      ctx.fillStyle = background
      ctx.fillRect(0, 0, size, size)
    }

    const qrDataUrl = await QRCode.toDataURL(text, {
      width: size,
      margin: quietZone,
      color: { dark: foreground, light: backgroundImage ? '#ffffff00' : background },
      errorCorrectionLevel: ecLevel,
    })

    const qrImg = await new Promise((resolve, reject) => {
      const img = new Image()
      img.onload = () => resolve(img)
      img.onerror = reject
      img.src = qrDataUrl
    })
    ctx.drawImage(qrImg, 0, 0, size, size)

    if (logoImage) {
      const logoSize = size * 0.2
      const lx = (size - logoSize) / 2
      const ly = (size - logoSize) / 2
      ctx.fillStyle = '#ffffff'
      roundRect(ctx, lx - 6, ly - 6, logoSize + 12, logoSize + 12, 8)
      ctx.fill()
      ctx.drawImage(logoImage, lx, ly, logoSize, logoSize)
    }

    return canvas.toDataURL('image/png')
  } catch {
    return null
  }
}

export function usesCustomQrRendering(options = {}) {
  const s = normalizeQrStyle(options)
  return (
    s.qr_shape !== 'square'
    || s.dot_style !== 'square'
    || s.corner_style !== 'sharp'
    || s.frame_style !== 'none'
  )
}
