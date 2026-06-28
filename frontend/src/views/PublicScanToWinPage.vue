<template>
  <div class="public-page public-page-light" :style="pageStyle">
    <div class="page-bg" aria-hidden="true"></div>
    <header class="page-header">
      <PublicBrandHeader />
      <ThemeToggle />
    </header>

    <div v-if="loading" class="page-center">
      <div class="loader-card">
        <div class="skeleton skeleton-header"></div>
        <div class="skeleton skeleton-line line-wide"></div>
      </div>
    </div>

    <div v-else-if="errorType === 'unpublished'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔒</div>
        <h2>{{ t('scanToWin.notPublishedTitle') }}</h2>
        <p>{{ t('scanToWin.notPublishedDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <div v-else-if="errorType === 'notfound'" class="page-center">
      <div class="state-card">
        <div class="state-icon">🔍</div>
        <h2>{{ t('scanToWin.notFoundTitle') }}</h2>
        <p>{{ t('scanToWin.notFoundDesc') }}</p>
        <router-link to="/" class="btn-primary">{{ t('common.goToQrScan') }}</router-link>
      </div>
    </div>

    <main v-else class="page-center wide">
      <div class="published-badge"><span class="badge-dot"></span>{{ t('nav.scanToWin') }}</div>
      <article class="public-win">
        <ScanToWinRenderer
          :name="campaign.name"
          :description="campaign.description"
          :template="campaign.template"
          :starts-at="campaign.starts_at"
          :ends-at="campaign.ends_at"
          :max-plays-per-day="campaign.max_plays_per_day"
          :prizes="campaign.prizes || []"
          :terms="campaign.terms"
          :theme-color="themeColor"
          :logo="campaign.logo_path"
          :background-image="campaign.background_image_path"
        >
          <template #action>
            <!-- Instant -->
            <div v-if="campaign.template === 'instant'" class="play-zone">
              <button
                type="button"
                class="play-btn"
                :disabled="playing || limitReached"
                @click="handlePlay"
              >
                {{ playing ? t('common.playing') : limitReached ? t('scanToWin.limitReached') : t('scanToWin.tryYourLuck') }}
              </button>
            </div>

            <!-- Wheel -->
            <div v-else-if="campaign.template === 'wheel'" class="play-zone">
              <div class="wheel-wrap">
                <div class="wheel" :class="{ spinning: spinning }">🎡</div>
              </div>
              <button
                type="button"
                class="play-btn"
                :disabled="playing || limitReached"
                @click="handlePlay"
              >
                {{ playing ? t('common.spinning') : limitReached ? t('scanToWin.limitReached') : t('scanToWin.spinTheWheel') }}
              </button>
            </div>

            <!-- Scratch -->
            <div v-else-if="campaign.template === 'scratch'" class="play-zone">
              <div class="scratch-card">
                <div v-if="showResult && playResult" class="scratch-result">
                  <div v-if="playResult.won && playResult.prize" class="result-won">
                    <img v-if="prizeImage(playResult.prize)" :src="prizeImage(playResult.prize)" alt="" class="result-img" />
                    <div class="result-title">{{ playResult.prize.name }}</div>
                    <p v-if="playResult.prize.description" class="result-desc">{{ playResult.prize.description }}</p>
                  </div>
                  <div v-else class="result-lose">
                    <div class="result-emoji">😔</div>
                    <p>{{ playResult.message }}</p>
                  </div>
                </div>
                <button
                  v-if="!scratchRevealed"
                  type="button"
                  class="scratch-overlay"
                  :disabled="playing || limitReached"
                  @click="handlePlay"
                >
                  {{ playing ? t('common.scratching') : limitReached ? t('scanToWin.limitReached') : t('scanToWin.scratchToReveal') }}
                </button>
              </div>
            </div>
          </template>
        </ScanToWinRenderer>

        <div class="share-qr">
          <p class="share-label">{{ t('scanToWin.scanToPlay') }}</p>
          <QrPreview
            :content="winUrl"
            :name="campaign.name"
            :foreground="themeColor"
            :logo-url="campaign.logo_path"
            :background-image="campaign.background_image_path"
            :size="160"
            :qr-shape="campaign.qr_shape || 'square'"
            :dot-style="campaign.dot_style || 'square'"
            :corner-style="campaign.corner_style || 'sharp'"
            :frame-style="campaign.frame_style || 'none'"
          />
        </div>
      </article>
    </main>

    <!-- Result modal (instant & wheel) -->
    <div
      v-if="showResult && playResult && campaign?.template !== 'scratch'"
      class="result-backdrop"
      @click.self="dismissResult"
    >
      <div class="result-card" :class="playResult.won ? 'result-card--won' : 'result-card--lose'">
        <button type="button" class="result-close" :aria-label="t('common.close')" @click="dismissResult">×</button>
        <div v-if="playResult.won && playResult.prize">
          <div class="result-emoji">🎉</div>
          <h3 class="result-heading">{{ t('scanToWin.youWon') }}</h3>
          <img v-if="prizeImage(playResult.prize)" :src="prizeImage(playResult.prize)" alt="" class="result-img" />
          <div class="result-title">{{ playResult.prize.name }}</div>
          <p v-if="playResult.prize.description" class="result-desc">{{ playResult.prize.description }}</p>
          <p v-if="playResult.message" class="result-msg">{{ playResult.message }}</p>
        </div>
        <div v-else>
          <div class="result-emoji">{{ limitReached ? '⏳' : '😔' }}</div>
          <h3 class="result-heading">{{ limitReached ? t('scanToWin.comeBackTomorrow') : t('scanToWin.notThisTime') }}</h3>
          <p class="result-msg">{{ playResult.message }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import ThemeToggle from '../components/ui/ThemeToggle.vue'
import PublicBrandHeader from '../components/ui/PublicBrandHeader.vue'
import ScanToWinRenderer from '../components/scan-to-win/ScanToWinRenderer.vue'
import QrPreview from '../components/previews/QrPreview.vue'
import { resolveStorageUrl } from '../utils/storageUrl'

const { t } = useI18n()

const route = useRoute()
const campaign = ref(null)
const loading = ref(true)
const errorType = ref(null)
const playing = ref(false)
const spinning = ref(false)
const scratchRevealed = ref(false)
const showResult = ref(false)
const playResult = ref(null)
const limitReached = ref(false)

const themeColor = computed(() => campaign.value?.theme_color || '#e8655a')
const pageStyle = computed(() => ({ '--page-theme': themeColor.value }))
const winUrl = computed(() => `${window.location.origin}/win/${route.params.slug}`)

function prizeImage(prize) {
  return prize?.image_path ? resolveStorageUrl(prize.image_path) : null
}

async function load() {
  loading.value = true
  errorType.value = null
  campaign.value = null
  scratchRevealed.value = false
  showResult.value = false
  playResult.value = null
  limitReached.value = false
  try {
    const { data } = await axios.get(`/api/win/${route.params.slug}`)
    campaign.value = data
  } catch (e) {
    if (e.response?.status === 403) errorType.value = 'unpublished'
    else errorType.value = 'notfound'
  } finally {
    loading.value = false
  }
}

async function handlePlay() {
  if (playing.value || limitReached.value) return
  playing.value = true

  if (campaign.value?.template === 'wheel') {
    spinning.value = true
    await new Promise((r) => setTimeout(r, 2200))
  }

  try {
    const { data } = await axios.post(`/api/win/${route.params.slug}/play`)
    playResult.value = data
    if (campaign.value?.template === 'scratch') {
      scratchRevealed.value = true
    }
    showResult.value = true
  } catch (e) {
    if (e.response?.status === 429) {
      limitReached.value = true
      playResult.value = {
        won: false,
        message: e.response.data?.message || t('scanToWin.dailyLimitReached'),
      }
      if (campaign.value?.template === 'scratch') scratchRevealed.value = true
      showResult.value = true
    }
  } finally {
    playing.value = false
    spinning.value = false
  }
}

function dismissResult() {
  showResult.value = false
}

watch(() => route.params.slug, load)
onMounted(load)
</script>

<style scoped>
.public-page { min-height: 100vh; position: relative; }
.page-bg {
  position: fixed; inset: 0; z-index: 0;
  background: linear-gradient(160deg, #faf8fd 0%, #f5f0ff 50%, #fde8e6 100%);
}
.page-header {
  position: relative; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.5rem; max-width: 48rem; margin: 0 auto;
}
.brand-link { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: #1a1333; text-decoration: none; font-size: 0.9375rem; }
.brand-icon { color: #e8655a; }
.brand-accent { color: #e8b84a; }
.page-center { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; padding: 1rem 1rem 3rem; max-width: 28rem; margin: 0 auto; }
.page-center.wide { max-width: 32rem; }
.published-badge {
  display: inline-flex; align-items: center; gap: 0.375rem;
  font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em;
  color: #6b4fa0; margin-bottom: 0.75rem;
}
.badge-dot { width: 0.375rem; height: 0.375rem; border-radius: 50%; background: #e8655a; }
.public-win { width: 100%; }
.share-qr { margin-top: 1.5rem; text-align: center; }
.share-label { font-size: 0.75rem; font-weight: 600; color: #8b839c; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem; }
.loader-card, .state-card {
  width: 100%; background: #fff; border-radius: 1.25rem; padding: 2rem;
  border: 1px solid #e8e4f0; text-align: center; box-shadow: 0 4px 24px rgba(26,19,51,0.06);
}
.skeleton { background: #f0ecf8; border-radius: 0.5rem; margin-bottom: 0.75rem; }
.skeleton-header { height: 4rem; }
.skeleton-line { height: 0.875rem; }
.line-wide { width: 80%; margin: 0 auto; }
.state-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.state-card h2 { font-size: 1.25rem; font-weight: 700; color: #1a1333; }
.state-card p { color: #8b839c; font-size: 0.875rem; margin: 0.5rem 0 1.25rem; }

.play-zone { margin-top: 1.25rem; text-align: center; }
.play-btn {
  width: 100%;
  padding: 0.875rem 1.25rem;
  border: none;
  border-radius: 0.875rem;
  background: var(--page-theme, #e8655a);
  color: #fff;
  font-size: 0.9375rem;
  font-weight: 700;
  cursor: pointer;
  transition: filter 0.2s, opacity 0.2s;
}
.play-btn:hover:not(:disabled) { filter: brightness(1.05); }
.play-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.wheel-wrap { margin-bottom: 1rem; display: flex; justify-content: center; }
.wheel {
  font-size: 4rem;
  line-height: 1;
  transition: transform 0.1s linear;
}
.wheel.spinning {
  animation: wheelSpin 2.2s cubic-bezier(0.2, 0.8, 0.3, 1) forwards;
}
@keyframes wheelSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(1440deg); }
}

.scratch-card {
  position: relative;
  min-height: 8rem;
  border-radius: 0.875rem;
  overflow: hidden;
  border: 1px solid #e8e4f0;
  background: #faf8fd;
}
.scratch-result {
  padding: 1.25rem;
  text-align: center;
}
.scratch-overlay {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: none;
  background: repeating-linear-gradient(
    45deg,
    #c4b8d8,
    #c4b8d8 8px,
    #b0a4cc 8px,
    #b0a4cc 16px
  );
  color: #1a1333;
  font-size: 0.9375rem;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.4s;
}
.scratch-overlay:disabled { cursor: not-allowed; opacity: 0.7; }

.result-backdrop {
  position: fixed;
  inset: 0;
  z-index: 100;
  background: rgba(26, 19, 51, 0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.result-card {
  position: relative;
  width: 100%;
  max-width: 20rem;
  background: #fff;
  border-radius: 1.25rem;
  padding: 2rem 1.5rem;
  text-align: center;
  box-shadow: 0 8px 40px rgba(26, 19, 51, 0.15);
}
.result-card--won { border: 2px solid color-mix(in srgb, var(--page-theme, #e8655a) 40%, transparent); }
.result-card--lose { border: 1px solid #e8e4f0; }
.result-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  width: 2rem;
  height: 2rem;
  border: none;
  background: #f0ecf8;
  border-radius: 50%;
  font-size: 1.25rem;
  line-height: 1;
  color: #5c5470;
  cursor: pointer;
}
.result-emoji { font-size: 2.5rem; margin-bottom: 0.5rem; }
.result-heading { font-size: 1.25rem; font-weight: 800; color: #1a1333; margin: 0 0 0.75rem; }
.result-img {
  width: 5rem;
  height: 5rem;
  object-fit: cover;
  border-radius: 0.75rem;
  margin: 0 auto 0.75rem;
  display: block;
}
.result-title { font-size: 1rem; font-weight: 700; color: #1a1333; }
.result-desc, .result-msg { font-size: 0.875rem; color: #8b839c; margin: 0.5rem 0 0; line-height: 1.5; }
.result-won .result-title { color: var(--page-theme, #e8655a); }
.result-lose p { font-size: 0.875rem; color: #8b839c; margin: 0; }
</style>
