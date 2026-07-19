<template>
  <div class="invite-public" :class="[`invite-public--${layout}`, { 'invite-public--dark': isDarkLayout }]" :style="pageStyle">
    <div class="invite-public__bg" aria-hidden="true" />

    <header class="invite-public__header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="invite-public__center">
      <div class="loader-card">
        <div class="skeleton skeleton-header"></div>
        <div class="skeleton skeleton-line line-wide"></div>
      </div>
    </div>

    <div v-else-if="errorType === 'unpublished'" class="invite-public__center">
      <div class="state-card">
        <div class="state-icon">🔒</div>
        <h2>{{ t('public.eventNotPublished') }}</h2>
        <p>{{ t('public.eventNotPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="invite-public__center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('public.eventNotFound') }}</h2>
        <p>{{ t('public.eventNotFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="invite-public__main">
      <div class="invite-public__card">
        <article class="invite-public__canvas">
          <EventHtmlPreview
            expandable
            embedded
            public-view
            :title="event.title"
            :subtitle="event.subtitle"
            :hosts="event.hosts"
            :event-date="event.event_date"
            :event-end-date="event.event_end_date"
            :venue-name="event.venue_name"
            :dress-code="event.dress_code"
            :cover-image="event.cover_image_path"
            :theme-color="themeColor"
            :content="event.content || {}"
            :event-type="event.event_type"
            :slug="event.slug"
            :invite-url="inviteUrl"
            :template="event.template || 'simple-invite'"
          />
        </article>

        <footer class="invite-public__footer">
          <p class="invite-public__footer-label">{{ t('digitalEvents.scanToViewInvite') }}</p>
          <div class="invite-public__qr">
            <QrPreview
              :content="inviteUrl"
              :foreground="themeColor"
              :logo-url="event.cover_image_path"
              :background-image="event.cover_image_path"
              :size="132"
              minimal
              :qr-shape="event.qr_shape || 'square'"
              :dot-style="event.dot_style || 'square'"
              :corner-style="event.corner_style || 'sharp'"
              :frame-style="event.frame_style || 'none'"
            />
          </div>
          <p class="invite-public__share">{{ inviteUrl }}</p>
        </footer>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import PublicBrandHeader from '../components/ui/PublicBrandHeader.vue'
import EventHtmlPreview from '../components/previews/EventHtmlPreview.vue'
import QrPreview from '../components/previews/QrPreview.vue'
import { EVENT_TEMPLATES, getEventTemplateLayout } from '../utils/eventTemplates'

const { t } = useI18n()
const route = useRoute()
const event = ref(null)
const loading = ref(true)
const errorType = ref(null)

const themeColor = computed(() => event.value?.theme_color || '#e8655a')
const layout = computed(() => getEventTemplateLayout(event.value?.template || 'simple-invite'))
const isDarkLayout = computed(() => layout.value === 'corporate' || layout.value === 'memorial')
const templateMeta = computed(() => EVENT_TEMPLATES.find((item) => item.id === event.value?.template))
const pageStyle = computed(() => ({
  '--page-theme': themeColor.value,
  '--page-gradient': templateMeta.value?.thumbGradient
    || `linear-gradient(145deg, ${themeColor.value}, color-mix(in srgb, ${themeColor.value} 35%, #1a1333))`,
}))
const inviteUrl = computed(() => `${window.location.origin}/invite/${route.params.slug}`)

async function load() {
  loading.value = true
  errorType.value = null
  event.value = null
  try {
    const { data } = await axios.get(`/api/invite/${route.params.slug}`)
    event.value = data
    document.title = `${data.title} — QRScan Invite`
  } catch (e) {
    if (e.response?.status === 403) errorType.value = 'unpublished'
    else errorType.value = 'notfound'
  } finally {
    loading.value = false
  }
}

watch(() => route.params.slug, load)
onMounted(load)
</script>

<style scoped>
.invite-public {
  min-height: 100dvh;
  position: relative;
  color: var(--text-primary, #1a1333);
  padding-bottom: 0;
}
.invite-public__bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(ellipse at 50% -5%, color-mix(in srgb, var(--page-theme, #e8655a) 16%, transparent), transparent 55%),
    linear-gradient(165deg, #faf8fd 0%, #f5f0ff 48%, color-mix(in srgb, var(--page-theme, #e8655a) 8%, #fff) 100%);
}
.invite-public--wedding .invite-public__bg {
  background:
    radial-gradient(ellipse at 50% 0%, color-mix(in srgb, var(--page-theme, #c9a227) 18%, transparent), transparent 58%),
    linear-gradient(165deg, #f8f4ee 0%, #f3ebe0 55%, #fff 100%);
}
.invite-public--birthday .invite-public__bg {
  background:
    radial-gradient(ellipse at 80% 0%, color-mix(in srgb, var(--page-theme, #e8655a) 14%, transparent), transparent 50%),
    linear-gradient(180deg, #fff5f7 0%, #fef3c7 50%, #fff 100%);
}
.invite-public--corporate.invite-public--dark .invite-public__bg,
.invite-public--memorial.invite-public--dark .invite-public__bg {
  background:
    radial-gradient(ellipse at 50% 0%, color-mix(in srgb, var(--page-theme, #64748b) 20%, transparent), transparent 55%),
    linear-gradient(165deg, #0f172a 0%, #1e293b 55%, #0f172a 100%);
}
.invite-public--gift .invite-public__bg {
  background:
    radial-gradient(ellipse at 50% 0%, color-mix(in srgb, var(--page-theme, #be185d) 16%, transparent), transparent 55%),
    linear-gradient(165deg, #fff1f2 0%, #fce7f3 55%, #fff 100%);
}
.invite-public--holiday .invite-public__bg {
  background:
    radial-gradient(ellipse at 30% 0%, color-mix(in srgb, var(--page-theme, #16a34a) 14%, transparent), transparent 50%),
    linear-gradient(165deg, #ecfdf5 0%, #fef3c7 50%, #fff 100%);
}
.invite-public__header {
  position: relative;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.875rem 1.25rem;
  max-width: 28rem;
  margin: 0 auto;
}
.invite-public__center {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  padding: 2rem 1rem 3rem;
  max-width: 28rem;
  margin: 0 auto;
}
.invite-public__main {
  position: relative;
  z-index: 1;
  width: min(100%, 440px);
  margin: 0 auto;
  padding: 0 1rem 1.5rem;
}
.invite-public__card {
  border-radius: 1.375rem;
  overflow: hidden;
  background: #fff;
  box-shadow:
    0 0 0 1px color-mix(in srgb, var(--page-theme, #e8655a) 10%, rgba(0, 0, 0, 0.06)),
    0 24px 56px rgba(26, 19, 51, 0.14);
}
.invite-public--dark .invite-public__card {
  background: #1e293b;
  box-shadow:
    0 0 0 1px #334155,
    0 24px 56px rgba(0, 0, 0, 0.4);
}
.invite-public__canvas {
  overflow: visible;
  line-height: 0;
}
.invite-public__canvas :deep(.event-html-preview),
.invite-public__canvas :deep(.html-doc-preview),
.invite-public__canvas :deep(.html-doc-preview__frame) {
  width: 100%;
  display: block;
  margin: 0;
  padding: 0;
}
.invite-public__footer {
  padding: 1rem 1.25rem 1.125rem;
  text-align: center;
  background: color-mix(in srgb, var(--page-theme, #e8655a) 4%, #fff);
  border-top: 1px solid color-mix(in srgb, var(--page-theme, #e8655a) 12%, #e8e4f0);
}
.invite-public--dark .invite-public__footer {
  background: rgba(15, 23, 42, 0.6);
  border-color: #334155;
  color: #e2e8f0;
}
.invite-public__footer-label {
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #8b839c;
  margin-bottom: 0.625rem;
}
.invite-public--dark .invite-public__footer-label { color: #94a3b8; }
.invite-public__qr { display: flex; justify-content: center; margin-bottom: 0.625rem; }
.invite-public__share {
  font-size: 0.625rem;
  font-family: ui-monospace, monospace;
  color: var(--page-theme, #e8655a);
  word-break: break-all;
  line-height: 1.45;
}
.loader-card, .state-card {
  width: 100%;
  background: #fff;
  border-radius: 1.25rem;
  padding: 2rem;
  border: 1px solid #e8e4f0;
  text-align: center;
  box-shadow: 0 4px 24px rgba(26,19,51,0.06);
}
.skeleton { background: #f0ecf8; border-radius: 0.5rem; margin-bottom: 0.75rem; }
.skeleton-header { height: 4rem; }
.skeleton-line { height: 0.875rem; }
.line-wide { width: 80%; margin: 0 auto; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; color: #1a1333; }
.state-card p { color: #8b839c; font-size: 0.875rem; margin: 0.5rem 0 1.25rem; }
</style>
