<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { rubricasApi } from '@/api/rubricas'
import { mensajeDeError } from '@/api/errores'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'

// Solo se aceptan avisos conocidos: lo que venga en la URL nunca se muestra tal cual
const AVISOS = {
  creada: 'La rúbrica se creó.',
  actualizada: 'La rúbrica se actualizó.',
}

const route = useRoute()
const router = useRouter()

const clave = String(route.query.aviso ?? '')

const rubricas = ref([])
const cargando = ref(true)
const error = ref('')
const aviso = ref(Object.hasOwn(AVISOS, clave) ? AVISOS[clave] : '')

const aEliminar = ref(null)
const eliminando = ref(false)
const errorEliminar = ref('')

function usoTexto(cantidad) {
  if (cantidad === 0) return 'No se usa en ninguna tarea'
  return cantidad === 1 ? 'Usada en 1 tarea' : `Usada en ${cantidad} tareas`
}

async function cargar() {
  error.value = ''

  try {
    rubricas.value = await rubricasApi.listar()
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}

function pedirEliminar(rubrica) {
  aviso.value = ''
  errorEliminar.value = ''
  aEliminar.value = rubrica
}

async function eliminar() {
  const nombre = aEliminar.value.nombre
  eliminando.value = true
  errorEliminar.value = ''

  try {
    await rubricasApi.eliminar(aEliminar.value.id)
    aEliminar.value = null
    aviso.value = `Se eliminó la rúbrica «${nombre}».`
    await cargar()
  } catch (e) {
    errorEliminar.value = mensajeDeError(e)
  } finally {
    eliminando.value = false
  }
}

onMounted(() => {
  if (route.query.aviso) router.replace({ query: {} }) // el aviso se muestra una sola vez
  cargar()
})
</script>

<template>
  <section>
    <header class="encabezado">
      <div>
        <h1>Rúbricas</h1>
        <p class="suave">
          Define qué evalúa la IA, criterio por criterio, y reutiliza cada rúbrica en varias tareas.
        </p>
      </div>
      <RouterLink :to="{ name: 'docente.rubrica.nueva' }" class="boton">Nueva rúbrica</RouterLink>
    </header>

    <p v-if="aviso" class="alerta alerta--exito" role="status">{{ aviso }}</p>

    <p v-if="cargando" class="suave">Cargando tus rúbricas…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="!rubricas.length" class="tarjeta vacio">
      <h2>Aún no tienes rúbricas</h2>
      <p class="suave">
        Crea la primera con «Nueva rúbrica» y asígnala al crear o editar una tarea.
      </p>
    </div>

    <div v-else class="rejilla">
      <article v-for="rubrica in rubricas" :key="rubrica.id" class="tarjeta rubrica">
        <h2>{{ rubrica.nombre }}</h2>
        <p v-if="rubrica.descripcion" class="suave texto-libre">{{ rubrica.descripcion }}</p>

        <p class="suave rubrica__meta">
          <span>
            {{ rubrica.criterios.length }}
            {{ rubrica.criterios.length === 1 ? 'criterio' : 'criterios' }}
          </span>
          <span>{{ rubrica.puntos_total }} puntos</span>
          <span>{{ usoTexto(rubrica.tareas_count) }}</span>
        </p>

        <details>
          <summary>Ver criterios</summary>
          <ol class="rubrica__criterios">
            <li v-for="criterio in rubrica.criterios" :key="criterio.id">
              <strong>{{ criterio.nombre }}</strong> · {{ criterio.puntos }} pts
              <span v-if="criterio.descripcion" class="suave texto-libre">
                — {{ criterio.descripcion }}
              </span>
            </li>
          </ol>
        </details>

        <div class="acciones acciones--izquierda">
          <RouterLink
            class="boton boton--secundario boton--chico"
            :to="{ name: 'docente.rubrica', params: { id: rubrica.id } }"
            :aria-label="`Editar la rúbrica ${rubrica.nombre}`"
          >
            Editar
          </RouterLink>
          <button
            type="button"
            class="boton boton--peligro boton--chico"
            :aria-label="`Eliminar la rúbrica ${rubrica.nombre}`"
            @click="pedirEliminar(rubrica)"
          >
            Eliminar
          </button>
        </div>
      </article>
    </div>

    <ModalConfirmacion
      v-if="aEliminar"
      titulo="Eliminar rúbrica"
      texto-confirmar="Eliminar"
      peligro
      :cargando="eliminando"
      :error="errorEliminar"
      @confirmar="eliminar"
      @cerrar="aEliminar = null"
    >
      <p>
        ¿Eliminar la rúbrica <strong>{{ aEliminar.nombre }}</strong
        >?
      </p>
      <p v-if="aEliminar.tareas_count">
        Se usa en {{ aEliminar.tareas_count }}
        {{ aEliminar.tareas_count === 1 ? 'tarea, que quedará' : 'tareas, que quedarán' }}
        sin rúbrica: la IA las evaluará solo con sus instrucciones. Las pre-evaluaciones que ya
        generaste no cambian.
      </p>
    </ModalConfirmacion>
  </section>
</template>

<style scoped>
.rubrica {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.rubrica h2,
.rubrica p {
  margin: 0;
}

.rubrica__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.25rem 1rem;
  font-size: 0.9rem;
}

.rubrica summary {
  cursor: pointer;
  font-weight: 600;
}

.rubrica__criterios {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin: 0.5rem 0 0;
  padding-left: 1.25rem;
}
</style>
