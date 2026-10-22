/** Muestra una fecha de la API (UTC) en la hora local del dispositivo, o en la zona indicada. */
export function formatearFechaHora(iso, zona) {
  if (!iso) return ''

  const fecha = new Date(iso)
  if (Number.isNaN(fecha.getTime())) return ''

  return new Intl.DateTimeFormat('es-MX', {
    dateStyle: 'medium',
    timeStyle: 'short',
    ...(zona ? { timeZone: zona } : {}),
  }).format(fecha)
}

/** Del valor de un <input type="datetime-local"> ("2030-01-01T23:59", hora local) a ISO en UTC. */
export function deInputLocalAIso(valor) {
  if (!valor) return ''

  const fecha = new Date(valor) // sin zona en el texto, se interpreta como hora local
  return Number.isNaN(fecha.getTime()) ? '' : fecha.toISOString()
}

/** De un ISO de la API a un valor para <input type="datetime-local">, en la hora local. */
export function deIsoAInputLocal(iso) {
  if (!iso) return ''

  const fecha = new Date(iso)
  if (Number.isNaN(fecha.getTime())) return ''

  const dos = (n) => String(n).padStart(2, '0')
  const dia = `${fecha.getFullYear()}-${dos(fecha.getMonth() + 1)}-${dos(fecha.getDate())}`
  return `${dia}T${dos(fecha.getHours())}:${dos(fecha.getMinutes())}`
}
