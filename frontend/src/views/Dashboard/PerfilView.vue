<template>
  <div class="max-w-5xl mx-auto">
    <!-- Encabezado -->
    <div class="mb-8">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Mi Perfil</h2>
      <p class="text-gray-400 text-sm mt-1">Gestiona tu información personal y preferencias de la cuenta</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Columna Izquierda: Tarjeta Principal del Usuario -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 flex flex-col items-center text-center shadow-lg">
          <div class="relative w-32 h-32 mb-4 group cursor-pointer">
            <div class="w-full h-full rounded-full bg-studia-dark border-4 border-studia-purple overflow-hidden">
              <img :src="`https://api.dicebear.com/7.x/avataaars/svg?seed=${user?.nombre || 'Usuario'}`" alt="Avatar" class="w-full h-full object-cover" />
            </div>
          </div>
          
          <h3 class="text-xl font-bold text-white">{{ user?.nombre || 'Cargando...' }}</h3>
          <p class="text-studia-purple font-mono text-sm mb-1">@{{ user?.email?.split('@')[0] || 'studia' }}</p>
          <div class="inline-flex items-center gap-1 bg-gray-800 px-3 py-1 rounded-full text-xs text-gray-300 mt-2 uppercase">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            {{ user?.rol || 'Usuario Activo' }}
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-studia-card rounded-xl border border-gray-800 overflow-hidden shadow-lg">
          <button @click="cerrarSesion" class="w-full flex items-center justify-between p-4 text-red-400 hover:bg-red-500/10 transition-colors cursor-pointer">
            <span class="flex items-center gap-3 text-sm font-bold">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              Cerrar Sesión
            </span>
            <span>→</span>
          </button>
        </div>
      </div>

      <!-- Columna Derecha: Formularios de Información -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Información Académica -->
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg">
          <h4 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-gray-800 pb-2">
            <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
            Información Académica
          </h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Programa Educativo</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                Ingeniería en Desarrollo y Gestión del Software
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Matrícula (UTTEC)</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full font-mono">
                9IDS2 - 2026
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Grupo Asignado</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                9IDS2 (Cuatrimestre 9)
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Proyecto Activo</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                StudIA (Gemini AI API)
              </div>
            </div>
          </div>
        </div>

        <!-- Información de Contacto y Edición -->
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg">
          <h4 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-gray-800 pb-2">
            <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            Datos Personales
          </h4>
          
          <form @submit.prevent="actualizarPerfil" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs text-gray-500 mb-1">Nombre Completo</label>
                <input v-model="form.nombre" type="text" required class="bg-transparent border-b border-gray-700 text-white px-1 py-1 focus:outline-none focus:border-studia-purple transition-colors w-full text-sm" />
              </div>
              <div>
                <label class="block text-xs text-gray-500 mb-1">Correo Electrónico</label>
                <input v-model="form.email" type="email" required class="bg-transparent border-b border-gray-700 text-white px-1 py-1 focus:outline-none focus:border-studia-purple transition-colors w-full text-sm" />
              </div>
            </div>

            <div class="pt-4 text-right">
              <button type="submit" :disabled="guardando" class="bg-studia-purple text-white font-bold py-2 px-6 rounded-full hover:bg-purple-600 transition-colors text-sm shadow-lg cursor-pointer disabled:opacity-50">
                {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../api/axios'

const router = useRouter()
const user = ref(null)
const guardando = ref(false)

const form = ref({
  nombre: '',
  email: ''
})

onMounted(async () => {
  try {
    const res = await api.get('/user')
    user.value = res.data
    form.value.nombre = res.data.nombre
    form.value.email = res.data.email
  } catch (error) {
    console.error('Error al obtener los datos del usuario:', error)
  }
})

const actualizarPerfil = async () => {
  guardando.value = true
  try {
    const res = await api.put('/user/profile', form.value)
    user.value = res.data.user || form.value
    alert('¡Perfil actualizado correctamente!')
  } catch (error) {
    alert(error.response?.data?.message || 'Error al actualizar el perfil.')
  } finally {
    guardando.value = false
  }
}

const cerrarSesion = async () => {
  try {
    await api.post('/logout')
  } catch (e) {
    // Forzar limpieza local
  }
  localStorage.removeItem('token')
  router.push('/login')
}
</script>