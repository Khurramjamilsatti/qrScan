<template>
  <div
    class="cert"
    :class="[`cert--${template}`, { 'cert--has-bg': bgUrl }]"
    :style="certVars"
  >
    <div class="cert__shadow">
      <div class="cert__outer">
        <div class="cert__frame">
          <div class="cert__paper" :style="paperStyle">
            <div class="cert__texture" aria-hidden="true" />
            <div class="cert__watermark" aria-hidden="true">{{ watermarkText }}</div>

            <span class="cert__corner cert__corner--tl" aria-hidden="true" />
            <span class="cert__corner cert__corner--tr" aria-hidden="true" />
            <span class="cert__corner cert__corner--bl" aria-hidden="true" />
            <span class="cert__corner cert__corner--br" aria-hidden="true" />

            <header class="cert__head">
              <div class="cert__head-row">
                <div class="cert__head-side">
                  <div v-if="logoUrl" class="cert__avatar-ring">
                    <img :src="logoUrl" alt="" class="cert__avatar-img" />
                  </div>
                </div>
                <div class="cert__head-center">
                  <p v-if="issuerName" class="cert__issuer">{{ issuerName }}</p>
                  <h1 class="cert__title">{{ title || t('renderers.certificate.defaultTitle') }}</h1>
                  <div class="cert__rule" aria-hidden="true">
                    <span class="cert__rule-line" />
                    <span class="cert__rule-gem">✦</span>
                    <span class="cert__rule-line" />
                  </div>
                </div>
                <div class="cert__head-side">
                  <div v-if="sealUrl" class="cert__avatar-ring">
                    <img :src="sealUrl" alt="" class="cert__avatar-img" />
                  </div>
                </div>
              </div>
            </header>

            <section class="cert__body">
              <p class="cert__presented">{{ t('renderers.certificate.presentedTo') }}</p>
              <h2 class="cert__recipient">{{ recipientName || t('renderers.certificate.recipient') }}</h2>
              <p v-if="awardTitle" class="cert__award">{{ awardTitle }}</p>
              <p v-if="description" class="cert__desc">{{ description }}</p>
              <div v-if="showDates && (completionDate || issueDate)" class="cert__dates">
                <span v-if="completionDate" class="cert__date-chip">
                  {{ t('renderers.certificate.completed', { date: fmtDate(completionDate) }) }}
                </span>
                <span v-if="issueDate" class="cert__date-chip">
                  {{ t('renderers.certificate.issued', { date: fmtDate(issueDate) }) }}
                </span>
              </div>
            </section>

            <footer class="cert__foot">
              <div class="cert__sig">
                <div class="cert__sig-pad">
                  <img v-if="instructorSigUrl" :src="instructorSigUrl" alt="" class="cert__sig-img" />
                </div>
                <div class="cert__sig-line">{{ t('renderers.certificate.instructorSig') }}</div>
              </div>

              <div v-if="showQr && verifyUrl" class="cert__qr-block">
                <div class="cert__qr-frame">
                  <img v-if="qrDataUrl" :src="qrDataUrl" alt="" class="cert__qr-img" />
                </div>
                <span class="cert__qr-label">{{ t('digitalCertificates.verifyQr') }}</span>
              </div>
              <div v-else class="cert__qr-block cert__qr-block--placeholder" aria-hidden="true">
                <div class="cert__medallion">✓</div>
                <span class="cert__qr-label">{{ t('renderers.certificate.verified') }}</span>
              </div>

              <div class="cert__sig">
                <div class="cert__sig-pad">
                  <img v-if="orgSigUrl" :src="orgSigUrl" alt="" class="cert__sig-img" />
                </div>
                <div class="cert__sig-line">{{ t('renderers.certificate.orgSig') }}</div>
              </div>
            </footer>

            <div v-if="showCertId && certificateId" class="cert__id">
              <span>{{ certificateId }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQrPreview } from '../../composables/useQrPreview'
import { resolveStorageUrl } from '../../utils/storageUrl'
import { formatBadgeDate, certificateFontCss } from '../../utils/digitalModules'

const { t } = useI18n()
const qrDataUrl = ref('')

const props = defineProps({
  title: String,
  template: { type: String, default: 'classic' },
  recipientName: String,
  awardTitle: String,
  issuerName: String,
  certificateId: String,
  description: String,
  completionDate: String,
  issueDate: String,
  settings: { type: Object, default: () => ({}) },
  themeColor: { type: String, default: '#1a1333' },
  logo: String,
  seal: String,
  instructorSignature: String,
  organizationSignature: String,
  backgroundImage: String,
  verifyUrl: String,
})

const logoUrl = computed(() => props.logo ? resolveStorageUrl(props.logo) : null)
const sealUrl = computed(() => props.seal ? resolveStorageUrl(props.seal) : null)
const instructorSigUrl = computed(() => props.instructorSignature ? resolveStorageUrl(props.instructorSignature) : null)
const orgSigUrl = computed(() => props.organizationSignature ? resolveStorageUrl(props.organizationSignature) : null)
const bgUrl = computed(() => props.backgroundImage ? resolveStorageUrl(props.backgroundImage) : null)

const paperStyle = computed(() => {
  const bg = props.settings?.background_color || '#fdfbf7'
  if (!bgUrl.value) return {}
  const hex = bg.replace('#', '')
  if (hex.length !== 6) return { backgroundImage: `linear-gradient(rgba(253,251,247,0.9), rgba(253,251,247,0.95)), url(${bgUrl.value})` }
  const r = parseInt(hex.slice(0, 2), 16)
  const g = parseInt(hex.slice(2, 4), 16)
  const b = parseInt(hex.slice(4, 6), 16)
  return { backgroundImage: `linear-gradient(rgba(${r},${g},${b},0.9), rgba(${r},${g},${b},0.95)), url(${bgUrl.value})` }
})

const certVars = computed(() => {
  const text = props.settings?.text_color || '#1a1333'
  const paper = props.settings?.background_color || '#fdfbf7'
  return {
    '--cert-primary': props.themeColor,
    '--cert-accent': `color-mix(in srgb, ${props.themeColor} 55%, #c9a227)`,
    '--cert-gold': '#b8952e',
    '--cert-paper': paper,
    '--cert-text': text,
    '--cert-text-muted': `color-mix(in srgb, ${text} 55%, #8b839c)`,
    '--cert-font': certificateFontCss(props.settings?.font_family),
  }
})

const watermarkText = computed(() => {
  const w = (props.title || 'Certificate').split(' ').pop() || 'Certificate'
  return w.toUpperCase()
})

const showDates = computed(() => props.settings?.show_dates !== false)
const showCertId = computed(() => props.settings?.show_certificate_id !== false)
const showQr = computed(() => props.settings?.show_qr !== false)

const { generateDataUrl } = useQrPreview()

watch(
  () => [props.verifyUrl, props.themeColor, props.logo],
  async () => {
    if (!props.verifyUrl) {
      qrDataUrl.value = ''
      return
    }
    qrDataUrl.value = await generateDataUrl(props.verifyUrl, {
      foreground: props.themeColor,
      background: '#ffffff',
      logoUrl: props.logo,
      size: 200,
      margin: 2,
      errorCorrection: 'M',
    })
  },
  { immediate: true },
)

function fmtDate(v) { return formatBadgeDate(v) }
</script>

<style scoped>
.cert {
  --cert-primary: #1a1333;
  --cert-accent: #5b4bb7;
  --cert-gold: #b8952e;
  --cert-paper: #fdfbf7;
  --cert-text: #1a1333;
  --cert-text-muted: #6b6280;
  --cert-font: 'Instrument Serif', Georgia, serif;
  width: 100%;
  font-family: var(--cert-font);
}

.cert__shadow {
  filter: drop-shadow(0 24px 48px rgba(26, 19, 51, 0.14)) drop-shadow(0 6px 16px rgba(26, 19, 51, 0.06));
}

.cert__outer {
  padding: 2px;
  background: linear-gradient(145deg, var(--cert-gold), var(--cert-accent), var(--cert-primary), var(--cert-accent));
  border-radius: 3px;
}

.cert__frame {
  padding: 9px;
  background: linear-gradient(168deg, #fffefc 0%, var(--cert-paper) 42%, #f6f1e8 100%);
  outline: 1px solid color-mix(in srgb, var(--cert-primary) 18%, #d9d2c4);
  outline-offset: -5px;
}

.cert__paper {
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  min-height: 440px;
  padding: 2.5rem 2.75rem 2rem;
  text-align: center;
  background: var(--cert-paper);
  background-size: cover;
  background-position: center;
}

.cert__texture {
  position: absolute;
  inset: 0;
  pointer-events: none;
  opacity: 0.45;
  background-image:
    radial-gradient(circle at 20% 30%, rgba(255,255,255,0.6) 0%, transparent 50%),
    radial-gradient(circle at 80% 70%, rgba(201,162,39,0.04) 0%, transparent 40%),
    repeating-linear-gradient(
      0deg,
      transparent,
      transparent 2px,
      rgba(180, 170, 150, 0.03) 2px,
      rgba(180, 170, 150, 0.03) 3px
    );
}

.cert__watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -52%);
  font-family: var(--cert-font);
  font-size: clamp(3.5rem, 12vw, 5.5rem);
  font-weight: 400;
  letter-spacing: 0.2em;
  color: color-mix(in srgb, var(--cert-text) 5%, transparent);
  white-space: nowrap;
  pointer-events: none;
  user-select: none;
}

.cert__corner {
  position: absolute;
  width: 32px;
  height: 32px;
  z-index: 1;
}
.cert__corner::before,
.cert__corner::after {
  content: '';
  position: absolute;
  background: color-mix(in srgb, var(--cert-gold) 70%, var(--cert-accent));
}
.cert__corner--tl { top: 16px; left: 16px; }
.cert__corner--tl::before { top: 0; left: 0; width: 28px; height: 2px; }
.cert__corner--tl::after { top: 0; left: 0; width: 2px; height: 28px; }
.cert__corner--tr { top: 16px; right: 16px; }
.cert__corner--tr::before { top: 0; right: 0; width: 28px; height: 2px; }
.cert__corner--tr::after { top: 0; right: 0; width: 2px; height: 28px; }
.cert__corner--bl { bottom: 16px; left: 16px; }
.cert__corner--bl::before { bottom: 0; left: 0; width: 28px; height: 2px; }
.cert__corner--bl::after { bottom: 0; left: 0; width: 2px; height: 28px; }
.cert__corner--br { bottom: 16px; right: 16px; }
.cert__corner--br::before { bottom: 0; right: 0; width: 28px; height: 2px; }
.cert__corner--br::after { bottom: 0; right: 0; width: 2px; height: 28px; }

.cert__head {
  position: relative;
  z-index: 1;
  margin-bottom: 1.35rem;
  padding-top: 0.25rem;
}

.cert__head-row {
  display: grid;
  grid-template-columns: 72px 1fr 72px;
  align-items: center;
  gap: 1rem;
}

.cert__head-side {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 72px;
}

.cert__head-center {
  min-width: 0;
}

.cert__avatar-ring {
  display: inline-block;
  width: 72px;
  height: 72px;
  border-radius: 50%;
  border: 2px solid color-mix(in srgb, var(--cert-gold) 40%, #d4cfc0);
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 8px rgba(26, 19, 51, 0.06);
  flex-shrink: 0;
}
.cert__avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.cert__issuer {
  font-size: 0.625rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--cert-text-muted);
  margin-bottom: 0.625rem;
  max-width: 28rem;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.5;
}

.cert__title {
  font-family: var(--cert-font);
  font-size: clamp(1.4rem, 3.8vw, 1.9rem);
  font-weight: 400;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--cert-text);
  line-height: 1.3;
  margin: 0;
}

.cert__rule {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.875rem;
  margin-top: 1.125rem;
}
.cert__rule-line {
  flex: 1;
  max-width: 140px;
  height: 1px;
  background: linear-gradient(90deg, transparent, color-mix(in srgb, var(--cert-gold) 50%, #ccc) 40%, color-mix(in srgb, var(--cert-gold) 50%, #ccc) 60%, transparent);
}
.cert__rule-gem {
  font-size: 0.5rem;
  color: var(--cert-gold);
  line-height: 1;
}

.cert__body {
  position: relative;
  z-index: 1;
  flex: 1;
  margin-bottom: 1.5rem;
}

.cert__presented {
  font-family: var(--cert-font);
  font-size: 0.9375rem;
  font-style: italic;
  color: var(--cert-text-muted);
  margin-bottom: 0.5rem;
  letter-spacing: 0.03em;
}

.cert__recipient {
  font-family: var(--cert-font);
  font-size: clamp(1.85rem, 5.5vw, 2.65rem);
  font-weight: 400;
  font-style: italic;
  color: var(--cert-text);
  margin: 0.25rem 0 0.875rem;
  line-height: 1.12;
  text-shadow: 0 1px 0 rgba(255,255,255,0.8);
}

.cert__award {
  font-size: 1.0625rem;
  font-weight: 600;
  color: color-mix(in srgb, var(--cert-text) 85%, #4a4460);
  margin-bottom: 0.75rem;
  letter-spacing: 0.03em;
}

.cert__desc {
  font-size: 0.875rem;
  color: var(--cert-text-muted);
  line-height: 1.7;
  max-width: 34rem;
  margin: 0 auto 1rem;
}

.cert__dates {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
}
.cert__date-chip {
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 0.4rem 0.875rem;
  border-radius: 9999px;
  background: rgba(255,255,255,0.75);
  border: 1px solid color-mix(in srgb, var(--cert-gold) 25%, #e8e4f0);
  color: var(--cert-text-muted);
}

.cert__foot {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 1.25rem;
  align-items: end;
  margin-top: auto;
  padding-top: 1.375rem;
  border-top: 1px solid color-mix(in srgb, var(--cert-gold) 30%, #e0dcd3);
}

.cert__sig { min-width: 0; }
.cert__sig-pad {
  height: 54px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  margin-bottom: 0.25rem;
}
.cert__sig-img {
  max-height: 50px;
  max-width: 140px;
  object-fit: contain;
}
.cert__sig-line {
  font-size: 0.5625rem;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #8b839c;
  padding-top: 0.5rem;
  border-top: 1px solid color-mix(in srgb, var(--cert-gold) 35%, #c4bdb0);
}

.cert__qr-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.4rem;
}
.cert__qr-frame {
  padding: 7px;
  background: #fff;
  border: 1px solid color-mix(in srgb, var(--cert-gold) 20%, #e8e4f0);
  border-radius: 0.5rem;
  box-shadow: 0 2px 10px rgba(26, 19, 51, 0.07);
}
.cert__qr-img {
  display: block;
  width: 92px;
  height: 92px;
  image-rendering: pixelated;
}
.cert__qr-block--placeholder .cert__medallion {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 1.35rem;
  color: var(--cert-gold);
  background: rgba(255,255,255,0.9);
  border: 2px solid color-mix(in srgb, var(--cert-gold) 50%, transparent);
  box-shadow: 0 2px 8px rgba(26, 19, 51, 0.06);
}
.cert__qr-label {
  font-size: 0.5rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: #8b839c;
}

.cert__id {
  position: relative;
  z-index: 1;
  margin-top: 1.375rem;
}
.cert__id span {
  display: inline-block;
  font-family: ui-monospace, 'SF Mono', monospace;
  font-size: 0.5625rem;
  font-weight: 600;
  letter-spacing: 0.1em;
  color: #8b839c;
  padding: 0.35rem 0.875rem;
  border-radius: 0.25rem;
  background: rgba(255,255,255,0.8);
  border: 1px dashed color-mix(in srgb, var(--cert-gold) 30%, #d4cfc0);
}

/* ── Template: classic ── */
.cert--classic .cert__title {
  font-weight: 700;
  letter-spacing: 0.08em;
}
.cert--classic .cert__recipient {
  font-style: normal;
  font-weight: 700;
}

/* ── Template: formal ── */
.cert--formal .cert__outer { border-radius: 0; padding: 4px; }
.cert--formal .cert__frame { outline-style: double; outline-width: 3px; }
.cert--formal .cert__title,
.cert--formal .cert__recipient {
  font-style: normal;
  font-weight: 700;
}
.cert--formal .cert__recipient { font-size: clamp(1.65rem, 5vw, 2.25rem); }

/* ── Template: modern ── */
.cert--modern .cert__outer {
  background: var(--cert-primary);
  padding: 3px;
  border-radius: 0.75rem;
}
.cert--modern .cert__frame {
  outline: none;
  border-left: 8px solid var(--cert-accent);
  border-radius: 0.5rem;
}
.cert--modern .cert__corner { display: none; }
.cert--modern .cert__watermark { display: none; }
.cert--modern .cert__title {
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: none;
}
.cert--modern .cert__recipient {
  font-style: normal;
  font-weight: 700;
}

/* ── Template: elegant ── */
.cert--elegant .cert__outer {
  background: transparent;
  padding: 0;
  border-radius: 0;
}
.cert--elegant .cert__outer {
  border: 1px solid color-mix(in srgb, var(--cert-gold) 60%, var(--cert-primary));
  padding: 5px;
}
.cert--elegant .cert__frame {
  outline: none;
  border: 1px solid color-mix(in srgb, var(--cert-gold) 40%, #d4cfc0);
  background: linear-gradient(180deg, #fffefc 0%, var(--cert-paper) 100%);
}
.cert--elegant .cert__title {
  font-weight: 400;
  letter-spacing: 0.22em;
  font-size: clamp(1rem, 2.8vw, 1.25rem);
}
.cert--elegant .cert__recipient {
  font-weight: 400;
  font-style: italic;
}
.cert--elegant .cert__corner::before,
.cert--elegant .cert__corner::after {
  background: var(--cert-gold);
}

@media (max-width: 540px) {
  .cert__paper { padding: 1.75rem 1.35rem 1.35rem; min-height: 380px; }
  .cert__head-row { grid-template-columns: 56px 1fr 56px; gap: 0.5rem; }
  .cert__avatar-ring { width: 56px; height: 56px; }
  .cert__head-side { min-height: 56px; }
  .cert__foot { grid-template-columns: 1fr; gap: 1.25rem; }
  .cert__qr-block { order: -1; }
  .cert__watermark { font-size: 2.5rem; }
}
</style>
