<template>
  <div class="space-y-6">
    <!-- Encabezado de Bienvenida -->
    <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-white">Panel de Alumno</h1>
        <p class="text-gray-400 text-sm mt-1">Hola, {{ authStore.user?.name || 'Estudiante' }}. Consulta tus materias pendientes y realiza tus entregas.</p>
      </div>
      <div class="bg-blue-500/20 border border-blue-500 text-blue-300 px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-wider">
        Rol: Alumno
      </div>
    </div>

    <!-- Módulos de Acceso Rápido para Alumnos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-md">
        <h2 class="text-lg font-bold text-white mb-2">Mis Materias y Tareas</h2>
        <p class="text-gray-400 text-sm mb-4">Revisa el listado de asignaturas activas y sube tus archivos de entrega.</p>
        <router-link to="/dashboard/tareas" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
          Ver Tareas
        </router-link>
      </div>

      <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-md">
        <h2 class="text-lg font-bold text-white mb-2">Mi Perfil Académico</h2>
        <p class="text-gray-400 text-sm mb-4">Consulta tu información personal y estado de cuenta dentro del sistema.</p>
        <router-link to="/dashboard/perfil" class="inline-block bg-blue-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
          Ver Perfil
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth'
const authStore = useAuthStore()
</script>