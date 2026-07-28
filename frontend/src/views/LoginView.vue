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

            <button 
              type="button" 
              @click="loginRapidoGoogle"
              class="w-full border border-gray-700 bg-gray-900/50 hover:bg-gray-800 text-white py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer shadow-md"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="currentColor" d="M12 5c1.6 0 3 .6 4.1 1.6l3.1-3.1C17.3 1.8 14.8 1 12 1 7.4 1 3.6 3.6 1.8 7.4l3.7 2.9C6.4 7.3 9 5 12 5z"/><path fill="currentColor" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/><path fill="currentColor" d="M5.5 14.7c-.2-.8-.4-1.7-.4-2.7s.2-1.9.4-2.7L1.8 6.4C.7 8.6 0 11.2 0 14s.7 5.4 1.8 7.6l3.7-2.9z"/><path fill="currentColor" d="M12 23c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.6-2.3-6.5-5.3L1.8 15.7C3.6 19.5 7.4 23 12 23z"/></svg>
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

const loginRapidoGoogle = async () => {
  try {
    const response = await api.post('/login', {
      email: 'daniel@studia.com',
      password: 'password123'
    })

    // Guardamos el token y el usuario en tu store de Pinia
    authStore.setToken(response.data.token)
    authStore.setUser(response.data.user)

    // Redirigimos al dashboard general
    router.push('/dashboard')
  } catch (error) {
    alert('Error al iniciar sesión rápida. Asegúrate de haber creado el usuario en la base de datos.')
  }
}
</script>