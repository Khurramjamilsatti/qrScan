import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'
import adminApi, { adminLoginRequest } from '../services/adminApi'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const adminUser = ref(null)
  const limits = ref(null)
  const usage = ref(null)
  const loading = ref(false)
  const adminLoading = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isAdminAuthenticated = computed(() => !!adminUser.value?.is_admin)
  const isAdmin = computed(() => user.value?.is_admin === true)

  async function fetchUser() {
    const token = localStorage.getItem('token')
    if (!token) return
    try {
      const { data } = await api.get('/user')
      user.value = data.user
      limits.value = data.limits
      usage.value = data.usage
    } catch {
      logout()
    }
  }

  async function fetchAdminUser() {
    const token = localStorage.getItem('admin_token')
    if (!token) return
    try {
      const { data } = await adminApi.get('/user')
      adminUser.value = data.user
    } catch {
      adminLogout()
    }
  }

  async function login(email, password) {
    loading.value = true
    try {
      const { data } = await api.post('/login', { email, password })
      localStorage.removeItem('admin_token')
      adminUser.value = null
      localStorage.setItem('token', data.token)
      user.value = data.user
      await fetchUser()
      return data
    } finally {
      loading.value = false
    }
  }

  async function adminLogin(email, password) {
    adminLoading.value = true
    try {
      const data = await adminLoginRequest(email, password)
      localStorage.removeItem('token')
      user.value = null
      limits.value = null
      usage.value = null
      localStorage.setItem('admin_token', data.token)
      adminUser.value = data.user
      return data
    } finally {
      adminLoading.value = false
    }
  }

  async function register(name, email, password, password_confirmation) {
    loading.value = true
    try {
      const { data } = await api.post('/register', { name, email, password, password_confirmation })
      localStorage.setItem('token', data.token)
      user.value = data.user
      await fetchUser()
      return data
    } finally {
      loading.value = false
    }
  }

  function logout() {
    localStorage.removeItem('token')
    user.value = null
    limits.value = null
    usage.value = null
  }

  function adminLogout() {
    localStorage.removeItem('admin_token')
    adminUser.value = null
  }

  function updateUser(partial) {
    if (user.value) user.value = { ...user.value, ...partial }
  }

  return {
    user,
    adminUser,
    limits,
    usage,
    loading,
    adminLoading,
    isAuthenticated,
    isAdminAuthenticated,
    isAdmin,
    fetchUser,
    fetchAdminUser,
    login,
    adminLogin,
    register,
    logout,
    adminLogout,
    updateUser,
  }
})
