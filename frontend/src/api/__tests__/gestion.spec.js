import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { anunciosApi } from '../anuncios'
import { rubricasApi } from '../rubricas'
import { seccionesApi } from '../secciones'
import { tareasApi } from '../tareas'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('seccionesApi', () => {
  it('crear envía el nombre a la materia indicada', async () => {
    http.post.mockReturnValue(respuesta({ id: 3, nombre: 'Unidad 3' }))

    const seccion = await seccionesApi.crear(5, 'Unidad 3')

    expect(http.post).toHaveBeenCalledWith('/materias/5/secciones', { nombre: 'Unidad 3' })
    expect(seccion.id).toBe(3)
  })

  it('actualizar usa PUT sobre la sección indicada', async () => {
    http.put.mockReturnValue(respuesta({ id: 3 }))

    await seccionesApi.actualizar(3, 'Cierre')

    expect(http.put).toHaveBeenCalledWith('/secciones/3', { nombre: 'Cierre' })
  })

  it('eliminar usa DELETE sobre la sección indicada', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await seccionesApi.eliminar(3)

    expect(http.delete).toHaveBeenCalledWith('/secciones/3')
  })

  it('reordenar envía los ids en el orden deseado', async () => {
    http.post.mockReturnValue(respuesta([]))

    await seccionesApi.reordenar(5, [3, 1, 2])

    expect(http.post).toHaveBeenCalledWith('/materias/5/secciones/reordenar', { orden: [3, 1, 2] })
  })
})

describe('anunciosApi', () => {
  it('crear envía el anuncio a la materia indicada', async () => {
    http.post.mockReturnValue(respuesta({ id: 2 }))

    await anunciosApi.crear(5, { contenido: 'Hola', seccion_id: null })

    expect(http.post).toHaveBeenCalledWith('/materias/5/anuncios', {
      contenido: 'Hola',
      seccion_id: null,
    })
  })

  it('actualizar puede mandar seccion_id null para dejarlo sin sección', async () => {
    http.put.mockReturnValue(respuesta({ id: 2 }))

    await anunciosApi.actualizar(2, { contenido: 'Hola', seccion_id: null })

    expect(http.put).toHaveBeenCalledWith('/anuncios/2', { contenido: 'Hola', seccion_id: null })
  })

  it('eliminar usa DELETE sobre el anuncio indicado', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await anunciosApi.eliminar(2)

    expect(http.delete).toHaveBeenCalledWith('/anuncios/2')
  })
})

describe('tareasApi', () => {
  it('crear manda el formulario a la materia indicada', async () => {
    http.post.mockReturnValue(respuesta({ id: 8 }))
    const formulario = new FormData()

    const tarea = await tareasApi.crear(5, formulario)

    expect(http.post).toHaveBeenCalledWith('/materias/5/tareas', formulario)
    expect(tarea.id).toBe(8)
  })

  it('actualizar usa POST sobre la tarea, porque el formulario ya trae _method=PUT', async () => {
    http.post.mockReturnValue(respuesta({ id: 7 }))
    const formulario = new FormData()

    await tareasApi.actualizar(7, formulario)

    expect(http.post).toHaveBeenCalledWith('/tareas/7', formulario)
    expect(http.put).not.toHaveBeenCalled()
  })

  it('eliminar usa DELETE sobre la tarea indicada', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await tareasApi.eliminar(7)

    expect(http.delete).toHaveBeenCalledWith('/tareas/7')
  })

  it('descargarArchivo pide el PDF como archivo binario', async () => {
    const blob = new Blob(['%PDF'])
    http.get.mockResolvedValue({ data: blob })

    const resultado = await tareasApi.descargarArchivo(7)

    expect(http.get).toHaveBeenCalledWith('/tareas/7/archivo', { responseType: 'blob' })
    expect(resultado).toBe(blob)
  })
})

describe('rubricasApi', () => {
  it('listar devuelve el arreglo de rúbricas', async () => {
    http.get.mockReturnValue(respuesta([{ id: 1, nombre: 'Mapa mental' }]))

    expect(await rubricasApi.listar()).toEqual([{ id: 1, nombre: 'Mapa mental' }])
    expect(http.get).toHaveBeenCalledWith('/rubricas')
  })
})
