<script setup>
import { onBeforeUnmount, onMounted, ref, useId } from 'vue'

defineProps({
  titulo: { type: String, required: true },
})

const emit = defineEmits(['cerrar'])

const dialogo = ref(null)
const idTitulo = useId()
let pulsoEnElFondo = false

// El padre monta el modal con v-if cuando hace falta; al montarse se abre
onMounted(() => dialogo.value?.showModal())

// Si se quita mientras está abierto, se cierra bien para que el foco vuelva a donde estaba
onBeforeUnmount(() => {
  if (dialogo.value?.open) dialogo.value.close()
})

function alPulsar(evento) {
  pulsoEnElFondo = evento.target === dialogo.value
}

function alHacerClic(evento) {
  // Pulsar fuera del cuadro cierra el modal, pero no si el clic empezó dentro
  // (por ejemplo, al seleccionar texto de un campo y soltar el ratón afuera)
  if (pulsoEnElFondo && evento.target === dialogo.value) emit('cerrar')
  pulsoEnElFondo = false
}
</script>

<template>
  <dialog
    ref="dialogo"
    class="modal"
    :aria-labelledby="idTitulo"
    @close="emit('cerrar')"
    @mousedown="alPulsar"
    @click="alHacerClic"
  >
    <div class="modal__cuerpo">
      <header class="modal__encabezado">
        <h2 :id="idTitulo" class="modal__titulo">{{ titulo }}</h2>
        <button
          type="button"
          class="boton boton--fantasma boton--icono"
          aria-label="Cerrar"
          @click="dialogo.close()"
        >
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            aria-hidden="true"
          >
            <path d="M6 6l12 12M18 6 6 18" />
          </svg>
        </button>
      </header>

      <slot />
    </div>
  </dialog>
</template>

<style scoped>
.modal {
  width: min(92vw, 540px);
  max-height: 90vh;
  max-height: 90dvh;
  padding: 0;
  overflow: auto;
  border: 1px solid var(--borde);
  border-radius: 22px;
  background: var(--superficie);
  color: var(--texto);
  box-shadow: 0 30px 80px -20px rgba(0, 0, 0, 0.55);
}

.modal[open] {
  animation: subir 0.25s var(--curva);
}

.modal::backdrop {
  background: rgba(15, 10, 35, 0.6);
  backdrop-filter: blur(2px);
}

.modal[open]::backdrop {
  animation: aparecer 0.2s;
}

.modal__cuerpo {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.5rem;
}

.modal__encabezado {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.modal__titulo {
  margin: 0;
  font-size: 1.4rem;
}

/* En el celular el modal sube desde abajo, como una hoja */
@media (max-width: 640px) {
  .modal {
    width: 100%;
    max-width: 100%;
    margin: auto 0 0;
    border-radius: 24px 24px 0 0;
  }

  .modal__cuerpo {
    padding: 1.25rem 1.1rem calc(1.5rem + env(safe-area-inset-bottom, 0px));
  }
}
</style>
