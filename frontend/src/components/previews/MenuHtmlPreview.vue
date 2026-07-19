<template>
  <div class="menu-html-preview">
    <HtmlDocumentPreview
      :html="html"
      :title="name || 'Menu preview'"
      :compact="compact"
      :embedded="embedded"
      :expandable="expandable"
      :scrollable="embedded && compact"
      :contain-scroll="expandable && !embedded"
    />
    <div v-if="menuUrl && !compact && !publicView && !embedded" class="menu-html-preview__meta">
      <span class="menu-html-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="menu-html-preview__url">{{ menuUrl }}</span>
      <span v-if="domainLabel" class="menu-html-preview__domain">{{ domainLabel }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import HtmlDocumentPreview from './HtmlDocumentPreview.vue'
import { renderMenuHtml } from '../../utils/menuHtmlRenderer'

const { t } = useI18n()

const props = defineProps({
  name: String,
  description: String,
  logo: String,
  backgroundImage: String,
  themeColor: { type: String, default: '#e8655a' },
  currency: { type: String, default: 'USD' },
  location: String,
  phone: String,
  hours: String,
  sections: { type: Array, default: () => [] },
  slug: String,
  menuUrl: String,
  domainLabel: String,
  template: { type: String, default: 'classic' },
  compact: { type: Boolean, default: false },
  embedded: { type: Boolean, default: false },
  expandable: { type: Boolean, default: false },
  livePreview: { type: Boolean, default: false },
  publicView: { type: Boolean, default: false },
})

const html = computed(() => renderMenuHtml({
  name: props.name,
  description: props.description,
  logo: props.logo,
  backgroundImage: props.backgroundImage,
  themeColor: props.themeColor,
  currency: props.currency,
  location: props.location,
  phone: props.phone,
  hours: props.hours,
  sections: props.sections,
  slug: props.slug,
  menuUrl: props.menuUrl,
  template: props.template,
  livePreview: props.livePreview,
  listView: props.embedded && props.compact,
  publicView: props.publicView,
}))
</script>

<style scoped>
.menu-html-preview { width: 100%; }
.menu-html-preview__meta {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.menu-html-preview__label {
  display: block;
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.menu-html-preview__url {
  display: block;
  font-size: 0.75rem;
  font-family: monospace;
  color: var(--brand);
  margin-top: 0.25rem;
  word-break: break-all;
}
.menu-html-preview__domain {
  display: block;
  font-size: 0.6875rem;
  color: var(--purple);
  margin-top: 0.125rem;
}
</style>
