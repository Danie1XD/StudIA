import http from './http'

export const anunciosApi = {
  crear: (materiaId, datos) =>
    http.post(`/materias/${materiaId}/anuncios`, datos).then((r) => r.data.data),
  actualizar: (id, datos) => http.put(`/anuncios/${id}`, datos).then((r) => r.data.data),
  eliminar: (id) => http.delete(`/anuncios/${id}`),
}
