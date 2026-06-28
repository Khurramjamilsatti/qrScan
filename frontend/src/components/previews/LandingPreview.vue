<template>
  <div class="landing-preview">
    <div class="preview-scale">
      <!-- Nav -->
      <div class="lp-nav">
        <span class="lp-logo">{{ site?.logo_text || t('common.brand') }}</span>
        <span class="lp-nav-links">{{ t('previews.featuresPricing') }}</span>
      </div>
      <!-- Hero -->
      <div class="lp-hero">
        <div v-if="hero?.badge" class="lp-badge">{{ hero.badge }}</div>
        <h1 class="lp-title">{{ hero?.title || t('previews.yourHeadline') }}</h1>
        <p class="lp-sub">{{ hero?.subtitle || t('previews.yourSubtitle') }}</p>
        <div class="lp-ctas">
          <span class="lp-btn-primary">{{ hero?.cta_primary || t('previews.startFree') }}</span>
          <span class="lp-btn-secondary">{{ hero?.cta_secondary || t('nav.pricing') }}</span>
        </div>
      </div>
      <!-- Stats -->
      <div class="lp-stats">
        <div v-for="(s, i) in stats" :key="i" class="lp-stat">
          <div class="lp-stat-val">{{ s.value || '0' }}</div>
          <div class="lp-stat-label">{{ s.label || t('previews.stat') }}</div>
        </div>
      </div>
      <!-- Features -->
      <div class="lp-features">
        <div v-for="f in features.slice(0, 8)" :key="f.id" class="lp-feature" :style="{ borderColor: f.color + '44' }">
          <div class="lp-feature-title" :style="{ color: f.color }">{{ f.title }}</div>
          <div class="lp-feature-desc">{{ f.description?.slice(0, 60) }}...</div>
        </div>
      </div>
      <!-- Pricing -->
      <div class="lp-pricing">
        <div v-for="p in pricing.slice(0, 4)" :key="p.id" class="lp-plan" :class="{ popular: p.is_popular }">
          <div class="lp-plan-name">{{ p.name }}</div>
          <div class="lp-plan-price">${{ p.price }}</div>
        </div>
      </div>
      <!-- CTA -->
      <div class="lp-cta">
        <div class="lp-cta-title">{{ cta?.title || t('previews.ctaTitle') }}</div>
        <span class="lp-btn-white">{{ cta?.button_text || t('landing.getStarted') }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

defineProps({
  hero: Object,
  stats: { type: Array, default: () => [] },
  features: { type: Array, default: () => [] },
  pricing: { type: Array, default: () => [] },
  cta: Object,
  site: Object,
  sections: Object,
  testimonials: { type: Array, default: () => [] },
})
</script>

<style scoped>
.landing-preview {
  background: #e2e8f0;
  border-radius: 1rem;
  padding: 1rem;
  overflow: hidden;
  border: 1px solid #cbd5e1;
}
.preview-scale {
  background: white;
  border-radius: 0.75rem;
  overflow: hidden;
  transform-origin: top center;
  font-size: 0.7rem;
}
.lp-nav {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9;
}
.lp-logo { font-weight: 700; color: #0f172a; }
.lp-nav-links { color: #94a3b8; font-size: 0.6rem; }
.lp-hero { padding: 1.5rem 1rem; text-align: center; }
.lp-badge {
  display: inline-block; font-size: 0.55rem; padding: 0.2rem 0.5rem;
  background: #ecfdf5; color: #059669; border-radius: 9999px; margin-bottom: 0.5rem;
}
.lp-title { font-family: 'Instrument Serif', serif; font-size: 1.1rem; color: #0f172a; line-height: 1.2; }
.lp-sub { color: #64748b; margin-top: 0.375rem; font-size: 0.6rem; line-height: 1.4; }
.lp-ctas { display: flex; gap: 0.375rem; justify-content: center; margin-top: 0.75rem; }
.lp-btn-primary {
  background: #10b981; color: white; padding: 0.3rem 0.75rem;
  border-radius: 9999px; font-size: 0.55rem; font-weight: 600;
}
.lp-btn-secondary {
  border: 1px solid #e2e8f0; padding: 0.3rem 0.75rem;
  border-radius: 9999px; font-size: 0.55rem; color: #475569;
}
.lp-stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
  padding: 0.75rem 0;
}
.lp-stat { text-align: center; }
.lp-stat-val { font-family: 'Instrument Serif', serif; font-size: 0.9rem; font-weight: 700; color: #0f172a; }
.lp-stat-label { font-size: 0.5rem; color: #94a3b8; }
.lp-features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; padding: 0.75rem; }
.lp-feature { border: 1px solid; border-radius: 0.5rem; padding: 0.5rem; }
.lp-feature-title { font-weight: 700; font-size: 0.6rem; }
.lp-feature-desc { font-size: 0.5rem; color: #64748b; margin-top: 0.25rem; }
.lp-pricing {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.375rem;
  padding: 0.75rem; background: #0f172a;
}
.lp-plan { background: rgba(255,255,255,0.05); border-radius: 0.375rem; padding: 0.5rem; text-align: center; color: white; }
.lp-plan.popular { border: 1px solid #10b981; }
.lp-plan-name { font-size: 0.55rem; font-weight: 600; }
.lp-plan-price { font-family: 'Instrument Serif', serif; font-size: 0.8rem; margin-top: 0.125rem; }
.lp-cta {
  background: linear-gradient(135deg, #10b981, #8b5cf6);
  padding: 1rem; text-align: center; color: white;
}
.lp-cta-title { font-family: 'Instrument Serif', serif; font-size: 0.8rem; margin-bottom: 0.5rem; }
.lp-btn-white {
  background: white; color: #10b981; padding: 0.3rem 0.75rem;
  border-radius: 9999px; font-size: 0.55rem; font-weight: 700;
}
</style>
