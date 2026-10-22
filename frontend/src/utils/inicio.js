import { prepararGruposDocente } from './revision'

/** Iniciales para el avatar: "Daniel Cumplido García" → "DC". */
export function iniciales(nombre) {
  const palabras = String(nombre ?? '')
    .trim()
    .split(/\s+/)
    .filter(Boolean)

  if (!palabras.length) return '?'

  const letras =
    palabras.length === 1
      ? Array.from(palabras[0]).slice(0, 2).join('')
      : Array.from(palabras[0])[0] + Array.from(palabras[1])[0]

  return letras.toUpperCase()
}

/** Al alumno se le saluda por su primer nombre; a los demás, por su nombre completo. */
export function nombreParaSaludo(usuario) {
  const nombre = String(usuario?.nombre ?? '').trim()
  if (!nombre) return ''

  return usuario.rol === 'alumno' ? nombre.split(/\s+/)[0] : nombre
}

/** Minúsculas y sin tildes, para que "matematicas" encuentre "Matemáticas". */
export function normalizar(texto) {
  return String(texto ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim()
}

/** `campos` es una lista de funciones que sacan de cada elemento el texto donde buscar. */
export function filtrarPorTexto(items, texto, campos) {
  const buscado = normalizar(texto)
  if (!buscado) return items

  return items.filter((item) => campos.some((campo) => normalizar(campo(item)).includes(buscado)))
}

/** Progreso del alumno en cada materia: tareas entregadas o calificadas entre el total. */
export function progresoPorMateria(materias, grupos) {
  const porMateria = new Map(grupos.map((g) => [g.materia.id, g.tareas]))

  return materias.map((materia) => {
    const tareas = porMateria.get(materia.id) ?? []
    const hechas = tareas.filter(
      (t) => t.estado === 'entregada' || t.estado === 'calificada',
    ).length

    return {
      materia,
      total: tareas.length,
      hechas,
      porcentaje: tareas.length ? Math.round((hechas / tareas.length) * 100) : null,
    }
  })
}

/** Tareas pendientes con fecha límite, la más próxima primero. */
export function proximasTareas(grupos, limite = 4) {
  return grupos
    .flatMap((g) => g.tareas.map((t) => ({ ...t, materia: g.materia })))
    .filter((t) => t.estado === 'pendiente' && t.fecha_limite)
    .sort((a, b) => new Date(a.fecha_limite) - new Date(b.fecha_limite))
    .slice(0, limite)
}

/** Resumen del docente por materia: entregas recibidas, calificadas y por revisar. */
export function resumenDocentePorMateria(materias, grupos) {
  const porMateria = new Map(grupos.map((g) => [g.materia.id, g.tareas]))

  return materias.map((materia) => {
    const tareas = porMateria.get(materia.id) ?? []
    const entregas = tareas.reduce((suma, t) => suma + (t.entregas_count ?? 0), 0)
    const calificadas = tareas.reduce((suma, t) => suma + (t.calificadas_count ?? 0), 0)

    return {
      materia,
      tareas: tareas.length,
      entregas,
      calificadas,
      porRevisar: Math.max(entregas - calificadas, 0),
      porcentaje: entregas ? Math.round((calificadas / entregas) * 100) : null,
    }
  })
}

/** Las tareas con más entregas por revisar, de todas las materias. */
export function porRevisarDocente(grupos, limite = 4) {
  return prepararGruposDocente(grupos)
    .flatMap((g) => g.tareas.map((t) => ({ ...t, materia: g.materia })))
    .filter((t) => t.por_revisar > 0)
    .sort((a, b) => b.por_revisar - a.por_revisar)
    .slice(0, limite)
}
