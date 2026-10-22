import http from './http'

export const tareasApi = {
  /** Las tareas del alumno agrupadas por materia: [{ materia, tareas: [...] }]. */
  misTareas: () => http.get('/mis-tareas').then((r) => r.data.data),
  mostrar: (id) => http.get(`/tareas/${id}`).then((r) => r.data.data),
  crear: (materiaId, formulario) =>
    http.post(`/materias/${materiaId}/tareas`, formulario).then((r) => r.data.data),
  // El formulario ya trae _method=PUT: PHP no lee archivos en un PUT de verdad
  actualizar: (id, formulario) => http.post(`/tareas/${id}`, formulario).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/tareas/${id}`),
  descargarArchivo: (id) =>
    http.get(`/tareas/${id}/archivo`, { responseType: 'blob' }).then((r) => r.data),
}
