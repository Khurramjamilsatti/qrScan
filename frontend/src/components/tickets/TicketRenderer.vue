<template>
  <div class="ticket-tpl" :class="`ticket-tpl--${template}`" :style="{ '--ticket-theme': themeColor }">
    <div class="ticket-tpl__header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <div class="ticket-tpl__overlay"></div>
      <img v-if="logoUrl" :src="logoUrl" alt="" class="ticket-tpl__logo" />
      <div class="ticket-tpl__event">
        <span class="ticket-tpl__type">{{ typeLabel }}</span>
        <h2>{{ eventName || t('renderers.ticket.defaultEvent') }}</h2>
      </div>
    </div>

    <div class="ticket-tpl__body">
      <div class="ticket-tpl__row">
        <div class="ticket-tpl__field">
          <span class="ticket-tpl__label">{{ t('renderers.ticket.holder') }}</span>
          <span class="ticket-tpl__value">{{ holderName || t('renderers.ticket.guest') }}</span>
        </div>
        <div v-if="orderId" class="ticket-tpl__field">
          <span class="ticket-tpl__label">{{ t('renderers.ticket.order') }}</span>
          <span class="ticket-tpl__value mono">{{ orderId }}</span>
        </div>
      </div>

      <div class="ticket-tpl__meta">
        <div v-if="eventDate" class="ticket-tpl__meta-item">📅 {{ eventDate }}<span v-if="eventTime"> · {{ eventTime }}</span></div>
        <div v-if="venue" class="ticket-tpl__meta-item">📍 {{ venue }}</div>
      </div>

      <div v-if="hasSeat" class="ticket-tpl__seats">
        <div v-if="seatSection" class="ticket-tpl__seat"><span>{{ t('renderers.ticket.section') }}</span><strong>{{ seatSection }}</strong></div>
        <div v-if="seatRow" class="ticket-tpl__seat"><span>{{ t('renderers.ticket.row') }}</span><strong>{{ seatRow }}</strong></div>
        <div v-if="seatNumber" class="ticket-tpl__seat"><span>{{ t('renderers.ticket.seat') }}</span><strong>{{ seatNumber }}</strong></div>
      </div>

      <div v-if="barcode" class="ticket-tpl__barcode">
        <span class="ticket-tpl__barcode-lines"></span>
        <span class="ticket-tpl__barcode-text">{{ barcode }}</span>
      </div>

      <div class="ticket-tpl__status" :class="`ticket-tpl__status--${status}`">{{ statusLabel }}</div>

      <p v-if="terms" class="ticket-tpl__terms">{{ terms }}</p>
    </div>

    <div class="ticket-tpl__stub"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'
import { ticketTypeLabel } from '../../utils/digitalModules'

const { t } = useI18n()

const props = defineProps({
  eventName: String,
  eventDate: String,
  eventTime: String,
  venue: String,
  holderName: String,
  ticketType: { type: String, default: 'general' },
  seatSection: String,
  seatRow: String,
  seatNumber: String,
  orderId: String,
  barcode: String,
  template: { type: String, default: 'concert' },
  terms: String,
  status: { type: String, default: 'valid' },
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
})

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)
const headerStyle = computed(() => bgUrl.value ? { backgroundImage: `url(${bgUrl.value})` } : {})
const typeLabel = computed(() => ticketTypeLabel(props.ticketType))
const hasSeat = computed(() => props.seatSection || props.seatRow || props.seatNumber)
const statusLabel = computed(() => ({
  valid: t('digitalTickets.statusValid'),
  used: t('digitalTickets.statusUsed'),
  expired: t('digitalTickets.statusExpired'),
  cancelled: t('digitalTickets.statusCancelled'),
}[props.status] || props.status))
</script>

<style scoped>
.ticket-tpl {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e8e4f0);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  position: relative;
}
.ticket-tpl__header {
  position: relative;
  padding: 1rem 1.25rem 1.25rem;
  background: linear-gradient(135deg, var(--ticket-theme), color-mix(in srgb, var(--ticket-theme) 50%, #1a1333));
  background-size: cover;
  color: #fff;
  min-height: 5rem;
}
.ticket-tpl__header.has-bg .ticket-tpl__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(26,19,51,0.55), rgba(26,19,51,0.75));
}
.ticket-tpl__logo {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.5rem;
  object-fit: cover;
  border: 2px solid rgba(255,255,255,0.35);
  z-index: 2;
}
.ticket-tpl__event { position: relative; z-index: 1; padding-right: 3rem; }
.ticket-tpl__type {
  display: inline-block;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 0.2rem 0.5rem;
  border-radius: 9999px;
  background: rgba(255,255,255,0.2);
  margin-bottom: 0.375rem;
}
.ticket-tpl__event h2 { font-size: 1.125rem; font-weight: 800; line-height: 1.25; }
.ticket-tpl__body { padding: 1rem 1.25rem 1.25rem; }
.ticket-tpl__row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
.ticket-tpl__field { flex: 1; min-width: 7rem; }
.ticket-tpl__label { display: block; font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted, #8b839c); }
.ticket-tpl__value { display: block; font-size: 0.9375rem; font-weight: 700; color: var(--text-primary, #1a1333); margin-top: 0.125rem; }
.ticket-tpl__value.mono { font-family: monospace; font-size: 0.8125rem; }
.ticket-tpl__meta { display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem; }
.ticket-tpl__meta-item { font-size: 0.8125rem; color: var(--text-secondary, #5c5470); }
.ticket-tpl__seats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
  margin-bottom: 0.875rem;
}
.ticket-tpl__seat {
  text-align: center;
  padding: 0.5rem;
  border-radius: 0.625rem;
  background: var(--bg-subtle, #faf8fd);
  border: 1px solid var(--border, #e8e4f0);
}
.ticket-tpl__seat span { display: block; font-size: 0.5625rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted, #8b839c); }
.ticket-tpl__seat strong { display: block; font-size: 1rem; font-weight: 800; color: var(--ticket-theme); margin-top: 0.125rem; }
.ticket-tpl__barcode { text-align: center; margin: 0.75rem 0; }
.ticket-tpl__barcode-lines {
  display: block;
  height: 2.5rem;
  margin: 0 auto 0.375rem;
  max-width: 12rem;
  background: repeating-linear-gradient(90deg, #1a1333 0 2px, transparent 2px 5px);
}
.ticket-tpl__barcode-text { font-size: 0.6875rem; font-family: monospace; color: var(--text-muted, #8b839c); }
.ticket-tpl__status {
  display: inline-block;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.3rem 0.75rem;
  border-radius: 9999px;
  margin-top: 0.25rem;
}
.ticket-tpl__status--valid { background: #dcfce7; color: #166534; }
.ticket-tpl__status--used { background: #fef3c7; color: #92400e; }
.ticket-tpl__status--expired, .ticket-tpl__status--cancelled { background: #fee2e2; color: #991b1b; }
.ticket-tpl__terms { font-size: 0.6875rem; color: var(--text-muted, #8b839c); margin-top: 0.875rem; line-height: 1.4; }
.ticket-tpl__stub {
  height: 0.5rem;
  background: repeating-linear-gradient(90deg, var(--ticket-theme) 0 8px, transparent 8px 16px);
  opacity: 0.35;
}
.ticket-tpl--transit .ticket-tpl__header { border-left: 4px solid var(--ticket-theme); background: var(--bg-subtle, #faf8fd); color: var(--text-primary, #1a1333); }
.ticket-tpl--transit .ticket-tpl__overlay { display: none; }
</style>
