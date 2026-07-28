<template>
  <div>
    <!-- Botón de regreso -->
    <div class="mb-6">
      <RouterLink to="/dashboard/tareas" class="text-gray-400 hover:text-white flex items-center gap-2 transition-colors w-fit">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Volver a tareas
      </RouterLink>
    </div>

    <!-- Contenedor Principal -->
    <div class="bg-studia-card p-8 rounded-xl border border-gray-800 mb-6 shadow-2xl">
      
      <!-- Cabecera de la Tarea -->
      <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-4">
        <div>
          <h2 class="text-3xl font-bold text-white mb-2">Implementación de BD</h2>
          <p class="text-studia-purple text-sm font-semibold">Bases de Datos • Mtra. Panchita Pérez</p>
        </div>
        
        <!-- Distintivo de Estado -->
        <div class="bg-studia-purple/20 text-studia-purple border border-studia-purple px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2">
          <span class="w-2 h-2 rounded-full bg-studia-purple animate-pulse"></span>
          Pendiente
        </div>
      </div>

      <!-- Metadatos -->
      <div class="flex items-center gap-6 text-sm text-gray-400 mb-8 border-b border-gray-800 pb-6">
        <p><span class="font-bold text-gray-300">Fecha límite:</span> 01/Jul/2026</p>
        <p><span class="font-bold text-gray-300">Puntaje máximo:</span> 100 pts</p>
      </div>

      <!-- Instrucciones -->
      <div class="mb-10">
        <h3 class="text-xl font-bold text-white mb-4">Instrucciones de la actividad</h3>
        <p class="text-gray-400 leading-relaxed text-sm bg-studia-dark p-6 rounded-lg border border-gray-800/50">
          Realizar el diseño e implementación de una base de datos relacional en MySQL para un sistema de inventario. 
          El documento debe incluir el diagrama Entidad-Relación, el script de creación de tablas y al menos 5 consultas básicas.
          <br><br>
          <strong class="text-white">Nota:</strong> Sube tu documento final obligatoriamente en formato PDF para que la IA de StudIA pueda procesar y pre-evaluar tu entrega según la rúbrica establecida.
        </p>
      </div>

      <!-- Zona de Subida de Archivo -->
      <div>
        <h3 class="text-xl font-bold text-white mb-4">Tu Trabajo</h3>
        
        <div class="border-2 border-dashed border-gray-700 hover:border-studia-purple bg-studia-dark/50 rounded-xl p-10 flex flex-col items-center justify-center text-center transition-all cursor-pointer group">
          <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mb-4 group-hover:bg-studia-purple/20 group-hover:text-studia-purple transition-colors text-gray-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
          </div>
          <p class="text-white font-bold mb-1">Haz clic para buscar o arrastra tu PDF aquí</p>
          <p class="text-gray-500 text-xs mb-6">Máximo 10MB</p>
          
          <button class="bg-white text-studia-dark font-bold py-2 px-6 rounded-full group-hover:bg-gray-200 transition-colors shadow-lg">
            Seleccionar Archivo
          </button>
        </div>
      </div>
      
      <!-- Botón de Envío -->
      <div class="mt-8 flex justify-end border-t border-gray-800 pt-6">
        <button class="bg-studia-purple text-white font-bold py-3 px-10 rounded-full opacity-50 cursor-not-allowed transition-colors">
          Entregar Tarea
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router'
// Aquí, cuando conectemos la API, usaremos useRoute() para saber qué ID de tarea buscar en la base de datos
</script>