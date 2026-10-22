import { describe, expect, it } from 'vitest'
import { armarCsv, celdaCsv } from '../csv'

describe('celdaCsv', () => {
  it('deja intacto el texto normal, pasa los números a texto y convierte null en vacío', () => {
    expect(celdaCsv('Ana')).toBe('Ana')
    expect(celdaCsv(86.5)).toBe('86.5')
    expect(celdaCsv(null)).toBe('')
    expect(celdaCsv(undefined)).toBe('')
  })

  it('encierra entre comillas lo que tiene comas, comillas o saltos de línea', () => {
    expect(celdaCsv('Pérez, Ana')).toBe('"Pérez, Ana"')
    expect(celdaCsv('dijo "hola"')).toBe('"dijo ""hola"""')
    expect(celdaCsv('línea 1\nlínea 2')).toBe('"línea 1\nlínea 2"')
  })

  it('neutraliza las fórmulas para que Excel no las ejecute', () => {
    expect(celdaCsv('=HYPERLINK("http://malo")')).toBe(`"'=HYPERLINK(""http://malo"")"`)

    for (const formula of ['+1+1', '-2+3', '@SUMA(A1)']) {
      expect(celdaCsv(formula)).toBe(`'${formula}`)
    }
  })
})

describe('armarCsv', () => {
  it('empieza con BOM, separa con comas y termina cada fila con salto de línea de Windows', () => {
    expect(
      armarCsv([
        ['Alumno', 'Final'],
        ['Ana', 90],
      ]),
    ).toBe('\uFEFFAlumno,Final\r\nAna,90\r\n')
  })
})
