<template>
  <div>
    <div v-if="loading" class="text-slate-400">{{ t('common.loading') }}</div>
    <div v-else class="space-y-6">
      <div>
        <h2 class="text-lg font-bold text-slate-900">{{ t('admin.sitePagesTitle') }}</h2>
        <p class="text-sm text-slate-500">{{ t('admin.sitePagesSub') }}</p>
      </div>

      <p v-if="message" class="text-brand-600 font-medium text-sm">{{ message }}</p>

      <div class="flex flex-wrap gap-2">
        <button
          v-for="p in pages"
          :key="p.slug"
          type="button"
          class="tab-btn"
          :class="{ active: activeSlug === p.slug }"
          @click="selectPage(p)"
        >{{ p.title }}</button>
      </div>

      <section v-if="form.id" class="dashboard-card">
        <form @submit.prevent="savePage" class="grid gap-4">
          <div class="form-group">
            <label>{{ t('common.title') }}</label>
            <input v-model="form.title" class="input-field" required />
          </div>
          <div class="form-group">
            <label>{{ t('admin.introLine') }}</label>
            <input v-model="form.intro" class="input-field" placeholder="Short subtitle under the title" />
          </div>
          <div class="form-group">
            <label>{{ t('digitalPages.body') }}</label>
            <textarea v-model="form.content" class="input-field" rows="14" placeholder="Use **bold** and blank lines between paragraphs"></textarea>
          </div>

          <div v-if="showContactFields" class="contact-fields">
            <h3 class="font-semibold text-sm text-slate-700 mb-2">{{ t('admin.contactDetails') }}</h3>
            <input v-model="form.contact_info.email" class="input-field mb-2" :placeholder="t('common.email')" />
            <input v-model="form.contact_info.phone" class="input-field mb-2" :placeholder="t('common.phone')" />
            <input v-model="form.contact_info.address" class="input-field mb-2" :placeholder="t('common.address')" />
            <input v-model="form.contact_info.hours" class="input-field" :placeholder="t('digitalMenus.hours')" />
          </div>

          <label class="flex items-center gap-2 text-sm">
            <input v-model="form.is_active" type="checkbox" /> {{ t('admin.publishedOnSite') }}
          </label>

          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ saving ? t('common.saving') : t('admin.savePage') }}</button>
        </form>
      </section>

      <section class="dashboard-card">
        <h2 class="font-semibold text-lg mb-4">{{ t('admin.footerSettings') }}</h2>
        <form @submit.prevent="saveFooter" class="grid gap-4 max-w-md">
          <input v-model="footer.tagline" class="input-field" :placeholder="t('admin.footerTagline')" />
          <input v-model="footer.support_email" type="email" class="input-field" :placeholder="t('admin.supportEmail')" />
          <button type="submit" class="btn-primary w-fit" :disabled="saving">{{ t('admin.saveFooter') }}</button>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'

const { t } = useI18n()

const loading = ref(true)
const saving = ref(false)
const message = ref('')
const pages = ref([])
const footer = ref({ tagline: '', support_email: '' })
const activeSlug = ref('support')
const form = ref({ contact_info: {} })

const showContactFields = computed(() => ['support', 'contact'].includes(activeSlug.value))

function selectPage(p) {
  activeSlug.value = p.slug
  form.value = {
    ...p,
    contact_info: { ...(p.contact_info || {}) },
  }
}

async function load() {
  const { data } = await adminApi.get('/site-pages')
  pages.value = data.pages || []
  footer.value = data.footer || { tagline: '', support_email: '' }
  if (pages.value.length) selectPage(pages.value.find((p) => p.slug === activeSlug.value) || pages.value[0])
}

async function savePage() {
  saving.value = true
  message.value = ''
  try {
    const payload = {
      title: form.value.title,
      intro: form.value.intro,
      content: form.value.content,
      is_active: form.value.is_active,
    }
    if (showContactFields.value) {
      payload.contact_info = form.value.contact_info
    }
    await adminApi.put(`/site-pages/${form.value.id}`, payload)
    message.value = t('admin.pageSaved', { title: form.value.title })
    await load()
  } finally {
    saving.value = false
  }
}

async function saveFooter() {
  saving.value = true
  await adminApi.put('/site-pages-footer', footer.value)
  message.value = t('admin.footerSaved')
  saving.value = false
}

onMounted(async () => {
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.tab-btn {
  padding: 0.5rem 1rem; border-radius: 9999px; font-size: 0.8125rem; font-weight: 600;
  border: 1px solid #e2e8f0; background: white; color: #64748b; cursor: pointer;
}
.tab-btn.active { background: #e8655a; color: white; border-color: #e8655a; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: #64748b; margin-bottom: 0.375rem; }
.contact-fields { background: #f8fafc; border-radius: 0.75rem; padding: 1rem; }
</style>
