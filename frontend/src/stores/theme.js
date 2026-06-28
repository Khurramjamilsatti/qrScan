import { defineStore } from 'pinia'
import { ref, watch } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const mode = ref(localStorage.getItem('theme') || 'light')

  function apply() {
    document.documentElement.setAttribute('data-theme', mode.value)
    document.documentElement.classList.toggle('dark', mode.value === 'dark')
  }

  function setTheme(value) {
    mode.value = value
    localStorage.setItem('theme', value)
    apply()
  }

  function toggle() {
    setTheme(mode.value === 'dark' ? 'light' : 'dark')
  }

  watch(mode, apply)

  return { mode, setTheme, toggle, apply }
})
