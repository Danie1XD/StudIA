<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '../../stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const esDocente = computed(() => authStore.user?.rol === 'docente')

// Estados generales
const cargando = ref(true)
const errorMsg = ref(null)

// ========== VISTA DOCENTE ==========
const entregasPendientes = ref([])
const entregaSeleccionada = ref(null)
const enviandoEvaluacion = ref(false)
const calificacionFinal = ref(0)
const comentariosDocente = ref('')
const usarCalificacionIA = ref(true)
const mensajeDocente = ref('')
const errorDocente = ref('')
const vistaDetalle = ref(false)

// ========== VISTA ALUMNO ==========
const misEntregas = ref([])
const entregaDetalle = ref(null)
const viendoDetalle = ref(false)

const cargarDatos = async () => {
  try {
    cargando.value = true
    errorMsg.value = null

    if (esDocente.value) {
      const docenteId = authStore.user?._id || authStore.user?.id || 'demo-docente'
      const response = await api.get('/entregas/docente/pendientes', {
        params: { docente_id: docenteId }
      })
      entregasPendientes.value = response.data
    } else {
      const alumnoId = authStore.user?._id || authStore.user?.id || 'demo-alumno'
      const response = await api.get(`/entregas/alumno/${alumnoId}`)
      misEntregas.value = response.data
    }
  } catch (err) {
    console.error('Error al cargar datos:', err)
    errorMsg.value = 'No se pudieron cargar los datos. Verifica que el backend esté corriendo.'
  } finally {
    cargando.value = false
  }
}

// ========== FUNCIONES DOCENTE ==========
const verDetalleEntrega = (entrega) => {
  entregaSeleccionada.value = entrega
  vistaDetalle.value = true
  calificacionFinal.value = entrega.puntaje_sugerido_ia || 0
  comentariosDocente.value = ''
  usarCalificacionIA.value = true
  mensajeDocente.value = ''
  errorDocente.value = ''
}

const toggleCalificacionIA = () => {
  usarCalificacionIA.value = !usarCalificacionIA.value
  if (usarCalificacionIA.value) {
    calificacionFinal.value = entregaSeleccionada.value?.puntaje_sugerido_ia || 0
  }
}

const volverListaDocente = () => {
  vistaDetalle.value = false
  entregaSeleccionada.value = null
}

const enviarEvaluacionDocente = async () => {
  if (!entregaSeleccionada.value) return

  enviandoEvaluacion.value = true
  mensajeDocente.value = ''
  errorDocente.value = ''

  const docenteId = authStore.user?._id || authStore.user?.id || 'demo-docente'

  try {
    await api.post(`/entregas/${entregaSeleccionada.value._id}/evaluar`, {
      calificacion_final: calificacionFinal.value,
      comentarios: comentariosDocente.value,
      docente_id: docenteId
    })

    mensajeDocente.value = `✅ Calificación enviada correctamente: ${calificacionFinal.value}/100`

    // Eliminar de la lista de pendientes
    entregasPendientes.value = entregasPendientes.value.filter(
      e => e._id !== entregaSeleccionada.value._id
    )

    setTimeout(() => {
      volverListaDocente()
    }, 1500)

  } catch (err) {
    console.error('Error al enviar evaluación:', err)
    errorDocente.value = err.response?.data?.error || 'No se pudo enviar la calificación.'
  } finally {
    enviandoEvaluacion.value = false
  }
}

// ========== FUNCIONES ALUMNO ==========
const verDetalleAlumno = (entrega) => {
  entregaDetalle.value = entrega
  viendoDetalle.value = true
}

const volverListaAlumno = () => {
  viendoDetalle.value = false
  entregaDetalle.value = null
}

const getEstadoBadge = (estado) => {
  const map = {
    'Pendiente': { color: 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20', icon: 'clock' },
    'Evaluado por IA': { color: 'text-blue-400 bg-blue-500/10 border-blue-500/20', icon: 'sparkles' },
    'Validado': { color: 'text-green-400 bg-green-500/10 border-green-500/20', icon: 'check' }
  }
  return map[estado] || { color: 'text-gray-400 bg-gray-500/10 border-gray-500/20', icon: 'help' }
}

onMounted(() => {
  cargarDatos()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">
        {{ esDocente ? 'Revisar Entregas' : 'Mis Evaluaciones' }}
      </h2>
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <!-- Cargando -->
    <div v-if="cargando" class="bg-studia-card p-12 rounded-xl border border-studia-purple/30 text-center">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-studia-purple mb-4"></div>
      <p class="text-gray-400">Cargando evaluaciones...</p>
    </div>

    <!-- Error -->
    <div v-else-if="errorMsg" class="bg-red-500/10 border border-red-500/30 p-6 rounded-xl text-red-400 text-center">
      <p>{{ errorMsg }}</p>
      <button @click="cargarDatos" class="mt-4 bg-studia-purple text-white px-4 py-2 rounded-lg text-sm">Reintentar</button>
    </div>

    <!-- ========== VISTA DOCENTE ========== -->
    <template v-else-if="esDocente">

      <!-- Lista de entregas pendientes -->
      <div v-if="!vistaDetalle">
        <div class="bg-studia-card p-4 rounded-xl border border-gray-800 mb-4">
          <p class="text-gray-400 text-sm">
            Tienes <strong class="text-white">{{ entregasPendientes.length }}</strong> entrega(s) pendiente(s) de revisión.
          </p>
        </div>

        <div v-if="entregasPendientes.length === 0" class="bg-studia-card p-12 rounded-xl border border-gray-800 text-center">
          <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <p class="text-gray-500">No hay entregas pendientes. ¡Todo está al día!</p>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="entrega in entregasPendientes"
            :key="entrega._id"
            @click="verDetalleEntrega(entrega)"
            class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:border-studia-purple/50 cursor-pointer transition-all shadow-md hover:shadow-studia-purple/5"
          >
            <div class="flex justify-between items-start gap-4">
              <div class="flex-1">
                <h3 class="text-lg font-bold text-white mb-1">{{ entrega.titulo_tarea }}</h3>
                <p class="text-xs text-gray-400 mb-3">
                  Alumno ID: <span class="text-gray-300 font-mono">{{ entrega.alumno_id }}</span>
                </p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <span>📅 {{ entrega.fecha_envio }}</span>
                  <span :class="entrega.tiene_evaluacion_ia ? 'text-green-400' : 'text-yellow-400'">
                    🤖 {{ entrega.tiene_evaluacion_ia ? 'Evaluado por IA' : 'Sin evaluación IA' }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <div
                  class="text-xs font-bold px-3 py-1 rounded-full border"
                  :class="getEstadoBadge(entrega.estado).color"
                >
                  {{ entrega.estado }}
                </div>
                <p v-if="entrega.puntaje_sugerido_ia" class="text-sm text-gray-400 mt-2">
                  IA: <span class="text-white font-bold">{{ entrega.puntaje_sugerido_ia }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detalle de entrega seleccionada (Docente revisando) -->
      <div v-else-if="entregaSeleccionada" class="max-w-4xl mx-auto">
        <button @click="volverListaDocente" class="text-gray-400 hover:text-white flex items-center gap-2 transition-colors mb-6">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Volver a lista
        </button>

        <!-- Info de la entrega -->
        <div class="bg-studia-card p-8 rounded-xl border border-gray-800 mb-6">
          <div class="flex justify-between items-start mb-6">
            <div>
              <h2 class="text-2xl font-bold text-white mb-1">{{ entregaSeleccionada.titulo_tarea }}</h2>
              <p class="text-gray-400 text-sm">Alumno: <span class="text-gray-300 font-mono">{{ entregaSeleccionada.alumno_id }}</span></p>
              <p class="text-gray-400 text-sm">Fecha de entrega: {{ entregaSeleccionada.fecha_envio }}</p>
            </div>
            <div
              class="text-xs font-bold px-4 py-2 rounded-full border"
              :class="getEstadoBadge(entregaSeleccionada.estado).color"
            >
              {{ entregaSeleccionada.estado }}
            </div>
          </div>

          <!-- Contenido del texto extraído -->
          <div v-if="entregaSeleccionada.contenido_texto" class="mb-6">
            <h4 class="text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Contenido de la entrega</h4>
            <div class="bg-studia-dark p-4 rounded-lg border border-gray-800/50 max-h-40 overflow-y-auto">
              <p class="text-gray-300 text-sm whitespace-pre-line">{{ entregaSeleccionada.contenido_texto }}</p>
            </div>
          </div>

          <!-- Evaluación de IA -->
          <div v-if="entregaSeleccionada.tiene_evaluacion_ia" class="bg-blue-500/5 border border-blue-500/20 rounded-xl p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
              <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <h4 class="text-lg font-bold text-white">Evaluación de Gemini (IA)</h4>
            </div>

            <div class="flex items-center justify-between mb-4">
              <p class="text-gray-400 text-sm">Puntaje sugerido</p>
              <span class="text-2xl font-bold text-green-400">{{ entregaSeleccionada.puntaje_sugerido_ia }}<span class="text-base text-gray-500">/100</span></span>
            </div>

            <div class="w-full bg-gray-800 rounded-full h-2 mb-4 overflow-hidden">
              <div
                class="bg-gradient-to-r from-studia-purple to-green-400 h-full rounded-full transition-all"
                :style="{ width: `${entregaSeleccionada.puntaje_sugerido_ia}%` }"
              ></div>
            </div>

            <div>
              <h5 class="text-sm font-bold text-gray-400 mb-2">Retroalimentación de IA</h5>
              <div class="bg-studia-dark p-4 rounded-lg border border-gray-800/50">
                <p class="text-gray-300 text-sm whitespace-pre-line">{{ entregaSeleccionada.feedback_ia }}</p>
              </div>
            </div>
          </div>

          <!-- Formulario de evaluación docente -->
          <div class="border-t border-gray-800 pt-6 mt-6">
            <h4 class="text-lg font-bold text-white mb-4">Tu Evaluación (Docente)</h4>

            <!-- Toggle usar calificación IA -->
            <div class="flex items-center gap-3 mb-6">
              <button
                @click="toggleCalificacionIA"
                :class="usarCalificacionIA ? 'bg-studia-purple' : 'bg-gray-700'"
                class="w-12 h-6 rounded-full relative transition-colors"
              >
                <span
                  :class="usarCalificacionIA ? 'translate-x-6' : 'translate-x-1'"
                  class="w-4 h-4 bg-white rounded-full absolute top-1 transition-transform"
                ></span>
              </button>
              <span class="text-sm text-gray-400">Usar calificación sugerida por IA</span>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-xs text-gray-500 mb-1 font-bold">Calificación final (1-100)</label>
                <input
                  v-model.number="calificacionFinal"
                  type="number"
                  min="1"
                  max="100"
                  :disabled="usarCalificacionIA"
                  class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                />
              </div>
            </div>

            <div class="mb-6">
              <label class="block text-xs text-gray-500 mb-1 font-bold">Comentarios para el alumno</label>
              <textarea
                v-model="comentariosDocente"
                rows="4"
                placeholder="Escribe aquí tus comentarios y observaciones..."
                class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white text-sm resize-none"
              ></textarea>
            </div>

            <!-- Mensajes -->
            <div v-if="mensajeDocente" class="bg-green-500/10 border border-green-500/20 p-4 rounded-lg text-green-400 text-sm mb-4">
              {{ mensajeDocente }}
            </div>
            <div v-if="errorDocente" class="bg-red-500/10 border border-red-500/30 p-4 rounded-lg text-red-400 text-sm mb-4">
              {{ errorDocente }}
            </div>

            <!-- Botón enviar -->
            <div class="flex justify-end gap-4">
              <button
                @click="volverListaDocente"
                class="bg-gray-800 text-gray-300 px-6 py-3 rounded-lg text-sm font-bold hover:bg-gray-700 transition-colors"
              >
                Cancelar
              </button>
              <button
                @click="enviarEvaluacionDocente"
                :disabled="enviandoEvaluacion"
                class="bg-studia-purple text-white px-8 py-3 rounded-lg text-sm font-bold hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <svg v-if="enviandoEvaluacion" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ enviandoEvaluacion ? 'Enviando...' : 'Enviar Calificación' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ========== VISTA ALUMNO ========== -->
    <template v-else>

      <!-- Lista de entregas del alumno -->
      <div v-if="!viendoDetalle">
        <p class="text-gray-400 text-sm mb-6">
          Historial de tus entregas y evaluaciones.
        </p>

        <div v-if="misEntregas.length === 0" class="bg-studia-card p-12 rounded-xl border border-gray-800 text-center">
          <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
          <p class="text-gray-500 mb-2">No has entregado ninguna tarea aún.</p>
          <router-link to="/dashboard/tareas" class="text-studia-purple text-sm font-bold hover:text-white transition-colors">
            Ir a Tareas Pendientes →
          </router-link>
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="entrega in misEntregas"
            :key="entrega._id"
            @click="verDetalleAlumno(entrega)"
            class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:border-studia-purple/50 cursor-pointer transition-all shadow-md"
          >
            <div class="flex justify-between items-start gap-4">
              <div class="flex-1">
                <h3 class="text-lg font-bold text-white mb-1">{{ entrega.titulo_tarea }}</h3>
                <p class="text-xs text-gray-400 mb-3">Entregado: {{ entrega.fecha_envio }}</p>
                <div class="flex flex-wrap items-center gap-3 text-xs">
                  <!-- Estado -->
                  <span
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full border font-bold"
                    :class="getEstadoBadge(entrega.estado).color"
                  >
                    {{ entrega.estado }}
                  </span>

                  <!-- Calificación IA -->
                  <span v-if="entrega.puntaje_sugerido_ia !== null" class="text-blue-400">
                    🤖 IA: {{ entrega.puntaje_sugerido_ia }}/100
                  </span>

                  <!-- Calificación Final -->
                  <span v-if="entrega.calificacion_final !== null" class="text-green-400 font-bold">
                    ✅ Final: {{ entrega.calificacion_final }}/100
                  </span>
                </div>
              </div>
              <svg class="w-5 h-5 text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Detalle de entrega seleccionada (Alumno viendo) -->
      <div v-else-if="entregaDetalle" class="max-w-4xl mx-auto">
        <button @click="volverListaAlumno" class="text-gray-400 hover:text-white flex items-center gap-2 transition-colors mb-6">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Volver a mis entregas
        </button>

        <div class="bg-studia-card p-8 rounded-xl border border-gray-800">
          <!-- Cabecera -->
          <div class="flex justify-between items-start mb-8 border-b border-gray-800 pb-6">
            <div>
              <h2 class="text-2xl font-bold text-white mb-2">{{ entregaDetalle.titulo_tarea }}</h2>
              <p class="text-gray-400 text-sm">Entregado el: {{ entregaDetalle.fecha_envio }}</p>
            </div>
            <span
              class="text-xs font-bold px-4 py-2 rounded-full border"
              :class="getEstadoBadge(entregaDetalle.estado).color"
            >
              {{ entregaDetalle.estado }}
            </span>
          </div>

          <!-- Evaluación IA -->
          <div v-if="entregaDetalle.puntaje_sugerido_ia !== null" class="mb-8">
            <div class="flex items-center gap-2 mb-4">
              <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
              <h4 class="text-lg font-bold text-white">Evaluación Preliminar (IA - Gemini)</h4>
            </div>

            <div class="flex items-center justify-between mb-4">
              <span class="text-gray-400 text-sm">Puntaje sugerido</span>
              <span class="text-2xl font-bold text-blue-400">{{ entregaDetalle.puntaje_sugerido_ia }}<span class="text-base text-gray-500">/100</span></span>
            </div>

            <div class="w-full bg-gray-800 rounded-full h-2 mb-4 overflow-hidden">
              <div
                class="bg-gradient-to-r from-studia-purple to-blue-400 h-full rounded-full transition-all"
                :style="{ width: `${entregaDetalle.puntaje_sugerido_ia}%` }"
              ></div>
            </div>

            <div v-if="entregaDetalle.feedback_ia">
              <h5 class="text-sm font-bold text-gray-400 mb-2">Retroalimentación de IA</h5>
              <div class="bg-studia-dark p-4 rounded-lg border border-gray-800/50">
                <p class="text-gray-300 text-sm whitespace-pre-line">{{ entregaDetalle.feedback_ia }}</p>
              </div>
            </div>
          </div>

          <!-- Evaluación final del docente -->
          <div v-if="entregaDetalle.calificacion_final !== null" class="border-t border-gray-800 pt-6 mt-6">
            <div class="flex items-center gap-2 mb-4">
              <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h4 class="text-lg font-bold text-white">Calificación del Docente (Final)</h4>
            </div>

            <div class="bg-green-500/5 border border-green-500/20 rounded-xl p-6">
              <div class="flex items-center justify-between mb-4">
                <span class="text-gray-400 text-sm">Calificación final</span>
                <span class="text-3xl font-bold text-green-400">{{ entregaDetalle.calificacion_final }}<span class="text-lg text-gray-500">/100</span></span>
              </div>

              <div class="w-full bg-gray-800 rounded-full h-3 mb-4">
                <div
                  class="bg-gradient-to-r from-green-500 to-green-400 h-full rounded-full"
                  :style="{ width: `${entregaDetalle.calificacion_final}%` }"
                ></div>
              </div>

              <div v-if="entregaDetalle.comentarios_docente">
                <h5 class="text-sm font-bold text-gray-400 mb-2">Comentarios del profesor</h5>
                <div class="bg-studia-dark p-4 rounded-lg border border-gray-800/50">
                  <p class="text-gray-300 text-sm whitespace-pre-line">{{ entregaDetalle.comentarios_docente }}</p>
                </div>
              </div>

              <p v-if="entregaDetalle.fecha_validacion" class="text-xs text-gray-500 mt-4">
                Validado el: {{ entregaDetalle.fecha_validacion }}
              </p>
            </div>
          </div>

          <!-- Mensaje si aún no hay calificación final -->
          <div v-else class="bg-yellow-500/10 border-l-4 border-yellow-500 p-4 rounded-r-lg mt-6">
            <div class="flex gap-3">
              <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <div>
                <h4 class="text-white font-bold text-sm">Evaluación Pendiente</h4>
                <p class="text-gray-400 text-xs mt-1">El docente aún no ha revisado tu entrega. Recibirás una notificación cuando esté lista.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
