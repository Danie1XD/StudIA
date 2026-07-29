import axios from 'axios'

const api = axios.create({
  baseURL: 'https://fantastic-adventure-967x4qp9rjxvhvvq-8000.app.github.dev/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

api.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api