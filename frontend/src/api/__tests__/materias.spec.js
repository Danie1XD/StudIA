import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { materiasApi } from '../materias'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

describe('materiasApi', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('listar devuelve el arreglo de materias', async () => {
    http.get.mockReturnValue(respuesta([{ id: 1, nombre: 'IA' }]))

    expect(await materiasApi.listar()).toEqual([{ id: 1, nombre: 'IA' }])
    expect(http.get).toHaveBeenCalledWith('/materias')
  })

  it('mostrar pide una materia por su id', async () => {
    http.get.mockReturnValue(respuesta({ id: 5 }))

    expect(await materiasApi.mostrar(5)).toEqual({ id: 5 })
    expect(http.get).toHaveBeenCalledWith('/materias/5')
  })

  it('contenido pide la clase agrupada por sección', async () => {
    http.get.mockReturnValue(respuesta({ secciones: [] }))

    await materiasApi.contenido(5)

    expect(http.get).toHaveBeenCalledWith('/materias/5/contenido')
  })

  it('crear envía los datos y devuelve la materia creada', async () => {
    http.post.mockReturnValue(respuesta({ id: 9, nombre: 'Álgebra' }))

    const materia = await materiasApi.crear({ nombre: 'Álgebra', descripcion: null })

    expect(http.post).toHaveBeenCalledWith('/materias', { nombre: 'Álgebra', descripcion: null })
    expect(materia.id).toBe(9)
  })

  it('actualizar usa PUT sobre la materia indicada', async () => {
    http.put.mockReturnValue(respuesta({ id: 7 }))

    await materiasApi.actualizar(7, { nombre: 'Nuevo nombre' })

    expect(http.put).toHaveBeenCalledWith('/materias/7', { nombre: 'Nuevo nombre' })
  })

  it('eliminar usa DELETE sobre la materia indicada', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await materiasApi.eliminar(7)

    expect(http.delete).toHaveBeenCalledWith('/materias/7')
  })

  it('unirse envía el código con el nombre de campo que espera el servidor', async () => {
    http.post.mockReturnValue(respuesta({ id: 3 }))

    await materiasApi.unirse('ABC123')

    expect(http.post).toHaveBeenCalledWith('/materias/unirse', { codigo_acceso: 'ABC123' })
  })
})
