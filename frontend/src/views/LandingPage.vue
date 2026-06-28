<template>
  <div class="landing min-h-screen overflow-x-hidden bg-page">
    <div v-if="loading" class="landing-loading">
      <div class="loading-spinner"></div>
    </div>

    <div v-else-if="error" class="landing-error">
      <p>{{ error }}</p>
      <button class="btn-primary" @click="loadContent">{{ t('common.tryAgain') }}</button>
    </div>

    <template v-else>
    <!-- Ambient background -->
    <div class="landing-bg" aria-hidden="true"></div>

    <!-- Nav -->
    <nav class="nav-bar" :class="{ scrolled }">
      <div class="nav-inner">
        <router-link to="/" class="logo-link">
          <div class="logo-icon">
            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z"/></svg>
          </div>
          <span class="logo-text">{{ content.site?.logo_text }}</span>
        </router-link>
        <div class="nav-links hidden md:flex">
          <a href="#features">{{ t('nav.features') }}</a>
          <a href="#pricing">{{ t('nav.pricing') }}</a>
          <a href="#testimonials">{{ t('nav.reviews') }}</a>
          <router-link to="/support">{{ t('nav.support') }}</router-link>
        </div>
        <div class="nav-actions">
          <LanguageSwitcher />
          <ThemeToggle />
          <router-link to="/login" class="btn-ghost text-sm hidden sm:inline-flex">{{ t('nav.logIn') }}</router-link>
          <router-link to="/register" class="btn-primary text-sm !py-2.5 !px-5">
            {{ content.hero?.cta_primary }}
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="hero-section">
      <div class="hero-inner">
        <div v-if="content.hero?.badge" class="hero-badge animate-fade-up">
          <span class="badge-dot"></span>
          {{ content.hero.badge }}
        </div>
        <h1 class="hero-title animate-fade-up" style="animation-delay:0.1s">
          <span class="text-gradient">{{ heroTitle }}</span>
          <span v-if="heroSubtitle" class="hero-title-sub">{{ heroSubtitle }}</span>
        </h1>
        <p class="hero-desc animate-fade-up" style="animation-delay:0.2s">
          {{ content.hero?.subtitle }}
        </p>
        <div class="hero-ctas animate-fade-up" style="animation-delay:0.3s">
          <router-link to="/register" class="btn-primary text-base !px-8 !py-4">
            {{ content.hero?.cta_primary }}
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </router-link>
          <a href="#pricing" class="btn-secondary text-base !px-8 !py-4">{{ content.hero?.cta_secondary }}</a>
        </div>

        <div v-if="heroPills.length" class="hero-visual animate-fade-up" style="animation-delay:0.5s">
          <div class="hero-card glass">
            <div class="hero-pills">
              <div v-for="(pill, i) in heroPills" :key="i" class="hero-pill" :style="{ '--pill-color': pill.color }">
                <span class="pill-icon">{{ pill.icon }}</span>
                <span class="pill-label">{{ pill.label }}</span>
                <span class="pill-stat">{{ pill.stat }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats -->
    <section v-if="content.stats?.length" class="stats-section">
      <div class="stats-grid">
        <div v-for="(stat, i) in content.stats" :key="i" class="stat-item">
          <div class="stat-counter">{{ stat.value }}</div>
          <div class="text-secondary text-sm">{{ stat.label }}</div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section v-if="content.features?.length" id="features" class="section-pad">
      <div class="section-inner">
        <div class="section-header">
          <p v-if="content.sections?.features_eyebrow" class="section-eyebrow">{{ content.sections.features_eyebrow }}</p>
          <h2 v-if="featuresTitleLines.length" class="section-title">
            <template v-for="(line, i) in featuresTitleLines" :key="i">
              {{ line }}<br v-if="i < featuresTitleLines.length - 1" />
            </template>
          </h2>
        </div>
        <div class="features-grid">
          <div v-for="feature in content.features" :key="feature.id || feature.title"
            class="feature-card" :style="{ '--card-accent': feature.color }">
            <div class="feature-icon" :style="{ background: feature.color + '22', color: feature.color }">
              {{ iconMap[feature.icon] || '✦' }}
            </div>
            <p class="feature-eyebrow" :style="{ color: feature.color }">{{ feature.subtitle }}</p>
            <h3 class="feature-title">{{ feature.title }}</h3>
            <p class="feature-desc">{{ feature.description }}</p>
            <ul class="feature-list">
              <li v-for="(item, idx) in parseItems(feature.items)" :key="idx">
                <svg :style="{ color: feature.color }" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ item }}
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section v-if="content.pricing?.length" id="pricing" class="pricing-section">
      <div class="section-inner">
        <div class="section-header text-center">
          <p v-if="content.sections?.pricing_eyebrow" class="section-eyebrow accent">{{ content.sections.pricing_eyebrow }}</p>
          <h2 v-if="content.sections?.pricing_title" class="section-title light">{{ content.sections.pricing_title }}</h2>
          <p v-if="content.sections?.pricing_subtitle" class="section-desc light">{{ content.sections.pricing_subtitle }}</p>
        </div>
        <div class="pricing-grid">
          <div v-for="plan in content.pricing" :key="plan.id || plan.slug"
            class="pricing-card" :class="{ popular: plan.is_popular }">
            <div v-if="plan.is_popular" class="popular-tag">{{ t('landing.mostPopular') }}</div>
            <h3 class="plan-name">{{ plan.name }}</h3>
            <div class="plan-price">
              <span class="price-num">${{ formatPrice(plan.price) }}</span>
              <span class="price-period">/{{ plan.billing_period || 'mo' }}</span>
            </div>
            <ul class="plan-features">
              <li v-for="(feat, i) in parseItems(plan.features)" :key="i">
                <svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                {{ feat }}
              </li>
            </ul>
            <router-link to="/register" class="plan-cta" :class="{ primary: plan.is_popular }">
              {{ plan.price == 0 ? t('landing.getStarted') : t('landing.startTrial') }}
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section v-if="content.testimonials?.length" id="testimonials" class="section-pad">
      <div class="section-inner">
        <h2 v-if="content.sections?.testimonials_title" class="section-title text-center mb-12">{{ content.sections.testimonials_title }}</h2>
        <div class="testimonials-grid">
          <div v-for="testimonial in content.testimonials" :key="testimonial.id || testimonial.name" class="testimonial-card">
            <div class="stars">★★★★★</div>
            <p class="quote">"{{ testimonial.content }}"</p>
            <div>
              <div class="author">{{ testimonial.name }}</div>
              <div class="role">{{ testimonial.role }}<span v-if="testimonial.company"> · {{ testimonial.company }}</span></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section v-if="content.cta?.title" class="section-pad">
      <div class="section-inner">
        <div class="cta-banner">
          <h2>{{ content.cta.title }}</h2>
          <p>{{ content.cta.subtitle }}</p>
          <router-link to="/register" class="cta-btn">
            {{ content.cta.button_text }}
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="site-footer">
      <div class="footer-inner">
        <div class="footer-brand">
          <div class="logo-icon sm"><svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8z"/></svg></div>
          <span>{{ content.site?.name }}</span>
          <span v-if="footerTagline" class="text-muted text-sm">· {{ footerTagline }}</span>
        </div>
        <nav v-if="content.site_pages?.length" class="footer-links">
          <router-link v-for="p in content.site_pages" :key="p.slug" :to="`/${p.slug}`">{{ p.title }}</router-link>
        </nav>
        <p class="text-muted text-sm">
          © {{ new Date().getFullYear() }} {{ content.site?.name }}
          <span v-if="supportEmail"> · <a :href="`mailto:${supportEmail}`" class="footer-mail">{{ supportEmail }}</a></span>
        </p>
      </div>
    </footer>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import publicApi from '../services/publicApi'
import { useLocaleWatch } from '../composables/useLocaleWatch'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import LanguageSwitcher from '../components/ui/LanguageSwitcher.vue'

const { t } = useI18n()

const content = ref(null)
const loading = ref(true)
const error = ref(null)
const scrolled = ref(false)
const iconMap = { qr: '▦', link: '🔗', card: '👤', page: '📄', menu: '🍽', badge: '🏅', ticket: '🎫', win: '🎰' }

const heroTitle = computed(() => {
  const title = content.value?.hero?.title || ''
  const parts = title.split('—')
  return parts[0]?.trim() || title
})
const heroSubtitle = computed(() => {
  const title = content.value?.hero?.title || ''
  const parts = title.split('—')
  return parts[1]?.trim() || ''
})

const heroPills = computed(() =>
  (content.value?.features || []).slice(0, 8).map(f => ({
    icon: iconMap[f.icon] || '✦',
    label: f.title,
    stat: f.subtitle,
    color: f.color,
  }))
)

const featuresTitleLines = computed(() => {
  const title = content.value?.sections?.features_title || ''
  return title.split('\n').filter(Boolean)
})

const footerTagline = computed(() => content.value?.footer?.tagline || content.value?.site?.tagline || '')
const supportEmail = computed(() => content.value?.footer?.support_email || '')

function parseItems(items) {
  if (Array.isArray(items)) return items
  if (typeof items === 'string') {
    try { return JSON.parse(items) } catch { return [] }
  }
  return []
}
function formatPrice(price) { return Number(price) === 0 ? '0' : Number(price).toFixed(0) }
function onScroll() { scrolled.value = window.scrollY > 20 }

async function loadContent() {
  loading.value = true
  error.value = null
  try {
    const { data } = await publicApi.get('/landing')
    content.value = data
  } catch {
    error.value = t('landing.loadError')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  window.addEventListener('scroll', onScroll)
  loadContent()
})
useLocaleWatch(loadContent)
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<style scoped>
.landing-loading, .landing-error {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-secondary);
}
.loading-spinner {
  width: 2.5rem; height: 2.5rem;
  border: 3px solid var(--border);
  border-top-color: var(--brand);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.landing { position: relative; color: var(--text-primary); }
.landing-bg {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background: var(--gradient-hero);
}
.landing > *:not(.landing-bg) { position: relative; z-index: 1; }

/* Nav */
.nav-bar {
  position: fixed; top: 0; inset-inline: 0; z-index: 50;
  padding: 1.25rem 0; transition: all 0.3s;
}
.nav-bar.scrolled {
  padding: 0.75rem 0;
  background: color-mix(in srgb, var(--surface) 85%, transparent);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}
.nav-inner {
  max-width: 80rem; margin: 0 auto; padding: 0 1.5rem;
  display: flex; align-items: center; justify-content: space-between;
}
.logo-link { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
.logo-icon {
  width: 2.25rem; height: 2.25rem; border-radius: 0.75rem;
  background: var(--brand);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 16px var(--brand-glow);
}
.logo-icon.sm { width: 2rem; height: 2rem; }
.logo-text { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); }
.nav-links { gap: 2rem; }
.nav-links a { color: var(--text-secondary); font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: color 0.2s; }
.nav-links a:hover { color: var(--brand); }
.nav-actions { display: flex; align-items: center; gap: 0.75rem; }

/* Hero */
.hero-section { padding: 8rem 1.5rem 5rem; text-align: center; }
.hero-inner { max-width: 56rem; margin: 0 auto; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 1rem; border-radius: 9999px;
  background: var(--brand-muted); border: 1px solid var(--border);
  color: var(--brand); font-size: 0.875rem; font-weight: 500; margin-bottom: 2rem;
}
.badge-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--brand); animation: pulseSoft 2s infinite; }
.hero-title { font-family: var(--font-display); line-height: 1.05; margin-bottom: 1.5rem; }
.hero-title .text-gradient { font-size: clamp(2.5rem, 7vw, 5rem); display: block; }
.hero-title-sub { display: block; font-size: clamp(1.5rem, 4vw, 2.5rem); color: var(--text-secondary); margin-top: 0.25rem; }
.hero-desc { font-size: 1.125rem; color: var(--text-secondary); max-width: 36rem; margin: 0 auto 2.5rem; line-height: 1.7; }
.hero-ctas { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; margin-bottom: 3rem; }
.hero-visual { max-width: 48rem; margin: 0 auto; }
.hero-card { border-radius: 1.5rem; padding: 1.5rem; }
.hero-pills { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; }
@media (min-width: 640px) { .hero-pills { grid-template-columns: repeat(4, 1fr); } }
.hero-pill {
  border-radius: 1rem; padding: 1rem; text-align: left;
  background: color-mix(in srgb, var(--pill-color) 12%, var(--surface));
  border: 1px solid color-mix(in srgb, var(--pill-color) 25%, var(--border));
  transition: transform 0.3s;
}
.hero-pill:hover { transform: translateY(-4px); }
.pill-icon { font-size: 1.5rem; display: block; margin-bottom: 0.5rem; }
.pill-label { font-weight: 600; font-size: 0.875rem; color: var(--text-primary); display: block; }
.pill-stat { font-size: 0.75rem; color: var(--text-muted); }

/* Stats */
.stats-section { border-block: 1px solid var(--border); background: var(--bg-subtle); padding: 3rem 1.5rem; }
.stats-grid { max-width: 80rem; margin: 0 auto; display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; }
@media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(4, 1fr); } }
.stat-item { text-align: center; }

/* Sections */
.section-pad { padding: 5rem 1.5rem; }
.section-inner { max-width: 80rem; margin: 0 auto; }
.section-header { margin-bottom: 3rem; }
.section-eyebrow { font-size: 0.8125rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--gold); margin-bottom: 0.75rem; }
.section-eyebrow.accent { color: var(--brand); }
.section-title { font-family: var(--font-display); font-size: clamp(2rem, 5vw, 3.5rem); color: var(--text-primary); line-height: 1.1; }
.section-title.light { color: #f8f9ff; }
.section-desc.light { color: #a8b0c8; margin-top: 0.75rem; }
.features-grid { display: grid; gap: 1.5rem; grid-template-columns: 1fr; }
@media (min-width: 640px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .features-grid { grid-template-columns: repeat(4, 1fr); } }
.feature-icon { width: 3.5rem; height: 3.5rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 1.25rem; }
.feature-eyebrow { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.25rem; }
.feature-title { font-size: 1.375rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; }
.feature-desc { color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.25rem; }
.feature-list { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.625rem; }
.feature-list li { display: flex; align-items: flex-start; gap: 0.625rem; font-size: 0.875rem; color: var(--text-secondary); }
.feature-list svg { width: 1.25rem; height: 1.25rem; flex-shrink: 0; margin-top: 0.1rem; }

/* Pricing */
.pricing-section {
  background: var(--section-dark-bg);
  padding: 5rem 1.5rem;
  color: #f8f9ff;
}
.pricing-grid { display: grid; gap: 1.25rem; }
@media (min-width: 768px) { .pricing-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .pricing-grid { grid-template-columns: repeat(4, 1fr); } }
.pricing-card { background: rgba(255,255,255,0.04); border-color: rgba(255,255,255,0.1); color: #f8f9ff; }
.pricing-card.popular { background: rgba(232, 101, 90, 0.12); border-color: var(--brand); transform: scale(1.03); }
.popular-tag {
  position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%);
  background: var(--brand); color: white; font-size: 0.6875rem;
  font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 9999px; text-transform: uppercase;
}
.plan-name { font-weight: 600; font-size: 1.125rem; }
.plan-price { margin: 1rem 0 1.5rem; }
.price-num { font-family: var(--font-display); font-size: 3rem; }
.price-period { color: #a8b0c8; font-size: 0.875rem; }
.plan-features { list-style: none; padding: 0; margin-bottom: 1.5rem; display: flex; flex-direction: column; gap: 0.625rem; }
.plan-features li { display: flex; gap: 0.5rem; font-size: 0.875rem; color: #c8cfe0; align-items: flex-start; }
.plan-features svg { width: 1rem; height: 1rem; color: var(--gold); flex-shrink: 0; margin-top: 0.15rem; }
.plan-cta {
  display: block; text-align: center; padding: 0.75rem; border-radius: 0.75rem;
  font-weight: 600; text-decoration: none; background: rgba(255,255,255,0.08); color: white; transition: all 0.2s;
}
.plan-cta:hover { background: rgba(255,255,255,0.15); }
.plan-cta.primary { background: var(--brand); }

/* Testimonials */
.testimonials-grid { display: grid; gap: 1.5rem; }
@media (min-width: 768px) { .testimonials-grid { grid-template-columns: repeat(3, 1fr); } }
.testimonial-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: 1.25rem; padding: 1.75rem; transition: all 0.3s;
}
.testimonial-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
.stars { color: var(--gold); margin-bottom: 1rem; letter-spacing: 2px; }
.quote { color: var(--text-secondary); line-height: 1.6; margin-bottom: 1.25rem; font-style: italic; }
.author { font-weight: 600; color: var(--text-primary); }
.role { font-size: 0.875rem; color: var(--text-muted); }

/* CTA */
.cta-banner {
  background: var(--gradient-cta); border-radius: 1.5rem;
  padding: 3rem 2rem; text-align: center; color: white;
  position: relative; overflow: hidden;
}
.cta-banner h2 { font-family: var(--font-display); font-size: clamp(1.75rem, 4vw, 2.75rem); margin-bottom: 0.75rem; }
.cta-banner p { color: rgba(255,255,255,0.85); margin-bottom: 1.5rem; max-width: 28rem; margin-inline: auto; }
.cta-btn {
  display: inline-flex; padding: 0.875rem 2rem; background: white; color: var(--brand);
  font-weight: 700; border-radius: 9999px; text-decoration: none; transition: transform 0.2s;
}
.cta-btn:hover { transform: scale(1.05); }

/* Footer */
.site-footer { border-top: 1px solid var(--border); padding: 3rem 1.5rem; background: var(--bg-subtle); }
.footer-inner { max-width: 80rem; margin: 0 auto; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
.footer-brand { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: var(--text-primary); }
.footer-links { display: flex; flex-wrap: wrap; gap: 1rem 1.5rem; }
.footer-links a { font-size: 0.875rem; color: var(--text-secondary); text-decoration: none; font-weight: 500; transition: color 0.2s; }
.footer-links a:hover { color: var(--brand); }
.footer-mail { color: var(--text-secondary); text-decoration: none; }
.footer-mail:hover { color: var(--brand); }
.nav-links :deep(a) { color: var(--text-secondary); font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: color 0.2s; }
.nav-links :deep(a:hover), .nav-links :deep(a.router-link-active) { color: var(--brand); }
</style>
