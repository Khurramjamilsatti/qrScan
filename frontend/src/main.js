import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { useThemeStore } from './stores/theme'
import { i18n } from './i18n'
import './style.css'

const app = createApp(App)
const pinia = createPinia()
app.use(pinia)
app.use(i18n)
app.use(router)

// Apply theme before mount to prevent flash
useThemeStore(pinia).apply()

app.mount('#app')
