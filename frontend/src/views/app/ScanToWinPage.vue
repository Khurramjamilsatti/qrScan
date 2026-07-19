<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('scanToWin.title') }}</h2>
        <p class="page-sub">{{ t('scanToWin.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('scanToWin.newCampaign') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('scanToWin.editCampaign') : t('scanToWin.createCampaign') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="editorTab">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('scanToWin.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('scanToWin.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('scanToWin.tabs.qrDesign') }}</button>
            </div>

            <div class="editor-form__scroll">
              <div v-show="editorTab === 'content'" class="tab-panel">
                <DomainSelect v-model="form.custom_domain_id" />
                <div class="form-group">
                  <label>{{ t('scanToWin.campaignUrlSlug') }}</label>
                  <div class="slug-input">
                    <span>{{ campaignHost }}/win/</span>
                    <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="summer-giveaway" />
                  </div>
                </div>
                <div class="form-group">
                  <label>{{ t('scanToWin.campaignName') }}</label>
                  <input v-model="form.name" required class="input-field" placeholder="Summer Giveaway 2026" />
                </div>
                <div class="form-group">
                  <label>{{ t('common.description') }}</label>
                  <textarea v-model="form.description" class="input-field" rows="2" placeholder="Scan the QR code for a chance to win!"></textarea>
                </div>

                <div class="template-section">
                  <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
                  <div class="template-grid template-grid--3">
                    <button
                      v-for="tpl in scanTemplates"
                      :key="tpl.id"
                      type="button"
                      class="template-card"
                      :class="{ active: form.template === tpl.id }"
                      @click="form.template = tpl.id"
                    >
                      <span class="template-card__icon">{{ tpl.icon }}</span>
                      <span class="template-card__label">{{ tpl.label }}</span>
                      <span class="template-card__desc">{{ tpl.description }}</span>
                    </button>
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label>{{ t('scanToWin.startsAt') }}</label>
                    <input v-model="form.starts_at" type="datetime-local" class="input-field" />
                  </div>
                  <div class="form-group">
                    <label>{{ t('scanToWin.endsAt') }}</label>
                    <input v-model="form.ends_at" type="datetime-local" class="input-field" />
                  </div>
                </div>
                <div class="form-group">
                  <label>{{ t('scanToWin.maxPlaysPerDay') }}</label>
                  <input v-model.number="form.max_plays_per_day" type="number" min="0" class="input-field" />
                </div>
                <div class="form-group">
                  <label>{{ t('scanToWin.winMessage') }}</label>
                  <input v-model="form.win_message" class="input-field" :placeholder="t('scanToWin.defaultWinMessage')" />
                </div>
                <div class="form-group">
                  <label>{{ t('scanToWin.loseMessage') }}</label>
                  <input v-model="form.lose_message" class="input-field" :placeholder="t('scanToWin.defaultLoseMessage')" />
                </div>
                <div class="form-group">
                  <label>{{ t('scanToWin.terms') }}</label>
                  <textarea v-model="form.terms" class="input-field" rows="3" :placeholder="t('scanToWin.termsPlaceholder')"></textarea>
                </div>

                <div class="content-section">
                  <div class="repeater-head">
                    <span class="section-title">{{ t('scanToWin.prizes') }}</span>
                    <button type="button" @click="addPrize" class="link-btn">+ {{ t('scanToWin.addPrize') }}</button>
                  </div>
                  <div v-for="(prize, i) in form.prizes" :key="i" class="repeater-block">
                    <div class="form-row">
                      <div class="form-group">
                        <label>{{ t('scanToWin.prizeName') }}</label>
                        <input v-model="prize.name" class="input-field" placeholder="Free coffee" />
                      </div>
                      <div class="form-group">
                        <label>{{ t('scanToWin.weightOdds') }}</label>
                        <input v-model.number="prize.weight" type="number" min="1" class="input-field" />
                      </div>
                    </div>
                    <div class="form-group">
                      <label>{{ t('common.description') }}</label>
                      <input v-model="prize.description" class="input-field" placeholder="Redeem at counter" />
                    </div>
                    <ImageAssetField v-model="prize.image_path" :label="t('scanToWin.prizeImage')" folder="prizes" ai-context="prize-gift" ai-placeholder="gift box prize" />
                    <div class="form-row">
                      <div class="form-group">
                        <label>{{ t('scanToWin.quantity') }}</label>
                        <input v-model.number="prize.quantity" type="number" min="0" class="input-field" @change="syncPrizeRemaining(prize)" />
                      </div>
                      <div class="form-group">
                        <label>{{ t('scanToWin.remaining') }}</label>
                        <input v-model.number="prize.remaining" type="number" min="0" class="input-field" />
                      </div>
                    </div>
                    <button type="button" @click="form.prizes.splice(i, 1)" class="remove-btn block">{{ t('scanToWin.removePrize') }}</button>
                  </div>
                </div>
              </div>

              <div v-show="editorTab === 'appearance'" class="tab-panel">
                <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="campaign-logo" ai-placeholder="promotional brand logo" />
                <ImageAssetField v-model="form.background_image_path" :label="t('common.background')" folder="backgrounds" ai-context="giveaway-background" ai-placeholder="festive celebration background" />
                <div class="form-group">
                  <label>{{ t('common.themeColor') }}</label>
                  <input v-model="form.theme_color" type="color" class="color-input" />
                </div>
              </div>

              <div v-show="editorTab === 'qr'" class="tab-panel">
                <QrStyleFields
                  v-model:qr-shape="form.qr_shape"
                  v-model:dot-style="form.dot_style"
                  v-model:corner-style="form.corner_style"
                  v-model:frame-style="form.frame_style"
                />
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
            :content="previewCampaignUrl"
            :name="form.name"
            :foreground="form.theme_color"
            :logo-url="form.logo_path"
            :background-image="form.background_image_path"
            :size="220"
            :qr-shape="form.qr_shape"
            :dot-style="form.dot_style"
            :corner-style="form.corner_style"
            :frame-style="form.frame_style"
          />
          <ScanToWinPreview
            v-else
            :name="form.name"
            :description="form.description"
            :template="form.template"
            :starts-at="form.starts_at"
            :ends-at="form.ends_at"
            :max-plays-per-day="form.max_plays_per_day"
            :prizes="form.prizes"
            :terms="form.terms"
            :theme-color="form.theme_color"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :campaign-url="previewCampaignUrl"
          />
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!campaigns.length" class="empty-state">
        <div class="empty-icon">🎁</div>
        <h3>{{ t('scanToWin.emptyTitle') }}</h3>
        <p>{{ t('scanToWin.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('scanToWin.emptyCta') }}</button>
      </div>
      <div v-else class="campaigns-grid">
        <div v-for="campaign in campaigns" :key="campaign.id" class="campaign-item" :class="{ draft: !campaign.is_active }">
          <div class="campaign-item__stack">
            <div v-if="!campaign.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
            <div class="item-preview-scroll">
              <ScanToWinPreview
                compact
                :name="campaign.name"
            :description="campaign.description"
            :template="campaign.template"
            :starts-at="campaign.starts_at"
            :ends-at="campaign.ends_at"
            :max-plays-per-day="campaign.max_plays_per_day"
            :prizes="campaign.prizes || []"
            :terms="campaign.terms"
            :theme-color="campaign.theme_color"
            :logo="campaign.logo_path"
            :background-image="campaign.background_image_path"
              />
            </div>
          </div>
          <div class="campaign-item__footer">
            <PublishToggle
              :model-value="!!campaign.is_active"
              :loading="togglingId === campaign.id"
              :active-label="t('publish.published')"
              :inactive-label="t('publish.draft')"
              @update:model-value="togglePublish(campaign)"
            />
            <span v-if="campaign.total_plays != null" class="view-stat">
              <span class="view-stat__num">{{ campaign.total_plays }}</span>
              <span class="view-stat__label">{{ t('common.plays') }}</span>
            </span>
            <span v-if="campaign.total_wins != null" class="view-stat">
              <span class="view-stat__num">{{ campaign.total_wins }}</span>
              <span class="view-stat__label">{{ t('common.wins') }}</span>
            </span>
            <div class="campaign-item__actions">
              <CopyButton :text="campaign.campaign_url" :label="t('common.copy')" />
              <button @click="openEdit(campaign)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="showAnalytics(campaign)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteCampaign(campaign)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsCampaign" class="drawer-overlay" @click.self="analyticsCampaign = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsCampaign.name }) }}</h3>
          <button @click="analyticsCampaign = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="scan-to-win" :id="analyticsCampaign.id" />
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
import ScanToWinPreview from '../../components/previews/ScanToWinPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import { SCAN_TEMPLATES, defaultScanToWinForm, defaultPrize } from '../../utils/digitalModules'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()

const scanTemplates = computed(() => translateList(SCAN_TEMPLATES, t))

const campaigns = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const loadError = ref('')
const analyticsCampaign = ref(null)
const togglingId = ref(null)
const editorTab = ref('content')

const campaignHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const form = ref(defaultScanToWinForm())

const previewCampaignUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/win/${form.value.slug}` : `${base}/win/...`
})

function toDatetimeLocal(value) {
  if (!value) return ''
  try {
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return value
    const pad = (n) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
  } catch {
    return value
  }
}

function addPrize() {
  if (!form.value.prizes) form.value.prizes = []
  form.value.prizes.push(defaultPrize())
}

function syncPrizeRemaining(prize) {
  if (prize.remaining == null || prize.remaining > prize.quantity) {
    prize.remaining = prize.quantity
  }
}

function openCreate() {
  editId.value = null
  form.value = defaultScanToWinForm()
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function openEdit(campaign) {
  editId.value = campaign.id
  form.value = {
    ...campaign,
    prizes: JSON.parse(JSON.stringify(campaign.prizes || [])),
    theme_color: campaign.theme_color || '#e8655a',
    qr_shape: campaign.qr_shape || 'square',
    dot_style: campaign.dot_style || 'square',
    corner_style: campaign.corner_style || 'sharp',
    frame_style: campaign.frame_style || 'none',
    starts_at: toDatetimeLocal(campaign.starts_at),
    ends_at: toDatetimeLocal(campaign.ends_at),
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
  loadError.value = ''
  try {
    const { data } = await api.get('/scan-to-win')
    campaigns.value = Array.isArray(data) ? data : []
  } catch (e) {
    campaigns.value = []
    loadError.value = e.response?.data?.message || t('scanToWin.loadFailed')
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = JSON.parse(JSON.stringify(form.value))
    delete payload.is_active
    delete payload.campaign_url
    delete payload.domain_label
    delete payload.view_count
    delete payload.total_plays
    delete payload.total_wins
    if (editId.value) await api.put(`/scan-to-win/${editId.value}`, payload)
    else await api.post('/scan-to-win', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(campaign) {
  togglingId.value = campaign.id
  try {
    const { data } = await api.patch(`/scan-to-win/${campaign.id}/publish`)
    const idx = campaigns.value.findIndex(c => c.id === campaign.id)
    if (idx !== -1) campaigns.value[idx] = data
  } catch {
    dialog.alert({
      title: t('common.notice'),
      message: t('scanToWin.updateFailedMessage'),
      variant: 'danger',
    })
  } finally {
    togglingId.value = null
  }
}

async function deleteCampaign(campaign) {
  const ok = await dialog.confirm({
    title: t('scanToWin.deleteTitle'),
    message: t('scanToWin.deleteMessage', { name: campaign.name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/scan-to-win/${campaign.id}`)
  await load()
}

function showAnalytics(campaign) { analyticsCampaign.value = campaign }

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
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.template-section, .content-section, .qr-section {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.section-title { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
.template-grid--3 { grid-template-columns: 1fr; }
@media (min-width: 640px) { .template-grid--3 { grid-template-columns: repeat(3, 1fr); } }
.template-card {
  display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.25rem;
  padding: 0.75rem 0.5rem; border-radius: 0.75rem; border: 2px solid var(--border);
  background: var(--surface); cursor: pointer; transition: all 0.15s;
}
.template-card:hover { border-color: color-mix(in srgb, var(--brand) 40%, var(--border)); }
.template-card.active { border-color: var(--brand); background: var(--brand-muted); }
.template-card__icon { font-size: 1.25rem; }
.template-card__label { font-size: 0.75rem; font-weight: 700; color: var(--text-primary); }
.template-card__desc { font-size: 0.625rem; color: var(--text-muted); line-height: 1.3; }
.repeater-head { display: flex; justify-content: space-between; align-items: center; }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; font-size: 0.8125rem; }
.repeater-block { display: flex; flex-direction: column; gap: 0.5rem; padding: 0.75rem; background: var(--surface); border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 0.5rem; }
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.875rem; }
.remove-btn.block { font-size: 0.75rem; text-align: left; padding: 0; }
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.campaigns-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; align-items: stretch; }
.campaign-item {
  position: relative;
  display: flex;
  flex-direction: column;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.campaign-item__stack {
  position: relative;
  flex: 1 1 auto;
  display: flex;
  flex-direction: column;
  min-height: 0;
}
.item-preview-scroll {
  flex: 1 1 auto;
  max-height: 400px;
  overflow-y: auto;
  background: var(--bg-subtle);
}
.campaign-item.draft { opacity: 0.88; }
.draft-ribbon {
  position: absolute; top: 0.5rem; left: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.campaign-item__footer {
  flex-shrink: 0;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border);
  background: var(--surface);
}
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.campaign-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
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
