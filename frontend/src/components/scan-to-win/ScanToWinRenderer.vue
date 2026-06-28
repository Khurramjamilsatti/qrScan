<template>
  <div class="win-tpl" :class="`win-tpl--${template}`" :style="{ '--win-theme': themeColor }">
    <div class="win-tpl__header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <div class="win-tpl__overlay"></div>
      <img v-if="logoUrl" :src="logoUrl" alt="" class="win-tpl__logo" />
    </div>

    <div class="win-tpl__body">
      <div class="win-tpl__badge">🎁 {{ t('renderers.scanToWin.badge') }}</div>
      <h2 class="win-tpl__title">{{ name || t('renderers.scanToWin.defaultName') }}</h2>
      <p v-if="description" class="win-tpl__desc">{{ description }}</p>

      <div v-if="dateRange" class="win-tpl__dates">📅 {{ dateRange }}</div>
      <div v-if="maxPlaysPerDay" class="win-tpl__limit">{{ maxPlaysPerDay === 1 ? t('renderers.scanToWin.playsPerDay', { count: maxPlaysPerDay }) : t('renderers.scanToWin.playsPerDayPlural', { count: maxPlaysPerDay }) }}</div>

      <div v-if="prizes?.length" class="win-tpl__prizes">
        <h3 class="win-tpl__prizes-title">{{ t('renderers.scanToWin.prizePool') }}</h3>
        <div v-for="(prize, i) in prizes" :key="i" class="win-tpl__prize">
          <div v-if="prizeImg(prize)" class="win-tpl__prize-img" :style="{ backgroundImage: `url(${prizeImg(prize)})` }"></div>
          <div class="win-tpl__prize-body">
            <div class="win-tpl__prize-name">{{ prize.name }}</div>
            <div v-if="prize.description" class="win-tpl__prize-desc">{{ prize.description }}</div>
            <div class="win-tpl__prize-meta">
              <span v-if="prize.remaining != null">{{ t('renderers.scanToWin.left', { count: prize.remaining }) }}</span>
              <span v-if="prize.weight"> · {{ t('renderers.scanToWin.odds', { weight: prize.weight }) }}</span>
            </div>
          </div>
        </div>
      </div>

      <slot name="action"></slot>

      <p v-if="terms" class="win-tpl__terms">{{ terms }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'

const { t } = useI18n()

const props = defineProps({
  name: String,
  description: String,
  template: { type: String, default: 'instant' },
  startsAt: String,
  endsAt: String,
  maxPlaysPerDay: { type: Number, default: 1 },
  prizes: { type: Array, default: () => [] },
  terms: String,
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
})

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)
const headerStyle = computed(() => bgUrl.value ? { backgroundImage: `url(${bgUrl.value})` } : {})

const dateRange = computed(() => {
  if (!props.startsAt && !props.endsAt) return ''
  const fmt = (v) => { try { return new Date(v).toLocaleDateString() } catch { return v } }
  if (props.startsAt && props.endsAt) return t('renderers.scanToWin.dateRange', { start: fmt(props.startsAt), end: fmt(props.endsAt) })
  if (props.startsAt) return t('renderers.scanToWin.starts', { date: fmt(props.startsAt) })
  return t('renderers.scanToWin.ends', { date: fmt(props.endsAt) })
})

function prizeImg(prize) {
  return prize.image_path ? resolveStorageUrl(prize.image_path) : null
}
</script>

<style scoped>
.win-tpl {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e8e4f0);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
}
.win-tpl__header {
  position: relative;
  height: 4.5rem;
  background: linear-gradient(135deg, var(--win-theme), color-mix(in srgb, var(--win-theme) 45%, #e8b84a));
  background-size: cover;
}
.win-tpl__header.has-bg .win-tpl__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(26,19,51,0.15), rgba(26,19,51,0.5));
}
.win-tpl__logo {
  position: absolute;
  bottom: -1rem;
  left: 1.25rem;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.625rem;
  object-fit: cover;
  border: 3px solid var(--surface, #fff);
  z-index: 2;
}
.win-tpl__body { padding: 1.75rem 1.25rem 1.25rem; }
.win-tpl__badge {
  display: inline-block;
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  background: color-mix(in srgb, var(--win-theme) 15%, transparent);
  color: var(--win-theme);
  margin-bottom: 0.5rem;
}
.win-tpl__title { font-size: 1.25rem; font-weight: 800; color: var(--text-primary, #1a1333); line-height: 1.25; }
.win-tpl__desc { font-size: 0.8125rem; color: var(--text-secondary, #5c5470); margin-top: 0.5rem; line-height: 1.5; }
.win-tpl__dates, .win-tpl__limit { font-size: 0.75rem; color: var(--text-muted, #8b839c); margin-top: 0.5rem; }
.win-tpl__prizes { margin-top: 1.25rem; }
.win-tpl__prizes-title {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--win-theme);
  margin-bottom: 0.625rem;
}
.win-tpl__prize {
  display: flex;
  gap: 0.75rem;
  padding: 0.625rem;
  border-radius: 0.75rem;
  border: 1px solid var(--border, #e8e4f0);
  background: var(--bg-subtle, #faf8fd);
  margin-bottom: 0.5rem;
}
.win-tpl__prize-img {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  background-size: cover;
  background-position: center;
  flex-shrink: 0;
  background-color: color-mix(in srgb, var(--win-theme) 10%, #fff);
}
.win-tpl__prize-name { font-size: 0.8125rem; font-weight: 700; color: var(--text-primary, #1a1333); }
.win-tpl__prize-desc { font-size: 0.6875rem; color: var(--text-muted, #8b839c); margin-top: 0.125rem; }
.win-tpl__prize-meta { font-size: 0.625rem; color: var(--win-theme); font-weight: 600; margin-top: 0.25rem; }
.win-tpl__terms { font-size: 0.6875rem; color: var(--text-muted, #8b839c); margin-top: 1rem; line-height: 1.4; }
</style>
