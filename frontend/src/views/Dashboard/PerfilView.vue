<template>
  <div class="max-w-5xl mx-auto">
    <!-- Encabezado -->
    <div class="mb-8">
      <h2 class="text-3xl font-bold uppercase tracking-wider text-white">Mi Perfil</h2>
      <p class="text-gray-400 text-sm mt-1">Gestiona tu información personal y preferencias de la cuenta</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      
      <!-- Columna Izquierda: Tarjeta Principal del Usuario -->
      <div class="lg:col-span-1 space-y-6">
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 flex flex-col items-center text-center shadow-lg">
          <div class="relative w-32 h-32 mb-4 group cursor-pointer">
            <div class="w-full h-full rounded-full bg-studia-dark border-4 border-studia-purple overflow-hidden">
              <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Pedro" alt="Avatar" class="w-full h-full object-cover" />
            </div>
            <!-- Botón flotante para editar foto -->
            <div class="absolute bottom-0 right-0 bg-studia-purple text-white p-2 rounded-full border-2 border-studia-card hover:bg-purple-600 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
            </div>
          </div>
          
          <h3 class="text-xl font-bold text-white">Pedro Jesús</h3>
          <p class="text-studia-purple font-mono text-sm mb-1">@jesus.cshtml</p>
          <div class="inline-flex items-center gap-1 bg-gray-800 px-3 py-1 rounded-full text-xs text-gray-300 mt-2">
            <span class="w-2 h-2 rounded-full bg-green-500"></span>
            Estudiante Activo
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-studia-card rounded-xl border border-gray-800 overflow-hidden shadow-lg">
          <button class="w-full flex items-center justify-between p-4 text-gray-300 hover:bg-gray-800 hover:text-white transition-colors border-b border-gray-800">
            <span class="flex items-center gap-3 text-sm">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Cambiar contraseña
            </span>
            <span>→</span>
          </button>
          <button class="w-full flex items-center justify-between p-4 text-red-400 hover:bg-red-500/10 transition-colors">
            <span class="flex items-center gap-3 text-sm font-bold">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              Cerrar Sesión
            </span>
          </button>
        </div>
      </div>

      <!-- Columna Derecha: Formularios de Información -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Información Académica -->
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg">
          <h4 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-gray-800 pb-2">
            <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
            Información Académica
          </h4>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs text-gray-500 mb-1">Programa Educativo</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                Ingeniería en Desarrollo de Sistemas de Software Multiplataforma
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Matrícula</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full font-mono">
                2233445566
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Grupo de Desarrollo (Startup)</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                ChavosTech
              </div>
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">Equipo / Compañero asignado</label>
              <div class="bg-studia-dark border border-gray-700 text-gray-300 px-4 py-2 rounded-lg text-sm w-full">
                Ángel Geovanny
              </div>
            </div>
          </div>
        </div>

        <!-- Información de Contacto -->
        <div class="bg-studia-card p-6 rounded-xl border border-gray-800 shadow-lg">
          <h4 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-gray-800 pb-2">
            <svg class="w-5 h-5 text-studia-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            Datos de Contacto
          </h4>
          
          <form class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs text-gray-500 mb-1">Correo Electrónico</label>
                <input type="email" value="contacto@chavostech.com" class="bg-transparent border-b border-gray-700 text-white px-1 py-1 focus:outline-none focus:border-studia-purple transition-colors w-full text-sm" />
              </div>
              <div>
                <label class="block text-xs text-gray-500 mb-1">Teléfono Móvil</label>
                <input type="tel" value="+52 55 1234 5678" class="bg-transparent border-b border-gray-700 text-white px-1 py-1 focus:outline-none focus:border-studia-purple transition-colors w-full text-sm" />
              </div>
              <div class="md:col-span-2">
                <label class="block text-xs text-gray-500 mb-1">Ruta de Traslado Principal (Referencia)</label>
                <input type="text" value="Zumpango - Tecámac" class="bg-transparent border-b border-gray-700 text-white px-1 py-1 focus:outline-none focus:border-studia-purple transition-colors w-full text-sm" />
              </div>
            </div>

            <div class="pt-4 text-right">
              <button type="button" class="bg-studia-purple text-white font-bold py-2 px-6 rounded-full hover:bg-purple-600 transition-colors text-sm shadow-lg">
                Guardar Cambios
              </button>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>