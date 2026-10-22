export const MAX_MENSAJE = 2000 // igual que el servidor
export const MAX_HISTORIAL = 20

export const SALUDO = '¡Hola! Soy el asistente virtual de StudIA. ¿En qué te puedo ayudar hoy?'

export function validarMensaje(texto) {
  const limpio = String(texto ?? '').trim()

  if (!limpio) return 'Escribe tu duda.'
  if (limpio.length > MAX_MENSAJE) return `El mensaje no puede pasar de ${MAX_MENSAJE} caracteres.`

  return ''
}

function recortar(texto, max) {
  if (texto.length <= max) return texto

  const ultimo = texto.charCodeAt(max - 1)
  const mitadDeEmoji = ultimo >= 0xd800 && ultimo <= 0xdbff // el corte partiría un emoji

  return texto.slice(0, mitadDeEmoji ? max - 1 : max)
}

/** Turnos anteriores en el formato del servidor: sin el saludo inicial, recortados y máximo 20. */
export function historialParaEnviar(mensajes) {
  const inicio = mensajes.findIndex((m) => m.rol === 'usuario')
  if (inicio === -1) return []

  return mensajes
    .slice(inicio)
    .map((m) => ({ rol: m.rol, texto: recortar(m.texto, MAX_MENSAJE) }))
    .slice(-MAX_HISTORIAL)
}
