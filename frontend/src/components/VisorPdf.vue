<script setup>
import { computed, onBeforeUnmount, onMounted, ref, useId } from 'vue'
import { mensajeDeError } from '@/api/errores'
import { guardarArchivo } from '@/utils/descarga'

const props = defineProps({
  titulo: { type: String, required: true },
  nombreArchivo: { type: String, default: 'documento.pdf' },
  /** Función que devuelve (como promesa) el archivo ya descargado, como Blob. */
  cargar: { type: Function, required: true },
})

const emit = defineEmits(['cerrar'])

const dialogo = ref(null)
const idTitulo = useId()
const cargando = ref(true)
const error = ref('')
const blob = ref(null)
const url = ref('')
const ampliado = ref(false)
let pulsoEnElFondo = false

// #toolbar y #view ajustan el visor nativo del navegador; si no los entiende, los ignora
const fuente = computed(() => (url.value ? `${url.value}#view=FitH` : ''))

onMounted(async () => {
  dialogo.value?.showModal()

  try {
    const archivo = await props.cargar()
    // Se fuerza el tipo: así el navegador lo muestra como PDF aunque el servidor no lo indique
    blob.value = new Blob([archivo], { type: 'application/pdf' })
    url.value = URL.createObjectURL(blob.value)
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})

onBeforeUnmount(() => {
  if (url.value) URL.revokeObjectURL(url.value)
  if (dialogo.value?.open) dialogo.value.close()
})

function descargar() {
  if (blob.value) guardarArchivo(blob.value, props.nombreArchivo)
}

function alPulsar(evento) {
  pulsoEnElFondo = evento.target === dialogo.value
}

function alHacerClic(evento) {
  if (pulsoEnElFondo && evento.target === dialogo.value) emit('cerrar')
  pulsoEnElFondo = false
}
</script>

<template>
  <dialog
    ref="dialogo"
    :class="['visor', { 'visor--ampliado': ampliado }]"
    :aria-labelledby="idTitulo"
    @close="emit('cerrar')"
    @mousedown="alPulsar"
    @click="alHacerClic"
  >
    <div class="visor__ventana">
      <header class="visor__barra">
        <h2 :id="idTitulo" class="visor__titulo" :title="titulo">{{ titulo }}</h2>

        <div class="visor__acciones">
          <button
            type="button"
            class="boton boton--secundario boton--chico"
            :disabled="!blob"
            @click="descargar"
          >
            Descargar
          </button>
          <button
            type="button"
            class="boton boton--fantasma boton--chico visor__ampliar"
            :aria-pressed="ampliado"
            @click="ampliado = !ampliado"
          >
            {{ ampliado ? 'Reducir' : 'Ampliar' }}
          </button>
          <button
            type="button"
            class="boton boton--fantasma boton--icono"
            aria-label="Cerrar el visor"
            @click="dialogo.close()"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
              <path d="M6 6l12 12M18 6 6 18" />
            </svg>
          </button>
        </div>
      </header>

      <div class="visor__contenido">
        <p v-if="cargando" class="visor__estado suave" role="status">Cargando el documento…</p>

        <div v-else-if="error" class="visor__estado">
          <p class="alerta" role="alert">{{ error }}</p>
        </div>

        <object v-else :data="fuente" type="application/pdf" class="visor__pdf" :aria-label="titulo">
          <!-- Algunos navegadores móviles no muestran PDF incrustados -->
          <div class="visor__estado">
            <p class="suave">Tu navegador no puede mostrar el PDF dentro de la página.</p>
            <button type="button" class="boton" @click="descargar">Descargar el PDF</button>
          </div>
        </object>
      </div>
    </div>
  </dialog>
</template>

<style scoped>
.visor {
  width: min(94vw, 960px);
  height: min(88dvh, 820px);
  max-height: none;
  padding: 0;
  overflow: hidden;
  border: 1px solid var(--borde);
  border-radius: var(--radio-grande);
  background: var(--superficie);
  color: var(--texto);
  box-shadow: 0 30px 80px -20px rgba(0, 0, 0, 0.55);
}

.visor--ampliado {
  width: 98vw;
  height: 96dvh;
}

.visor[open] {
  animation: subir 0.25s var(--curva);
}

.visor::backdrop {
  background: rgba(15, 10, 35, 0.6);
  backdrop-filter: blur(2px);
}

.visor__ventana {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.visor__barra {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--borde);
  background: var(--superficie-alta);
}

.visor__titulo {
  min-width: 0;
  margin: 0;
  overflow: hidden;
  font-size: 1.05rem;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.visor__acciones {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 0.35rem;
}

.visor__contenido {
  flex: 1;
  min-height: 0;
  background: var(--fondo);
}

.visor__pdf {
  display: block;
  width: 100%;
  height: 100%;
  border: 0;
}

.visor__estado {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  height: 100%;
  padding: 1.5rem;
  text-align: center;
}

@media (max-width: 640px) {
  .visor,
  .visor--ampliado {
    width: 100vw;
    max-width: 100vw;
    height: 100dvh;
    border-radius: 0;
  }

  .visor__ampliar {
    display: none;
  }
}
</style>
