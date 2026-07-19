<template>
  <div class="public-page" :style="pageStyle">
    <div class="page-bg" aria-hidden="true" />
    <header class="page-header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="page-center">
      <div class="loader-card">
        <div class="skeleton skeleton-lg" />
        <div class="skeleton skeleton-sm" />
      </div>
    </div>

    <div v-else-if="errorType" class="page-center">
      <div class="state-card">
        <div class="state-icon">{{ errorType === 'unpublished' ? '🔒' : '🔍' }}</div>
        <h2>{{ errorType === 'unpublished' ? t('digitalCertificates.notPublishedTitle') : t('digitalCertificates.notFoundTitle') }}</h2>
        <p>{{ errorType === 'unpublished' ? t('digitalCertificates.notPublishedDesc') : t('digitalCertificates.notFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-main">
      <div class="cert-badge">
        <span class="cert-badge__dot" />
        {{ t('public.digitalCertificate') }}
      </div>

      <article class="cert-stage">
        <CertificateRenderer
          :title="cert.title"
          :template="cert.template"
          :recipient-name="cert.recipient_name"
          :award-title="cert.award_title"
          :issuer-name="cert.issuer_name"
          :certificate-id="cert.certificate_id"
          :description="cert.description"
          :completion-date="cert.completion_date"
          :issue-date="cert.issue_date"
          :settings="cert.settings || {}"
          :theme-color="themeColor"
          :logo="cert.logo_path"
          :seal="cert.seal_path"
          :instructor-signature="cert.instructor_signature_path"
          :organization-signature="cert.organization_signature_path"
          :background-image="cert.background_image_path"
          :verify-url="cert.verify_url"
        />
      </article>

      <div class="cert-actions">
        <a :href="`/verify/${cert.certificate_id}`" class="action-card">
          <span class="action-card__icon">✓</span>
          <span class="action-card__label">{{ t('digitalCertificates.verifyOnline') }}</span>
        </a>
        <a :href="pdfUrl" class="action-card" target="_blank" rel="noopener">
          <span class="action-card__icon">↓</span>
          <span class="action-card__label">{{ t('digitalCertificates.downloadPdf') }}</span>
        </a>
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
import CertificateRenderer from '../components/certificates/CertificateRenderer.vue'

const { t } = useI18n()
const route = useRoute()
const cert = ref(null)
const loading = ref(true)
const errorType = ref(null)

const themeColor = computed(() => cert.value?.theme_color || '#1a1333')
const pageStyle = computed(() => ({
  '--page-theme': themeColor.value,
  '--page-accent': `color-mix(in srgb, ${themeColor.value} 65%, #c9a227)`,
}))
const pdfUrl = computed(() => cert.value ? `/api/verify/${cert.value.certificate_id}/pdf` : '#')

async function load() {
  loading.value = true
  errorType.value = null
  try {
    const { data } = await axios.get(`/api/certificate/${route.params.slug}`)
    cert.value = data
  } catch (e) {
    errorType.value = e.response?.status === 403 ? 'unpublished' : 'notfound'
  } finally {
    loading.value = false
  }
}

watch(() => route.params.slug, load)
onMounted(load)
</script>

<style scoped>
.public-page {
  min-height: 100vh;
  position: relative;
  --page-theme: #1a1333;
  --page-accent: #5b4bb7;
}

.page-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  background:
    radial-gradient(ellipse 80% 50% at 50% -10%, color-mix(in srgb, var(--page-accent) 18%, transparent), transparent),
    radial-gradient(ellipse 60% 40% at 100% 100%, color-mix(in srgb, var(--page-theme) 8%, transparent), transparent),
    linear-gradient(165deg, #faf8fd 0%, #f3eef9 40%, #eef4fb 100%);
}

.page-header {
  position: relative;
  z-index: 10;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  max-width: 56rem;
  margin: 0 auto;
}

.page-center {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  padding: 2rem 1rem 4rem;
  max-width: 28rem;
  margin: 0 auto;
}

.page-main {
  position: relative;
  z-index: 1;
  max-width: 56rem;
  margin: 0 auto;
  padding: 0 1rem 3.5rem;
}

.cert-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--page-accent);
  margin-bottom: 1rem;
}

.cert-badge__dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--page-accent);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--page-accent) 25%, transparent);
}

.cert-stage {
  width: 100%;
  animation: fadeUp 0.5s ease-out;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

.cert-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: center;
  margin-top: 1.75rem;
}

.action-card {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.9);
  border-radius: 9999px;
  text-decoration: none;
  color: var(--page-theme);
  font-size: 0.8125rem;
  font-weight: 600;
  box-shadow: 0 4px 16px rgba(26, 19, 51, 0.06);
  transition: transform 0.15s, box-shadow 0.15s;
}

.action-card:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(26, 19, 51, 0.1);
}

.action-card__icon {
  width: 1.75rem;
  height: 1.75rem;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: color-mix(in srgb, var(--page-theme) 8%, #fff);
  font-size: 0.875rem;
}

.loader-card, .state-card {
  width: 100%;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 1.25rem;
  padding: 2rem;
  border: 1px solid #e8e4f0;
  text-align: center;
  box-shadow: 0 8px 32px rgba(26, 19, 51, 0.06);
}

.skeleton { background: linear-gradient(90deg, #f0ecf8 25%, #e8e4f0 50%, #f0ecf8 75%); background-size: 200% 100%; border-radius: 0.5rem; animation: shimmer 1.2s infinite; }
.skeleton-lg { height: 280px; margin-bottom: 0.75rem; }
.skeleton-sm { height: 1rem; width: 60%; margin: 0 auto; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; color: #1a1333; }
.state-card p { color: #8b839c; font-size: 0.875rem; margin: 0.5rem 0 1.25rem; }
</style>
