import { describe, expect, it } from 'vitest'
import {
  detalleParcial,
  filasParaExportar,
  hayCambios,
  leerParcial,
  promedioDelGrupo,
  promedioFinal,
  textosIniciales,
  validarParcial,
  valorExacto,
  valoresSimulados,
} from '../promedios'

// El alumno del recorrido del paso 12: P1 130 de 150, P2 0 de 100 y P3 sin tareas contadas
const parciales = [
  { parcial: 1, promedio: 86.67, puntos_obtenidos: 130, puntos_posibles: 150 },
  { parcial: 2, promedio: 0, puntos_obtenidos: 0, puntos_posibles: 100 },
  { parcial: 3, promedio: null, puntos_obtenidos: 0, puntos_posibles: 0 },
]

describe('leerParcial', () => {
  it('lee números de 0 a 100 con hasta dos decimales', () => {
    expect(leerParcial('86.5')).toBe(86.5)
    expect(leerParcial(' 100 ')).toBe(100)
    expect(leerParcial('0')).toBe(0)
  })

  it('devuelve null con vacío, texto, coma decimal, negativos, de más de 100 o con más decimales', () => {
    for (const valor of ['', '  ', 'abc', '8,5', '101', '-1', '1.234', null, undefined]) {
      expect(leerParcial(valor)).toBeNull()
    }
  })
})

describe('validarParcial', () => {
  it('acepta un campo vacío y un valor correcto', () => {
    expect(validarParcial('')).toBe('')
    expect(validarParcial('   ')).toBe('')
    expect(validarParcial('90')).toBe('')
  })

  it('explica qué falla', () => {
    expect(validarParcial('abc')).toMatch(/número válido/)
    expect(validarParcial('101')).toMatch(/100/)
  })
})

describe('promedioFinal', () => {
  it('promedia los valores que existen y redondea a dos decimales', () => {
    expect(promedioFinal([80, null, 90.5])).toBe(85.25)
    expect(promedioFinal([10, 10, 11])).toBe(10.33)
    expect(promedioFinal([100, 50, 0])).toBe(50)
  })

  it('devuelve null si no hay ningún valor', () => {
    expect(promedioFinal([])).toBeNull()
    expect(promedioFinal([null, null])).toBeNull()
    expect(promedioFinal(['a', Number.NaN])).toBeNull()
  })
})

describe('hayCambios', () => {
  it('detecta si el alumno cambió algún parcial', () => {
    expect(hayCambios(parciales, ['86.67', '0', ''])).toBe(false)
    expect(hayCambios(parciales, ['86.67', '0', '90'])).toBe(true)
    expect(hayCambios(parciales, ['86.67', '', ''])).toBe(true)
  })

  it('ignora los espacios en un campo vacío', () => {
    expect(hayCambios(parciales, ['86.67', '0', '  '])).toBe(false)
  })
})

describe('textosIniciales', () => {
  it('muestra el promedio real de cada parcial y deja vacío el que no tiene', () => {
    expect(textosIniciales(parciales)).toEqual(['86.67', '0', ''])
  })
})

describe('valorExacto', () => {
  it('calcula el parcial sin redondear, o null si no hay puntos posibles', () => {
    expect(valorExacto(parciales[0])).toBeCloseTo(86.6667, 3)
    expect(valorExacto(parciales[1])).toBe(0)
    expect(valorExacto(parciales[2])).toBeNull()
  })
})

describe('valoresSimulados', () => {
  it('sin tocar nada usa los valores exactos y coincide con el promedio del servidor', () => {
    const textos = textosIniciales(parciales)

    // Con los valores ya redondeados (86.67) saldría 43.34; con los exactos sale 43.33
    expect(promedioFinal([86.67, 0])).toBe(43.34)
    expect(promedioFinal(valoresSimulados(parciales, textos))).toBe(43.33)
  })

  it('con cambios usa lo escrito, y un campo vacío o inválido no cuenta', () => {
    expect(promedioFinal(valoresSimulados(parciales, ['86.67', '0', '90']))).toBe(58.89)
    expect(promedioFinal(valoresSimulados(parciales, ['86.67', '', '']))).toBe(86.67)
    expect(promedioFinal(valoresSimulados(parciales, ['86.67', '0', 'abc']))).toBe(43.33)
    expect(promedioFinal(valoresSimulados(parciales, ['', '', '']))).toBeNull()
    expect(promedioFinal(valoresSimulados(parciales, ['100', '100', '100']))).toBe(100)
  })
})

describe('promedioDelGrupo', () => {
  it('promedia los finales que existen', () => {
    const filas = [{ promedio_final: 80 }, { promedio_final: null }, { promedio_final: 90.5 }]

    expect(promedioDelGrupo(filas)).toBe(85.25)
  })

  it('devuelve null si nadie tiene calificaciones', () => {
    expect(promedioDelGrupo([{ promedio_final: null }])).toBeNull()
    expect(promedioDelGrupo([])).toBeNull()
  })
})

describe('detalleParcial', () => {
  it('dice cuántas tareas del parcial cuentan, en singular o plural', () => {
    expect(detalleParcial({ tareas_contadas: 2, tareas_total: 3 })).toBe('2 de 3 tareas contadas')
    expect(detalleParcial({ tareas_contadas: 1, tareas_total: 1 })).toBe('1 de 1 tarea contada')
  })

  it('maneja un parcial sin tareas o sin datos', () => {
    expect(detalleParcial({ tareas_contadas: 0, tareas_total: 0 })).toBe('Sin tareas')
    expect(detalleParcial(undefined)).toBe('')
  })
})

describe('filasParaExportar', () => {
  it('arma el encabezado y una fila por alumno, con vacío donde no hay datos', () => {
    const filas = [
      {
        alumno: { nombre: 'Ana' },
        parciales: [
          { parcial: 1, promedio: 86.67 },
          { parcial: 2, promedio: null },
          { parcial: 3, promedio: null },
        ],
        promedio_final: 86.67,
      },
    ]

    expect(filasParaExportar(filas)).toEqual([
      ['Alumno', 'Parcial 1', 'Parcial 2', 'Parcial 3', 'Promedio final'],
      ['Ana', 86.67, '', '', 86.67],
    ])
  })
})
