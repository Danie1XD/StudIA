import { describe, expect, it } from 'vitest'
import { alternarOculto, guardarOcultos, leerOcultos, mostrar } from '../colapsables'

function almacenFalso(inicial = {}) {
  const datos = { ...inicial }
  return {
    getItem: (k) => datos[k] ?? null,
    setItem: (k, v) => {
      datos[k] = v
    },
  }
}

describe('secciones ocultas', () => {
  it('todo queda abierto si no hay nada guardado', () => {
    expect(leerOcultos(1, almacenFalso()).size).toBe(0)
  })

  it('guarda y recupera lo oculto de cada materia por separado', () => {
    const almacen = almacenFalso()
    guardarOcultos(1, new Set(['seccion-3', 'general']), almacen)

    expect([...leerOcultos(1, almacen)].sort()).toEqual(['general', 'seccion-3'])
    expect(leerOcultos(2, almacen).size).toBe(0)
  })

  it('ignora datos dañados en vez de romper la pantalla', () => {
    const almacen = almacenFalso({ 'studia.secciones-ocultas.1': '{no es json' })
    expect(leerOcultos(1, almacen).size).toBe(0)
  })

  it('no falla si el navegador no deja guardar', () => {
    const sinPermiso = {
      getItem: () => {
        throw new Error('bloqueado')
      },
      setItem: () => {
        throw new Error('bloqueado')
      },
    }

    expect(leerOcultos(1, sinPermiso).size).toBe(0)
    expect(() => guardarOcultos(1, new Set(['a']), sinPermiso)).not.toThrow()
  })

  it('alterna sin modificar el conjunto original', () => {
    const original = new Set(['a'])

    expect(alternarOculto(original, 'b').has('b')).toBe(true)
    expect(alternarOculto(original, 'a').has('a')).toBe(false)
    expect([...original]).toEqual(['a'])
  })

  it('mostrar abre una sección oculta', () => {
    expect(mostrar(new Set(['a', 'b']), 'a').has('a')).toBe(false)
  })
})
