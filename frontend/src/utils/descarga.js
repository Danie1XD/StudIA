const EXTENSIONES = {
  'application/pdf': 'pdf',
  'image/png': 'png',
  'image/jpeg': 'jpg',
  'application/zip': 'zip',
  'application/msword': 'doc',
  'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
  'application/vnd.ms-powerpoint': 'ppt',
  'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'pptx',
  'application/vnd.ms-excel': 'xls',
  'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
}

/** Nombre de archivo seguro a partir del título de una tarea. */
export function nombreDeArchivo(titulo, extension = 'pdf') {
  const base = String(titulo ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '') // quita las tildes
    .replace(/[^a-zA-Z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .toLowerCase()
    .slice(0, 60)
    .replace(/-+$/, '')

  return `${base || 'archivo'}.${extension}`
}

/** Extensión según el tipo de archivo que respondió el servidor ("bin" si no se reconoce). */
export function extensionDeMime(mime) {
  const tipo = String(mime ?? '')
    .split(';')[0]
    .trim()
    .toLowerCase()

  return EXTENSIONES[tipo] ?? 'bin'
}

/** Nombre para descargar el archivo de una entrega: "entrega-<título>.<extensión>". */
export function nombreDeEntrega(titulo, mime) {
  return nombreDeArchivo(`entrega ${titulo ?? ''}`, extensionDeMime(mime))
}

/** Entrega al navegador un archivo ya descargado (blob) como descarga. */
export function guardarArchivo(blob, nombre) {
  const url = URL.createObjectURL(blob)
  const enlace = document.createElement('a')
  enlace.href = url
  enlace.download = nombre
  document.body.append(enlace)
  enlace.click()
  enlace.remove()
  setTimeout(() => URL.revokeObjectURL(url), 1000)
}
