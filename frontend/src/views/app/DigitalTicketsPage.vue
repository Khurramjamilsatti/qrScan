<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalTickets.title') }}</h2>
        <p class="page-sub">{{ t('digitalTickets.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('digitalTickets.newTicket') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalTickets.editTicket') : t('digitalTickets.createTicket') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor>
        <template #form>
          <form @submit.prevent="save" class="form-stack">
            <DomainSelect v-model="form.custom_domain_id" />
            <div class="form-group">
              <label>{{ t('digitalTickets.ticketUrlSlug') }}</label>
              <div class="slug-input">
                <span>{{ ticketHost }}/ticket/</span>
                <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="summer-fest-2026" />
              </div>
            </div>

            <div class="template-section">
              <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
              <div class="template-grid">
                <button
                  v-for="tpl in ticketTemplates"
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

            <div class="form-group">
              <label>{{ t('digitalTickets.eventName') }}</label>
              <input v-model="form.event_name" required class="input-field" placeholder="Summer Music Festival" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.eventDate') }}</label>
                <input v-model="form.event_date" class="input-field" placeholder="June 15, 2026" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalTickets.eventTime') }}</label>
                <input v-model="form.event_time" class="input-field" placeholder="7:00 PM" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('digitalTickets.venue') }}</label>
              <input v-model="form.venue" class="input-field" placeholder="Central Park Arena" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.holderName') }}</label>
                <input v-model="form.holder_name" required class="input-field" :placeholder="t('auth.namePlaceholder')" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalTickets.holderEmail') }}</label>
                <input v-model="form.holder_email" type="email" class="input-field" :placeholder="t('auth.emailPlaceholder')" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.ticketType') }}</label>
                <select v-model="form.ticket_type" class="input-field">
                  <option v-for="ticketType in ticketTypes" :key="ticketType.id" :value="ticketType.id">{{ ticketType.label }}</option>
                </select>
              </div>
              <div class="form-group">
                <label>{{ t('common.status') }}</label>
                <select v-model="form.status" class="input-field">
                  <option value="valid">{{ t('digitalTickets.statusValid') }}</option>
                  <option value="used">{{ t('digitalTickets.statusUsed') }}</option>
                  <option value="expired">{{ t('digitalTickets.statusExpired') }}</option>
                  <option value="cancelled">{{ t('digitalTickets.statusCancelled') }}</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.seatSection') }}</label>
                <input v-model="form.seat_section" class="input-field" placeholder="A" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalTickets.seatRow') }}</label>
                <input v-model="form.seat_row" class="input-field" placeholder="12" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('digitalTickets.seatNumber') }}</label>
              <input v-model="form.seat_number" class="input-field" placeholder="8" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.orderId') }}</label>
                <input v-model="form.order_id" class="input-field" placeholder="ORD-12345" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalTickets.barcode') }}</label>
                <input v-model="form.barcode" class="input-field" placeholder="1234567890" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('nav.terms') }}</label>
              <textarea v-model="form.terms" class="input-field" rows="3" placeholder="Non-transferable. Valid ID required at entry."></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>{{ t('digitalTickets.validFrom') }}</label>
                <input v-model="form.valid_from" type="datetime-local" class="input-field" />
              </div>
              <div class="form-group">
                <label>{{ t('digitalTickets.validUntil') }}</label>
                <input v-model="form.valid_until" type="datetime-local" class="input-field" />
              </div>
            </div>

            <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="event-logo" ai-placeholder="event brand logo" />
            <ImageAssetField v-model="form.background_image_path" :label="t('common.background')" folder="backgrounds" ai-context="event-background" ai-placeholder="concert stage lights" />
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

            <p v-if="error" class="error-text">{{ error }}</p>
            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <TicketPreview
            :event-name="form.event_name"
            :event-date="form.event_date"
            :event-time="form.event_time"
            :venue="form.venue"
            :holder-name="form.holder_name"
            :ticket-type="form.ticket_type"
            :seat-section="form.seat_section"
            :seat-row="form.seat_row"
            :seat-number="form.seat_number"
            :order-id="form.order_id"
            :barcode="form.barcode"
            :template="form.template"
            :terms="form.terms"
            :status="form.status"
            :theme-color="form.theme_color"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :ticket-url="previewTicketUrl"
          />
          <div class="mt-4 text-center">
            <QrPreview
              v-if="form.slug"
              :content="previewTicketUrl"
              :name="form.event_name"
              :foreground="form.theme_color"
              :logo-url="form.logo_path"
              :background-image="form.background_image_path"
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

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!tickets.length" class="empty-state">
        <div class="empty-icon">🎫</div>
        <h3>{{ t('digitalTickets.emptyTitle') }}</h3>
        <p>{{ t('digitalTickets.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('digitalTickets.emptyCta') }}</button>
      </div>
      <div v-else class="tickets-grid">
        <div v-for="ticket in tickets" :key="ticket.id" class="ticket-item" :class="{ draft: !ticket.is_active }">
          <div v-if="!ticket.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
          <TicketPreview
            :event-name="ticket.event_name"
            :event-date="ticket.event_date"
            :event-time="ticket.event_time"
            :venue="ticket.venue"
            :holder-name="ticket.holder_name"
            :ticket-type="ticket.ticket_type"
            :seat-section="ticket.seat_section"
            :seat-row="ticket.seat_row"
            :seat-number="ticket.seat_number"
            :order-id="ticket.order_id"
            :barcode="ticket.barcode"
            :template="ticket.template"
            :terms="ticket.terms"
            :status="ticket.status"
            :theme-color="ticket.theme_color"
            :logo="ticket.logo_path"
            :background-image="ticket.background_image_path"
            :ticket-url="ticket.ticket_url"
          />
          <div class="ticket-item__footer">
            <PublishToggle
              :model-value="!!ticket.is_active"
              :loading="togglingId === ticket.id"
              :active-label="t('publish.published')"
              :inactive-label="t('publish.draft')"
              @update:model-value="togglePublish(ticket)"
            />
            <span class="view-stat">
              <span class="view-stat__num">{{ ticket.view_count }}</span>
              <span class="view-stat__label">{{ t('common.views') }}</span>
            </span>
            <div class="ticket-item__actions">
              <CopyButton :text="ticket.ticket_url" :label="t('common.copy')" />
              <button @click="openEdit(ticket)" class="action-btn">{{ t('common.edit') }}</button>
              <button @click="showAnalytics(ticket)" class="action-btn">{{ t('common.stats') }}</button>
              <button @click="deleteTicket(ticket)" class="action-btn danger">{{ t('common.delete') }}</button>
            </div>
          </div>
        </div>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsTicket" class="drawer-overlay" @click.self="analyticsTicket = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsTicket.event_name }) }}</h3>
          <button @click="analyticsTicket = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="digital-tickets" :id="analyticsTicket.id" />
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
import TicketPreview from '../../components/previews/TicketPreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import { TICKET_TEMPLATES, TICKET_TYPES, defaultTicketForm } from '../../utils/digitalModules'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()

const tickets = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const loadError = ref('')
const analyticsTicket = ref(null)
const togglingId = ref(null)

const ticketTemplates = computed(() => translateList(TICKET_TEMPLATES, t))
const ticketTypes = computed(() => translateList(TICKET_TYPES, t))

const ticketHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const form = ref(defaultTicketForm())

const previewTicketUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/ticket/${form.value.slug}` : `${base}/ticket/...`
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

function openCreate() {
  editId.value = null
  form.value = defaultTicketForm()
  editing.value = true
  error.value = ''
}

function openEdit(ticket) {
  editId.value = ticket.id
  form.value = {
    ...ticket,
    theme_color: ticket.theme_color || '#e8655a',
    qr_shape: ticket.qr_shape || 'square',
    dot_style: ticket.dot_style || 'square',
    corner_style: ticket.corner_style || 'sharp',
    frame_style: ticket.frame_style || 'none',
    valid_from: toDatetimeLocal(ticket.valid_from),
    valid_until: toDatetimeLocal(ticket.valid_until),
  }
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
    const { data } = await api.get('/digital-tickets')
    tickets.value = Array.isArray(data) ? data : []
  } catch (e) {
    tickets.value = []
    loadError.value = e.response?.data?.message || t('digitalTickets.loadFailed')
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const payload = JSON.parse(JSON.stringify(form.value))
    delete payload.is_active
    delete payload.ticket_url
    delete payload.domain_label
    delete payload.view_count
    delete payload.check_in_count
    if (editId.value) await api.put(`/digital-tickets/${editId.value}`, payload)
    else await api.post('/digital-tickets', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(ticket) {
  togglingId.value = ticket.id
  try {
    const { data } = await api.patch(`/digital-tickets/${ticket.id}/publish`)
    const idx = tickets.value.findIndex(t => t.id === ticket.id)
    if (idx !== -1) tickets.value[idx] = data
  } catch {
    dialog.alert({ title: t('common.notice'), message: t('digitalPages.updateFailedMessage'), variant: 'danger' })
  } finally {
    togglingId.value = null
  }
}

async function deleteTicket(ticket) {
  const ok = await dialog.confirm({
    title: t('digitalTickets.deleteTitle'),
    message: t('digitalTickets.deleteMessage', { eventName: ticket.event_name }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/digital-tickets/${ticket.id}`)
  await load()
}

function showAnalytics(ticket) { analyticsTicket.value = ticket }

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
.template-section, .qr-section {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.section-title { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.template-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
@media (min-width: 640px) { .template-grid { grid-template-columns: repeat(4, 1fr); } }
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
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.tickets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.ticket-item { position: relative; display: flex; flex-direction: column; gap: 0.75rem; }
.ticket-item.draft { opacity: 0.88; }
.draft-ribbon {
  position: absolute; top: 0.5rem; left: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.ticket-item__footer { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.ticket-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
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
