import http from './http'

export const usuariosApi = {
  /** Devuelve el cuerpo completo: { data: [...], links, meta }. */
  listar: (params = {}) => http.get('/admin/usuarios', { params }).then((r) => r.data),
  crear: (datos) => http.post('/admin/usuarios', datos).then((r) => r.data.data),
  actualizar: (id, datos) => http.put(`/admin/usuarios/${id}`, datos).then((r) => r.data.data),
  desactivar: (id) => http.post(`/admin/usuarios/${id}/desactivar`).then((r) => r.data.data),
  activar: (id) => http.post(`/admin/usuarios/${id}/activar`).then((r) => r.data.data),
}
