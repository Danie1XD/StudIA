import http from './http'

export const authApi = {
  login: (credenciales) => http.post('/login', credenciales).then((r) => r.data),
  me: () => http.get('/me').then((r) => r.data.user),
  logout: () => http.post('/logout'),
}
