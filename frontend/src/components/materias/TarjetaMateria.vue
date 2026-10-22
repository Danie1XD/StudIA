<script setup>
import { computed } from 'vue'
import { tonoDeMateria } from '@/utils/colores'

const props = defineProps({
  materia: { type: Object, required: true },
  to: { type: [String, Object], required: true },
})

const tono = computed(() => tonoDeMateria(props.materia.id))
</script>

<template>
  <article :class="['materia', 'tarjeta', tono]">
    <div class="materia__banner">
      <h2 class="materia__titulo">
        <RouterLink :to="to" class="materia__enlace">{{ materia.nombre }}</RouterLink>
      </h2>
      <p class="materia__meta"><slot name="meta" /></p>
    </div>

    <div class="materia__cuerpo">
      <p v-if="materia.descripcion" class="materia__descripcion suave">
        {{ materia.descripcion }}
      </p>
      <slot />
    </div>
  </article>
</template>

<style scoped>
.materia.tarjeta {
  position: relative;
  display: flex;
  flex-direction: column;
  padding: 0;
  overflow: hidden;
  transition:
    transform 0.2s var(--curva),
    border-color 0.2s;
}

.materia.tarjeta:hover {
  transform: translateY(-3px);
  border-color: color-mix(in srgb, var(--h) 55%, var(--borde));
}

.materia.tarjeta::before {
  content: '';
  height: 6px;
  background: var(--h);
}

.materia__banner {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  padding: 1.2rem 1.35rem 0.4rem;
}

.materia__titulo {
  margin: 0;
  font-size: 1.3rem;
  line-height: 1.2;
  overflow-wrap: anywhere;
}

.materia__enlace {
  color: var(--texto);
  text-decoration: none;
}

/* El enlace cubre toda la tarjeta (salvo los botones de gestión, que quedan por encima) */
.materia__enlace::after {
  content: '';
  position: absolute;
  inset: 0;
}

.materia__enlace:hover {
  color: var(--morado-texto);
}

.materia__meta {
  margin: 0;
  color: var(--h);
  font-size: 0.92rem;
  font-weight: 600;
}

.materia__cuerpo {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  padding: 0.5rem 1.35rem 1.25rem;
}

.materia__cuerpo:empty {
  display: none;
}

.materia__descripcion {
  display: -webkit-box;
  margin: 0;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  line-clamp: 2;
}
</style>
