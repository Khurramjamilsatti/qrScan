<template>
  <div class="card-html-preview">
    <HtmlDocumentPreview
      :html="html"
      :title="fullName || 'Card preview'"
      :compact="compact"
      :embedded="embedded"
      :scrollable="embedded && compact"
    />
    <div v-if="cardUrl && !compact" class="card-html-preview__meta">
      <span class="card-html-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="card-html-preview__url">{{ cardUrl }}</span>
      <span v-if="domainLabel" class="card-html-preview__domain">{{ domainLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import HtmlDocumentPreview from './HtmlDocumentPreview.vue'
import { renderCardHtml } from '../../utils/cardHtmlRenderer'

const { t } = useI18n()

const props = defineProps({
  fullName: String,
  jobTitle: String,
  company: String,
  tagline: String,
  bio: String,
  email: String,
  phone: String,
  website: String,
  address: String,
  photo: String,
  logo: String,
  backgroundImage: String,
  slug: String,
  cardUrl: String,
  domainLabel: String,
  themeColor: { type: String, default: '#e8655a' },
  template: { type: String, default: 'classic' },
  socialLinks: { type: Array, default: () => [] },
  compact: { type: Boolean, default: false },
  embedded: { type: Boolean, default: false },
})

const html = computed(() => renderCardHtml({
  fullName: props.fullName,
  jobTitle: props.jobTitle,
  company: props.company,
  tagline: props.tagline,
  bio: props.bio,
  email: props.email,
  phone: props.phone,
  website: props.website,
  address: props.address,
  photo: props.photo,
  logo: props.logo,
  backgroundImage: props.backgroundImage,
  slug: props.slug,
  cardUrl: props.cardUrl,
  themeColor: props.themeColor,
  template: props.template,
  socialLinks: props.socialLinks,
  listView: props.embedded && props.compact,
}))
</script>

<style scoped>
.card-html-preview {
  width: 100%;
}
.card-html-preview__meta {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.card-html-preview__label {
  display: block;
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.card-html-preview__url {
  display: block;
  font-size: 0.75rem;
  font-family: monospace;
  color: var(--brand);
  margin-top: 0.25rem;
  word-break: break-all;
}
.card-html-preview__domain {
  display: block;
  font-size: 0.6875rem;
  color: var(--purple);
  margin-top: 0.125rem;
}
</style>
