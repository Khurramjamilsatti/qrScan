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
        <h2>{{ t('forms.notPublishedTitle') }}</h2>
        <p>{{ t('forms.notPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('forms.notFoundTitle') }}</h2>
        <p>{{ t('forms.notFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="submitted" class="page-center">
      <div class="state-card success-card">
        <div class="state-icon">✅</div>
        <h2>{{ alreadySubmitted ? t('forms.alreadySubmittedTitle') : t('forms.thankYou') }}</h2>
        <p>{{ confirmationMessage }}</p>
        <button v-if="showSubmitAnother" type="button" class="btn-secondary" @click="resetForm">
          {{ t('forms.submitAnother') }}
        </button>
      </div>
    </div>

    <main v-else-if="form" class="page-center wide">
      <div class="published-badge"><span class="badge-dot"></span>{{ t('nav.forms') }}</div>

      <article class="public-form">
        <div v-if="headerImageUrl" class="form-header-img">
          <img :src="headerImageUrl" alt="" />
        </div>

        <div class="form-card form-surface" :style="{ borderTopColor: themeColor }">
          <img v-if="logoUrl" :src="logoUrl" alt="" class="form-logo" />
          <h1 class="form-title">{{ form.title }}</h1>
          <p v-if="form.description" class="form-desc">{{ form.description }}</p>

          <div v-if="!form.is_accepting" class="closed-notice">
            {{ t('forms.closedNotice') }}
          </div>

          <form v-else novalidate @submit.prevent="handleSubmit" class="form-body">
            <div v-if="form.settings?.collect_email" class="email-field">
              <label class="field-label">
                {{ t('forms.respondentEmail') }} <span class="required">*</span>
              </label>
              <input
                v-model="respondentEmail"
                type="email"
                class="input-field"
                :class="{ 'input-error': fieldErrors.respondent_email }"
              />
              <p v-if="fieldErrors.respondent_email" class="field-error">{{ fieldErrors.respondent_email }}</p>
            </div>

            <div v-if="form.settings?.show_progress_bar && inputFields.length > 1" class="progress-bar">
              <div class="progress-bar__fill" :style="{ width: progressPct + '%' }"></div>
            </div>

            <FormFieldRenderer
              v-for="field in displayFields"
              :key="field.id"
              :field="field"
              v-model="responses[field.id]"
              variant="light"
              :error="fieldErrors[`responses.${field.id}`]"
            />

            <p v-if="submitError" class="submit-error">{{ submitError }}</p>

            <button type="submit" class="submit-btn" :style="{ background: themeColor }" :disabled="submitting">
              {{ submitting ? t('common.saving') : t('forms.submit') }}
            </button>
          </form>
        </div>
      </article>
    </main>

    <div v-else class="page-center">
      <div class="state-card">
        <div class="state-icon">⚠️</div>
        <h2>{{ t('forms.loadFailed') }}</h2>
        <p>{{ t('forms.notFoundDesc') }}</p>
        <button type="button" class="btn-primary" @click="loadForm">{{ t('forms.tryAgain') }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import publicApi from '../services/publicApi'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import PublicBrandHeader from '../components/ui/PublicBrandHeader.vue'
import FormFieldRenderer from '../components/forms/FormFieldRenderer.vue'
import { resolveStorageUrl } from '../utils/storageUrl'
import { emptyResponses, isInputField } from '../utils/formFieldTypes'
import {
  hasSubmitted,
  getSubmittedMessage,
  markSubmitted,
  clearSubmitted,
} from '../utils/formSubmissionStorage'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const form = ref(null)
const displayFields = ref([])
const loading = ref(true)
const errorType = ref(null)
const responses = ref({})
const respondentEmail = ref('')
const fieldErrors = ref({})
const submitError = ref('')
const submitting = ref(false)
const submitted = ref(false)
const alreadySubmitted = ref(false)
const confirmationMessage = ref('')
const showSubmitAnother = ref(false)

const themeColor = computed(() => form.value?.theme_color || '#673ab7')
const headerImageUrl = computed(() => resolveStorageUrl(form.value?.header_image_path))
const logoUrl = computed(() => resolveStorageUrl(form.value?.logo_path))
const bgUrl = computed(() => resolveStorageUrl(form.value?.background_image_path))

const pageStyle = computed(() => ({
  '--page-theme': themeColor.value,
  '--page-bg': form.value?.background_color || '#f3f0ff',
  backgroundColor: form.value?.background_color || '#f3f0ff',
  backgroundImage: bgUrl.value ? `url(${bgUrl.value})` : undefined,
  backgroundSize: 'cover',
  backgroundPosition: 'center',
}))

const inputFields = computed(() =>
  (form.value?.fields || []).filter(f => isInputField(f.type))
)

const progressPct = computed(() => {
  const total = inputFields.value.length
  if (!total) return 0
  const filled = inputFields.value.filter(f => {
    const v = responses.value[f.id]
    if (Array.isArray(v)) return v.length > 0
    if (typeof v === 'object' && v !== null) return Object.keys(v).length > 0
    return v !== '' && v !== null && v !== undefined
  }).length
  return Math.round((filled / total) * 100)
})

function buildDisplayFields(data) {
  const fields = [...(data?.fields || [])]
  if (data?.settings?.shuffle_questions) {
    for (let i = fields.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [fields[i], fields[j]] = [fields[j], fields[i]]
    }
  }
  return fields
}

function applySubmittedState(data, fromStorage = false) {
  const settings = data?.settings || form.value?.settings || {}
  const message = data?.confirmation_message
    || settings.confirmation_message
    || getSubmittedMessage(route.params.slug)
    || t('forms.defaultConfirmation')

  confirmationMessage.value = message
  alreadySubmitted.value = !!data?.already_submitted || fromStorage
  showSubmitAnother.value = !alreadySubmitted.value
    && !!settings.show_submit_another
    && (data?.max_submissions_per_respondent === 0
      || form.value?.max_submissions_per_respondent === 0)
  submitted.value = true
}

async function loadForm() {
  loading.value = true
  errorType.value = null
  submitted.value = false
  alreadySubmitted.value = false
  submitError.value = ''
  fieldErrors.value = {}
  const slug = route.params.slug
  try {
    const { data } = await publicApi.get(`/form/${slug}`)
    form.value = data
    displayFields.value = buildDisplayFields(data)
    responses.value = emptyResponses(data.fields)
    document.title = `${data.title} — QRScan`

    if (data.already_submitted || hasSubmitted(slug)) {
      applySubmittedState(data, hasSubmitted(slug) && !data.already_submitted)
      return
    }
  } catch (e) {
    form.value = null
    displayFields.value = []
    errorType.value = e.response?.status === 403 ? 'unpublished' : 'notfound'
  } finally {
    loading.value = false
  }
}

function redirectAfterSubmit(url) {
  const target = String(url || '').trim()
  if (!target) return

  try {
    const parsed = new URL(target, window.location.origin)
    if (parsed.origin === window.location.origin) {
      router.push(`${parsed.pathname}${parsed.search}${parsed.hash}`)
      return
    }
  } catch {
    // fall through
  }

  window.location.href = target.startsWith('/') ? target : url
}

async function handleSubmit() {
  if (submitting.value || !form.value) return
  submitting.value = true
  submitError.value = ''
  fieldErrors.value = {}

  try {
    const payload = { responses: { ...responses.value } }
    if (form.value.settings?.collect_email) {
      payload.respondent_email = respondentEmail.value
    }
    const { data } = await publicApi.post(`/form/${route.params.slug}/submit`, payload)
    const message = data.message || t('forms.defaultConfirmation')
    confirmationMessage.value = message
    markSubmitted(route.params.slug, message)
    alreadySubmitted.value = false
    showSubmitAnother.value = !!data.show_submit_another
      && (form.value.max_submissions_per_respondent === 0
        || form.value.max_submissions_per_respondent > 1)
    submitted.value = true
    const redirectUrl = data.redirect_url || form.value?.settings?.redirect_url || '/app/forms'
    setTimeout(() => redirectAfterSubmit(redirectUrl), 1200)
  } catch (e) {
    const status = e.response?.status
    submitError.value = e.response?.data?.message || t('forms.submitFailed')
    if (status === 403) {
      errorType.value = 'unpublished'
      form.value = null
      return
    }
    if (status === 422 || status === 429) {
      if (status === 429) {
        const message = e.response?.data?.message || t('forms.alreadySubmittedDesc')
        markSubmitted(route.params.slug, message)
        confirmationMessage.value = message
        alreadySubmitted.value = true
        showSubmitAnother.value = false
        submitted.value = true
        return
      }
      const errors = e.response?.data?.errors || {}
      const mapped = {}
      for (const [key, msgs] of Object.entries(errors)) {
        mapped[key] = Array.isArray(msgs) ? msgs[0] : msgs
      }
      fieldErrors.value = mapped
    }
  } finally {
    submitting.value = false
  }
}

function resetForm() {
  clearSubmitted(route.params.slug)
  submitted.value = false
  alreadySubmitted.value = false
  responses.value = emptyResponses(form.value?.fields)
  respondentEmail.value = ''
  fieldErrors.value = {}
  submitError.value = ''
}

watch(() => route.params.slug, loadForm)
onMounted(loadForm)
</script>

<style scoped>
.public-page { min-height: 100vh; position: relative; background-size: cover; background-position: center; }
.page-bg {
  position: fixed; inset: 0; z-index: 0;
  pointer-events: none;
}
.page-header {
  position: relative; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.5rem;
}
.page-center { position: relative; z-index: 5; display: flex; justify-content: center; padding: 2rem 1rem; }
.page-center.wide { max-width: 640px; margin: 0 auto; flex-direction: column; align-items: stretch; width: 100%; }
.published-badge {
  display: inline-flex; align-items: center; gap: 0.375rem;
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
  color: var(--page-theme); margin-bottom: 1rem; align-self: center;
}
.badge-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--page-theme); }
.public-form { width: 100%; }
.form-header-img img { width: 100%; height: 140px; object-fit: cover; border-radius: 0.75rem 0.75rem 0 0; display: block; }
.form-card {
  background: white; border-radius: 0.75rem; padding: 1.5rem;
  border-top: 6px solid var(--page-theme);
  box-shadow: 0 2px 12px rgba(0,0,0,0.08);
}
.form-surface {
  --text-primary: #1a1333;
  --text-secondary: #6b7280;
  --text-muted: #9ca3af;
  --border: #e5e7eb;
  --bg-subtle: #f9fafb;
  --surface: #ffffff;
  color: #1a1333;
}
.form-logo { max-height: 56px; margin-bottom: 1rem; display: block; }
.form-title { font-size: 1.5rem; font-weight: 700; color: #1a1333; margin-bottom: 0.5rem; }
.form-desc { font-size: 0.9375rem; color: #6b7280; margin-bottom: 1.25rem; white-space: pre-wrap; }
.closed-notice {
  padding: 1rem; background: #fef3c7; border-radius: 0.5rem;
  color: #92400e; font-size: 0.875rem; text-align: center;
}
.form-body { display: flex; flex-direction: column; gap: 0.25rem; }
.email-field { margin-bottom: 0.75rem; }
.field-label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.375rem; color: #1a1333; }
.required { color: #ef4444; }
.input-error { border-color: #ef4444 !important; }
.field-error { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }
.progress-bar { height: 4px; background: #e5e7eb; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; }
.progress-bar__fill { height: 100%; background: var(--page-theme); border-radius: 2px; transition: width 0.3s; }
.submit-btn {
  margin-top: 1.25rem; padding: 0.75rem 2rem; border: none; border-radius: 0.375rem;
  color: white; font-weight: 600; font-size: 0.9375rem; cursor: pointer; align-self: flex-start;
}
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.submit-error { color: #ef4444; font-size: 0.875rem; margin-top: 0.5rem; }
.state-card { background: white; border-radius: 1rem; padding: 2rem; text-align: center; max-width: 400px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); color: #1a1333; }
.state-icon { font-size: 2.5rem; margin-bottom: 1rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: #1a1333; }
.state-card p { color: #6b7280; margin-bottom: 1.25rem; }
.success-card .btn-secondary { margin-top: 0.5rem; }
.loader-card { background: white; border-radius: 1rem; padding: 2rem; width: 100%; max-width: 480px; }
.skeleton { background: #e5e7eb; border-radius: 0.375rem; animation: pulse 1.5s infinite; }
.skeleton-header { height: 2rem; width: 60%; margin-bottom: 1rem; }
.skeleton-line { height: 1rem; }
.line-wide { width: 80%; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
</style>
