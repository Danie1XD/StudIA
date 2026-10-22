export const ESTADOS = ['pendiente', 'sin_entregar', 'entregada', 'calificada']

export const ESTADO_TEXTO = {
  pendiente: 'Pendiente',
  sin_entregar: 'Sin entregar',
  entregada: 'Entregada',
  calificada: 'Calificada',
}

/** Texto de los botones del filtro (en plural). */
export const FILTRO_TEXTO = {
  pendiente: 'Pendientes',
  sin_entregar: 'Sin entregar',
  entregada: 'Entregadas',
  calificada: 'Calificadas',
}

export const ESTADO_CLASE = {
  pendiente: 'insignia--aviso',
  sin_entregar: 'insignia--error',
  entregada: '',
  calificada: 'insignia--exito',
}

// De más a menos urgente para el alumno
const PRIORIDAD = { pendiente: 0, sin_entregar: 1, entregada: 2, calificada: 3 }

/** Estado de una tarea para un alumno; refleja las mismas reglas que el servidor. */
export function estadoDeTarea(tarea, entrega, ahora = new Date()) {
  if (entrega?.estado === 'calificado') return 'calificada'
  if (entrega) return 'entregada'
  if (tarea.fecha_limite && new Date(tarea.fecha_limite) < ahora) return 'sin_entregar'
  return 'pendiente'
}

/** ¿Se entregó después de la fecha límite? */
export function entregadaTarde(tarea, entrega) {
  if (!tarea?.fecha_limite || !entrega?.entregada_at) return false
  return new Date(entrega.entregada_at) > new Date(tarea.fecha_limite)
}

/** Puntos con hasta dos decimales y sin ceros sobrantes: 95, 86.67, 80.5. */
export function formatearPuntos(valor) {
  if (valor === null || valor === undefined || valor === '') return '—'

  const numero = Number(valor)
  if (!Number.isFinite(numero)) return '—'

  return String(Math.round(numero * 100) / 100)
}

/** Cuántas tareas hay en cada estado, para los botones del filtro. */
export function contarPorEstado(grupos) {
  const conteos = Object.fromEntries(ESTADOS.map((estado) => [estado, 0]))

  for (const grupo of grupos) {
    for (const tarea of grupo.tareas) {
      if (conteos[tarea.estado] !== undefined) conteos[tarea.estado] += 1
    }
  }

  return conteos
}

function porUrgencia(a, b) {
  const prioridad = PRIORIDAD[a.estado] - PRIORIDAD[b.estado]
  if (prioridad !== 0) return prioridad

  // Las que no tienen fecha van al final. No se resta: Infinity - Infinity da NaN
  const fechaA = a.fecha_limite ? new Date(a.fecha_limite).getTime() : Infinity
  const fechaB = b.fecha_limite ? new Date(b.fecha_limite).getTime() : Infinity
  if (fechaA === fechaB) return 0
  return fechaA < fechaB ? -1 : 1
}

/** Filtra por estado, ordena por urgencia, quita las materias sin tareas y las ordena por nombre. */
export function prepararGrupos(grupos, estado = '') {
  return grupos
    .map((grupo) => ({
      ...grupo,
      tareas: grupo.tareas.filter((t) => !estado || t.estado === estado).sort(porUrgencia),
    }))
    .filter((grupo) => grupo.tareas.length > 0)
    .sort((a, b) => a.materia.nombre.localeCompare(b.materia.nombre, 'es'))
}
