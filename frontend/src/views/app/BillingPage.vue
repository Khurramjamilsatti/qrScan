<template>
  <div>
    <div class="dashboard-card mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-slate-900 capitalize">{{ t('billing.planTitle', { plan: auth.user?.plan || 'free' }) }}</h2>
          <p class="text-slate-500 mt-1">{{ t('billing.planSubtitle') }}</p>
        </div>
        <div class="px-4 py-2 rounded-full bg-brand-50 text-brand-700 font-semibold text-sm capitalize">
          {{ auth.user?.plan }}
        </div>
      </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div v-for="plan in plans" :key="plan.slug"
        class="dashboard-card relative"
        :class="{ 'ring-2 ring-brand-500': plan.slug === auth.user?.plan }">
        <div v-if="plan.is_popular" class="absolute -top-2 right-4 px-2 py-0.5 bg-brand-500 text-white text-xs rounded-full">{{ t('billing.popular') }}</div>
        <h3 class="font-semibold text-lg">{{ plan.name }}</h3>
        <div class="text-3xl font-display my-3">${{ plan.price }}<span class="text-sm text-slate-400 font-sans">{{ t('billing.perMonth') }}</span></div>
        <ul class="space-y-2 mb-6">
          <li v-for="(f, i) in parseFeatures(plan.features)" :key="i" class="text-sm text-slate-600 flex items-start gap-2">
            <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ f }}
          </li>
        </ul>
        <button
          :disabled="plan.slug === auth.user?.plan"
          class="w-full py-2.5 rounded-xl font-semibold text-sm transition-all"
          :class="plan.slug === auth.user?.plan ? 'bg-slate-100 text-slate-400 cursor-default' : 'bg-brand-500 text-white hover:bg-brand-600'"
          @click="upgrade(plan)">
          {{ plan.slug === auth.user?.plan ? t('billing.currentPlan') : t('billing.upgrade') }}
        </button>
      </div>
    </div>

    <div class="dashboard-card mt-8 bg-brand-50 border-brand-100">
      <h3 class="font-semibold text-slate-900 mb-2">{{ t('billing.stripeTitle') }}</h3>
      <p class="text-sm text-slate-600">{{ t('billing.stripeDesc') }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'
import { useDialog } from '../../composables/useDialog'

const { t } = useI18n()
const auth = useAuthStore()
const dialog = useDialog()
const plans = ref([])

function parseFeatures(features) {
  if (Array.isArray(features)) return features
  try { return JSON.parse(features) } catch { return [] }
}

function upgrade(plan) {
  dialog.alert({
    title: t('billing.stripeComingSoonTitle'),
    message: t('billing.stripeComingSoonMessage', { planName: plan.name, price: plan.price }),
    variant: 'info',
    confirmText: t('common.gotIt'),
  })
}

onMounted(async () => {
  const { data } = await api.get('/landing')
  plans.value = data.pricing || []
})
</script>
