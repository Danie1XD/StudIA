<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import {
  contarPorEstado,
  entregadaTarde,
  ESTADO_CLASE,
  ESTADO_TEXTO,
  ESTADOS,
  FILTRO_TEXTO,
  formatearPuntos,
  prepararGrupos,
} from '@/utils/estados'
import { formatearFechaHora } from '@/utils/fechas'
import { tonoDeMateria } from '@/utils/colores'

const route = useRoute()
const router = useRouter()

const grupos = ref([])
const cargando = ref(true)
const error = ref('')

// El filtro vive en la URL: F5 y el botón "atrás" lo conservan
const filtro = computed(() => (ESTADOS.includes(route.query.estado) ? route.query.estado : ''))
const conteos = computed(() => contarPorEstado(grupos.value))
const total = computed(() => ESTADOS.reduce((suma, estado) => suma + conteos.value[estado], 0))
const visibles = computed(() => prepararGrupos(grupos.value, filtro.value))
const cantidadVisible = computed(() => visibles.value.reduce((s, g) => s + g.tareas.length, 0))

const opciones = computed(() => [
  { valor: '', texto: 'Todas', cantidad: total.value },
  ...ESTADOS.map((estado) => ({
    valor: estado,
    texto: FILTRO_TEXTO[estado],
    cantidad: conteos.value[estado],
  })),
])

const resumen = computed(() =>
  cantidadVisible.value === 1 ? 'Mostrando 1 tarea.' : `Mostrando ${cantidadVisible.value} tareas.`,
)

function elegir(valor) {
  router.replace({ query: valor ? { estado: valor } : {} })
}

onMounted(async () => {
  try {
    grupos.value = await tareasApi.misTareas()
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <section>
    <header class="encabezado">
      <div>
        <h1>Mis tareas</h1>
        <p class="suave">Todas tus actividades, agrupadas por materia.</p>
      </div>
    </header>

    <p v-if="cargando" class="suave">Cargando tus tareas…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="!total" class="tarjeta vacio">
      <h2>Aún no tienes tareas</h2>
      <p class="suave">Cuando tus docentes publiquen tareas, aparecerán aquí.</p>
    </div>

    <template v-else>
      <div class="filtros" role="group" aria-label="Filtrar por estado">
        <button
          v-for="opcion in opciones"
          :key="opcion.valor"
          type="button"
          class="boton boton--secundario boton--chico"
          :aria-pressed="filtro === opcion.valor ? 'true' : 'false'"
          @click="elegir(opcion.valor)"
        >
          {{ opcion.texto }} ({{ opcion.cantidad }})
        </button>
      </div>

      <p class="solo-lectores" role="status">{{ resumen }}</p>

      <p v-if="!visibles.length" class="suave">No tienes tareas con este estado.</p>

      <section v-for="grupo in visibles" :key="grupo.materia.id" :class="['materia-grupo', tonoDeMateria(grupo.materia.id)]">
        <h2 class="materia-grupo__titulo">
          <RouterLink :to="{ name: 'alumno.materia', params: { id: grupo.materia.id } }">
            {{ grupo.materia.nombre }}
          </RouterLink>
          <span class="suave">{{ grupo.tareas.length }}</span>
        </h2>

        <ul class="lista-tareas">
          <li v-for="tarea in grupo.tareas" :key="tarea.id" class="tarjeta fila-tarea">
            <div class="fila-tarea__principal">
              <RouterLink
                class="fila-tarea__titulo"
                :to="{ name: 'alumno.tarea', params: { id: tarea.id } }"
              >
                {{ tarea.titulo }}
              </RouterLink>
              <p class="fila-tarea__meta suave">
                <span>
                  {{
                    tarea.fecha_limite
                      ? `Entrega: ${formatearFechaHora(tarea.fecha_limite)}`
                      : 'Sin fecha límite'
                  }}
                </span>
                <span>Parcial {{ tarea.parcial }}</span>
              </p>
            </div>

            <div class="fila-tarea__estado">
              <span :class="['insignia', ESTADO_CLASE[tarea.estado]]">
                {{ ESTADO_TEXTO[tarea.estado] }}
              </span>
              <span v-if="entregadaTarde(tarea, tarea.entrega)" class="insignia insignia--aviso">
                Con retraso
              </span>
              <strong v-if="tarea.estado === 'calificada'" class="fila-tarea__nota">
                {{ formatearPuntos(tarea.entrega?.calificacion_final) }} /
                {{ tarea.puntaje_maximo }}
              </strong>
              <span v-else class="suave">{{ tarea.puntaje_maximo }} pts</span>
            </div>
          </li>
        </ul>
      </section>
    </template>
  </section>
</template>
