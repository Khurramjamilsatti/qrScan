<template>
  <div class="badge-tpl" :class="`badge-tpl--${template}`" :style="{ '--badge-theme': themeColor }">
    <div class="badge-tpl__header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <div class="badge-tpl__overlay"></div>
      <img v-if="logoUrl" :src="logoUrl" alt="" class="badge-tpl__logo" />
    </div>

    <div class="badge-tpl__body">
      <div v-if="badgeImgUrl" class="badge-tpl__medal">
        <img :src="badgeImgUrl" alt="" />
      </div>
      <div v-else class="badge-tpl__medal badge-tpl__medal--placeholder">🏅</div>

      <p class="badge-tpl__issuer" v-if="issuerName">{{ issuerName }}</p>
      <h2 class="badge-tpl__title">{{ title || t('renderers.badge.defaultTitle') }}</h2>
      <p class="badge-tpl__recipient">{{ recipientName || t('renderers.badge.recipient') }}</p>
      <p v-if="description" class="badge-tpl__desc">{{ description }}</p>

      <div v-if="showDates && (issueDate || expiryDate)" class="badge-tpl__dates">
        <span v-if="issueDate">{{ t('renderers.badge.issued', { date: fmtDate(issueDate) }) }}</span>
        <span v-if="expiryDate"> · {{ t('renderers.badge.expires', { date: fmtDate(expiryDate) }) }}</span>
      </div>

      <div v-if="showBadgeId && badgeId" class="badge-tpl__id">ID: {{ badgeId }}</div>

      <div v-if="showSkills && skills?.length" class="badge-tpl__skills">
        <span v-for="(skill, i) in skills" :key="i" class="badge-tpl__skill">{{ skill }}</span>
      </div>

      <a
        v-if="showVerifyLink && verifyUrl"
        :href="verifyUrl"
        class="badge-tpl__verify"
        target="_blank"
        rel="noopener"
      >{{ t('renderers.badge.verifyCredential') }}</a>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'
import { formatBadgeDate } from '../../utils/digitalModules'

const { t } = useI18n()

const props = defineProps({
  title: String,
  template: { type: String, default: 'classic' },
  recipientName: String,
  issuerName: String,
  badgeId: String,
  description: String,
  skills: { type: Array, default: () => [] },
  issueDate: String,
  expiryDate: String,
  verifyUrl: String,
  settings: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#e8655a' },
  logo: String,
  backgroundImage: String,
  badgeImage: String,
})

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)
const badgeImgUrl = computed(() => props.badgeImage ? resolveStorageUrl(props.badgeImage) : null)
const headerStyle = computed(() => bgUrl.value ? { backgroundImage: `url(${bgUrl.value})` } : {})

const showSkills = computed(() => props.settings?.show_skills !== false)
const showDates = computed(() => props.settings?.show_dates !== false)
const showBadgeId = computed(() => props.settings?.show_badge_id !== false)
const showVerifyLink = computed(() => props.settings?.show_verify_link !== false)

function fmtDate(v) { return formatBadgeDate(v) }
</script>

<style scoped>
.badge-tpl {
  background: var(--surface, #fff);
  border: 1px solid var(--border, #e8e4f0);
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm, 0 2px 8px rgba(26,19,51,0.06));
}
.badge-tpl__header {
  position: relative;
  height: 4.5rem;
  background: linear-gradient(135deg, var(--badge-theme), color-mix(in srgb, var(--badge-theme) 55%, #6b4fa0));
  background-size: cover;
  background-position: center;
}
.badge-tpl__header.has-bg .badge-tpl__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(26,19,51,0.1), rgba(26,19,51,0.45));
}
.badge-tpl__logo {
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
.badge-tpl__body { padding: 1.75rem 1.25rem 1.25rem; text-align: center; }
.badge-tpl__medal {
  width: 4.5rem;
  height: 4.5rem;
  margin: 0 auto 0.75rem;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid color-mix(in srgb, var(--badge-theme) 30%, transparent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  background: color-mix(in srgb, var(--badge-theme) 10%, #fff);
}
.badge-tpl__medal img { width: 100%; height: 100%; object-fit: cover; }
.badge-tpl__issuer {
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--badge-theme);
  margin-bottom: 0.375rem;
}
.badge-tpl__title { font-size: 1.125rem; font-weight: 800; color: var(--text-primary, #1a1333); line-height: 1.3; }
.badge-tpl__recipient { font-size: 0.9375rem; font-weight: 600; color: var(--text-secondary, #5c5470); margin-top: 0.375rem; }
.badge-tpl__desc { font-size: 0.8125rem; color: var(--text-muted, #8b839c); margin-top: 0.625rem; line-height: 1.5; }
.badge-tpl__dates { font-size: 0.75rem; color: var(--text-muted, #8b839c); margin-top: 0.75rem; }
.badge-tpl__id {
  display: inline-block;
  margin-top: 0.75rem;
  font-size: 0.6875rem;
  font-family: monospace;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  background: var(--bg-subtle, #faf8fd);
  border: 1px solid var(--border, #e8e4f0);
  color: var(--text-secondary, #5c5470);
}
.badge-tpl__skills { display: flex; flex-wrap: wrap; gap: 0.375rem; justify-content: center; margin-top: 0.875rem; }
.badge-tpl__skill {
  font-size: 0.6875rem;
  font-weight: 600;
  padding: 0.25rem 0.625rem;
  border-radius: 9999px;
  background: color-mix(in srgb, var(--badge-theme) 12%, transparent);
  color: var(--badge-theme);
}
.badge-tpl__verify {
  display: inline-block;
  margin-top: 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--badge-theme);
  text-decoration: none;
}
.badge-tpl--certificate .badge-tpl__body { border-top: 4px double color-mix(in srgb, var(--badge-theme) 40%, transparent); }
.badge-tpl--modern .badge-tpl__header { height: 0.375rem; }
.badge-tpl--modern .badge-tpl__body { padding-top: 1.25rem; }
</style>
