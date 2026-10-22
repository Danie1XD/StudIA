<script setup>
import { computed } from 'vue'
import { tonoDeMateria } from '@/utils/colores'

const props = defineProps({
  to: { type: [String, Object], required: true },
  materiaId: { type: [String, Number], required: true },
  titulo: { type: String, required: true },
  subtitulo: { type: String, default: '' },
  insignia: { type: String, default: '' },
  progreso: { type: Number, default: null }, // de 0 a 100; null si aún no aplica
  etiqueta: { type: String, default: 'Progreso' },
  vacio: { type: String, default: 'Aún sin tareas' },
})

const tono = computed(() => tonoDeMateria(props.materiaId))
</script>

<template>
  <RouterLink :to="to" :class="['clase', tono]">
    <div class="clase__arriba">
      <span v-if="insignia" class="clase__insignia">{{ insignia }}</span>
      <h3 class="clase__titulo">{{ titulo }}</h3>
      <p v-if="subtitulo" class="clase__subtitulo">{{ subtitulo }}</p>
    </div>

    <div>
      <div
        v-if="progreso !== null"
        class="progreso"
        role="progressbar"
        :aria-valuenow="progreso"
        aria-valuemin="0"
        aria-valuemax="100"
        :aria-label="`${etiqueta} de ${titulo}`"
      >
        <span class="progreso__relleno" :style="{ width: `${progreso}%` }"></span>
      </div>
      <p class="clase__etiqueta">{{ progreso !== null ? `${etiqueta}: ${progreso}%` : vacio }}</p>
    </div>
  </RouterLink>
</template>

<style scoped>
.clase {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 1.1rem;
  min-height: 150px;
  padding: 1.25rem 1.3rem 1.15rem;
  overflow: hidden;
  border: 1px solid var(--borde);
  border-radius: var(--radio-grande);
  background: var(--superficie);
  box-shadow: var(--sombra);
  color: var(--texto);
  text-decoration: none;
  transition:
    transform 0.2s var(--curva),
    border-color 0.2s;
}

/* Franja de color de la materia */
.clase::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 5px;
  background: var(--h);
}

.clase:hover {
  transform: translateY(-3px);
  border-color: color-mix(in srgb, var(--h) 55%, var(--borde));
}

.clase__insignia {
  display: inline-block;
  margin-bottom: 0.45rem;
  padding: 0.1rem 0.7rem;
  border-radius: 999px;
  background: color-mix(in srgb, var(--h) 16%, var(--superficie));
  color: var(--h);
  font-size: 0.8rem;
  font-weight: 700;
}

.clase__titulo {
  margin: 0;
  font-size: 1.22rem;
  line-height: 1.22;
  overflow-wrap: anywhere;
}

.clase__subtitulo {
  margin: 0.25rem 0 0;
  color: var(--texto-suave);
  font-size: 0.92rem;
}

.progreso {
  height: 8px;
  overflow: hidden;
  border-radius: 999px;
  background: var(--borde);
}

.progreso__relleno {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--h);
  transform-origin: left;
  animation: llenar 0.9s 0.2s var(--curva) both;
}

.clase__etiqueta {
  margin: 0.45rem 0 0;
  color: var(--texto-suave);
  font-size: 0.85rem;
  font-weight: 600;
}
</style>
