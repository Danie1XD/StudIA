<script setup>
import { onMounted, ref } from 'vue'
import { comentariosApi } from '@/api/comentarios'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { formatearFechaHora } from '@/utils/fechas'
import CampoTexto from '@/components/CampoTexto.vue'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'

const props = defineProps({
  tareaId: { type: [String, Number], required: true },
})

const comentarios = ref([])
const cargando = ref(true)
const error = ref('')

const mensaje = ref('')
const errorMensaje = ref('')
const errorPublicar = ref('')
const publicando = ref(false)

const aEliminar = ref(null)
const eliminando = ref(false)
const errorEliminar = ref('')

onMounted(async () => {
  try {
    comentarios.value = await comentariosApi.listar(props.tareaId)
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})

async function publicar() {
  errorMensaje.value = ''
  errorPublicar.value = ''

  if (!mensaje.value.trim()) {
    errorMensaje.value = 'Escribe un comentario.'
    return
  }

  publicando.value = true

  try {
    const nuevo = await comentariosApi.crear(props.tareaId, mensaje.value.trim())
    comentarios.value = [...comentarios.value, nuevo]
    mensaje.value = ''
  } catch (e) {
    errorMensaje.value = erroresDeCampos(e).mensaje ?? ''
    if (!errorMensaje.value) errorPublicar.value = mensajeDeError(e)
  } finally {
    publicando.value = false
  }
}

function pedirEliminar(comentario) {
  errorEliminar.value = ''
  aEliminar.value = comentario
}

async function eliminar() {
  eliminando.value = true
  errorEliminar.value = ''

  try {
    await comentariosApi.eliminar(aEliminar.value.id)
    comentarios.value = comentarios.value.filter((c) => c.id !== aEliminar.value.id)
    aEliminar.value = null
  } catch (e) {
    errorEliminar.value = mensajeDeError(e)
  } finally {
    eliminando.value = false
  }
}
</script>

<template>
  <section class="foro tarjeta">
    <h2>Foro de la clase</h2>
    <p class="suave">Tus compañeros y tu docente ven estos comentarios.</p>

    <p v-if="cargando" class="suave">Cargando comentarios…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <template v-else>
      <p v-if="!comentarios.length" class="suave">Aún no hay comentarios. Sé el primero.</p>

      <ul v-else class="foro__lista">
        <li v-for="comentario in comentarios" :key="comentario.id" class="comentario">
          <div class="comentario__encabezado">
            <strong>{{ comentario.autor?.nombre }}</strong>
            <span v-if="comentario.autor?.rol === 'docente'" class="insignia">Docente</span>
            <span class="suave">{{ formatearFechaHora(comentario.created_at) }}</span>
            <button
              v-if="comentario.puede_eliminar"
              type="button"
              class="boton boton--fantasma boton--chico comentario__eliminar"
              :aria-label="`Eliminar el comentario de ${comentario.autor?.nombre}`"
              @click="pedirEliminar(comentario)"
            >
              Eliminar
            </button>
          </div>
          <p class="texto-libre">{{ comentario.mensaje }}</p>
        </li>
      </ul>

      <form class="formulario" novalidate @submit.prevent="publicar">
        <p v-if="errorPublicar" class="alerta" role="alert">{{ errorPublicar }}</p>

        <CampoTexto
          v-model="mensaje"
          etiqueta="Escribe un comentario"
          area
          :error="errorMensaje"
          maxlength="2000"
        />

        <div class="acciones">
          <button type="submit" class="boton" :disabled="publicando">
            {{ publicando ? 'Publicando…' : 'Publicar' }}
          </button>
        </div>
      </form>
    </template>

    <ModalConfirmacion
      v-if="aEliminar"
      titulo="Eliminar comentario"
      texto-confirmar="Eliminar"
      peligro
      :cargando="eliminando"
      :error="errorEliminar"
      @confirmar="eliminar"
      @cerrar="aEliminar = null"
    >
      <p>¿Eliminar este comentario? Esta acción no se puede deshacer.</p>
    </ModalConfirmacion>
  </section>
</template>

<style scoped>
.foro {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.foro h2,
.foro p {
  margin: 0;
}

.foro__lista {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.comentario {
  padding: 0.75rem 0.9rem;
  border-radius: var(--radio);
  background: var(--superficie-alta);
}

.comentario__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.comentario__eliminar {
  margin-left: auto;
}
</style>
