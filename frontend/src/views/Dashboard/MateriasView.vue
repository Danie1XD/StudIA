<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Mis Materias</h2>
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <div v-if="authStore.user?.rol === 'docente'" class="bg-studia-card p-6 rounded-xl border border-gray-800">
      <h3 class="text-lg font-bold text-white mb-4">Crear nueva materia</h3>
      <form class="grid md:grid-cols-3 gap-4" @submit.prevent="crearMateria">
        <input v-model="nuevaMateria.nombre_materia" required placeholder="Nombre de la materia" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <input v-model="nuevaMateria.codigo_grupo" placeholder="Código (opcional)" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <textarea v-model="nuevaMateria.descripcion" rows="1" placeholder="Descripción" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white md:col-span-2"></textarea>
        <button class="bg-studia-purple text-white rounded-lg px-4 py-2 text-sm font-semibold" :disabled="cargando">Crear materia</button>
      </form>
      <p v-if="mensaje" class="mt-3 text-sm text-green-400">{{ mensaje }}</p>
      <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>
    </div>

    <div v-else class="bg-studia-card p-6 rounded-xl border border-gray-800">
      <h3 class="text-lg font-bold text-white mb-4">Unirse a una materia</h3>
      <form class="flex flex-col md:flex-row gap-3" @submit.prevent="unirseMateria">
        <input v-model="codigoGrupo" required placeholder="Código del profesor" class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <button class="bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-semibold" :disabled="cargando">Unirme</button>
      </form>
      <p v-if="mensaje" class="mt-3 text-sm text-green-400">{{ mensaje }}</p>
      <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="materia in materias" :key="materia._id || materia.id" class="bg-studia-card p-6 rounded-xl border border-gray-800">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-lg font-bold text-white mb-1">{{ materia.nombre_materia }}</h3>
            <p class="text-xs text-gray-400">{{ materia.descripcion || 'Sin descripción' }}</p>
          </div>
          <div class="text-xs text-studia-purple font-semibold">{{ materia.codigo_grupo }}</div>
        </div>
        <div class="mt-4 text-sm text-gray-400">
          <p>Docente: {{ materia.docente_id }}</p>
          <p>Alumnos: {{ (materia.alumnos_inscritos || []).length }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '@/api/axios'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const materias = ref([])
const cargando = ref(false)
const mensaje = ref('')
const error = ref('')
const codigoGrupo = ref('')
const nuevaMateria = ref({ nombre_materia: '', descripcion: '', codigo_grupo: '' })

const cargarMaterias = async () => {
  try {
    const response = await api.get('/asignaturas')
    materias.value = response.data
  } catch (err) {
    console.error(err)
  }
}

const crearMateria = async () => {
  cargando.value = true
  mensaje.value = ''
  error.value = ''
  try {
    await api.post('/asignaturas', nuevaMateria.value)
    nuevaMateria.value = { nombre_materia: '', descripcion: '', codigo_grupo: '' }
    mensaje.value = 'Materia creada correctamente.'
    await cargarMaterias()
  } catch (err) {
    error.value = err.response?.data?.message || 'No se pudo crear la materia.'
  } finally {
    cargando.value = false
  }
}

const unirseMateria = async () => {
  cargando.value = true
  mensaje.value = ''
  error.value = ''
  try {
    await api.post('/asignaturas/unirse', { codigo_grupo: codigoGrupo.value })
    codigoGrupo.value = ''
    mensaje.value = 'Te has unido a la materia.'
    await cargarMaterias()
  } catch (err) {
    error.value = err.response?.data?.error || 'No se pudo unir a la materia.'
  } finally {
    cargando.value = false
  }
}

onMounted(() => {
  cargarMaterias()
})
</script>