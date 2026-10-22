export const INICIO_POR_ROL = {
  alumno: '/alumno',
  docente: '/docente',
  admin: '/admin',
}

export function rutaInicio(rol) {
  return INICIO_POR_ROL[rol] ?? '/login'
}

/**
 * Decide si se puede entrar a una ruta. Devuelve `true` o la ruta a la que mandar al usuario.
 * Es una función pura (sin Vue) para poder probarla fácilmente.
 *
 * @param {{ meta?: object, fullPath?: string }} destino
 * @param {{ autenticado: boolean, rol: string | null }} sesion
 */
export function resolverAcceso(destino, sesion) {
  const meta = destino.meta ?? {}

  // Quien ya inició sesión no necesita ver el login. Si su rol no tiene inicio conocido,
  // se le deja pasar: mandarlo a /login desde /login sería un bucle infinito.
  if (meta.soloInvitados && sesion.autenticado && INICIO_POR_ROL[sesion.rol]) {
    return rutaInicio(sesion.rol)
  }

  if (meta.requiereSesion && !sesion.autenticado) {
    const query = destino.fullPath && destino.fullPath !== '/' ? { redirect: destino.fullPath } : {}
    return { name: 'login', query }
  }

  if (meta.roles && !meta.roles.includes(sesion.rol)) {
    return rutaInicio(sesion.rol)
  }

  return true
}
