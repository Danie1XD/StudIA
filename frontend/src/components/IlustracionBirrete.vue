<script setup>
// Ilustración de la marca: birrete sobre libros. Los degradados se declaran con id propio
// para no chocar con otras ilustraciones de la misma pantalla.
</script>

<template>
  <svg viewBox="0 0 220 200" fill="none" aria-hidden="true" focusable="false">
    <defs>
      <linearGradient id="ib-libro1" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#ffffff" />
        <stop offset="1" stop-color="#d9ccff" />
      </linearGradient>
      <linearGradient id="ib-libro2" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#9b73ff" />
        <stop offset="1" stop-color="#6a35f0" />
      </linearGradient>
      <linearGradient id="ib-gorro" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0" stop-color="#7a47ff" />
        <stop offset="1" stop-color="#3f1bb0" />
      </linearGradient>
    </defs>

    <ellipse cx="110" cy="182" rx="84" ry="10" fill="#6a35f0" opacity="0.16" />

    <!-- libros -->
    <path d="M26 140 110 170l84-30v22l-84 30-84-30z" fill="url(#ib-libro2)" />
    <path d="M26 118 110 148l84-30v22l-84 30-84-30z" fill="url(#ib-libro1)" />
    <path d="M26 118 110 88l84 30-84 30z" fill="#ffffff" opacity="0.95" />
    <path d="M110 148v22" stroke="#6a35f0" stroke-opacity="0.35" />

    <!-- birrete -->
    <path d="M62 78v26c0 12 21 22 48 22s48-10 48-22V78z" fill="url(#ib-gorro)" />
    <path d="M110 30 202 64 110 98 18 64z" fill="url(#ib-gorro)" />
    <path d="M110 30 202 64 110 98z" fill="#ffffff" opacity="0.12" />
    <path d="M110 64 186 72v38" stroke="#ffd36e" stroke-width="3" stroke-linecap="round" />
    <circle cx="186" cy="114" r="6" fill="#ffd36e" />
  </svg>
</template>
