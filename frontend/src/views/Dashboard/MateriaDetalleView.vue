<template>
  <div v-if="cargando" class="text-center py-16 text-gray-400 text-sm">
    Cargando el salón de clases...
  </div>

  <div v-else-if="!materia" class="text-center py-16 text-red-400 text-sm">
    No se encontró información de la materia.
  </div>

  <div v-else class="space-y-6">
    <!-- BANNER ESTILO CLASSROOM (Con degradado morado/azul de StudIA) -->
    <div class="relative rounded-2xl bg-gradient-to-r from-studia-purple via-purple-900 to-blue-900 p-8 text-white shadow-2xl overflow-hidden border border-purple-500/30">
      <div class="relative z-10 max-w-2xl">
        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">{{ materia.nombre }}</h1>
        <p class="text-purple-200 text-sm mb-6 line-clamp-2">{{ materia.descripcion || 'Sin encuadre registrado.' }}</p>
        <div class="flex flex-wrap items-center gap-4 text-xs font-medium">
          <span class="bg-black/40 px-3 py-1.5 rounded-full border border-white/10">
            🎓 Prof. {{ materia.docente?.nombre }}
          </span>
          <!-- Código visible solo para el docente -->
          <span v-if="esDocente" @click="copiarCodigo" title="Clic para copiar" class="bg-studia-dark/80 hover:bg-studia-dark px-3 py-1.5 rounded-full border border-purple-400/50 cursor-pointer font-mono text-purple-300 transition-colors">
            🔑 Código: {{ materia.codigo_acceso }}
          </span>
        </div>
      </div>
      <!-- Ícono de fondo decorativo -->
      <svg class="absolute -right-6 -bottom-6 w-64 h-64 text-white/5 pointer-events-none" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9L12 15L21 10.09V17H23V9M5 13.18V17.18L12 21L19 17.18V13.18L12 17L5 13.18Z" /></svg>
    </div>

    <!-- NAVEGACIÓN POR PESTAÑAS (Tablón, Trabajo de clase, Personas) -->
    <div class="flex border-b border-gray-800 gap-8 px-4 text-sm font-semibold">
      <button 
        @click="pestañaActiva = 'tablon'" 
        :class="pestañaActiva === 'tablon' ? 'text-studia-purple border-b-2 border-studia-purple pb-3' : 'text-gray-400 hover:text-white pb-3 transition-colors cursor-pointer'"
      >
        Tablón
      </button>
      <button 
        @click="pestañaActiva = 'trabajos'" 
        :class="pestañaActiva === 'trabajos' ? 'text-studia-purple border-b-2 border-studia-purple pb-3' : 'text-gray-400 hover:text-white pb-3 transition-colors cursor-pointer'"
      >
        Trabajo de clase
      </button>
      <button 
        @click="pestañaActiva = 'personas'" 
        :class="pestañaActiva === 'personas' ? 'text-studia-purple border-b-2 border-studia-purple pb-3' : 'text-gray-400 hover:text-white pb-3 transition-colors cursor-pointer'"
      >
        Personas
      </button>
    </div>

    <!-- CONTENIDO PESTAÑA 1: TABLÓN -->
    <div v-if="pestañaActiva === 'tablon'" class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
      <!-- Columna Izquierda: Próximas Entregas -->
      <div class="lg:col-span-1 bg-studia-card p-5 rounded-xl border border-gray-800 shadow-md">
        <h3 class="text-sm font-bold text-white mb-3">Próximas entregas</h3>
        <div v-if="tareasPendientes.length === 0" class="text-xs text-gray-500 py-2">
          ¡Yuju! ¡No tienes tareas pendientes para pronto!
        </div>
        <ul v-else class="space-y-3 text-xs">
          <li v-for="tarea in tareasPendientes.slice(0, 3)" :key="tarea.id" class="border-l-2 border-studia-purple pl-3 py-1">
            <p class="font-semibold text-gray-200 truncate">{{ tarea.titulo }}</p>
            <p class="text-gray-500">{{ formatearFecha(tarea.fecha_limite) }}</p>
          </li>
        </ul>
        <button @click="pestañaActiva = 'trabajos'" class="w-full mt-4 text-center text-xs font-semibold text-purple-400 hover:text-purple-300 transition-colors cursor-pointer block">
          Ver todo
        </button>
      </div>

      <!-- Columna Derecha: Muro de Publicaciones -->
      <div class="lg:col-span-3 space-y-4">
        <!-- Caja para publicar nueva tarea (Solo Docentes) -->
        <div v-if="esDocente" @click="abrirModalTarea" class="bg-studia-card p-4 rounded-xl border border-gray-800 hover:border-gray-700 transition-all flex items-center gap-4 cursor-pointer shadow-md group">
          <div class="w-10 h-10 rounded-full bg-studia-purple flex items-center justify-center font-bold text-white shrink-0">
            {{ authStore.user?.nombre?.charAt(0).toUpperCase() }}
          </div>
          <div class="flex-1 bg-gray-900/60 group-hover:bg-gray-900 border border-gray-800 rounded-full px-5 py-2.5 text-xs text-gray-400 transition-colors">
            Anuncia algo a tu clase o publica una nueva actividad...
          </div>
          <button class="bg-studia-purple text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-purple-700 transition-colors">
            + Tarea
          </button>
        </div>

        <!-- Lista de Tareas en el Tablón -->
        <div v-if="materia.tareas?.length === 0" class="bg-studia-card/50 p-12 rounded-xl border border-gray-800 text-center text-gray-500 text-xs">
          Aún no hay publicaciones ni tareas en este grupo.
        </div>

        <div 
          v-for="tarea in materia.tareas" 
          :key="tarea.id"
          @click="irATarea(tarea.id)"
          class="bg-studia-card p-5 rounded-xl border border-gray-800 hover:border-studia-purple/50 transition-all flex items-center justify-between cursor-pointer shadow-md group"
        >
          <div class="flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400 shrink-0 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-white group-hover:text-purple-300 transition-colors">
                {{ materia.docente?.nombre }} ha publicado una nueva tarea: <span class="text-purple-200">{{ tarea.titulo }}</span>
              </p>
              <p class="text-xs text-gray-500 mt-0.5">Publicada el {{ formatearFecha(tarea.created_at) }}</p>
            </div>
          </div>
          <div class="text-right shrink-0">
            <span class="text-xs font-medium text-gray-400 bg-gray-900 px-3 py-1 rounded-full border border-gray-800">
              Entrega: {{ formatearFecha(tarea.fecha_limite) }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENIDO PESTAÑA 2: TRABAJO DE CLASE -->
    <div v-if="pestañaActiva === 'trabajos'" class="space-y-4">
      <div v-if="esDocente" class="flex justify-end">
        <button @click="abrirModalTarea" class="bg-studia-purple hover:bg-purple-700 text-white font-bold py-2 px-5 rounded-lg text-xs shadow-lg flex items-center gap-2 transition-colors cursor-pointer">
          + Crear Tarea
        </button>
      </div>

      <div v-if="materia.tareas?.length === 0" class="bg-studia-card p-12 rounded-xl border border-gray-800 text-center text-gray-500 text-sm">
        No se han asignado trabajos para esta clase.
      </div>

      <div 
        v-for="tarea in materia.tareas" 
        :key="tarea.id"
        @click="irATarea(tarea.id)"
        class="bg-studia-card p-5 rounded-xl border border-gray-800 hover:border-purple-500/50 transition-all flex items-center justify-between cursor-pointer shadow-md"
      >
        <div class="flex items-center gap-4">
          <div class="w-10 h-10 rounded-lg bg-studia-purple/20 flex items-center justify-center text-studia-purple">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
          </div>
          <div>
            <h4 class="text-base font-bold text-white">{{ tarea.titulo }}</h4>
            <p class="text-xs text-gray-400 line-clamp-1 mt-1">{{ tarea.descripcion }}</p>
          </div>
        </div>
        <div class="text-right text-xs text-gray-400">
          <p class="font-bold text-purple-300">{{ tarea.puntaje_maximo }} pts</p>
          <p class="mt-1">Límite: {{ formatearFecha(tarea.fecha_limite) }}</p>
        </div>
      </div>
    </div>

    <!-- CONTENIDO PESTAÑA 3: PERSONAS -->
    <div v-if="pestañaActiva === 'personas'" class="space-y-8 max-w-4xl">
      <!-- Sección Profesores -->
      <div>
        <h3 class="text-xl font-bold text-studia-purple border-b border-studia-purple/30 pb-3 mb-4">Profesor</h3>
        <div class="flex items-center gap-4 bg-studia-card p-4 rounded-xl border border-gray-800">
          <div class="w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center font-bold text-white">
            {{ materia.docente?.nombre?.charAt(0).toUpperCase() }}
          </div>
          <div>
            <p class="text-sm font-bold text-white">{{ materia.docente?.nombre }}</p>
            <p class="text-xs text-gray-400">{{ materia.docente?.email }}</p>
          </div>
        </div>
      </div>

      <!-- Sección Compañeros / Alumnos -->
      <div>
        <div class="flex justify-between items-center border-b border-gray-800 pb-3 mb-4">
          <h3 class="text-xl font-bold text-white">Compañeros de clase</h3>
          <span class="text-xs text-purple-400 font-semibold">{{ materia.alumnos?.length || 0 }} alumnos</span>
        </div>
        
        <div v-if="materia.alumnos?.length === 0" class="text-xs text-gray-500 py-4">
          Aún no hay estudiantes inscritos en esta materia.
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div v-for="alumno in materia.alumnos" :key="alumno.id" class="flex items-center gap-3 bg-studia-card/60 p-3 rounded-lg border border-gray-800/80">
            <div class="w-8 h-8 rounded-full bg-blue-600/30 border border-blue-500/30 flex items-center justify-center text-xs font-bold text-blue-300">
              {{ alumno.nombre.charAt(0).toUpperCase() }}
            </div>
            <div class="truncate">
              <p class="text-xs font-semibold text-gray-200 truncate">{{ alumno.nombre }}</p>
              <p class="text-[10px] text-gray-500 truncate">{{ alumno.email }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: CREAR TAREA (Con Barra de Formato y Subida de PDF) -->
    <div v-if="modalTarea" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50">
      <div class="bg-studia-card border border-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold text-white">Crear Nueva Tarea</h3>
        <form @submit.prevent="guardarTarea" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1">Título de la actividad *</label>
            <input v-model="formTarea.titulo" type="text" required placeholder="Ej. Actividad 1: Mapa conceptual" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-studia-purple" />
          </div>

          <!-- BARRA DE FORMATO ESTILO CLASSROOM -->
          <div>
            <label class="block text-xs text-gray-400 mb-1">Instrucciones *</label>
            <div class="border border-gray-700 rounded-lg overflow-hidden bg-gray-900 focus-within:border-studia-purple transition-colors">
              <!-- Toolbar -->
              <div class="flex items-center gap-1 p-1.5 bg-gray-800/80 border-b border-gray-700 text-gray-300">
                <button type="button" @click="insertarFormato('bold')" title="Negrita" class="p-1.5 hover:bg-gray-700 hover:text-white rounded font-bold text-xs w-7 h-7 flex items-center justify-center cursor-pointer">B</button>
                <button type="button" @click="insertarFormato('italic')" title="Cursiva" class="p-1.5 hover:bg-gray-700 hover:text-white rounded italic text-xs w-7 h-7 flex items-center justify-center cursor-pointer">I</button>
                <button type="button" @click="insertarFormato('underline')" title="Subrayado" class="p-1.5 hover:bg-gray-700 hover:text-white rounded underline text-xs w-7 h-7 flex items-center justify-center cursor-pointer">U</button>
                <div class="h-4 w-[1px] bg-gray-700 mx-1"></div>
                <button type="button" @click="insertarFormato('ul')" title="Lista con viñetas" class="p-1.5 hover:bg-gray-700 hover:text-white rounded text-xs px-2 h-7 flex items-center justify-center gap-1 cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                  <span class="text-[10px]">Lista</span>
                </button>
                <button type="button" @click="insertarFormato('clear')" title="Limpiar formato" class="p-1.5 hover:bg-red-500/20 hover:text-red-300 ml-auto rounded text-xs w-7 h-7 flex items-center justify-center cursor-pointer">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
              <!-- Textarea de instrucciones -->
              <textarea 
                ref="textareaInstrucciones"
                v-model="formTarea.descripcion" 
                rows="4" 
                required 
                placeholder="Explica las instrucciones. Usa los botones de arriba para dar formato..." 
                class="w-full bg-transparent p-3 text-white text-sm focus:outline-none resize-y min-h-[100px]"
              ></textarea>
            </div>
          </div>

          <!-- INPUT DE ARCHIVO PDF -->
          <div>
            <label class="block text-xs text-gray-400 mb-1">Adjuntar archivo de apoyo (PDF)</label>
            <input 
              type="file" 
              accept=".pdf" 
              @change="alSeleccionarPdf" 
              class="w-full bg-gray-900 border border-gray-700 rounded-lg p-1.5 text-xs text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-studia-purple file:text-white hover:file:bg-purple-700 file:cursor-pointer"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-gray-400 mb-1">Fecha límite *</label>
              <input v-model="formTarea.fecha_limite" type="datetime-local" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-studia-purple" />
            </div>
            <div>
              <label class="block text-xs text-gray-400 mb-1">Puntaje Máximo</label>
              <input v-model="formTarea.puntaje_maximo" type="number" min="1" max="1000" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-studia-purple" />
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-3">
            <button type="button" @click="modalTarea = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-white transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" :disabled="guardando" class="bg-studia-purple hover:bg-purple-700 text-white text-xs font-bold px-6 py-2 rounded-lg transition-colors cursor-pointer disabled:opacity-50">
              {{ guardando ? 'Publicando...' : 'Publicar Tarea' }}
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
import { useAuthStore } from '../../stores/auth'
import api from '../../api/axios'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const esDocente = computed(() => authStore.user?.rol === 'docente')
const materia = ref(null)
const cargando = ref(true)
const pestañaActiva = ref('tablon')

// Estados para crear tarea
const modalTarea = ref(false)
const guardando = ref(false)
const formTarea = ref({
  titulo: '',
  descripcion: '',
  fecha_limite: '',
  puntaje_maximo: 100
})

// Tareas pendientes ordenadas para el recuadro izquierdo
const tareasPendientes = computed(() => {
  if (!materia.value?.tareas) return []
  return materia.value.tareas.slice().reverse()
})

const cargarMateria = async () => {
  cargando.value = true
  try {
    const response = await api.get(`/materias/${route.params.id}`)
    materia.value = response.data
  } catch (error) {
    console.error('Error cargando materia:', error)
  } finally {
    cargando.value = false
  }
}

const archivoPdfSeleccionado = ref(null)
const textareaInstrucciones = ref(null)

const alSeleccionarPdf = (event) => {
  archivoPdfSeleccionado.value = event.target.files[0] || null
}

// Lógica de la barra de formato
const insertarFormato = (tipo) => {
  const textarea = textareaInstrucciones.value
  if (!textarea) return

  const inicio = textarea.selectionStart
  const fin = textarea.selectionEnd
  const textoSeleccionado = formTarea.value.descripcion.substring(inicio, fin)
  let reemplazo = ''

  if (tipo === 'bold') reemplazo = `<b>${textoSeleccionado || 'texto en negrita'}</b>`
  if (tipo === 'italic') reemplazo = `<i>${textoSeleccionado || 'texto en cursiva'}</i>`
  if (tipo === 'underline') reemplazo = `<u>${textoSeleccionado || 'texto subrayado'}</u>`
  if (tipo === 'ul') {
    reemplazo = `<ul>\n  <li>${textoSeleccionado || 'Elemento de lista 1'}</li>\n  <li>Elemento de lista 2</li>\n</ul>`
  }
  if (tipo === 'clear') {
    reemplazo = textoSeleccionado.replace(/<[^>]*>?/gm, '')
  }

  formTarea.value.descripcion = 
    formTarea.value.descripcion.substring(0, inicio) + 
    reemplazo + 
    formTarea.value.descripcion.substring(fin)
}

// Reemplaza la función guardarTarea existente por esta que usa FormData:
const guardarTarea = async () => {
  guardando.value = true
  try {
    const formData = new FormData()
    formData.append('materia_id', materia.value.id)
    formData.append('titulo', formTarea.value.titulo)
    formData.append('descripcion', formTarea.value.descripcion)
    formData.append('fecha_limite', formTarea.value.fecha_limite)
    formData.append('puntaje_maximo', formTarea.value.puntaje_maximo)
    
    if (archivoPdfSeleccionado.value) {
      formData.append('archivo_pdf', archivoPdfSeleccionado.value)
    }

    await api.post('/tareas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    modalTarea.value = false
    formTarea.value = { titulo: '', descripcion: '', fecha_limite: '', puntaje_maximo: 100 }
    archivoPdfSeleccionado.value = null
    await cargarMateria()
  } catch (error) {
    alert(error.response?.data?.message || 'Error al publicar la tarea.')
  } finally {
    guardando.value = false
  }
}

const abrirModalTarea = () => { modalTarea.value = true }

const copiarCodigo = () => {
  navigator.clipboard.writeText(materia.value.codigo_acceso)
  alert(`Código ${materia.value.codigo_acceso} copiado al portapapeles.`)
}

const irATarea = (id) => {
  router.push(`/dashboard/tareas/${id}`)
}

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return 'Sin fecha'
  const opciones = { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }
  return new Date(fechaStr).toLocaleDateString('es-ES', opciones)
}

onMounted(() => {
  cargarMateria()
})
</script>