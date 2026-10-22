<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { materiasApi } from '@/api/materias'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { useAuthStore } from '@/stores/auth'
import { iniciales } from '@/utils/inicio'
import {
  filtrarPorTexto,
  nombreParaSaludo,
  porRevisarDocente,
  resumenDocentePorMateria,
} from '@/utils/inicio'
import IconoNav from '@/components/IconoNav.vue'
import IlustracionAcceso from '@/components/IlustracionAcceso.vue'
import IlustracionBirrete from '@/components/IlustracionBirrete.vue'
import TarjetaClase from '@/components/inicio/TarjetaClase.vue'
import TutorIA from '@/components/inicio/TutorIA.vue'

const auth = useAuthStore()
const route = useRoute()

const materias = ref([])
const grupos = ref([])
// La búsqueda vive en la barra superior y llega por la dirección (?q=…)
const busqueda = computed(() => String(route.query.q ?? ''))
const cargando = ref(true)
const error = ref('')

const clases = computed(() =>
  filtrarPorTexto(resumenDocentePorMateria(materias.value, grupos.value), busqueda.value, [
    (c) => c.materia.nombre,
  ]),
)

const porRevisar = computed(() =>
  filtrarPorTexto(porRevisarDocente(grupos.value, 50), busqueda.value, [
    (t) => t.titulo,
    (t) => t.materia.nombre,
  ]).slice(0, 4),
)

function alumnos(cantidad) {
  return `${cantidad} ${cantidad === 1 ? 'alumno' : 'alumnos'}`
}

onMounted(async () => {
  try {
    const [listaMaterias, listaTareas] = await Promise.all([
      materiasApi.listar(),
      tareasApi.misTareas(),
    ])
    materias.value = listaMaterias
    grupos.value = listaTareas
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <div class="inicio">
    <section class="inicio__principal">
      <header class="bienvenida">
        <div class="bienvenida__texto">
          <span class="bienvenida__saludo">
            <span aria-hidden="true">{{ iniciales(auth.usuario.nombre) }}</span>
            ¡Hola, {{ nombreParaSaludo(auth.usuario) }}!
          </span>
          <h1>Panel de Docente</h1>
          <p>Revisa las entregas de tus alumnos, administra tus materias y apóyate en la IA para evaluar.</p>
        </div>
        <IlustracionBirrete class="bienvenida__ilustracion" />
      </header>

      <div class="accesos">
        <RouterLink :to="{ name: 'docente.materias' }" class="acceso tarjeta tono-0">
          <span class="acceso__icono"><IconoNav nombre="materias" /></span>
          <div>
            <h2>Mis Materias y Grupos</h2>
            <p>Gestiona tus materias, publica tareas y comparte el código de acceso con tus alumnos.</p>
          </div>
          <span class="boton acceso__ir">Ver Materias <IconoNav nombre="flecha" /></span>
          <IlustracionAcceso tipo="tareas" class="acceso__fondo" />
        </RouterLink>
        <RouterLink :to="{ name: 'docente.rubricas' }" class="acceso tarjeta tono-1">
          <span class="acceso__icono"><IconoNav nombre="rubricas" /></span>
          <div>
            <h2>Rúbricas de Evaluación</h2>
            <p>Define qué evalúa la IA, criterio por criterio, y reutilízalas en tus tareas.</p>
          </div>
          <span class="boton acceso__ir">Ver Rúbricas <IconoNav nombre="flecha" /></span>
          <IlustracionAcceso tipo="promedios" class="acceso__fondo" />
        </RouterLink>
      </div>

      <p v-if="busqueda" class="suave" role="status">
        Resultados para «{{ busqueda }}» ·
        <RouterLink :to="{ query: {} }">Quitar búsqueda</RouterLink>
      </p>

      <p v-if="cargando" class="suave">Cargando tu panel…</p>
      <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

      <template v-else>
        <div class="seccion-titulo">
          <h2>Mis clases</h2>
          <RouterLink :to="{ name: 'docente.materias' }">Gestionar</RouterLink>
        </div>

        <div v-if="!clases.length" class="tarjeta vacio">
          <h2>{{ busqueda ? 'Sin resultados' : 'Aún no tienes materias' }}</h2>
          <p class="suave">
            {{
              busqueda
                ? 'Prueba con otra búsqueda.'
                : 'Crea la primera desde «Mis materias» y comparte su código con tus alumnos.'
            }}
          </p>
        </div>

        <div v-else class="clases">
          <TarjetaClase
            v-for="clase in clases"
            :key="clase.materia.id"
            :to="{ name: 'docente.materia', params: { id: clase.materia.id } }"
            :materia-id="clase.materia.id"
            :titulo="clase.materia.nombre"
            :subtitulo="alumnos(clase.materia.alumnos_count ?? 0)"
            :insignia="clase.porRevisar ? `${clase.porRevisar} por revisar` : ''"
            :progreso="clase.porcentaje"
            etiqueta="Calificado"
            vacio="Sin entregas aún"
          />
        </div>
      </template>
    </section>

    <aside class="inicio__lateral" aria-label="Tutor IA y entregas por revisar">
      <TutorIA />

      <section>
        <div class="seccion-titulo">
          <h2>Por revisar</h2>
          <RouterLink :to="{ name: 'docente.tareas' }">Ver todas</RouterLink>
        </div>

        <ul v-if="porRevisar.length" class="lista-lateral">
          <li v-for="tarea in porRevisar" :key="tarea.id">
            <RouterLink
              class="item-lateral tarjeta"
              :to="{ name: 'docente.tarea', params: { id: tarea.id } }"
            >
              <span class="item-lateral__icono"><IconoNav nombre="entregas" /></span>
              <span class="item-lateral__texto">
                <strong>{{ tarea.titulo }}</strong>
                <small>{{ tarea.materia.nombre }} · {{ tarea.por_revisar }} por revisar</small>
              </span>
            </RouterLink>
          </li>
        </ul>
        <p v-else-if="!cargando" class="suave">
          {{
            busqueda ? 'Ninguna tarea coincide con tu búsqueda.' : 'No tienes entregas por revisar.'
          }}
          <span aria-hidden="true">🎉</span>
        </p>
      </section>
    </aside>
  </div>
</template>
