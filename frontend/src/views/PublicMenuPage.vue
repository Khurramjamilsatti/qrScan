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
      </div>
    </div>

    <div v-else-if="errorType === 'unpublished'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔒</div>
        <h2>{{ t('public.menuNotPublished') }}</h2>
        <p>{{ t('public.menuNotPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('public.menuNotFound') }}</h2>
        <p>{{ t('public.menuNotFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-center wide">
      <div class="published-badge"><span class="badge-dot"></span>{{ t('public.digitalMenu') }}</div>
      <article class="public-menu">
        <MenuPreview
          :name="menu.name"
          :description="menu.description"
          :logo="menu.logo_path"
          :background-image="menu.background_image_path"
          :theme-color="themeColor"
          :currency="menu.currency"
          :location="menu.location"
          :phone="menu.phone"
          :hours="menu.hours"
          :sections="menu.sections || []"
        />
        <div class="share-qr">
          <p class="share-label">{{ t('digitalMenus.scanToViewMenu') }}</p>
          <QrPreview
            :content="menuUrl"
            :name="menu.name"
            :foreground="themeColor"
            :logo-url="menu.logo_path"
            :background-image="menu.background_image_path"
            :size="160"
          />
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
import MenuPreview from '../components/previews/MenuPreview.vue'
import QrPreview from '../components/previews/QrPreview.vue'

const { t } = useI18n()

const route = useRoute()
const menu = ref(null)
const loading = ref(true)
const errorType = ref(null)

const themeColor = computed(() => menu.value?.theme_color || '#e8655a')
const pageStyle = computed(() => ({ '--page-theme': themeColor.value }))
const menuUrl = computed(() => `${window.location.origin}/menu/${route.params.slug}`)

async function load() {
  loading.value = true
  errorType.value = null
  menu.value = null
  try {
    const { data } = await axios.get(`/api/menu/${route.params.slug}`)
    menu.value = data
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
.brand-link { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: #1a1333; text-decoration: none; font-size: 0.9375rem; }
.brand-icon { color: #e8655a; }
.brand-accent { color: #e8b84a; }
.page-center { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; padding: 1rem 1rem 3rem; max-width: 28rem; margin: 0 auto; }
.page-center.wide { max-width: 32rem; }
.published-badge {
  display: inline-flex; align-items: center; gap: 0.375rem;
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #6b4fa0; margin-bottom: 0.75rem;
}
.badge-dot { width: 0.375rem; height: 0.375rem; border-radius: 50%; background: #e8655a; }
.public-menu { width: 100%; }
.share-qr { margin-top: 1.5rem; text-align: center; }
.share-label { font-size: 0.75rem; font-weight: 600; color: #8b839c; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; }
.loader-card, .state-card {
  width: 100%; background: #fff; border-radius: 1.25rem; padding: 2rem;
  border: 1px solid #e8e4f0; text-align: center; box-shadow: 0 4px 24px rgba(26,19,51,0.06);
}
.skeleton { background: #f0ecf8; border-radius: 0.5rem; margin-bottom: 0.75rem; }
.skeleton-header { height: 4rem; }
.skeleton-line { height: 0.875rem; }
.line-wide { width: 80%; margin: 0 auto; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; color: #1a1333; }
.state-card p { color: #8b839c; font-size: 0.875rem; margin: 0.5rem 0 1.25rem; }
:deep(.menu-preview__sections) { max-height: none; }
</style>
