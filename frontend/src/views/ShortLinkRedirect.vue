<template>
  <div class="redirect-page">
    <p>{{ t('common.redirecting') }}</p>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()

onMounted(() => {
  const slug = encodeURIComponent(route.params.slug)
  window.location.replace(`/api/r/${slug}`)
})
</script>

<style scoped>
.redirect-page {
  min-height: 50vh;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  font-size: 0.875rem;
}
</style>
