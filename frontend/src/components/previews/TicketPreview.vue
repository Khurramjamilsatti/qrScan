<template>
  <div class="module-preview">
    <TicketRenderer
      :event-name="eventName"
      :event-date="eventDate"
      :event-time="eventTime"
      :venue="venue"
      :holder-name="holderName"
      :ticket-type="ticketType"
      :seat-section="seatSection"
      :seat-row="seatRow"
      :seat-number="seatNumber"
      :order-id="orderId"
      :barcode="barcode"
      :template="template"
      :terms="terms"
      :status="status"
      :theme-color="themeColor"
      :logo="logo"
      :background-image="backgroundImage"
    />
    <div v-if="ticketUrl" class="module-preview__url">
      <span class="module-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="module-preview__link">{{ ticketUrl }}</span>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import TicketRenderer from '../tickets/TicketRenderer.vue'

const { t } = useI18n()

defineProps({
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
  ticketUrl: String,
})
</script>

<style scoped>
.module-preview__url { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); }
.module-preview__label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.module-preview__link { display: block; font-size: 0.75rem; font-family: monospace; color: var(--brand); margin-top: 0.25rem; word-break: break-all; }
</style>
