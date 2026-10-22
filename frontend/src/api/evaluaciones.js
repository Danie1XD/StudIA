import http from './http'

export const evaluacionesApi = {
  /** La pre-evaluación que la IA ya generó para una entrega. */
  obtener: (entregaId) => http.get(`/entregas/${entregaId}/evaluacion-ia`).then((r) => r.data.data),
  /** Pide la pre-evaluación a la IA (tarda entre 10 y 60 segundos). */
  generar: (entregaId) =>
    http.post(`/entregas/${entregaId}/evaluacion-ia`).then((r) => r.data.data),
}
