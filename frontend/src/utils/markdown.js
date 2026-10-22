// Convierte la respuesta del asistente (Markdown básico) en datos, nunca en HTML:
// Vue los pinta como texto, así que una etiqueta escrita por la IA no se ejecuta.

const INLINE = /(\*\*[^*]+\*\*|`[^`]+`|\*[^*\s][^*]*\*)/g

export function parsearInline(texto) {
  return String(texto ?? '')
    .split(INLINE)
    .filter((t) => t !== '')
    .map((t) => {
      if (t.startsWith('**') && t.endsWith('**') && t.length > 4) {
        return { texto: t.slice(2, -2), negrita: true }
      }
      if (t.startsWith('`') && t.endsWith('`') && t.length > 2) {
        return { texto: t.slice(1, -1), codigo: true }
      }
      if (t.startsWith('*') && t.endsWith('*') && t.length > 2) {
        return { texto: t.slice(1, -1), cursiva: true }
      }
      return { texto: t }
    })
}

export function parsearMarkdown(texto) {
  const bloques = []
  let lista = null
  let lineas = []

  const cerrarParrafo = () => {
    if (lineas.length) {
      bloques.push({ tipo: 'parrafo', lineas: lineas.map(parsearInline) })
      lineas = []
    }
  }

  const cerrarLista = () => {
    if (lista) {
      bloques.push(lista)
      lista = null
    }
  }

  for (const crudo of String(texto ?? '')
    .replace(/\r\n?/g, '\n')
    .split('\n')) {
    const linea = crudo.trim()

    if (linea === '') {
      cerrarParrafo()
      cerrarLista()
      continue
    }

    const vineta = linea.match(/^[-*•]\s+(.*)$/)
    const numerada = linea.match(/^\d+[.)]\s+(.*)$/)
    const titulo = linea.match(/^#{1,6}\s+(.*)$/)

    if (vineta || numerada) {
      cerrarParrafo()
      const ordenada = Boolean(numerada)

      if (!lista || lista.ordenada !== ordenada) {
        cerrarLista()
        lista = { tipo: 'lista', ordenada, items: [] }
      }
      lista.items.push(parsearInline((vineta ?? numerada)[1]))
    } else if (titulo) {
      cerrarParrafo()
      cerrarLista()
      bloques.push({ tipo: 'parrafo', lineas: [[{ texto: titulo[1], negrita: true }]] })
    } else {
      cerrarLista()
      lineas.push(linea)
    }
  }

  cerrarParrafo()
  cerrarLista()

  return bloques
}
