import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { asistenteApi } from '../asistente'
import { perfilApi } from '../perfil'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('asistenteApi', () => {
  it('enviar manda el mensaje con el historial y devuelve solo el texto de la respuesta', async () => {
    http.post.mockReturnValue(respuesta({ respuesta: 'Claro, te ayudo.' }))
    const historial = [{ rol: 'usuario', texto: 'Hola' }]

    expect(await asistenteApi.enviar('¿Qué tengo pendiente?', historial)).toBe('Claro, te ayudo.')
    expect(http.post).toHaveBeenCalledWith('/asistente/mensajes', {
      mensaje: '¿Qué tengo pendiente?',
      historial,
    })
  })
})

describe('perfilApi', () => {
  it('actualizar usa PUT y devuelve el usuario actualizado', async () => {
    http.put.mockReturnValue(respuesta({ id: 3, nombre: 'Ana María' }))

    expect(await perfilApi.actualizar({ nombre: 'Ana María' })).toEqual({
      id: 3,
      nombre: 'Ana María',
    })
    expect(http.put).toHaveBeenCalledWith('/perfil', { nombre: 'Ana María' })
  })

  it('cambiarPassword usa PUT y devuelve el mensaje del servidor', async () => {
    http.put.mockResolvedValue({ data: { message: 'Contraseña actualizada.' } })
    const datos = { password_actual: 'a', password: 'b', password_confirmation: 'b' }

    expect(await perfilApi.cambiarPassword(datos)).toEqual({ message: 'Contraseña actualizada.' })
    expect(http.put).toHaveBeenCalledWith('/perfil/password', datos)
  })
})
