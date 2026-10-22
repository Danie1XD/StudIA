import http from './http'

export const entregasApi = {
  /** La entrega del alumno en esa tarea, o null si aún no entrega. */
  miEntrega: (tareaId) => http.get(`/tareas/${tareaId}/mi-entrega`).then((r) => r.data.data),
  /** Crea la entrega o reemplaza la que ya tenía (mientras no esté calificada). */
  entregar: (tareaId, formulario) =>
    http.post(`/tareas/${tareaId}/entrega`, formulario).then((r) => r.data.data),
  anular: (tareaId) => http.delete(`/tareas/${tareaId}/entrega`),
  descargarArchivo: (entregaId) =>
    http.get(`/entregas/${entregaId}/archivo`, { responseType: 'blob' }).then((r) => r.data),

  /** Docente: cada alumno inscrito con su entrega, o con estado "sin_entregar". */
  listarDeTarea: (tareaId) => http.get(`/tareas/${tareaId}/entregas`).then((r) => r.data.data),
  /** Docente: publica o actualiza la calificación final de una entrega. */
  calificar: (entregaId, datos) =>
    http.put(`/entregas/${entregaId}/calificacion`, datos).then((r) => r.data.data),
}
