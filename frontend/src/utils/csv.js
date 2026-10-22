// Una celda que empieza así puede ejecutarse como fórmula al abrir el archivo en Excel o Calc
const DISPARADORES = /^[=+\-@\t\r]/

/** Convierte un valor en una celda de CSV, neutralizando las fórmulas y escapando comillas. */
export function celdaCsv(valor) {
  let texto = valor === null || valor === undefined ? '' : String(valor)

  if (DISPARADORES.test(texto)) texto = `'${texto}`

  return /[",\n\r]/.test(texto) ? `"${texto.replaceAll('"', '""')}"` : texto
}

/** CSV listo para guardar: empieza con BOM, para que Excel lea bien las tildes. */
export function armarCsv(filas) {
  return `\uFEFF${filas.map((fila) => fila.map(celdaCsv).join(',')).join('\r\n')}\r\n`
}
