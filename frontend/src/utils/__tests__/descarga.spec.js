import { describe, expect, it } from 'vitest'
import { extensionDeMime, nombreDeArchivo, nombreDeEntrega } from '../descarga'

describe('nombreDeArchivo', () => {
  it('quita las tildes y los símbolos', () => {
    expect(nombreDeArchivo('Actividad 1: ¿Qué es la IA?')).toBe('actividad-1-que-es-la-ia.pdf')
  })

  it('usa un nombre genérico si no queda nada', () => {
    expect(nombreDeArchivo('¿¿??')).toBe('archivo.pdf')
    expect(nombreDeArchivo(null)).toBe('archivo.pdf')
  })

  it('recorta los títulos largos y acepta otra extensión', () => {
    expect(nombreDeArchivo('a'.repeat(100), 'zip')).toBe(`${'a'.repeat(60)}.zip`)
  })
})

describe('extensionDeMime', () => {
  it('reconoce los tipos de archivo que acepta una entrega', () => {
    expect(extensionDeMime('application/pdf')).toBe('pdf')
    expect(extensionDeMime('image/jpeg')).toBe('jpg')
    expect(extensionDeMime('application/zip')).toBe('zip')
    expect(
      extensionDeMime('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    ).toBe('docx')
  })

  it('ignora parámetros como el charset y las mayúsculas', () => {
    expect(extensionDeMime('Application/PDF; charset=binary')).toBe('pdf')
  })

  it('usa "bin" si no lo reconoce o no hay tipo', () => {
    expect(extensionDeMime('application/x-raro')).toBe('bin')
    expect(extensionDeMime('')).toBe('bin')
    expect(extensionDeMime(undefined)).toBe('bin')
  })
})

describe('nombreDeEntrega', () => {
  it('antepone "entrega" al título y usa la extensión del archivo', () => {
    expect(nombreDeEntrega('Actividad 2: Modelos neuronales', 'application/pdf')).toBe(
      'entrega-actividad-2-modelos-neuronales.pdf',
    )
  })

  it('funciona sin título', () => {
    expect(nombreDeEntrega(null, 'image/png')).toBe('entrega.png')
  })
})
