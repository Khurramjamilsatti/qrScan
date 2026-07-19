<template>
  <div>
    <div v-if="loading" class="text-slate-400">{{ t('common.loading') }}</div>
    <div v-else class="admin-editor-layout">
      <div class="admin-editor-form space-y-6">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-900">{{ t('admin.landingEditorTitle') }}</h2>
            <p class="text-sm text-slate-500">{{ t('admin.landingEditorSub') }}</p>
          </div>
          <div class="flex items-center gap-3">
            <div class="locale-toggle">
              <button type="button" :class="{ active: editLocale === 'en' }" @click="switchLocale('en')">EN</button>
              <button type="button" :class="{ active: editLocale === 'ar' }" @click="switchLocale('ar')">AR</button>
            </div>
            <p v-if="message" class="text-brand-600 font-medium text-sm">{{ message }}</p>
          </div>
        </div>
        <p v-if="loadError" class="text-red-500 text-sm">{{ loadError }}</p>

      <!-- Section headings -->
      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.sectionHeadings') }}</h2>
        <form @submit.prevent="saveSections" class="grid gap-4">
          <input v-model="sections.features_eyebrow" class="input-field" placeholder="Features eyebrow" />
          <textarea v-model="sections.features_title" class="input-field" rows="2" placeholder="Features title (one line per row)"></textarea>
          <input v-model="sections.pricing_eyebrow" class="input-field" placeholder="Pricing eyebrow" />
          <input v-model="sections.pricing_title" class="input-field" placeholder="Pricing title" />
          <input v-model="sections.pricing_subtitle" class="input-field" placeholder="Pricing subtitle" />
          <input v-model="sections.testimonials_title" class="input-field" placeholder="Testimonials title" />
          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ t('admin.saveHeadings') }}</button>
        </form>
      </section>

      <!-- Site settings -->
      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.siteBranding') }}</h2>
        <form @submit.prevent="saveSite" class="grid gap-4">
          <input v-model="site.name" class="input-field" placeholder="Site name" required />
          <input v-model="site.logo_text" class="input-field" placeholder="Logo text" />
          <input v-model="site.tagline" class="input-field" placeholder="Tagline" />
          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ t('admin.saveSite') }}</button>
        </form>
      </section>

      <!-- Hero editor -->
      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.heroSection') }}</h2>
        <form @submit.prevent="saveHero" class="grid gap-4">
          <input v-model="hero.badge" class="input-field" placeholder="Badge text" />
          <input v-model="hero.title" class="input-field" placeholder="Title" required />
          <textarea v-model="hero.subtitle" class="input-field" rows="3" placeholder="Subtitle"></textarea>
          <div class="grid grid-cols-2 gap-4">
            <input v-model="hero.cta_primary" class="input-field" placeholder="Primary CTA" />
            <input v-model="hero.cta_secondary" class="input-field" placeholder="Secondary CTA" />
          </div>
          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ saving ? t('common.saving') : t('admin.saveHero') }}</button>
        </form>
      </section>

      <!-- Stats editor -->
      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.statsBar') }}</h2>
        <div v-for="(stat, i) in stats" :key="i" class="grid grid-cols-2 gap-4 mb-3">
          <input v-model="stat.label" class="input-field" placeholder="Label" />
          <input v-model="stat.value" class="input-field" placeholder="Value" />
        </div>
        <button @click="stats.push({ label: '', value: '' })" class="btn-ghost text-sm mb-3">{{ t('admin.addStat') }}</button>
        <button @click="saveStats" class="btn-primary" :disabled="saving">{{ t('admin.saveStats') }}</button>
      </section>

      <!-- Features -->
      <section class="dashboard-card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-lg">{{ t('admin.featuresSection') }}</h2>
          <button @click="editFeature(null)" class="btn-primary text-sm">{{ t('admin.addFeatureBtn') }}</button>
        </div>
        <div v-for="feature in features" :key="feature.id" class="border border-slate-100 rounded-xl p-4 mb-3 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ feature.title }}</div>
            <div class="text-sm text-slate-500">{{ feature.subtitle }}</div>
          </div>
          <div class="flex gap-2">
            <button @click="editFeature(feature)" class="btn-ghost text-sm">{{ t('common.edit') }}</button>
            <button @click="deleteFeature(feature)" class="btn-ghost text-sm text-red-500">{{ t('common.delete') }}</button>
          </div>
        </div>
      </section>

      <!-- Pricing -->
      <section class="dashboard-card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-lg">{{ t('admin.pricingPlans') }}</h2>
          <button @click="editPlan(null)" class="btn-primary text-sm">{{ t('admin.addPlanBtn') }}</button>
        </div>
        <div v-for="plan in pricing" :key="plan.id" class="border border-slate-100 rounded-xl p-4 mb-3 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ plan.name }} — ${{ plan.price }}/mo</div>
            <div class="text-sm text-slate-500">{{ plan.is_popular ? t('landing.mostPopular') : '' }}</div>
          </div>
          <div class="flex gap-2">
            <button @click="editPlan(plan)" class="btn-ghost text-sm">{{ t('common.edit') }}</button>
            <button @click="deletePlan(plan)" class="btn-ghost text-sm text-red-500">{{ t('common.delete') }}</button>
          </div>
        </div>
      </section>

      <!-- Testimonials -->
      <section class="dashboard-card">
        <div class="flex items-center justify-between mb-4">
          <h2 class="font-semibold text-lg">{{ t('admin.testimonials') }}</h2>
          <button @click="editTestimonial(null)" class="btn-primary text-sm">{{ t('admin.addTestimonialBtn') }}</button>
        </div>
        <div v-for="item in testimonials" :key="item.id" class="border border-slate-100 rounded-xl p-4 mb-3 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ item.name }}</div>
            <div class="text-sm text-slate-500">{{ item.content?.slice(0, 80) }}...</div>
          </div>
          <div class="flex gap-2">
            <button @click="editTestimonial(item)" class="btn-ghost text-sm">{{ t('common.edit') }}</button>
            <button @click="deleteTestimonial(item)" class="btn-ghost text-sm text-red-500">{{ t('common.delete') }}</button>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.bottomCta') }}</h2>
        <form @submit.prevent="saveCta" class="grid gap-4">
          <input v-model="cta.title" class="input-field" placeholder="Title" />
          <textarea v-model="cta.subtitle" class="input-field" rows="2" placeholder="Subtitle"></textarea>
          <input v-model="cta.button_text" class="input-field" placeholder="Button text" />
          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ t('admin.saveCta') }}</button>
        </form>
      </section>

      <p v-if="message && false" class="text-brand-600 font-medium">{{ message }}</p>
    </div>

      <!-- Live preview panel -->
      <div class="admin-preview-panel">
        <div class="preview-sticky-wrap">
          <div class="preview-label-row">
            <span class="preview-dot"></span>
            {{ t('landing.liveLandingPreview') }}
          </div>
          <LandingPreview
            :hero="previewHero"
            :stats="previewStats"
            :features="features"
            :pricing="pricing"
            :cta="previewCta"
            :site="previewSite"
            :sections="previewSections"
            :testimonials="testimonials"
          />
        </div>
      </div>
    </div>

    <!-- Feature modal -->
    <div v-if="featureModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="featureModal = false">
      <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h3 class="font-semibold text-lg mb-4">{{ featureForm.id ? t('admin.editFeatureTitle') : t('admin.addFeatureTitle') }}</h3>
        <form @submit.prevent="saveFeature" class="space-y-4">
          <input v-model="featureForm.title" class="input-field" placeholder="Title" required />
          <input v-model="featureForm.subtitle" class="input-field" placeholder="Subtitle" />
          <textarea v-model="featureForm.description" class="input-field" rows="2" placeholder="Description"></textarea>
          <textarea v-model="featureForm.itemsText" class="input-field" rows="4" placeholder="Items (one per line)"></textarea>
          <input v-model="featureForm.color" type="color" class="w-full h-10 rounded-lg" />
          <div class="flex gap-3">
            <button type="button" @click="featureModal = false" class="btn-secondary flex-1">{{ t('common.cancel') }}</button>
            <button type="submit" class="btn-primary flex-1">{{ t('common.save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Testimonial modal -->
    <div v-if="testimonialModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="testimonialModal = false">
      <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h3 class="font-semibold text-lg mb-4">{{ testimonialForm.id ? t('admin.editTestimonialTitle') : t('admin.addTestimonialTitle') }}</h3>
        <form @submit.prevent="saveTestimonial" class="space-y-4">
          <input v-model="testimonialForm.name" class="input-field" placeholder="Name" required />
          <input v-model="testimonialForm.role" class="input-field" placeholder="Role" />
          <input v-model="testimonialForm.company" class="input-field" placeholder="Company" />
          <textarea v-model="testimonialForm.content" class="input-field" rows="4" placeholder="Quote" required></textarea>
          <div class="flex gap-3">
            <button type="button" @click="testimonialModal = false" class="btn-secondary flex-1">{{ t('common.cancel') }}</button>
            <button type="submit" class="btn-primary flex-1">{{ t('common.save') }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Plan modal -->
    <div v-if="planModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="planModal = false">
      <div class="bg-white rounded-2xl p-8 w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <h3 class="font-semibold text-lg mb-4">{{ planForm.id ? t('admin.editPlanTitle') : t('admin.addPlanTitle') }}</h3>
        <form @submit.prevent="savePlan" class="space-y-4">
          <input v-model="planForm.name" class="input-field" placeholder="Name" required />
          <input v-model="planForm.slug" class="input-field" placeholder="Slug" required />
          <input v-model.number="planForm.price" type="number" step="0.01" class="input-field" placeholder="Price" />
          <textarea v-model="planForm.featuresText" class="input-field" rows="4" placeholder="Features (one per line)"></textarea>
          <label class="flex items-center gap-2 text-sm">
            <input v-model="planForm.is_popular" type="checkbox" /> {{ t('landing.mostPopular') }}
          </label>
          <div class="flex gap-3">
            <button type="button" @click="planModal = false" class="btn-secondary flex-1">{{ t('common.cancel') }}</button>
            <button type="submit" class="btn-primary flex-1">{{ t('common.save') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'
import LandingPreview from '../../components/previews/LandingPreview.vue'
import { useDialog } from '../../composables/useDialog'
import {
  extractObject,
  extractStats,
  mergeObject,
  mergeStats,
  resolveLocale,
} from '../../utils/localizedContent'

const { t } = useI18n()
const dialog = useDialog()

const loading = ref(true)
const saving = ref(false)
const message = ref('')
const loadError = ref('')
const editLocale = ref('en')

const rawHero = ref({})
const rawStats = ref([])
const rawCta = ref({})
const rawSite = ref({})
const rawSections = ref({})

const hero = ref({})
const stats = ref([])
const features = ref([])
const pricing = ref([])
const cta = ref({})
const site = ref({})
const sections = ref({})
const testimonials = ref([])

const allFeatures = ref([])
const allPricing = ref([])
const allTestimonials = ref([])

const featureModal = ref(false)
const planModal = ref(false)
const testimonialModal = ref(false)
const featureForm = ref({})
const planForm = ref({})
const testimonialForm = ref({})

const previewHero = computed(() => extractObject(rawHero.value, editLocale.value))
const previewStats = computed(() => extractStats(rawStats.value, editLocale.value))
const previewCta = computed(() => extractObject(rawCta.value, editLocale.value))
const previewSite = computed(() => extractObject(rawSite.value, editLocale.value))
const previewSections = computed(() => extractObject(rawSections.value, editLocale.value))

function applyLocaleForms(locale) {
  hero.value = extractObject(rawHero.value, locale)
  stats.value = extractStats(rawStats.value, locale)
  cta.value = extractObject(rawCta.value, locale)
  site.value = extractObject(rawSite.value, locale)
  sections.value = extractObject(rawSections.value, locale)
  features.value = allFeatures.value.filter((f) => (f.locale || 'en') === locale)
  pricing.value = allPricing.value.filter((p) => (p.locale || 'en') === locale)
  testimonials.value = allTestimonials.value.filter((item) => (item.locale || 'en') === locale)
}

function persistLocaleForms(locale) {
  rawHero.value = mergeObject(rawHero.value, hero.value, locale)
  rawStats.value = mergeStats(rawStats.value, stats.value, locale)
  rawCta.value = mergeObject(rawCta.value, cta.value, locale)
  rawSite.value = mergeObject(rawSite.value, site.value, locale)
  rawSections.value = mergeObject(rawSections.value, sections.value, locale)
}

function switchLocale(locale) {
  if (locale === editLocale.value) return
  persistLocaleForms(editLocale.value)
  editLocale.value = locale
  applyLocaleForms(locale)
}

async function load() {
  loadError.value = ''
  const { data } = await adminApi.get('/landing')
  rawHero.value = data.hero || {}
  rawStats.value = data.stats || []
  rawCta.value = data.cta || {}
  rawSite.value = data.site || {}
  rawSections.value = data.sections || {}
  allFeatures.value = data.features || []
  allPricing.value = data.pricing || []
  allTestimonials.value = data.testimonials || []
  applyLocaleForms(editLocale.value)
}

async function saveHero() {
  saving.value = true
  try {
    persistLocaleForms(editLocale.value)
    await adminApi.put('/landing/hero', rawHero.value)
    message.value = t('admin.heroSaved')
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.saveFailed')
  } finally {
    saving.value = false
  }
}

async function saveStats() {
  saving.value = true
  try {
    persistLocaleForms(editLocale.value)
    await adminApi.put('/landing/stats', { stats: rawStats.value })
    message.value = t('admin.statsSaved')
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.saveFailed')
  } finally {
    saving.value = false
  }
}

async function saveCta() {
  saving.value = true
  try {
    persistLocaleForms(editLocale.value)
    await adminApi.put('/landing/cta', rawCta.value)
    message.value = t('admin.ctaSaved')
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.saveFailed')
  } finally {
    saving.value = false
  }
}

async function saveSite() {
  saving.value = true
  try {
    persistLocaleForms(editLocale.value)
    await adminApi.put('/landing/site', rawSite.value)
    message.value = t('admin.siteSaved')
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.saveFailed')
  } finally {
    saving.value = false
  }
}

async function saveSections() {
  saving.value = true
  try {
    persistLocaleForms(editLocale.value)
    await adminApi.put('/landing/sections', rawSections.value)
    message.value = t('admin.headingsSaved')
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.saveFailed')
  } finally {
    saving.value = false
  }
}

function editTestimonial(item) {
  if (item) {
    testimonialForm.value = { ...item }
  } else {
    testimonialForm.value = { name: '', role: '', company: '', content: '', locale: editLocale.value, rating: 5 }
  }
  testimonialModal.value = true
}

async function saveTestimonial() {
  const payload = { ...testimonialForm.value, locale: testimonialForm.value.locale || editLocale.value }
  if (payload.id) {
    await adminApi.put(`/testimonials/${payload.id}`, payload)
  } else {
    await adminApi.post('/testimonials', payload)
  }
  testimonialModal.value = false
  message.value = t('admin.testimonialSaved')
  await load()
}

async function deleteTestimonial(item) {
  const ok = await dialog.confirm({
    title: t('admin.deleteTestimonialTitle'),
    message: t('admin.deleteTestimonialMessage', { name: item.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await adminApi.delete(`/testimonials/${item.id}`)
  await load()
}

function editFeature(f) {
  if (f) {
    const items = Array.isArray(f.items) ? f.items : JSON.parse(f.items || '[]')
    featureForm.value = { ...f, itemsText: items.join('\n') }
  } else {
    featureForm.value = { title: '', subtitle: '', description: '', itemsText: '', color: '#10b981', icon: 'qr', locale: editLocale.value }
  }
  featureModal.value = true
}

async function saveFeature() {
  const payload = {
    ...featureForm.value,
    locale: featureForm.value.locale || editLocale.value,
    items: featureForm.value.itemsText.split('\n').filter(Boolean),
    description: featureForm.value.description || featureForm.value.subtitle || featureForm.value.title || '—',
  }
  delete payload.itemsText
  if (featureForm.value.id) {
    await adminApi.put(`/features/${featureForm.value.id}`, payload)
  } else {
    await adminApi.post('/features', payload)
  }
  featureModal.value = false
  message.value = t('admin.featureSaved')
  await load()
}

async function deleteFeature(f) {
  const ok = await dialog.confirm({
    title: t('admin.deleteFeatureTitle'),
    message: t('admin.deleteFeatureMessage', { title: resolveLocale(f.title, editLocale.value) || f.title }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await adminApi.delete(`/features/${f.id}`)
  await load()
}

function editPlan(p) {
  if (p) {
    const feats = Array.isArray(p.features) ? p.features : JSON.parse(p.features || '[]')
    planForm.value = { ...p, featuresText: feats.join('\n') }
  } else {
    planForm.value = { name: '', slug: '', price: 0, featuresText: '', is_popular: false, limits: {}, billing_period: 'month', locale: editLocale.value }
  }
  planModal.value = true
}

async function savePlan() {
  const payload = {
    ...planForm.value,
    locale: planForm.value.locale || editLocale.value,
    features: planForm.value.featuresText.split('\n').filter(Boolean),
    limits: planForm.value.limits || {},
  }
  delete payload.featuresText
  if (planForm.value.id) {
    await adminApi.put(`/pricing-plans/${planForm.value.id}`, payload)
  } else {
    await adminApi.post('/pricing-plans', payload)
  }
  planModal.value = false
  message.value = t('admin.planSaved')
  await load()
}

async function deletePlan(p) {
  const ok = await dialog.confirm({
    title: t('admin.deletePlanTitle'),
    message: t('admin.deletePlanMessage', { name: p.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await adminApi.delete(`/pricing-plans/${p.id}`)
  await load()
}

onMounted(async () => {
  try {
    await load()
  } catch (e) {
    loadError.value = e.response?.data?.message || t('admin.landingLoadFailed')
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.admin-editor-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
}
@media (min-width: 1280px) {
  .admin-editor-layout {
    grid-template-columns: 1fr 400px;
    align-items: start;
  }
}
.preview-sticky-wrap {
  position: sticky;
  top: 5rem;
}
.preview-label-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 1rem;
}
.preview-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #10b981;
  animation: pulse 2s infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
.locale-toggle {
  display: inline-flex; border: 1px solid #e2e8f0; border-radius: 0.5rem; overflow: hidden;
}
.locale-toggle button {
  padding: 0.375rem 0.75rem; font-size: 0.75rem; font-weight: 700; background: white; color: #64748b; border: none; cursor: pointer;
}
.locale-toggle button.active { background: #6b4fa0; color: white; }
</style>
