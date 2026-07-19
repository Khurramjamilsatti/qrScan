<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('smartQr.title') }}</h2>
        <p class="page-sub">{{ t('smartQr.subtitle') }}</p>
      </div>
    </div>

    <div class="feature-grid">
      <div class="feature-card">
        <div class="feature-icon">🤖</div>
        <h3>{{ t('smartQr.features.assistant') }}</h3>
        <p>{{ t('smartQr.features.assistantDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🎯</div>
        <h3>{{ t('smartQr.features.funnels') }}</h3>
        <p>{{ t('smartQr.features.funnelsDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔀</div>
        <h3>{{ t('smartQr.features.routing') }}</h3>
        <p>{{ t('smartQr.features.routingDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>{{ t('smartQr.features.analytics') }}</h3>
        <p>{{ t('smartQr.features.analyticsDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>{{ t('smartQr.features.security') }}</h3>
        <p>{{ t('smartQr.features.securityDesc') }}</p>
      </div>
    </div>

    <div class="tabs">
      <button v-for="tab in tabs" :key="tab.id" type="button" class="tab-btn" :class="{ active: activeTab === tab.id }" @click="activeTab = tab.id">
        {{ tab.label }}
      </button>
    </div>

    <div class="tab-panel">
      <QrAssistantPanel
        v-if="activeTab === 'assistant'"
        @apply-funnel="onApplyFunnel"
        @apply-settings="onApplySettings"
      />
      <QrFunnelBuilder v-else-if="activeTab === 'funnels'" ref="funnelBuilder" />
      <div v-else-if="activeTab === 'routing'" class="info-panel">
        <p>{{ t('smartQr.routing.info') }}</p>
        <router-link to="/app/qr-codes" class="btn-primary text-sm inline-link">{{ t('smartQr.routing.openQrCodes') }}</router-link>
      </div>
      <div v-else-if="activeTab === 'security'" class="info-panel">
        <p>{{ t('smartQr.security.info') }}</p>
        <router-link to="/app/qr-codes" class="btn-primary text-sm inline-link">{{ t('smartQr.security.openQrCodes') }}</router-link>
      </div>
    </div>

    <p v-if="appliedNotice" class="applied-notice">{{ appliedNotice }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRouter } from 'vue-router'
import QrAssistantPanel from '../../components/smart-qr/QrAssistantPanel.vue'
import QrFunnelBuilder from '../../components/smart-qr/QrFunnelBuilder.vue'

const { t } = useI18n()
const router = useRouter()
const activeTab = ref('assistant')
const appliedNotice = ref('')
const funnelBuilder = ref(null)

const tabs = computed(() => [
  { id: 'assistant', label: t('smartQr.tabs.assistant') },
  { id: 'funnels', label: t('smartQr.tabs.funnels') },
  { id: 'routing', label: t('smartQr.tabs.routing') },
  { id: 'security', label: t('smartQr.tabs.security') },
])

function onApplyFunnel(template) {
  activeTab.value = 'funnels'
  appliedNotice.value = t('smartQr.assistant.templateReady', { name: template.name })
}

function onApplySettings(result) {
  appliedNotice.value = t('smartQr.assistant.settingsReady')
  router.push({
    path: '/app/qr-codes',
    query: {
      smart: '1',
      name: result.suggested_name || '',
      destination: result.destination_url || '',
      goal: result.intent?.goal || '',
    },
  })
}
</script>

<style scoped>
.page-header { margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.feature-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem; }
.feature-card { background: var(--surface); border: 1px solid var(--border); border-radius: 0.875rem; padding: 1rem; }
.feature-icon { font-size: 1.25rem; margin-bottom: 0.375rem; }
.feature-card h3 { font-size: 0.875rem; font-weight: 700; }
.feature-card p { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.4; }
.tabs { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-bottom: 1rem; }
.tab-btn { padding: 0.5rem 0.875rem; border-radius: 9999px; border: 1px solid var(--border); background: var(--bg-subtle); font-size: 0.8125rem; font-weight: 600; cursor: pointer; }
.tab-btn.active { background: var(--brand-muted); border-color: var(--brand); color: var(--brand); }
.tab-panel { min-height: 200px; }
.info-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 0.875rem; padding: 1.25rem; font-size: 0.875rem; color: var(--text-secondary); }
.inline-link { display: inline-block; margin-top: 0.75rem; text-decoration: none; }
.applied-notice { margin-top: 1rem; padding: 0.75rem 1rem; border-radius: 0.5rem; background: #dcfce7; color: #166534; font-size: 0.8125rem; }
</style>
