<template>
  <div class="gallery-images">
    <label v-if="label" class="field-label">{{ label }}</label>

    <div v-if="modelValue.length" class="gallery-images__grid">
      <div v-for="(item, i) in modelValue" :key="i" class="gallery-images__card">
        <div class="gallery-images__thumb">
          <img
            v-if="item.image_path"
            :src="resolveStorageUrl(item.image_path)"
            alt=""
            class="gallery-images__img"
          />
          <div v-else class="gallery-images__empty">🖼</div>
          <button type="button" class="gallery-images__remove" @click="removeItem(i)">✕</button>
        </div>
        <div class="gallery-images__meta">
          <input v-model="item.caption" class="input-field input-field--sm" :placeholder="t('digitalPages.captionOptional')" />
          <input v-model="item.url" class="input-field input-field--sm" :placeholder="t('digitalPages.linkUrlOptional')" />
        </div>
      </div>
    </div>

    <label class="gallery-images__upload">
      <input type="file" accept="image/*" multiple class="hidden" @change="onMultiUpload" />
      <span class="gallery-images__upload-icon">📷</span>
      <span>{{ t('common.uploadMultipleImages') }}</span>
    </label>
    <p v-if="uploadError" class="error">{{ uploadError }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { resolveStorageUrl } from '../../utils/storageUrl'

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  label: String,
  folder: { type: String, default: 'photos' },
})

const emit = defineEmits(['update:modelValue'])

const { t } = useI18n()
const uploadError = ref('')

async function uploadFile(file) {
  const fd = new FormData()
  fd.append('image', file)
  fd.append('folder', props.folder)
  const { data } = await api.post('/ai/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
  return data.path
    ? `/storage/${String(data.path).replace(/^\/+/, '')}`
    : resolveStorageUrl(data.url)
}

async function onMultiUpload(e) {
  const files = Array.from(e.target.files || [])
  if (!files.length) return
  uploadError.value = ''
  try {
    const items = [...props.modelValue]
    for (const file of files) {
      const path = await uploadFile(file)
      items.push({ image_path: path, caption: '', url: '' })
    }
    emit('update:modelValue', items)
  } catch (err) {
    uploadError.value = err.response?.data?.message || t('errors.uploadFailed')
  }
  e.target.value = ''
}

function removeItem(index) {
  const items = [...props.modelValue]
  items.splice(index, 1)
  emit('update:modelValue', items)
}
</script>

<style scoped>
.field-label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.5rem; }
.hidden { display: none; }
.error { color: #ef4444; font-size: 0.75rem; margin-top: 0.375rem; }

.gallery-images__grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}
.gallery-images__card {
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  overflow: hidden;
  background: var(--surface);
}
.gallery-images__thumb {
  position: relative;
  aspect-ratio: 1;
  background: var(--bg-subtle);
}
.gallery-images__img { width: 100%; height: 100%; object-fit: cover; display: block; }
.gallery-images__empty {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  opacity: 0.4;
}
.gallery-images__remove {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: none;
  background: rgba(0,0,0,0.65);
  color: #fff;
  font-size: 10px;
  cursor: pointer;
}
.gallery-images__meta {
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}
.input-field--sm { font-size: 0.75rem; padding: 0.375rem 0.5rem; }

.gallery-images__upload {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border: 2px dashed var(--border);
  border-radius: 0.75rem;
  background: var(--bg-subtle);
  cursor: pointer;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  transition: border-color 0.15s, background 0.15s;
}
.gallery-images__upload:hover {
  border-color: color-mix(in srgb, var(--brand) 50%, var(--border));
  background: var(--brand-muted);
  color: var(--brand);
}
.gallery-images__upload-icon { font-size: 1.125rem; }
</style>
