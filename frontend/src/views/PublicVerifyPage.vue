<template>
  <div class="public-page" :style="pageStyle">
    <div class="page-bg" aria-hidden="true" />
    <header class="page-header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="page-center">
      <div class="loader-card"><div class="skeleton" /></div>
    </div>

    <div v-else-if="errorType" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('digitalCertificates.notFoundTitle') }}</h2>
        <p>{{ t('digitalCertificates.notFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-main">
      <div class="status-pill" :class="cert.is_valid ? 'valid' : 'invalid'">
        <span class="status-pill__dot" />
        {{ cert.is_valid ? t('digitalCertificates.statusValid') : (cert.status === 'revoked' ? t('digitalCertificates.statusRevoked') : t('digitalCertificates.statusExpired')) }}
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
          :theme-color="cert.theme_color || '#1a1333'"
          :logo="cert.logo_path"
          :seal="cert.seal_path"
          :instructor-signature="cert.instructor_signature_path"
          :organization-signature="cert.organization_signature_path"
          :background-image="cert.background_image_path"
          :verify-url="cert.verify_url"
        />
      </article>

      <div class="verify-actions">
        <a :href="pdfUrl" class="btn-primary" target="_blank" rel="noopener">{{ t('digitalCertificates.downloadPdf') }}</a>
        <router-link v-if="cert.slug" :to="`/certificate/${cert.slug}`" class="btn-secondary">{{ t('digitalCertificates.viewCertificate') }}</router-link>
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

const pageStyle = computed(() => ({
  '--page-theme': cert.value?.theme_color || '#1a1333',
  '--page-accent': `color-mix(in srgb, ${cert.value?.theme_color || '#1a1333'} 65%, #c9a227)`,
}))
const pdfUrl = computed(() => `/api/verify/${route.params.certificateId}/pdf`)

async function load() {
  loading.value = true
  errorType.value = null
  try {
    const { data } = await axios.get(`/api/verify/${route.params.certificateId}`)
    cert.value = data
  } catch {
    errorType.value = 'notfound'
  } finally {
    loading.value = false
  }
}

watch(() => route.params.certificateId, load)
onMounted(load)
</script>

<style scoped>
.public-page { min-height: 100vh; position: relative; }
.page-bg {
  position: fixed; inset: 0; z-index: 0;
  background:
    radial-gradient(ellipse 70% 45% at 50% 0%, color-mix(in srgb, var(--page-accent) 15%, transparent), transparent),
    linear-gradient(165deg, #faf8fd 0%, #f3eef9 50%, #eef4fb 100%);
}
.page-header {
  position: relative; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.5rem; max-width: 40rem; margin: 0 auto;
}
.page-center { position: relative; z-index: 1; padding: 2rem 1rem; max-width: 28rem; margin: 0 auto; }
.page-main { position: relative; z-index: 1; max-width: 56rem; margin: 0 auto; padding: 0 1rem 3rem; }
.cert-stage { width: 100%; margin-bottom: 1.5rem; }

.status-pill {
  display: inline-flex; align-items: center; gap: 0.5rem;
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
  padding: 0.4rem 0.875rem; border-radius: 9999px; margin-bottom: 1rem;
}
.status-pill__dot { width: 6px; height: 6px; border-radius: 50%; }
.status-pill.valid { background: #dcfce7; color: #166534; }
.status-pill.valid .status-pill__dot { background: #22c55e; }
.status-pill.invalid { background: #fee2e2; color: #991b1b; }
.status-pill.invalid .status-pill__dot { background: #ef4444; }

.verify-actions { display: flex; flex-wrap: wrap; gap: 0.625rem; justify-content: center; }
.btn-secondary {
  display: inline-block; padding: 0.625rem 1.125rem; border-radius: 0.5rem;
  border: 1px solid #d9d3ea; text-decoration: none; color: #1a1333;
  font-weight: 600; font-size: 0.875rem;
}
.loader-card, .state-card {
  background: #fff; border-radius: 1.25rem; padding: 2rem;
  border: 1px solid #e8e4f0; text-align: center;
}
.skeleton { height: 200px; background: #f0ecf8; border-radius: 0.5rem; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
</style>
