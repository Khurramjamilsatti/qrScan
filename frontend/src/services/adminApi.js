import axios from 'axios'

const adminApi = axios.create({
  baseURL: '/api/admin',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

adminApi.interceptors.request.use((config) => {
  const token = localStorage.getItem('admin_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  const locale = localStorage.getItem('qrscan-locale') || 'en'
  config.headers['Accept-Language'] = locale
  config.headers['X-Locale'] = locale
  return config
})

adminApi.interceptors.response.use(
  (res) => res,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('admin_token')
      if (!window.location.pathname.startsWith('/admin/login')) {
        window.location.href = '/admin/login'
      }
    }
    return Promise.reject(error)
  }
)

export async function adminLoginRequest(email, password) {
  const locale = localStorage.getItem('qrscan-locale') || 'en'
  const { data } = await axios.post('/api/admin/login', { email, password }, {
    headers: { 'Accept-Language': locale, 'X-Locale': locale },
  })
  return data
}

export default adminApi
