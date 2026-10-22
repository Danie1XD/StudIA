import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { authApi } from '@/api/auth'
import { useAsistenteStore } from './asistente'

const CLAVE_TOKEN = 'studia.token'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(CLAVE_TOKEN))
  const usuario = ref(null)
  const listo = ref(false) // ya se intentó restaurar la sesión al arrancar

  const autenticado = computed(() => Boolean(token.value && usuario.value))
  const rol = computed(() => usuario.value?.rol ?? null)

  function guardarToken(valor) {
    token.value = valor
    if (valor) localStorage.setItem(CLAVE_TOKEN, valor)
    else localStorage.removeItem(CLAVE_TOKEN)
  }

  function limpiarSesion() {
    guardarToken(null)
    usuario.value = null
    useAsistenteStore().reiniciar() // la conversación con el asistente no sobrevive a la sesión
  }

  function actualizarUsuario(datos) {
    usuario.value = datos
  }

  async function iniciarSesion(credenciales) {
    const { user, token: nuevo } = await authApi.login(credenciales)
    guardarToken(nuevo)
    usuario.value = user
    return user
  }

  /** Recupera al usuario con el token guardado. Se llama antes de pintar la aplicación. */
  async function restaurarSesion() {
    if (token.value) {
      try {
        usuario.value = await authApi.me()
      } catch (error) {
        // Solo se descarta el token si el servidor dijo que ya no vale.
        // Si simplemente no hay conexión, se conserva para poder reintentar.
        if (error.response?.status === 401) limpiarSesion()
      }
    }
    listo.value = true
  }

  async function cerrarSesion() {
    try {
      await authApi.logout()
    } catch {
      // Aunque el servidor no responda, la sesión local se cierra de todos modos
    } finally {
      limpiarSesion()
    }
  }

  return {
    token,
    usuario,
    listo,
    autenticado,
    rol,
    iniciarSesion,
    restaurarSesion,
    cerrarSesion,
    limpiarSesion,
    actualizarUsuario,
  }
})
