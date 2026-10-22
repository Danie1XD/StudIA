/**
 * Qué secciones de una materia están ocultas. Se recuerda por materia en el navegador,
 * para que al volver a entrar la clase quede como la dejó la persona.
 */
const prefijo = 'studia.secciones-ocultas.'

/** Claves (p. ej. "seccion-3") de los grupos ocultos de esa materia. */
export function leerOcultos(materiaId, almacen = globalThis.localStorage) {
  try {
    const lista = JSON.parse(almacen?.getItem(prefijo + materiaId) ?? '[]')
    return new Set(Array.isArray(lista) ? lista.filter((c) => typeof c === 'string') : [])
  } catch {
    return new Set() // sin almacenamiento o dato dañado: todo abierto
  }
}

export function guardarOcultos(materiaId, ocultos, almacen = globalThis.localStorage) {
  try {
    almacen?.setItem(prefijo + materiaId, JSON.stringify([...ocultos]))
  } catch {
    // modo privado o almacenamiento lleno: se pierde solo la preferencia
  }
}

/** Copia del conjunto con `clave` oculta si estaba visible, o visible si estaba oculta. */
export function alternarOculto(ocultos, clave) {
  const copia = new Set(ocultos)
  if (copia.has(clave)) copia.delete(clave)
  else copia.add(clave)
  return copia
}

/** Copia del conjunto sin `clave` (la sección queda abierta). */
export function mostrar(ocultos, clave) {
  const copia = new Set(ocultos)
  copia.delete(clave)
  return copia
}
