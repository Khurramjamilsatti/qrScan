import axios from 'axios'
import { isAuthPage, isPublicAppPath } from '../utils/publicRoutes'

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  const locale = localStorage.getItem('qrscan-locale') || 'en'
  config.headers['Accept-Language'] = locale
  config.headers['X-Locale'] = locale
  return config
})

api.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      const path = window.location.pathname
      if (!isPublicAppPath(path) && !isAuthPage(path)) {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default api
