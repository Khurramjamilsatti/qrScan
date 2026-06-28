import { ref } from 'vue'

export function useCopy() {
  const copied = ref(false)

  async function copy(text) {
    try {
      await navigator.clipboard.writeText(text)
      copied.value = true
      setTimeout(() => { copied.value = false }, 2000)
      return true
    } catch {
      return false
    }
  }

  return { copied, copy }
}
