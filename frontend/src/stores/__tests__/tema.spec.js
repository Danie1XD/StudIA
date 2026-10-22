// @vitest-environment jsdom
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useTemaStore } from '../tema'

/** Almacenamiento en memoria: no depende del localStorage de Node ni de jsdom. */
function crearAlmacenamiento() {
  const datos = new Map()
  return {
    getItem: (clave) => (datos.has(clave) ? datos.get(clave) : null),
    setItem: (clave, valor) => datos.set(clave, String(valor)),
    removeItem: (clave) => datos.delete(clave),
    clear: () => datos.clear(),
  }
}

describe('store de tema', () => {
  beforeEach(() => {
    vi.stubGlobal('localStorage', crearAlmacenamiento())
    delete document.documentElement.dataset.tema
    setActivePinia(createPinia())
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('empieza en oscuro, la identidad de StudIA, si no hay preferencia guardada', () => {
    const tema = useTemaStore()

    expect(tema.actual).toBe('oscuro')
    expect(document.documentElement.dataset.tema).toBe('oscuro')
  })

  it('respeta la preferencia guardada', () => {
    localStorage.setItem('studia.tema', 'claro')

    const tema = useTemaStore()

    expect(tema.actual).toBe('claro')
    expect(document.documentElement.dataset.tema).toBe('claro')
  })

  it('ignora un valor guardado que no es un tema válido', () => {
    localStorage.setItem('studia.tema', 'rosa')

    expect(useTemaStore().actual).toBe('oscuro')
  })

  it('alternar cambia el tema, lo guarda y lo aplica al documento', () => {
    const tema = useTemaStore()

    tema.alternar()
    expect(tema.actual).toBe('claro')
    expect(localStorage.getItem('studia.tema')).toBe('claro')
    expect(document.documentElement.dataset.tema).toBe('claro')

    tema.alternar()
    expect(tema.actual).toBe('oscuro')
    expect(document.documentElement.dataset.tema).toBe('oscuro')
  })

  it('ignora temas desconocidos', () => {
    const tema = useTemaStore()

    tema.establecer('rosa')

    expect(tema.actual).toBe('oscuro')
  })

  it('sigue funcionando si el almacenamiento está bloqueado', () => {
    const bloqueado = () => {
      throw new Error('bloqueado')
    }
    vi.stubGlobal('localStorage', { getItem: bloqueado, setItem: bloqueado })

    const tema = useTemaStore()
    expect(tema.actual).toBe('oscuro')

    tema.alternar()
    expect(tema.actual).toBe('claro')
    expect(document.documentElement.dataset.tema).toBe('claro')
  })
})
