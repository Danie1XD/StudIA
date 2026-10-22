import { ref } from 'vue'
import { defineStore } from 'pinia'
import { asistenteApi } from '@/api/asistente'
import { mensajeDeError } from '@/api/errores'
import { historialParaEnviar, SALUDO, validarMensaje } from '@/utils/asistente'

export const useAsistenteStore = defineStore('asistente', () => {
  let contador = 0
  let conversacion = 0 // cambia al reiniciar, para ignorar respuestas que llegan tarde

  function crear(rol, texto) {
    contador += 1
    return { id: contador, rol, texto }
  }

  const mensajes = ref([crear('asistente', SALUDO)])
  const enviando = ref(false)
  const error = ref('')
  const pendiente = ref('') // pregunta escrita en el panel de inicio, a la espera de enviarse

  function reiniciar() {
    conversacion += 1
    mensajes.value = [crear('asistente', SALUDO)]
    enviando.value = false
    error.value = ''
    pendiente.value = ''
  }

  function dejarPregunta(texto) {
    pendiente.value = String(texto ?? '').trim()
  }

  /** Devuelve la pregunta pendiente (si hay) y la borra: solo se envía una vez. */
  function tomarPregunta() {
    const texto = pendiente.value
    pendiente.value = ''
    return texto
  }

  async function enviar(texto) {
    const limpio = String(texto ?? '').trim()
    const problema = validarMensaje(limpio)

    if (problema) {
      error.value = problema
      return false
    }
    if (enviando.value) return false

    error.value = ''
    const historial = historialParaEnviar(mensajes.value)
    const propio = crear('usuario', limpio)
    mensajes.value.push(propio)
    enviando.value = true
    const mia = conversacion

    try {
      const respuesta = await asistenteApi.enviar(limpio, historial)
      if (mia !== conversacion) return false

      mensajes.value.push(crear('asistente', respuesta))
      return true
    } catch (e) {
      if (mia !== conversacion) return false

      mensajes.value = mensajes.value.filter((m) => m.id !== propio.id)
      error.value = mensajeDeError(e)
      return false
    } finally {
      if (mia === conversacion) enviando.value = false
    }
  }

  return { mensajes, enviando, error, enviar, reiniciar, dejarPregunta, tomarPregunta }
})
