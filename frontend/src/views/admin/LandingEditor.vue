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
          <p v-if="message" class="text-brand-600 font-medium text-sm">{{ message }}</p>
        </div>

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
        <div v-for="t in testimonials" :key="t.id" class="border border-slate-100 rounded-xl p-4 mb-3 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ t.name }}</div>
            <div class="text-sm text-slate-500">{{ t.content?.slice(0, 80) }}...</div>
          </div>
          <div class="flex gap-2">
            <button @click="editTestimonial(t)" class="btn-ghost text-sm">{{ t('common.edit') }}</button>
            <button @click="deleteTestimonial(t)" class="btn-ghost text-sm text-red-500">{{ t('common.delete') }}</button>
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
            :hero="hero"
            :stats="stats"
            :features="features"
            :pricing="pricing"
            :cta="cta"
            :site="site"
            :sections="sections"
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
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'
import LandingPreview from '../../components/previews/LandingPreview.vue'
import { useDialog } from '../../composables/useDialog'

const { t } = useI18n()
const dialog = useDialog()

const loading = ref(true)
const saving = ref(false)
const message = ref('')
const hero = ref({})
const stats = ref([])
const features = ref([])
const pricing = ref([])
const cta = ref({})
const site = ref({})
const sections = ref({})
const testimonials = ref([])
const featureModal = ref(false)
const planModal = ref(false)
const testimonialModal = ref(false)
const featureForm = ref({})
const planForm = ref({})
const testimonialForm = ref({})

async function load() {
  const { data } = await adminApi.get('/landing')
  hero.value = data.hero || {}
  stats.value = data.stats || []
  features.value = data.features || []
  pricing.value = data.pricing || []
  cta.value = data.cta || {}
  site.value = data.site || {}
  sections.value = data.sections || {}
  testimonials.value = data.testimonials || []
}

async function saveHero() {
  saving.value = true
  await adminApi.put('/landing/hero', hero.value)
  message.value = t('admin.heroSaved')
  saving.value = false
}

async function saveStats() {
  saving.value = true
  await adminApi.put('/landing/stats', { stats: stats.value })
  message.value = t('admin.statsSaved')
  saving.value = false
}

async function saveCta() {
  saving.value = true
  await adminApi.put('/landing/cta', cta.value)
  message.value = t('admin.ctaSaved')
  saving.value = false
}

async function saveSite() {
  saving.value = true
  await adminApi.put('/landing/site', site.value)
  message.value = t('admin.siteSaved')
  saving.value = false
}

async function saveSections() {
  saving.value = true
  await adminApi.put('/landing/sections', sections.value)
  message.value = t('admin.headingsSaved')
  saving.value = false
}

function editTestimonial(t) {
  if (t) {
    testimonialForm.value = { ...t }
  } else {
    testimonialForm.value = { name: '', role: '', company: '', content: '' }
  }
  testimonialModal.value = true
}

async function saveTestimonial() {
  if (testimonialForm.value.id) {
    await adminApi.put(`/testimonials/${testimonialForm.value.id}`, testimonialForm.value)
  } else {
    await adminApi.post('/testimonials', testimonialForm.value)
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
    featureForm.value = { title: '', subtitle: '', description: '', itemsText: '', color: '#10b981', icon: 'qr' }
  }
  featureModal.value = true
}

async function saveFeature() {
  const payload = {
    ...featureForm.value,
    items: featureForm.value.itemsText.split('\n').filter(Boolean),
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
    message: t('admin.deleteFeatureMessage', { title: f.title }),
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
    planForm.value = { name: '', slug: '', price: 0, featuresText: '', is_popular: false, limits: {}, billing_period: 'month' }
  }
  planModal.value = true
}

async function savePlan() {
  const payload = {
    ...planForm.value,
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
  try { await load() } finally { loading.value = false }
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
</style>
