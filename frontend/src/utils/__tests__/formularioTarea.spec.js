// @vitest-environment jsdom
import { describe, expect, it } from 'vitest'
import { armarFormularioTarea } from '../formularioTarea'

const formulario = (extra = {}) => ({
  titulo: '  Actividad 2  ',
  instrucciones: ' Haz una infografía. ',
  fecha_limite: '2030-01-01T23:59',
  puntaje_maximo: '50',
  parcial: '2',
  tipo_evaluacion: 'visual',
  seccion_id: '3',
  rubrica_id: '',
  ...extra,
})

describe('armarFormularioTarea', () => {
  it('arma los campos al crear, sin espacios sobrantes', () => {
    const datos = armarFormularioTarea(formulario())

    expect(datos.get('titulo')).toBe('Actividad 2')
    expect(datos.get('instrucciones')).toBe('Haz una infografía.')
    expect(datos.get('puntaje_maximo')).toBe('50')
    expect(datos.get('parcial')).toBe('2')
    expect(datos.get('tipo_evaluacion')).toBe('visual')
    expect(datos.get('seccion_id')).toBe('3')
    expect(datos.has('_method')).toBe(false)
  })

  it('manda la fecha límite como ISO en UTC', () => {
    const datos = armarFormularioTarea(formulario())

    expect(datos.get('fecha_limite')).toBe(new Date('2030-01-01T23:59').toISOString())
  })

  it('manda vacío, y no omite, lo que quedó sin elegir, para que el servidor lo borre', () => {
    const datos = armarFormularioTarea(
      formulario({ fecha_limite: '', seccion_id: '', rubrica_id: '' }),
    )

    for (const campo of ['fecha_limite', 'seccion_id', 'rubrica_id']) {
      expect(datos.has(campo)).toBe(true)
      expect(datos.get(campo)).toBe('')
    }
  })

  it('comparte la rúbrica solo si hay una elegida y el docente lo pidió', () => {
    const compartida = armarFormularioTarea(formulario({ rubrica_id: '4', rubrica_visible: true }))
    const sinMarcar = armarFormularioTarea(formulario({ rubrica_id: '4', rubrica_visible: false }))
    const sinRubrica = armarFormularioTarea(formulario({ rubrica_id: '', rubrica_visible: true }))

    expect(compartida.get('rubrica_visible')).toBe('1')
    expect(sinMarcar.get('rubrica_visible')).toBe('0')
    expect(sinRubrica.get('rubrica_visible')).toBe('0')
  })

  it('incluye el PDF cuando se elige uno', () => {
    const pdf = new File(['%PDF-1.4'], 'tarea.pdf', { type: 'application/pdf' })

    const datos = armarFormularioTarea(formulario(), { archivo: pdf })

    expect(datos.get('archivo').name).toBe('tarea.pdf')
  })

  it('al editar agrega _method=PUT, porque PHP no lee archivos en un PUT', () => {
    const datos = armarFormularioTarea(formulario(), { edicion: true })

    expect(datos.get('_method')).toBe('PUT')
  })
  it('acepta el puntaje como número, porque un campo de tipo número lo entrega así', () => {
    const datos = armarFormularioTarea(formulario({ puntaje_maximo: 50, parcial: 2 }))

    expect(datos.get('puntaje_maximo')).toBe('50')
  })

  it('quitar_archivo solo se manda al editar y si no hay un archivo nuevo', () => {
    const pdf = new File(['x'], 'nuevo.pdf', { type: 'application/pdf' })

    const editandoYQuitando = armarFormularioTarea(formulario(), {
      edicion: true,
      quitarArchivo: true,
    })
    expect(editandoYQuitando.get('quitar_archivo')).toBe('1')

    const creando = armarFormularioTarea(formulario(), { quitarArchivo: true })
    expect(creando.has('quitar_archivo')).toBe(false)

    const conNuevo = armarFormularioTarea(formulario(), {
      edicion: true,
      quitarArchivo: true,
      archivo: pdf,
    })
    expect(conNuevo.has('quitar_archivo')).toBe(false)
    expect(conNuevo.has('archivo')).toBe(true)
  })
})
