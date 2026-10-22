<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { materiasApi } from '@/api/materias'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { useAuthStore } from '@/stores/auth'
import { iniciales } from '@/utils/inicio'
import { formatearFechaHora } from '@/utils/fechas'
import {
  filtrarPorTexto,
  nombreParaSaludo,
  progresoPorMateria,
  proximasTareas,
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
  filtrarPorTexto(progresoPorMateria(materias.value, grupos.value), busqueda.value, [
    (c) => c.materia.nombre,
  ]),
)

const proximas = computed(() =>
  filtrarPorTexto(proximasTareas(grupos.value, 50), busqueda.value, [
    (t) => t.titulo,
    (t) => t.materia.nombre,
  ]).slice(0, 4),
)

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
          <h1>Panel de Alumno</h1>
          <p>Aquí encontrarás tus materias, tareas, calificaciones y toda la información importante de tu trayectoria académica.</p>
        </div>
        <IlustracionBirrete class="bienvenida__ilustracion" />
      </header>

      <div class="accesos">
        <RouterLink :to="{ name: 'alumno.materias' }" class="acceso tarjeta tono-0">
          <span class="acceso__icono"><IconoNav nombre="materias" /></span>
          <div>
            <h2>Mis Materias y Tareas</h2>
            <p>Revisa el listado de asignaturas activas y accede a tus tareas y actividades.</p>
          </div>
          <span class="boton acceso__ir">Ver Materias <IconoNav nombre="flecha" /></span>
          <IlustracionAcceso tipo="tareas" class="acceso__fondo" />
        </RouterLink>
        <RouterLink :to="{ name: 'alumno.perfil' }" class="acceso tarjeta tono-1">
          <span class="acceso__icono"><IconoNav nombre="perfil" /></span>
          <div>
            <h2>Mi Perfil Académico</h2>
            <p>Consulta tu información personal y el estado de cuenta dentro del sistema.</p>
          </div>
          <span class="boton acceso__ir">Ver Perfil <IconoNav nombre="flecha" /></span>
          <IlustracionAcceso tipo="perfil" class="acceso__fondo" />
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
          <RouterLink :to="{ name: 'alumno.materias' }">Ver todas</RouterLink>
        </div>

        <div v-if="!clases.length" class="tarjeta vacio">
          <h2>{{ busqueda ? 'Sin resultados' : 'Aún no estás en ninguna materia' }}</h2>
          <p class="suave">
            {{
              busqueda
                ? 'Prueba con otra búsqueda.'
                : 'Pide el código a tu docente y úsalo en «Mis materias».'
            }}
          </p>
        </div>

        <div v-else class="clases">
          <TarjetaClase
            v-for="clase in clases"
            :key="clase.materia.id"
            :to="{ name: 'alumno.materia', params: { id: clase.materia.id } }"
            :materia-id="clase.materia.id"
            :titulo="clase.materia.nombre"
            :subtitulo="clase.materia.docente ? `Prof. ${clase.materia.docente.nombre}` : ''"
            :progreso="clase.porcentaje"
          />
        </div>
      </template>
    </section>

    <aside class="inicio__lateral" aria-label="Tutor IA y próximas tareas">
      <TutorIA />

      <section>
        <div class="seccion-titulo">
          <h2>Próximas tareas</h2>
          <RouterLink :to="{ name: 'alumno.tareas' }">Ver todas</RouterLink>
        </div>

        <ul v-if="proximas.length" class="lista-lateral">
          <li v-for="tarea in proximas" :key="tarea.id">
            <RouterLink
              class="item-lateral tarjeta"
              :to="{ name: 'alumno.tarea', params: { id: tarea.id } }"
            >
              <span class="item-lateral__icono"><IconoNav nombre="calendario" /></span>
              <span class="item-lateral__texto">
                <strong>{{ tarea.titulo }}</strong>
                <small
                  >{{ tarea.materia.nombre }} · {{ formatearFechaHora(tarea.fecha_limite) }}</small
                >
              </span>
            </RouterLink>
          </li>
        </ul>
        <p v-else-if="!cargando" class="suave">
          {{
            busqueda ? 'Ninguna tarea coincide con tu búsqueda.' : 'No tienes tareas pendientes.'
          }}
          <span aria-hidden="true">🎉</span>
        </p>
      </section>
    </aside>
  </div>
</template>
