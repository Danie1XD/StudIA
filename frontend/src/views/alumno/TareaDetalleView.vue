<script setup>
import { computed, ref, watch } from 'vue'
import { entregasApi } from '@/api/entregas'
import { materiasApi } from '@/api/materias'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { entregadaTarde, ESTADO_CLASE, ESTADO_TEXTO, estadoDeTarea } from '@/utils/estados'
import { guardarArchivo, nombreDeArchivo } from '@/utils/descarga'
import { formatearFechaHora } from '@/utils/fechas'
import { tonoDeMateria } from '@/utils/colores'
import EntregaAlumno from '@/components/tareas/EntregaAlumno.vue'
import ForoTarea from '@/components/tareas/ForoTarea.vue'
import VisorPdf from '@/components/VisorPdf.vue'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const tarea = ref(null)
const materia = ref(null)
const entrega = ref(null)
const cargando = ref(true)
const error = ref('')
const errorDescarga = ref('')
const visor = ref(null)

const estado = computed(() =>
  tarea.value ? estadoDeTarea(tarea.value, entrega.value) : 'pendiente',
)
const conRetraso = computed(() => entregadaTarde(tarea.value, entrega.value))

// Generamos el tono dinámico de la materia para pintar la cabecera
const tono = computed(() => (materia.value ? tonoDeMateria(materia.value.id) : ''))

const rutaVolver = computed(() =>
  materia.value
    ? { name: 'alumno.materia', params: { id: materia.value.id } }
    : { name: 'alumno.tareas' },
)

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion 
  cargando.value = true
  error.value = ''
  errorDescarga.value = ''
  tarea.value = null
  materia.value = null
  entrega.value = null

  try {
    const [datosTarea, datosEntrega] = await Promise.all([
      tareasApi.mostrar(props.id),
      entregasApi.miEntrega(props.id),
    ])
    if (actual !== ultimaPeticion) return

    const datosMateria = await materiasApi.mostrar(datosTarea.materia_id).catch(() => null)
    if (actual !== ultimaPeticion) return

    tarea.value = datosTarea
    entrega.value = datosEntrega
    materia.value = datosMateria
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

function abrirPdf() {
  errorDescarga.value = ''
  visor.value = {
    titulo: nombreDeArchivo(tarea.value.titulo),
    nombreArchivo: nombreDeArchivo(tarea.value.titulo),
    cargar: () => tareasApi.descargarArchivo(tarea.value.id),
  }
}

async function descargarPdf() {
  errorDescarga.value = ''

  try {
    const archivo = await tareasApi.descargarArchivo(tarea.value.id)
    guardarArchivo(archivo, nombreDeArchivo(tarea.value.titulo))
  } catch (e) {
    errorDescarga.value = mensajeDeError(e)
  }
}

watch(() => props.id, cargar, { immediate: true })
</script>

<template>
  <section :class="['detalle', tono]">
    <RouterLink :to="rutaVolver" class="enlace-volver">
      ← {{ materia ? materia.nombre : 'Mis tareas' }}
    </RouterLink>

    <p v-if="cargando" class="suave">Cargando la tarea…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <template v-else-if="tarea">
      <div class="detalle__layout">
        
        <!-- Columna Izquierda -->
        <div class="detalle__principal">
          
          <!-- Nueva Cabecera tipo "Detalles de materia" -->
          <header class="banner">
            <div class="tarea-info__encabezado">
              <div class="tarea-info__icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              </div>
              <div class="tarea-info__titulos">
                <h1>{{ tarea.titulo }}</h1>
                <p v-if="materia" class="tarea-info__materia">{{ materia.nombre }}</p>
              </div>
            </div>

            <div class="banner__meta">
              <span v-if="materia?.docente">Por <strong>{{ materia.docente.nombre }}</strong></span>
              <span><strong>{{ tarea.puntaje_maximo }}</strong> puntos</span>
              <span>Límite: <strong>{{ tarea.fecha_limite ? formatearFechaHora(tarea.fecha_limite) : 'Sin fecha límite' }}</strong></span>
              <span v-if="tarea.parcial">Parcial {{ tarea.parcial }}</span>
            </div>
          </header>

          <!-- Caja de instrucciones separada -->
          <article class="tarjeta">
            <div class="tarea-info__contenido">
              <h2>Instrucciones</h2>
              <div class="instrucciones-caja mt-3">
                <p class="texto-libre">{{ tarea.instrucciones }}</p>
              </div>

              <p v-if="errorDescarga" class="alerta mt-3" role="alert">{{ errorDescarga }}</p>
              <div v-if="tarea.tiene_archivo" class="acciones acciones--izquierda mt-3">
                <button type="button" class="boton boton--secundario boton--chico" @click="abrirPdf">
                  Abrir el PDF de la tarea
                </button>
                <button type="button" class="boton boton--fantasma boton--chico" @click="descargarPdf">
                  Descargar
                </button>
              </div>
            </div>
          </article>

          <!-- La rúbrica solo llega si el docente la compartió -->
          <article v-if="tarea.rubrica" class="tarjeta rubrica" aria-labelledby="titulo-rubrica">
            <div class="rubrica__encabezado">
              <h2 id="titulo-rubrica">Rúbrica: {{ tarea.rubrica.nombre }}</h2>
              <span class="insignia">{{ tarea.rubrica.puntos_total }} pts</span>
            </div>
            <p v-if="tarea.rubrica.descripcion" class="suave rubrica__descripcion">
              {{ tarea.rubrica.descripcion }}
            </p>
            <ol class="rubrica__criterios">
              <li v-for="criterio in tarea.rubrica.criterios" :key="criterio.id">
                <div class="rubrica__criterio-cabecera">
                  <strong>{{ criterio.nombre }}</strong>
                  <span class="insignia insignia--aviso">{{ criterio.puntos }} pts</span>
                </div>
                <p v-if="criterio.descripcion" class="suave">{{ criterio.descripcion }}</p>
              </li>
            </ol>
          </article>
        </div>

        <!-- Columna Derecha -->
        <aside class="detalle__lateral">
          
          <!-- 
            Le pasamos el estado a tu componente para que él mismo 
            pueda pintar la etiqueta de "Calificada" adentro. 
          -->
          <EntregaAlumno
            :tarea="tarea"
            :entrega="entrega"
            :estado="estado"
            :con-retraso="conRetraso"
            @entregada="entrega = $event"
            @anulada="entrega = null"
          />

          <ForoTarea :tarea-id="tarea.id" />
        </aside>

      </div>
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
.detalle__principal {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  min-width: 0;
}

.detalle__lateral {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detalle__layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 1.5rem;
  align-items: start;
}

.tarea-info__encabezado {
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.tarea-info__icono {
  width: 54px;
  height: 54px;
  background: rgba(255, 255, 255, 0.15);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
}

.tarea-info__icono svg {
  width: 28px;
  height: 28px;
}

.tarea-info__materia {
  margin: 0.2rem 0 0;
  font-weight: 500;
}

.instrucciones-caja {
  background: var(--superficie-alta, rgba(255,255,255,0.02));
  border: 1px solid var(--borde, rgba(255,255,255,0.05));
  border-radius: 12px;
  padding: 1.25rem;
}

.texto-libre {
  margin: 0;
  white-space: pre-wrap;
  color: var(--texto-suave, #ddd);
  line-height: 1.6;
}

.mt-3 {
  margin-top: 1rem;
}

.rubrica__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.rubrica__encabezado h2 {
  margin: 0;
  font-size: 1.1rem;
}

.rubrica__descripcion {
  margin: 0 0 1rem;
}

.rubrica__criterios {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.rubrica__criterios li {
  padding: 0.85rem 1rem;
  border: 1px solid var(--borde);
  border-left: 4px solid var(--h);
  border-radius: var(--radio);
  background: var(--superficie-alta);
}

.rubrica__criterios p {
  margin: 0.35rem 0 0;
}

.rubrica__criterio-cabecera {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

@media (max-width: 1024px) {
  .detalle__layout {
    grid-template-columns: 1fr;
  }
}
</style>