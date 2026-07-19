<template>
  <div class="image-field" :class="[`image-field--${variant}`, { 'image-field--has-image': displayUrl }]">
    <label v-if="label" class="field-label">{{ label }}</label>

    <div v-if="displayUrl" class="image-field__preview">
      <img :src="displayUrl" alt="" class="image-field__img" @error="onImgError" />
      <div class="image-field__overlay">
        <label class="image-field__change">
          <input type="file" accept="image/*" class="hidden" @change="onUpload" />
          {{ t('common.changeImage') }}
        </label>
        <button type="button" class="image-field__remove" @click="clear">✕</button>
      </div>
    </div>

    <div v-else class="image-field__dropzone">
      <label class="image-field__drop-label">
        <input type="file" accept="image/*" :multiple="multiple" class="hidden" @change="onUpload" />
        <span class="image-field__drop-icon">🖼</span>
        <span class="image-field__drop-text">{{ multiple ? t('common.uploadImages') : t('common.uploadImage') }}</span>
        <span class="image-field__drop-hint">{{ t('common.dragOrClick') }}</span>
      </label>
    </div>

    <div class="image-field__actions">
      <button type="button" class="ai-btn" :disabled="generating" @click="toggleAi">
        {{ generating ? t('common.generating') : `✨ ${t('common.aiGenerate')}` }}
      </button>
      <button v-if="!showUrlInput" type="button" class="link-btn-sm" @click="showUrlInput = true">
        {{ t('common.pasteUrl') }}
      </button>
    </div>

    <div v-if="showUrlInput" class="image-field__url-row">
      <input
        v-model="urlInput"
        type="url"
        class="input-field"
        :placeholder="t('common.imageUrlPlaceholder')"
        @keyup.enter="applyUrl"
      />
      <button type="button" class="btn-secondary text-sm" @click="applyUrl">{{ t('common.apply') }}</button>
    </div>

    <div v-if="showAi" class="ai-panel">
      <p class="ai-provider">Powered by Hugging Face · Stable Diffusion</p>
      <input v-model="prompt" class="input-field" :placeholder="aiPlaceholder" @keyup.enter="generate" />
      <button
        type="button"
        class="btn-primary text-sm py-2"
        :disabled="generating || !prompt || !aiAvailable"
        @click="generate"
      >
        {{ aiAvailable ? t('common.generateImage') : 'AI not configured' }}
      </button>
      <p v-if="!aiAvailable" class="hint">
        Add <code>HUGGINGFACE_API_TOKEN</code> to <code>backend/.env</code> and restart the API server.
      </p>
    </div>

    <p v-if="uploadError" class="error">{{ uploadError }}</p>
    <p v-if="aiError" class="error">{{ aiError }}</p>
    <p v-if="imgError" class="error">{{ t('common.imageLoadError') }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { resolveStorageUrl } from '../../utils/storageUrl'

const props = defineProps({
  modelValue: String,
  label: String,
  folder: { type: String, default: 'uploads' },
  aiContext: { type: String, default: 'qr-background' },
  aiPlaceholder: { type: String, default: 'Describe your image...' },
  variant: { type: String, default: 'card' },
  multiple: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'uploaded'])

const { t } = useI18n()
const urlInput = ref('')
const showUrlInput = ref(false)
const showAi = ref(false)
const prompt = ref('')
const generating = ref(false)
const aiError = ref('')
const uploadError = ref('')
const imgError = ref(false)
const aiAvailable = ref(false)

const displayUrl = computed(() => resolveStorageUrl(props.modelValue || ''))

watch(() => props.modelValue, () => { imgError.value = false })

onMounted(async () => {
  try {
    const { data } = await api.get('/ai/status')
    aiAvailable.value = data.configured
  } catch {}
})

function toggleAi() {
  showAi.value = !showAi.value
  aiError.value = ''
}

function clear() {
  emit('update:modelValue', '')
  urlInput.value = ''
  imgError.value = false
  uploadError.value = ''
}

function onImgError() {
  imgError.value = true
}

async function uploadFile(file) {
  const fd = new FormData()
  fd.append('image', file)
  fd.append('folder', props.folder)
  const { data } = await api.post('/ai/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  return data.path
    ? `/storage/${String(data.path).replace(/^\/+/, '')}`
    : resolveStorageUrl(data.url)
}

async function onUpload(e) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  imgError.value = false
  uploadError.value = ''
  aiError.value = ''
  try {
    if (props.multiple && files.length > 1) {
      const paths = []
      for (const file of files) {
        paths.push(await uploadFile(file))
      }
      emit('uploaded', paths)
    } else {
      const stored = await uploadFile(files[0])
      emit('update:modelValue', stored)
    }
  } catch (err) {
    uploadError.value = err.response?.data?.message || t('errors.uploadFailed')
  }
  e.target.value = ''
}

function applyUrl() {
  if (urlInput.value) {
    imgError.value = false
    emit('update:modelValue', resolveStorageUrl(urlInput.value))
    showUrlInput.value = false
    urlInput.value = ''
  }
}

async function generate() {
  if (!aiAvailable.value) {
    aiError.value = 'Add HUGGINGFACE_API_TOKEN to backend/.env and restart the server.'
    return
  }
  generating.value = true
  aiError.value = ''
  imgError.value = false
  try {
    const { data } = await api.post('/ai/generate', { prompt: prompt.value, context: props.aiContext })
    emit('update:modelValue', resolveStorageUrl(data.url))
    showAi.value = false
  } catch (e) {
    aiError.value = e.response?.data?.message || t('errors.generationFailed')
  } finally {
    generating.value = false
  }
}
</script>

<style scoped>
.field-label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
.hidden { display: none; }
.error { color: #ef4444; font-size: 0.75rem; margin: 0.25rem 0 0; }
.hint { font-size: 0.75rem; color: var(--text-muted); margin: 0; line-height: 1.5; }
.hint code { font-size: 0.6875rem; background: var(--bg-subtle); padding: 0.1rem 0.3rem; border-radius: 0.25rem; }

.image-field__preview {
  position: relative;
  border-radius: 0.875rem;
  overflow: hidden;
  border: 1px solid var(--border);
  background: var(--bg-subtle);
  margin-bottom: 0.625rem;
}
.image-field--card .image-field__preview { aspect-ratio: 16 / 10; }
.image-field--compact .image-field__preview { width: 5rem; height: 5rem; border-radius: 0.75rem; }
.image-field__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.image-field--card .image-field__img { min-height: 120px; }
.image-field__overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  padding: 0.5rem;
  background: linear-gradient(transparent 40%, rgba(0,0,0,0.55));
  opacity: 0;
  transition: opacity 0.15s;
}
.image-field__preview:hover .image-field__overlay { opacity: 1; }
.image-field__change {
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  background: rgba(255,255,255,0.2);
}
.image-field__remove {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  border: none;
  background: rgba(0,0,0,0.6);
  color: #fff;
  font-size: 0.75rem;
  cursor: pointer;
}

.image-field__dropzone {
  border: 2px dashed var(--border);
  border-radius: 0.875rem;
  background: var(--bg-subtle);
  margin-bottom: 0.625rem;
  transition: border-color 0.15s, background 0.15s;
}
.image-field__dropzone:hover { border-color: color-mix(in srgb, var(--brand) 50%, var(--border)); background: var(--brand-muted); }
.image-field__drop-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 1.5rem 1rem;
  cursor: pointer;
  text-align: center;
}
.image-field--compact .image-field__drop-label { padding: 1rem; }
.image-field__drop-icon { font-size: 1.5rem; }
.image-field__drop-text { font-size: 0.8125rem; font-weight: 600; color: var(--text-primary); }
.image-field__drop-hint { font-size: 0.6875rem; color: var(--text-muted); }

.image-field__actions { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
.ai-btn { font-size: 0.8125rem; font-weight: 500; padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 1px solid color-mix(in srgb, var(--brand) 40%, var(--border)); background: var(--brand-muted); cursor: pointer; color: var(--brand); }
.ai-btn:disabled { opacity: 0.5; }
.link-btn-sm { font-size: 0.75rem; font-weight: 600; color: var(--brand); background: none; border: none; cursor: pointer; padding: 0.25rem 0; }
.image-field__url-row { display: flex; gap: 0.5rem; margin-top: 0.5rem; align-items: center; }
.image-field__url-row .input-field { flex: 1; }

.ai-panel { margin-top: 0.75rem; padding: 0.875rem; background: var(--purple-muted, var(--brand-muted)); border: 1px solid var(--border); border-radius: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; }
.ai-provider { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--purple, var(--brand)); margin: 0; }
</style>
