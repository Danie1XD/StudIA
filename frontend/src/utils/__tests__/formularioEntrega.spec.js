// @vitest-environment jsdom
import { describe, expect, it } from 'vitest'
import {
  armarFormularioEntrega,
  TAMANO_MAXIMO_ENTREGA,
  validarArchivo,
  validarEntrega,
} from '../formularioEntrega'

describe('armarFormularioEntrega', () => {
  it('manda el texto sin espacios sobrantes', () => {
    expect(armarFormularioEntrega('  mi enlace  ').get('contenido')).toBe('mi enlace')
  })

  it('manda el campo de texto aunque esté vacío, porque es el texto nuevo de la entrega', () => {
    const datos = armarFormularioEntrega('   ')

    expect(datos.has('contenido')).toBe(true)
    expect(datos.get('contenido')).toBe('')
  })

  it('incluye el archivo solo cuando se elige uno', () => {
    const pdf = new File(['%PDF-1.4'], 'trabajo.pdf', { type: 'application/pdf' })

    expect(armarFormularioEntrega('x').has('archivo')).toBe(false)
    expect(armarFormularioEntrega('x', pdf).get('archivo').name).toBe('trabajo.pdf')
  })
})

describe('validarArchivo', () => {
  it('acepta que no haya archivo', () => {
    expect(validarArchivo(null)).toBe('')
  })

  it('rechaza un archivo de más de 20 MB', () => {
    expect(validarArchivo({ size: TAMANO_MAXIMO_ENTREGA + 1 })).toMatch(/20 MB/)
  })

  it('acepta un archivo de exactamente 20 MB', () => {
    expect(validarArchivo({ size: TAMANO_MAXIMO_ENTREGA })).toBe('')
  })
})

describe('validarEntrega', () => {
  it('exige un archivo o un texto cuando no había nada entregado', () => {
    expect(validarEntrega({ texto: '', archivo: null, tieneArchivoPrevio: false })).toMatch(
      /Adjunta un archivo/,
    )
    expect(validarEntrega({ texto: '   ', archivo: null, tieneArchivoPrevio: false })).toMatch(
      /Adjunta un archivo/,
    )
  })

  it('acepta solo un texto o solo un archivo', () => {
    expect(
      validarEntrega({
        texto: 'https://drive.example/x',
        archivo: null,
        tieneArchivoPrevio: false,
      }),
    ).toBe('')
    expect(validarEntrega({ texto: '', archivo: { size: 10 }, tieneArchivoPrevio: false })).toBe('')
  })

  it('acepta un texto vacío si ya había un archivo entregado', () => {
    expect(validarEntrega({ texto: '', archivo: null, tieneArchivoPrevio: true })).toBe('')
  })
})
