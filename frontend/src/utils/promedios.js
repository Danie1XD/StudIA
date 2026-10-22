import { formatearPuntos } from './estados'

export const ESCALA = 100

const NUMERO = /^\d+(\.\d{1,2})?$/
const PARCIALES = [1, 2, 3]

/** Lo escrito en un campo de simulación como número de 0 a 100; null si está vacío o no sirve. */
export function leerParcial(texto) {
  const limpio = String(texto ?? '').trim()
  if (!NUMERO.test(limpio)) return null

  const valor = Number(limpio)
  return valor <= ESCALA ? valor : null
}

/** Mensaje de error de un campo de simulación. Un campo vacío es válido: ese parcial no cuenta. */
export function validarParcial(texto) {
  const limpio = String(texto ?? '').trim()

  if (limpio === '') return ''
  if (!NUMERO.test(limpio)) return 'Escribe un número válido, con hasta dos decimales.'
  if (Number(limpio) > ESCALA) return `El máximo es ${ESCALA}.`

  return ''
}

/** Promedio de los valores que existen, con dos decimales; null si no hay ninguno. */
export function promedioFinal(valores) {
  const validos = valores.filter((v) => typeof v === 'number' && Number.isFinite(v))
  if (!validos.length) return null

  return Math.round((validos.reduce((suma, v) => suma + v, 0) / validos.length) * 100) / 100
}

/** Lo que muestra cada campo de simulación al empezar: el promedio real del parcial, o vacío. */
export function textosIniciales(parciales) {
  return parciales.map((p) =>
    p.promedio === null || p.promedio === undefined ? '' : formatearPuntos(p.promedio),
  )
}

/** ¿El alumno cambió algo respecto a sus parciales reales? */
export function hayCambios(parciales, textos) {
  const iniciales = textosIniciales(parciales)
  return textos.some((texto, i) => String(texto).trim() !== iniciales[i])
}

/** Valor de un parcial sin redondear, a partir de sus puntos; null si aún no cuenta ninguna tarea. */
export function valorExacto(parcial) {
  return parcial.puntos_posibles > 0
    ? (parcial.puntos_obtenidos / parcial.puntos_posibles) * ESCALA
    : null
}

/**
 * Valores con los que se simula el promedio final. Lo que el alumno no tocó se usa exacto (sin el
 * redondeo que se muestra), así que sin cambios el resultado coincide con el del servidor. Lo que
 * escribió cuenta tal cual; un campo vacío o con un valor inválido no cuenta.
 */
export function valoresSimulados(parciales, textos) {
  const iniciales = textosIniciales(parciales)

  return parciales.map((parcial, i) => {
    const texto = String(textos[i] ?? '').trim()
    if (texto === iniciales[i]) return valorExacto(parcial)

    return validarParcial(texto) ? null : leerParcial(texto)
  })
}

/** Promedio de los promedios finales del grupo; null si nadie tiene calificaciones. */
export function promedioDelGrupo(filas) {
  return promedioFinal(filas.map((f) => f.promedio_final))
}

/** "2 de 3 tareas contadas": cuántas tareas del parcial entran en el cálculo. */
export function detalleParcial(parcial) {
  if (!parcial) return ''
  if (parcial.tareas_total === 0) return 'Sin tareas'

  const unidad = parcial.tareas_total === 1 ? 'tarea contada' : 'tareas contadas'
  return `${parcial.tareas_contadas} de ${parcial.tareas_total} ${unidad}`
}

/** Filas para el CSV: encabezado y una fila por alumno ('' donde no hay datos). */
export function filasParaExportar(filas) {
  return [
    ['Alumno', ...PARCIALES.map((n) => `Parcial ${n}`), 'Promedio final'],
    ...filas.map((fila) => [
      fila.alumno.nombre,
      ...PARCIALES.map((n) => fila.parciales.find((p) => p.parcial === n)?.promedio ?? ''),
      fila.promedio_final ?? '',
    ]),
  ]
}
