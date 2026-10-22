import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import { configurarHttp } from '@/api/http'
import { useAuthStore } from '@/stores/auth'
import { useTemaStore } from '@/stores/tema'

async function iniciar() {
  const app = createApp(App)
  const pinia = createPinia()
  app.use(pinia)

  useTemaStore(pinia) // aplica el tema guardado al documento
  const auth = useAuthStore(pinia)

  configurarHttp({
    obtenerToken: () => auth.token,
    alSesionInvalida: () => {
      auth.limpiarSesion()
      // Mientras se restaura la sesión al arrancar, la propia restauración se encarga
      if (auth.listo && router.currentRoute.value.name !== 'login') {
        router.push({
          name: 'login',
          query: { redirect: router.currentRoute.value.fullPath, expirada: 1 },
        })
      }
    },
  })

  // Antes de pintar nada se recupera la sesión: así un F5 no te saca ni te cambia de rol
  await auth.restaurarSesion()

  app.use(router)
  app.mount('#app')
}

iniciar()
