<template>
  <div class="space-y-6">
    <!-- Encabezado -->
    <div class="border-b border-gray-800 pb-6 flex justify-between items-center">
      <div>
        <h2 class="text-3xl font-bold uppercase tracking-wider text-white">
          {{ esDocente ? 'Gestión de Tareas Publicadas' : 'Mis Tareas Pendientes' }}
        </h2>
        <p class="text-sm text-gray-400 mt-1">
          {{ esDocente ? 'Todas las actividades que has asignado en tus materias.' : 'Consulta tus entregas pendientes y calificaciones.' }}
        </p>
      </div>
    </div>

    <!-- Estados de Carga y dentro de las tareas -->
    <div v-if="cargando" class="text-center py-12 text-gray-400 text-sm">
      Cargando tareas de la base de datos...
    </div>

    <div v-else-if="tareas.length === 0" class="text-center py-16 bg-studia-card/50 rounded-xl border border-gray-800/80">
      <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
      <p class="text-gray-400 font-semibold text-base">No hay tareas registradas</p>
      <p class="text-gray-500 text-xs mt-1">Cuando se publiquen actividades en tus materias, aparecerán aquí.</p>
    </div>

    <!-- Lista de Tareas Reales -->
    <div v-else class="space-y-4">
      <div 
        v-for="tarea in tareas" 
        :key="tarea.id"
        @click="irATarea(tarea.id)"
        class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:border-studia-purple/50 transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-4 cursor-pointer shadow-lg group"
      >
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 rounded-xl bg-studia-purple/10 border border-studia-purple/30 flex items-center justify-center text-studia-purple shrink-0 group-hover:scale-105 transition-transform">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
          </div>
          <div>
            <span class="text-[11px] font-bold tracking-wider uppercase bg-purple-900/40 text-purple-300 px-2.5 py-0.5 rounded border border-purple-500/20">
              {{ tarea.materia?.nombre || 'General' }}
            </span>
            <h3 class="text-lg font-bold text-white mt-1 group-hover:text-studia-purple transition-colors">{{ tarea.titulo }}</h3>
            <p class="text-xs text-gray-400 line-clamp-1 mt-1" v-html="limpiarHtml(tarea.descripcion)"></p>
          </div>
        </div>

        <div class="flex items-center gap-6 self-end md:self-center shrink-0">
          <div class="text-right">
            <p class="text-xs text-gray-400">Fecha límite:</p>
            <p class="text-xs font-semibold text-gray-200">{{ formatearFecha(tarea.fecha_limite) }}</p>
          </div>

          <!-- Estado de Entrega dinámico para el Alumno -->
          <div v-if="!esDocente">
            <span 
              :class="{
                'bg-blue-500/20 text-blue-400 border-blue-500/30': obtenerEstadoEntrega(tarea) === 'Calificado',
                'bg-green-500/20 text-green-300 border-green-500/30': obtenerEstadoEntrega(tarea) === 'Entregada',
                'bg-yellow-500/20 text-yellow-300 border-yellow-500/30': obtenerEstadoEntrega(tarea) === 'Pendiente'
              }" 
              class="px-3 py-1.5 rounded-lg text-xs font-bold border inline-flex items-center gap-1.5"
            >
              {{ obtenerEstadoEntrega(tarea) === 'Calificado' ? '✔ Calificado' : (obtenerEstadoEntrega(tarea) === 'Entregada' ? '✔ Entregada' : '⏳ Pendiente') }}
              <span v-if="obtenerEstadoEntrega(tarea) === 'Calificado' && obtenerCalificacion(tarea) !== null" class="text-white font-extrabold ml-1">
                ({{ obtenerCalificacion(tarea) }})
              </span>
            </span>
          </div>

          <!-- Puntaje -->
          <div class="bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-800 text-center min-w-[70px]">
            <span class="text-xs font-extrabold text-purple-300">{{ tarea.puntaje_maximo }}</span>
            <span class="text-[10px] text-gray-500 block -mt-0.5">Puntos</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../api/axios'

const router = useRouter()
const authStore = useAuthStore()
const esDocente = computed(() => authStore.user?.rol === 'docente')

const tareas = ref([])
const cargando = ref(true)
const usuarioActual = ref(null)

const cargarDatosIniciales = async () => {
  cargando.value = true
  try {
    try {
      const resUser = await api.get('/user')
      usuarioActual.value = resUser.data
    } catch (e) {
      usuarioActual.value = authStore.user
    }

    const response = await api.get('/tareas')
    tareas.value = response.data
  } catch (error) {
    console.error('Error al cargar tareas:', error)
  } finally {
    cargando.value = false
  }
}

// Funciones auxiliares sincronizadas con el usuario real
const obtenerEntregaUsuario = (tarea) => {
  if (!tarea.entregas || tarea.entregas.length === 0) return null
  const user = usuarioActual.value || authStore.user
  if (!user) return null

  return tarea.entregas.find(e => 
    Number(e.alumno_id) === Number(user.id) || 
    Number(e.user_id) === Number(user.id) ||
    (e.alumno?.email && user.email && e.alumno.email.toLowerCase() === user.email.toLowerCase())
  )
}

const obtenerEstadoEntrega = (tarea) => {
  const entrega = obtenerEntregaUsuario(tarea)
  if (!entrega) return 'Pendiente'
  if (entrega.estado === 'calificado') return 'Calificado'
  return 'Entregada'
}

const obtenerCalificacion = (tarea) => {
  const entrega = obtenerEntregaUsuario(tarea)
  return entrega?.calificacion_final ?? null
}

const irATarea = (id) => { router.push(`/dashboard/tareas/${id}`) }

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return 'Sin fecha'
  return new Date(fechaStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const limpiarHtml = (html) => {
  return html ? html.replace(/<[^>]*>?/gm, '') : ''
}

onMounted(() => { cargarDatosIniciales() })
</script>