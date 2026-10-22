import { describe, expect, it } from 'vitest'
import { deInputLocalAIso, deIsoAInputLocal, formatearFechaHora } from '../fechas'

describe('formatearFechaHora', () => {
  it('devuelve texto vacío si no hay fecha', () => {
    expect(formatearFechaHora(null)).toBe('')
    expect(formatearFechaHora('')).toBe('')
  })

  it('devuelve texto vacío si la fecha no es válida', () => {
    expect(formatearFechaHora('no-es-una-fecha')).toBe('')
  })

  it('muestra el año y la hora en la zona indicada', () => {
    // 05:59 UTC del 2 de enero = 23:59 del 1 de enero en la Ciudad de México
    const texto = formatearFechaHora('2030-01-02T05:59:00Z', 'America/Mexico_City')

    expect(texto).toMatch(/2030/)
    expect(texto).toMatch(/(11|23):59/) // según el idioma, en formato de 12 o de 24 horas
  })
})

describe('conversión con <input type="datetime-local">', () => {
  it('interpreta el valor del campo como hora local y lo pasa a ISO en UTC', () => {
    const iso = deInputLocalAIso('2030-06-15T10:30')

    expect(iso).toMatch(/Z$/)
    expect(new Date(iso).getHours()).toBe(10)
    expect(new Date(iso).getMinutes()).toBe(30)
  })

  it('ida y vuelta conserva el momento exacto', () => {
    const original = '2030-01-02T05:59:00.000Z'

    expect(deInputLocalAIso(deIsoAInputLocal(original))).toBe(original)
  })

  it('el valor para el campo no lleva segundos ni zona', () => {
    expect(deIsoAInputLocal('2030-01-02T05:59:00.000Z')).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/)
  })

  it('devuelve texto vacío con valores vacíos o inválidos', () => {
    expect(deInputLocalAIso('')).toBe('')
    expect(deInputLocalAIso('basura')).toBe('')
    expect(deIsoAInputLocal(null)).toBe('')
    expect(deIsoAInputLocal('basura')).toBe('')
  })
})
