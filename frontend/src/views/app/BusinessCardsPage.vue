<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('businessCards.title') }}</h2>
        <p class="page-sub">{{ t('businessCards.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('businessCards.newCard') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('businessCards.editBusinessCard') : t('businessCards.createBusinessCard') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor>
        <template #form>
          <form @submit.prevent="save" class="form-stack">
            <DomainSelect v-model="form.custom_domain_id" />
            <div class="form-group">
              <label>{{ t('businessCards.profileUrlSlug') }}</label>
              <div class="slug-input">
                <span>{{ cardHost }}/card/</span>
                <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" :placeholder="t('businessCards.slugPlaceholder')" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('auth.fullName') }}</label>
              <input v-model="form.full_name" required class="input-field" />
            </div>
            <div class="form-group">
              <label>{{ t('businessCards.tagline') }}</label>
              <input v-model="form.tagline" class="input-field" :placeholder="t('businessCards.taglinePlaceholder')" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('businessCards.jobTitle') }}</label>
                <input v-model="form.job_title" class="input-field" />
              </div>
              <div class="form-group">
                <label>{{ t('businessCards.company') }}</label>
                <input v-model="form.company" class="input-field" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('businessCards.bio') }}</label>
              <textarea v-model="form.bio" class="input-field" rows="3" :placeholder="t('businessCards.bioPlaceholder')"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('common.email') }}</label>
                <input v-model="form.email" type="email" class="input-field" />
              </div>
              <div class="form-group">
                <label>{{ t('common.phone') }}</label>
                <input v-model="form.phone" class="input-field" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('common.website') }}</label>
              <input v-model="form.website" type="url" class="input-field" placeholder="https://..." />
            </div>
            <div class="form-group">
              <label>{{ t('common.address') }}</label>
              <input v-model="form.address" class="input-field" placeholder="City, Country" />
            </div>
            <ImageAssetField v-model="form.photo_path" :label="t('businessCards.profilePhoto')" folder="photos" ai-context="card-photo" ai-placeholder="professional business headshot" />
            <ImageAssetField v-model="form.background_image_path" :label="t('businessCards.headerBackground')" folder="backgrounds" ai-context="card-background" ai-placeholder="soft gradient abstract" />
            <ImageAssetField v-model="form.logo_path" :label="t('businessCards.companyLogo')" folder="logos" ai-context="qr-logo" ai-placeholder="company logo icon" />
            <div class="form-group">
              <label>{{ t('common.themeColor') }}</label>
              <input v-model="form.theme_color" type="color" class="color-input" />
            </div>
            <div class="qr-section">
              <div class="section-title">{{ t('qrCodes.qrCodeStyle') }}</div>
              <QrStyleFields
                v-model:qr-shape="form.qr_shape"
                v-model:dot-style="form.dot_style"
                v-model:corner-style="form.corner_style"
                v-model:frame-style="form.frame_style"
              />
            </div>
            <div class="social-section">
              <div class="flex justify-between items-center mb-2">
                <label class="!mb-0">{{ t('businessCards.socialLinks') }}</label>
                <button type="button" @click="addSocial" class="text-sm text-brand-500 font-medium">+ {{ t('common.add') }}</button>
              </div>
              <div v-for="(s, i) in form.social_links" :key="i" class="social-row-input">
                <input v-model="s.platform" class="input-field" :placeholder="t('social.linkedin')" />
                <input v-model="s.url" class="input-field" placeholder="https://..." />
                <button type="button" @click="form.social_links.splice(i, 1)" class="text-red-400 text-sm">✕</button>
              </div>
            </div>
            <p v-if="error" class="error-text">{{ error }}</p>
            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <CardPreview
            :full-name="form.full_name"
            :job-title="form.job_title"
            :company="form.company"
            :tagline="form.tagline"
            :bio="form.bio"
            :email="form.email"
            :phone="form.phone"
            :website="form.website"
            :address="form.address"
            :photo="form.photo_path"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :slug="form.slug"
            :card-url="previewCardUrl"
            :domain-label="domains.labelFor(form.custom_domain_id)"
            :theme-color="form.theme_color"
            :social-links="form.social_links"
          />
          <div class="mt-4 text-center">
            <QrPreview
              v-if="form.slug"
              :content="previewCardUrl"
              :name="form.full_name"
              :foreground="form.theme_color"
              :logo-url="form.logo_path"
              :background-image="form.background_image_path"
              :background="'#ffffff'"
              :size="140"
              :qr-shape="form.qr_shape"
              :dot-style="form.dot_style"
              :corner-style="form.corner_style"
              :frame-style="form.frame_style"
            />
          </div>
        </template>
      </SplitEditor>
    </div>

    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <div v-else-if="!cards.length && !editing" class="empty-state">
      <div class="empty-icon">👤</div>
      <h3>{{ t('businessCards.emptyTitle') }}</h3>
      <p>{{ t('businessCards.emptyDesc') }}</p>
      <button @click="openCreate" class="btn-primary">{{ t('businessCards.emptyCta') }}</button>
    </div>
    <div v-else class="cards-grid">
      <div v-for="card in cards" :key="card.id" class="card-item" :class="{ draft: !card.is_active }">
        <div v-if="!card.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
        <CardPreview
          :full-name="card.full_name"
          :job-title="card.job_title"
          :company="card.company"
          :tagline="card.tagline"
          :bio="card.bio"
          :email="card.email"
          :phone="card.phone"
          :website="card.website"
          :address="card.address"
          :photo="card.photo_path"
          :logo="card.logo_path"
          :background-image="card.background_image_path"
          :slug="card.slug"
          :card-url="card.card_url"
          :domain-label="card.domain_label"
          :theme-color="card.theme_color"
          :social-links="card.social_links || []"
        />
        <div class="card-item__footer">
          <div class="footer-top">
            <PublishToggle
              :model-value="!!card.is_active"
              :loading="togglingId === card.id"
              @update:model-value="togglePublish(card)"
            />
            <span class="view-stat">
              <span class="view-stat__num">{{ card.view_count }}</span>
              <span class="view-stat__label">{{ t('common.views') }}</span>
            </span>
          </div>
          <div class="card-item__actions">
            <router-link
              :to="`/card/${card.slug}`"
              target="_blank"
              class="action-btn"
              :class="{ muted: !card.is_active }"
            >
              {{ card.is_active ? t('common.view') : t('common.preview') }}
            </router-link>
            <CopyButton :text="card.card_url || `${domains.baseUrlFor(card.custom_domain_id)}/card/${card.slug}`" :label="t('common.copy')" />
            <button type="button" class="action-btn" @click="saveVcard(card)">{{ t('common.vcard') }}</button>
            <button @click="openEdit(card)" class="action-btn">{{ t('common.edit') }}</button>
            <button @click="showAnalytics(card)" class="action-btn">{{ t('common.stats') }}</button>
            <button @click="deleteCard(card)" class="action-btn danger">{{ t('common.delete') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="analyticsCard" class="drawer-overlay" @click.self="analyticsCard = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsCard.full_name }) }}</h3>
          <button @click="analyticsCard = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="business-cards" :id="analyticsCard.id" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useDomainsStore } from '../../stores/domains'
import SplitEditor from '../../components/ui/SplitEditor.vue'
import CardPreview from '../../components/previews/CardPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import { useDialog } from '../../composables/useDialog'
import { downloadVcard } from '../../utils/vcard'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()
const cardHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})
const previewCardUrl = computed(() => form.value.slug
  ? `${domains.baseUrlFor(form.value.custom_domain_id)}/card/${form.value.slug}`
  : '')

const cards = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsCard = ref(null)
const togglingId = ref(null)

const defaultForm = () => ({
  slug: '', full_name: '', job_title: '', company: '', tagline: '', bio: '',
  email: '', phone: '', website: '', address: '',
  photo_path: '', background_image_path: '', logo_path: '', custom_domain_id: null,
  theme_color: '#e8655a', social_links: [], is_active: true,
  qr_shape: 'square', dot_style: 'square', corner_style: 'sharp', frame_style: 'none',
})
const form = ref(defaultForm())

function addSocial() {
  if (!form.value.social_links) form.value.social_links = []
  form.value.social_links.push({ platform: '', url: '' })
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  editing.value = true
}

function openEdit(card) {
  window.scrollTo ({
    top:10,
    behavior: 'smooth'
  });
  editId.value = card.id
  form.value = { ...card, social_links: card.social_links ? [...card.social_links] : [] }
  editing.value = true
}

function closeEditor() { editing.value = false; editId.value = null }

async function load() {
  const { data } = await api.get('/business-cards')
  cards.value = data
}

async function save() {
  saving.value = true; error.value = ''
  try {
    const payload = { ...form.value, social_links: form.value.social_links.filter(s => s.url) }
    delete payload.is_active
    if (editId.value) await api.put(`/business-cards/${editId.value}`, payload)
    else await api.post('/business-cards', { ...payload, is_active: true })
    closeEditor(); await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally { saving.value = false }
}

async function togglePublish(card) {
  togglingId.value = card.id
  try {
    const { data } = await api.patch(`/business-cards/${card.id}/publish`)
    const idx = cards.value.findIndex(c => c.id === card.id)
    if (idx !== -1) cards.value[idx] = data
  } catch {
    dialog.alert({
      title: t('common.notice'),
      message: t('businessCards.updateFailedMessage'),
      variant: 'danger',
    })
  } finally {
    togglingId.value = null
  }
}

async function deleteCard(card) {
  const ok = await dialog.confirm({
    title: t('businessCards.deleteTitle'),
    message: t('businessCards.deleteMessage', { name: card.full_name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/business-cards/${card.id}`)
  await load()
}

function showAnalytics(card) { analyticsCard.value = card }

async function saveVcard(card) {
  try {
    await downloadVcard(card.slug, `${card.slug}.vcf`)
  } catch {
    dialog.alert({
      title: t('businessCards.vcardDownloadFailedTitle'),
      message: t('businessCards.vcardDownloadFailedMessage'),
      variant: 'danger',
    })
  }
}

onMounted(async () => {
  domains.fetch()
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; box-shadow: var(--shadow-sm); }
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.editor-panel__header h3 { font-weight: 700; color: var(--text-primary); }
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); }
.social-section { background: var(--bg-subtle); border-radius: 0.75rem; padding: 1rem; border: 1px solid var(--border); }
.qr-section {
  background: var(--bg-subtle);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  padding: 1rem;
}
.section-title {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.75rem;
}
.social-row-input { display: grid; grid-template-columns: 1fr 2fr auto; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center; }
.form-actions { display: flex; gap: 0.75rem; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
.card-item {
  position: relative;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  padding: 1.25rem;
  box-shadow: var(--shadow-sm);
  transition: box-shadow 0.2s, transform 0.2s;
}
.card-item.draft { opacity: 0.88; }
.card-item.draft::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 1.25rem;
  pointer-events: none;
  border: 2px dashed color-mix(in srgb, var(--text-muted) 35%, transparent);
}
.draft-ribbon {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  z-index: 20;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.2rem 0.5rem;
  border-radius: 0.375rem;
  background: var(--gold-muted);
  color: #92680a;
  border: 1px solid color-mix(in srgb, var(--gold) 40%, transparent);
}
.card-item:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.card-item__footer { margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
.footer-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}
.view-stat {
  display: inline-flex;
  align-items: baseline;
  gap: 0.25rem;
  padding: 0.2rem 0.625rem;
  border-radius: 9999px;
  background: var(--purple-muted);
  border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num {
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--purple);
}
.view-stat__label {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
}
.card-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.action-btn.muted { opacity: 0.75; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.3rem 0.625rem; border-radius: 0.5rem;
  border: 1px solid var(--border); background: var(--bg-subtle); color: var(--text-secondary);
  cursor: pointer; text-decoration: none; transition: all 0.15s;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-muted); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.drawer-overlay { position: fixed; inset: 0; background: rgba(26, 19, 51, 0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
.error-text { color: #ef4444; font-size: 0.875rem; }
</style>
