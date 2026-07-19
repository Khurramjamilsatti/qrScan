<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalMenus.title') }}</h2>
        <p class="page-sub">{{ t('digitalMenus.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('digitalMenus.newMenu') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalMenus.editMenu') : t('digitalMenus.createMenu') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="editorTab">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('digitalMenus.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('digitalMenus.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('digitalMenus.tabs.qrDesign') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="editorTab === 'content'" class="tab-panel">
                <div class="template-section">
                  <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
                  <TemplateGallery
                    v-model="form.template"
                    :templates="menuTemplates"
                    :categories="menuTemplateCategories"
                    :columns="3"
                  />
                </div>
                <DomainSelect v-model="form.custom_domain_id" />
                <div class="form-group">
                  <label>{{ t('digitalMenus.menuUrlSlug') }}</label>
                  <div class="slug-input">
                    <span>{{ menuHost }}/menu/</span>
                    <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="bistro-menu" />
                  </div>
                </div>

                <div class="content-section">
                  <div class="section-title">{{ t('digitalMenus.menuContent') }}</div>
                  <div class="form-group">
                    <label>{{ t('digitalMenus.restaurantName') }}</label>
                    <input v-model="form.name" required class="input-field" placeholder="The Golden Fork" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('common.description') }}</label>
                    <textarea v-model="form.description" class="input-field" rows="2" placeholder="Fresh seasonal cuisine"></textarea>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label>{{ t('digitalPages.location') }}</label>
                      <input v-model="form.location" class="input-field" placeholder="123 Main St" />
                    </div>
                    <div class="form-group">
                      <label>{{ t('common.phone') }}</label>
                      <input v-model="form.phone" class="input-field" />
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group">
                      <label>{{ t('digitalMenus.hours') }}</label>
                      <input v-model="form.hours" class="input-field" placeholder="Mon–Fri 11am–10pm" />
                    </div>
                    <div class="form-group">
                      <label>{{ t('digitalMenus.currency') }}</label>
                      <select v-model="form.currency" class="input-field">
                        <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                      </select>
                    </div>
                  </div>

                  <div class="menu-sections-editor">
                    <div class="repeater-head">
                      <span class="section-title">{{ t('digitalMenus.menuSections') }}</span>
                      <button type="button" @click="addSection" class="link-btn">+ {{ t('common.add') }}</button>
                    </div>
                    <div v-for="(section, si) in form.sections" :key="si" class="section-block">
                      <div class="section-block__head">
                        <input v-model="section.name" class="input-field" :placeholder="t('digitalMenus.newSectionDefault')" />
                        <button type="button" @click="form.sections.splice(si, 1)" class="remove-btn">✕</button>
                      </div>
                      <div v-for="(item, ii) in section.items" :key="ii" class="item-block">
                        <div class="form-row">
                          <div class="form-group"><label>{{ t('common.name') }}</label><input v-model="item.name" class="input-field" /></div>
                          <div class="form-group"><label>{{ t('common.price') }}</label><input v-model="item.price" class="input-field" placeholder="12.00" /></div>
                        </div>
                        <div class="form-group"><label>{{ t('common.description') }}</label><input v-model="item.description" class="input-field" /></div>
                        <ImageAssetField v-model="item.image_path" :label="t('common.gallery')" folder="photos" ai-context="food-photo" ai-placeholder="appetizing food photo" />
                        <div class="form-group">
                          <label>{{ t('common.tags') }}</label>
                          <div class="tag-picker">
                            <button
                              v-for="tag in DIETARY_TAGS"
                              :key="tag"
                              type="button"
                              class="tag-btn"
                              :class="{ active: item.tags?.includes(tag) }"
                              @click="toggleTag(item, tag)"
                            >{{ tag }}</button>
                          </div>
                        </div>
                        <button type="button" @click="section.items.splice(ii, 1)" class="remove-btn block">{{ t('common.remove') }}</button>
                      </div>
                      <button type="button" @click="addItem(section)" class="link-btn">+ {{ t('common.add') }}</button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-show="editorTab === 'appearance'" class="tab-panel">
                <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="restaurant-logo" ai-placeholder="restaurant logo emblem" />
                <ImageAssetField v-model="form.background_image_path" :label="t('businessCards.headerBackground')" folder="backgrounds" ai-context="restaurant-interior" ai-placeholder="warm restaurant interior" />
                <div class="form-group">
                  <label>{{ t('common.themeColor') }}</label>
                  <input v-model="form.theme_color" type="color" class="color-input" />
                </div>
              </div>

              <div v-show="editorTab === 'qr'" class="tab-panel">
                <div class="qr-section">
                  <div class="section-title">{{ t('digitalMenus.menuQrStyle') }}</div>
                  <QrStyleFields
                    v-model:qr-shape="form.qr_shape"
                    v-model:dot-style="form.dot_style"
                    v-model:corner-style="form.corner_style"
                    v-model:frame-style="form.frame_style"
                  />
                </div>
              </div>

              <p v-if="error" class="error-text">{{ error }}</p>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <QrPreview
            v-if="editorTab === 'qr' && form.slug"
            minimal
            :content="previewMenuUrl"
            :foreground="form.theme_color"
            :logo-url="form.logo_path"
            :background-image="form.background_image_path"
            :background="'#ffffff'"
            :size="220"
            :qr-shape="form.qr_shape"
            :dot-style="form.dot_style"
            :corner-style="form.corner_style"
            :frame-style="form.frame_style"
          />
          <MenuHtmlPreview
            v-else
            expandable
            live-preview
            :name="form.name"
            :description="form.description"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :theme-color="form.theme_color"
            :currency="form.currency"
            :location="form.location"
            :phone="form.phone"
            :hours="form.hours"
            :sections="form.sections"
            :slug="form.slug"
            :menu-url="previewMenuUrl"
            :template="form.template"
          />
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!menus.length" class="empty-state">
        <div class="empty-icon">🍽</div>
        <h3>{{ t('digitalMenus.emptyTitle') }}</h3>
        <p>{{ t('digitalMenus.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('digitalMenus.emptyCta') }}</button>
      </div>
      <div v-else class="menus-grid">
        <div v-for="menu in menus" :key="menu.id" class="menu-item" :class="{ draft: !menu.is_active }">
          <div class="menu-item__stack">
            <div v-if="!menu.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
            <MenuHtmlPreview
              compact
              embedded
              :name="menu.name"
              :description="menu.description"
              :logo="menu.logo_path"
              :background-image="menu.background_image_path"
              :theme-color="menu.theme_color"
              :currency="menu.currency"
              :location="menu.location"
              :phone="menu.phone"
              :hours="menu.hours"
              :sections="menu.sections || []"
              :slug="menu.slug"
              :menu-url="menu.menu_url"
              :domain-label="menu.domain_label"
              :template="menu.template || 'classic'"
            />
            <div class="menu-item__footer">
              <PublishToggle
                :model-value="!!menu.is_active"
                :loading="togglingId === menu.id"
                :active-label="t('publish.published')"
                :inactive-label="t('publish.draft')"
                @update:model-value="togglePublish(menu)"
              />
              <span class="view-stat">
                <span class="view-stat__num">{{ menu.view_count }}</span>
                <span class="view-stat__label">{{ t('common.views') }}</span>
              </span>
              <div class="menu-item__actions">
                <CopyButton :text="menu.menu_url" :label="t('common.copy')" />
                <button @click="openEdit(menu)" class="action-btn">{{ t('common.edit') }}</button>
                <button @click="showAnalytics(menu)" class="action-btn">{{ t('common.stats') }}</button>
                <button @click="deleteMenu(menu)" class="action-btn danger">{{ t('common.delete') }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <div v-if="analyticsMenu" class="drawer-overlay" @click.self="analyticsMenu = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsMenu.name }) }}</h3>
          <button @click="analyticsMenu = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="digital-menus" :id="analyticsMenu.id" />
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
import MenuHtmlPreview from '../../components/previews/MenuHtmlPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import TemplateGallery from '../../components/ui/TemplateGallery.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import { defaultMenuSections, DIETARY_TAGS } from '../../utils/pageTemplates'
import { MENU_TEMPLATES, MENU_TEMPLATE_CATEGORIES } from '../../utils/menuTemplates'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()
const menuTemplates = computed(() => translateList(MENU_TEMPLATES, t))
const menuTemplateCategories = computed(() => translateList(MENU_TEMPLATE_CATEGORIES, t))
const currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'INR']

const menus = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsMenu = ref(null)
const togglingId = ref(null)
const editorTab = ref('content')

const menuHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const defaultForm = () => ({
  slug: '', template: 'classic', name: '', description: '',
  logo_path: '', background_image_path: '', theme_color: '#e8655a',
  currency: 'USD', location: '', phone: '', hours: '',
  sections: defaultMenuSections(),
  custom_domain_id: null,
  qr_shape: 'square', dot_style: 'square', corner_style: 'sharp', frame_style: 'none',
})
const form = ref(defaultForm())

const previewMenuUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/menu/${form.value.slug}` : `${base}/menu/...`
})

function addSection() {
  form.value.sections.push({ name: t('digitalMenus.newSectionDefault'), items: [] })
}

function addItem(section) {
  if (!section.items) section.items = []
  section.items.push({ name: '', description: '', price: '', image_path: '', tags: [] })
}

function toggleTag(item, tag) {
  if (!item.tags) item.tags = []
  const idx = item.tags.indexOf(tag)
  if (idx === -1) item.tags.push(tag)
  else item.tags.splice(idx, 1)
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function openEdit(menu) {
  editId.value = menu.id
  form.value = {
    ...menu,
    template: menu.template || 'classic',
    sections: JSON.parse(JSON.stringify(menu.sections || defaultMenuSections())),
    theme_color: menu.theme_color || '#e8655a',
    currency: menu.currency || 'USD',
    qr_shape: menu.qr_shape || 'square',
    dot_style: menu.dot_style || 'square',
    corner_style: menu.corner_style || 'sharp',
    frame_style: menu.frame_style || 'none',
  }
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

async function load() {
  const { data } = await api.get('/digital-menus')
  menus.value = data
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    delete payload.is_active
    delete payload.menu_url
    delete payload.domain_label
    delete payload.view_count
    if (editId.value) await api.put(`/digital-menus/${editId.value}`, payload)
    else await api.post('/digital-menus', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(menu) {
  togglingId.value = menu.id
  try {
    const { data } = await api.patch(`/digital-menus/${menu.id}/publish`)
    const idx = menus.value.findIndex(m => m.id === menu.id)
    if (idx !== -1) menus.value[idx] = data
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('digitalPages.updateFailedMessage'), variant: 'danger' })
  } finally {
    togglingId.value = null
  }
}

async function deleteMenu(menu) {
  const ok = await dialog.confirm({
    title: t('digitalMenus.deleteTitle'),
    message: t('digitalMenus.deleteMessage', { name: menu.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/digital-menus/${menu.id}`)
  await load()
}

function showAnalytics(menu) { analyticsMenu.value = menu }

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
.editor-panel__header h3 { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); }
.template-section, .content-section, .qr-section {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem;
  padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;
}
.section-title { font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.menu-sections-editor {
  background: var(--surface); border: 1px solid var(--border); border-radius: 0.75rem;
  padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem;
}
.repeater-head { display: flex; justify-content: space-between; align-items: center; }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; font-size: 0.8125rem; }
.section-block {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem;
  padding: 0.875rem; display: flex; flex-direction: column; gap: 0.625rem;
}
.section-block__head { display: flex; gap: 0.5rem; align-items: center; }
.item-block {
  padding: 0.75rem; background: var(--surface); border-radius: 0.5rem;
  border: 1px dashed var(--border); display: flex; flex-direction: column; gap: 0.5rem;
}
.tag-picker { display: flex; flex-wrap: wrap; gap: 0.375rem; }
.tag-btn {
  font-size: 0.6875rem; font-weight: 600; text-transform: uppercase;
  padding: 0.25rem 0.5rem; border-radius: 9999px; border: 1px solid var(--border);
  background: var(--surface); color: var(--text-muted); cursor: pointer;
}
.tag-btn.active { background: var(--purple-muted); color: var(--purple); border-color: color-mix(in srgb, var(--purple) 30%, var(--border)); }
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; }
.remove-btn.block { font-size: 0.75rem; text-align: left; padding: 0; }
.form-actions { display: flex; gap: 0.75rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.menus-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; align-items: start; }
.menu-item { display: flex; justify-content: center; }
.menu-item__stack {
  position: relative;
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.menu-item.draft { opacity: 0.88; }
.draft-ribbon {
  position: absolute; top: 0.5rem; left: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.menu-item__footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
}
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.menu-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem;
  border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; }
.drawer-overlay { position: fixed; inset: 0; background: rgba(26,19,51,0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
</style>
