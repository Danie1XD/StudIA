<template>
  <div class="min-h-screen flex items-center justify-center bg-studia-dark p-4">
    <!-- Contenedor principal de la tarjeta -->
    <div class="flex flex-col md:flex-row w-full max-w-4xl bg-studia-card rounded-xl overflow-hidden shadow-2xl">
      
      <!-- Mitad Izquierda (Logo y Marca) -->
      <div class="md:w-1/2 p-12 flex flex-col items-center justify-center bg-studia-dark border-r border-gray-800">
        <div class="w-32 h-32 mb-6 text-studia-purple">
          <!-- SVG simulando el birrete tecnológico -->
          <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
          </svg>
        </div>
        <h1 class="text-5xl font-bold text-studia-purple mb-2 tracking-wider">StudIA</h1>
        <p class="text-gray-400 text-sm">Sistema Universitario Dirigido por IA</p>
      </div>

      <!-- Mitad Derecha (Formulario) -->
      <div class="md:w-1/2 p-12 bg-gradient-to-br from-studia-purple to-purple-900 flex flex-col justify-center">
        <h2 class="text-2xl font-bold text-white mb-1 uppercase tracking-wider">Inicia Sesión</h2>
        <p class="text-purple-200 text-xs mb-6">Ingresa tus datos</p>

        <!-- Alerta de Error si las credenciales fallan -->
        <div v-if="errorMsg" class="mb-4 bg-red-500/20 border border-red-500 text-red-200 px-4 py-2 rounded-lg text-xs">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="iniciarSesion" class="space-y-6">
          <!-- Input Correo -->
          <div>
            <div class="flex items-center text-purple-200 text-xs mb-1 gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              <label>Correo electrónico</label>
            </div>
            <input 
              type="email" 
              v-model="email" 
              required
              class="w-full bg-transparent border-b border-purple-300 text-white px-1 py-1 focus:outline-none focus:border-white transition-colors" 
            />
          </div>

          <!-- Input Contraseña -->
          <div>
            <div class="flex items-center text-purple-200 text-xs mb-1 gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              <label>Contraseña</label>
            </div>
            <input 
              type="password" 
              v-model="password" 
              required
              class="w-full bg-transparent border-b border-purple-300 text-white px-1 py-1 focus:outline-none focus:border-white transition-colors" 
            />
          </div>

          <div class="text-right">
            <a href="#" class="text-xs text-purple-300 hover:text-white transition-colors">¿Olvidaste tu contraseña?</a>
          </div>

          <!-- Botones -->
          <div class="space-y-3 pt-2">
            <button type="submit" class="w-full bg-white text-studia-purple font-bold py-2 rounded-full hover:bg-gray-200 transition-colors shadow-lg cursor-pointer">
              Iniciar Sesión
            </button>

            <button type="button" class="w-full bg-transparent border border-purple-300 text-white font-bold py-2 rounded-full hover:bg-white/10 transition-colors flex items-center justify-center gap-2 cursor-pointer">
              Continúa con Google
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const errorMsg = ref('')

const iniciarSesion = async () => {
  errorMsg.value = '' 
  
  const exito = await authStore.login(email.value, password.value)
  
  if (exito) {
    const rol = authStore.user?.rol

    // Redirección condicionada según el rol del usuario autenticado
    if (rol === 'docente') {
      router.push({ name: 'docente-inicio' })
    } else if (rol === 'alumno') {
      router.push({ name: 'alumno-inicio' })
    } else {
      router.push({ name: 'inicio' })
    }
  } else {
    errorMsg.value = 'Correo o contraseña incorrectos.'
  }
}
</script>