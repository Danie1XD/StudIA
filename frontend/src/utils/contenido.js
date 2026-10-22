function unir(tareas = [], anuncios = []) {
  return [
    ...tareas.map((t) => ({ ...t, tipo: 'tarea', clave: `tarea-${t.id}` })),
    ...anuncios.map((a) => ({ ...a, tipo: 'anuncio', clave: `anuncio-${a.id}` })),
  ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at)) // lo más reciente primero
}

/**
 * Convierte la respuesta de /materias/{id}/contenido en grupos listos para pintar:
 * primero lo general (sin sección) y luego cada sección en el orden que dio el servidor.
 * `seccionId` es null en "General", que no es una sección real.
 */
export function agruparContenido(contenido, { incluirVacias = false } = {}) {
  if (!contenido) return []

  const grupos = []

  const general = unir(contenido.sin_seccion?.tareas, contenido.sin_seccion?.anuncios)
  if (general.length) {
    grupos.push({ clave: 'general', nombre: 'General', seccionId: null, items: general })
  }

  for (const seccion of contenido.secciones ?? []) {
    const items = unir(seccion.tareas, seccion.anuncios)
    if (items.length || incluirVacias) {
      grupos.push({
        clave: `seccion-${seccion.id}`,
        nombre: seccion.nombre,
        seccionId: seccion.id,
        items,
      })
    }
  }

  return grupos
}
