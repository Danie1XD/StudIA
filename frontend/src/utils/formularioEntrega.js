export const TAMANO_MAXIMO_ENTREGA = 20 * 1024 * 1024 // 20 MB, igual que el servidor
export const TIPOS_ENTREGA = '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.png,.jpg,.jpeg'

/** Mensaje de error si el archivo elegido no sirve; texto vacío si está bien o no hay archivo. */
export function validarArchivo(archivo) {
  if (archivo && archivo.size > TAMANO_MAXIMO_ENTREGA) {
    return 'El archivo no puede pesar más de 20 MB.'
  }
  return ''
}

/** La entrega debe quedar con un archivo o con un texto (contando el archivo que ya tenía). */
export function validarEntrega({ texto, archivo, tieneArchivoPrevio }) {
  if (!archivo && !texto.trim() && !tieneArchivoPrevio) {
    return 'Adjunta un archivo o escribe un enlace o comentario para entregar.'
  }
  return ''
}

/**
 * El texto se manda siempre, aunque esté vacío: el servidor lo toma como el texto nuevo de la
 * entrega. El archivo solo se manda si se eligió uno; si no, se conserva el anterior.
 */
export function armarFormularioEntrega(texto, archivo = null) {
  const datos = new FormData()

  datos.append('contenido', texto.trim())
  if (archivo) datos.append('archivo', archivo)

  return datos
}
