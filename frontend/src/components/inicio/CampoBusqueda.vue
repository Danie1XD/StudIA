<script setup>
import { useId } from 'vue'
import IconoNav from '@/components/IconoNav.vue'

defineProps({
  etiqueta: { type: String, required: true },
  placeholder: { type: String, default: 'Buscar…' },
})

const modelo = defineModel({ type: String, default: '' })
const id = useId()
</script>

<template>
  <div class="busqueda">
    <label :for="id" class="solo-lectores">{{ etiqueta }}</label>
    <IconoNav nombre="buscar" class="busqueda__icono" />
    <input
      :id="id"
      v-model="modelo"
      class="busqueda__campo"
      type="search"
      :placeholder="placeholder"
      autocomplete="off"
    />
  </div>
</template>

<style scoped>
.busqueda {
  position: relative;
}

.busqueda__icono {
  position: absolute;
  top: 50%;
  left: 1rem;
  width: 1.1rem;
  height: 1.1rem;
  color: var(--texto-suave);
  transform: translateY(-50%);
  pointer-events: none;
}

.busqueda__campo {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  border: 1px solid var(--borde);
  border-radius: 999px;
  background: var(--superficie);
  box-shadow: var(--sombra);
  color: var(--texto);
  font: inherit;
}

.busqueda__campo::placeholder {
  color: var(--texto-suave);
  opacity: 1;
}

.busqueda__campo:focus-visible {
  outline: 2px solid var(--foco);
  outline-offset: 2px;
}
</style>
