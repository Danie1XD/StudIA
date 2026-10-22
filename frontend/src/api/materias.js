import http from './http'

export const materiasApi = {
  listar: () => http.get('/materias').then((r) => r.data.data),
  mostrar: (id) => http.get(`/materias/${id}`).then((r) => r.data.data),
  contenido: (id) => http.get(`/materias/${id}/contenido`).then((r) => r.data.data),
  crear: (datos) => http.post('/materias', datos).then((r) => r.data.data),
  actualizar: (id, datos) => http.put(`/materias/${id}`, datos).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/materias/${id}`),
  unirse: (codigo) =>
    http.post('/materias/unirse', { codigo_acceso: codigo }).then((r) => r.data.data),
}
