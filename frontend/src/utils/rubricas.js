export const MAX_CRITERIOS = 20 // igual que el servidor
export const MAX_PUNTOS = 1000

let contador = 0

/** Un criterio en blanco. La `clave` es solo para que Vue distinga las filas al reordenarlas. */
export function criterioVacio() {
  contador += 1
  return { clave: `c${contador}`, nombre: '', descripcion: '', puntos: '' }
}

/** Convierte una rúbrica de la API en el formulario del editor (los puntos van como texto). */
export function desdeRubrica(rubrica) {
  const criterios = (rubrica.criterios ?? []).map((c) => ({
    ...criterioVacio(),
    nombre: c.nombre ?? '',
    descripcion: c.descripcion ?? '',
    puntos: String(c.puntos ?? ''),
  }))

  return {
    nombre: rubrica.nombre ?? '',
    descripcion: rubrica.descripcion ?? '',
    criterios: criterios.length ? criterios : [criterioVacio()],
  }
}

const ENTERO = /^\d+$/

/** Puntos de un criterio como entero de 1 a 1000, o null si lo escrito no sirve. */
export function puntosDe(texto) {
  const limpio = String(texto ?? '').trim()
  if (!ENTERO.test(limpio)) return null

  const valor = Number(limpio)
  return valor >= 1 && valor <= MAX_PUNTOS ? valor : null
}

/** Suma de los puntos válidos; lo que todavía no es un número válido no suma. */
export function totalPuntos(criterios) {
  return criterios.reduce((suma, c) => suma + (puntosDe(c.puntos) ?? 0), 0)
}

/** Revisa el formulario antes de enviarlo, para que los mensajes salgan en español. */
export function validarRubrica(form) {
  const errores = { nombre: '', criterios: '', porCriterio: [], valido: true }

  if (!form.nombre.trim()) errores.nombre = 'El nombre de la rúbrica es obligatorio.'

  if (form.criterios.length < 1) {
    errores.criterios = 'La rúbrica necesita al menos un criterio.'
  } else if (form.criterios.length > MAX_CRITERIOS) {
    errores.criterios = `Una rúbrica no puede tener más de ${MAX_CRITERIOS} criterios.`
  }

  errores.porCriterio = form.criterios.map((c) => {
    const problemas = {}
    if (!c.nombre.trim()) problemas.nombre = 'Cada criterio necesita un nombre.'
    if (puntosDe(c.puntos) === null) {
      problemas.puntos = `Escribe los puntos como un número entero entre 1 y ${MAX_PUNTOS}.`
    }
    return problemas
  })

  errores.valido =
    !errores.nombre &&
    !errores.criterios &&
    errores.porCriterio.every((problemas) => Object.keys(problemas).length === 0)

  return errores
}

/** Arma lo que espera el servidor: sin espacios sobrantes, descripciones vacías como null. */
export function armarDatosRubrica(form) {
  return {
    nombre: form.nombre.trim(),
    descripcion: form.descripcion.trim() || null,
    criterios: form.criterios.map((c) => ({
      nombre: c.nombre.trim(),
      descripcion: c.descripcion.trim() || null,
      puntos: puntosDe(c.puntos),
    })),
  }
}

/** Copia de la lista con el elemento `indice` movido `delta` lugares; si no se puede, queda igual. */
export function mover(lista, indice, delta) {
  const destino = indice + delta
  if (indice < 0 || indice >= lista.length || destino < 0 || destino >= lista.length) {
    return [...lista]
  }

  const copia = [...lista]
  ;[copia[indice], copia[destino]] = [copia[destino], copia[indice]]
  return copia
}
