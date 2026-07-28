<template>
  <div>
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Mis Materias</h2>
      <!-- Avatar de prueba superior derecho -->
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <!-- Cuadrícula de Tarjetas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      
      <!-- Tarjeta dinámica de Materia -->
      <div 
        v-for="(materia, index) in materias" 
        :key="index"
        class="bg-studia-card p-6 rounded-xl border border-gray-800 hover:bg-gray-900 transition-all flex justify-between items-center group cursor-pointer"
      >
        <div>
          <h3 class="text-lg font-bold text-white mb-1">{{ materia.nombre }}</h3>
          <p class="text-xs text-gray-500 mb-6">{{ materia.semestre }}</p>
          <p class="text-xs text-gray-400">{{ materia.docente }}</p>
        </div>
        
        <!-- Círculo de color dinámico con ícono -->
        <div :class="`w-12 h-12 rounded-full border-2 flex items-center justify-center ${materia.color}`">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Estos son los datos de prueba extraídos exactamente de tu prototipo
const materias = ref([
  {
    nombre: 'Bases de datos',
    semestre: '2do cuatrimestre',
    docente: 'Mtra. Panchita Pérez',
    color: 'border-pink-500 text-pink-500'
  },
  {
    nombre: 'Redes de computadoras',
    semestre: '2do cuatrimestre',
    docente: 'Mtro. Panchito López',
    color: 'border-green-500 text-green-500'
  },
  {
    nombre: 'Programación Avanzada',
    semestre: '2do cuatrimestre',
    docente: 'Mtro. Fulanito de Tal',
    color: 'border-purple-500 text-purple-500'
  },
  {
    nombre: 'Inglés',
    semestre: '2do cuatrimestre',
    docente: 'Mtro. Fulanita de Tal',
    color: 'border-blue-500 text-blue-500'
  },
  {
    nombre: 'Tutoría',
    semestre: '2do cuatrimestre',
    docente: 'Mtra. Manganita González',
    color: 'border-purple-400 text-purple-400'
  },
  {
    nombre: 'Matemáticas',
    semestre: '2do cuatrimestre',
    docente: 'Mtro. Perenganito Sánchez',
    color: 'border-blue-400 text-blue-400'
  }
])
</script>