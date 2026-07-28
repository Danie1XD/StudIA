import axios from 'axios'

// Creamos una instancia configurada para apuntar a tu backend en Laravel
const api = axios.create({
  baseURL: 'https://fluffy-goldfish-5g4gpg9rvj67c4qvg-8000.app.github.dev/api', // Puerto estándar de Laravel
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// Este interceptor revisa si hay un token guardado antes de cada petición
api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api