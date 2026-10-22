<script setup>
defineProps({
  tipo: { type: String, default: 'tareas' }, // 'tareas' | 'perfil' | 'asistente' | 'promedios'
})
</script>

<template>
  <svg viewBox="0 0 120 120" fill="none" aria-hidden="true" focusable="false">
    <template v-if="tipo === 'tareas'">
      <rect x="22" y="14" width="64" height="84" rx="10" fill="currentColor" opacity="0.5" />
      <rect x="32" y="8" width="26" height="14" rx="5" fill="currentColor" />
      <path d="m34 44 6 6 11-12M34 66l6 6 11-12" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
      <path d="M60 44h18M60 66h18" stroke="#fff" stroke-width="5" stroke-linecap="round" />
      <path d="M70 84 108 96v10L70 94z" fill="currentColor" />
      <path d="M70 94 108 106 70 104z" fill="currentColor" opacity="0.6" />
    </template>

    <template v-else-if="tipo === 'perfil'">
      <rect x="10" y="22" width="92" height="68" rx="10" fill="currentColor" opacity="0.5" />
      <circle cx="40" cy="48" r="11" fill="#fff" />
      <path d="M22 76c2-11 10-16 18-16s16 5 18 16z" fill="#fff" />
      <path d="M68 44h26M68 58h26M68 72h18" stroke="#fff" stroke-width="5" stroke-linecap="round" />
      <path d="M96 100c0-18 6-30 20-36-2 14-8 28-20 36z" fill="currentColor" opacity="0.7" />
    </template>

    <template v-else-if="tipo === 'asistente'">
      <path d="M60 14l9 26 26 9-26 9-9 26-9-26-26-9 26-9z" fill="currentColor" />
      <path d="M94 74l4 11 11 4-11 4-4 11-4-11-11-4 11-4z" fill="currentColor" opacity="0.6" />
    </template>

    <template v-else>
      <rect x="18" y="62" width="20" height="40" rx="5" fill="currentColor" opacity="0.5" />
      <rect x="50" y="36" width="20" height="66" rx="5" fill="currentColor" opacity="0.75" />
      <rect x="82" y="16" width="20" height="86" rx="5" fill="currentColor" />
    </template>
  </svg>
</template>
