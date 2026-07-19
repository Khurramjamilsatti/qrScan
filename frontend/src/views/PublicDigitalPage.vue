<template>
  <div class="public-page public-page-light" :style="pageStyle">
    <div class="page-bg" aria-hidden="true"></div>
    <header class="page-header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="page-center">
      <div class="loader-card">
        <div class="skeleton skeleton-header"></div>
        <div class="skeleton skeleton-line line-wide"></div>
        <div class="skeleton skeleton-line line-narrow"></div>
      </div>
    </div>

    <div v-else-if="errorType === 'unpublished'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔒</div>
        <h2>{{ t('public.pageNotPublished') }}</h2>
        <p>{{ t('public.pageNotPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('public.pageNotFound') }}</h2>
        <p>{{ t('public.pageNotFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-center" :class="{ wide: hasGallery }">
      <div class="published-badge"><span class="badge-dot"></span>{{ t('public.digitalPage') }}</div>
      <article class="public-card">
        <HtmlDocumentPreview
          :html="pageHtml"
          title="Digital page"
          expandable
          embedded
        />
        <div class="page-qr">
          <p class="page-qr__label">{{ t('public.shareThisPage') }}</p>
          <div class="page-qr__code">
            <QrPreview
              :content="pageUrl"
              :foreground="themeColor"
              :logo-url="page.logo_path"
              :background-image="page.background_image_path"
              :size="160"
              minimal
              :qr-shape="page.qr_shape || 'square'"
              :dot-style="page.dot_style || 'square'"
              :corner-style="page.corner_style || 'sharp'"
              :frame-style="page.frame_style || 'none'"
            />
          </div>
        </div>
      </article>
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
import HtmlDocumentPreview from '../components/previews/HtmlDocumentPreview.vue'
import QrPreview from '../components/previews/QrPreview.vue'
import { mergePageContent } from '../utils/pageTemplates'
import { renderPageHtml } from '../utils/pageHtmlRenderer'

const { t } = useI18n()

const route = useRoute()
const page = ref(null)
const loading = ref(true)
const errorType = ref(null)

const themeColor = computed(() => page.value?.theme_color || '#e8655a')
const pageStyle = computed(() => ({ '--page-theme': themeColor.value }))
const pageUrl = computed(() => `${window.location.origin}/page/${route.params.slug}`)
const pageContent = computed(() => {
  if (!page.value) return {}
  return mergePageContent(page.value.template, page.value.content || {})
})
const pageHtml = computed(() => page.value ? renderPageHtml({
  title: page.value.title,
  template: page.value.template,
  content: pageContent.value,
  themeColor: themeColor.value,
  logo: page.value.logo_path,
  backgroundImage: page.value.background_image_path,
  publicView: true,
}) : '')
const hasGallery = computed(() => {
  const g = page.value?.content?.gallery
  return g?.enabled && (g?.items?.length || 0) > 0
})

async function load() {
  loading.value = true
  errorType.value = null
  page.value = null
  try {
    const { data } = await axios.get(`/api/page/${route.params.slug}`)
    page.value = data
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
.public-page { min-height: 100vh; position: relative; }
.page-bg {
  position: fixed; inset: 0; z-index: 0;
  background: linear-gradient(160deg, #faf8fd 0%, #f5f0ff 50%, #fde8e6 100%);
}
.page-header {
  position: relative; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.5rem; max-width: 48rem; margin: 0 auto;
}
.page-center {
  position: relative; z-index: 1;
  display: flex; flex-direction: column; align-items: center;
  padding: 1rem 1rem 3rem; max-width: 28rem; margin: 0 auto;
}
.page-center.wide { max-width: 36rem; }
.published-badge {
  display: inline-flex; align-items: center; gap: 0.375rem;
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #6b4fa0; margin-bottom: 0.75rem;
}
.badge-dot { width: 0.375rem; height: 0.375rem; border-radius: 50%; background: #e8655a; }

.public-card {
  width: 100%;
  background: #fff;
  border-radius: 1.5rem;
  border: 1px solid #e8e4f0;
  box-shadow: 0 8px 28px rgba(26, 19, 51, 0.08);
  overflow: hidden;
}

.page-qr {
  padding: 1.25rem 1.5rem 1.5rem;
  border-top: 1px solid #e8e4f0;
  text-align: center;
}
.page-qr__label {
  font-size: 0.6875rem;
  font-weight: 700;
  color: #8b839c;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  margin-bottom: 0.75rem;
}
.page-qr__code {
  display: flex;
  justify-content: center;
}
.page-qr__code :deep(.qr-preview-card--minimal) {
  padding: 0;
  background: transparent;
  border: none;
  box-shadow: none;
}
.page-qr__code :deep(.qr-frame) {
  min-height: auto;
  padding: 0;
  background: transparent !important;
}

.loader-card, .state-card {
  width: 100%; background: #fff; border-radius: 1.25rem; padding: 2rem;
  border: 1px solid #e8e4f0; text-align: center; box-shadow: 0 4px 24px rgba(26,19,51,0.06);
}
.skeleton { background: #f0ecf8; border-radius: 0.5rem; margin-bottom: 0.75rem; }
.skeleton-header { height: 4rem; }
.skeleton-line { height: 0.875rem; }
.line-wide { width: 80%; margin: 0 auto 0.75rem; }
.line-narrow { width: 50%; margin: 0 auto; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; color: #1a1333; }
.state-card p { color: #8b839c; font-size: 0.875rem; margin: 0.5rem 0 1.25rem; }
</style>
