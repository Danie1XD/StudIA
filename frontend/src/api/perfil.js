import http from './http'

export const perfilApi = {
  actualizar: (datos) => http.put('/perfil', datos).then((r) => r.data.data),
  cambiarPassword: (datos) => http.put('/perfil/password', datos).then((r) => r.data),
}
