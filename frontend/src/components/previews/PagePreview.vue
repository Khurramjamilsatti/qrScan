<template>
  <div class="page-preview">
    <PageTemplateRenderer
      :title="title"
      :template="template"
      :content="content"
      :theme-color="themeColor"
      :logo="logo"
      :background-image="backgroundImage"
      live-preview
    />
    <div v-if="pageUrl" class="page-preview__url">
      <span class="page-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="page-preview__link">{{ pageUrl }}</span>
      <span v-if="domainLabel" class="page-preview__domain">{{ domainLabel }}</span>
    </div>
    <div class="page-preview__badge">{{ templateLabel }}</div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import PageTemplateRenderer from '../pages/PageTemplateRenderer.vue'

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
})

const templateLabel = computed(() => t(`templates.page.${props.template}.label`))
</script>

<style scoped>
.page-preview { position: relative; }
.page-preview__url {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border);
}
.page-preview__label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); }
.page-preview__link { display: block; font-size: 0.75rem; font-family: monospace; color: var(--brand); margin-top: 0.25rem; word-break: break-all; }
.page-preview__domain { display: block; font-size: 0.6875rem; color: var(--purple); margin-top: 0.125rem; }
.page-preview__badge {
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
}
</style>
