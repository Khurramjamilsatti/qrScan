<template>
  <div class="link-preview-card">
    <div class="browser-chrome">
      <div class="dots"><span></span><span></span><span></span></div>
      <div class="url-bar">{{ shortUrl || 'qrscan.digital/r/your-slug' }}</div>
    </div>
    <div class="link-body">
      <div v-if="title" class="link-title">{{ title }}</div>
      <div v-else class="link-title placeholder">{{ t('previews.campaignTitle') }}</div>
      <p v-if="description" class="link-desc">{{ description }}</p>
      <p v-else class="link-desc placeholder">{{ t('previews.addDescription') }}</p>
      <div class="redirect-box">
        <div class="redirect-label">{{ t('previews.redirectsTo') }}</div>
        <div class="dest-url">{{ fullDestination || 'https://your-destination.com' }}</div>
      </div>
      <div v-if="hasUtm" class="utm-tags">
        <span v-if="utm_source" class="utm-tag">source: {{ utm_source }}</span>
        <span v-if="utm_medium" class="utm-tag">medium: {{ utm_medium }}</span>
        <span v-if="utm_campaign" class="utm-tag">campaign: {{ utm_campaign }}</span>
        <span v-if="utm_term" class="utm-tag">term: {{ utm_term }}</span>
        <span v-if="utm_content" class="utm-tag">content: {{ utm_content }}</span>
      </div>
      <div v-if="expiresAt" class="expiry">
        Expires {{ formatDate(expiresAt) }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  slug: String,
  title: String,
  description: String,
  destination: String,
  utm_source: String,
  utm_medium: String,
  utm_campaign: String,
  utm_term: String,
  utm_content: String,
  expiresAt: String,
  baseUrl: { type: String, default: 'qrscan.digital/r/' },
})

const shortUrl = computed(() => props.slug ? `${props.baseUrl}${props.slug}` : null)

const fullDestination = computed(() => {
  if (!props.destination) return null
  const params = {}
  if (props.utm_source) params.utm_source = props.utm_source
  if (props.utm_medium) params.utm_medium = props.utm_medium
  if (props.utm_campaign) params.utm_campaign = props.utm_campaign
  if (props.utm_term) params.utm_term = props.utm_term
  if (props.utm_content) params.utm_content = props.utm_content
  const qs = new URLSearchParams(params).toString()
  if (!qs) return props.destination
  const sep = props.destination.includes('?') ? '&' : '?'
  return props.destination + sep + qs
})

const hasUtm = computed(() => props.utm_source || props.utm_medium || props.utm_campaign || props.utm_term || props.utm_content)

function formatDate(d) {
  return new Date(d).toLocaleDateString()
}
</script>

<style scoped>
.link-preview-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.browser-chrome {
  background: var(--bg-subtle);
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-bottom: 1px solid var(--border);
}
.dots { display: flex; gap: 0.375rem; }
.dots span { width: 10px; height: 10px; border-radius: 50%; background: var(--border-strong); }
.dots span:first-child { background: var(--brand); }
.dots span:nth-child(2) { background: var(--gold); }
.dots span:nth-child(3) { background: var(--purple); }
.url-bar {
  flex: 1;
  background: var(--surface);
  border-radius: 0.5rem;
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
  font-family: monospace;
  color: var(--brand);
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  border: 1px solid var(--border);
}
.link-body { padding: 1.25rem; }
.link-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); }
.link-title.placeholder { color: var(--text-muted); }
.link-desc { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.375rem; line-height: 1.5; }
.link-desc.placeholder { color: var(--text-muted); }
.redirect-box {
  margin-top: 1rem;
  padding: 0.875rem;
  background: var(--bg-subtle);
  border-radius: 0.75rem;
  border: 1px dashed var(--border);
}
.redirect-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}
.dest-url { font-size: 0.8125rem; color: var(--text-secondary); word-break: break-all; font-family: monospace; }
.utm-tags { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.875rem; }
.utm-tag {
  font-size: 0.6875rem;
  padding: 0.25rem 0.5rem;
  background: var(--brand-muted);
  color: var(--brand);
  border-radius: 0.375rem;
  font-weight: 600;
  border: 1px solid color-mix(in srgb, var(--brand) 25%, var(--border));
}
.expiry { margin-top: 0.75rem; font-size: 0.75rem; color: var(--gold); font-weight: 600; }
</style>
