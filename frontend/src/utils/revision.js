export const MAX_RETROALIMENTACION = 5000 // igual que el servidor

export const ESTADO_FILA = {
  entregado: { texto: 'Por revisar', clase: 'insignia--aviso' },
  calificado: { texto: 'Calificada', clase: 'insignia--exito' },
  sin_entregar: { texto: 'Sin entregar', clase: '' },
}

// Lo que hay que revisar va primero; lo ya calificado, después; lo que no se entregó, al final
const PRIORIDAD_FILA = { entregado: 0, calificado: 1, sin_entregar: 2 }

export function infoEstadoFila(estado) {
  return ESTADO_FILA[estado] ?? ESTADO_FILA.sin_entregar
}

/** Fecha como número; lo que no tiene fecha (o la tiene inválida) va al final. */
function instante(iso) {
  const tiempo = iso ? new Date(iso).getTime() : Infinity
  return Number.isNaN(tiempo) ? Infinity : tiempo
}

/**
 * Ordena la lista de alumnos de una tarea: primero lo por revisar (la entrega más antigua arriba,
 * para no dejar esperando a nadie), luego lo calificado y al final lo sin entregar.
 */
export function ordenarFilas(filas) {
  return [...filas].sort((a, b) => {
    const prioridad = (PRIORIDAD_FILA[a.estado] ?? 3) - (PRIORIDAD_FILA[b.estado] ?? 3)
    if (prioridad !== 0) return prioridad

    if (a.estado === 'entregado') {
      const fechaA = instante(a.entrega?.entregada_at)
      const fechaB = instante(b.entrega?.entregada_at)
      if (fechaA !== fechaB) return fechaA < fechaB ? -1 : 1
    }

    return a.alumno.nombre.localeCompare(b.alumno.nombre, 'es')
  })
}

export function resumirFilas(filas) {
  const cuenta = (estado) => filas.filter((f) => f.estado === estado).length

  return {
    total: filas.length,
    porRevisar: cuenta('entregado'),
    calificadas: cuenta('calificado'),
    sinEntregar: cuenta('sin_entregar'),
  }
}

/** La entrega por revisar más antigua que no sea la que se está viendo, o null si no queda. */
export function siguientePorRevisar(filas, entregaIdActual) {
  return (
    ordenarFilas(filas).find(
      (f) => f.estado === 'entregado' && f.entrega && f.entrega.id !== entregaIdActual,
    ) ?? null
  )
}

/** Texto de retroalimentación listo para el alumno, armado con lo que sugirió la IA. */
export function armarRetroalimentacion(evaluacion) {
  const r = evaluacion?.retroalimentacion ?? {}
  const bloques = []

  if (r.retroalimentacion_general) bloques.push(r.retroalimentacion_general)
  if (r.puntos_fuertes) bloques.push(`Puntos fuertes:\n${r.puntos_fuertes}`)
  if (r.areas_mejora) bloques.push(`Áreas de mejora:\n${r.areas_mejora}`)

  const texto = bloques.join('\n\n')

  return texto.length > MAX_RETROALIMENTACION
    ? `${texto.slice(0, MAX_RETROALIMENTACION - 1)}…`
    : texto
}

/** Mensaje de error si la calificación no sirve; texto vacío si está bien. */
export function validarCalificacion(valor, maximo) {
  const texto = String(valor ?? '').trim()

  if (texto === '') return 'La calificación es obligatoria.'
  if (!/^\d+(\.\d{1,2})?$/.test(texto)) return 'Escribe un número válido, con hasta dos decimales.'
  if (Number(texto) > maximo) {
    return `La calificación no puede superar el puntaje máximo de ${maximo} puntos.`
  }

  return ''
}

/**
 * Si el texto es solo un enlace http o https, devuelve su dirección; si no, null.
 * Así un `javascript:` o un texto con otras palabras nunca se convierte en enlace.
 */
export function enlaceDe(texto) {
  const limpio = String(texto ?? '').trim()
  if (!limpio || /\s/.test(limpio)) return null

  try {
    const url = new URL(limpio)
    return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : null
  } catch {
    return null
  }
}

function porRevisarYFecha(a, b) {
  if (a.por_revisar !== b.por_revisar) return b.por_revisar - a.por_revisar

  const fechaA = instante(a.fecha_limite)
  const fechaB = instante(b.fecha_limite)
  if (fechaA === fechaB) return 0
  return fechaA < fechaB ? -1 : 1
}

/** Agrega `por_revisar` a cada tarea, ordena (lo más pendiente primero) y quita materias vacías. */
export function prepararGruposDocente(grupos) {
  return grupos
    .map((grupo) => ({
      ...grupo,
      tareas: grupo.tareas
        .map((t) => ({
          ...t,
          por_revisar: Math.max((t.entregas_count ?? 0) - (t.calificadas_count ?? 0), 0),
        }))
        .sort(porRevisarYFecha),
    }))
    .filter((grupo) => grupo.tareas.length > 0)
    .sort((a, b) => a.materia.nombre.localeCompare(b.materia.nombre, 'es'))
}
