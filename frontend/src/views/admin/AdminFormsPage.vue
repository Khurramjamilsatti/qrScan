<template>
  <div class="admin-page">
    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <template v-else>
      <div class="page-head">
        <div>
          <h2 class="page-title">{{ t('admin.formsTitle') }}</h2>
          <p class="page-sub">{{ t('admin.formsSub') }}</p>
        </div>
        <div class="summary-chip">{{ t('admin.formsTotal', { count: forms.length }) }}</div>
      </div>

      <div class="forms-layout">
        <section class="panel-card forms-list">
          <div v-if="!forms.length" class="empty-note">{{ t('admin.noFormsYet') }}</div>
          <button
            v-for="item in forms"
            :key="item.id"
            type="button"
            class="form-row"
            :class="{ active: selectedId === item.id }"
            @click="selectForm(item.id)"
          >
            <div class="form-row__main">
              <div class="form-row__title">{{ item.title }}</div>
              <div class="form-row__meta">
                <span>{{ item.user?.name || t('admin.userFallback') }}</span>
                <span>·</span>
                <span>{{ item.submission_count }} {{ t('forms.responses') }}</span>
              </div>
            </div>
            <span class="status-pill" :class="item.is_active ? 'live' : 'draft'">
              {{ item.is_active ? t('common.published') : t('common.draft') }}
            </span>
          </button>
        </section>

        <section class="panel-card responses-panel-wrap">
          <div v-if="!selectedId" class="empty-note">{{ t('admin.selectFormToView') }}</div>
          <div v-else-if="detailLoading" class="text-muted">{{ t('common.loading') }}</div>
          <template v-else-if="selectedForm">
            <div class="detail-head">
              <div>
                <h3>{{ selectedForm.title }}</h3>
                <p class="detail-meta">
                  {{ t('forms.slug') }}: {{ selectedForm.slug }}
                  · {{ selectedForm.user?.email }}
                  · <router-link :to="`/admin/users/${selectedForm.user?.id}`" class="panel-link">{{ t('admin.viewUser') }}</router-link>
                </p>
              </div>
              <a :href="`/form/${selectedForm.slug}`" target="_blank" rel="noopener" class="btn-secondary text-sm">
                {{ t('forms.viewPublic') }}
              </a>
            </div>
            <FormResponses
              :submissions="submissions"
              :summary="summary"
              :fields="selectedForm.fields || []"
              :total="submissionTotal"
              :loading="false"
              @delete="deleteSubmission"
            />
          </template>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import adminApi from '../../services/adminApi'
import FormResponses from '../../components/forms/FormResponses.vue'
import { useDialog } from '../../composables/useDialog'

const { t } = useI18n()
const dialog = useDialog()

const loading = ref(true)
const detailLoading = ref(false)
const forms = ref([])
const selectedId = ref(null)
const selectedForm = ref(null)
const submissions = ref([])
const summary = ref([])
const submissionTotal = ref(0)

async function loadForms() {
  const { data } = await adminApi.get('/forms')
  forms.value = data.data || data.forms || data || []
}

async function selectForm(id) {
  selectedId.value = id
  detailLoading.value = true
  try {
    const { data } = await adminApi.get(`/forms/${id}`)
    selectedForm.value = data.form
    submissions.value = data.submissions?.data || data.submissions || []
    summary.value = data.summary || []
    submissionTotal.value = data.submissions?.total ?? submissions.value.length
  } finally {
    detailLoading.value = false
  }
}

async function deleteSubmission(sub) {
  const ok = await dialog.confirm({
    title: t('forms.deleteResponseTitle'),
    message: t('forms.deleteResponseMessage'),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok || !selectedId.value) return
  await adminApi.delete(`/forms/${selectedId.value}/submissions/${sub.id}`)
  await selectForm(selectedId.value)
  await loadForms()
}

onMounted(async () => {
  try {
    await loadForms()
    if (forms.value.length) await selectForm(forms.value[0].id)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem; }
.page-title { font-size: 1.25rem; font-weight: 700; }
.page-sub { font-size: 0.875rem; color: #64748b; margin-top: 0.25rem; }
.summary-chip { background: #f1f5f9; color: #475569; padding: 0.5rem 0.875rem; border-radius: 999px; font-size: 0.8125rem; font-weight: 600; }
.forms-layout { display: grid; grid-template-columns: 320px 1fr; gap: 1.25rem; align-items: start; }
@media (max-width: 1024px) { .forms-layout { grid-template-columns: 1fr; } }
.panel-card { background: white; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1rem; }
.forms-list { display: flex; flex-direction: column; gap: 0.5rem; max-height: 70vh; overflow-y: auto; }
.form-row {
  display: flex; align-items: center; justify-content: space-between; gap: 0.75rem;
  width: 100%; text-align: start; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.75rem;
  background: #f8fafc; cursor: pointer; transition: border-color 0.15s, background 0.15s;
}
.form-row:hover, .form-row.active { border-color: #6b4fa0; background: #faf5ff; }
.form-row__title { font-weight: 600; font-size: 0.9375rem; color: #0f172a; }
.form-row__meta { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: flex; gap: 0.375rem; flex-wrap: wrap; }
.status-pill { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; padding: 0.25rem 0.5rem; border-radius: 999px; }
.status-pill.live { background: #dcfce7; color: #166534; }
.status-pill.draft { background: #f1f5f9; color: #64748b; }
.responses-panel-wrap { min-height: 400px; }
.detail-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
.detail-head h3 { font-size: 1.125rem; font-weight: 700; }
.detail-meta { font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; }
.panel-link { color: #6b4fa0; font-weight: 600; text-decoration: none; }
.panel-link:hover { text-decoration: underline; }
.empty-note { color: #94a3b8; font-size: 0.875rem; padding: 1.5rem; text-align: center; }
</style>
