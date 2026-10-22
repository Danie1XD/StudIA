import http from './http'

export const asistenteApi = {
  enviar: (mensaje, historial) =>
    http.post('/asistente/mensajes', { mensaje, historial }).then((r) => r.data.data.respuesta),
}
