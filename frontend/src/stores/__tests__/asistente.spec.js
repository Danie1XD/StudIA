import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAsistenteStore } from '../asistente'
import { asistenteApi } from '@/api/asistente'
import { SALUDO } from '@/utils/asistente'

vi.mock('@/api/asistente', () => ({ asistenteApi: { enviar: vi.fn() } }))

describe('store del asistente', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('empieza con el saludo del asistente', () => {
    const asistente = useAsistenteStore()

    expect(asistente.mensajes).toHaveLength(1)
    expect(asistente.mensajes[0]).toMatchObject({ rol: 'asistente', texto: SALUDO })
    expect(asistente.enviando).toBe(false)
  })

  it('envía el mensaje con el historial previo (sin el saludo) y agrega la respuesta', async () => {
    asistenteApi.enviar
      .mockResolvedValueOnce('Primera respuesta')
      .mockResolvedValueOnce('Segunda respuesta')
    const asistente = useAsistenteStore()

    expect(await asistente.enviar('  Hola  ')).toBe(true)
    expect(asistenteApi.enviar).toHaveBeenLastCalledWith('Hola', [])
    expect(asistente.mensajes.map((m) => [m.rol, m.texto])).toEqual([
      ['asistente', SALUDO],
      ['usuario', 'Hola'],
      ['asistente', 'Primera respuesta'],
    ])

    await asistente.enviar('Otra duda')

    expect(asistenteApi.enviar).toHaveBeenLastCalledWith('Otra duda', [
      { rol: 'usuario', texto: 'Hola' },
      { rol: 'asistente', texto: 'Primera respuesta' },
    ])
  })

  it('si falla, quita el mensaje, explica el error en español y devuelve false', async () => {
    asistenteApi.enviar.mockRejectedValue({
      response: {
        status: 429,
        headers: {},
        data: { message: 'Se alcanzó el límite de uso de la IA. Espera unos minutos.' },
      },
    })
    const asistente = useAsistenteStore()

    expect(await asistente.enviar('Hola')).toBe(false)
    expect(asistente.mensajes).toHaveLength(1)
    expect(asistente.error).toMatch(/límite de uso/)
    expect(asistente.enviando).toBe(false)
  })

  it('no envía mensajes vacíos ni mientras espera una respuesta', async () => {
    let resolver
    asistenteApi.enviar.mockReturnValue(
      new Promise((resolve) => {
        resolver = resolve
      }),
    )
    const asistente = useAsistenteStore()

    expect(await asistente.enviar('   ')).toBe(false)
    expect(asistente.error).toBe('Escribe tu duda.')
    expect(asistenteApi.enviar).not.toHaveBeenCalled()

    const primero = asistente.enviar('Hola')
    expect(asistente.enviando).toBe(true)
    expect(await asistente.enviar('Otro')).toBe(false)
    expect(asistenteApi.enviar).toHaveBeenCalledTimes(1)

    resolver('Listo')
    expect(await primero).toBe(true)
  })

  it('reiniciar deja solo el saludo e ignora la respuesta que llega tarde', async () => {
    let resolver
    asistenteApi.enviar.mockReturnValue(
      new Promise((resolve) => {
        resolver = resolve
      }),
    )
    const asistente = useAsistenteStore()

    const pendiente = asistente.enviar('Hola')
    asistente.reiniciar()

    expect(asistente.mensajes).toHaveLength(1)
    expect(asistente.enviando).toBe(false)

    resolver('Respuesta tardía')
    expect(await pendiente).toBe(false)
    expect(asistente.mensajes).toHaveLength(1)
  })

  it('reiniciar limpia un error anterior y permite volver a enviar', async () => {
    asistenteApi.enviar.mockResolvedValue('Hola de nuevo')
    const asistente = useAsistenteStore()

    await asistente.enviar('')
    expect(asistente.error).not.toBe('')

    asistente.reiniciar()
    expect(asistente.error).toBe('')

    expect(await asistente.enviar('Hola')).toBe(true)
    expect(asistente.mensajes).toHaveLength(3)
  })

  it('una pregunta dejada desde el inicio se toma una sola vez y reiniciar la borra', () => {
    const asistente = useAsistenteStore()

    asistente.dejarPregunta('  ¿Qué es un límite?  ')
    expect(asistente.tomarPregunta()).toBe('¿Qué es un límite?')
    expect(asistente.tomarPregunta()).toBe('')

    asistente.dejarPregunta('otra')
    asistente.reiniciar()
    expect(asistente.tomarPregunta()).toBe('')
  })
})
