import { describe, expect, it } from 'vitest'
import {
  armarDatosRubrica,
  criterioVacio,
  desdeRubrica,
  MAX_CRITERIOS,
  mover,
  puntosDe,
  totalPuntos,
  validarRubrica,
} from '../rubricas'

const criterio = (nombre, puntos, descripcion = '') => ({
  ...criterioVacio(),
  nombre,
  puntos,
  descripcion,
})

describe('puntosDe', () => {
  it('acepta enteros entre 1 y 1000, con espacios alrededor', () => {
    expect(puntosDe('1')).toBe(1)
    expect(puntosDe(' 25 ')).toBe(25)
    expect(puntosDe('1000')).toBe(1000)
  })

  it('rechaza vacío, cero, decimales, negativos, texto y más de 1000', () => {
    for (const valor of ['', '   ', '0', '2.5', '-3', 'abc', '1001', '1e2', null, undefined]) {
      expect(puntosDe(valor)).toBeNull()
    }
  })
})

describe('totalPuntos', () => {
  it('suma solo los criterios con puntos válidos', () => {
    expect(totalPuntos([criterio('A', '60'), criterio('B', 'abc'), criterio('C', '40')])).toBe(100)
  })
})

describe('validarRubrica', () => {
  it('acepta una rúbrica completa', () => {
    const resultado = validarRubrica({
      nombre: 'Mapa mental',
      descripcion: '',
      criterios: [criterio('Contenido', '60'), criterio('Organización', '40')],
    })

    expect(resultado.valido).toBe(true)
    expect(resultado.porCriterio).toEqual([{}, {}])
  })

  it('señala qué falta y en qué criterio', () => {
    const resultado = validarRubrica({
      nombre: '  ',
      descripcion: '',
      criterios: [criterio('', '60'), criterio('Orden', '0')],
    })

    expect(resultado.valido).toBe(false)
    expect(resultado.nombre).toMatch(/obligatorio/)
    expect(resultado.porCriterio[0].nombre).toBe('Cada criterio necesita un nombre.')
    expect(resultado.porCriterio[0].puntos).toBeUndefined()
    expect(resultado.porCriterio[1].puntos).toMatch(/entero entre 1 y 1000/)
  })

  it('rechaza más de 20 criterios', () => {
    const criterios = Array.from({ length: MAX_CRITERIOS + 1 }, (_, i) => criterio(`C${i}`, '5'))

    expect(validarRubrica({ nombre: 'X', descripcion: '', criterios }).criterios).toMatch(
      /más de 20/,
    )
  })

  it('rechaza una rúbrica sin criterios', () => {
    expect(validarRubrica({ nombre: 'X', descripcion: '', criterios: [] }).criterios).toMatch(
      /al menos un criterio/,
    )
  })
})

describe('armarDatosRubrica', () => {
  it('quita espacios, manda null en las descripciones vacías y los puntos como número', () => {
    const datos = armarDatosRubrica({
      nombre: '  Mapa  ',
      descripcion: '   ',
      criterios: [criterio('  Contenido ', ' 60 ', ' Cubre lo básico '), criterio('Orden', '40')],
    })

    expect(datos).toEqual({
      nombre: 'Mapa',
      descripcion: null,
      criterios: [
        { nombre: 'Contenido', descripcion: 'Cubre lo básico', puntos: 60 },
        { nombre: 'Orden', descripcion: null, puntos: 40 },
      ],
    })
  })
})

describe('mover', () => {
  it('intercambia posiciones sin modificar la lista original', () => {
    const original = ['a', 'b', 'c']

    expect(mover(original, 0, 1)).toEqual(['b', 'a', 'c'])
    expect(mover(original, 2, -1)).toEqual(['a', 'c', 'b'])
    expect(original).toEqual(['a', 'b', 'c'])
  })

  it('no cambia nada si el movimiento se sale de la lista', () => {
    expect(mover(['a', 'b'], 0, -1)).toEqual(['a', 'b'])
    expect(mover(['a', 'b'], 1, 1)).toEqual(['a', 'b'])
    expect(mover(['a'], 5, 1)).toEqual(['a'])
  })
})

describe('desdeRubrica', () => {
  it('convierte la rúbrica de la API en el formulario, con puntos como texto y claves únicas', () => {
    const form = desdeRubrica({
      nombre: 'Mapa',
      descripcion: null,
      criterios: [
        { id: 1, nombre: 'Contenido', descripcion: null, puntos: 60, orden: 1 },
        { id: 2, nombre: 'Orden', descripcion: 'x', puntos: 40, orden: 2 },
      ],
    })

    expect(form.descripcion).toBe('')
    expect(form.criterios.map((c) => c.puntos)).toEqual(['60', '40'])
    expect(form.criterios[0].descripcion).toBe('')
    expect(new Set(form.criterios.map((c) => c.clave)).size).toBe(2)
  })

  it('deja un criterio en blanco si la rúbrica no trae ninguno', () => {
    const form = desdeRubrica({ nombre: 'X', criterios: [] })

    expect(form.criterios).toHaveLength(1)
    expect(form.criterios[0].nombre).toBe('')
  })
})
