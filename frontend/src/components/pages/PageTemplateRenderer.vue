<template>
  <div class="page-tpl" :style="{ '--tpl-theme': themeColor }">
    <div class="page-tpl__header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <div class="page-tpl__overlay"></div>
      <img v-if="logoUrl" :src="logoUrl" alt="" class="page-tpl__logo" />
    </div>

    <div class="page-tpl__body">
      <!-- Template body -->
      <template v-if="template === 'landing'">
        <h2 class="page-tpl__title">{{ normalized.headline || title }}</h2>
        <p v-if="normalized.subheadline" class="page-tpl__sub">{{ normalized.subheadline }}</p>
        <a v-if="normalized.cta_label && normalized.cta_url" :href="normalized.cta_url" class="page-tpl__cta" target="_blank" rel="noopener">
          {{ normalized.cta_label }}
        </a>
        <div v-if="normalized.features?.length" class="page-tpl__features">
          <div v-for="(f, i) in normalized.features" :key="i" class="page-tpl__feature">
            <div class="page-tpl__feature-title">{{ f.title }}</div>
            <div class="page-tpl__feature-desc">{{ f.description }}</div>
          </div>
        </div>
      </template>

      <template v-else-if="template === 'portfolio'">
        <h2 class="page-tpl__title">{{ normalized.headline || title }}</h2>
        <p v-if="normalized.about" class="page-tpl__sub">{{ normalized.about }}</p>
        <div v-if="normalized.projects?.length" class="page-tpl__projects">
          <div v-for="(p, i) in normalized.projects" :key="i" class="page-tpl__project">
            <div v-if="projectImg(p)" class="page-tpl__project-img" :style="{ backgroundImage: `url(${projectImg(p)})` }"></div>
            <div class="page-tpl__project-body">
              <div class="page-tpl__project-title">{{ p.title }}</div>
              <div class="page-tpl__project-desc">{{ p.description }}</div>
            </div>
          </div>
        </div>
      </template>

      <template v-else-if="template === 'event'">
        <div class="page-tpl__event-badge">{{ t('renderers.page.event') }}</div>
        <h2 class="page-tpl__title">{{ normalized.event_name || title }}</h2>
        <div v-if="normalized.date" class="page-tpl__meta">📅 {{ normalized.date }}</div>
        <div v-if="normalized.location" class="page-tpl__meta">📍 {{ normalized.location }}</div>
        <p v-if="normalized.description" class="page-tpl__sub">{{ normalized.description }}</p>
        <a v-if="normalized.cta_label && normalized.cta_url" :href="normalized.cta_url" class="page-tpl__cta" target="_blank" rel="noopener">
          {{ normalized.cta_label }}
        </a>
      </template>

      <template v-else>
        <h2 class="page-tpl__title">{{ normalized.headline || title }}</h2>
        <p v-if="normalized.body" class="page-tpl__body-text">{{ normalized.body }}</p>
        <a v-if="normalized.cta_label && normalized.cta_url" :href="normalized.cta_url" class="page-tpl__cta" target="_blank" rel="noopener">
          {{ normalized.cta_label }}
        </a>
      </template>

      <!-- Shared extras (sorted) -->
      <template v-for="sectionId in sectionsOrder" :key="sectionId">
        <section v-if="sectionId === 'gallery' && galleryVisible" class="page-block">
          <h3 class="page-block__title">{{ normalized.gallery?.title || t('templates.sections.galleryTitle') }}</h3>
          <div class="page-gallery" :class="`page-gallery--${normalized.gallery?.layout || 'grid-3'}`">
            <component
              :is="item.url ? 'a' : 'div'"
              v-for="(item, i) in normalized.gallery?.items || []"
              :key="i"
              :href="item.url || undefined"
              :target="item.url ? '_blank' : undefined"
              :rel="item.url ? 'noopener' : undefined"
              class="page-gallery__item"
            >
              <div
                v-if="galleryImg(item)"
                class="page-gallery__img"
                :style="{ backgroundImage: `url(${galleryImg(item)})` }"
              ></div>
              <p v-if="item.caption" class="page-gallery__caption">{{ item.caption }}</p>
            </component>
          </div>
        </section>

        <section v-else-if="sectionId === 'calendar' && calendarVisible" class="page-block">
          <h3 class="page-block__title">{{ normalized.calendar?.title || t('templates.sections.calendarTitle') }}</h3>
          <div v-if="normalized.calendar?.embed_url" class="page-calendar-embed">
            <iframe :src="normalized.calendar.embed_url" :title="t('common.calendar')" frameborder="0" scrolling="no"></iframe>
          </div>
          <div v-if="normalized.calendar?.events?.length" class="page-events">
            <div v-for="(ev, i) in normalized.calendar.events" :key="i" class="page-event">
              <div class="page-event__main">
                <div class="page-event__title">{{ ev.title }}</div>
                <div v-if="ev.date" class="page-event__meta">📅 {{ ev.date }}<span v-if="ev.time"> · {{ ev.time }}</span></div>
                <div v-if="ev.location" class="page-event__meta">📍 {{ ev.location }}</div>
              </div>
              <a
                v-if="ev.url || calAddUrl(ev)"
                :href="ev.url || calAddUrl(ev)"
                class="page-event__btn"
                target="_blank"
                rel="noopener"
              >{{ ev.url ? t('common.details') : t('common.addToCalendar') }}</a>
            </div>
          </div>
        </section>

        <section v-else-if="sectionId === 'contact' && contactVisible" class="page-block">
          <h3 class="page-block__title">{{ t('templates.sections.contact') }}</h3>
          <div class="page-contact">
            <div v-if="normalized.contact?.name" class="page-contact__name">{{ normalized.contact.name }}</div>
            <a v-if="normalized.contact?.email" :href="`mailto:${normalized.contact.email}`" class="page-contact__row">✉ {{ normalized.contact.email }}</a>
            <a v-if="normalized.contact?.phone" :href="`tel:${normalized.contact.phone}`" class="page-contact__row">📞 {{ normalized.contact.phone }}</a>
            <div v-if="normalized.contact?.address" class="page-contact__row">📍 {{ normalized.contact.address }}</div>
            <a v-if="normalized.contact?.website" :href="normalized.contact.website" class="page-contact__row" target="_blank" rel="noopener">🌐 {{ cleanUrl(normalized.contact.website) }}</a>
          </div>
        </section>

        <section v-else-if="sectionId === 'social' && socialVisible" class="page-block">
          <h3 class="page-block__title">{{ normalized.social?.title || t('templates.sections.connectTitle') }}</h3>
          <div class="page-social">
            <a
              v-for="(s, i) in normalized.social?.links || []"
              :key="i"
              :href="socialHref(s)"
              class="page-social__btn"
              target="_blank"
              rel="noopener"
            >
              <SocialIcon :platform="s.platform" />
              <span>{{ platformLabel(s.platform) }}</span>
            </a>
          </div>
        </section>

        <section v-else-if="sectionId === 'extra_links' && linksVisible" class="page-block">
          <h3 class="page-block__title">{{ normalized.extra_links?.title || t('templates.sections.linksTitle') }}</h3>
          <div class="page-links">
            <a
              v-for="(link, i) in extraLinkItems"
              :key="i"
              :href="link.url"
              class="page-links__btn"
              target="_blank"
              rel="noopener"
            >
              <span class="page-links__icon">{{ linkIcon(link.icon) }}</span>
              <span>{{ link.label || t('templates.sections.fallbackLink') }}</span>
            </a>
          </div>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'
import SocialIcon from '../ui/SocialIcon.vue'
import { platformLabel, googleCalendarUrl } from '../../utils/socialPlatforms'
import { normalizePageContent, isSectionVisible, getExtraLinkItems } from '../../utils/pageSections'

const { t } = useI18n()

const props = defineProps({
  title: { type: String, default: '' },
  template: { type: String, default: 'landing' },
  content: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
})

const normalized = computed(() => normalizePageContent(props.content))
const sectionsOrder = computed(() => normalized.value.sections_order || [])
const extraLinkItems = computed(() => getExtraLinkItems(normalized.value))

const galleryVisible = computed(() => isSectionVisible('gallery', normalized.value))
const calendarVisible = computed(() => isSectionVisible('calendar', normalized.value))
const contactVisible = computed(() => isSectionVisible('contact', normalized.value))
const socialVisible = computed(() => isSectionVisible('social', normalized.value))
const linksVisible = computed(() => isSectionVisible('extra_links', normalized.value))

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)
const headerStyle = computed(() => bgUrl.value ? { backgroundImage: `url(${bgUrl.value})` } : {})

function projectImg(p) {
  return p.image_path ? resolveStorageUrl(p.image_path) : null
}
function galleryImg(item) {
  return item.image_path ? resolveStorageUrl(item.image_path) : null
}
function calAddUrl(ev) {
  return googleCalendarUrl(ev)
}
function socialHref(s) {
  if (!s.url) return '#'
  if (s.platform === 'email') return `mailto:${s.url.replace(/^mailto:/, '')}`
  return s.url
}
function cleanUrl(url) {
  try { return new URL(url).host + new URL(url).pathname.replace(/\/$/, '') } catch { return url }
}
function linkIcon(icon) {
  return { link: '🔗', download: '⬇', external: '↗', doc: '📄', shop: '🛒' }[icon] || '🔗'
}
</script>

<style scoped>
.page-tpl {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e8e4f0);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(26,19,51,0.06));
}
.page-tpl__header {
  position: relative;
  height: 5rem;
  background: linear-gradient(135deg, var(--tpl-theme), color-mix(in srgb, var(--tpl-theme) 60%, #6b4fa0));
  background-size: cover;
  background-position: center;
}
.page-tpl__header.has-bg .page-tpl__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(26,19,51,0.15), rgba(26,19,51,0.45));
}
.page-tpl__logo {
  position: absolute;
  bottom: -1.25rem;
  left: 1.25rem;
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.75rem;
  object-fit: cover;
  border: 3px solid var(--surface, #fff);
  box-shadow: var(--shadow-sm);
  z-index: 2;
}
.page-tpl__body { padding: 1.75rem 1.25rem 1.25rem; }
.page-tpl__title {
  font-size: 1.125rem;
  font-weight: 800;
  color: var(--text-primary, #1a1333);
  line-height: 1.3;
  margin-top: 0.5rem;
}
.page-tpl__sub {
  font-size: 0.8125rem;
  color: var(--text-secondary, #5c5470);
  margin-top: 0.5rem;
  line-height: 1.5;
}
.page-tpl__body-text {
  font-size: 0.875rem;
  color: var(--text-secondary, #5c5470);
  margin-top: 0.75rem;
  line-height: 1.6;
  white-space: pre-wrap;
}
.page-tpl__cta {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.5rem 1.25rem;
  background: var(--tpl-theme);
  color: #fff;
  font-size: 0.8125rem;
  font-weight: 600;
  border-radius: 9999px;
  text-decoration: none;
}
.page-tpl__features { display: flex; flex-direction: column; gap: 0.625rem; margin-top: 1rem; }
.page-tpl__feature {
  padding: 0.75rem;
  background: var(--bg-subtle, #faf8fd);
  border-radius: 0.75rem;
  border: 1px solid var(--border, #e8e4f0);
}
.page-tpl__feature-title { font-size: 0.8125rem; font-weight: 700; color: var(--text-primary, #1a1333); }
.page-tpl__feature-desc { font-size: 0.75rem; color: var(--text-muted, #8b839c); margin-top: 0.125rem; }
.page-tpl__projects { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem; }
.page-tpl__project { border: 1px solid var(--border, #e8e4f0); border-radius: 0.75rem; overflow: hidden; }
.page-tpl__project-img { height: 4rem; background-size: cover; background-position: center; background-color: var(--bg-subtle, #faf8fd); }
.page-tpl__project-body { padding: 0.625rem 0.75rem; }
.page-tpl__project-title { font-size: 0.8125rem; font-weight: 700; color: var(--text-primary, #1a1333); }
.page-tpl__project-desc { font-size: 0.75rem; color: var(--text-muted, #8b839c); margin-top: 0.125rem; }
.page-tpl__event-badge {
  display: inline-block;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.2rem 0.5rem;
  border-radius: 9999px;
  background: color-mix(in srgb, var(--tpl-theme) 15%, transparent);
  color: var(--tpl-theme);
  margin-bottom: 0.5rem;
}
.page-tpl__meta { font-size: 0.8125rem; color: var(--text-secondary, #5c5470); margin-top: 0.375rem; }

.page-block { margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border, #e8e4f0); }
.page-block__title {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--tpl-theme);
  margin-bottom: 0.75rem;
}
.page-gallery { display: grid; gap: 0.5rem; }
.page-gallery--grid-2 { grid-template-columns: repeat(2, 1fr); }
.page-gallery--grid-3 { grid-template-columns: repeat(3, 1fr); }
.page-gallery--grid-4 { grid-template-columns: repeat(4, 1fr); }
.page-gallery--masonry { grid-template-columns: repeat(2, 1fr); grid-auto-rows: 4rem; }
.page-gallery--masonry .page-gallery__item:nth-child(3n+1) { grid-row: span 2; }
.page-gallery__item { text-decoration: none; color: inherit; display: block; }
.page-gallery__img {
  aspect-ratio: 1;
  border-radius: 0.5rem;
  background-size: cover;
  background-position: center;
  background-color: var(--bg-subtle, #faf8fd);
}
.page-gallery--masonry .page-gallery__img { aspect-ratio: auto; height: 100%; min-height: 4rem; }
.page-gallery__caption { font-size: 0.625rem; color: var(--text-muted, #8b839c); margin-top: 0.25rem; line-height: 1.3; }

.page-calendar-embed {
  position: relative;
  width: 100%;
  padding-bottom: 75%;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid var(--border, #e8e4f0);
  margin-bottom: 0.75rem;
}
.page-calendar-embed iframe {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.page-events { display: flex; flex-direction: column; gap: 0.5rem; }
.page-event {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  background: var(--bg-subtle, #faf8fd);
  border-radius: 0.625rem;
  border: 1px solid var(--border, #e8e4f0);
}
.page-event__title { font-size: 0.8125rem; font-weight: 700; color: var(--text-primary, #1a1333); }
.page-event__meta { font-size: 0.6875rem; color: var(--text-muted, #8b839c); margin-top: 0.125rem; }
.page-event__btn {
  flex-shrink: 0;
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 0.35rem 0.65rem;
  border-radius: 9999px;
  background: var(--tpl-theme);
  color: #fff;
  text-decoration: none;
  white-space: nowrap;
}

.page-contact { display: flex; flex-direction: column; gap: 0.375rem; }
.page-contact__name { font-size: 0.875rem; font-weight: 700; color: var(--text-primary, #1a1333); margin-bottom: 0.25rem; }
.page-contact__row {
  font-size: 0.8125rem;
  color: var(--text-secondary, #5c5470);
  text-decoration: none;
  line-height: 1.4;
}
a.page-contact__row:hover { color: var(--tpl-theme); }

.page-social { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.page-social__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.4rem 0.75rem;
  border-radius: 9999px;
  border: 1px solid var(--border, #e8e4f0);
  background: var(--bg-subtle, #faf8fd);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-primary, #1a1333);
  text-decoration: none;
  transition: border-color 0.15s, color 0.15s;
}
.page-social__btn:hover { border-color: var(--tpl-theme); color: var(--tpl-theme); }

.page-links { display: flex; flex-direction: column; gap: 0.375rem; }
.page-links__btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 0.875rem;
  border-radius: 0.625rem;
  border: 1px solid var(--border, #e8e4f0);
  background: var(--bg-subtle, #faf8fd);
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-primary, #1a1333);
  text-decoration: none;
}
.page-links__btn:hover { border-color: var(--tpl-theme); color: var(--tpl-theme); }
.page-links__icon { font-size: 1rem; line-height: 1; }
</style>
