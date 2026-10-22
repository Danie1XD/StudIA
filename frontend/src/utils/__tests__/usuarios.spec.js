import { describe, expect, it } from 'vitest'
import { armarDatosUsuario, armarParametros, textoMaterias, validarUsuario } from '../usuarios'

const nuevo = {
  nombre: 'Profa. López',
  email: 'lopez@studia.com',
  rol: 'docente',
  password: 'clave-segura-1',
}

describe('armarParametros', () => {
  it('omite los filtros vacíos y pide 15 por página', () => {
    expect(armarParametros()).toEqual({ por_pagina: 15 })
    expect(armarParametros({ pagina: 1, buscar: '   ' })).toEqual({ por_pagina: 15 })
  })

  it('incluye los filtros que se usan, también "desactivados", y la página desde la 2', () => {
    expect(armarParametros({ rol: 'docente', activo: '0', buscar: '  ana ', pagina: 3 })).toEqual({
      por_pagina: 15,
      rol: 'docente',
      activo: '0',
      buscar: 'ana',
      page: 3,
    })
  })
})

describe('validarUsuario', () => {
  it('acepta un usuario nuevo completo', () => {
    expect(validarUsuario(nuevo)).toEqual({})
  })

  it('al crear exige nombre, correo válido, rol y contraseña de al menos 8 caracteres', () => {
    expect(validarUsuario({ nombre: '', email: 'x', rol: 'rector', password: '' })).toEqual({
      nombre: 'El nombre es obligatorio.',
      email: 'Escribe un correo válido.',
      rol: 'Elige un rol.',
      password: 'La contraseña es obligatoria.',
    })
    expect(validarUsuario({ ...nuevo, password: '123' }).password).toMatch(/al menos 8/)
  })

  it('al editar la contraseña es opcional, pero si se escribe debe tener 8 caracteres', () => {
    expect(validarUsuario({ ...nuevo, password: '' }, { edicion: true })).toEqual({})
    expect(validarUsuario({ ...nuevo, password: '123' }, { edicion: true }).password).toMatch(
      /al menos 8/,
    )
  })

  it('si es el propio usuario no se valida ni el rol ni la contraseña', () => {
    const propio = { nombre: 'Yo', email: 'yo@studia.com', rol: '', password: '' }

    expect(validarUsuario(propio, { edicion: true, propio: true })).toEqual({})
  })
})

describe('armarDatosUsuario', () => {
  it('al crear manda todo, sin espacios sobrantes', () => {
    const form = {
      nombre: '  Ana ',
      email: ' ana@studia.com ',
      rol: 'alumno',
      password: 'clave-segura-1',
    }

    expect(armarDatosUsuario(form)).toEqual({
      nombre: 'Ana',
      email: 'ana@studia.com',
      rol: 'alumno',
      password: 'clave-segura-1',
    })
  })

  it('al editar solo manda la contraseña si se escribió', () => {
    expect(armarDatosUsuario({ ...nuevo, password: '' }, { edicion: true })).toEqual({
      nombre: 'Profa. López',
      email: 'lopez@studia.com',
      rol: 'docente',
    })
    expect(
      armarDatosUsuario({ ...nuevo, password: 'nueva-clave-1' }, { edicion: true }).password,
    ).toBe('nueva-clave-1')
  })

  it('el propio usuario nunca manda rol ni contraseña', () => {
    expect(armarDatosUsuario({ ...nuevo, password: 'x' }, { edicion: true, propio: true })).toEqual(
      {
        nombre: 'Profa. López',
        email: 'lopez@studia.com',
      },
    )
  })
})

describe('textoMaterias', () => {
  it('dice cuántas materias imparte o cursa según el rol', () => {
    expect(textoMaterias({ rol: 'docente', materias_impartidas_count: 2 })).toBe(
      'Imparte 2 materias',
    )
    expect(textoMaterias({ rol: 'docente', materias_impartidas_count: 1 })).toBe(
      'Imparte 1 materia',
    )
    expect(textoMaterias({ rol: 'alumno', materias_inscritas_count: 0 })).toBe(
      'Inscrito en 0 materias',
    )
    expect(textoMaterias({ rol: 'admin' })).toBe('—')
  })
})
