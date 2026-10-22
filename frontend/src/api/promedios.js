import http from './http'

export const promediosApi = {
  /** Promedios del alumno en cada una de sus materias: { data: [...], reglas: {...} }. */
  mios: () => http.get('/mis-promedios').then((r) => r.data),
  /** Docente: promedios de todos los alumnos de una materia: { data: [...], reglas: {...} }. */
  deMateria: (materiaId) => http.get(`/materias/${materiaId}/promedios`).then((r) => r.data),
}
