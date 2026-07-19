<template>
  <div class="form-preview" :class="{ 'form-preview--compact': compact }" :style="previewStyle">
    <div v-if="resolvedHeaderImage" class="form-preview__header-img">
      <img :src="resolvedHeaderImage" alt="" />
    </div>
    <div class="form-preview__card form-surface">
      <div v-if="resolvedLogo" class="form-preview__logo">
        <img :src="resolvedLogo" alt="" />
      </div>
      <h2 v-if="!compact" class="form-preview__title">{{ title || t('forms.untitledForm') }}</h2>
      <p v-if="description" class="form-preview__desc">{{ description }}</p>

      <div v-if="settings?.collect_email" class="form-preview__email">
        <label class="field-label">{{ t('forms.respondentEmail') }} <span class="required-mark">*</span></label>
        <input type="email" class="input-field" :placeholder="t('forms.emailPlaceholder')" disabled />
      </div>

      <div v-if="settings?.show_progress_bar && inputFieldCount > 1" class="progress-bar">
        <div class="progress-bar__fill" :style="{ width: '0%' }"></div>
      </div>

      <FormFieldRenderer
        v-for="field in displayFields"
        :key="field.id"
        :field="field"
        :model-value="previewResponses[field.id]"
        variant="light"
        disabled
      />

      <p v-if="extraFieldCount > 0" class="more-fields">+{{ extraFieldCount }} {{ t('forms.moreQuestions') }}</p>

      <button v-if="!compact" type="button" class="submit-btn" :style="{ background: themeColor }" disabled>
        {{ t('forms.submit') }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import FormFieldRenderer from '../forms/FormFieldRenderer.vue'
import { resolveStorageUrl } from '../../utils/storageUrl'
import { isInputField, emptyResponses } from '../../utils/formFieldTypes'

const props = defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  fields: { type: Array, default: () => [] },
  settings: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#673ab7' },
  backgroundColor: { type: String, default: '#f3f0ff' },
  headerImage: { type: String, default: '' },
  logo: { type: String, default: '' },
  backgroundImage: { type: String, default: '' },
  compact: { type: Boolean, default: false },
})

const { t } = useI18n()

const displayFields = computed(() => {
  const fields = props.fields || []
  if (!props.compact) return fields
  return fields.slice(0, 2)
})

const extraFieldCount = computed(() => {
  if (!props.compact) return 0
  return Math.max(0, (props.fields || []).length - displayFields.value.length)
})

const previewResponses = computed(() => emptyResponses(props.fields))
const inputFieldCount = computed(() => (props.fields || []).filter(f => isInputField(f.type)).length)

const resolvedHeaderImage = computed(() => resolveStorageUrl(props.headerImage))
const resolvedLogo = computed(() => resolveStorageUrl(props.logo))
const resolvedBackgroundImage = computed(() => resolveStorageUrl(props.backgroundImage))

const previewStyle = computed(() => ({
  '--form-theme': props.themeColor || '#673ab7',
  '--form-bg': props.backgroundColor || '#f3f0ff',
  backgroundColor: props.backgroundColor || '#f3f0ff',
  backgroundImage: resolvedBackgroundImage.value ? `url(${resolvedBackgroundImage.value})` : undefined,
}))
</script>

<style scoped>
.form-preview {
  border-radius: 0.75rem;
  overflow: hidden;
  min-height: 200px;
  background-size: cover;
  background-position: center;
}
.form-preview__header-img img { width: 100%; height: 120px; object-fit: cover; display: block; }
.form-preview__card {
  background: #ffffff;
  margin: 0.75rem;
  padding: 1.25rem;
  border-radius: 0.75rem;
  border-top: 6px solid var(--form-theme);
  box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.form-surface {
  --text-primary: #1a1333;
  --text-secondary: #6b7280;
  --text-muted: #9ca3af;
  --border: #e5e7eb;
  --bg-subtle: #f9fafb;
  --surface: #ffffff;
  color: #1a1333;
}
.form-preview__logo img { max-height: 48px; margin-bottom: 0.75rem; display: block; }
.form-preview__title { font-size: 1.375rem; font-weight: 700; color: #1a1333; margin-bottom: 0.5rem; }
.form-preview__desc { font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; white-space: pre-wrap; }
.form-preview__email { margin-bottom: 1rem; }
.field-label { display: block; font-size: 0.875rem; font-weight: 600; color: #1a1333; margin-bottom: 0.375rem; }
.required-mark { color: #ef4444; }
.progress-bar { height: 4px; background: #e5e7eb; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; }
.progress-bar__fill { height: 100%; background: var(--form-theme); border-radius: 2px; }
.submit-btn {
  margin-top: 1rem; padding: 0.625rem 1.5rem; border: none; border-radius: 0.375rem;
  color: white; font-weight: 600; font-size: 0.875rem; opacity: 0.7; cursor: not-allowed;
}
.form-preview--compact {
  min-height: 0;
  border-radius: 0;
}
.form-preview--compact .form-preview__header-img img { height: 72px; }
.form-preview--compact .form-preview__card {
  margin: 0.5rem;
  padding: 0.875rem;
}
.form-preview--compact .form-preview__title {
  font-size: 1rem;
  margin-bottom: 0.25rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.form-preview--compact .form-preview__desc {
  font-size: 0.75rem;
  margin-bottom: 0.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.form-preview--compact .form-preview__logo img { max-height: 32px; margin-bottom: 0.5rem; }
.form-preview--compact :deep(.form-field) { margin-bottom: 0.625rem; }
.form-preview--compact :deep(.field-label) { font-size: 0.75rem; }
.form-preview--compact :deep(.input-field) { font-size: 0.75rem; padding: 0.375rem 0.5rem; }
.more-fields {
  font-size: 0.6875rem;
  font-weight: 600;
  color: #6b7280;
  text-align: center;
  margin-top: 0.25rem;
}
</style>
