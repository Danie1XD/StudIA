const PERMISO_DENEGADO = 'This action is unauthorized.'
const LIMITE_LARAVEL = 'Too Many Attempts.'

/** Convierte cualquier error de la API en un mensaje en español para mostrar al usuario. */
export function mensajeDeError(
  error,
  porDefecto = 'Ocurrió un error inesperado. Inténtalo de nuevo.',
) {
  const respuesta = error?.response

  if (!respuesta) {
    return 'No se pudo conectar con el servidor. Revisa tu conexión e inténtalo de nuevo.'
  }

  const { status, data, headers } = respuesta
  const mensaje = typeof data?.message === 'string' ? data.message : ''

  if (status === 422 && data?.errors) {
    return Object.values(data.errors).flat()[0] ?? porDefecto
  }

  if (status === 429) {
    // El límite de uso de la IA trae su propio mensaje en español; el genérico de Laravel no sirve
    if (mensaje && mensaje !== LIMITE_LARAVEL) return mensaje

    const espera = headers?.['retry-after']
    return espera
      ? `Demasiados intentos. Espera ${espera} segundos e inténtalo de nuevo.`
      : 'Demasiados intentos. Espera un momento e inténtalo de nuevo.'
  }

  if (status === 401) return 'Tu sesión expiró. Inicia sesión de nuevo.'

  if (status === 403 && (!mensaje || mensaje === PERMISO_DENEGADO)) {
    return 'No tienes permiso para realizar esta acción.'
  }

  const esNoEncontradoGenerico =
    !mensaje ||
    mensaje === 'Not Found' ||
    mensaje.startsWith('No query results') ||
    mensaje.includes('could not be found')
  if (status === 404 && esNoEncontradoGenerico) return 'No se encontró lo que buscas.'

  // Un 500 es un fallo inesperado del servidor; su mensaje técnico no sirve al usuario.
  // Los 502/503 sí traen un texto en español pensado para él (por ejemplo, los de la IA).
  if (status === 500) return porDefecto

  return mensaje || porDefecto
}

/** Errores de validación (422) por campo, p. ej. { email: 'Ese correo ya está registrado.' } */
export function erroresDeCampos(error) {
  const errores = error?.response?.status === 422 ? error.response.data?.errors : null
  if (!errores) return {}

  return Object.fromEntries(
    Object.entries(errores).map(([campo, mensajes]) => [
      campo,
      Array.isArray(mensajes) ? mensajes[0] : String(mensajes),
    ]),
  )
}
