<script setup>
import { onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  codigo: { type: String, required: true },
})

const estado = ref('') // '', 'copiado' o 'error'
let temporizador

async function copiar() {
  try {
    await navigator.clipboard.writeText(props.codigo)
    estado.value = 'copiado'
  } catch {
    estado.value = 'error' // sin permiso o sin contexto seguro
  }

  clearTimeout(temporizador)
  temporizador = setTimeout(() => (estado.value = ''), 2000)
}

onBeforeUnmount(() => clearTimeout(temporizador))
</script>

<template>
  <div class="codigo">
    <span class="suave">Código de acceso</span>
    <code class="codigo__valor">{{ codigo }}</code>
    <button type="button" class="boton boton--fantasma boton--chico" @click="copiar">
      {{ estado === 'copiado' ? 'Copiado' : 'Copiar' }}
    </button>
    <span v-if="estado === 'error'" class="codigo__error" role="alert">No se pudo copiar</span>
    <span class="solo-lectores" role="status">{{
      estado === 'copiado' ? 'Código copiado' : ''
    }}</span>
  </div>
</template>

<style scoped>
.codigo {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.codigo__valor {
  padding: 0.2rem 0.7rem;
  border: 1px dashed var(--borde-campo);
  border-radius: 8px;
  background: var(--superficie-alta);
  color: var(--texto);
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  font-size: 1.05rem;
  font-weight: 700;
  letter-spacing: 0.12em;
}

.codigo__error {
  color: var(--error-texto);
  font-size: 0.9rem;
}
</style>
