<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { entregasApi } from '@/api/entregas'
import { rubricasApi } from '@/api/rubricas'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { tonoDeMateria } from '@/utils/colores'
import { guardarArchivo, nombreDeEntrega } from '@/utils/descarga'
import { formatearFechaHora } from '@/utils/fechas'
import { TIPOS_EVALUACION } from '@/utils/formularioTarea'
import { enlaceDe, infoEstadoFila, siguientePorRevisar } from '@/utils/revision'
import FormularioCalificacion from '@/components/tareas/FormularioCalificacion.vue'
import PanelIA from '@/components/tareas/PanelIA.vue'
import VisorPdf from '@/components/VisorPdf.vue'

const props = defineProps({
  id: { type: [String, Number], required: true },
  entregaId: { type: [String, Number], required: true },
})

const tarea = ref(null)
const rubricaNombre = ref('')
const filas = ref([])
const cargando = ref(true)
const error = ref('')
const errorDescarga = ref('')
const titulo = ref(null)
const formulario = ref(null)

// La entrega que se está viendo se busca en la lista de la tarea (no hay "ver una entrega suelta")
const fila = computed(
  () =>
    filas.value.find((f) => f.entrega && String(f.entrega.id) === String(props.entregaId)) ?? null,
)
const calificada = computed(() => fila.value?.estado === 'calificado')
const siguiente = computed(() => siguientePorRevisar(filas.value, fila.value?.entrega.id))
const enlace = computed(() => enlaceDe(fila.value?.entrega.contenido))
const tipoTexto = computed(
  () => TIPOS_EVALUACION.find((t) => t.valor === tarea.value?.tipo_evaluacion)?.texto ?? '',
)

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion
  cargando.value = true
  error.value = ''
  tarea.value = null
  rubricaNombre.value = ''
  filas.value = []

  try {
    const [datosTarea, datosFilas] = await Promise.all([
      tareasApi.mostrar(props.id),
      entregasApi.listarDeTarea(props.id),
    ])
    if (actual !== ultimaPeticion) return

    // El nombre de la rúbrica solo adorna el panel: si falla, la página sigue
    const rubricas = datosTarea.rubrica_id ? await rubricasApi.listar().catch(() => []) : []
    if (actual !== ultimaPeticion) return

    tarea.value = datosTarea
    filas.value = datosFilas
    rubricaNombre.value = rubricas.find((r) => r.id === datosTarea.rubrica_id)?.nombre ?? ''
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

watch(() => props.id, cargar, { immediate: true })

// Al pasar a la siguiente entrega, el foco va al nombre del alumno
watch(
  () => props.entregaId,
  async () => {
    errorDescarga.value = ''
    await nextTick()
    titulo.value?.focus()
  },
)

const visor = ref(null)
const tono = computed(() => (tarea.value ? tonoDeMateria(tarea.value.materia_id) : ''))

function abrirPdf() {
  errorDescarga.value = ''
  const nombre = nombreDeEntrega(
    `${tarea.value.titulo} ${fila.value.alumno.nombre}`,
    'application/pdf',
  )
  visor.value = {
    titulo: nombre,
    nombreArchivo: nombre,
    cargar: () => entregasApi.descargarArchivo(fila.value.entrega.id),
  }
}

async function descargar() {
  errorDescarga.value = ''

  try {
    const blob = await entregasApi.descargarArchivo(fila.value.entrega.id)
    guardarArchivo(
      blob,
      nombreDeEntrega(`${tarea.value.titulo} ${fila.value.alumno.nombre}`, blob.type),
    )
  } catch (e) {
    errorDescarga.value = mensajeDeError(e)
  }
}

/** Refleja en la pantalla la calificación recién guardada, sin volver a pedir toda la lista. */
function alCalificar(entrega) {
  const i = filas.value.findIndex((f) => f.entrega && f.entrega.id === entrega.id)
  if (i === -1) return

  const actual = filas.value[i]
  filas.value[i] = { ...actual, estado: 'calificado', entrega: { ...actual.entrega, ...entrega } }
}
</script>

<template>
  <section :class="tono">
    <RouterLink :to="{ name: 'docente.tarea', params: { id } }" class="enlace-volver">
      ← {{ tarea ? tarea.titulo : 'Entregas de la tarea' }}
    </RouterLink>

    <p v-if="cargando" class="suave">Cargando la entrega…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="tarea && !fila" class="tarjeta vacio">
      <h2>No se encontró esa entrega</h2>
      <p class="suave">Puede que el alumno la haya anulado.</p>
    </div>

    <template v-else-if="tarea && fila">
      <header class="banner">
        <div class="cabecera__estados">
          <span :class="['insignia', infoEstadoFila(fila.estado).clase]">
            {{ infoEstadoFila(fila.estado).texto }}
          </span>
          <span v-if="fila.entrega.entregada_tarde" class="insignia insignia--aviso">
            Con retraso
          </span>
        </div>

        <h1 ref="titulo" tabindex="-1">{{ fila.alumno.nombre }}</h1>
        <p class="suave">
          {{ fila.alumno.email }} · Entregó el {{ formatearFechaHora(fila.entrega.entregada_at) }}
        </p>
        <p class="suave">{{ tarea.titulo }} · {{ tarea.puntaje_maximo }} puntos</p>

        <div v-if="siguiente" class="acciones acciones--izquierda">
          <RouterLink
            class="boton boton--secundario boton--chico"
            :to="{
              name: 'docente.entrega',
              params: { id: tarea.id, entregaId: siguiente.entrega.id },
            }"
          >
            Siguiente por revisar: {{ siguiente.alumno.nombre }} →
          </RouterLink>
        </div>
      </header>

      <section class="trabajo tarjeta">
        <h2>Trabajo entregado</h2>

        <p v-if="fila.entrega.contenido">
          <a
            v-if="enlace"
            :href="enlace"
            target="_blank"
            rel="noopener noreferrer"
            class="texto-libre"
          >
            {{ fila.entrega.contenido }}
          </a>
          <span v-else class="texto-libre">{{ fila.entrega.contenido }}</span>
        </p>

        <p v-if="errorDescarga" class="alerta" role="alert">{{ errorDescarga }}</p>
        <div v-if="fila.entrega.tiene_archivo" class="acciones acciones--izquierda">
          <button
            v-if="fila.entrega.archivo_es_pdf"
            type="button"
            class="boton boton--secundario boton--chico"
            @click="abrirPdf"
          >
            Abrir el PDF
          </button>
          <button
            type="button"
            :class="['boton', 'boton--chico', fila.entrega.archivo_es_pdf ? 'boton--fantasma' : 'boton--secundario']"
            @click="descargar"
          >
            {{ fila.entrega.archivo_es_pdf ? 'Descargar' : 'Descargar el archivo' }}
          </button>
        </div>
      </section>

      <PanelIA
        :key="`ia-${fila.entrega.id}`"
        :entrega-id="fila.entrega.id"
        :tiene-evaluacion="Boolean(fila.entrega.tiene_evaluacion_ia)"
        :puede-generar="!calificada"
        :puntaje-maximo="tarea.puntaje_maximo"
        :tipo-texto="tipoTexto"
        :rubrica-nombre="rubricaNombre"
        @usar="formulario?.aplicarSugerencia($event)"
      />

      <section class="calificacion tarjeta">
        <h2>Calificación</h2>
        <FormularioCalificacion
          :key="`nota-${fila.entrega.id}`"
          ref="formulario"
          :entrega="fila.entrega"
          :puntaje-maximo="tarea.puntaje_maximo"
          @calificada="alCalificar"
        />
      </section>
    </template>

    <VisorPdf
      v-if="visor"
      :titulo="visor.titulo"
      :nombre-archivo="visor.nombreArchivo"
      :cargar="visor.cargar"
      @cerrar="visor = null"
    />
  </section>
</template>

<style scoped>
.cabecera__estados {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.trabajo,
.calificacion {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.trabajo h2,
.calificacion h2,
.trabajo p {
  margin: 0;
}
</style>
