import { defineStore } from 'pinia'
import { ref } from 'vue'
import { i18n } from '../i18n'

const t = (key) => i18n.global.t(key)

const defaults = {
  type: 'alert',
  variant: 'info',
  title: '',
  message: '',
  confirmText: '',
  cancelText: '',
}

export const useDialogStore = defineStore('dialog', () => {
  const visible = ref(false)
  const options = ref({ ...defaults })
  let resolvePromise = null

  function open(opts) {
    options.value = {
      ...defaults,
      confirmText: t('common.ok'),
      cancelText: t('common.cancel'),
      ...opts,
    }
    visible.value = true
    return new Promise((resolve) => {
      resolvePromise = resolve
    })
  }

  function alert(input) {
    const opts = typeof input === 'string' ? { message: input } : input
    return open({
      type: 'alert',
      variant: opts.variant || 'info',
      title: opts.title || t('common.notice'),
      message: opts.message || '',
      confirmText: opts.confirmText || t('common.gotIt'),
      ...opts,
    }).then(() => true)
  }

  function confirm(input) {
    const opts = typeof input === 'string' ? { message: input } : input
    return open({
      type: 'confirm',
      variant: opts.variant || 'danger',
      title: opts.title || t('common.areYouSure'),
      message: opts.message || '',
      confirmText: opts.confirmText || t('common.confirm'),
      cancelText: opts.cancelText || t('common.cancel'),
      ...opts,
    })
  }

  function resolve(value) {
    visible.value = false
    resolvePromise?.(value)
    resolvePromise = null
  }

  function confirmAction() {
    resolve(true)
  }

  function cancelAction() {
    resolve(false)
  }

  return {
    visible,
    options,
    alert,
    confirm,
    confirmAction,
    cancelAction,
  }
})
