<template>
  <div class="flex h-screen bg-studia-dark text-studia-light overflow-hidden">
    
    <!-- BARRA LATERAL (Sidebar) -->
    <aside class="w-64 bg-studia-card border-r border-gray-800 flex flex-col">
      <!-- Logo -->
      <div class="p-6 flex items-center gap-3">
        <div class="w-10 h-10 text-studia-purple">
          <svg fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-studia-purple tracking-wider">StudIA</h1>
      </div>

      <!-- Menú de Navegación -->
      <nav class="flex-1 px-4 space-y-2 mt-4">
        
        <RouterLink to="/dashboard/inicio" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" active-class="text-white bg-white/10 border-l-2 border-studia-purple">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Inicio
        </RouterLink>

        <RouterLink to="/dashboard/materias" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" active-class="text-white bg-white/10 border-l-2 border-studia-purple">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
          Mis materias
        </RouterLink>

        <RouterLink to="/dashboard/tareas" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" active-class="text-studia-light bg-studia-purple/20 border-l-2 border-studia-purple">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          {{ usuario.rol === 'docente' ? 'Gestión de Tareas' : 'Tareas pendientes' }}
        </RouterLink>

        <!-- AQUÍ ESTÁ EL TRUCO: v-if para que solo el Docente vea la Inteligencia Artificial -->
        <RouterLink v-if="usuario.rol === 'docente'" to="/dashboard/evaluacion-ia" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" active-class="text-white bg-white/10 border-l-2 border-studia-purple">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          Evaluación IA
        </RouterLink>

        <RouterLink to="/dashboard/perfil" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors" active-class="text-white bg-white/10 border-l-2 border-studia-purple">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
          Perfil
        </RouterLink>

      </nav>

      <!-- Usuario Inferior Conectado Dinámicamente + Botón de Salir -->
      <div class="p-4 border-t border-gray-800 flex items-center justify-between">
        <div class="flex items-center gap-3 overflow-hidden">
          <div class="w-8 h-8 rounded-full bg-studia-purple flex items-center justify-center text-sm font-bold text-white shrink-0">
            <!-- Muestra la primera letra de tu nombre real -->
            {{ usuario.nombre ? usuario.nombre.charAt(0).toUpperCase() : 'U' }}
          </div>
          <div class="text-xs truncate">
            <p class="font-bold text-white truncate">{{ usuario.nombre || 'Usuario StudIA' }}</p>
            <p class="text-gray-500 capitalize">{{ usuario.rol || 'estudiante' }}</p>
          </div>
        </div>

        <!-- Botón para Cerrar Sesión -->
        <button @click="cerrarSesion" title="Cerrar Sesión" class="p-2 text-gray-400 hover:text-red-400 hover:bg-white/5 rounded-lg transition-colors shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </button>
      </div>
    </aside>

    <!-- ÁREA CENTRAL DE CONTENIDO -->
    <main class="flex-1 overflow-y-auto p-8 relative">
      <RouterView />
    </main>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

// Estado reactivo del usuario
const usuario = ref({
  nombre: '',
  rol: ''
})

// Al cargar la pantalla, leemos quién inició sesión en el Local Storage
onMounted(() => {
  const userGuardado = localStorage.getItem('usuario') || localStorage.getItem('user')
  if (userGuardado) {
    try {
      usuario.value = JSON.parse(userGuardado)
    } catch (error) {
      console.error('No se pudo leer la información del usuario:', error)
    }
  }
})

// Función real para cerrar sesión y salir del sistema
const cerrarSesion = async () => {
  try {
    const token = localStorage.getItem('token')
    if (token) {
      await axios.post('http://localhost:8000/api/logout', {}, {
        headers: {
          Authorization: `Bearer ${token}`
        }
      })
    }
  } catch (error) {
    console.log('El servidor cerró la sesión o el token ya había expirado');
  } finally {
    // Limpiamos absolutamente toda la memoria y regresamos al login
    localStorage.clear()
    router.push('/')
  }
}
</script>