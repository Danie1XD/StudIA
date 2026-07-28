<template>
  <div>
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Tareas Pendientes</h2>
      <!-- Avatar superior derecho -->
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <!-- Lista de Tareas -->
    <div class="space-y-4">
      
      <!-- Tarjeta dinámica de Tarea -->
      <div 
        v-for="(tarea, index) in tareas" 
        :key="index"
        class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:bg-gray-900 transition-all flex flex-col md:flex-row md:justify-between md:items-center gap-4 group"
      >
        <!-- Información izquierda -->
        <div>
          <h3 class="text-lg font-bold text-white mb-1">{{ tarea.titulo }}</h3>
          <p class="text-xs text-gray-500">{{ tarea.materia }}</p>
        </div>
        
        <!-- Información derecha y botón -->
        <div class="flex flex-col items-start md:items-end gap-2">
          <!-- Este RouterLink nos llevará a la vista de detalle más adelante -->
          <!-- Cambia el to="#" por to="/dashboard/tareas/1" -->
          <RouterLink to="/dashboard/tareas/1" class="text-studia-purple text-sm font-bold flex items-center gap-2 hover:text-white transition-colors">
            Ver detalles
            <span>→</span>
          </RouterLink>
          <p class="text-xs text-gray-500">Fecha de entrega: {{ tarea.fecha_entrega }}</p>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Datos quemados extraídos de tu prototipo de Figma
const tareas = ref([
  {
    titulo: 'Implementación de BD',
    materia: 'Bases de Datos',
    fecha_entrega: '01/Jul/2026'
  },
  {
    titulo: 'Laplace',
    materia: 'Matemáticas',
    fecha_entrega: '03/Jul/2026'
  },
  {
    titulo: 'Proyecto Final - IA',
    materia: 'Programación Avanzada',
    fecha_entrega: '10/Ago/2026'
  }
])
</script>