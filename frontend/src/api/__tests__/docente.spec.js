import { beforeEach, describe, expect, it, vi } from 'vitest'
import http from '../http'
import { entregasApi } from '../entregas'
import { evaluacionesApi } from '../evaluaciones'

vi.mock('../http', () => ({
  default: { get: vi.fn(), post: vi.fn(), put: vi.fn(), delete: vi.fn() },
}))

const respuesta = (data) => Promise.resolve({ data: { data } })

beforeEach(() => {
  vi.clearAllMocks()
})

describe('entregasApi (docente)', () => {
  it('listarDeTarea devuelve a cada alumno con su entrega', async () => {
    const filas = [{ alumno: { id: 3 }, estado: 'entregado', entrega: { id: 1 } }]
    http.get.mockReturnValue(respuesta(filas))

    expect(await entregasApi.listarDeTarea(4)).toEqual(filas)
    expect(http.get).toHaveBeenCalledWith('/tareas/4/entregas')
  })

  it('calificar usa PUT sobre la calificación de la entrega y devuelve la entrega', async () => {
    http.put.mockReturnValue(respuesta({ id: 9, estado: 'calificado' }))
    const datos = { calificacion_final: 85, retroalimentacion_final: 'Buen trabajo.' }

    const entrega = await entregasApi.calificar(9, datos)

    expect(http.put).toHaveBeenCalledWith('/entregas/9/calificacion', datos)
    expect(entrega.estado).toBe('calificado')
  })
})

describe('evaluacionesApi', () => {
  it('obtener pide la pre-evaluación de una entrega', async () => {
    http.get.mockReturnValue(respuesta({ id: 1, calificacion_sugerida: 70 }))

    expect((await evaluacionesApi.obtener(9)).calificacion_sugerida).toBe(70)
    expect(http.get).toHaveBeenCalledWith('/entregas/9/evaluacion-ia')
  })

  it('generar la pide a la IA con POST y devuelve la evaluación', async () => {
    http.post.mockReturnValue(respuesta({ id: 2, modelo: 'gemini-3.6-flash' }))

    const evaluacion = await evaluacionesApi.generar(9)

    expect(http.post).toHaveBeenCalledWith('/entregas/9/evaluacion-ia')
    expect(evaluacion.id).toBe(2)
  })
})
