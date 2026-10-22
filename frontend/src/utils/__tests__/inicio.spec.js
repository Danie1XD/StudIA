import { describe, expect, it } from 'vitest'
import { tonoDeMateria } from '../colores'
import {
  filtrarPorTexto,
  iniciales,
  nombreParaSaludo,
  porRevisarDocente,
  progresoPorMateria,
  proximasTareas,
  resumenDocentePorMateria,
} from '../inicio'

describe('iniciales', () => {
  it('toma la inicial de las dos primeras palabras, o dos letras si es una sola', () => {
    expect(iniciales('Daniel Cumplido García')).toBe('DC')
    expect(iniciales('  ana  ')).toBe('AN')
    expect(iniciales('profesor agustín')).toBe('PA')
    expect(iniciales('😀 Ana')).toBe('😀A')
  })

  it('devuelve un signo de interrogación si no hay nombre', () => {
    expect(iniciales('')).toBe('?')
    expect(iniciales(null)).toBe('?')
  })
})

describe('nombreParaSaludo', () => {
  it('saluda al alumno por su primer nombre y a los demás por su nombre completo', () => {
    expect(nombreParaSaludo({ rol: 'alumno', nombre: 'Daniel Cumplido García' })).toBe('Daniel')
    expect(nombreParaSaludo({ rol: 'docente', nombre: 'Profesor Agustín' })).toBe(
      'Profesor Agustín',
    )
    expect(nombreParaSaludo(null)).toBe('')
  })
})

describe('filtrarPorTexto', () => {
  const items = [
    { n: 'Matemáticas II', p: 'Ana' },
    { n: 'Física', p: 'Beto' },
  ]
  const campos = [(i) => i.n, (i) => i.p]

  it('ignora mayúsculas y tildes y busca en todos los campos', () => {
    expect(filtrarPorTexto(items, 'MATEMATICAS', campos)).toEqual([items[0]])
    expect(filtrarPorTexto(items, 'beto', campos)).toEqual([items[1]])
  })

  it('con búsqueda vacía devuelve todo, y sin coincidencias devuelve nada', () => {
    expect(filtrarPorTexto(items, '   ', campos)).toEqual(items)
    expect(filtrarPorTexto(items, 'xyz', campos)).toEqual([])
  })
})

describe('progresoPorMateria', () => {
  it('cuenta como hecho lo entregado o calificado, e incluye las materias sin tareas', () => {
    const materias = [
      { id: 1, nombre: 'IA' },
      { id: 2, nombre: 'Álgebra' },
    ]
    const grupos = [
      {
        materia: { id: 1, nombre: 'IA' },
        tareas: [
          { estado: 'calificada' },
          { estado: 'entregada' },
          { estado: 'pendiente' },
          { estado: 'sin_entregar' },
        ],
      },
    ]

    const resultado = progresoPorMateria(materias, grupos)

    expect(resultado[0]).toMatchObject({ total: 4, hechas: 2, porcentaje: 50 })
    expect(resultado[1]).toMatchObject({ total: 0, hechas: 0, porcentaje: null })
  })
})

describe('proximasTareas', () => {
  const grupos = [
    {
      materia: { id: 1, nombre: 'IA' },
      tareas: [
        { id: 1, estado: 'pendiente', fecha_limite: '2030-03-01T00:00:00Z' },
        { id: 2, estado: 'pendiente', fecha_limite: '2030-01-01T00:00:00Z' },
        { id: 3, estado: 'entregada', fecha_limite: '2030-01-02T00:00:00Z' },
        { id: 4, estado: 'pendiente', fecha_limite: null },
      ],
    },
    {
      materia: { id: 2, nombre: 'Álgebra' },
      tareas: [{ id: 5, estado: 'pendiente', fecha_limite: '2030-02-01T00:00:00Z' }],
    },
  ]

  it('toma solo las pendientes con fecha, la más próxima primero', () => {
    expect(proximasTareas(grupos).map((t) => t.id)).toEqual([2, 5, 1])
  })

  it('respeta el límite y agrega la materia a cada tarea', () => {
    expect(proximasTareas(grupos, 2).map((t) => t.id)).toEqual([2, 5])
    expect(proximasTareas(grupos)[0].materia.nombre).toBe('IA')
  })
})

describe('panel del docente', () => {
  const materias = [
    { id: 1, nombre: 'IA' },
    { id: 2, nombre: 'Álgebra' },
  ]

  it('resumenDocentePorMateria suma las entregas y calcula lo calificado', () => {
    const grupos = [
      {
        materia: { id: 1, nombre: 'IA' },
        tareas: [
          { entregas_count: 4, calificadas_count: 1 },
          { entregas_count: 2, calificadas_count: 2 },
        ],
      },
    ]

    const resultado = resumenDocentePorMateria(materias, grupos)

    expect(resultado[0]).toMatchObject({
      tareas: 2,
      entregas: 6,
      calificadas: 3,
      porRevisar: 3,
      porcentaje: 50,
    })
    expect(resultado[1]).toMatchObject({ tareas: 0, entregas: 0, porRevisar: 0, porcentaje: null })
  })

  it('porRevisarDocente lista primero las tareas con más entregas por revisar', () => {
    const grupos = [
      {
        materia: { id: 1, nombre: 'Matemáticas' },
        tareas: [
          { id: 1, titulo: 'A', fecha_limite: null, entregas_count: 3, calificadas_count: 3 },
          { id: 2, titulo: 'B', fecha_limite: null, entregas_count: 5, calificadas_count: 1 },
        ],
      },
      {
        materia: { id: 2, nombre: 'Física' },
        tareas: [
          { id: 3, titulo: 'C', fecha_limite: null, entregas_count: 2, calificadas_count: 0 },
        ],
      },
    ]

    expect(porRevisarDocente(grupos).map((t) => [t.id, t.por_revisar])).toEqual([
      [2, 4],
      [3, 2],
    ])
    expect(porRevisarDocente(grupos, 1).map((t) => [t.id, t.por_revisar])).toEqual([[2, 4]])
  })
})

describe('tonoDeMateria', () => {
  it('da siempre el mismo tono para la misma materia y cicla cada cuatro', () => {
    expect(tonoDeMateria(1)).toBe(tonoDeMateria(1))
    expect(tonoDeMateria(1)).not.toBe(tonoDeMateria(2))
    expect(tonoDeMateria(5)).toBe(tonoDeMateria(1))
  })

  it('no se rompe con ids raros', () => {
    for (const id of [undefined, null, 'abc', '7', -3]) {
      expect(tonoDeMateria(id)).toMatch(/^tono-[0-3]$/)
    }
  })
})
