import { describe, expect, it } from 'vitest'
import { validarCambioPassword, validarPerfil } from '../perfil'

describe('validarPerfil', () => {
  it('acepta datos correctos', () => {
    expect(validarPerfil({ nombre: 'Ana', email: 'ana@studia.com' })).toEqual({})
  })

  it('exige nombre y correo', () => {
    expect(validarPerfil({ nombre: '  ', email: '' })).toEqual({
      nombre: 'El nombre es obligatorio.',
      email: 'El correo es obligatorio.',
    })
  })

  it('rechaza un correo mal escrito', () => {
    for (const email of ['sin-arroba', 'a@b', 'a b@c.com', '@c.com']) {
      expect(validarPerfil({ nombre: 'Ana', email }).email).toBe('Escribe un correo válido.')
    }
  })
})

describe('validarCambioPassword', () => {
  it('acepta un cambio correcto', () => {
    expect(
      validarCambioPassword({
        actual: '12345678',
        nueva: 'nueva-clave-1',
        confirmacion: 'nueva-clave-1',
      }),
    ).toEqual({})
  })

  it('exige la contraseña actual y la nueva', () => {
    expect(validarCambioPassword({ actual: '', nueva: '', confirmacion: '' })).toEqual({
      password_actual: 'Escribe tu contraseña actual.',
      password: 'Escribe la contraseña nueva.',
    })
  })

  it('rechaza una contraseña nueva de menos de 8 caracteres', () => {
    const errores = validarCambioPassword({ actual: 'x', nueva: 'corta', confirmacion: 'corta' })

    expect(errores.password).toMatch(/al menos 8/)
  })

  it('rechaza una contraseña nueva igual a la actual', () => {
    const errores = validarCambioPassword({
      actual: '12345678',
      nueva: '12345678',
      confirmacion: '12345678',
    })

    expect(errores.password).toMatch(/distinta/)
  })

  it('rechaza una confirmación que no coincide', () => {
    const errores = validarCambioPassword({
      actual: '12345678',
      nueva: 'nueva-clave-1',
      confirmacion: 'otra-clave-12',
    })

    expect(errores.password).toMatch(/no coincide/)
  })
})
