import axios from 'axios'

const publicApi = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

publicApi.interceptors.request.use((config) => {
  const locale = localStorage.getItem('qrscan-locale') || 'en'
  config.headers['Accept-Language'] = locale
  config.headers['X-Locale'] = locale
  return config
})

export default publicApi
