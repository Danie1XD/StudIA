const TONOS = [
  'tono-0', 'tono-1', 'tono-2', 'tono-3', 'tono-4', 
  'tono-5', 'tono-6', 'tono-7', 'tono-8', 'tono-9'
]

// Usamos un Map para recordar qué tono le tocó a cada materia
const memoriaDeTonos = new Map()
let contadorTonos = 0

export function tonoDeMateria(id) {
  // Si por alguna razón no hay id, devolvemos el primer color
  if (!id) return TONOS[0]
  
  const clave = String(id)

  // Si la materia NO tiene un color asignado en esta sesión, le damos uno nuevo en orden estricto
  if (!memoriaDeTonos.has(clave)) {
    const nuevoTono = TONOS[contadorTonos % TONOS.length]
    memoriaDeTonos.set(clave, nuevoTono)
    contadorTonos++ // Solo avanzamos el contador cuando es una materia nueva
  }

  // Devolvemos el color guardado en memoria para esa materia
  return memoriaDeTonos.get(clave)
}