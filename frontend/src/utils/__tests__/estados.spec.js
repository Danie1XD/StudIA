import { describe, expect, it } from 'vitest'
import {
  contarPorEstado,
  entregadaTarde,
  estadoDeTarea,
  formatearPuntos,
  prepararGrupos,
} from '../estados'

const AHORA = new Date('2030-06-15T12:00:00Z')
const PASADA = '2030-06-01T00:00:00Z'
const FUTURA = '2030-07-01T00:00:00Z'

describe('estadoDeTarea', () => {
  it('es calificada cuando el docente ya puso nota', () => {
    expect(estadoDeTarea({ fecha_limite: PASADA }, { estado: 'calificado' }, AHORA)).toBe(
      'calificada',
    )
  })

  it('es entregada si el alumno entregó y aún no hay nota, aunque ya haya vencido', () => {
    expect(estadoDeTarea({ fecha_limite: PASADA }, { estado: 'entregado' }, AHORA)).toBe(
      'entregada',
    )
  })

  it('es sin_entregar si venció y no hay entrega', () => {
    expect(estadoDeTarea({ fecha_limite: PASADA }, null, AHORA)).toBe('sin_entregar')
  })

  it('es pendiente si la fecha límite todavía no llega', () => {
    expect(estadoDeTarea({ fecha_limite: FUTURA }, null, AHORA)).toBe('pendiente')
  })

  it('es pendiente si la tarea no tiene fecha límite', () => {
    expect(estadoDeTarea({ fecha_limite: null }, null, AHORA)).toBe('pendiente')
  })
})

describe('entregadaTarde', () => {
  it('es verdadero si se entregó después de la fecha límite', () => {
    expect(
      entregadaTarde(
        { fecha_limite: '2030-06-01T00:00:00Z' },
        { entregada_at: '2030-06-02T00:00:00Z' },
      ),
    ).toBe(true)
  })

  it('es falso si se entregó a tiempo', () => {
    expect(
      entregadaTarde(
        { fecha_limite: '2030-06-02T00:00:00Z' },
        { entregada_at: '2030-06-01T00:00:00Z' },
      ),
    ).toBe(false)
  })

  it('es falso si falta la entrega o la fecha límite', () => {
    expect(entregadaTarde({ fecha_limite: null }, { entregada_at: PASADA })).toBe(false)
    expect(entregadaTarde({ fecha_limite: PASADA }, null)).toBe(false)
  })
})

describe('formatearPuntos', () => {
  it('quita los ceros sobrantes', () => {
    expect(formatearPuntos(95)).toBe('95')
    expect(formatearPuntos('80.50')).toBe('80.5')
  })

  it('redondea a dos decimales', () => {
    expect(formatearPuntos(86.667)).toBe('86.67')
  })

  it('muestra un guion si no hay valor', () => {
    expect(formatearPuntos(null)).toBe('—')
    expect(formatearPuntos(undefined)).toBe('—')
    expect(formatearPuntos('')).toBe('—')
    expect(formatearPuntos('abc')).toBe('—')
  })
})

const tarea = (id, estado, fecha_limite = null) => ({ id, estado, fecha_limite })

const datos = () => [
  {
    materia: { id: 1, nombre: 'Matemáticas' },
    tareas: [
      tarea(1, 'calificada', '2030-01-01T00:00:00Z'),
      tarea(2, 'pendiente', '2030-08-01T00:00:00Z'),
      tarea(3, 'pendiente', '2030-07-01T00:00:00Z'),
      tarea(4, 'pendiente', null),
      tarea(5, 'sin_entregar', '2030-06-01T00:00:00Z'),
    ],
  },
  {
    materia: { id: 2, nombre: 'Álgebra' },
    tareas: [tarea(6, 'entregada', '2030-07-01T00:00:00Z')],
  },
]

describe('contarPorEstado', () => {
  it('cuenta las tareas de todas las materias en cada estado', () => {
    expect(contarPorEstado(datos())).toEqual({
      pendiente: 3,
      sin_entregar: 1,
      entregada: 1,
      calificada: 1,
    })
  })

  it('ignora los estados desconocidos', () => {
    const grupos = [{ materia: { id: 1, nombre: 'IA' }, tareas: [tarea(1, 'inventado')] }]

    expect(contarPorEstado(grupos)).toEqual({
      pendiente: 0,
      sin_entregar: 0,
      entregada: 0,
      calificada: 0,
    })
  })
})

describe('prepararGrupos', () => {
  it('ordena por urgencia: pendientes por fecha (sin fecha al final), sin entregar, entregadas y calificadas', () => {
    const matematicas = prepararGrupos(datos()).find((g) => g.materia.id === 1)

    expect(matematicas.tareas.map((t) => t.id)).toEqual([3, 2, 4, 5, 1])
  })

  it('filtra por estado y quita las materias que se quedan sin tareas', () => {
    const grupos = prepararGrupos(datos(), 'entregada')

    expect(grupos).toHaveLength(1)
    expect(grupos[0].materia.nombre).toBe('Álgebra')
    expect(grupos[0].tareas.map((t) => t.id)).toEqual([6])
  })

  it('sin filtro devuelve todas las tareas', () => {
    const total = prepararGrupos(datos()).reduce((suma, g) => suma + g.tareas.length, 0)

    expect(total).toBe(6)
  })

  it('ordena las materias por nombre', () => {
    expect(prepararGrupos(datos()).map((g) => g.materia.nombre)).toEqual(['Álgebra', 'Matemáticas'])
  })

  it('no modifica los datos originales', () => {
    const original = datos()
    const copia = JSON.parse(JSON.stringify(original))

    prepararGrupos(original, 'pendiente')

    expect(original).toEqual(copia)
  })
})
