<script setup>
import { computed, ref, watch } from 'vue'
import { entregasApi } from '@/api/entregas'
import { materiasApi } from '@/api/materias'
import { rubricasApi } from '@/api/rubricas'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { tonoDeMateria } from '@/utils/colores'
import { formatearPuntos } from '@/utils/estados'
import { formatearFechaHora } from '@/utils/fechas'
import { TIPOS_EVALUACION } from '@/utils/formularioTarea'
import { infoEstadoFila, ordenarFilas, resumirFilas } from '@/utils/revision'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const tarea = ref(null)
const materia = ref(null)
const rubricaNombre = ref('')
const filas = ref([])
const cargando = ref(true)
const error = ref('')

const tono = computed(() => (tarea.value ? tonoDeMateria(tarea.value.materia_id) : ''))
const ordenadas = computed(() => ordenarFilas(filas.value))
const resumen = computed(() => resumirFilas(filas.value))
const tipoTexto = computed(
  () => TIPOS_EVALUACION.find((t) => t.valor === tarea.value?.tipo_evaluacion)?.texto ?? '',
)

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion // si el usuario cambia de tarea rápido, se ignora la respuesta vieja
  cargando.value = true
  error.value = ''
  tarea.value = null
  materia.value = null
  rubricaNombre.value = ''
  filas.value = []

  try {
    const [datosTarea, datosFilas] = await Promise.all([
      tareasApi.mostrar(props.id),
      entregasApi.listarDeTarea(props.id),
    ])
    if (actual !== ultimaPeticion) return

    // Estos dos datos solo adornan la página: si fallan, la página sigue funcionando
    const [datosMateria, rubricas] = await Promise.all([
      materiasApi.mostrar(datosTarea.materia_id).catch(() => null),
      datosTarea.rubrica_id ? rubricasApi.listar().catch(() => []) : Promise.resolve([]),
    ])
    if (actual !== ultimaPeticion) return

    tarea.value = datosTarea
    filas.value = datosFilas
    materia.value = datosMateria
    rubricaNombre.value = rubricas.find((r) => r.id === datosTarea.rubrica_id)?.nombre ?? ''
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

watch(() => props.id, cargar, { immediate: true })
</script>

<template>
  <section :class="tono">
    <RouterLink :to="{ name: 'docente.tareas' }" class="enlace-volver">← Entregas</RouterLink>

    <p v-if="cargando" class="suave">Cargando la tarea…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <template v-else-if="tarea">
      <header class="banner">
        <h1>{{ tarea.titulo }}</h1>
        <p v-if="materia">
          <RouterLink :to="{ name: 'docente.materia', params: { id: materia.id } }">
            {{ materia.nombre }}
          </RouterLink>
        </p>
        <p class="banner__meta">
          <span>
            {{
              tarea.fecha_limite
                ? `Entrega: ${formatearFechaHora(tarea.fecha_limite)}`
                : 'Sin fecha límite'
            }}
          </span>
          <span>{{ tarea.puntaje_maximo }} puntos</span>
          <span>Parcial {{ tarea.parcial }}</span>
          <span v-if="tipoTexto">Tipo: {{ tipoTexto }}</span>
          <span>{{ rubricaNombre ? `Rúbrica: ${rubricaNombre}` : 'Sin rúbrica' }}</span>
          <span v-if="rubricaNombre">
            {{ tarea.rubrica_visible ? 'Compartida con los alumnos' : 'No compartida con los alumnos' }}
          </span>
        </p>
        <details>
          <summary>Ver instrucciones</summary>
          <p class="texto-libre">{{ tarea.instrucciones }}</p>
        </details>
      </header>

      <p v-if="!filas.length" class="suave aviso">Aún no hay alumnos inscritos en esta materia.</p>

      <template v-else>
        <p class="resumen" role="status">
          <span class="insignia insignia--aviso">{{ resumen.porRevisar }} por revisar</span>
          <span class="insignia insignia--exito">{{ resumen.calificadas }} calificadas</span>
          <span class="insignia">{{ resumen.sinEntregar }} sin entregar</span>
          <span class="suave">de {{ resumen.total }} alumnos</span>
        </p>

        <div class="tabla-envoltura">
          <table class="tabla">
            <caption class="solo-lectores">
              Entregas de
              {{
                tarea.titulo
              }}
            </caption>
            <thead>
              <tr>
                <th scope="col">Alumno</th>
                <th scope="col">Estado</th>
                <th scope="col">Entregó</th>
                <th scope="col">Calificación</th>
                <th scope="col"><span class="solo-lectores">Acciones</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fila in ordenadas" :key="fila.alumno.id">
                <th scope="row">
                  {{ fila.alumno.nombre }}
                  <small class="suave alumno-correo">{{ fila.alumno.email }}</small>
                </th>
                <td>
                  <span :class="['insignia', infoEstadoFila(fila.estado).clase]">
                    {{ infoEstadoFila(fila.estado).texto }}
                  </span>
                  <span v-if="fila.entrega?.entregada_tarde" class="insignia insignia--aviso">
                    Con retraso
                  </span>
                  <span v-if="fila.entrega?.tiene_evaluacion_ia" class="insignia">IA</span>
                </td>
                <td>{{ fila.entrega ? formatearFechaHora(fila.entrega.entregada_at) : '—' }}</td>
                <td>
                  {{
                    fila.estado === 'calificado'
                      ? `${formatearPuntos(fila.entrega.calificacion_final)} / ${tarea.puntaje_maximo}`
                      : '—'
                  }}
                </td>
                <td>
                  <RouterLink
                    v-if="fila.entrega"
                    class="boton boton--secundario boton--chico"
                    :to="{
                      name: 'docente.entrega',
                      params: { id: tarea.id, entregaId: fila.entrega.id },
                    }"
                    :aria-label="`${fila.estado === 'calificado' ? 'Ver' : 'Revisar'} la entrega de ${fila.alumno.nombre}`"
                  >
                    {{ fila.estado === 'calificado' ? 'Ver' : 'Revisar' }}
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </template>
  </section>
</template>

<style scoped>
.aviso {
  margin-top: 1rem;
}

.resumen {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin: 1.25rem 0 0.75rem;
}

.alumno-correo {
  display: block;
  font-weight: 400;
  overflow-wrap: anywhere;
}
</style>
