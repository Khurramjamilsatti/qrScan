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
        <div class="skeleton skeleton-avatar"></div>
        <div class="skeleton skeleton-line line-wide"></div>
        <div class="skeleton skeleton-line line-narrow"></div>
      </div>
    </div>

    <div v-else-if="errorType === 'unpublished'" class="page-center">
      <div class="state-card">
        <div class="state-icon unpublished">🔒</div>
        <h2>{{ t('public.cardNotPublished') }}</h2>
        <p>{{ t('public.cardNotPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('public.cardNotFound') }}</h2>
        <p>{{ t('public.cardNotFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-center">
      <div class="published-badge">
        <span class="badge-dot"></span>
        {{ t('public.publicProfile') }}
      </div>
      <article class="profile-card" :class="`profile-card--${cardLayout}`">
        <div
          v-if="cardLayout !== 'minimal'"
          class="profile-header"
          :class="{ 'has-bg': bgUrl }"
          :style="headerStyle"
        >
          <div class="header-overlay"></div>
          <img v-if="logoUrl" :src="logoUrl" alt="" class="profile-logo" />
        </div>

        <div class="profile-body" :class="{ 'profile-body--minimal': cardLayout === 'minimal' }">
          <div class="avatar-wrap">
            <div
              class="avatar-ring"
              :class="{ 'avatar-ring--round': cardLayout === 'modern' || cardLayout === 'minimal' }"
              :style="{ '--ring': themeColor }"
            >
              <img v-if="photoUrl" :src="photoUrl" alt="" class="avatar-img" />
              <span v-else class="avatar-letter">{{ initial }}</span>
            </div>
          </div>

          <img
            v-if="cardLayout === 'minimal' && logoUrl"
            :src="logoUrl"
            alt=""
            class="profile-logo-minimal"
          />

          <h1 class="profile-name">{{ card.full_name }}</h1>
          <p v-if="card.tagline" class="profile-tagline">{{ card.tagline }}</p>
          <p v-if="card.job_title || card.company" class="profile-role">
            <span v-if="card.job_title">{{ card.job_title }}</span>
            <span v-if="card.job_title && card.company"> · </span>
            <span v-if="card.company">{{ card.company }}</span>
          </p>

          <p v-if="card.bio" class="profile-bio">{{ card.bio }}</p>

          <div v-if="hasContactInfo" class="contact-list">
            <a v-if="card.email" :href="`mailto:${card.email}`" class="contact-item">✉ {{ card.email }}</a>
            <a v-if="card.phone" :href="`tel:${card.phone}`" class="contact-item">📞 {{ card.phone }}</a>
            <a v-if="card.website" :href="card.website" target="_blank" rel="noopener" class="contact-item">🌐 {{ cleanUrl(card.website) }}</a>
            <div v-if="card.address" class="contact-item">📍 {{ card.address }}</div>
          </div>

          <div v-if="card.social_links?.length" class="social-row">
            <a
              v-for="(s, i) in card.social_links"
              :key="i"
              :href="s.url"
              target="_blank"
              rel="noopener"
              class="social-pill"
            >
              {{ s.platform || t('templates.sections.fallbackLink') }}
            </a>
          </div>
        </div>

        <div class="page-qr">
          <p class="page-qr__label">{{ t('public.scanToSave') }}</p>
          <div class="page-qr__code">
            <QrPreview
              :content="shareUrl"
              :foreground="themeColor"
              :background="'#ffffff'"
              :logo-url="card.logo_path"
              :background-image="card.background_image_path"
              :size="160"
              minimal
              :qr-shape="card.qr_shape || 'square'"
              :dot-style="card.dot_style || 'square'"
              :corner-style="card.corner_style || 'sharp'"
              :frame-style="card.frame_style || 'none'"
            />
          </div>
          <button type="button" class="save-contact-btn" @click="saveVcard">
            {{ t('public.saveContact') }}
          </button>
          <button type="button" class="share-btn" @click="shareCard">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            {{ t('public.shareCard') }}
          </button>
        </div>
      </article>

      <p class="powered">
        {{ t('public.digitalBusinessCardBy') }}
        <router-link to="/">{{ t('common.brand') }}</router-link>
      </p>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import QrPreview from '../components/previews/QrPreview.vue'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import PublicBrandHeader from '../components/ui/PublicBrandHeader.vue'
import { downloadVcard } from '../utils/vcard'
import { resolveStorageUrl } from '../utils/storageUrl'
import { getCardTemplateLayout } from '../utils/digitalModules'

const { t } = useI18n()

const route = useRoute()
const card = ref(null)
const loading = ref(true)
const errorType = ref(null)

const themeColor = computed(() => card.value?.theme_color || '#e8655a')
const cardLayout = computed(() => getCardTemplateLayout(card.value?.template))
const photoUrl = computed(() => resolveStorageUrl(card.value?.photo_path))
const logoUrl = computed(() => resolveStorageUrl(card.value?.logo_path))
const bgUrl = computed(() => resolveStorageUrl(card.value?.background_image_path))
const initial = computed(() => card.value?.full_name?.charAt(0)?.toUpperCase() || '?')
const shareUrl = computed(() => `${window.location.origin}/card/${route.params.slug}`)

const pageStyle = computed(() => ({ '--card-theme': themeColor.value }))
const hasContactInfo = computed(() =>
  card.value?.email || card.value?.phone || card.value?.website || card.value?.address
)
const headerStyle = computed(() => {
  if (bgUrl.value) {
    return { backgroundImage: `url(${bgUrl.value})`, backgroundSize: 'cover', backgroundPosition: 'center' }
  }
  return { background: `linear-gradient(135deg, ${themeColor.value}, color-mix(in srgb, ${themeColor.value} 55%, #1a1333))` }
})

function cleanUrl(url) {
  return url?.replace(/^https?:\/\//, '') || ''
}

async function loadCard() {
  loading.value = true
  errorType.value = null
  card.value = null
  try {
    const { data } = await axios.get(`/api/card/${route.params.slug}`)
    if (!data.is_active) {
      errorType.value = 'unpublished'
      return
    }
    card.value = data
    document.title = `${data.full_name} — QRScan Card`
  } catch (e) {
    errorType.value = e.response?.status === 403 ? 'unpublished' : 'notfound'
  } finally {
    loading.value = false
  }
}

async function saveVcard() {
  try {
    await downloadVcard(route.params.slug, `${route.params.slug}.vcf`)
  } catch { /* ignore */ }
}

async function shareCard() {
  if (navigator.share) {
    try {
      await navigator.share({ title: card.value?.full_name, url: shareUrl.value })
      return
    } catch { /* cancelled */ }
  }
  try {
    await navigator.clipboard.writeText(shareUrl.value)
  } catch { /* ignore */ }
}

onMounted(loadCard)
watch(() => route.params.slug, loadCard)
</script>

<style scoped>
.public-page {
  min-height: 100vh;
  background: var(--bg-page);
  position: relative;
  --card-theme: #e8655a;
}
.page-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background:
    radial-gradient(ellipse 80% 50% at 20% 0%, color-mix(in srgb, var(--card-theme) 18%, transparent), transparent),
    radial-gradient(ellipse 60% 40% at 80% 100%, color-mix(in srgb, var(--gold) 12%, transparent), transparent),
    var(--bg-page);
}
.page-header {
  position: relative;
  z-index: 10;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  max-width: 32rem;
  margin: 0 auto;
}

.page-center {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1rem 1rem 3rem;
  max-width: 28rem;
  margin: 0 auto;
}

.profile-card {
  width: 100%;
  background: var(--surface);
  border-radius: 1.5rem;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  position: relative;
}
.published-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--brand);
  background: var(--brand-muted);
  border: 1px solid color-mix(in srgb, var(--brand) 30%, var(--border));
  padding: 0.3rem 0.75rem;
  border-radius: 9999px;
  margin-bottom: 0.75rem;
}
.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--brand);
  animation: pulseSoft 2s ease-in-out infinite;
}
.profile-header {
  height: 7.5rem;
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 0.75rem;
  border-radius: 1.5rem 1.5rem 0 0;
  overflow: hidden;
}
.profile-header.has-bg .header-overlay {
  background: linear-gradient(to bottom, transparent 20%, rgba(0,0,0,0.4));
}
.header-overlay {
  position: absolute;
  inset: 0;
  z-index: 1;
  pointer-events: none;
}
.profile-logo {
  position: relative;
  z-index: 3;
  width: 2.75rem;
  height: 2.75rem;
  object-fit: contain;
  background: white;
  border-radius: 0.625rem;
  padding: 0.25rem;
  box-shadow: var(--shadow-md);
}
.profile-body {
  position: relative;
  z-index: 5;
  padding: 0 1.5rem 1.5rem;
  margin-top: -3.25rem;
  text-align: center;
}
.profile-body--minimal {
  margin-top: 0;
  padding-top: 1.75rem;
}
.profile-logo-minimal {
  width: 2.5rem;
  height: 2.5rem;
  object-fit: contain;
  margin: 0.75rem auto 0;
  display: block;
}
.avatar-wrap {
  position: relative;
  z-index: 10;
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}
.avatar-ring {
  position: relative;
  z-index: 10;
  width: 6.5rem;
  height: 6.5rem;
  border-radius: 1.25rem;
  padding: 3px;
  background: linear-gradient(135deg, var(--ring), color-mix(in srgb, var(--ring) 50%, var(--gold)));
  box-shadow: 0 8px 28px color-mix(in srgb, var(--ring) 35%, transparent);
}
.avatar-ring .avatar-img,
.avatar-ring .avatar-letter {
  position: relative;
  z-index: 11;
  width: 100%;
  height: 100%;
  border-radius: 1.1rem;
  object-fit: cover;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--surface);
  font-size: 2.25rem;
  font-weight: 800;
  color: var(--ring);
}
.avatar-ring--round { border-radius: 50%; }
.avatar-ring--round .avatar-img,
.avatar-ring--round .avatar-letter { border-radius: 50%; }

.profile-card--modern .profile-header { height: 6rem; }
.profile-card--modern .profile-body { margin-top: -3.5rem; }
.profile-card--modern .avatar-ring { width: 7rem; height: 7rem; }

.profile-card--bold .profile-header { height: 9rem; align-items: center; padding: 1rem 1.25rem; }
.profile-card--bold .profile-body { margin-top: -3.75rem; }
.profile-card--bold .profile-name { font-size: 2rem; }
.profile-card--bold .avatar-ring { width: 7.5rem; height: 7.5rem; }

.profile-card--minimal .profile-name { font-size: 1.5rem; }
.profile-card--minimal .profile-bio { text-align: center; }
.profile-card--modern .contact-list,
.profile-card--minimal .contact-list { align-items: center; }
.profile-card--modern .social-row,
.profile-card--minimal .social-row { justify-content: center; }

.contact-list {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
  text-align: left;
}
.contact-item {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  text-decoration: none;
  word-break: break-word;
  transition: color 0.2s;
}
a.contact-item:hover { color: var(--card-theme); }

.social-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
  margin-top: 0.875rem;
}
.social-pill {
  font-size: 0.6875rem;
  padding: 0.25rem 0.625rem;
  background: color-mix(in srgb, var(--card-theme) 12%, var(--surface));
  color: var(--card-theme);
  border-radius: 9999px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}
.social-pill:hover {
  background: var(--card-theme);
  color: white;
}

.profile-name {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1.2;
  margin: 0;
}
.profile-tagline {
  margin: 0.375rem 0 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--card-theme);
}
.profile-role {
  margin: 0.25rem 0 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}
.profile-bio {
  margin: 1rem 0 0;
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--text-secondary);
  text-align: left;
}

.page-qr {
  padding: 1.25rem 1.5rem 1.5rem;
  border-top: 1px solid var(--border);
  text-align: center;
}
.page-qr__label {
  font-size: 0.6875rem;
  font-weight: 700;
  color: var(--text-muted);
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
.save-contact-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  margin-top: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  border: none;
  background: var(--card-theme);
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: filter 0.2s;
}
.save-contact-btn:hover { filter: brightness(1.05); }
.share-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  margin-top: 0.75rem;
  padding: 0.625rem;
  border-radius: 0.75rem;
  border: 1px dashed var(--border);
  background: transparent;
  color: var(--text-secondary);
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.share-btn:hover {
  border-color: var(--card-theme);
  color: var(--card-theme);
  background: color-mix(in srgb, var(--card-theme) 6%, transparent);
}

.powered {
  margin-top: 1.5rem;
  font-size: 0.75rem;
  color: var(--text-muted);
  text-align: center;
}
.powered a {
  color: var(--brand);
  font-weight: 600;
  text-decoration: none;
}

.state-card {
  text-align: center;
  padding: 2.5rem 1.5rem;
  background: var(--surface);
  border-radius: 1.25rem;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  width: 100%;
}
.state-icon {
  font-size: 2.5rem;
  margin-bottom: 1rem;
}
.state-icon.unpublished { opacity: 0.8; }
.state-card h2 {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 0.5rem;
}
.state-card p {
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin: 0 0 1.25rem;
  line-height: 1.6;
}

.loader-card {
  width: 100%;
  padding: 1.5rem;
  background: var(--surface);
  border-radius: 1.25rem;
  border: 1px solid var(--border);
}
.skeleton {
  background: linear-gradient(90deg, var(--bg-subtle) 25%, var(--border) 50%, var(--bg-subtle) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
  border-radius: 0.5rem;
}
.skeleton-header { height: 5rem; border-radius: 0.75rem; margin-bottom: 1rem; }
.skeleton-avatar { width: 5rem; height: 5rem; border-radius: 1rem; margin: 0 auto 1rem; }
.skeleton-line { height: 0.875rem; margin: 0.5rem auto; }
.line-wide { width: 75%; }
.line-narrow { width: 50%; }
@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}
@keyframes pulseSoft {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.6; transform: scale(0.85); }
}
</style>
