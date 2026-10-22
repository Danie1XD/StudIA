import { deInputLocalAIso } from './fechas'

export const TAMANO_MAXIMO_PDF = 10 * 1024 * 1024 // 10 MB, igual que el servidor

export const TIPOS_EVALUACION = [
  { valor: 'general', texto: 'General' },
  { valor: 'ensayo', texto: 'Texto escrito (ensayo, reporte)' },
  { valor: 'codigo', texto: 'Programación' },
  { valor: 'visual', texto: 'Visual (mapa mental, infografía, presentación)' },
]

/**
 * Arma el formulario multipart de una tarea. Lo que el docente dejó sin elegir se manda como
 * texto vacío: Laravel lo convierte en null (sin fecha, sin sección, sin rúbrica).
 */
export function armarFormularioTarea(
  form,
  { archivo = null, quitarArchivo = false, edicion = false } = {},
) {
  const datos = new FormData()

  datos.append('titulo', form.titulo.trim())
  datos.append('instrucciones', form.instrucciones.trim())
  datos.append('fecha_limite', deInputLocalAIso(form.fecha_limite))
  datos.append('puntaje_maximo', form.puntaje_maximo)
  datos.append('parcial', form.parcial)
  datos.append('tipo_evaluacion', form.tipo_evaluacion)
  datos.append('seccion_id', form.seccion_id)
  datos.append('rubrica_id', form.rubrica_id)
  // Solo se comparte si hay rúbrica elegida
  datos.append('rubrica_visible', form.rubrica_id && form.rubrica_visible ? '1' : '0')

  if (archivo) datos.append('archivo', archivo)
  else if (edicion && quitarArchivo) datos.append('quitar_archivo', '1')

  // PHP no lee archivos en peticiones PUT: se manda POST y Laravel lo trata como PUT
  if (edicion) datos.append('_method', 'PUT')

  return datos
}
