import { describe, expect, it } from 'vitest'
import { mensajeDeError } from '../errores'

const respuesta = (status, data = {}, headers = {}) => ({ response: { status, data, headers } })

describe('mensajeDeError', () => {
  it('avisa cuando no hay conexión con el servidor', () => {
    expect(mensajeDeError(new Error('Network Error'))).toMatch(/No se pudo conectar/)
  })

  it('muestra el primer error de validación', () => {
    const error = respuesta(422, {
      message: 'x',
      errors: { email: ['Ese correo ya está registrado.'], password: ['Otro'] },
    })
    expect(mensajeDeError(error)).toBe('Ese correo ya está registrado.')
  })

  it('indica cuánto esperar cuando hay demasiados intentos', () => {
    expect(mensajeDeError(respuesta(429, {}, { 'retry-after': '42' }))).toContain('42 segundos')
  })

  it('usa un mensaje genérico si el servidor no dice cuánto esperar', () => {
    expect(mensajeDeError(respuesta(429))).toMatch(/Demasiados intentos/)
  })

  it('traduce el 403 genérico de Laravel', () => {
    const error = respuesta(403, { message: 'This action is unauthorized.' })
    expect(mensajeDeError(error)).toBe('No tienes permiso para realizar esta acción.')
  })

  it('conserva el 403 que ya viene en español', () => {
    const error = respuesta(403, { message: 'No tienes permisos para realizar esta acción.' })
    expect(mensajeDeError(error)).toBe('No tienes permisos para realizar esta acción.')
  })

  it('traduce el 404 genérico de un modelo no encontrado', () => {
    const error = respuesta(404, { message: 'No query results for model [App\\Models\\Entrega] 1' })
    expect(mensajeDeError(error)).toBe('No se encontró lo que buscas.')
  })

  it('conserva el 404 con un mensaje propio', () => {
    const error = respuesta(404, { message: 'Esta tarea no tiene archivo adjunto.' })
    expect(mensajeDeError(error)).toBe('Esta tarea no tiene archivo adjunto.')
  })

  it('oculta el detalle técnico de un 500', () => {
    const error = respuesta(500, { message: 'SQLSTATE[42S02]: Base table not found' })
    expect(mensajeDeError(error)).toBe('Ocurrió un error inesperado. Inténtalo de nuevo.')
  })

  it('muestra el mensaje en español de un error de la IA (502)', () => {
    const error = respuesta(502, { message: 'La clave de la IA no es válida.' })
    expect(mensajeDeError(error)).toBe('La clave de la IA no es válida.')
  })
  it('conserva el mensaje en español de un límite de uso de la IA (429)', () => {
    const mensaje =
      'Se alcanzó el límite de uso de la IA. Espera unos minutos e inténtalo de nuevo.'

    expect(mensajeDeError(respuesta(429, { message: mensaje }))).toBe(mensaje)
  })

  it('usa el mensaje genérico cuando el 429 es el de Laravel', () => {
    const error = respuesta(429, { message: 'Too Many Attempts.' }, { 'retry-after': '30' })

    expect(mensajeDeError(error)).toContain('30 segundos')
  })
})
