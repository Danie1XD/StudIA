<script setup>
defineProps({
  nombre: { type: String, required: true },
})

const ICONOS = {
  inicio: ['M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z'],
  materias: [
    'M4 4.5A1.5 1.5 0 0 1 5.5 3H20v15H5.5A1.5 1.5 0 0 0 4 19.5z',
    'M4 19.5A1.5 1.5 0 0 0 5.5 21H20',
  ],
  tareas: [
    'M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1z',
    'M8 5H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2',
    'm9 13 2 2 4-4',
  ],
  promedios: ['M5 20V11', 'M12 20V4', 'M19 20v-6'],
  asistente: [
    'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8z',
    'M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z',
  ],
  perfil: ['M12 4a4 4 0 1 1 0 8 4 4 0 0 1 0-8z', 'M4 21a8 8 0 0 1 16 0'],
  entregas: ['M3 13l3-8h12l3 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z', 'M3 13h5a4 4 0 0 0 8 0h5'],
  rubricas: ['M9 6h11', 'M9 12h11', 'M9 18h11', 'M4 6h.01', 'M4 12h.01', 'M4 18h.01'],
  usuarios: [
    'M9 4a3.5 3.5 0 1 1 0 7 3.5 3.5 0 0 1 0-7z',
    'M2.5 20a6.5 6.5 0 0 1 13 0',
    'M16 4.6a3.5 3.5 0 0 1 0 6.8',
    'M18 14a6.5 6.5 0 0 1 3.5 6',
  ],
  calendario: [
    'M5 4h14a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1z',
    'M4 9h16',
    'M8 2v4',
    'M16 2v4',
  ],
  salir: ['M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4', 'm16 17 5-5-5-5', 'M21 12H9'],
  buscar: ['M11 4a7 7 0 1 1 0 14 7 7 0 0 1 0-14z', 'm20 20-3.5-3.5'],
  campana: ['M6 9a6 6 0 1 1 12 0c0 5 2 6 2 7H4c0-1 2-2 2-7z', 'M10 20a2 2 0 0 0 4 0'],
  flecha: ['M5 12h14', 'm13 6 6 6-6 6'],
  mas: ['M12 5v14', 'M5 12h14'],
  politicas: ['M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z', 'm9 12 2 2 4-4'],
  menu: ['M4 7h16', 'M4 12h16', 'M4 17h16'],
  sol: [
    'M12 8a4 4 0 1 1 0 8 4 4 0 0 1 0-8z',
    'M12 2v2',
    'M12 20v2',
    'M4.9 4.9l1.4 1.4',
    'M17.7 17.7l1.4 1.4',
    'M2 12h2',
    'M20 12h2',
    'M4.9 19.1l1.4-1.4',
    'M17.7 6.3l1.4-1.4',
  ],
  luna: ['M20 14.5A8 8 0 0 1 9.5 4 8 8 0 1 0 20 14.5z'],
  correo: ['M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z', 'm3 7 9 6 9-6'],
  candado: ['M5 11h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1z', 'M8 11V8a4 4 0 0 1 8 0v3'],
}
</script>

<template>
  <svg
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
  >
    <path v-for="(d, i) in ICONOS[nombre] ?? []" :key="i" :d="d" />
  </svg>
</template>
