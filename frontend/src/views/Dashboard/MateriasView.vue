<template>
  <div class="space-y-6">
    <!-- Encabezado y Acción Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-800 pb-6">
      <div>
        <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Mis Materias</h2>
        <p class="text-sm text-gray-400 mt-1">
          {{ esDocente ? 'Gestiona tus grupos y comparte el código con tus estudiantes.' : 'Clases a las que te encuentras inscrito actualmente.' }}
        </p>
      </div>

      <!-- Botón de acción condicionado por el rol en Pinia -->
      <div>
        <button 
          v-if="esDocente" 
          @click="abrirModalCrear"
          class="bg-studia-purple hover:bg-purple-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-lg flex items-center gap-2 transition-all cursor-pointer text-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
          Nueva Materia
        </button>

        <button 
          v-else 
          @click="abrirModalUnirse"
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-lg flex items-center gap-2 transition-all cursor-pointer text-sm"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
          Unirse a Clase
        </button>
      </div>
    </div>

    <!-- Mensajes de Estado (Carga y Errores) -->
    <div v-if="cargando" class="text-center py-12 text-gray-400 text-sm">
      Cargando materias...
    </div>

    <div v-else-if="errorMsg" class="bg-red-500/10 border border-red-500/50 text-red-300 p-4 rounded-xl text-sm">
      {{ errorMsg }}
    </div>

    <!-- Estado Vacío -->
    <div v-else-if="materias.length === 0" class="text-center py-16 bg-studia-card/50 rounded-xl border border-gray-800/80">
      <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
      <p class="text-gray-400 font-semibold text-base mb-1">Aún no hay materias registradas</p>
      <p class="text-gray-500 text-xs max-w-sm mx-auto">
        {{ esDocente ? 'Comienza creando tu primera clase para generar un código de acceso.' : 'Pide el código de acceso a tu profesor para matricularte en su clase.' }}
      </p>
    </div>

    <!-- Cuadrícula de Tarjetas -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div 
        v-for="materia in materias" 
        :key="materia.id"
        @click="verMateria(materia.id)"
        class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:border-studia-purple/50 transition-all flex flex-col justify-between group cursor-pointer relative overflow-hidden shadow-lg"
      >
        <!-- Acento superior de color -->
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-studia-purple to-purple-400 opacity-0 group-hover:opacity-100 transition-opacity"></div>

        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-xl font-bold text-white group-hover:text-studia-purple transition-colors mb-1">{{ materia.nombre }}</h3>
            <p class="text-xs text-gray-400 line-clamp-2">{{ materia.descripcion || 'Sin descripción adicional' }}</p>
          </div>
          
          <div class="w-10 h-10 rounded-lg bg-studia-purple/10 border border-studia-purple/30 flex items-center justify-center text-studia-purple shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
          </div>
        </div>

        <div class="pt-4 border-t border-gray-800/80 flex items-center justify-between text-xs">
          <!-- Si es alumno muestra el nombre de su profe, si es profe muestra el conteo de alumnos -->
          <div class="text-gray-400">
            <span v-if="esDocente" class="text-purple-300 font-semibold">
              👥 {{ materia.alumnos_count || 0 }} {{ (materia.alumnos_count === 1) ? 'alumno' : 'alumnos' }}
            </span>
            <span v-else class="text-gray-300">
              🎓 Prof. {{ materia.docente?.nombre || 'Docente' }}
            </span>
          </div>

          <!-- Etiqueta de Código de Acceso (Solo visible para el docente que la creó) -->
          <div v-if="esDocente && materia.codigo_acceso" @click.stop="copiarCodigo(materia.codigo_acceso)" title="Clic para copiar código" class="bg-gray-900 border border-gray-700 px-2.5 py-1 rounded-md font-mono text-purple-300 flex items-center gap-1.5 hover:border-purple-400 transition-colors">
            <span>🔑 {{ materia.codigo_acceso }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Crear Materia (Exclusivo Docentes) -->
    <div v-if="modalCrear" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-studia-card border border-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-white mb-4">Crear Nueva Materia</h3>
        <form @submit.prevent="crearMateria" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1">Nombre de la asignatura *</label>
            <input v-model="formCrear.nombre" type="text" required placeholder="Ej. Programación Avanzada" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-studia-purple" />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">Descripción o encuadre</label>
            <textarea v-model="formCrear.descripcion" rows="3" placeholder="Breve descripción del curso..." class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-studia-purple"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="modalCrear = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-white transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" :disabled="procesando" class="bg-studia-purple hover:bg-purple-700 text-white text-xs font-bold px-5 py-2 rounded-lg transition-colors cursor-pointer disabled:opacity-50">
              {{ procesando ? 'Creando...' : 'Guardar y Generar Código' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL: Unirse a Materia (Exclusivo Alumnos) -->
    <div v-if="modalUnirse" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-studia-card border border-gray-800 rounded-xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-lg font-bold text-white mb-1">Unirse a una Clase</h3>
        <p class="text-xs text-gray-400 mb-4">Introduce el código alfanumérico de 6 dígitos que te proporcionó tu profesor.</p>
        <form @submit.prevent="unirseAMateria" class="space-y-4">
          <div>
            <input v-model="codigoInput" type="text" required maxlength="8" placeholder="Ej. XY78AB" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-3 py-2.5 text-white font-mono text-center text-lg tracking-widest uppercase focus:outline-none focus:border-blue-500" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="modalUnirse = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-white transition-colors cursor-pointer">Cancelar</button>
            <button type="submit" :disabled="procesando" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2 rounded-lg transition-colors cursor-pointer disabled:opacity-50">
              {{ procesando ? 'Inscribiendo...' : 'Unirse Ahora' }}
            </button>
          </div>
        </form>
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

// Validamos el rol reactivamente
const esDocente = computed(() => authStore.user?.rol === 'docente')

// Estados de interfaz
const materias = ref([])
const cargando = ref(true)
const procesando = ref(false)
const errorMsg = ref('')

// Estados de modales y formularios
const modalCrear = ref(false)
const modalUnirse = ref(false)
const formCrear = ref({ nombre: '', descripcion: '' })
const codigoInput = ref('')

// 1. Obtener la lista de materias desde la API
const cargarMaterias = async () => {
  cargando.value = true
  errorMsg.value = ''
  try {
    const response = await api.get('/materias')
    materias.value = response.data
  } catch (error) {
    console.error(error)
    errorMsg.value = 'No se pudieron cargar las materias. Inténtalo de nuevo.'
  } finally {
    cargando.value = false
  }
}

// 2. Acción Docente: Crear materia en PostgreSQL
const crearMateria = async () => {
  if (!formCrear.value.nombre.trim()) return
  procesando.value = true
  try {
    await api.post('/materias', formCrear.value)
    modalCrear.value = false
    formCrear.value = { nombre: '', descripcion: '' }
    await cargarMaterias() // Recargar lista al instante
  } catch (error) {
    alert(error.response?.data?.message || 'Error al crear la materia.')
  } finally {
    procesando.value = false
  }
}

// 3. Acción Alumno: Matricularse usando código
const unirseAMateria = async () => {
  if (!codigoInput.value.trim()) return
  procesando.value = true
  try {
    await api.post('/materias/unirse', { codigo_acceso: codigoInput.value })
    modalUnirse.value = false
    codigoInput.value = ''
    await cargarMaterias() // Recargar lista con la nueva materia
  } catch (error) {
    alert(error.response?.data?.message || 'No se pudo unir a la materia. Verifica el código.')
  } finally {
    procesando.value = false
  }
}

// Auxiliares de interfaz
const abrirModalCrear = () => { modalCrear.value = true }
const abrirModalUnirse = () => { modalUnirse.value = true }

const copiarCodigo = (codigo) => {
  navigator.clipboard.writeText(codigo)
  alert(`Código ${codigo} copiado al portapapeles. ¡Compártelo con tus alumnos!`)
}

const verMateria = (id) => {
  // Aquí los redirigiremos más adelante para ver tareas y entregas
  router.push(`/dashboard/materias/${id}`)
}

onMounted(() => {
  cargarMaterias()
})
</script>