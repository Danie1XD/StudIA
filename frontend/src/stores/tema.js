import { ref } from 'vue'
import { defineStore } from 'pinia'

const CLAVE = 'studia.tema'
const TEMAS = ['oscuro', 'claro']
const TEMA_POR_DEFECTO = 'oscuro'

function leerGuardado() {
  try {
    const valor = localStorage.getItem(CLAVE)
    return TEMAS.includes(valor) ? valor : TEMA_POR_DEFECTO
  } catch {
    return TEMA_POR_DEFECTO
  }
}

export const useTemaStore = defineStore('tema', () => {
  const actual = ref(leerGuardado())

  function aplicar() {
    document.documentElement.dataset.tema = actual.value
  }

  function establecer(valor) {
    if (!TEMAS.includes(valor)) return

    actual.value = valor
    try {
      localStorage.setItem(CLAVE, valor)
    } catch {
      // Sin almacenamiento el tema funciona igual, solo que no se recuerda
    }
    aplicar()
  }

  function alternar() {
    establecer(actual.value === 'oscuro' ? 'claro' : 'oscuro')
  }

  aplicar() // sincroniza el documento al crear el store

  return { actual, establecer, alternar }
})
