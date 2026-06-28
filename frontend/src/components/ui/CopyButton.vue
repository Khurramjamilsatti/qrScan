<template>
  <button type="button" @click="doCopy" class="copy-btn" :class="{ copied }">
    <svg v-if="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    <svg v-else class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
    <span>{{ copied ? t('common.copied') : displayLabel }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useCopy } from '../../composables/useCopy'

const props = defineProps({ text: String, label: { type: String, default: undefined } })
const { t } = useI18n()
const { copied, copy } = useCopy()

const displayLabel = computed(() => props.label ?? t('common.copy'))

function doCopy() {
  if (props.text) copy(props.text)
}
</script>

<style scoped>
.copy-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  font-size: 0.8125rem;
  font-weight: 500;
  color: #64748b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  transition: all 0.2s;
  cursor: pointer;
}
.copy-btn:hover { border-color: #10b981; color: #10b981; }
.copy-btn.copied { border-color: #10b981; color: #10b981; background: #ecfdf5; }
</style>
