<template>
  <div v-if="cargando" class="text-center py-16 text-gray-400 text-sm">
    Cargando datos de la entrega...
  </div>

  <div v-else-if="!entrega" class="text-center py-16 text-red-400 text-sm">
    No se encontró la información de esta entrega.
  </div>

  <div v-else class="space-y-6 max-w-5xl mx-auto">
    <!-- Encabezado y Botón Volver -->
    <div class="flex items-center justify-between border-b border-gray-800 pb-6">
      <div>
        <button @click="$router.back()" class="text-xs text-gray-400 hover:text-white flex items-center gap-1.5 transition-colors cursor-pointer mb-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Volver a las entregas
        </button>
        <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Evaluación Inteligente</h2>
      </div>
      <div class="w-10 h-10 rounded-full bg-gray-800 border border-purple-500/30 flex items-center justify-center text-white font-bold text-sm">
        {{ entrega.alumno?.nombre?.charAt(0).toUpperCase() }}
      </div>
    </div>

    <!-- TARJETA DE INFORMACIÓN DEL TRABAJO DEL ALUMNO -->
    <div class="bg-gray-900/80 p-6 rounded-xl border border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider bg-purple-900/40 text-purple-300 px-2.5 py-1 rounded border border-purple-500/20">
          {{ entrega.tarea?.materia?.nombre }}
        </span>
        <h3 class="text-xl font-bold text-white mt-2">{{ entrega.tarea?.titulo }}</h3>
        <p class="text-xs text-gray-400 mt-1">
          Estudiante: <strong class="text-gray-200">{{ entrega.alumno?.nombre }}</strong> ({{ entrega.alumno?.email }})
        </p>
      </div>

      <div class="flex items-center gap-3">
        <a v-if="entrega.archivo_url" :href="obtenerUrlStorage(entrega.archivo_url)" target="_blank" class="bg-gray-800 hover:bg-studia-purple border border-gray-700 text-white px-4 py-2 rounded-lg text-xs font-bold flex items-center gap-2 transition-all cursor-pointer">
          📄 Ver Archivo Adjunto
        </a>
        <div class="text-right">
          <span class="text-xs text-gray-500 block">Puntaje Máximo:</span>
          <span class="text-sm font-extrabold text-purple-300">{{ entrega.tarea?.puntaje_maximo }} pts</span>
        </div>
      </div>
    </div>

    <!-- Comentario o contenido en texto enviado por el alumno -->
    <div v-if="entrega.contenido" class="bg-studia-card p-5 rounded-xl border border-gray-800">
      <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Nota / Enlace enviado por el estudiante:</h4>
      <p class="text-sm text-gray-200 font-mono bg-gray-900 p-3 rounded-lg border border-gray-800 break-all">{{ entrega.contenido }}</p>
    </div>

    <!-- ========================================== -->
    <!-- ESTADO 1: AÚN NO EVALUADO POR GEMINI AI    -->
    <!-- ========================================== -->
    <div v-if="entrega.estado === 'entregado' && !datosIA" class="bg-studia-card p-12 rounded-2xl border border-studia-purple/30 text-center space-y-6 relative overflow-hidden shadow-[0_0_25px_rgba(147,51,234,0.15)]">
      <div class="absolute top-0 right-0 -mt-12 -mr-12 w-48 h-48 bg-studia-purple opacity-10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-studia-purple/20 to-blue-500/20 border border-purple-500/40 flex items-center justify-center text-purple-400 mx-auto shadow-lg animate-pulse">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
      </div>

      <div class="max-w-md mx-auto space-y-2">
        <h3 class="text-xl font-bold text-white">Asistente de Pre-evaluación StudIA</h3>
        <p class="text-xs text-gray-400 leading-relaxed">
          Al presionar el botón, Google Gemini analizará el trabajo y las instrucciones de la tarea para sugerirte una calificación y redactar una retroalimentación detallada al instante.
        </p>
      </div>

      <button 
        @click="solicitarEvaluacionIA" 
        :disabled="evaluando" 
        class="bg-gradient-to-r from-studia-purple to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-extrabold px-8 py-3.5 rounded-xl shadow-xl transition-all cursor-pointer disabled:opacity-50 text-sm flex items-center justify-center gap-3 mx-auto"
      >
        <svg v-if="evaluando" class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <span>{{ evaluando ? 'Gemini está analizando el trabajo...' : '✨ Generar Pre-evaluación con Gemini' }}</span>
      </button>
    </div>

    <!-- ========================================== -->
    <!-- ESTADO 2: REPORTE GENERADO (Tu diseño visual) -->
    <!-- ========================================== -->
    <div v-else-if="datosIA" class="space-y-6">
      
      <!-- Tarjeta Principal de Reporte IA -->
      <div class="bg-studia-card p-8 rounded-2xl border border-studia-purple/40 shadow-[0_0_20px_rgba(147,51,234,0.15)] relative overflow-hidden">
        <div class="absolute top-0 right-0 -mt-10 -mr-10 w-48 h-48 bg-studia-purple opacity-10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Cabecera -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 border-b border-gray-800 pb-6 relative z-10">
          <div>
            <div class="flex items-center gap-2 mb-1.5">
              <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
              <h3 class="text-purple-400 text-xs font-extrabold tracking-widest uppercase">Reporte Generado por Gemini AI</h3>
            </div>
            <h2 class="text-2xl font-bold text-white">{{ entrega.tarea?.titulo }}</h2>
          </div>
          
          <div :class="entrega.estado === 'calificado' ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-green-500/10 text-green-400 border-green-500/20'" class="border px-4 py-1.5 rounded-full text-xs font-bold flex items-center gap-2 self-start md:self-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ entrega.estado === 'calificado' ? 'Calificación Publicada' : 'Análisis IA Completado' }}</span>
          </div>
        </div>

        <!-- Puntuación Sugerida y Barra -->
        <div class="mb-8 relative z-10">
          <div class="flex justify-between items-end mb-2">
            <div>
              <h4 class="text-base font-bold text-white">Puntaje Sugerido por IA</h4>
              <p class="text-[11px] text-gray-500">Basado en el cumplimiento estricto de las instrucciones</p>
            </div>
            <span class="text-3xl font-extrabold text-green-400">
              {{ datosIA.calificacion_sugerida }}<span class="text-base text-gray-500">/{{ entrega.tarea?.puntaje_maximo }}</span>
            </span>
          </div>
          <div class="w-full bg-gray-800 rounded-full h-3 mb-1.5 overflow-hidden">
            <div class="bg-gradient-to-r from-studia-purple to-green-400 h-full rounded-full transition-all duration-1000" :style="`width: ${porcentajeScore}%`"></div>
          </div>
        </div>

        <!-- Retroalimentación Detallada -->
        <div class="relative z-10 space-y-4">
          <h4 class="text-base font-bold text-white">Análisis y Retroalimentación</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Puntos Fuertes -->
            <div class="bg-gray-900/90 p-5 rounded-xl border border-green-500/20 space-y-2">
              <h5 class="text-xs font-bold text-green-400 uppercase tracking-wider flex items-center gap-1.5">
                <span>★</span> Puntos Fuertes
              </h5>
              <p class="text-xs text-gray-300 leading-relaxed">{{ datosIA.puntos_fuertes || 'No se registraron observaciones específicas.' }}</p>
            </div>

            <!-- Áreas de Mejora -->
            <div class="bg-gray-900/90 p-5 rounded-xl border border-purple-500/20 space-y-2">
              <h5 class="text-xs font-bold text-purple-300 uppercase tracking-wider flex items-center gap-1.5">
                <span>▲</span> Áreas de Oportunidad
              </h5>
              <p class="text-xs text-gray-300 leading-relaxed">{{ datosIA.areas_mejora || 'No se registraron observaciones específicas.' }}</p>
            </div>
          </div>

          <!-- Mensaje Motivacional del Asistente -->
          <div v-if="datosIA.retroalimentacion_general" class="bg-studia-dark p-4 rounded-xl border border-gray-800 text-xs text-gray-300 italic">
            "{{ datosIA.retroalimentacion_general }}"
          </div>
        </div>

        <!-- Botón para regenerar si el docente quiere otra opinión -->
        <div class="mt-6 pt-4 border-t border-gray-800/80 flex justify-end">
          <button @click="solicitarEvaluacionIA" :disabled="evaluando" class="text-[11px] font-semibold text-purple-400 hover:text-purple-300 flex items-center gap-1 transition-colors cursor-pointer disabled:opacity-50">
            <span>↻ Volver a analizar con Gemini</span>
          </button>
        </div>
      </div>

      <!-- Alerta Informativa (Tu diseño) -->
      <div class="bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded-r-xl flex gap-4 items-start shadow-lg">
        <svg class="w-6 h-6 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
          <h4 class="text-white font-bold text-xs">Evaluación Preliminar de Inteligencia Artificial</h4>
          <p class="text-gray-400 text-[11px] mt-0.5">Este reporte es una sugerencia generada por Google Gemini. Como docente, tienes el control total para modificar la calificación y editar los comentarios antes de enviarlos oficialmente al estudiante.</p>
        </div>
      </div>

      <!-- ======================================================== -->
      <!-- PANEL DE EDICIÓN Y VALIDACIÓN DOCENTE (Tu última palabra) -->
      <!-- ======================================================== -->
      <div class="bg-studia-card p-8 rounded-2xl border border-gray-800 shadow-xl space-y-6">
        <div class="border-b border-gray-800 pb-4">
          <h3 class="text-lg font-bold text-white flex items-center gap-2">
            <span>✔</span> Validación y Publicación de Nota
          </h3>
          <p class="text-xs text-gray-400 mt-1">Ajusta la calificación o complementa los comentarios si lo consideras necesario. Al publicar, el estudiante podrá ver su resultado en su perfil.</p>
        </div>

        <form @submit.prevent="publicarCalificacion" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Calificación Final Editable -->
            <div class="md:col-span-1">
              <label class="block text-xs font-bold text-purple-300 uppercase tracking-wider mb-1.5">Calificación Oficial *</label>
              <div class="relative">
                <input 
                  v-model="formFinal.calificacion_final" 
                  type="number" 
                  step="0.1" 
                  min="0" 
                  :max="entrega.tarea?.puntaje_maximo" 
                  required 
                  class="w-full bg-gray-900 border border-purple-500/50 rounded-xl px-4 py-3 text-white font-extrabold text-2xl focus:outline-none focus:border-studia-purple text-center"
                />
                <span class="absolute right-4 top-4 text-xs text-gray-500 font-bold">/ {{ entrega.tarea?.puntaje_maximo }}</span>
              </div>
            </div>

            <!-- Comentarios Finales para el Estudiante -->
            <div class="md:col-span-2">
              <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-1.5">Comentarios de Retroalimentación Oficial *</label>
              <textarea 
                v-model="formFinal.retroalimentacion_final" 
                rows="4" 
                required 
                placeholder="Escribe o edita los comentarios que verá el alumno..." 
                class="w-full bg-gray-900 border border-gray-700 rounded-xl p-3 text-white text-xs focus:outline-none focus:border-studia-purple resize-y leading-relaxed"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end gap-4 pt-4 border-t border-gray-800/80">
            <button type="button" @click="$router.back()" class="px-5 py-2.5 text-xs font-bold text-gray-400 hover:text-white transition-colors cursor-pointer">
              Cancelar
            </button>
            <button 
              type="submit" 
              :disabled="publicando" 
              class="bg-green-600 hover:bg-green-500 text-white font-extrabold px-8 py-3 rounded-xl shadow-lg transition-all cursor-pointer disabled:opacity-50 text-xs flex items-center gap-2"
            >
              <span>{{ publicando ? 'Publicando...' : (entrega.estado === 'calificado' ? 'Actualizar Calificación' : '✔ Aprobar y Enviar al Estudiante') }}</span>
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../api/axios'

const route = useRoute()
const router = useRouter()

const entrega = ref(null)
const cargando = ref(true)
const evaluando = ref(false)
const publicando = ref(false)

// Formulario para la nota final del docente
const formFinal = ref({
  calificacion_final: 0,
  retroalimentacion_final: ''
})

// === SOLUCIÓN AL ARCHIVO EN CODESPACES ===
const obtenerUrlStorage = (ruta) => {
  if (!ruta) return '#'
  const base = api.defaults.baseURL ? api.defaults.baseURL.replace(/\/api$/, '') : 'http://localhost:8000'
  return `${base}/storage/${ruta}`
}

// 1. Extraemos y parseamos el JSON de la IA guardado en la base de datos
const datosIA = computed(() => {
  if (!entrega.value?.retroalimentacion_ia) return null
  try {
    return typeof entrega.value.retroalimentacion_ia === 'string' 
      ? JSON.parse(entrega.value.retroalimentacion_ia) 
      : entrega.value.retroalimentacion_ia
  } catch (e) {
    console.error('Error al parsear JSON de la IA:', e)
    return null
  }
})

// Cálculo dinámico para la barra de porcentaje morada/verde
const porcentajeScore = computed(() => {
  if (!datosIA.value || !entrega.value?.tarea?.puntaje_maximo) return 0
  const sugerida = Number(datosIA.value.calificacion_sugerida) || 0
  const max = Number(entrega.value.tarea.puntaje_maximo) || 100
  return Math.min(Math.round((sugerida / max) * 100), 100)
})

// 2. Cargar datos reales de la entrega desde el servidor
const cargarEntrega = async () => {
  cargando.value = true
  try {
    // Reutilizamos el endpoint de TareaDetalle pero filtrando la entrega en memoria o con endpoint directo
    // Para simplificar y hacerlo seguro, obtenemos la materia/tarea donde está esta entrega
    const resTarea = await api.get('/tareas')
    let entregaEncontrada = null
    
    // Buscamos la entrega dentro de las tareas del docente
    for (const t of resTarea.data) {
      if (t.entregas) {
        const busqueda = t.entregas.find(e => e.id === Number(route.params.id))
        if (busqueda) {
          entregaEncontrada = { ...busqueda, tarea: t }
          break
        }
      }
    }

    // Si no venía en la lista general, hacemos petición individual al endpoint de Tarea
    if (!entregaEncontrada && route.query.tarea_id) {
      const resT = await api.get(`/tareas/${route.query.tarea_id}`)
      const busq = resT.data.entregas?.find(e => e.id === Number(route.params.id))
      if (busq) entregaEncontrada = { ...busq, tarea: resT.data }
    }

    // Como respaldo final directo
    if (!entregaEncontrada) {
      // Hacemos una búsqueda directa en las tareas del profesor
      const misTareas = await api.get('/tareas')
      for (const t of misTareas.data) {
        const detalle = await api.get(`/tareas/${t.id}`)
        const e = detalle.data.entregas?.find(item => item.id === Number(route.params.id))
        if (e) {
          entregaEncontrada = { ...e, tarea: detalle.data }
          break
        }
      }
    }

    if (entregaEncontrada) {
      entrega.value = entregaEncontrada
      
      // Si ya hay evaluación final o de IA, llenamos el formulario del profesor
      if (entregaEncontrada.calificacion_final) {
        formFinal.value.calificacion_final = entregaEncontrada.calificacion_final
        formFinal.value.retroalimentacion_final = entregaEncontrada.retroalimentacion_final
      } else if (datosIA.value) {
        prellenarFormularioConIA()
      }
    }
  } catch (error) {
    console.error('Error al cargar la entrega:', error)
  } finally {
    cargando.value = false
  }
}

// Auxiliar para prellenar los campos editables con lo que sugirió Gemini
const prellenarFormularioConIA = () => {
  if (!datosIA.value) return
  formFinal.value.calificacion_final = datosIA.value.calificacion_sugerida || 0
  formFinal.value.retroalimentacion_final = `¡Hola! Aquí tienes la retroalimentación de tu actividad:\n\n★ Puntos fuertes: ${datosIA.value.puntos_fuertes || 'Buen trabajo en general.'}\n\n▲ Áreas de mejora: ${datosIA.value.areas_mejora || 'Sigue esforzándote para perfeccionar los detalles.'}\n\n${datosIA.value.retroalimentacion_general || ''}`.trim()
}

// 3. Solicitar el análisis a la API de Google Gemini en Laravel
const solicitarEvaluacionIA = async () => {
  evaluando.value = true
  try {
    const res = await api.post(`/entregas/${entrega.value.id}/evaluar-ia`)
    entrega.value = res.data.entrega
    
    // Al recibir respuesta, llenamos los cuadros del docente al instante
    setTimeout(() => {
      prellenarFormularioConIA()
    }, 100)
    
  } catch (error) {
    alert(error.response?.data?.message || 'Error al conectar con Google Gemini. Verifica tu API Key en Laravel.')
  } finally {
    evaluando.value = false
  }
}

// 4. Publicar la calificación oficial hacia el estudiante
const publicarCalificacion = async () => {
  publicando.value = true
  try {
    await api.post(`/entregas/${entrega.value.id}/calificar`, formFinal.value)
    alert('¡Evaluación publicada exitosamente! El estudiante ya puede consultar su nota.')
    await cargarEntrega()
  } catch (error) {
    alert(error.response?.data?.message || 'Hubo un error al guardar la calificación.')
  } finally {
    publicando.value = false
  }
}

onMounted(() => { cargarEntrega() })
</script>