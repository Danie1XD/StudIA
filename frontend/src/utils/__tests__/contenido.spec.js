import { describe, expect, it } from 'vitest'
import { agruparContenido } from '../contenido'

const tarea = (id, creada) => ({ id, titulo: `T${id}`, created_at: creada })
const anuncio = (id, creada) => ({ id, contenido: `A${id}`, created_at: creada })

const contenido = {
  secciones: [
    {
      id: 1,
      nombre: 'Unidad 1',
      tareas: [tarea(1, '2030-01-01T10:00:00Z')],
      anuncios: [anuncio(1, '2030-01-03T10:00:00Z')],
    },
    { id: 2, nombre: 'Unidad 2', tareas: [], anuncios: [] },
  ],
  sin_seccion: { tareas: [tarea(2, '2030-01-02T10:00:00Z')], anuncios: [] },
}

describe('agruparContenido', () => {
  it('pone primero lo general y luego las secciones en el orden del servidor', () => {
    const grupos = agruparContenido(contenido)

    expect(grupos.map((g) => g.nombre)).toEqual(['General', 'Unidad 1'])
    expect(grupos.map((g) => g.clave)).toEqual(['general', 'seccion-1'])
  })

  it('ordena lo más reciente primero, mezclando tareas y anuncios', () => {
    const unidad1 = agruparContenido(contenido).find((g) => g.clave === 'seccion-1')

    expect(unidad1.items.map((i) => i.clave)).toEqual(['anuncio-1', 'tarea-1'])
  })

  it('marca cada elemento con su tipo', () => {
    const unidad1 = agruparContenido(contenido).find((g) => g.clave === 'seccion-1')

    expect(unidad1.items.map((i) => i.tipo)).toEqual(['anuncio', 'tarea'])
  })

  it('oculta las secciones vacías por defecto (vista del alumno)', () => {
    const claves = agruparContenido(contenido).map((g) => g.clave)

    expect(claves).not.toContain('seccion-2')
  })

  it('muestra las secciones vacías si se pide (vista del docente)', () => {
    const grupos = agruparContenido(contenido, { incluirVacias: true })
    const unidad2 = grupos.find((g) => g.clave === 'seccion-2')

    expect(unidad2).toBeDefined()
    expect(unidad2.items).toEqual([])
  })

  it('omite lo general cuando no tiene contenido', () => {
    const sinGeneral = { ...contenido, sin_seccion: { tareas: [], anuncios: [] } }

    expect(agruparContenido(sinGeneral).map((g) => g.clave)).toEqual(['seccion-1'])
    expect(agruparContenido(null)).toEqual([])
  })

  it('cada grupo trae el id de su sección, y "General" no tiene', () => {
    const grupos = agruparContenido(contenido)

    expect(grupos.map((g) => g.seccionId)).toEqual([null, 1])
  })
})
