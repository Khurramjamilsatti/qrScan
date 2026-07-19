<template>
  <div class="template-gallery-wrap">
    <div v-if="categories?.length" class="template-gallery__cats">
      <button
        v-for="cat in categories"
        :key="cat.id"
        type="button"
        class="template-gallery__cat"
        :class="{ 'template-gallery__cat--active': activeCategory === cat.id }"
        @click="activeCategory = cat.id"
      >
        {{ cat.label }}
      </button>
    </div>
    <div class="template-gallery" :class="`template-gallery--cols-${columns}`">
      <button
        v-for="tpl in filteredTemplates"
        :key="tpl.id"
        type="button"
        class="template-gallery__card"
        :class="{
          'template-gallery__card--active': modelValue === tpl.id,
          'template-gallery__card--locked': isLocked(tpl),
        }"
        @click="selectTemplate(tpl)"
      >
        <div class="template-gallery__thumb">
          <span v-if="tpl.popular" class="template-gallery__badge">{{ t('templates.popular') }}</span>
          <span v-if="tpl.premium" class="template-gallery__badge template-gallery__badge--premium">{{ t('templates.premium') }}</span>
          <span v-if="isLocked(tpl)" class="template-gallery__lock">🔒</span>
          <img
            v-if="tpl.thumbnail"
            :src="tpl.thumbnail"
            alt=""
            class="template-gallery__img"
            loading="lazy"
          />
          <div
            v-else
            class="template-gallery__fallback"
            :style="{ background: tpl.thumbGradient || defaultGradient(tpl.id) }"
          >
            <span v-if="tpl.icon" class="template-gallery__icon">{{ tpl.icon }}</span>
          </div>
        </div>
        <div class="template-gallery__body">
          <div class="template-gallery__title">{{ tpl.label }}</div>
          <p v-if="tpl.description" class="template-gallery__desc">{{ tpl.description }}</p>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  modelValue: { type: String, default: '' },
  templates: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  columns: { type: Number, default: 3 },
  canUsePremium: { type: Boolean, default: true },
})

const emit = defineEmits(['update:modelValue', 'premium-blocked'])

function isLocked(tpl) {
  return !!tpl.premium && !props.canUsePremium
}

function selectTemplate(tpl) {
  if (isLocked(tpl)) {
    emit('premium-blocked', tpl)
    return
  }
  emit('update:modelValue', tpl.id)
}

const { t } = useI18n()
const activeCategory = ref(props.categories[0]?.id || 'all')

const filteredTemplates = computed(() => {
  if (!props.categories?.length || activeCategory.value === 'all') return props.templates
  return props.templates.filter((tpl) => tpl.category === activeCategory.value)
})

const gradients = {
  classic: 'linear-gradient(145deg, #1a1333 0%, #3d2d6b 55%, #e8655a 100%)',
  modern: 'linear-gradient(145deg, #667eea 0%, #764ba2 100%)',
  bold: 'linear-gradient(145deg, #e8655a 0%, #c0392b 100%)',
  minimal: 'linear-gradient(145deg, #f8f6fc 0%, #e8e4f0 100%)',
  landing: 'linear-gradient(145deg, #1a1333 0%, #2d4a6f 100%)',
  portfolio: 'linear-gradient(145deg, #4a5568 0%, #a0aec0 100%)',
  event: 'linear-gradient(145deg, #553c9a 0%, #b794f4 100%)',
  simple: 'linear-gradient(145deg, #faf8fd 0%, #e8e4f0 100%)',
  restaurant: 'linear-gradient(145deg, #2d5016 0%, #8fbc8f 100%)',
  product: 'linear-gradient(145deg, #1a1333 0%, #e8655a 100%)',
  pricing: 'linear-gradient(145deg, #0f172a 0%, #334155 100%)',
  team: 'linear-gradient(145deg, #4c1d95 0%, #a78bfa 100%)',
  links: 'linear-gradient(145deg, #ec4899 0%, #8b5cf6 100%)',
  resume: 'linear-gradient(145deg, #1e3a5f 0%, #64748b 100%)',
  announcement: 'linear-gradient(145deg, #dc2626 0%, #f97316 100%)',
  video: 'linear-gradient(145deg, #0f0f23 0%, #6366f1 100%)',
  formal: 'linear-gradient(145deg, #2c1810 0%, #8b7355 100%)',
  elegant: 'linear-gradient(145deg, #f5f0e8 0%, #c9a227 100%)',
}

function defaultGradient(id) {
  return gradients[id] || 'linear-gradient(145deg, #e8e4f0, #faf8fd)'
}
</script>

<style scoped>
.template-gallery-wrap { display: flex; flex-direction: column; gap: 1rem; }
.template-gallery__cats {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}
.template-gallery__cat {
  padding: 0.375rem 0.875rem;
  border-radius: 9999px;
  border: 1px solid var(--border, #e8e4f0);
  background: var(--surface, #fff);
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary, #5c5470);
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s, color 0.15s;
}
.template-gallery__cat:hover {
  border-color: color-mix(in srgb, var(--brand) 40%, var(--border));
  color: var(--text-primary, #1a1333);
}
.template-gallery__cat--active {
  background: var(--brand-muted);
  border-color: color-mix(in srgb, var(--brand) 50%, var(--border));
  color: var(--brand);
}

.template-gallery {
  display: grid;
  gap: 1rem;
  grid-template-columns: 1fr;
}
.template-gallery--cols-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
.template-gallery--cols-3 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}
@media (min-width: 720px) {
  .template-gallery--cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
@media (min-width: 900px) {
  .template-gallery--cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

.template-gallery__card {
  display: flex;
  flex-direction: column;
  text-align: left;
  padding: 0;
  border: 2px solid #e8e4f0;
  border-radius: 14px;
  background: #fff;
  cursor: pointer;
  overflow: hidden;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 1px 3px rgba(26, 19, 51, 0.04);
}
.template-gallery__card:hover {
  border-color: #c4bdb8;
  box-shadow: 0 4px 14px rgba(26, 19, 51, 0.08);
}
.template-gallery__card--active {
  border-color: #1a1333;
  border-width: 2.5px;
  box-shadow: 0 0 0 1px #1a1333;
}

.template-gallery__thumb {
  position: relative;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: #f4f2f8;
}
.template-gallery__badge {
  position: absolute;
  top: 10px;
  left: 10px;
  z-index: 2;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #fff;
  background: #1a1333;
  padding: 4px 8px;
  border-radius: 4px;
  line-height: 1;
}
.template-gallery__badge--premium {
  left: auto;
  right: 10px;
  background: linear-gradient(135deg, #c9a227, #e8655a);
}
.template-gallery__lock {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.75rem;
  background: rgba(26, 19, 51, 0.45);
  z-index: 3;
}
.template-gallery__card--locked {
  opacity: 0.85;
}
.template-gallery__card--locked:hover {
  border-color: #e8e4f0;
}
.template-gallery__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.template-gallery__fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.template-gallery__icon {
  font-size: 2rem;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.2));
}

.template-gallery__body {
  padding: 14px 14px 16px;
  background: #fff;
}
.template-gallery__title {
  font-size: 0.9375rem;
  font-weight: 700;
  color: #1a1333;
  line-height: 1.3;
  margin-bottom: 6px;
}
.template-gallery__desc {
  font-size: 0.8125rem;
  color: #6b6578;
  line-height: 1.45;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
