import { describe, expect, it } from 'vitest'
import { parsearInline, parsearMarkdown } from '../markdown'

describe('parsearInline', () => {
  it('reconoce negrita, cursiva y código, y deja el resto como texto', () => {
    expect(parsearInline('Hola **mundo**, *qué* tal `x = 1`.')).toEqual([
      { texto: 'Hola ' },
      { texto: 'mundo', negrita: true },
      { texto: ', ' },
      { texto: 'qué', cursiva: true },
      { texto: ' tal ' },
      { texto: 'x = 1', codigo: true },
      { texto: '.' },
    ])
  })

  it('no confunde asteriscos sueltos con énfasis', () => {
    expect(parsearInline('2 * 3 * 4')).toEqual([{ texto: '2 * 3 * 4' }])
  })
})

describe('parsearMarkdown', () => {
  it('interpreta una respuesta típica con párrafos y lista con viñetas', () => {
    const texto =
      '¡Hola! Tienes **1 tarea** pendiente:\n\n* **Materia:** IA  \n* **Tarea:** Actividad 2\n\n¿Te ayudo?'

    expect(parsearMarkdown(texto)).toEqual([
      {
        tipo: 'parrafo',
        lineas: [
          [
            { texto: '¡Hola! Tienes ' },
            { texto: '1 tarea', negrita: true },
            { texto: ' pendiente:' },
          ],
        ],
      },
      {
        tipo: 'lista',
        ordenada: false,
        items: [
          [{ texto: 'Materia:', negrita: true }, { texto: ' IA' }],
          [{ texto: 'Tarea:', negrita: true }, { texto: ' Actividad 2' }],
        ],
      },
      { tipo: 'parrafo', lineas: [[{ texto: '¿Te ayudo?' }]] },
    ])
  })

  it('conserva los saltos de línea simples dentro de un párrafo', () => {
    expect(parsearMarkdown('Línea 1\nLínea 2\n\nOtro')).toEqual([
      { tipo: 'parrafo', lineas: [[{ texto: 'Línea 1' }], [{ texto: 'Línea 2' }]] },
      { tipo: 'parrafo', lineas: [[{ texto: 'Otro' }]] },
    ])
  })

  it('separa las listas numeradas de las de viñetas', () => {
    expect(parsearMarkdown('1. Uno\n2) Dos\n- A\n- B')).toEqual([
      { tipo: 'lista', ordenada: true, items: [[{ texto: 'Uno' }], [{ texto: 'Dos' }]] },
      { tipo: 'lista', ordenada: false, items: [[{ texto: 'A' }], [{ texto: 'B' }]] },
    ])
  })

  it('convierte los títulos en un párrafo en negrita', () => {
    expect(parsearMarkdown('## Resumen\nTexto')).toEqual([
      { tipo: 'parrafo', lineas: [[{ texto: 'Resumen', negrita: true }]] },
      { tipo: 'parrafo', lineas: [[{ texto: 'Texto' }]] },
    ])
  })

  it('deja el HTML como texto literal: nunca lo convierte en etiquetas', () => {
    expect(parsearMarkdown('<img src=x onerror=alert(1)> y <b>negrita</b>')).toEqual([
      { tipo: 'parrafo', lineas: [[{ texto: '<img src=x onerror=alert(1)> y <b>negrita</b>' }]] },
    ])
  })

  it('devuelve una lista vacía si no hay texto', () => {
    for (const valor of ['', null, undefined, '   \n  ']) {
      expect(parsearMarkdown(valor)).toEqual([])
    }
  })
})
