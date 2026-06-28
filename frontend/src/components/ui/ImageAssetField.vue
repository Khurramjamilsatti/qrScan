<template>
  <div class="image-field">
    <label v-if="label" class="field-label">{{ label }}</label>
    <div v-if="displayUrl" class="preview-thumb">
      <img :src="displayUrl" alt="Preview" @error="onImgError" />
      <button type="button" class="remove-btn" @click="clear">✕</button>
    </div>
    <div class="actions">
      <label class="upload-btn">
        <input type="file" accept="image/*" class="hidden" @change="onUpload" />
        {{ t('common.uploadImage') }}
      </label>
      <button type="button" class="ai-btn" :disabled="generating" @click="toggleAi">
        {{ generating ? t('common.generating') : `✨ ${t('common.aiGenerate')}` }}
      </button>
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
      <p v-if="aiError" class="error">{{ aiError }}</p>
      <p v-if="!aiAvailable" class="hint">
        Add <code>HUGGINGFACE_API_TOKEN</code> to <code>backend/.env</code> and restart the API server to enable AI generation.
      </p>
    </div>
    <input v-model="urlInput" type="input" class="input-field mt-2" placeholder="Or paste image URL..." @change="applyUrl" />
    <p v-if="imgError" class="error">Could not load image preview — try re-uploading.</p>
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
})
const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()
const urlInput = ref(props.modelValue || '')
const showAi = ref(false)
const prompt = ref('')
const generating = ref(false)
const aiError = ref('')
const imgError = ref(false)
const aiAvailable = ref(false)

const displayUrl = computed(() => resolveStorageUrl(props.modelValue || ''))

watch(() => props.modelValue, (v) => { urlInput.value = v || '' })

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
}

function onImgError() {
  imgError.value = true
}

async function onUpload(e) {
  const file = e.target.files?.[0]
  if (!file) return
  imgError.value = false
  const fd = new FormData()
  fd.append('image', file)
  fd.append('folder', props.folder)
  try {
    const { data } = await api.post('/ai/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    const url = resolveStorageUrl(data.url)
    emit('update:modelValue', url)
    urlInput.value = url
  } catch (err) {
    aiError.value = err.response?.data?.message || t('errors.uploadFailed')
  }
  e.target.value = ''
}

function applyUrl() {
  if (urlInput.value) {
    imgError.value = false
    emit('update:modelValue', resolveStorageUrl(urlInput.value))
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
    const url = resolveStorageUrl(data.url)
    emit('update:modelValue', url)
    urlInput.value = url
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
.preview-thumb { position: relative; width: 80px; height: 80px; border-radius: 0.75rem; overflow: hidden; margin-bottom: 0.5rem; border: 1px solid var(--border); }
.preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
.remove-btn { position: absolute; top: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%; background: rgba(0,0,0,0.6); color: white; border: none; font-size: 10px; cursor: pointer; }
.actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.upload-btn, .ai-btn { font-size: 0.8125rem; font-weight: 500; padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: var(--bg-subtle); cursor: pointer; color: var(--text-primary); }
.ai-btn { border-color: color-mix(in srgb, var(--brand) 40%, var(--border)); color: var(--brand); background: var(--brand-muted); }
.ai-btn:disabled { opacity: 0.5; }
.ai-panel { margin-top: 0.75rem; padding: 0.875rem; background: var(--purple-muted, var(--brand-muted)); border: 1px solid var(--border); border-radius: 0.75rem; display: flex; flex-direction: column; gap: 0.5rem; }
.ai-provider { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--purple, var(--brand)); margin: 0; }
.hidden { display: none; }
.error { color: #ef4444; font-size: 0.75rem; margin: 0; }
.hint { font-size: 0.75rem; color: var(--text-muted); margin: 0; line-height: 1.5; }
.hint code { font-size: 0.6875rem; background: var(--bg-subtle); padding: 0.1rem 0.3rem; border-radius: 0.25rem; }
</style>
