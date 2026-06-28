<template>
  <div class="site-page min-h-screen bg-page">
    <div class="page-bg" aria-hidden="true"></div>
    <header class="page-header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="page-center">
      <div class="loader-card">
        <div class="skeleton skeleton-line line-wide"></div>
        <div class="skeleton skeleton-line line-narrow"></div>
      </div>
    </div>

    <div v-else-if="error" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2 class="state-title">{{ t('public.pageNotFound') }}</h2>
        <router-link to="/" class="btn-primary">{{ t('common.goHome') }}</router-link>
      </div>
    </div>

    <main v-else class="page-center">
      <article class="content-card">
        <p v-if="page.intro" class="content-intro">{{ page.intro }}</p>
        <h1 class="content-title">{{ page.title }}</h1>

        <div v-if="hasContact" class="contact-box">
          <a v-if="page.contact_info?.email" :href="`mailto:${page.contact_info.email}`" class="contact-row">
            ✉ {{ page.contact_info.email }}
          </a>
          <a v-if="page.contact_info?.phone" :href="`tel:${page.contact_info.phone}`" class="contact-row">
            📞 {{ page.contact_info.phone }}
          </a>
          <div v-if="page.contact_info?.address" class="contact-row">📍 {{ page.contact_info.address }}</div>
          <div v-if="page.contact_info?.hours" class="contact-row">🕐 {{ page.contact_info.hours }}</div>
        </div>

        <div class="content-body">
          <p v-for="(block, i) in contentBlocks" :key="i" v-html="formatBlock(block)"></p>
        </div>
      </article>
    </main>

    <footer class="site-footer">
      <nav class="footer-links">
        <router-link to="/support">{{ t('nav.support') }}</router-link>
        <router-link to="/contact">{{ t('nav.contact') }}</router-link>
        <router-link to="/privacy">{{ t('nav.privacy') }}</router-link>
        <router-link to="/terms">{{ t('nav.terms') }}</router-link>
      </nav>
      <router-link to="/" class="footer-home">← {{ t('common.backToHome') }}</router-link>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import publicApi from '../services/publicApi'
import { useLocaleWatch } from '../composables/useLocaleWatch'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import PublicBrandHeader from '../components/ui/PublicBrandHeader.vue'

const { t } = useI18n()

const props = defineProps({
  slug: { type: String, default: '' },
})

const route = useRoute()
const page = ref(null)
const loading = ref(true)
const error = ref(false)

const pageSlug = computed(() => props.slug || route.meta.pageSlug || route.params.slug)

const hasContact = computed(() => {
  const c = page.value?.contact_info
  return c && (c.email || c.phone || c.address || c.hours)
})

const contentBlocks = computed(() => {
  const text = page.value?.content || ''
  return text.split(/\n\n+/).filter(Boolean)
})

function formatBlock(block) {
  return block
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>')
}

async function load() {
  loading.value = true
  error.value = false
  page.value = null
  try {
    const { data } = await publicApi.get(`/pages/${pageSlug.value}`)
    page.value = data
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

watch(pageSlug, load)
useLocaleWatch(load)
onMounted(load)
</script>

<style scoped>
.site-page { position: relative; color: var(--text-primary); }
.page-bg {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background: var(--gradient-hero);
}
.page-header {
  position: relative; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.5rem; max-width: 48rem; margin: 0 auto;
}
.brand-link { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-primary); text-decoration: none; }
.brand-icon { color: var(--brand); }
.brand-accent { color: var(--gold); }
.page-center { position: relative; z-index: 1; padding: 1rem 1rem 3rem; max-width: 40rem; margin: 0 auto; }
.content-card {
  background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem;
  padding: 2rem; box-shadow: var(--shadow-sm);
}
.content-intro { font-size: 0.9375rem; color: var(--purple); font-weight: 600; margin-bottom: 0.5rem; }
.content-title { font-size: 1.75rem; font-weight: 800; margin-bottom: 1.25rem; line-height: 1.2; color: var(--text-primary); }
.contact-box {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem;
  padding: 1rem; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.5rem;
}
.contact-row { font-size: 0.875rem; color: var(--text-secondary); text-decoration: none; line-height: 1.5; }
a.contact-row:hover { color: var(--brand); }
.content-body { display: flex; flex-direction: column; gap: 1rem; }
.content-body :deep(p), .content-body p { font-size: 0.9375rem; color: var(--text-secondary); line-height: 1.7; margin: 0; }
.content-body :deep(strong) { color: var(--text-primary); font-weight: 700; }
.site-footer {
  position: relative; z-index: 1; text-align: center; padding: 2rem;
  font-size: 0.875rem; display: flex; flex-direction: column; gap: 1rem; align-items: center;
}
.footer-links { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; }
.footer-links a { color: var(--text-secondary); text-decoration: none; font-weight: 500; }
.footer-links a:hover, .footer-home:hover { color: var(--brand); }
.footer-home { color: var(--brand); text-decoration: none; font-weight: 600; }
.loader-card, .state-card {
  background: var(--surface); border-radius: 1.25rem; padding: 2rem;
  border: 1px solid var(--border); text-align: center;
}
.state-title { color: var(--text-primary); margin-bottom: 1rem; }
.skeleton { background: var(--bg-subtle); border-radius: 0.5rem; height: 0.875rem; margin-bottom: 0.75rem; }
.line-wide { width: 80%; margin: 0 auto 0.75rem; }
.line-narrow { width: 50%; margin: 0 auto; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
</style>
