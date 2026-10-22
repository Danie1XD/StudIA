import { describe, expect, it } from 'vitest'
import { erroresDeCampos } from '../errores'

describe('erroresDeCampos', () => {
  it('devuelve el primer mensaje de cada campo en un error de validación', () => {
    const error = {
      response: {
        status: 422,
        data: {
          errors: {
            nombre: ['El nombre es obligatorio.', 'Otro mensaje'],
            email: ['Ese correo ya está registrado.'],
          },
        },
      },
    }

    expect(erroresDeCampos(error)).toEqual({
      nombre: 'El nombre es obligatorio.',
      email: 'Ese correo ya está registrado.',
    })
  })

  it('devuelve un objeto vacío si el error no es de validación', () => {
    expect(erroresDeCampos({ response: { status: 500, data: {} } })).toEqual({})
    expect(erroresDeCampos({ response: { status: 403, data: { message: 'x' } } })).toEqual({})
  })

  it('devuelve un objeto vacío si no hubo respuesta del servidor', () => {
    expect(erroresDeCampos(new Error('Network Error'))).toEqual({})
  })

  it('devuelve un objeto vacío si el 422 no trae errores por campo', () => {
    expect(erroresDeCampos({ response: { status: 422, data: { message: 'x' } } })).toEqual({})
  })
})
