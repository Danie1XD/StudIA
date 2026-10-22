<script setup>
import { computed, onMounted, ref } from 'vue'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { formatearFechaHora } from '@/utils/fechas'
import { tonoDeMateria } from '@/utils/colores'
import { prepararGruposDocente } from '@/utils/revision'

const grupos = ref([])
const cargando = ref(true)
const error = ref('')

const visibles = computed(() => prepararGruposDocente(grupos.value))
const porRevisar = computed(() =>
  visibles.value.reduce(
    (suma, grupo) => suma + grupo.tareas.reduce((s, tarea) => s + tarea.por_revisar, 0),
    0,
  ),
)

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
        <h1>Entregas</h1>
        <p class="suave">Revisa y califica el trabajo de tus alumnos, tarea por tarea.</p>
      </div>
    </header>

    <p v-if="cargando" class="suave">Cargando tus tareas…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="!visibles.length" class="tarjeta vacio">
      <h2>Aún no tienes tareas</h2>
      <p class="suave">Crea una desde el detalle de una de tus materias.</p>
    </div>

    <template v-else>
      <p v-if="porRevisar" class="alerta alerta--aviso" role="status">
        Tienes {{ porRevisar }} {{ porRevisar === 1 ? 'entrega' : 'entregas' }} por revisar.
      </p>
      <p v-else class="suave" role="status">No tienes entregas por revisar.</p>

      <section v-for="grupo in visibles" :key="grupo.materia.id" :class="['materia-grupo', tonoDeMateria(grupo.materia.id)]">
        <h2 class="materia-grupo__titulo">
          <RouterLink :to="{ name: 'docente.materia', params: { id: grupo.materia.id } }">
            {{ grupo.materia.nombre }}
          </RouterLink>
          <span class="suave">{{ grupo.tareas.length }}</span>
        </h2>

        <ul class="lista-tareas">
          <li v-for="tarea in grupo.tareas" :key="tarea.id" class="tarjeta fila-tarea">
            <div class="fila-tarea__principal">
              <RouterLink
                class="fila-tarea__titulo"
                :to="{ name: 'docente.tarea', params: { id: tarea.id } }"
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
                <span>{{ tarea.puntaje_maximo }} puntos</span>
              </p>
            </div>

            <div class="fila-tarea__estado">
              <span v-if="tarea.por_revisar" class="insignia insignia--aviso">
                {{ tarea.por_revisar }} por revisar
              </span>
              <span class="insignia">
                {{ tarea.entregas_count }} {{ tarea.entregas_count === 1 ? 'entrega' : 'entregas' }}
              </span>
              <span v-if="tarea.calificadas_count" class="insignia insignia--exito">
                {{ tarea.calificadas_count }}
                {{ tarea.calificadas_count === 1 ? 'calificada' : 'calificadas' }}
              </span>
            </div>
          </li>
        </ul>
      </section>
    </template>
  </section>
</template>
