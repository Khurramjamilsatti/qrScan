<template>
  <div
    class="html-doc-preview"
    :class="{
      'html-doc-preview--compact': compact,
      'html-doc-preview--expandable': expandable,
      'html-doc-preview--embedded': embedded,
      'html-doc-preview--scrollable': scrollable,
      'html-doc-preview--contain-scroll': containScroll,
      'html-doc-preview--device-screen': deviceScreen,
      'html-doc-preview--full-bleed': fullBleed,
    }"
    :style="containerStyle"
  >
    <iframe
      ref="frameRef"
      :srcdoc="html"
      class="html-doc-preview__frame"
      :style="frameStyle"
      :title="title"
      sandbox="allow-same-origin allow-scripts"
      :scrolling="scrollable ? 'auto' : 'no'"
      @load="onFrameLoad"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onBeforeUnmount } from 'vue'

const LIST_PREVIEW_HEIGHT = 400

const props = defineProps({
  html: { type: String, required: true },
  title: { type: String, default: 'Preview' },
  compact: { type: Boolean, default: false },
  expandable: { type: Boolean, default: false },
  embedded: { type: Boolean, default: false },
  scrollable: { type: Boolean, default: false },
  containScroll: { type: Boolean, default: false },
  deviceScreen: { type: Boolean, default: false },
  fullBleed: { type: Boolean, default: false },
  fixedHeight: { type: Number, default: LIST_PREVIEW_HEIGHT },
})

const frameRef = ref(null)
const frameHeight = ref(initialHeight())
let resizeObserver = null
let syncTimers = []
let wheelHandler = null
let touchStartY = 0
let touchHandlerStart = null
let touchHandlerMove = null

const containerStyle = computed(() => {
  if (!props.scrollable) return undefined
  return {
    height: `${props.fixedHeight}px`,
    maxHeight: `${props.fixedHeight}px`,
  }
})

const frameStyle = computed(() => {
  if (props.scrollable) {
    return {
      width: '100%',
      height: '100%',
      minHeight: `${props.fixedHeight}px`,
    }
  }
  return { height: `${frameHeight.value}px` }
})

function initialHeight() {
  if (props.scrollable) return props.fixedHeight
  if (props.compact) return 480
  return 620
}

function clearSyncTimers() {
  syncTimers.forEach((id) => clearTimeout(id))
  syncTimers = []
}

function disconnectObserver() {
  resizeObserver?.disconnect()
  resizeObserver = null
}

function measureContentHeight(doc) {
  if (!doc) return 0

  const invite = doc.querySelector('.invite')
  if (invite) {
    const rect = invite.getBoundingClientRect()
    return Math.ceil(rect.height || invite.offsetHeight || invite.scrollHeight)
  }

  const body = doc.body
  const root = doc.documentElement
  return Math.max(
    body?.scrollHeight || 0,
    body?.offsetHeight || 0,
    root?.scrollHeight || 0,
    root?.offsetHeight || 0,
  )
}

function syncHeight() {
  if (props.scrollable || props.deviceScreen) return

  const frame = frameRef.value
  const doc = frame?.contentDocument
  if (!doc) return

  const height = measureContentHeight(doc)
  if (!height) return

  if (props.expandable) {
    frameHeight.value = Math.max(120, height + (props.fullBleed ? 0 : 2))
    return
  }

  const max = props.compact ? 480 : 720
  const min = props.compact ? 320 : 380
  frameHeight.value = Math.min(max, Math.max(min, height + 8))
}

function isFrameInternallyScrollable(frame) {
  const doc = frame?.contentDocument
  if (!doc) return false
  const root = doc.documentElement
  const body = doc.body
  return [root, body].some((el) => el && el.scrollHeight > el.clientHeight + 1)
}

function bindScrollPassthrough() {
  unbindScrollPassthrough()
  if (!props.fullBleed || !frameRef.value) return

  const frame = frameRef.value

  wheelHandler = (e) => {
    if (isFrameInternallyScrollable(frame)) return
    window.scrollBy({ top: e.deltaY, left: e.deltaX, behavior: 'auto' })
    e.preventDefault()
  }
  frame.addEventListener('wheel', wheelHandler, { passive: false })

  touchHandlerStart = (e) => {
    touchStartY = e.touches[0]?.clientY ?? 0
  }
  touchHandlerMove = (e) => {
    if (isFrameInternallyScrollable(frame)) return
    const y = e.touches[0]?.clientY ?? touchStartY
    const delta = touchStartY - y
    if (Math.abs(delta) > 0) {
      window.scrollBy({ top: delta, behavior: 'auto' })
      touchStartY = y
      e.preventDefault()
    }
  }
  frame.addEventListener('touchstart', touchHandlerStart, { passive: true })
  frame.addEventListener('touchmove', touchHandlerMove, { passive: false })
}

function unbindScrollPassthrough() {
  const frame = frameRef.value
  if (frame && wheelHandler) frame.removeEventListener('wheel', wheelHandler)
  if (frame && touchHandlerStart) frame.removeEventListener('touchstart', touchHandlerStart)
  if (frame && touchHandlerMove) frame.removeEventListener('touchmove', touchHandlerMove)
  wheelHandler = null
  touchHandlerStart = null
  touchHandlerMove = null
}

function bindImageLoadSync(doc) {
  doc.querySelectorAll('img').forEach((img) => {
    if (!img.complete) {
      img.addEventListener('load', syncHeight, { once: true })
      img.addEventListener('error', syncHeight, { once: true })
    }
  })
}

function observeFrame() {
  disconnectObserver()
  if (props.scrollable || props.deviceScreen) return

  const doc = frameRef.value?.contentDocument
  const target = doc?.body
  if (!target || typeof ResizeObserver === 'undefined') return

  resizeObserver = new ResizeObserver(() => syncHeight())
  resizeObserver.observe(target)
  if (doc.documentElement && doc.documentElement !== target) {
    resizeObserver.observe(doc.documentElement)
  }
}

function scheduleSyncBurst() {
  clearSyncTimers()
  const delays = props.fullBleed ? [0, 50, 150, 400, 900, 1500, 2500] : [0, 50, 150, 400, 900]
  delays.forEach((delay) => {
    syncTimers.push(setTimeout(syncHeight, delay))
  })
}

function onFrameLoad() {
  syncHeight()
  bindImageLoadSync(frameRef.value?.contentDocument)
  observeFrame()
  scheduleSyncBurst()
  bindScrollPassthrough()
}

watch(() => props.html, async () => {
  unbindScrollPassthrough()
  frameHeight.value = initialHeight()
  await nextTick()
  onFrameLoad()
})

onBeforeUnmount(() => {
  clearSyncTimers()
  disconnectObserver()
  unbindScrollPassthrough()
})
</script>

<style scoped>
.html-doc-preview {
  display: flex;
  justify-content: center;
  width: 100%;
  max-height: 720px;
  overflow-y: auto;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}
.html-doc-preview--expandable {
  max-height: none;
  overflow: visible;
}
.html-doc-preview--contain-scroll {
  max-height: calc(100vh - 10rem);
  overflow-y: auto;
  overflow-x: hidden;
  overscroll-behavior: contain;
  -webkit-overflow-scrolling: touch;
}
.html-doc-preview--scrollable {
  overflow: hidden;
  display: block;
}
.html-doc-preview--device-screen {
  max-height: none;
  overflow: hidden;
  width: 100%;
  height: 100%;
  display: block;
}
.html-doc-preview--device-screen .html-doc-preview__frame {
  width: 100%;
  height: 100%;
  min-height: 100%;
  border: none;
  border-radius: 0;
  box-shadow: none;
  transform: none;
}
.html-doc-preview--device-screen.html-doc-preview--scrollable {
  overflow: hidden;
}
.html-doc-preview--full-bleed {
  max-height: none;
  overflow: visible;
  display: block;
}
.html-doc-preview--full-bleed .html-doc-preview__frame {
  width: 100%;
  max-width: none;
  margin: 0;
  border: none;
  border-radius: 0;
  box-shadow: none;
  transform: none;
  display: block;
  vertical-align: top;
}
.html-doc-preview--embedded.html-doc-preview--full-bleed {
  overflow: visible;
}
.html-doc-preview__frame {
  width: 375px;
  border: 1px solid var(--border, #e8e4f0);
  border-radius: 1.25rem;
  background: #fff;
  box-shadow: var(--shadow-md, 0 8px 28px rgba(26,19,51,.1));
  transform-origin: top center;
  display: block;
}
.html-doc-preview--embedded .html-doc-preview__frame {
  width: 100%;
  border: none;
  border-radius: 0;
  box-shadow: none;
  transform: none;
}
.html-doc-preview--compact .html-doc-preview__frame {
  width: 300px;
  transform: scale(0.92);
}
.html-doc-preview--compact {
  max-height: 480px;
}
.html-doc-preview--compact.html-doc-preview--embedded {
  max-height: none;
  overflow: visible;
  display: block;
}
.html-doc-preview--compact.html-doc-preview--embedded.html-doc-preview--scrollable {
  overflow: hidden;
  border-radius: 1.25rem;
  box-shadow: var(--shadow-sm, 0 4px 16px rgba(26, 19, 51, 0.08));
  background: #fff;
}
.html-doc-preview--compact.html-doc-preview--embedded .html-doc-preview__frame {
  width: 100%;
  max-width: 100%;
  transform: none;
}
.html-doc-preview--compact.html-doc-preview--embedded.html-doc-preview--scrollable .html-doc-preview__frame {
  display: block;
  border-radius: 0;
  background: #fff;
}
@media (min-width: 1024px) {
  .html-doc-preview:not(.html-doc-preview--compact):not(.html-doc-preview--embedded):not(.html-doc-preview--device-screen) .html-doc-preview__frame {
    transform: scale(0.96);
  }
  .html-doc-preview--contain-scroll:not(.html-doc-preview--embedded):not(.html-doc-preview--device-screen) .html-doc-preview__frame {
    transform: scale(0.96);
  }
}
</style>
