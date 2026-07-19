<template>
  <div
    class="event-html-preview"
    :class="{
      'event-html-preview--device': showDeviceFrame,
      'event-html-preview--public': publicView,
    }"
    :style="previewShellStyle"
  >
    <div v-if="showDeviceFrame" class="phone-preview">
      <div class="phone-preview__scale">
        <div class="phone-preview__halo" aria-hidden="true" />
        <div class="phone-preview__frame">
          <div class="phone-preview__status">
            <span class="phone-preview__time">9:41</span>
            <span class="phone-preview__island" />
            <span class="phone-preview__icons" aria-hidden="true">
              <span class="phone-preview__signal" />
              <span class="phone-preview__wifi" />
              <span class="phone-preview__battery" />
            </span>
          </div>
          <div class="phone-preview__screen">
            <HtmlDocumentPreview
              :html="html"
              :title="title || 'Event preview'"
              :compact="compact"
              :embedded="embedded"
              :expandable="expandable"
              :scrollable="true"
              :contain-scroll="false"
              :fixed-height="deviceScreenHeight"
              :device-screen="true"
            />
            <div class="phone-preview__scroll-hint" aria-hidden="true">
              <span class="phone-preview__scroll-chevron">⌄</span>
              <span>{{ t('digitalEvents.previewScrollHint') }}</span>
            </div>
          </div>
          <div class="phone-preview__home" />
        </div>
        <div v-if="templateMeta" class="phone-preview__badge">
          <span class="phone-preview__badge-icon">{{ templateMeta.icon }}</span>
          <span class="phone-preview__badge-label">{{ templateLabel }}</span>
        </div>
      </div>
    </div>
    <HtmlDocumentPreview
      v-else
      :html="html"
      :title="title || 'Event preview'"
      :compact="compact"
      :embedded="embedded"
      :expandable="expandable"
      :scrollable="isScrollable"
      :contain-scroll="isContainScroll"
      :fixed-height="fixedHeight"
      :full-bleed="publicView && embedded"
    />
    <div v-if="inviteUrl && !compact && !publicView && !embedded" class="event-html-preview__meta">
      <span class="event-html-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="event-html-preview__url">{{ inviteUrl }}</span>
      <span v-if="domainLabel" class="event-html-preview__domain">{{ domainLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import HtmlDocumentPreview from './HtmlDocumentPreview.vue'
import { renderEventHtml } from '../../utils/eventHtmlRenderer'
import { EVENT_TEMPLATES } from '../../utils/eventTemplates'

const PHONE_WIDTH = 400
const SCREEN_HEIGHT = 820

const { t } = useI18n()

const props = defineProps({
  title: String,
  subtitle: String,
  hosts: String,
  eventDate: String,
  eventEndDate: String,
  venueName: String,
  dressCode: String,
  coverImage: String,
  themeColor: { type: String, default: '#e8655a' },
  content: { type: Object, default: () => ({}) },
  eventType: { type: String, default: 'general' },
  slug: String,
  inviteUrl: String,
  domainLabel: String,
  template: { type: String, default: 'simple-invite' },
  compact: { type: Boolean, default: false },
  embedded: { type: Boolean, default: false },
  expandable: { type: Boolean, default: false },
  scrollable: { type: Boolean, default: false },
  containScroll: { type: Boolean, default: false },
  deviceFrame: { type: Boolean, default: false },
  fixedHeight: { type: Number, default: 400 },
  livePreview: { type: Boolean, default: false },
  publicView: { type: Boolean, default: false },
})

const deviceScreenHeight = SCREEN_HEIGHT
const isScrollable = computed(() => props.scrollable || (props.embedded && props.compact && !props.publicView))
const isContainScroll = computed(() => props.containScroll || (props.expandable && !props.embedded && !props.deviceFrame))
const showDeviceFrame = computed(() => props.deviceFrame && props.expandable && !props.embedded && !props.compact)

const templateMeta = computed(() => EVENT_TEMPLATES.find((item) => item.id === props.template))

const templateLabel = computed(() => {
  if (!templateMeta.value) return ''
  return t(templateMeta.value.labelKey)
})

const previewShellStyle = computed(() => {
  if (!showDeviceFrame.value) return undefined
  const gradient = templateMeta.value?.thumbGradient
    || `linear-gradient(145deg, ${props.themeColor}, color-mix(in srgb, ${props.themeColor} 35%, #1a1333))`
  return {
    '--preview-accent': props.themeColor,
    '--preview-gradient': gradient,
    '--phone-width': `${PHONE_WIDTH}px`,
    '--phone-screen-h': `${SCREEN_HEIGHT}px`,
    '--phone-natural-h': `${SCREEN_HEIGHT + 92}px`,
  }
})

const html = computed(() => renderEventHtml({
  title: props.title,
  subtitle: props.subtitle,
  hosts: props.hosts,
  event_date: props.eventDate,
  event_end_date: props.eventEndDate,
  venue_name: props.venueName,
  dress_code: props.dressCode,
  cover_image_path: props.coverImage,
  theme_color: props.themeColor,
  content: props.content,
  event_type: props.eventType,
  slug: props.slug,
  invite_url: props.inviteUrl,
  template: props.template,
  livePreview: props.livePreview,
  publicView: props.publicView,
  compact: props.compact,
  listView: props.embedded && props.compact && !props.publicView,
}))
</script>

<style scoped>
.event-html-preview {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: 100%;
  min-width: 0;
}
.event-html-preview--public {
  width: 100%;
  display: block;
  line-height: 0;
}
.event-html-preview--public :deep(.html-doc-preview) {
  max-height: none;
  overflow: visible;
}

.event-html-preview--device {
  align-items: center;
}

.phone-preview {
  width: 100%;
  display: flex;
  justify-content: center;
}

.phone-preview__scale {
  position: relative;
  width: var(--phone-width, 400px);
  max-width: 100%;
  transform: scale(min(1, calc((100dvh - 10rem) / var(--phone-natural-h, 912px))));
  transform-origin: top center;
  margin-bottom: calc((1 - min(1, calc((100dvh - 10rem) / var(--phone-natural-h, 912px)))) * var(--phone-natural-h, 912px) * -0.5);
}

.phone-preview__halo {
  position: absolute;
  inset: 8% 2% auto;
  height: 55%;
  border-radius: 50%;
  background: var(--preview-gradient);
  filter: blur(48px);
  opacity: 0.45;
  pointer-events: none;
  z-index: 0;
}

.phone-preview__frame {
  position: relative;
  z-index: 1;
  width: 100%;
  border-radius: 46px;
  padding: 11px 11px 15px;
  background:
    linear-gradient(155deg, rgba(255, 255, 255, 0.12) 0%, transparent 42%),
    linear-gradient(165deg, #48425a 0%, #1a1724 42%, #0c0b10 100%);
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, 0.09) inset,
    0 0 0 1px rgba(0, 0, 0, 0.35),
    0 28px 64px rgba(20, 14, 40, 0.28),
    0 8px 20px rgba(20, 14, 40, 0.16);
}

.phone-preview__status {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  padding: 7px 14px 11px;
  color: rgba(255, 255, 255, 0.92);
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.02em;
}

.phone-preview__time { justify-self: start; padding-left: 2px; }

.phone-preview__icons {
  justify-self: end;
  display: flex;
  align-items: center;
  gap: 5px;
  padding-right: 2px;
}

.phone-preview__signal,
.phone-preview__wifi,
.phone-preview__battery {
  display: block;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 1px;
}

.phone-preview__signal { width: 14px; height: 8px; clip-path: polygon(0 100%, 25% 55%, 50% 75%, 75% 35%, 100% 55%, 100% 100%); }
.phone-preview__wifi { width: 12px; height: 8px; border-radius: 999px 999px 0 0; border: 2px solid rgba(255,255,255,.9); border-bottom: none; background: transparent; }
.phone-preview__battery { width: 18px; height: 8px; border-radius: 2px; border: 1.5px solid rgba(255,255,255,.85); background: transparent; position: relative; }
.phone-preview__battery::after {
  content: '';
  position: absolute;
  inset: 1px 4px 1px 1px;
  border-radius: 1px;
  background: rgba(255, 255, 255, 0.9);
}

.phone-preview__island {
  width: 98px;
  height: 29px;
  border-radius: 999px;
  background: #030304;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.06);
}

.phone-preview__screen {
  position: relative;
  overflow: hidden;
  width: 100%;
  height: var(--phone-screen-h, 820px);
  flex-shrink: 0;
  border-radius: 34px;
  background: #fff;
  box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
}

.phone-preview__screen :deep(.html-doc-preview) {
  width: 100%;
  height: 100%;
  max-height: none;
  overflow: hidden;
  display: block;
}

.phone-preview__screen :deep(.html-doc-preview__frame) {
  width: 100% !important;
  height: 100% !important;
  min-height: 100% !important;
  border: none !important;
  border-radius: 0 !important;
  box-shadow: none !important;
  transform: none !important;
  display: block;
}

.phone-preview__scroll-hint {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.125rem;
  padding: 1.75rem 1rem 0.625rem;
  background: linear-gradient(180deg, transparent, rgba(255, 255, 255, 0.92) 55%, #fff);
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: color-mix(in srgb, var(--preview-accent, #e8655a) 70%, #6b6578);
  pointer-events: none;
}

.phone-preview__scroll-chevron {
  font-size: 0.875rem;
  line-height: 1;
  animation: scroll-bounce 2s ease-in-out infinite;
}

@keyframes scroll-bounce {
  0%, 100% { transform: translateY(0); opacity: 0.7; }
  50% { transform: translateY(3px); opacity: 1; }
}

.phone-preview__home {
  width: 124px;
  height: 4px;
  margin: 13px auto 1px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.32);
}

.phone-preview__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.4375rem 0.875rem;
  border-radius: 999px;
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e8e4f0);
  box-shadow: 0 4px 16px rgba(26, 19, 51, 0.08);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary, #6b6578);
}

.phone-preview__badge-icon { font-size: 1rem; line-height: 1; }
.phone-preview__badge-label { letter-spacing: 0.01em; }

.event-html-preview__meta {
  display: flex; flex-wrap: wrap; gap: 0.375rem 0.75rem; align-items: center;
  padding: 0.5rem 0.25rem; font-size: 0.75rem; color: var(--text-secondary, #6b6578);
}
.event-html-preview__label { font-weight: 600; }
.event-html-preview__url { font-family: monospace; word-break: break-all; color: var(--brand, #e8655a); }
.event-html-preview__domain { font-size: 0.6875rem; opacity: 0.8; }
</style>
