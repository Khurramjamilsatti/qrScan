<template>
  <div class="menu-preview" :style="{ '--menu-theme': themeColor }">
    <div class="menu-preview__header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <div class="menu-preview__overlay"></div>
      <img v-if="logoUrl" :src="logoUrl" alt="" class="menu-preview__logo" />
      <div class="menu-preview__brand">
        <h3>{{ name || t('previews.menuName') }}</h3>
        <p v-if="description">{{ description }}</p>
      </div>
    </div>

    <div v-if="location || phone || hours" class="menu-preview__info">
      <span v-if="location">📍 {{ location }}</span>
      <span v-if="phone">📞 {{ phone }}</span>
      <span v-if="hours">🕐 {{ hours }}</span>
    </div>

    <div class="menu-preview__sections">
      <div v-for="(section, si) in sections" :key="si" class="menu-section">
        <h4 class="menu-section__title">{{ section.name || t('previews.section') }}</h4>
        <div v-for="(item, ii) in section.items || []" :key="ii" class="menu-item">
          <div v-if="itemImg(item)" class="menu-item__img" :style="{ backgroundImage: `url(${itemImg(item)})` }"></div>
          <div class="menu-item__body">
            <div class="menu-item__head">
              <span class="menu-item__name">{{ item.name }}</span>
              <span v-if="item.price" class="menu-item__price">{{ fmt(item.price) }}</span>
            </div>
            <p v-if="item.description" class="menu-item__desc">{{ item.description }}</p>
            <div v-if="item.tags?.length" class="menu-item__tags">
              <span v-for="tag in item.tags" :key="tag" class="menu-tag">{{ dietaryLabel(tag) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="menuUrl" class="menu-preview__url">
      <span class="menu-preview__label">{{ t('common.publicUrl') }}</span>
      <span class="menu-preview__link">{{ menuUrl }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'
import { formatPrice } from '../../utils/pageTemplates'

const { t } = useI18n()

function dietaryLabel(tag) {
  const key = `dietary.${tag.replace(/-([a-z])/g, (_, c) => c.toUpperCase())}`
  const translated = t(key)
  return translated !== key ? translated : tag
}

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
  menuUrl: String,
})

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)
const headerStyle = computed(() => bgUrl.value ? { backgroundImage: `url(${bgUrl.value})` } : {})
function itemImg(item) {
  return item.image_path ? resolveStorageUrl(item.image_path) : null
}
function fmt(price) {
  return formatPrice(price, props.currency)
}
</script>

<style scoped>
.menu-preview {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.menu-preview__header {
  position: relative;
  padding: 1.25rem;
  min-height: 5rem;
  background: linear-gradient(135deg, var(--menu-theme), color-mix(in srgb, var(--menu-theme) 55%, #6b4fa0));
  background-size: cover;
  background-position: center;
  color: #fff;
}
.menu-preview__header.has-bg .menu-preview__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(26,19,51,0.2), rgba(26,19,51,0.55));
}
.menu-preview__logo {
  position: relative;
  z-index: 1;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.625rem;
  object-fit: cover;
  border: 2px solid rgba(255,255,255,0.8);
  margin-bottom: 0.5rem;
}
.menu-preview__brand { position: relative; z-index: 1; }
.menu-preview__brand h3 { font-size: 1rem; font-weight: 800; }
.menu-preview__brand p { font-size: 0.75rem; opacity: 0.9; margin-top: 0.125rem; }
.menu-preview__info {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
  padding: 0.625rem 1rem;
  font-size: 0.6875rem;
  color: var(--text-muted);
  background: var(--bg-subtle);
  border-bottom: 1px solid var(--border);
}
.menu-preview__sections { padding: 0.75rem; display: flex; flex-direction: column; gap: 1rem; max-height: 22rem; overflow-y: auto; }
.menu-section__title {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--menu-theme);
  padding-bottom: 0.375rem;
  border-bottom: 2px solid color-mix(in srgb, var(--menu-theme) 25%, var(--border));
  margin-bottom: 0.5rem;
}
.menu-item {
  display: flex;
  gap: 0.625rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid var(--border);
}
.menu-item:last-child { border-bottom: none; }
.menu-item__img {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.5rem;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
  background-color: var(--bg-subtle);
}
.menu-item__body { flex: 1; min-width: 0; }
.menu-item__head { display: flex; justify-content: space-between; gap: 0.5rem; align-items: baseline; }
.menu-item__name { font-size: 0.8125rem; font-weight: 700; color: var(--text-primary); }
.menu-item__price { font-size: 0.8125rem; font-weight: 700; color: var(--menu-theme); white-space: nowrap; }
.menu-item__desc { font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.125rem; line-height: 1.4; }
.menu-item__tags { display: flex; flex-wrap: wrap; gap: 0.25rem; margin-top: 0.25rem; }
.menu-tag {
  font-size: 0.5625rem;
  font-weight: 600;
  text-transform: uppercase;
  padding: 0.1rem 0.35rem;
  border-radius: 0.25rem;
  background: var(--purple-muted);
  color: var(--purple);
}
.menu-preview__url { padding: 0.75rem 1rem; border-top: 1px solid var(--border); }
.menu-preview__label { display: block; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); }
.menu-preview__link { display: block; font-size: 0.75rem; font-family: monospace; color: var(--brand); margin-top: 0.25rem; word-break: break-all; }
</style>
