<template>
  <div class="card-preview" :style="{ '--theme': themeColor }">
    <div class="card-header" :class="{ 'has-bg': bgUrl }" :style="headerStyle">
      <img v-if="logoUrl" :src="logoUrl" alt="" class="card-logo" />
    </div>
    <div class="card-content">
      <div class="avatar" :style="{position:'relative',zIndex:10}">
        <img v-if="photoUrl" :src="photoUrl" alt="" class="avatar-img" />
        <span v-else class="avatar-letter">{{ initial }}</span>
      </div>
      <h3 class="card-name">{{ fullName || t('previews.yourName') }}</h3>
      <p v-if="tagline" class="card-tagline">{{ tagline }}</p>
      <p class="card-role">
        <span v-if="jobTitle">{{ jobTitle }}</span>
        <span v-if="jobTitle && company"> · </span>
        <span v-if="company">{{ company }}</span>
        <span v-if="!jobTitle && !company" class="text-slate-300">{{ t('previews.jobTitleCompany') }}</span>
      </p>
      <p v-if="bio" class="card-bio">{{ bio }}</p>
      <p v-else class="card-bio text-slate-300">{{ t('previews.bioPlaceholder') }}</p>
      <div class="contact-list">
        <div v-if="email" class="contact-item">✉ {{ email }}</div>
        <div v-else class="contact-item text-slate-300">✉ email@example.com</div>
        <div v-if="phone" class="contact-item">📞 {{ phone }}</div>
        <div v-if="website" class="contact-item">🌐 {{ cleanUrl(website) }}</div>
        <div v-if="address" class="contact-item">📍 {{ address }}</div>
      </div>
      <div v-if="socialLinks?.length" class="social-row">
        <a v-for="(s, i) in socialLinks" :key="i" :href="s.url" class="social-pill" target="_blank">
          {{ s.platform || s.url }}
        </a>
      </div>
      <div class="card-footer">
        <span class="slug">{{ cardUrl || `/card/${slug || 'your-name'}` }}</span>
        <span v-if="domainLabel" class="domain-tag">{{ domainLabel }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { resolveStorageUrl } from '../../utils/storageUrl'

const { t } = useI18n()

const props = defineProps({
  fullName: String, jobTitle: String, company: String, tagline: String, bio: String,
  email: String, phone: String, website: String, address: String,
  photo: String, logo: String, backgroundImage: String, slug: String, cardUrl: String, domainLabel: String,
  themeColor: { type: String, default: '#e8655a' },
  socialLinks: { type: Array, default: () => [] },
})

const photoUrl = computed(() => resolveStorageUrl(props.photo))
const logoUrl = computed(() => resolveStorageUrl(props.logo))
const bgUrl = computed(() => resolveStorageUrl(props.backgroundImage))

const initial = computed(() => props.fullName?.charAt(0)?.toUpperCase() || '?')

const headerStyle = computed(() => {
  if (bgUrl.value) return { backgroundImage: `url(${bgUrl.value})`, backgroundSize: 'cover', backgroundPosition: 'center' }
  return { background: `linear-gradient(135deg, ${props.themeColor}, color-mix(in srgb, ${props.themeColor} 70%, white))` }
})

function cleanUrl(url) {
  return url?.replace(/^https?:\/\//, '') || ''
}
</script>

<style scoped>
.card-preview {
  background: var(--surface);
  border-radius: 1.25rem;
  overflow: visible;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  max-width: 340px;
  margin: 0 auto;
  position: relative;
}
.card-header {
  height: 5rem;
  position: relative;
  z-index: 1;
  display: flex;
  align-items: flex-end;
  justify-content: flex-end;
  padding: 0.5rem;
  overflow: hidden;
}
.card-logo { position: relative; z-index: 3; width: 2.5rem; height: 2.5rem; object-fit: contain; background: white; border-radius: 0.5rem; padding: 0.25rem; box-shadow: var(--shadow-sm); }
.card-content { position: relative; z-index: 5; padding: 0 1.25rem 1.25rem; margin-top: -2rem; }
.avatar {
  position: relative;
  z-index: 10;
  width: 4.5rem; height: 4.5rem; border-radius: 1rem; background: white;
  box-shadow: 0 4px 16px rgba(0,0,0,0.12); display: flex; align-items: center;
  justify-content: center; overflow: hidden; border: 3px solid white;
}
.avatar-letter { font-size: 1.75rem; font-weight: 700; color: var(--theme); }
.avatar-img { width: 100%; height: 100%; object-fit: cover; }
.card-name { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-top: 0.75rem; }
.card-tagline { font-size: 0.8125rem; color: var(--theme); font-weight: 500; margin-top: 0.125rem; }
.card-role { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem; }
.card-bio { font-size: 0.8125rem; color: var(--text-secondary); margin-top: 0.75rem; line-height: 1.5; }
.contact-list { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.375rem; }
.contact-item { font-size: 0.8125rem; color: var(--text-secondary); }
.social-row { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.875rem; }
.social-pill {
  font-size: 0.6875rem; padding: 0.25rem 0.625rem;
  background: color-mix(in srgb, var(--theme) 12%, white);
  color: var(--theme); border-radius: 9999px; font-weight: 600; text-decoration: none;
}
.card-footer { margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
.slug { font-size: 0.75rem; font-family: monospace; color: var(--text-muted); display: block; }
.domain-tag { font-size: 0.6875rem; color: var(--purple); margin-top: 0.25rem; display: block; }
</style>
