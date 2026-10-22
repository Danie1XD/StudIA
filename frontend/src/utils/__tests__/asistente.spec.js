import { describe, expect, it } from 'vitest'
import { historialParaEnviar, MAX_MENSAJE, validarMensaje } from '../asistente'

const mensaje = (id, rol, texto) => ({ id, rol, texto })

describe('validarMensaje', () => {
  it('exige texto y respeta el máximo del servidor', () => {
    expect(validarMensaje('')).toBe('Escribe tu duda.')
    expect(validarMensaje('   ')).toBe('Escribe tu duda.')
    expect(validarMensaje('a'.repeat(MAX_MENSAJE + 1))).toMatch(/2000/)
    expect(validarMensaje('Hola')).toBe('')
    expect(validarMensaje('a'.repeat(MAX_MENSAJE))).toBe('')
  })
})

describe('historialParaEnviar', () => {
  it('omite el saludo inicial y usa el formato del servidor', () => {
    const mensajes = [
      mensaje(1, 'asistente', 'Saludo'),
      mensaje(2, 'usuario', 'P1'),
      mensaje(3, 'asistente', 'R1'),
    ]

    expect(historialParaEnviar(mensajes)).toEqual([
      { rol: 'usuario', texto: 'P1' },
      { rol: 'asistente', texto: 'R1' },
    ])
  })

  it('devuelve una lista vacía si el usuario aún no ha escrito nada', () => {
    expect(historialParaEnviar([mensaje(1, 'asistente', 'Saludo')])).toEqual([])
  })

  it('manda como máximo los últimos 20 turnos', () => {
    const mensajes = [mensaje(0, 'asistente', 'Saludo')]
    for (let i = 1; i <= 25; i++) {
      mensajes.push(mensaje(i, i % 2 ? 'usuario' : 'asistente', `t${i}`))
    }

    const historial = historialParaEnviar(mensajes)

    expect(historial).toHaveLength(20)
    expect(historial[0].texto).toBe('t6')
    expect(historial[19].texto).toBe('t25')
  })

  it('recorta los textos largos a 2000 sin partir un emoji', () => {
    const conEmoji = historialParaEnviar([mensaje(1, 'usuario', `${'a'.repeat(1999)}😀`)])
    const largo = historialParaEnviar([mensaje(1, 'usuario', 'b'.repeat(2500))])

    expect(conEmoji[0].texto).toHaveLength(1999)
    expect(largo[0].texto).toHaveLength(2000)
  })
})
