import http from './http'

export const rubricasApi = {
  listar: () => http.get('/rubricas').then((r) => r.data.data),
  mostrar: (id) => http.get(`/rubricas/${id}`).then((r) => r.data.data),
  crear: (datos) => http.post('/rubricas', datos).then((r) => r.data.data),
  actualizar: (id, datos) => http.put(`/rubricas/${id}`, datos).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/rubricas/${id}`),
}
