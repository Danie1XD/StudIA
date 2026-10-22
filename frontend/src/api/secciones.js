import http from './http'

export const seccionesApi = {
  crear: (materiaId, nombre) =>
    http.post(`/materias/${materiaId}/secciones`, { nombre }).then((r) => r.data.data),
  actualizar: (id, nombre) => http.put(`/secciones/${id}`, { nombre }).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/secciones/${id}`),
  reordenar: (materiaId, ids) =>
    http
      .post(`/materias/${materiaId}/secciones/reordenar`, { orden: ids })
      .then((r) => r.data.data),
}
