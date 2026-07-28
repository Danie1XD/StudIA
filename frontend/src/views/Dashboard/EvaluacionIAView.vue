<template>
  <div>
    <!-- Encabezado -->
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Evaluación IA</h2>
      <div class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-8 h-8 rounded-full" />
      </div>
    </div>

    <!-- Tarjeta de Reporte de IA -->
    <div class="bg-studia-card p-8 rounded-xl border border-studia-purple/30 shadow-[0_0_15px_rgba(147,51,234,0.1)] mb-6 relative overflow-hidden">
      
      <!-- Fondo decorativo (brillo morado) -->
      <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-studia-purple opacity-10 rounded-full blur-3xl"></div>

      <!-- Cabecera del Reporte -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 border-b border-gray-800 pb-6 relative z-10">
        <div>
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            <h3 class="text-gray-400 text-sm font-bold tracking-widest uppercase">Reporte Generado por Gemini</h3>
          </div>
          <h2 class="text-2xl font-bold text-white">Implementación de BD</h2>
          <p class="text-gray-500 text-sm">Entregado el: 01/Jul/2026</p>
        </div>
        
        <!-- Etiqueta de Estado -->
        <div class="bg-green-500/10 text-green-400 border border-green-500/20 px-4 py-2 rounded-full text-sm font-bold flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          Análisis Completado
        </div>
      </div>

      <!-- Puntuación Sugerida -->
      <div class="mb-8 relative z-10">
        <div class="flex justify-between items-end mb-2">
          <h4 class="text-lg font-bold text-white">Puntaje Sugerido</h4>
          <span class="text-3xl font-bold text-green-400">92<span class="text-lg text-gray-500">/100</span></span>
        </div>
        <!-- Barra de progreso dinámica -->
        <div class="w-full bg-gray-800 rounded-full h-3 mb-1 overflow-hidden">
          <!-- Aquí el style="width: 92%" cambiará dinámicamente según lo que diga la IA -->
          <div class="bg-gradient-to-r from-studia-purple to-green-400 h-full rounded-full transition-all duration-1000" style="width: 92%"></div>
        </div>
        <p class="text-xs text-gray-500 text-right">Pendiente de validación por el docente</p>
      </div>

      <!-- Retroalimentación (Feedback) -->
      <div class="relative z-10">
        <h4 class="text-lg font-bold text-white mb-4">Retroalimentación Detallada</h4>
        <div class="bg-studia-dark p-6 rounded-lg border border-gray-800/50">
          <p class="text-gray-300 leading-relaxed text-sm">
            El trabajo cumple de manera excelente con los requisitos de la rúbrica. El diagrama Entidad-Relación está estructurado correctamente, identificando de manera clara las llaves primarias y foráneas. 
            <br><br>
            <strong class="text-white">Puntos fuertes:</strong> Las consultas SQL están muy bien optimizadas y hacen uso correcto de los JOINs.
            <br><br>
            <strong class="text-studia-purple">Área de oportunidad:</strong> Se sugiere agregar índices a las tablas transaccionales para mejorar el rendimiento de la base de datos a futuro. Faltó incluir la normalización de la tercera forma normal en la tabla de 'proveedores'.
          </p>
        </div>
      </div>

    </div>

    <!-- Alerta informativa -->
    <div class="bg-blue-500/10 border-l-4 border-blue-500 p-4 rounded-r-lg flex gap-4 items-start shadow-lg">
      <svg class="w-6 h-6 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      <div>
        <h4 class="text-white font-bold text-sm">Evaluación Preliminar</h4>
        <p class="text-gray-400 text-xs mt-1">Este resultado fue generado por Inteligencia Artificial. Tu calificación oficial será asignada una vez que la Mtra. Panchita Pérez revise y valide este reporte.</p>
      </div>
    </div>

  </div>
</template>
