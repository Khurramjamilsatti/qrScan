import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useDomainsStore = defineStore('domains', () => {
  const domains = ref([])
  const canUse = ref(false)
  const canAdd = ref(false)
  const defaultBaseUrl = ref(window.location.origin)
  const loaded = ref(false)

  const verifiedDomains = computed(() => domains.value.filter(d => d.is_verified))

  async function fetch() {
    const { data } = await api.get('/custom-domains')
    domains.value = data.domains || []
    canUse.value = data.can_use
    canAdd.value = data.can_add
    defaultBaseUrl.value = data.default_base_url || window.location.origin
    loaded.value = true
  }

  function labelFor(id) {
    if (!id) return 'Default (qrscan.digital)'
    return domains.value.find(d => d.id === id)?.domain || 'Custom domain'
  }

  function baseUrlFor(id) {
    const d = domains.value.find(x => x.id === id && x.is_verified)
    if (d) return `https://${d.domain}`
    const primary = domains.value.find(x => x.is_primary && x.is_verified)
    if (primary && canUse.value) return `https://${primary.domain}`
    return defaultBaseUrl.value.replace(/\/$/, '')
  }

  return { domains, canUse, canAdd, defaultBaseUrl, loaded, verifiedDomains, fetch, labelFor, baseUrlFor }
})
