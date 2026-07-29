<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Tareas</h2>
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <div v-if="authStore.user?.rol === 'docente'" class="bg-studia-card p-6 rounded-xl border border-gray-800">
      <h3 class="text-lg font-bold text-white mb-4">Crear tarea</h3>
      <form class="grid md:grid-cols-2 gap-4" @submit.prevent="crearTarea">
        <select v-model="nuevaTarea.asignatura_id" required class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white">
          <option value="">Selecciona una materia</option>
          <option v-for="materia in materias" :key="materia._id || materia.id" :value="materia._id || materia.id">{{ materia.nombre_materia }}</option>
        </select>
        <input v-model="nuevaTarea.titulo" required placeholder="Nombre de la tarea" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <textarea v-model="nuevaTarea.descripcion" required rows="2" placeholder="Descripción" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white md:col-span-2"></textarea>
        <input v-model="nuevaTarea.fecha_entrega_limite" required type="date" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <input v-model="nuevaTarea.puntaje_maximo" required type="number" min="1" max="100" placeholder="Puntaje máximo" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white" />
        <input v-model="rubricaTexto" placeholder="Criterio: Añade lo que la ia debe evaluar sobre esta tarea. (Se claro)" class="bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white md:col-span-2" />
        <button class="bg-studia-purple text-white rounded-lg px-4 py-2 text-sm font-semibold" :disabled="cargando">Guardar tarea</button>
      </form>
      <p v-if="mensaje" class="mt-3 text-sm text-green-400">{{ mensaje }}</p>
      <p v-if="error" class="mt-3 text-sm text-red-400">{{ error }}</p>
    </div>

    <div class="space-y-4">
      <div v-for="tarea in tareas" :key="tarea._id || tarea.id" class="bg-studia-card p-6 rounded-xl border border-gray-800 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
          <h3 class="text-lg font-bold text-white mb-1">{{ tarea.titulo }}</h3>
          <p class="text-xs text-gray-500">{{ tarea.descripcion }}</p>
        </div>
        <div class="flex flex-col items-start md:items-end gap-2">
          <RouterLink :to="`/dashboard/tareas/${tarea._id || tarea.id}`" class="text-studia-purple text-sm font-bold flex items-center gap-2 hover:text-white transition-colors">
            Ver detalles
            <span>→</span>
          </RouterLink>
          <p class="text-xs text-gray-500">Fecha límite: {{ tarea.fecha_entrega_limite }}</p>
          <p class="text-xs text-gray-500">Puntaje: {{ tarea.puntaje_maximo || 100 }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/api/axios'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const tareas = ref([])
const materias = ref([])
const cargando = ref(false)
const mensaje = ref('')
const error = ref('')
const rubricaTexto = ref('')
const nuevaTarea = ref({ asignatura_id: '', titulo: '', descripcion: '', fecha_entrega_limite: '', puntaje_maximo: 100 })

const cargarMaterias = async () => {
  try {
    const response = await api.get('/asignaturas')
    materias.value = response.data
  } catch (err) {
    console.error(err)
  }
}

const cargarTareas = async () => {
  try {
    const materiasActuales = materias.value.length ? materias.value : (await api.get('/asignaturas')).data
    const tareasPorMateria = []
    for (const materia of materiasActuales) {
      const id = materia._id || materia.id
      try {
        const response = await api.get(`/asignaturas/${id}/tareas`)
        tareasPorMateria.push(...response.data)
      } catch (err) {
        console.error(err)
      }
    }
    tareas.value = tareasPorMateria
  } catch (err) {
    console.error(err)
  }
}

const crearTarea = async () => {
  cargando.value = true
  mensaje.value = ''
  error.value = ''
  try {
    const payload = {
      asignatura_id: nuevaTarea.value.asignatura_id,
      titulo: nuevaTarea.value.titulo,
      descripcion: nuevaTarea.value.descripcion,
      fecha_entrega_limite: nuevaTarea.value.fecha_entrega_limite,
      puntaje_maximo: Number(nuevaTarea.value.puntaje_maximo),
      rubrica: rubricaTexto.value ? [{ criterio: rubricaTexto.value, puntaje: Number(nuevaTarea.value.puntaje_maximo) }] : [{ criterio: 'Comprensión general', puntaje: Number(nuevaTarea.value.puntaje_maximo) }]
    }
    await api.post('/tareas', payload)
    nuevaTarea.value = { asignatura_id: '', titulo: '', descripcion: '', fecha_entrega_limite: '', puntaje_maximo: 100 }
    rubricaTexto.value = ''
    mensaje.value = 'Tarea creada correctamente.'
    await cargarMaterias()
    await cargarTareas()
  } catch (err) {
    error.value = err.response?.data?.error || 'No se pudo crear la tarea.'
  } finally {
    cargando.value = false
  }
}

onMounted(async () => {
  await cargarMaterias()
  await cargarTareas()
})
</script>