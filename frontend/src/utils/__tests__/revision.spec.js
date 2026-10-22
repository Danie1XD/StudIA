import { describe, expect, it } from 'vitest'
import {
  armarRetroalimentacion,
  enlaceDe,
  infoEstadoFila,
  MAX_RETROALIMENTACION,
  ordenarFilas,
  prepararGruposDocente,
  resumirFilas,
  siguientePorRevisar,
  validarCalificacion,
} from '../revision'

const fila = (id, nombre, estado, entregada_at = null) => ({
  alumno: { id, nombre },
  estado,
  entrega: estado === 'sin_entregar' ? null : { id: id * 10, entregada_at },
})

const filasEjemplo = () => [
  fila(1, 'Zoe', 'calificado'),
  fila(2, 'Ana', 'sin_entregar'),
  fila(3, 'Luis', 'entregado', '2030-01-03T00:00:00Z'),
  fila(4, 'Mia', 'entregado', '2030-01-01T00:00:00Z'),
]

describe('ordenarFilas', () => {
  it('pone primero lo por revisar (la entrega más antigua arriba), luego lo calificado y al final lo sin entregar', () => {
    expect(ordenarFilas(filasEjemplo()).map((f) => f.alumno.nombre)).toEqual([
      'Mia',
      'Luis',
      'Zoe',
      'Ana',
    ])
  })

  it('ordena por nombre dentro de cada grupo', () => {
    const calificadas = [
      fila(1, 'Zoe', 'calificado'),
      fila(2, 'Ana', 'calificado'),
      fila(3, 'Édgar', 'calificado'),
    ]

    expect(ordenarFilas(calificadas).map((f) => f.alumno.nombre)).toEqual(['Ana', 'Édgar', 'Zoe'])
  })

  it('no modifica el arreglo original', () => {
    const original = filasEjemplo()
    const copia = [...original]

    ordenarFilas(original)

    expect(original).toEqual(copia)
  })
})

describe('resumirFilas', () => {
  it('cuenta cada estado', () => {
    expect(resumirFilas(filasEjemplo())).toEqual({
      total: 4,
      porRevisar: 2,
      calificadas: 1,
      sinEntregar: 1,
    })
  })
})

describe('siguientePorRevisar', () => {
  it('devuelve la entrega por revisar más antigua que no sea la actual', () => {
    const siguiente = siguientePorRevisar(filasEjemplo(), 40) // 40 es la entrega de Mia

    expect(siguiente.alumno.nombre).toBe('Luis')
  })

  it('devuelve null si no queda ninguna por revisar', () => {
    const filas = [
      fila(1, 'Zoe', 'calificado'),
      fila(4, 'Mia', 'entregado', '2030-01-01T00:00:00Z'),
    ]

    expect(siguientePorRevisar(filas, 40)).toBeNull()
  })
})

describe('armarRetroalimentacion', () => {
  it('junta el mensaje, los puntos fuertes y las áreas de mejora', () => {
    const evaluacion = {
      retroalimentacion: {
        retroalimentacion_general: 'Hola, Ana. Buen trabajo.',
        puntos_fuertes: 'Buenos ejemplos.',
        areas_mejora: 'Falta profundidad.',
        criterios: [],
      },
    }

    expect(armarRetroalimentacion(evaluacion)).toBe(
      'Hola, Ana. Buen trabajo.\n\nPuntos fuertes:\nBuenos ejemplos.\n\nÁreas de mejora:\nFalta profundidad.',
    )
  })

  it('omite lo que la IA no devolvió', () => {
    const evaluacion = { retroalimentacion: { retroalimentacion_general: 'Solo esto.' } }

    expect(armarRetroalimentacion(evaluacion)).toBe('Solo esto.')
  })

  it('recorta el texto al máximo que acepta el servidor', () => {
    const evaluacion = { retroalimentacion: { retroalimentacion_general: 'a'.repeat(6000) } }
    const texto = armarRetroalimentacion(evaluacion)

    expect(texto).toHaveLength(MAX_RETROALIMENTACION)
    expect(texto.endsWith('…')).toBe(true)
  })

  it('devuelve texto vacío si no hay evaluación', () => {
    expect(armarRetroalimentacion(null)).toBe('')
    expect(armarRetroalimentacion({})).toBe('')
  })
})

describe('validarCalificacion', () => {
  it('exige un valor', () => {
    expect(validarCalificacion('', 100)).toMatch(/obligatoria/)
    expect(validarCalificacion('   ', 100)).toMatch(/obligatoria/)
    expect(validarCalificacion(null, 100)).toMatch(/obligatoria/)
  })

  it('rechaza texto, negativos y más de dos decimales', () => {
    for (const valor of ['abc', '-5', '8.555', '8,5', '1e2']) {
      expect(validarCalificacion(valor, 100)).toMatch(/número válido/)
    }
  })

  it('rechaza lo que supera el puntaje máximo', () => {
    expect(validarCalificacion('101', 100)).toMatch(/superar el puntaje máximo de 100/)
    expect(validarCalificacion('100.01', 100)).toMatch(/superar/)
  })

  it('acepta el límite y valores con uno o dos decimales', () => {
    for (const valor of ['0', '100', '85.5', '85.25']) {
      expect(validarCalificacion(valor, 100)).toBe('')
    }
  })

  it('acepta un número, porque un campo de tipo número lo entrega así', () => {
    expect(validarCalificacion(85.5, 100)).toBe('')
    expect(validarCalificacion(0, 100)).toBe('')
  })
})

describe('enlaceDe', () => {
  it('devuelve la dirección si el texto es solo un enlace http o https', () => {
    expect(enlaceDe(' https://drive.google.com/mi-trabajo ')).toBe(
      'https://drive.google.com/mi-trabajo',
    )
    expect(enlaceDe('http://ejemplo.com/a')).toBe('http://ejemplo.com/a')
  })

  it('ignora los esquemas peligrosos o distintos de http', () => {
    for (const texto of [
      'javascript:alert(1)',
      'ftp://archivos.ejemplo.com/x',
      'data:text/html,hola',
      'file:///etc/passwd',
    ]) {
      expect(enlaceDe(texto)).toBeNull()
    }
  })

  it('no trata como enlace un texto con más palabras ni uno sin esquema', () => {
    expect(enlaceDe('revisa https://drive.google.com/x')).toBeNull()
    expect(enlaceDe('drive.google.com/x')).toBeNull()
  })

  it('devuelve null si no hay texto', () => {
    expect(enlaceDe(null)).toBeNull()
    expect(enlaceDe('')).toBeNull()
    expect(enlaceDe('   ')).toBeNull()
  })
})

const tareaDocente = (id, entregas, calificadas, fecha = null) => ({
  id,
  titulo: `T${id}`,
  fecha_limite: fecha,
  entregas_count: entregas,
  calificadas_count: calificadas,
})

const gruposDocente = () => [
  {
    materia: { id: 1, nombre: 'Matemáticas' },
    tareas: [
      tareaDocente(1, 3, 3, '2030-01-01T00:00:00Z'),
      tareaDocente(2, 4, 1, '2030-03-01T00:00:00Z'),
      tareaDocente(3, 2, 0, '2030-02-01T00:00:00Z'),
      tareaDocente(4, 0, 0),
    ],
  },
  { materia: { id: 2, nombre: 'Álgebra' }, tareas: [] },
  { materia: { id: 3, nombre: 'Física' }, tareas: [tareaDocente(5, 1, 0, '2030-01-01T00:00:00Z')] },
]

describe('prepararGruposDocente', () => {
  it('calcula las entregas por revisar de cada tarea', () => {
    const matematicas = prepararGruposDocente(gruposDocente()).find((g) => g.materia.id === 1)
    const porRevisar = Object.fromEntries(matematicas.tareas.map((t) => [t.id, t.por_revisar]))

    expect(porRevisar).toEqual({ 1: 0, 2: 3, 3: 2, 4: 0 })
  })

  it('pone primero las tareas con más por revisar y, a igualdad, la fecha más cercana (sin fecha al final)', () => {
    const matematicas = prepararGruposDocente(gruposDocente()).find((g) => g.materia.id === 1)

    expect(matematicas.tareas.map((t) => t.id)).toEqual([2, 3, 1, 4])
  })

  it('ordena las materias por nombre, quita las vacías y no modifica los datos', () => {
    const original = gruposDocente()
    const grupos = prepararGruposDocente(original)

    expect(grupos.map((g) => g.materia.nombre)).toEqual(['Física', 'Matemáticas'])
    expect(original[0].tareas[0]).not.toHaveProperty('por_revisar')
  })
})

describe('infoEstadoFila', () => {
  it('da el texto y la clase de cada estado, y trata lo desconocido como sin entregar', () => {
    expect(infoEstadoFila('entregado')).toEqual({ texto: 'Por revisar', clase: 'insignia--aviso' })
    expect(infoEstadoFila('calificado').texto).toBe('Calificada')
    expect(infoEstadoFila('raro').texto).toBe('Sin entregar')
  })
})
