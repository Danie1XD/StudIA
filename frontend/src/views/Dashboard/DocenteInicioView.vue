<template>
  <div class="space-y-6">
    <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Panel de Docente</h1>
        <p class="text-gray-400 text-sm mt-1">Bienvenido, {{ authStore.user?.name || 'Profesor' }}. Crea grupos, publica tareas y revisa las evaluaciones de tus alumnos.</p>
      </div>
      <div class="bg-studia-purple/20 border border-studia-purple text-studia-purple px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-wider">
        Rol: Docente
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-md">
        <h2 class="text-lg font-bold text-white mb-2">Crear Grupo</h2>
        <p class="text-gray-400 text-sm mb-4">Organiza a tus alumnos por materia o sección.</p>
        <button class="inline-block bg-studia-purple text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-purple-700 transition-colors">
          Nuevo Grupo
        </button>
      </div>

      <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-md">
        <h2 class="text-lg font-bold text-white mb-2">Crear Tarea</h2>
        <p class="text-gray-400 text-sm mb-4">Publica nuevas actividades con rúbrica para tus grupos.</p>
        <router-link to="/dashboard/materias" class="inline-block bg-studia-purple text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-purple-700 transition-colors">
          Gestionar Materias
        </router-link>
      </div>

      <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-md">
        <h2 class="text-lg font-bold text-white mb-2">Evaluar Entregas</h2>
        <p class="text-gray-400 text-sm mb-4">Revisa las entregas y envía la calificación final al alumno.</p>
        <router-link to="/dashboard/evaluacion-ia" class="inline-block bg-studia-purple text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-purple-700 transition-colors">
          Ir a Evaluaciones IA
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
const authStore = useAuthStore()
</script>