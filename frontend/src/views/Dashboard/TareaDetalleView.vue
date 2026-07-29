<template>
  <div>
    <!-- Botón de regreso -->
    <div class="mb-6">
      <RouterLink to="/dashboard/tareas" class="text-gray-400 hover:text-white flex items-center gap-2 transition-colors w-fit">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Volver a tareas
      </RouterLink>
    </div>

    <!-- Indicador de carga -->
    <div v-if="cargandoDetalle" class="text-white flex items-center gap-3">
      <span class="w-4 h-4 rounded-full bg-studia-purple animate-ping"></span>
      Cargando detalle de la tarea...
    </div>

    <!-- Tarjeta Principal -->
    <div v-else class="bg-studia-card p-8 rounded-xl border border-gray-800 mb-6 shadow-2xl">
      <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
        <div>
          <h2 class="text-3xl font-bold text-white mb-2">{{ tarea?.titulo || 'Tarea' }}</h2>
          <p class="text-studia-purple text-sm font-semibold">{{ tarea?.descripcion || 'Sin descripción' }}</p>
        </div>
        <div class="bg-studia-purple/20 text-studia-purple border border-studia-purple px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2 w-fit">
          <span class="w-2 h-2 rounded-full bg-studia-purple animate-pulse"></span>
          {{ authStore.user?.rol === 'docente' ? 'Vista docente' : 'Pendiente' }}
        </div>
      </div>

      <div class="flex items-center gap-6 text-sm text-gray-400 mb-8 border-b border-gray-800 pb-6">
        <p><span class="font-bold text-gray-300">Fecha límite:</span> {{ tarea?.fecha_entrega_limite || 'Sin fecha' }}</p>
        <p><span class="font-bold text-gray-300">Puntaje máximo:</span> {{ tarea?.puntaje_maximo || 100 }} pts</p>
      </div>

      <!-- 1. Instrucciones de la actividad -->
      <div class="mb-6">
        <h3 class="text-xl font-bold text-white mb-4">Instrucciones de la actividad</h3>
        <p class="text-gray-400 leading-relaxed text-sm bg-studia-dark p-6 rounded-lg border border-gray-800/50">
          {{ tarea?.descripcion || 'Sigue las instrucciones del profesor.' }}
        </p>
      </div>

      <!-- 2. AQUÍ ESTÁ LA RÚBRICA QUE EVALUARÁ LA IA -->
      <div class="mb-10">
        <h3 class="text-xl font-bold text-white mb-4">Criterios de Evaluación (Rúbrica para IA)</h3>
        <div class="bg-studia-purple/10 border border-studia-purple/30 p-6 rounded-lg">
          <p class="text-studia-purple font-semibold text-sm mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Parámetros que tomará en cuenta la evaluación automática:
          </p>
          <p class="text-gray-300 leading-relaxed text-sm">
            {{ tarea?.rubrica || 'El docente no definió criterios específicos. Se evaluará el contenido general.' }}
          </p>
        </div>
      </div>

      <!-- Sección de Entrega (Solo Visible para Alumnos/Estudiantes) -->
      <div v-if="authStore.user?.rol === 'alumno' || authStore.user?.rol === 'estudiante' || !authStore.user?.rol">
        <h3 class="text-xl font-bold text-white mb-4">Tu Trabajo</h3>
        
        <!-- Input File Oculto pero Vinculado mediante ref -->
        <input 
          ref="fileInput" 
          type="file" 
          class="hidden" 
          accept="application/pdf" 
          @change="seleccionarArchivo" 
        />

        <!-- Zona Clickeable/Drop Area -->
        <div 
          @click="abrirSelector" 
          class="border-2 border-dashed border-gray-700 hover:border-studia-purple bg-studia-dark/50 rounded-xl p-10 flex flex-col items-center justify-center text-center transition-all cursor-pointer group"
        >
          <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mb-4 group-hover:bg-studia-purple/20 group-hover:text-studia-purple transition-colors text-gray-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
          </div>
          <p class="text-white font-bold mb-1">Haz clic para buscar tu archivo PDF</p>
          <p class="text-gray-500 text-xs mb-6">Máximo 10MB (Formatos soportados: .pdf)</p>
          
          <span class="bg-white text-studia-dark font-bold py-2 px-6 rounded-full group-hover:bg-gray-200 transition-colors shadow-lg">
            {{ archivoSeleccionado ? 'Cambiar Archivo' : 'Seleccionar Archivo' }}
          </span>
        </div>

        <!-- Feedback de Archivo y Mensajes de Estado -->
        <div v-if="archivoSeleccionado" class="mt-4 text-sm text-gray-300 flex items-center gap-2 bg-gray-800/40 p-3 rounded-lg border border-gray-700">
          <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Archivo preparado: <strong class="text-white">{{ archivoSeleccionado.name }}</strong></span>
        </div>

        <div v-if="mensaje" class="mt-4 rounded-lg border border-green-500/20 bg-green-500/10 p-3 text-sm text-green-400">
          {{ mensaje }}
        </div>
        
        <div v-if="error" class="mt-4 rounded-lg border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-400">
          {{ error }}
        </div>

        <!-- Botón de Envío -->
        <div class="mt-8 flex justify-end border-t border-gray-800 pt-6">
          <button 
            class="bg-studia-purple hover:bg-opacity-90 text-white font-bold py-3 px-10 rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg" 
            :disabled="cargando || !archivoSeleccionado" 
            @click="entregarTarea"
          >
            {{ cargando ? 'Enviando y Evaluando con Gemini...' : 'Entregar Tarea' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// Referencia directa al input file del DOM
const fileInput = ref(null)

const tarea = ref(null)
const archivoSeleccionado = ref(null)
const cargando = ref(false)
const cargandoDetalle = ref(true)
const mensaje = ref('')
const error = ref('')

const abrirSelector = () => {
  if (fileInput.value) {
    fileInput.value.click()
  }
}

const seleccionarArchivo = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.type !== 'application/pdf') {
      error.value = 'El archivo debe ser en formato PDF.'
      archivoSeleccionado.value = null
      return
    }
    archivoSeleccionado.value = file
    error.value = ''
    mensaje.value = ''
  }
}

const cargarTarea = async () => {
  try {
    const response = await api.get(`/tareas/${route.params.id}`)
    tarea.value = response.data
  } catch (err) {
    console.error('Error al cargar la tarea:', err)
  } finally {
    cargandoDetalle.value = false
  }
}

const entregarTarea = async () => {
  if (!archivoSeleccionado.value) {
    error.value = 'Selecciona un archivo PDF antes de entregar.'
    return
  }

  cargando.value = true
  mensaje.value = ''
  error.value = ''

  const formData = new FormData()
  formData.append('tarea_id', route.params.id)
  formData.append('alumno_id', authStore.user?._id || authStore.user?.id || 'demo-alumno')
  formData.append('archivo', archivoSeleccionado.value)

  try {
    const response = await api.post('/entregas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    // Garantiza leer el ID de la entrega devuelto por Laravel / MongoDB
    const entregaData = response.data.entrega || response.data
    const entregaId = entregaData._id || entregaData.id

    mensaje.value = '¡Entrega recibida! Redirigiendo a la evaluación con IA...'
    
    // Redirección inmediata pasando el ID verdadero
    setTimeout(() => {
      router.push(`/dashboard/evaluacion-ia/${entregaId}`)
    }, 500)

  } catch (err) {
    console.error('Error al entregar la tarea:', err)
    error.value = err.response?.data?.error || err.response?.data?.message || 'No se pudo entregar la tarea. Intenta nuevamente.'
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  cargarTarea()
})
</script>