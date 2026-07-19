<template>
  <div>
    <div class="dashboard-card mb-8">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h2 class="text-xl font-semibold text-slate-900 capitalize">{{ t('billing.planTitle', { plan: auth.user?.plan || 'free' }) }}</h2>
          <p class="text-slate-500 mt-1">{{ t('billing.planSubtitle') }}</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="px-4 py-2 rounded-full bg-brand-50 text-brand-700 font-semibold text-sm capitalize">
            {{ auth.user?.plan }}
          </div>
          <button
            v-if="billing.has_subscription"
            type="button"
            class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-slate-50"
            :disabled="checkoutLoading"
            @click="openPortal"
          >
            {{ t('billing.manageSubscription') }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="!billing.enabled" class="dashboard-card mb-6 border-amber-200 bg-amber-50">
      <p class="text-sm text-amber-900">{{ t('billing.notConfigured') }}</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="plan in plans"
        :key="plan.slug"
        class="dashboard-card relative"
        :class="{ 'ring-2 ring-brand-500': plan.slug === auth.user?.plan }"
      >
        <div v-if="plan.is_popular" class="absolute -top-2 right-4 px-2 py-0.5 bg-brand-500 text-white text-xs rounded-full">
          {{ t('billing.popular') }}
        </div>
        <h3 class="font-semibold text-lg">{{ plan.name }}</h3>
        <div class="text-3xl font-display my-3">
          ${{ plan.price }}<span class="text-sm text-slate-400 font-sans">{{ t('billing.perMonth') }}</span>
        </div>
        <ul class="space-y-2 mb-6">
          <li v-for="(f, i) in parseFeatures(plan.features)" :key="i" class="text-sm text-slate-600 flex items-start gap-2">
            <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ f }}
          </li>
        </ul>
        <button
          type="button"
          :disabled="isPlanDisabled(plan) || checkoutLoading"
          class="w-full py-2.5 rounded-xl font-semibold text-sm transition-all disabled:opacity-60"
          :class="planButtonClass(plan)"
          @click="handlePlanAction(plan)"
        >
          {{ planButtonLabel(plan) }}
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
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'
import { useDialog } from '../../composables/useDialog'
import { useApiError } from '../../composables/useApiError'

const PLAN_RANK = { free: 0, starter: 1, pro: 2, business: 3 }

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const dialog = useDialog()
const { firstError } = useApiError()

const plans = ref([])
const checkoutLoading = ref(false)
const billing = ref({
  enabled: false,
  has_subscription: false,
  subscription_status: null,
})

function parseFeatures(features) {
  if (Array.isArray(features)) return features
  try { return JSON.parse(features) } catch { return [] }
}

function currentRank() {
  return PLAN_RANK[auth.user?.plan || 'free'] ?? 0
}

function planRank(plan) {
  return PLAN_RANK[plan.slug] ?? 0
}

function isCurrentPlan(plan) {
  return plan.slug === auth.user?.plan
}

function isPlanDisabled(plan) {
  if (isCurrentPlan(plan)) return true
  if (plan.slug === 'free') return true
  if (!billing.value.enabled) return true
  return false
}

function planButtonClass(plan) {
  if (isCurrentPlan(plan)) return 'bg-slate-100 text-slate-400 cursor-default'
  if (plan.slug === 'free' || !billing.value.enabled) return 'bg-slate-100 text-slate-400 cursor-default'
  if (planRank(plan) > currentRank()) return 'bg-brand-500 text-white hover:bg-brand-600'
  return 'bg-slate-800 text-white hover:bg-slate-900'
}

function planButtonLabel(plan) {
  if (isCurrentPlan(plan)) return t('billing.currentPlan')
  if (plan.slug === 'free') return t('billing.freePlan')
  if (!billing.value.enabled) return t('billing.upgrade')
  if (planRank(plan) > currentRank()) return t('billing.upgrade')
  return t('billing.changePlan')
}

async function startCheckout(plan) {
  checkoutLoading.value = true
  try {
    const { data } = await api.post('/billing/checkout', { plan: plan.slug })
    if (data.checkout_url) {
      window.location.href = data.checkout_url
      return
    }
    dialog.alert({
      title: t('errors.failedToSave'),
      message: t('billing.checkoutFailed'),
      variant: 'error',
      confirmText: t('common.gotIt'),
    })
  } catch (error) {
    dialog.alert({
      title: t('errors.failedToSave'),
      message: firstError(error, 'billing.checkoutFailed'),
      variant: 'error',
      confirmText: t('common.gotIt'),
    })
  } finally {
    checkoutLoading.value = false
  }
}

async function openPortal() {
  checkoutLoading.value = true
  try {
    const { data } = await api.post('/billing/portal')
    if (data.portal_url) {
      window.location.href = data.portal_url
    }
  } catch (error) {
    dialog.alert({
      title: t('errors.failedToSave'),
      message: firstError(error, 'billing.portalFailed'),
      variant: 'error',
      confirmText: t('common.gotIt'),
    })
  } finally {
    checkoutLoading.value = false
  }
}

function handlePlanAction(plan) {
  if (isPlanDisabled(plan)) return

  if (planRank(plan) > currentRank()) {
    startCheckout(plan)
    return
  }

  if (billing.value.has_subscription) {
    openPortal()
  }
}

async function loadBillingConfig() {
  try {
    const { data } = await api.get('/billing/config')
    billing.value = data
  } catch {
    billing.value = { enabled: false, has_subscription: false, subscription_status: null }
  }
}

function handleCheckoutQuery() {
  const status = route.query.checkout
  if (!status) return

  if (status === 'success') {
    const sessionId = route.query.session_id
    if (sessionId) {
      confirmCheckout(sessionId)
    } else {
      dialog.alert({
        title: t('billing.checkoutSuccessTitle'),
        message: t('billing.checkoutSuccessMessage'),
        variant: 'info',
        confirmText: t('common.gotIt'),
      })
      auth.fetchUser()
      loadBillingConfig()
    }
  } else if (status === 'cancel') {
    dialog.alert({
      title: t('billing.checkoutCancelTitle'),
      message: t('billing.checkoutCancelMessage'),
      variant: 'info',
      confirmText: t('common.gotIt'),
    })
  }

  router.replace({ query: {} })
}

async function confirmCheckout(sessionId) {
  try {
    const { data } = await api.post('/billing/confirm', { session_id: sessionId })
    auth.updateUser({ plan: data.plan })
    await auth.fetchUser()
    await loadBillingConfig()
    dialog.alert({
      title: t('billing.checkoutSuccessTitle'),
      message: data.message || t('billing.checkoutSuccessMessage'),
      variant: 'info',
      confirmText: t('common.gotIt'),
    })
  } catch (error) {
    dialog.alert({
      title: t('errors.failedToSave'),
      message: firstError(error, 'billing.checkoutFailed'),
      variant: 'error',
      confirmText: t('common.gotIt'),
    })
  }
}

onMounted(async () => {
  const { data } = await api.get('/landing')
  plans.value = data.pricing || []
  await loadBillingConfig()
  handleCheckoutQuery()
})
</script>
