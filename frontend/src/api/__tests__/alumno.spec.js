import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { comentariosApi } from '../comentarios'
import { entregasApi } from '../entregas'
import { tareasApi } from '../tareas'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('tareasApi (alumno)', () => {
  it('misTareas devuelve los grupos por materia', async () => {
    const grupos = [{ materia: { id: 1, nombre: 'IA' }, tareas: [] }]
    http.get.mockReturnValue(Promise.resolve({ data: { data: grupos, conteos: {} } }))

    expect(await tareasApi.misTareas()).toEqual(grupos)
    expect(http.get).toHaveBeenCalledWith('/mis-tareas')
  })

  it('mostrar pide una tarea por su id', async () => {
    http.get.mockReturnValue(respuesta({ id: 4 }))

    expect(await tareasApi.mostrar(4)).toEqual({ id: 4 })
    expect(http.get).toHaveBeenCalledWith('/tareas/4')
  })
})

describe('entregasApi', () => {
  it('miEntrega devuelve null cuando aún no hay entrega', async () => {
    http.get.mockReturnValue(respuesta(null))

    expect(await entregasApi.miEntrega(4)).toBeNull()
    expect(http.get).toHaveBeenCalledWith('/tareas/4/mi-entrega')
  })

  it('entregar manda el formulario a la tarea indicada y devuelve la entrega', async () => {
    http.post.mockReturnValue(respuesta({ id: 9, estado: 'entregado' }))
    const formulario = new FormData()

    const entrega = await entregasApi.entregar(4, formulario)

    expect(http.post).toHaveBeenCalledWith('/tareas/4/entrega', formulario)
    expect(entrega.id).toBe(9)
  })

  it('anular usa DELETE sobre la entrega de esa tarea', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await entregasApi.anular(4)

    expect(http.delete).toHaveBeenCalledWith('/tareas/4/entrega')
  })

  it('descargarArchivo pide el archivo como binario', async () => {
    const blob = new Blob(['%PDF'])
    http.get.mockResolvedValue({ data: blob })

    expect(await entregasApi.descargarArchivo(9)).toBe(blob)
    expect(http.get).toHaveBeenCalledWith('/entregas/9/archivo', { responseType: 'blob' })
  })
})

describe('comentariosApi', () => {
  it('listar devuelve los comentarios de la tarea', async () => {
    http.get.mockReturnValue(respuesta([{ id: 1, mensaje: 'Hola' }]))

    expect(await comentariosApi.listar(4)).toEqual([{ id: 1, mensaje: 'Hola' }])
    expect(http.get).toHaveBeenCalledWith('/tareas/4/comentarios')
  })

  it('crear manda el mensaje y devuelve el comentario publicado', async () => {
    http.post.mockReturnValue(respuesta({ id: 2, mensaje: 'Gracias' }))

    const comentario = await comentariosApi.crear(4, 'Gracias')

    expect(http.post).toHaveBeenCalledWith('/tareas/4/comentarios', { mensaje: 'Gracias' })
    expect(comentario.id).toBe(2)
  })

  it('eliminar usa DELETE sobre el comentario indicado', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await comentariosApi.eliminar(2)

    expect(http.delete).toHaveBeenCalledWith('/comentarios/2')
  })
})
