<template>
  <div class="page-html-preview">
    <HtmlDocumentPreview
      :html="html"
      :title="title || 'Page preview'"
      :compact="compact"
      :embedded="embedded"
      :scrollable="embedded && compact"
    />
    <div v-if="pageUrl && !compact" class="page-html-preview__meta">
      <span class="page-html-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="page-html-preview__url">{{ pageUrl }}</span>
      <span v-if="domainLabel" class="page-html-preview__domain">{{ domainLabel }}</span>
    </div>
    <div v-if="!compact" class="page-html-preview__badge">{{ templateLabel }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import HtmlDocumentPreview from './HtmlDocumentPreview.vue'
import { renderPageHtml } from '../../utils/pageHtmlRenderer'

const { t } = useI18n()

const props = defineProps({
  title: String,
  template: { type: String, default: 'landing' },
  content: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
  pageUrl: String,
  domainLabel: String,
  compact: { type: Boolean, default: false },
  embedded: { type: Boolean, default: false },
})

const html = computed(() => renderPageHtml({
  title: props.title,
  template: props.template,
  content: props.content,
  themeColor: props.themeColor,
  logo: props.logo,
  backgroundImage: props.backgroundImage,
  listView: props.embedded && props.compact,
}))

const templateLabel = computed(() => t(`templates.page.${props.template}.label`))
</script>

<style scoped>
.page-html-preview {
  position: relative;
  width: 100%;
}
.page-html-preview__meta {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.page-html-preview__label {
  display: block;
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.page-html-preview__url {
  display: block;
  font-size: 0.75rem;
  font-family: monospace;
  color: var(--brand);
  margin-top: 0.25rem;
  word-break: break-all;
}
.page-html-preview__domain {
  display: block;
  font-size: 0.6875rem;
  color: var(--purple);
  margin-top: 0.125rem;
}
.page-html-preview__badge {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  font-size: 0.5625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.2rem 0.5rem;
  border-radius: 9999px;
  background: var(--purple-muted);
  color: var(--purple);
  border: 1px solid color-mix(in srgb, var(--purple) 25%, transparent);
  z-index: 2;
}
</style>
