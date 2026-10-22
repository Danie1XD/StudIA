import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { usuariosApi } from '../usuarios'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('usuariosApi', () => {
  it('listar manda los filtros y devuelve el cuerpo completo con la paginación', async () => {
    const cuerpo = { data: [{ id: 1 }], meta: { current_page: 1, last_page: 2, total: 20 } }
    http.get.mockResolvedValue({ data: cuerpo })

    expect(await usuariosApi.listar({ rol: 'docente', page: 2 })).toEqual(cuerpo)
    expect(http.get).toHaveBeenCalledWith('/admin/usuarios', {
      params: { rol: 'docente', page: 2 },
    })
  })

  it('crear envía los datos y devuelve el usuario creado', async () => {
    http.post.mockReturnValue(respuesta({ id: 8, rol: 'docente' }))
    const datos = {
      nombre: 'Profa.',
      email: 'p@studia.com',
      rol: 'docente',
      password: 'clave-segura-1',
    }

    expect((await usuariosApi.crear(datos)).id).toBe(8)
    expect(http.post).toHaveBeenCalledWith('/admin/usuarios', datos)
  })

  it('actualizar usa PUT sobre el usuario indicado', async () => {
    http.put.mockReturnValue(respuesta({ id: 8 }))

    await usuariosApi.actualizar(8, { nombre: 'Nuevo' })

    expect(http.put).toHaveBeenCalledWith('/admin/usuarios/8', { nombre: 'Nuevo' })
  })

  it('desactivar usa POST sobre /desactivar y devuelve el usuario', async () => {
    http.post.mockReturnValue(respuesta({ id: 8, activo: false }))

    expect((await usuariosApi.desactivar(8)).activo).toBe(false)
    expect(http.post).toHaveBeenCalledWith('/admin/usuarios/8/desactivar')
  })

  it('activar usa POST sobre /activar y devuelve el usuario', async () => {
    http.post.mockReturnValue(respuesta({ id: 8, activo: true }))

    expect((await usuariosApi.activar(8)).activo).toBe(true)
    expect(http.post).toHaveBeenCalledWith('/admin/usuarios/8/activar')
  })
})
