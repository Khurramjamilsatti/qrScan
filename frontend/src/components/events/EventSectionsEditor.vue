<template>
  <div class="sections-editor">
    <p class="sections-editor__intro">{{ t('digitalEvents.sectionsIntro') }}</p>

    <div class="sections-chips">
      <button
        v-for="section in sections"
        :key="section.id"
        type="button"
        class="section-chip"
        :class="{ 'section-chip--on': content[section.id]?.enabled }"
        @click="toggleSection(section.id)"
      >
        <span class="section-chip__icon">{{ section.icon }}</span>
        <span class="section-chip__label">{{ sectionLabel(section.id, t) }}</span>
      </button>
    </div>

    <div class="sections-list">
      <div
        v-for="section in sections"
        :key="section.id"
        class="section-card"
        :class="{
          'section-card--on': content[section.id]?.enabled,
          'section-card--open': openId === section.id,
        }"
      >
        <button type="button" class="section-card__head" @click="toggleOpen(section.id)">
          <span class="section-card__title">
            <span class="section-card__icon">{{ section.icon }}</span>
            {{ sectionLabel(section.id, t) }}
          </span>
          <span class="section-card__meta">
            <span class="section-card__status" :class="{ on: content[section.id]?.enabled }">
              {{ content[section.id]?.enabled ? t('digitalEvents.sectionOn') : t('digitalEvents.sectionOff') }}
            </span>
            <span class="section-card__chevron">{{ openId === section.id ? '▾' : '▸' }}</span>
          </span>
        </button>

        <div v-show="openId === section.id" class="section-card__body">
          <label class="section-enable">
            <input v-model="content[section.id].enabled" type="checkbox" />
            {{ t('digitalEvents.showSectionOnPage') }}
          </label>

          <template v-if="content[section.id].enabled">
            <div v-if="section.id === 'countdown'" class="field-stack">
              <div class="form-group">
                <label>{{ t('digitalEvents.countdownTarget') }}</label>
                <input v-model="content.countdown.target" type="datetime-local" class="input-field" />
                <p class="hint-text">{{ t('digitalEvents.countdownHint') }}</p>
              </div>
            </div>

            <template v-if="section.id === 'schedule'">
              <div class="form-group">
                <label>{{ t('digitalEvents.sectionTitle') }}</label>
                <input v-model="content.schedule.title" class="input-field" />
              </div>
              <div v-for="(item, idx) in content.schedule.items" :key="idx" class="repeat-card">
                <div class="repeat-card__head">
                  <span class="repeat-card__index">{{ idx + 1 }}</span>
                  <button type="button" class="remove-btn" @click="content.schedule.items.splice(idx, 1)">{{ t('common.remove') }}</button>
                </div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('common.name') }}</label><input v-model="item.name" class="input-field" :placeholder="t('digitalEvents.scheduleNamePlaceholder')" /></div>
                  <div class="form-group"><label>{{ t('digitalEvents.time') }}</label><input v-model="item.time" class="input-field" placeholder="4:00 PM" /></div>
                </div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalEvents.date') }}</label><input v-model="item.date" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.location') }}</label><input v-model="item.location" class="input-field" /></div>
                </div>
                <div class="form-group"><label>{{ t('common.description') }}</label><input v-model="item.description" class="input-field" /></div>
              </div>
              <button type="button" class="add-card-btn" @click="content.schedule.items.push({ name: '', date: '', time: '', location: '', description: '' })">
                + {{ t('digitalEvents.addScheduleItem') }}
              </button>
            </template>

            <template v-if="section.id === 'location'">
              <div class="form-group"><label>{{ t('digitalPages.location') }}</label><textarea v-model="content.location.address" class="input-field" rows="3" :placeholder="t('digitalEvents.addressPlaceholder')" /></div>
              <div class="form-group"><label>{{ t('digitalEvents.mapsUrl') }}</label><input v-model="content.location.maps_url" class="input-field" placeholder="https://maps.google.com/..." /></div>
            </template>

            <template v-if="section.id === 'rsvp'">
              <div class="form-group"><label>{{ t('digitalEvents.rsvpUrl') }}</label><input v-model="content.rsvp.url" class="input-field" placeholder="https://..." /></div>
              <div class="form-row">
                <div class="form-group"><label>{{ t('common.email') }}</label><input v-model="content.rsvp.email" type="email" class="input-field" /></div>
                <div class="form-group"><label>{{ t('common.phone') }}</label><input v-model="content.rsvp.phone" class="input-field" /></div>
              </div>
              <div class="form-row">
                <div class="form-group"><label>{{ t('digitalEvents.rsvpDeadline') }}</label><input v-model="content.rsvp.deadline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalEvents.rsvpNote') }}</label><input v-model="content.rsvp.note" class="input-field" /></div>
              </div>
            </template>

            <template v-if="section.id === 'calendar'">
              <p class="hint-text">{{ t('digitalEvents.calendarHint') }}</p>
            </template>

            <template v-if="section.id === 'registry'">
              <div v-for="(item, idx) in content.registry.items" :key="idx" class="repeat-card repeat-card--inline">
                <div class="form-group"><label>{{ t('common.label') }}</label><input v-model="item.label" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalEvents.linkUrl') }}</label><input v-model="item.url" class="input-field" /></div>
                <button type="button" class="remove-btn block" @click="content.registry.items.splice(idx, 1)">{{ t('common.remove') }}</button>
              </div>
              <button type="button" class="add-card-btn" @click="content.registry.items.push({ label: '', url: '' })">+ {{ t('digitalEvents.addRegistryLink') }}</button>
            </template>

            <template v-if="section.id === 'gallery'">
              <GalleryImagesField v-model="content.gallery.items" :label="t('digitalEvents.galleryPhotos')" folder="photos" />
            </template>

            <template v-if="section.id === 'guestbook'">
              <div v-for="(msg, idx) in content.guestbook.messages" :key="idx" class="repeat-card">
                <div class="repeat-card__head">
                  <span class="repeat-card__index">{{ idx + 1 }}</span>
                  <button type="button" class="remove-btn" @click="content.guestbook.messages.splice(idx, 1)">{{ t('common.remove') }}</button>
                </div>
                <div class="form-group"><label>{{ t('common.name') }}</label><input v-model="msg.name" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalEvents.message') }}</label><textarea v-model="msg.message" class="input-field" rows="2" /></div>
              </div>
              <button type="button" class="add-card-btn" @click="content.guestbook.messages.push({ name: '', message: '' })">+ {{ t('digitalEvents.addMessage') }}</button>
            </template>

            <template v-if="section.id === 'livestream'">
              <div class="form-group"><label>{{ t('digitalEvents.livestreamUrl') }}</label><input v-model="content.livestream.url" class="input-field" placeholder="https://youtube.com/live/..." /></div>
            </template>

            <template v-if="section.id === 'video'">
              <div class="form-group"><label>{{ t('digitalEvents.videoUrl') }}</label><input v-model="content.video.url" class="input-field" placeholder="YouTube or Vimeo URL" /></div>
            </template>

            <template v-if="section.id === 'gift'">
              <div class="form-group"><label>{{ t('digitalEvents.giftMessage') }}</label><textarea v-model="content.gift.message" class="input-field" rows="2" /></div>
              <div class="form-group"><label>{{ t('digitalEvents.cashGiftLink') }}</label><input v-model="content.gift.cash_link" class="input-field" placeholder="https://..." /></div>
              <div class="form-group"><label>{{ t('digitalEvents.revealText') }}</label><input v-model="content.gift.reveal_text" class="input-field" /></div>
            </template>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import GalleryImagesField from '../ui/GalleryImagesField.vue'
import { sectionLabel } from '../../utils/eventSections'

const props = defineProps({
  content: { type: Object, required: true },
  sections: { type: Array, required: true },
})

const { t } = useI18n()
const openId = ref('schedule')

function toggleOpen(id) {
  openId.value = openId.value === id ? null : id
}

function toggleSection(id) {
  if (!props.content[id]) return
  props.content[id].enabled = !props.content[id].enabled
  if (props.content[id].enabled) openId.value = id
}
</script>

<style scoped>
.sections-editor__intro {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  line-height: 1.5;
  margin-bottom: 1rem;
}
.sections-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}
.section-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.65rem;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: var(--surface);
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.15s;
}
.section-chip:hover { border-color: color-mix(in srgb, var(--brand) 40%, var(--border)); }
.section-chip--on {
  background: var(--brand-muted);
  border-color: color-mix(in srgb, var(--brand) 35%, var(--border));
  color: var(--brand);
}
.section-chip__icon { font-size: 0.875rem; }
.sections-list { display: flex; flex-direction: column; gap: 0.625rem; }
.section-card {
  border: 1px solid var(--border);
  border-radius: 0.875rem;
  background: var(--surface);
  overflow: hidden;
  transition: border-color 0.15s, box-shadow 0.15s;
}
.section-card--on { border-color: color-mix(in srgb, var(--brand) 25%, var(--border)); }
.section-card--open { box-shadow: var(--shadow-sm); }
.section-card__head {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: none;
  background: var(--bg-subtle);
  cursor: pointer;
  text-align: start;
}
.section-card__title {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--text-primary);
}
.section-card__icon { font-size: 1rem; }
.section-card__meta { display: inline-flex; align-items: center; gap: 0.5rem; }
.section-card__status {
  font-size: 0.625rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--text-muted);
  padding: 0.15rem 0.45rem;
  border-radius: 999px;
  background: var(--bg-page);
}
.section-card__status.on { color: var(--brand); background: var(--brand-muted); }
.section-card__chevron { color: var(--text-muted); font-size: 0.75rem; }
.section-card__body {
  padding: 0.875rem 1rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  border-top: 1px solid var(--border);
}
.section-enable {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
}
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
@media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
.hint-text { font-size: 0.75rem; color: var(--text-muted); line-height: 1.4; }
.repeat-card {
  padding: 0.75rem;
  border-radius: 0.625rem;
  border: 1px dashed var(--border);
  background: var(--bg-subtle);
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}
.repeat-card__head { display: flex; justify-content: space-between; align-items: center; }
.repeat-card__index {
  font-size: 0.6875rem;
  font-weight: 800;
  color: var(--brand);
  text-transform: uppercase;
}
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.75rem; font-weight: 600; }
.remove-btn.block { text-align: start; padding: 0; }
.add-card-btn {
  width: 100%;
  padding: 0.75rem;
  border: 2px dashed var(--border);
  border-radius: 0.625rem;
  background: transparent;
  color: var(--text-secondary);
  font-size: 0.8125rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
}
.add-card-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-muted); }
</style>
