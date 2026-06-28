<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('domains.title') }}</h2>
        <p class="page-sub">{{ t('domains.subtitle') }}</p>
      </div>
      <button v-if="data.can_add" @click="showAdd = true" class="btn-primary text-sm">+ {{ t('domains.addDomain') }}</button>
    </div>

    <div v-if="!data.can_use" class="upgrade-banner">
      <h3>{{ t('domains.upgradeTitle') }}</h3>
      <p>{{ t('domains.upgradeDesc') }}</p>
      <router-link to="/app/billing" class="btn-primary text-sm mt-3 inline-flex">{{ t('domains.viewPlans') }}</router-link>
    </div>

    <div v-if="showAdd" class="editor-panel mb-6">
      <h3 class="font-bold mb-4">{{ t('domains.addCustomDomain') }}</h3>
      <form @submit.prevent="addDomain" class="form-stack">
        <input v-model="newDomain" class="input-field" :placeholder="t('domains.domainPlaceholder')" required />
        <label class="toggle-label">
          <input v-model="setPrimary" type="checkbox" class="toggle-check" />
          {{ t('domains.setPrimary') }}
        </label>
        <p v-if="addError" class="error-text">{{ addError }}</p>
        <div class="form-actions">
          <button type="button" @click="showAdd = false" class="btn-secondary">{{ t('common.cancel') }}</button>
          <button type="submit" class="btn-primary">{{ t('domains.addDomainBtn') }}</button>
        </div>
      </form>
    </div>

    <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
    <div v-else-if="!data.domains?.length" class="empty-state">
      <h3>{{ t('domains.emptyTitle') }}</h3>
      <p>{{ t('domains.emptyDesc') }}</p>
    </div>
    <div v-else class="domain-grid">
      <div v-for="d in data.domains" :key="d.id" class="domain-card">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="domain-name">{{ d.domain }}</h3>
            <span class="status" :class="d.is_verified ? 'verified' : 'pending'">
              {{ d.is_verified ? t('common.verified') : t('common.pendingVerification') }}
            </span>
            <span v-if="d.is_primary" class="primary-badge">{{ t('common.primary') }}</span>
          </div>
        </div>
        <div v-if="!d.is_verified" class="dns-box">
          <p class="dns-title">{{ t('domains.dnsSetupRequired') }}</p>
          <div class="dns-record">
            <p class="dns-record-label">{{ t('domains.cnameRecord') }}</p>
            <div class="dns-row"><span>{{ t('domains.host') }}</span><code>{{ dnsFor(d).cname_host || d.domain }}</code></div>
            <div class="dns-row"><span>{{ t('domains.pointsTo') }}</span><code>{{ dnsFor(d).value }}</code></div>
          </div>
          <div class="dns-record">
            <p class="dns-record-label">{{ t('domains.txtRecord') }}</p>
            <div class="dns-row"><span>{{ t('domains.host') }}</span><code>{{ dnsFor(d).txt_host }}</code></div>
            <div class="dns-row"><span>{{ t('domains.value') }}</span><code>{{ dnsFor(d).txt_value }}</code></div>
          </div>
          <div v-if="checkStatus[d.id]" class="check-status">
            <span class="check-pill" :class="{ ok: checkStatus[d.id].checks?.txt }">TXT {{ checkStatus[d.id].checks?.txt ? '✓' : '✗' }}</span>
            <span class="check-pill" :class="{ ok: checkStatus[d.id].checks?.cname }">CNAME {{ checkStatus[d.id].checks?.cname ? '✓' : '✗' }}</span>
          </div>
          <p v-if="verifyErrors[d.id]" class="error-text">{{ verifyErrors[d.id] }}</p>
          <p v-if="verifyWarnings[d.id]" class="warn-text">{{ verifyWarnings[d.id] }}</p>
          <div class="dns-actions">
            <button type="button" @click="checkDns(d)" class="action-btn" :disabled="checkingId === d.id">
              {{ checkingId === d.id ? t('common.checking') : t('domains.checkDns') }}
            </button>
            <button type="button" @click="verify(d)" class="action-btn primary" :disabled="verifyingId === d.id">
              {{ verifyingId === d.id ? t('common.verifying') : t('domains.verifyDomain') }}
            </button>
          </div>
        </div>
        <div class="domain-actions">
          <button v-if="d.is_verified && !d.is_primary" @click="setAsPrimary(d)" class="action-btn">{{ t('domains.setPrimaryBtn') }}</button>
          <button @click="remove(d)" class="action-btn danger">{{ t('common.remove') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useDomainsStore } from '../../stores/domains'
import { useDialog } from '../../composables/useDialog'

const { t } = useI18n()
const domainsStore = useDomainsStore()
const dialog = useDialog()
const loading = ref(true)
const data = ref({ domains: [], can_use: false, can_add: false })
const showAdd = ref(false)
const newDomain = ref('')
const setPrimary = ref(true)
const addError = ref('')
const dnsInfo = reactive({})
const verifyErrors = reactive({})
const verifyWarnings = reactive({})
const checkStatus = reactive({})
const verifyingId = ref(null)
const checkingId = ref(null)

async function load() {
  const { data: res } = await api.get('/custom-domains')
  data.value = res
  for (const d of res.domains || []) {
    if (!d.is_verified && d.dns) {
      dnsInfo[d.id] = d.dns
    } else {
      delete dnsInfo[d.id]
    }
  }
  await domainsStore.fetch()
}

function dnsFor(domain) {
  return dnsInfo[domain.id] || domain.dns || {}
}

async function addDomain() {
  addError.value = ''
  try {
    const { data: res } = await api.post('/custom-domains', { domain: newDomain.value, is_primary: setPrimary.value })
    dnsInfo[res.domain.id] = res.dns
    showAdd.value = false
    newDomain.value = ''
    await load()
  } catch (e) {
    addError.value = e.response?.data?.message || t('domains.failedToAdd')
  }
}

async function checkDns(d) {
  checkingId.value = d.id
  verifyErrors[d.id] = ''
  try {
    const { data } = await api.get(`/custom-domains/${d.id}/verification-status`)
    checkStatus[d.id] = data
    if (data.message && !data.verified) {
      verifyErrors[d.id] = data.message
    }
  } catch (e) {
    verifyErrors[d.id] = e.response?.data?.message || t('domains.couldNotCheckDns')
  } finally {
    checkingId.value = null
  }
}

async function verify(d) {
  verifyingId.value = d.id
  verifyErrors[d.id] = ''
  verifyWarnings[d.id] = ''
  try {
    const { data } = await api.post(`/custom-domains/${d.id}/verify`)
    checkStatus[d.id] = { checks: data.checks, verified: true }
    if (data.warnings?.length) {
      verifyWarnings[d.id] = data.warnings.join(' ')
    }
    await load()
  } catch (e) {
    const body = e.response?.data
    verifyErrors[d.id] = body?.message || t('domains.verificationFailed')
    if (body?.checks) {
      checkStatus[d.id] = { checks: body.checks, verified: false }
    }
  } finally {
    verifyingId.value = null
  }
}

async function setAsPrimary(d) {
  await api.put(`/custom-domains/${d.id}/primary`)
  await load()
}

async function remove(d) {
  const ok = await dialog.confirm({
    title: t('domains.deleteTitle'),
    message: t('domains.deleteMessage', { domain: d.domain }),
    confirmText: t('common.remove'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/custom-domains/${d.id}`)
  await load()
}

onMounted(async () => {
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.page-header { display: flex; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.upgrade-banner {
  background: var(--purple-muted);
  border: 1px solid var(--border);
  border-radius: 1rem;
  padding: 2rem;
  margin-bottom: 1.5rem;
  text-align: center;
  color: var(--text-secondary);
}
.upgrade-banner h3 { color: var(--text-primary); font-weight: 700; margin-bottom: 0.5rem; }
.upgrade-banner p { color: var(--text-secondary); }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 1.5rem; }
.editor-panel h3 { color: var(--text-primary); }
.form-stack { display: flex; flex-direction: column; gap: 1rem; }
.form-actions { display: flex; gap: 0.75rem; }
.toggle-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-secondary); }
.toggle-check { accent-color: var(--brand); }
.domain-grid { display: flex; flex-direction: column; gap: 1rem; }
.domain-card { background: var(--surface); border: 1px solid var(--border); border-radius: 1rem; padding: 1.25rem; }
.domain-name { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); }
.status { font-size: 0.75rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 9999px; }
.status.verified { background: var(--teal-muted); color: var(--teal); }
.status.pending { background: var(--gold-muted); color: var(--gold); }
.primary-badge {
  font-size: 0.6875rem; background: var(--purple-muted); color: var(--purple);
  padding: 0.2rem 0.5rem; border-radius: 9999px; margin-left: 0.5rem;
}
.dns-box {
  margin-top: 1rem; padding: 0.875rem; background: var(--bg-subtle);
  border: 1px solid var(--border); border-radius: 0.75rem; font-size: 0.8125rem;
}
.dns-title { font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary); }
.dns-record { margin-bottom: 0.875rem; }
.dns-record-label { font-size: 0.75rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.375rem; text-transform: uppercase; letter-spacing: 0.04em; }
.dns-row { display: flex; gap: 0.5rem; margin-bottom: 0.375rem; color: var(--text-secondary); align-items: flex-start; }
.dns-row span { flex-shrink: 0; font-weight: 600; color: var(--text-muted); min-width: 4.5rem; }
.dns-row code {
  font-size: 0.75rem; word-break: break-all; color: var(--text-primary);
  background: var(--surface); padding: 0.125rem 0.375rem; border-radius: 0.25rem;
  border: 1px solid var(--border); flex: 1;
}
.check-status { display: flex; gap: 0.5rem; flex-wrap: wrap; margin: 0.75rem 0; }
.check-pill {
  font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px;
  background: color-mix(in srgb, #ef4444 12%, var(--surface)); color: #ef4444;
  border: 1px solid color-mix(in srgb, #ef4444 25%, var(--border));
}
.check-pill.ok {
  background: var(--teal-muted); color: var(--teal);
  border-color: color-mix(in srgb, var(--teal) 30%, var(--border));
}
.dns-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.75rem; }
.warn-text { color: var(--gold); font-size: 0.8125rem; margin-top: 0.5rem; }
.domain-actions { display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
.action-btn {
  font-size: 0.8125rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem;
  border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer; transition: all 0.15s;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-muted); }
.action-btn.primary { background: var(--brand); color: white; border-color: var(--brand); }
.action-btn.primary:hover { background: var(--brand-hover); color: white; }
.action-btn:disabled { opacity: 0.6; cursor: wait; }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; background: color-mix(in srgb, #ef4444 12%, var(--surface)); }
.error-text { color: #ef4444; font-size: 0.875rem; }
</style>
