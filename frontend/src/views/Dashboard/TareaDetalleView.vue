<template>
  <div v-if="cargando" class="text-center py-16 text-gray-400 text-sm">
    Cargando información de la tarea...
  </div>

  <div v-else-if="!tarea" class="text-center py-16 text-red-400 text-sm">
    No se encontró la tarea o fue eliminada.
  </div>

  <div v-else class="space-y-6 max-w-6xl mx-auto">
    <!-- Botón Regresar -->
    <button @click="$router.back()" class="text-xs text-gray-400 hover:text-white flex items-center gap-1.5 transition-colors cursor-pointer">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
      Volver al listado
    </button>

    <!-- CUERPO PRINCIPAL EN 2 COLUMNAS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
      
      <!-- COLUMNA IZQUIERDA (2 Tercios): Instrucciones y Material del Profe -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-studia-card p-8 rounded-2xl border border-gray-800 shadow-xl space-y-6">
          
          <div class="border-b border-gray-800 pb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-studia-purple/20 border border-studia-purple/40 flex items-center justify-center text-studia-purple shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
              </div>
              <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white">{{ tarea.titulo }}</h1>
                <p class="text-xs text-purple-300 font-semibold">{{ tarea.materia?.nombre }}</p>
              </div>
            </div>
            <div class="flex flex-wrap justify-between items-center text-xs text-gray-400 mt-4 pt-4 border-t border-gray-800/60 gap-2">
              <span>Por: <strong class="text-gray-200">{{ tarea.materia?.docente?.nombre }}</strong></span>
              <div class="flex gap-4">
                <span class="font-bold text-white">{{ tarea.puntaje_maximo }} puntos</span>
                <span>Límite: <strong class="text-purple-300">{{ formatearFecha(tarea.fecha_limite) }}</strong></span>
              </div>
            </div>
          </div>

          <!-- Instrucciones -->
          <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Instrucciones</h3>
            <div class="prose prose-invert max-w-none text-sm text-gray-300 leading-relaxed space-y-2 bg-gray-900/50 p-5 rounded-xl border border-gray-800/80" v-html="tarea.descripcion"></div>
          </div>

          <!-- ARCHIVO ADJUNTO DEL PROFESOR -->
          <div v-if="tarea.archivo_pdf">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Material de apoyo</h3>
            <a 
              :href="obtenerUrlStorage(tarea.archivo_pdf)" 
              target="_blank"
              class="flex items-center gap-4 bg-gray-900 hover:bg-gray-800 border border-gray-700 hover:border-studia-purple p-4 rounded-xl transition-all group cursor-pointer"
            >
              <div class="w-10 h-10 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-400 font-bold text-xs shrink-0 group-hover:scale-110 transition-transform">
                PDF
              </div>
              <div class="flex-1 truncate">
                <p class="text-sm font-bold text-white group-hover:text-purple-300 transition-colors truncate">Documento de la actividad</p>
                <p class="text-xs text-gray-500">Haz clic para abrir el PDF en una nueva pestaña</p>
              </div>
              <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            </a>
          </div>

        </div>
      </div>

      <!-- COLUMNA DERECHA (1 Tercio): Paneles de Classroom -->
      <div class="lg:col-span-1 space-y-6">
        
        <!-- PANEL ALUMNO: TU TRABAJO -->
        <div v-if="!esDocente" class="space-y-4">
          
          <!-- Caja principal: Tu trabajo -->
          <div class="bg-studia-card p-6 rounded-2xl border border-gray-800 shadow-xl space-y-4">
            <div class="flex justify-between items-center">
              <h3 class="text-base font-bold text-white">Tu trabajo</h3>
              <span :class="miEntrega?.estado === 'calificado' ? 'text-blue-400 font-bold' : (miEntrega ? 'text-green-400 font-bold' : 'text-purple-300 font-semibold')" class="text-xs uppercase tracking-wider">
                {{ miEntrega?.estado === 'calificado' ? 'Calificado' : (miEntrega ? 'Entregado' : 'Asignado') }}
              </span>
            </div>

            <!-- CALIFICACIÓN FINAL (Visible cuando el docente califica) -->
            <div v-if="miEntrega?.calificacion_final !== null && miEntrega?.calificacion_final !== undefined" class="bg-gray-900 p-4 rounded-xl border border-blue-500/40 text-center space-y-1">
              <span class="text-[11px] text-gray-400 block uppercase tracking-wider font-semibold">Calificación Obtenida</span>
              <div class="text-3xl font-extrabold text-blue-400">
                {{ miEntrega.calificacion_final }} <span class="text-sm text-gray-500">/ {{ tarea.puntaje_maximo }}</span>
              </div>
            </div>

            <!-- RETROALIMENTACIÓN DEL DOCENTE -->
            <div v-if="miEntrega?.retroalimentacion_final" class="bg-gray-900 p-4 rounded-xl border border-gray-800 space-y-2 text-xs">
              <span class="font-bold text-purple-400 block uppercase tracking-wider">Comentarios del docente:</span>
              <p class="text-gray-300 whitespace-pre-line leading-relaxed">{{ miEntrega.retroalimentacion_final }}</p>
            </div>

            <!-- Lista de elementos adjuntos (Archivo o Enlace) -->
            <div class="space-y-2">
              <a v-if="miEntrega?.archivo_url" :href="obtenerUrlStorage(miEntrega.archivo_url)" target="_blank" class="flex items-center gap-3 bg-gray-900 p-3 rounded-xl border border-gray-700 hover:border-purple-400 text-xs transition-colors group">
                <svg class="w-5 h-5 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                <span class="truncate flex-1 font-semibold text-gray-200 group-hover:text-white">Archivo entregado</span>
              </a>

              <div v-if="miEntrega?.contenido" class="bg-gray-900 p-3 rounded-xl border border-gray-700 text-xs text-gray-300 space-y-1">
                <span class="text-[10px] uppercase font-bold text-purple-400 block">Enlace o nota:</span>
                <p class="break-all">{{ miEntrega.contenido }}</p>
              </div>

              <!-- Archivo en cola para subir -->
              <div v-if="archivoSeleccionado" class="flex items-center justify-between bg-purple-900/20 border border-purple-500/50 p-3 rounded-xl text-xs">
                <div class="flex items-center gap-2 truncate">
                  <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  <span class="truncate font-semibold text-purple-200">{{ archivoSeleccionado.name }}</span>
                </div>
                <button @click="archivoSeleccionado = null" class="text-gray-400 hover:text-red-400 font-bold ml-2 cursor-pointer">✕</button>
              </div>

              <!-- Enlace en cola para subir -->
              <div v-if="enlaceSeleccionado" class="flex items-center justify-between bg-blue-900/20 border border-blue-500/50 p-3 rounded-xl text-xs">
                <div class="flex items-center gap-2 truncate">
                  <svg class="w-4 h-4 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" /></svg>
                  <span class="truncate font-semibold text-blue-200">{{ enlaceSeleccionado }}</span>
                </div>
                <button @click="enlaceSeleccionado = ''" class="text-gray-400 hover:text-red-400 font-bold ml-2 cursor-pointer">✕</button>
              </div>
            </div>

            <!-- Botón Desplegable: + Añadir o crear (Solo si no ha entregado) -->
            <div v-if="!miEntrega" class="relative">
              <button @click="menuAnadir = !menuAnadir" class="w-full bg-studia-purple/10 hover:bg-studia-purple/20 border border-studia-purple/40 text-studia-purple hover:text-purple-300 font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Añadir o crear
              </button>

              <div v-if="menuAnadir" class="absolute left-0 right-0 mt-2 bg-studia-card border border-gray-700 rounded-xl shadow-2xl py-2 z-20 space-y-1">
                <button @click="abrirModalEnlace" class="w-full px-4 py-2 text-left text-xs text-gray-300 hover:bg-white/5 hover:text-white flex items-center gap-3 transition-colors cursor-pointer">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" /></svg>
                  Enlace
                </button>
                <button @click="abrirModalArchivo" class="w-full px-4 py-2 text-left text-xs text-gray-300 hover:bg-white/5 hover:text-white flex items-center gap-3 transition-colors cursor-pointer">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                  Archivo
                </button>
              </div>
            </div>

            <!-- Botón Principal de Entrega -->
            <button 
              v-if="!miEntrega"
              @click="enviarTrabajo" 
              :disabled="enviando || (!archivoSeleccionado && !enlaceSeleccionado)" 
              class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs shadow-lg transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
            >
              {{ enviando ? 'Subiendo archivos...' : (archivoSeleccionado || enlaceSeleccionado ? 'Entregar' : 'Marcar como completado') }}
            </button>

            <!-- Anular entrega (Solo si no ha sido calificado) -->
            <button 
              v-else-if="miEntrega?.estado !== 'calificado'" 
              @click="anularEntrega" 
              class="w-full bg-gray-800 hover:bg-red-500/20 hover:text-red-300 border border-gray-700 text-gray-300 font-semibold py-2.5 rounded-xl text-xs transition-all cursor-pointer"
            >
              Anular entrega
            </button>
          </div>

        </div>

        <!-- PANEL DOCENTE: ENTREGAS RECIBIDAS -->
        <div v-if="esDocente" class="bg-studia-card p-6 rounded-2xl border border-gray-800 shadow-xl space-y-4">
          <div class="flex justify-between items-center border-b border-gray-800 pb-3">
            <h3 class="text-base font-bold text-white">Entregas Recibidas</h3>
            <span class="text-xs font-bold text-purple-300 bg-purple-900/40 px-2.5 py-1 rounded border border-purple-500/20">
              {{ tarea.entregas?.length || 0 }} envíos
            </span>
          </div>

          <div v-if="tarea.entregas?.length === 0" class="text-center py-6 text-xs text-gray-500">
            Aún ningún estudiante ha mandado su trabajo.
          </div>

          <ul v-else class="space-y-3">
            <li v-for="entrega in tarea.entregas" :key="entrega.id" class="bg-gray-900/80 p-3.5 rounded-xl border border-gray-800 flex justify-between items-center gap-3">
              <div class="truncate">
                <p class="text-xs font-bold text-white truncate">{{ entrega.alumno?.nombre }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                  <a v-if="entrega.archivo_url" :href="obtenerUrlStorage(entrega.archivo_url)" target="_blank" class="text-[10px] text-purple-400 hover:underline flex items-center gap-1">
                    📄 Ver Archivo
                  </a>
                  <span v-if="entrega.contenido" :title="entrega.contenido" class="text-[10px] text-gray-400 truncate max-w-[120px]">
                    💬 {{ entrega.contenido }}
                  </span>
                </div>
              </div>
              <button 
                @click="$router.push(`/dashboard/evaluacion-ia/${entrega.id}`)"
                class="bg-purple-600/30 hover:bg-studia-purple text-purple-300 hover:text-white border border-purple-500/30 px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all cursor-pointer shrink-0"
              >
                Revisar con IA
              </button>
            </li>
          </ul>
        </div>

        <!-- APARTADO: COMENTARIOS PRIVADOS (Ahora visible para Alumnos y Docentes siempre) -->
        <div class="bg-studia-card p-6 rounded-2xl border border-gray-800 shadow-xl space-y-4">
          <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            <h3 class="text-base font-bold text-white">Foro de Clase</h3>
          </div>

          <!-- Listado de comentarios privados -->
          <div class="space-y-2 max-h-40 overflow-y-auto">
            <div v-for="comentario in tarea.comentarios" :key="comentario.id" class="bg-gray-900 p-3 rounded-xl border border-gray-800 text-xs space-y-1">
              <div class="flex justify-between text-[10px] text-gray-500 font-semibold">
                <span class="text-purple-300">{{ comentario.user?.nombre || 'Usuario' }}</span>
              </div>
              <p class="text-gray-300 leading-relaxed break-all">{{ comentario.mensaje }}</p>
            </div>
            <p v-if="!tarea.comentarios || tarea.comentarios.length === 0" class="text-xs text-gray-500 text-center py-2">
              No hay comentarios aún.
            </p>
          </div>

          <!-- Input para enviar un nuevo comentario privado -->
          <div class="relative flex items-center">
            <input 
              v-model="comentarioPrivado" 
              type="text" 
              placeholder="Escribe una duda o comentario para la clase..." 
              @keyup.enter="enviarComentarioPrivado"
              class="w-full bg-gray-900 border border-gray-700 rounded-xl pl-3 pr-10 py-2.5 text-xs text-white focus:outline-none focus:border-purple-500 transition-colors"
            />
            <button 
              @click="enviarComentarioPrivado"
              class="absolute right-2 text-purple-400 hover:text-purple-300 p-1.5 cursor-pointer transition-colors"
              title="Enviar comentario"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- MODAL 1: AÑADIR ENLACE -->
    <div v-if="modalEnlace" class="fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50">
      <div class="bg-studia-card border border-gray-800 rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <h3 class="text-base font-bold text-white">Añadir enlace</h3>
        <input v-model="inputEnlaceTemp" type="url" placeholder="https://drive.google.com/..." class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500" />
        <div class="flex justify-end gap-3 pt-2">
          <button @click="modalEnlace = false" class="px-3 py-1.5 text-xs text-gray-400 hover:text-white transition-colors cursor-pointer font-bold">Cancelar</button>
          <button @click="confirmarEnlace" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition-colors cursor-pointer">Añadir enlace</button>
        </div>
      </div>
    </div>

    <!-- MODAL 2: SUBIR ARCHIVO CON DRAG & DROP -->
    <div v-if="modalArchivo" class="fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
      <div class="bg-studia-card border border-gray-800 rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl">
        <!-- Cabecera del modal -->
        <div class="flex justify-between items-center p-5 border-b border-gray-800">
          <h3 class="text-base font-bold text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-studia-purple"></span>
            Insertar archivos con StudIA
          </h3>
          <button @click="modalArchivo = false" class="text-gray-400 hover:text-white font-bold text-sm cursor-pointer">✕</button>
        </div>

        <!-- ZONA DE DRAG & DROP (Arrastrar y soltar) -->
        <div class="p-8">
          <div 
            @dragover.prevent="zonaDragActiva = true"
            @dragleave.prevent="zonaDragActiva = false"
            @drop.prevent="alSoltarArchivo"
            :class="zonaDragActiva ? 'border-studia-purple bg-studia-purple/10 scale-98' : 'border-gray-700 bg-gray-900/60 hover:border-gray-600'"
            class="border-2 border-dashed rounded-2xl p-10 text-center transition-all flex flex-col items-center justify-center min-h-[220px]"
          >
            <!-- Ícono de Nube animada -->
            <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-studia-purple/20 to-blue-500/20 border border-purple-500/30 flex items-center justify-center text-purple-400 mb-4 shadow-inner">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
            </div>

            <!-- Botón Examinar oculto que se activa con el botón visible -->
            <input ref="inputArchivoOculto" type="file" @change="alSeleccionarDesdeExplorador" class="hidden" />

            <button type="button" @click="$refs.inputArchivoOculto.click()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-full text-xs font-bold shadow-lg transition-all cursor-pointer mb-3">
              Examinar
            </button>

            <p class="text-xs text-gray-400">
              o arrastra archivos para subirlos aquí
            </p>
          </div>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import api from '../../api/axios'

const route = useRoute()
const authStore = useAuthStore()

const esDocente = computed(() => authStore.user?.rol === 'docente')
const tarea = ref(null)
const cargando = ref(true)

// Estados de la interfaz Classroom Alumno
const menuAnadir = ref(false)
const modalEnlace = ref(false)
const modalArchivo = ref(false)
const zonaDragActiva = ref(false)

// Cola temporal para entregar
const archivoSeleccionado = ref(null)
const enlaceSeleccionado = ref('')
const comentarioPrivado = ref('')
const inputEnlaceTemp = ref('')
const enviando = ref(false)

// Variables locales para asegurar datos inmediatos
const usuarioActual = ref(null)
const miEntrega = ref(null)

// Función para buscar y asignar la entrega comparando con el usuario real
const verificarEntregaAlumno = () => {
  if (!tarea.value?.entregas || esDocente.value) {
    miEntrega.value = null
    return
  }

  const user = usuarioActual.value || authStore.user
  console.log("Usuario verificado para la entrega:", user)
  console.log("Lista de entregas recibidas:", tarea.value.entregas)

  if (!user) {
    miEntrega.value = null
    return
  }

  // Buscamos coincidencia exacta por ID o por correo electrónico
  const encontrada = tarea.value.entregas.find(e => {
    const idEntrega = Number(e.alumno_id || e.user_id)
    const idUser = Number(user.id)
    
    const coincideId = idEntrega === idUser || Number(e.alumno?.id) === idUser
    const coincideEmail = e.alumno?.email && user.email && 
                          e.alumno.email.toLowerCase() === user.email.toLowerCase()

    return coincideId || coincideEmail
  })

  miEntrega.value = encontrada || null
  console.log("Entrega detectada para este usuario:", miEntrega.value)
}

// === SOLUCIÓN AL PDF EN CODESPACES ===
const obtenerUrlStorage = (ruta) => {
  if (!ruta) return '#'
  const base = api.defaults.baseURL ? api.defaults.baseURL.replace(/\/api$/, '') : 'http://localhost:8000'
  return `${base}/storage/${ruta}`
}

// Carga combinada: Obtiene el usuario autenticado y la tarea de forma síncrona/secuencial
const cargarDatosIniciales = async () => {
  cargando.value = true
  try {
    // 1. Obtenemos el usuario directamente del backend para evitar que llegue nulo
    try {
      const resUser = await api.get('/user')
      usuarioActual.value = resUser.data
    } catch (e) {
      usuarioActual.value = authStore.user
    }

    // 2. Obtenemos los datos de la tarea y sus entregas
    const response = await api.get(`/tareas/${route.params.id}`)
    tarea.value = response.data
    
    // 3. Verificamos la entrega con los datos ya seguros
    verificarEntregaAlumno()
  } catch (error) {
    console.error('Error cargando los datos:', error)
  } finally {
    cargando.value = false
  }
}

// Funciones del menú Añadir o crear
const abrirModalEnlace = () => {
  menuAnadir.value = false
  inputEnlaceTemp.value = enlaceSeleccionado.value
  modalEnlace.value = true
}

const abrirModalArchivo = () => {
  menuAnadir.value = false
  modalArchivo.value = true
}

const confirmarEnlace = () => {
  if (inputEnlaceTemp.value.trim()) {
    enlaceSeleccionado.value = inputEnlaceTemp.value.trim()
  }
  modalEnlace.value = false
}

// Lógica de Arrastrar y Soltar (Drag & Drop)
const alSoltarArchivo = (e) => {
  zonaDragActiva.value = false
  const archivos = e.dataTransfer.files
  if (archivos.length > 0) {
    archivoSeleccionado.value = archivos[0]
    modalArchivo.value = false
  }
}

const alSeleccionarDesdeExplorador = (e) => {
  if (e.target.files.length > 0) {
    archivoSeleccionado.value = e.target.files[0]
    modalArchivo.value = false
  }
}

// Enviar el trabajo del alumno a Laravel
const enviarTrabajo = async () => {
  if (!archivoSeleccionado.value && !enlaceSeleccionado.value && !comentarioPrivado.value) {
    alert('Por favor adjunta un archivo, un enlace o escribe un comentario antes de entregar.')
    return
  }

  enviando.value = true
  try {
    const formData = new FormData()
    formData.append('tarea_id', tarea.value.id)
    
    if (archivoSeleccionado.value) {
      formData.append('archivo', archivoSeleccionado.value)
    }

    const textoAEnviar = [enlaceSeleccionado.value, comentarioPrivado.value].filter(Boolean).join(' | ')
    if (textoAEnviar) {
      formData.append('contenido', textoAEnviar)
    }

    await api.post('/entregas', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    alert('¡Excelente! Trabajo entregado correctamente.')
    archivoSeleccionado.value = null
    enlaceSeleccionado.value = ''
    comentarioPrivado.value = ''
    await cargarDatosIniciales() 
  } catch (error) {
    alert(error.response?.data?.message || 'Error al procesar la entrega.')
  } finally {
    enviando.value = false
  }
}

// Anular entrega conectado al backend
const anularEntrega = async () => {
  if (!confirm('¿Deseas anular tu entrega para modificar tus archivos?')) return
  
  if (!miEntrega.value) {
    alert('No se encontró una entrega activa para anular.')
    return
  }

  try {
    await api.delete(`/entregas/${miEntrega.value.id}`)
    alert('Entrega anulada correctamente.')
    await cargarDatosIniciales() 
  } catch (error) {
    alert(error.response?.data?.message || 'Error al anular la entrega.')
  }
}

// Enviar comentario privado de manera independiente
const enviarComentarioPrivado = async () => {
  if (!comentarioPrivado.value.trim()) return

  try {
    await api.post('/comentarios', {
      tarea_id: tarea.value.id,
      mensaje: comentarioPrivado.value.trim()
    })
    comentarioPrivado.value = ''
    await cargarDatosIniciales() // Recarga la tarea y sus comentarios limpios
  } catch (error) {
    alert(error.response?.data?.message || 'Error al enviar el comentario.')
  }
}

const formatearFecha = (fechaStr) => {
  if (!fechaStr) return 'Sin fecha'
  return new Date(fechaStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => { 
  cargarDatosIniciales() 
})
</script>