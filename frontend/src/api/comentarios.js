import http from './http'

export const comentariosApi = {
  listar: (tareaId) => http.get(`/tareas/${tareaId}/comentarios`).then((r) => r.data.data),
  crear: (tareaId, mensaje) =>
    http.post(`/tareas/${tareaId}/comentarios`, { mensaje }).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/comentarios/${id}`),
}
