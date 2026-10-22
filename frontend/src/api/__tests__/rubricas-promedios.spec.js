import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { promediosApi } from '../promedios'
import { rubricasApi } from '../rubricas'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('rubricasApi', () => {
  it('mostrar pide una rúbrica por su id', async () => {
    http.get.mockReturnValue(respuesta({ id: 3, nombre: 'Mapa mental' }))

    expect(await rubricasApi.mostrar(3)).toEqual({ id: 3, nombre: 'Mapa mental' })
    expect(http.get).toHaveBeenCalledWith('/rubricas/3')
  })

  it('crear envía los datos y devuelve la rúbrica creada', async () => {
    http.post.mockReturnValue(respuesta({ id: 9 }))
    const datos = { nombre: 'Nueva', descripcion: null, criterios: [] }

    expect((await rubricasApi.crear(datos)).id).toBe(9)
    expect(http.post).toHaveBeenCalledWith('/rubricas', datos)
  })

  it('actualizar usa PUT sobre la rúbrica indicada', async () => {
    http.put.mockReturnValue(respuesta({ id: 3 }))

    await rubricasApi.actualizar(3, { nombre: 'Cambiada' })

    expect(http.put).toHaveBeenCalledWith('/rubricas/3', { nombre: 'Cambiada' })
  })

  it('eliminar usa DELETE sobre la rúbrica indicada', async () => {
    http.delete.mockResolvedValue({ status: 204 })

    await rubricasApi.eliminar(3)

    expect(http.delete).toHaveBeenCalledWith('/rubricas/3')
  })
})

describe('promediosApi', () => {
  it('mios devuelve los promedios y las reglas aplicadas', async () => {
    const cuerpo = { data: [{ materia: { id: 1 } }], reglas: { vencidas_como_cero: true } }
    http.get.mockResolvedValue({ data: cuerpo })

    expect(await promediosApi.mios()).toEqual(cuerpo)
    expect(http.get).toHaveBeenCalledWith('/mis-promedios')
  })

  it('deMateria pide los promedios de todos los alumnos de la materia', async () => {
    const cuerpo = { data: [], reglas: { vencidas_como_cero: false } }
    http.get.mockResolvedValue({ data: cuerpo })

    expect(await promediosApi.deMateria(5)).toEqual(cuerpo)
    expect(http.get).toHaveBeenCalledWith('/materias/5/promedios')
  })
})
