<script setup>
import { computed, onMounted, ref } from 'vue'
import { materiasApi } from '@/api/materias'
import { mensajeDeError } from '@/api/errores'
import { useAuthStore } from '@/stores/auth'
import CodigoAcceso from '@/components/materias/CodigoAcceso.vue'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'
import ModalMateria from '@/components/materias/ModalMateria.vue'
import ModalUnirse from '@/components/materias/ModalUnirse.vue'
import TarjetaMateria from '@/components/materias/TarjetaMateria.vue'
import confetti from 'canvas-confetti' // Importación de la librería de confeti

const auth = useAuthStore()
const esDocente = computed(() => auth.rol === 'docente')
const prefijo = computed(() => (esDocente.value ? 'docente' : 'alumno'))

const materias = ref([])
const cargando = ref(true)
const error = ref('')
const aviso = ref('')

const mostrarFormulario = ref(false)
const materiaEnEdicion = ref(null)
const mostrarUnirse = ref(false)
const materiaAEliminar = ref(null)
const eliminando = ref(false)
const errorEliminar = ref('')

async function cargar() {
  error.value = ''
  try {
    materias.value = await materiasApi.listar()
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}

function abrirNueva() {
  aviso.value = ''
  materiaEnEdicion.value = null
  mostrarFormulario.value = true
}

function abrirEdicion(materia) {
  aviso.value = ''
  materiaEnEdicion.value = materia
  mostrarFormulario.value = true
}

function cerrarFormulario() {
  mostrarFormulario.value = false
  materiaEnEdicion.value = null
}

async function alGuardar() {
  const eraEdicion = materiaEnEdicion.value !== null
  cerrarFormulario()
  aviso.value = eraEdicion
    ? 'Los cambios se guardaron.'
    : 'La materia se creó. Comparte su código con tus alumnos.'
  await cargar()
}

async function alUnirse(materia) {
  mostrarUnirse.value = false
  aviso.value = `Te uniste a «${materia.nombre}».`
  await cargar()

  // Disparar la animación de confeti con los colores de StudIA
  confetti({
    particleCount: 120,
    spread: 80,
    origin: { y: 0.6 }, // Ligeramente más abajo del centro para un mejor arco
    colors: ['#b19cf9', '#4ade80', '#fbbf24', '#f472b6', '#38bdf8'] 
  })
}

function pedirEliminar(materia) {
  aviso.value = ''
  errorEliminar.value = ''
  materiaAEliminar.value = materia
}

function cancelarEliminar() {
  materiaAEliminar.value = null
}

async function confirmarEliminar() {
  const nombre = materiaAEliminar.value.nombre
  eliminando.value = true
  errorEliminar.value = ''

  try {
    await materiasApi.eliminar(materiaAEliminar.value.id)
    materiaAEliminar.value = null
    aviso.value = `Se eliminó «${nombre}».`
    await cargar()
  } catch (e) {
    errorEliminar.value = mensajeDeError(e)
  } finally {
    eliminando.value = false
  }
}

onMounted(cargar)
</script>

<template>
  <section>
    <header class="encabezado">
      <div>
        <h1>Mis materias</h1>
        <p class="suave">
          {{
            esDocente
              ? 'Gestiona tus grupos y comparte el código con tus alumnos.'
              : 'Clases en las que estás inscrito.'
          }}
        </p>
      </div>

      <button v-if="esDocente" type="button" class="boton" @click="abrirNueva">
        Nueva materia
      </button>
      <button v-else type="button" class="boton" @click="mostrarUnirse = true">
        Unirme con código
      </button>
    </header>

    <p v-if="aviso" class="alerta alerta--exito" role="status">{{ aviso }}</p>

    <p v-if="cargando" class="suave">Cargando materias…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="!materias.length" class="tarjeta vacio">
      <h2>{{ esDocente ? 'Aún no tienes materias' : 'Aún no estás en ninguna materia' }}</h2>
      <p class="suave">
        {{
          esDocente
            ? 'Crea la primera con el botón «Nueva materia».'
            : 'Pide el código a tu docente y úsalo en «Unirme con código».'
        }}
      </p>
    </div>

    <div v-else class="rejilla">
      <TarjetaMateria
        v-for="materia in materias"
        :key="materia.id"
        :materia="materia"
        :to="{ name: `${prefijo}.materia`, params: { id: materia.id } }"
      >
        <template #meta>
          <template v-if="esDocente">{{ materia.alumnos_count ?? 0 }} alumno(s)</template>
          <template v-else>Prof. {{ materia.docente?.nombre }}</template>
        </template>

        <div v-if="esDocente" class="gestion">
          <CodigoAcceso v-if="materia.codigo_acceso" :codigo="materia.codigo_acceso" />
          <div class="acciones">
            <button
              type="button"
              class="boton boton--secundario boton--chico"
              @click="abrirEdicion(materia)"
            >
              Editar
            </button>
            <button
              type="button"
              class="boton boton--peligro boton--chico"
              @click="pedirEliminar(materia)"
            >
              Eliminar
            </button>
          </div>
        </div>
      </TarjetaMateria>
    </div>

    <ModalMateria
      v-if="mostrarFormulario"
      :materia="materiaEnEdicion"
      @guardado="alGuardar"
      @cerrar="cerrarFormulario"
    />

    <ModalUnirse v-if="mostrarUnirse" @unido="alUnirse" @cerrar="mostrarUnirse = false" />

    <ModalConfirmacion
      v-if="materiaAEliminar"
      titulo="Eliminar materia"
      texto-confirmar="Eliminar"
      peligro
      :cargando="eliminando"
      :error="errorEliminar"
      @confirmar="confirmarEliminar"
      @cerrar="cancelarEliminar"
    >
      <p>
        ¿Eliminar <strong>{{ materiaAEliminar.nombre }}</strong
        >? Se borrarán también sus secciones, anuncios, tareas, entregas y comentarios, y los
        archivos que subieron los alumnos. Esta acción no se puede deshacer.
      </p>
    </ModalConfirmacion>
  </section>
</template>

<style scoped>
.gestion {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.gestion .acciones {
  justify-content: flex-start;
}
</style>