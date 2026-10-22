export const MIN_PASSWORD = 8

const CORREO = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

export function esCorreo(texto) {
  return CORREO.test(String(texto ?? '').trim())
}

export function validarPerfil({ nombre, email }) {
  const errores = {}

  if (!String(nombre ?? '').trim()) errores.nombre = 'El nombre es obligatorio.'

  if (!String(email ?? '').trim()) errores.email = 'El correo es obligatorio.'
  else if (!esCorreo(email)) errores.email = 'Escribe un correo válido.'

  return errores
}

/** Las claves coinciden con las del servidor (password_actual, password) para mostrar errores igual. */
export function validarCambioPassword({ actual, nueva, confirmacion }) {
  const errores = {}

  if (!actual) errores.password_actual = 'Escribe tu contraseña actual.'

  if (!nueva) {
    errores.password = 'Escribe la contraseña nueva.'
  } else if (nueva.length < MIN_PASSWORD) {
    errores.password = `La contraseña nueva debe tener al menos ${MIN_PASSWORD} caracteres.`
  } else if (nueva === actual) {
    errores.password = 'La contraseña nueva debe ser distinta de la actual.'
  } else if (nueva !== confirmacion) {
    errores.password = 'La confirmación no coincide con la contraseña nueva.'
  }

  return errores
}
