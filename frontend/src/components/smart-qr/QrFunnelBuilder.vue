<template>
  <div>
    <div class="funnel-toolbar">
      <button type="button" class="btn-primary text-sm" @click="openCreate">{{ t('smartQr.funnels.new') }}</button>
      <button type="button" class="btn-secondary text-sm" @click="showTemplates = !showTemplates">
        {{ t('smartQr.funnels.templates') }}
      </button>
    </div>

    <div v-if="showTemplates" class="templates-grid">
      <button
        v-for="tpl in templates"
        :key="tpl.id"
        type="button"
        class="template-btn"
        @click="applyTemplate(tpl)"
      >
        <strong>{{ tpl.name }}</strong>
        <span>{{ tpl.description }}</span>
      </button>
    </div>

    <div v-if="editing" class="editor-card">
      <h4>{{ editId ? t('smartQr.funnels.edit') : t('smartQr.funnels.create') }}</h4>
      <form @submit.prevent="save" class="form-stack">
        <div class="form-row">
          <div class="form-group">
            <label>{{ t('common.name') }}</label>
            <input v-model="form.name" required class="input-field" />
          </div>
          <div class="form-group">
            <label>{{ t('common.slug') }}</label>
            <input v-model="form.slug" required class="input-field" pattern="[A-Za-z0-9_-]+" />
          </div>
        </div>
        <div class="form-group">
          <label>{{ t('smartQr.funnels.goal') }}</label>
          <select v-model="form.goal" class="input-field">
            <option value="lead">{{ t('smartQr.funnels.goals.lead') }}</option>
            <option value="booking">{{ t('smartQr.funnels.goals.booking') }}</option>
            <option value="purchase">{{ t('smartQr.funnels.goals.purchase') }}</option>
            <option value="download">{{ t('smartQr.funnels.goals.download') }}</option>
            <option value="engagement">{{ t('smartQr.funnels.goals.engagement') }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>{{ t('common.description') }}</label>
          <textarea v-model="form.description" class="input-field" rows="2" />
        </div>

        <div class="steps-section">
          <div class="steps-header">
            <span>{{ t('smartQr.funnels.steps') }}</span>
            <button type="button" class="btn-ghost text-xs" @click="addStep">+ {{ t('smartQr.funnels.addStep') }}</button>
          </div>
          <div v-for="(step, i) in form.steps" :key="i" class="step-row">
            <input v-model="step.title" class="input-field" :placeholder="t('smartQr.funnels.stepTitle')" />
            <select v-model="step.target_type" class="input-field">
              <option value="url">URL</option>
              <option value="form">Form</option>
              <option value="page">Page</option>
              <option value="menu">Menu</option>
              <option value="card">Card</option>
              <option value="ticket">Ticket</option>
              <option value="win">Scan to Win</option>
            </select>
            <input v-model="step.target_slug" class="input-field" :placeholder="t('smartQr.funnels.slugOrUrl')" />
            <input v-model="step.target_url" class="input-field" placeholder="https://..." />
            <button type="button" class="btn-ghost text-xs danger" @click="form.steps.splice(i, 1)">✕</button>
          </div>
        </div>

        <p v-if="error" class="error-text">{{ error }}</p>
        <div class="form-actions">
          <button type="button" class="btn-secondary" @click="closeEditor">{{ t('common.cancel') }}</button>
          <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? t('common.saving') : t('common.save') }}</button>
        </div>
      </form>
    </div>

    <div v-if="loading" class="text-muted py-4">{{ t('common.loading') }}</div>
    <div v-else-if="!funnels.length && !editing" class="empty-hint py-4">{{ t('smartQr.funnels.empty') }}</div>
    <div v-else class="funnel-list">
      <div v-for="f in funnels" :key="f.id" class="funnel-card">
        <div>
          <h5>{{ f.name }}</h5>
          <p class="slug">/{{ f.slug }} · {{ f.goal }}</p>
          <p class="steps-count">{{ f.steps?.length || 0 }} {{ t('smartQr.funnels.steps') }}</p>
        </div>
        <div class="funnel-actions">
          <PublishToggle
            :model-value="!!f.is_active"
            :loading="togglingId === f.id"
            :active-label="t('common.active')"
            :inactive-label="t('common.paused')"
            @update:model-value="togglePublish(f)"
          />
          <button type="button" class="action-btn" @click="openEdit(f)">{{ t('common.edit') }}</button>
          <button type="button" class="action-btn danger" @click="remove(f)">{{ t('common.delete') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import PublishToggle from '../ui/PublishToggle.vue'
import { useDialog } from '../../composables/useDialog'

const emit = defineEmits(['created', 'updated'])
const { t } = useI18n()
const dialog = useDialog()

const funnels = ref([])
const templates = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const showTemplates = ref(false)
const togglingId = ref(null)

const defaultForm = () => ({
  name: '', slug: '', goal: 'lead', description: '', theme_color: '#1a1333', is_active: true,
  steps: [{ step_type: 'landing', title: 'Step 1', target_type: 'url', target_slug: '', target_url: '', cta_text: 'Continue', sort_order: 0 }],
})
const form = ref(defaultForm())

function slugify(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '').slice(0, 50)
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  editing.value = true
  error.value = ''
}

function openEdit(f) {
  editId.value = f.id
  form.value = { ...f, steps: (f.steps || []).map((s, i) => ({ ...s, sort_order: s.sort_order ?? i })) }
  editing.value = true
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

function addStep() {
  form.value.steps.push({
    step_type: 'custom', title: `Step ${form.value.steps.length + 1}`,
    target_type: 'url', target_slug: '', target_url: '', cta_text: 'Continue', sort_order: form.value.steps.length,
  })
}

function applyTemplate(tpl) {
  form.value = {
    ...defaultForm(),
    name: tpl.name,
    slug: slugify(tpl.name),
    goal: tpl.goal,
    description: tpl.description,
    steps: tpl.steps.map((s, i) => ({ ...s, sort_order: i })),
  }
  editing.value = true
  showTemplates.value = false
}

async function load() {
  const [listRes, tplRes] = await Promise.all([
    api.get('/qr-funnels'),
    api.get('/qr-funnels/templates'),
  ])
  funnels.value = listRes.data
  templates.value = tplRes.data.templates || []
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    if (editId.value) {
      const { data } = await api.put(`/qr-funnels/${editId.value}`, form.value)
      emit('updated', data)
    } else {
      const { data } = await api.post('/qr-funnels', form.value)
      emit('created', data)
    }
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(f) {
  togglingId.value = f.id
  try {
    const { data } = await api.patch(`/qr-funnels/${f.id}/publish`)
    const idx = funnels.value.findIndex(x => x.id === f.id)
    if (idx !== -1) funnels.value[idx] = data
  } finally {
    togglingId.value = null
  }
}

async function remove(f) {
  const ok = await dialog.confirm({
    title: t('smartQr.funnels.deleteTitle'),
    message: t('smartQr.funnels.deleteMessage', { name: f.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/qr-funnels/${f.id}`)
  await load()
}

onMounted(async () => {
  try { await load() } finally { loading.value = false }
})

defineExpose({ funnels, load })
</script>

<style scoped>
.funnel-toolbar { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.5rem; margin-bottom: 1rem; }
.template-btn { text-align: left; padding: 0.75rem; border: 1px solid var(--border); border-radius: 0.625rem; background: var(--bg-subtle); cursor: pointer; }
.template-btn strong { display: block; font-size: 0.8125rem; }
.template-btn span { font-size: 0.75rem; color: var(--text-muted); }
.editor-card { background: var(--surface); border: 1px solid var(--border); border-radius: 0.875rem; padding: 1rem; margin-bottom: 1rem; }
.form-stack { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.75rem; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.steps-section { border: 1px dashed var(--border); border-radius: 0.5rem; padding: 0.75rem; }
.steps-header { display: flex; justify-content: space-between; font-size: 0.8125rem; font-weight: 700; margin-bottom: 0.5rem; }
.step-row { display: grid; grid-template-columns: 1fr 0.8fr 0.8fr 1fr auto; gap: 0.375rem; margin-bottom: 0.375rem; }
.funnel-list { display: flex; flex-direction: column; gap: 0.5rem; }
.funnel-card { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 0.875rem 1rem; border: 1px solid var(--border); border-radius: 0.75rem; background: var(--surface); }
.funnel-card h5 { font-weight: 700; font-size: 0.9375rem; }
.slug, .steps-count { font-size: 0.75rem; color: var(--text-muted); }
.funnel-actions { display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; }
.action-btn { font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle); cursor: pointer; }
.action-btn.danger { color: #ef4444; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.form-actions { display: flex; gap: 0.5rem; }
.empty-hint { color: var(--text-muted); font-size: 0.875rem; }
@media (max-width: 768px) {
  .step-row { grid-template-columns: 1fr; }
  .funnel-card { flex-direction: column; align-items: flex-start; }
}
</style>
