<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { anunciosApi } from '@/api/anuncios'
import { materiasApi } from '@/api/materias'
import { rubricasApi } from '@/api/rubricas'
import { seccionesApi } from '@/api/secciones'
import { tareasApi } from '@/api/tareas'
import { mensajeDeError } from '@/api/errores'
import { useAuthStore } from '@/stores/auth'
import { tonoDeMateria } from '@/utils/colores'
import { agruparContenido } from '@/utils/contenido'
import { alternarOculto, guardarOcultos, leerOcultos, mostrar } from '@/utils/colapsables'
import { guardarArchivo, nombreDeArchivo } from '@/utils/descarga'
import CodigoAcceso from '@/components/materias/CodigoAcceso.vue'
import ElementoClase from '@/components/materias/ElementoClase.vue'
import ModalAnuncio from '@/components/materias/ModalAnuncio.vue'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'
import ModalSeccion from '@/components/materias/ModalSeccion.vue'
import ModalTarea from '@/components/materias/ModalTarea.vue'
import VisorPdf from '@/components/VisorPdf.vue'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const auth = useAuthStore()
const esDocente = computed(() => auth.rol === 'docente')
const tono = computed(() => tonoDeMateria(props.id))
const rutaLista = computed(() => ({
  name: esDocente.value ? 'docente.materias' : 'alumno.materias',
}))

const materia = ref(null)
const contenido = ref(null)
const rubricas = ref([])
const cargando = ref(true)
const error = ref('')
const aviso = ref('')
const errorAccion = ref('')

const secciones = computed(() => contenido.value?.secciones ?? [])
// El alumno no ve secciones vacías; el docente sí, para poder llenarlas y gestionarlas
const grupos = computed(() => agruparContenido(contenido.value, { incluirVacias: esDocente.value }))

// Extraemos dinámicamente las tareas para el panel lateral
const proximasTareas = computed(() => {
  const tareas = []
  for (const g of grupos.value) {
    for (const item of g.items) {
      if (item.tipo === 'tarea') tareas.push(item)
    }
  }
  return tareas.slice(0, 3) // Limitamos a 3
})

// Extraemos dinámicamente los anuncios para el panel lateral debajo de las tareas
const anunciosRecientes = computed(() => {
  const anuncios = []
  for (const g of grupos.value) {
    for (const item of g.items) {
      if (item.tipo === 'anuncio') anuncios.push(item)
    }
  }
  return anuncios.slice(0, 3) // Limitamos a 3 para no saturar
})

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion
  cargando.value = true
  error.value = ''
  aviso.value = ''
  errorAccion.value = ''
  materia.value = null
  contenido.value = null
  rubricas.value = []

  try {
    const [datosMateria, datosContenido, datosRubricas] = await Promise.all([
      materiasApi.mostrar(props.id),
      materiasApi.contenido(props.id),
      esDocente.value ? rubricasApi.listar().catch(() => []) : Promise.resolve([]),
    ])
    if (actual !== ultimaPeticion) return
    materia.value = datosMateria
    contenido.value = datosContenido
    rubricas.value = datosRubricas
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

async function recargarContenido() {
  const actual = ++ultimaPeticion
  try {
    const datos = await materiasApi.contenido(props.id)
    if (actual === ultimaPeticion) contenido.value = datos
  } catch (e) {
    if (actual === ultimaPeticion) errorAccion.value = mensajeDeError(e)
  }
}

watch(() => props.id, cargar, { immediate: true })

// ---------- Mostrar y ocultar secciones ----------
const ocultos = ref(new Set())

watch(
  () => props.id,
  (id) => {
    ocultos.value = leerOcultos(id)
  },
  { immediate: true },
)

const hayVariosGrupos = computed(() => grupos.value.length > 1)
const todoOculto = computed(
  () => grupos.value.length > 0 && grupos.value.every((g) => ocultos.value.has(g.clave)),
)

function estaAbierto(grupo) {
  return !ocultos.value.has(grupo.clave)
}

function fijarOcultos(nuevos) {
  ocultos.value = nuevos
  guardarOcultos(props.id, nuevos)
}

function alternarGrupo(grupo) {
  fijarOcultos(alternarOculto(ocultos.value, grupo.clave))
}

function alternarTodo() {
  fijarOcultos(todoOculto.value ? new Set() : new Set(grupos.value.map((g) => g.clave)))
}

// ---------- Crear y editar ----------
const modal = ref(null)

const MENSAJES_GUARDADO = {
  seccion: ['La sección se creó.', 'La sección se renombró.'],
  anuncio: ['El anuncio se publicó.', 'El anuncio se actualizó.'],
  tarea: ['La tarea se publicó.', 'La tarea se actualizó.'],
}

function abrirModal(tipo, elemento = null, seccionId = '') {
  aviso.value = ''
  errorAccion.value = ''
  // Si se va a agregar algo a una sección oculta, se abre para que se vea lo nuevo
  if (seccionId) fijarOcultos(mostrar(ocultos.value, `seccion-${seccionId}`))
  modal.value = { tipo, elemento, seccionId }
}

function cerrarModal() {
  modal.value = null
}

async function alGuardar(editando) {
  aviso.value = MENSAJES_GUARDADO[modal.value.tipo][editando ? 1 : 0]
  cerrarModal()
  await recargarContenido()
}

// ---------- Eliminar ----------
const eliminacion = ref(null)
const eliminando = ref(false)
const errorEliminar = ref('')

const ELIMINAR = {
  seccion: {
    titulo: 'Eliminar sección',
    llamar: (e) => seccionesApi.eliminar(e.seccionId),
    hecho: (e) => `Se eliminó la sección «${e.nombre}». Su contenido pasó a General.`,
  },
  anuncio: {
    titulo: 'Eliminar anuncio',
    llamar: (e) => anunciosApi.eliminar(e.id),
    hecho: () => 'Se eliminó el anuncio.',
  },
  tarea: {
    titulo: 'Eliminar tarea',
    llamar: (e) => tareasApi.eliminar(e.id),
    hecho: (e) => `Se eliminó la tarea «${e.titulo}».`,
  },
}

const textoEliminacion = computed(() => {
  if (!eliminacion.value) return ''
  const { tipo, elemento } = eliminacion.value

  if (tipo === 'seccion') {
    return `¿Eliminar la sección «${elemento.nombre}»? Su contenido no se borra: pasará a «General».`
  }
  if (tipo === 'anuncio') return '¿Eliminar este anuncio? Esta acción no se puede deshacer.'
  return `¿Eliminar la tarea «${elemento.titulo}»? Se borrarán también las entregas de los alumnos, sus comentarios y los archivos. Esta acción no se puede deshacer.`
})

function pedirEliminar(tipo, elemento) {
  aviso.value = ''
  errorAccion.value = ''
  errorEliminar.value = ''
  eliminacion.value = { tipo, elemento }
}

async function confirmarEliminar() {
  const { tipo, elemento } = eliminacion.value
  eliminando.value = true
  errorEliminar.value = ''

  try {
    await ELIMINAR[tipo].llamar(elemento)
    aviso.value = ELIMINAR[tipo].hecho(elemento)
    eliminacion.value = null
    await recargarContenido()
  } catch (e) {
    errorEliminar.value = mensajeDeError(e)
  } finally {
    eliminando.value = false
  }
}

// ---------- Reordenar secciones ----------
const reordenando = ref(false)

function puedeMover(grupo, delta) {
  const i = secciones.value.findIndex((s) => s.id === grupo.seccionId)
  const j = i + delta
  return i !== -1 && j >= 0 && j < secciones.value.length
}

async function mover(grupo, delta) {
  if (!puedeMover(grupo, delta) || reordenando.value) return

  aviso.value = ''
  errorAccion.value = ''

  const lista = [...secciones.value]
  const i = lista.findIndex((s) => s.id === grupo.seccionId)
  ;[lista[i], lista[i + delta]] = [lista[i + delta], lista[i]]
  contenido.value = { ...contenido.value, secciones: lista }

  reordenando.value = true
  try {
    await seccionesApi.reordenar(
      props.id,
      lista.map((s) => s.id),
    )
  } catch (e) {
    errorAccion.value = mensajeDeError(e)
    await recargarContenido()
  } finally {
    reordenando.value = false
  }

  await nextTick()
  const mismo = document.getElementById(`${delta < 0 ? 'subir' : 'bajar'}-${grupo.seccionId}`)
  const otro = document.getElementById(`${delta < 0 ? 'bajar' : 'subir'}-${grupo.seccionId}`)
  ;(mismo && !mismo.disabled ? mismo : otro)?.focus()
}

// ---------- PDF de la tarea ----------
const visor = ref(null)

function abrirPdf(tarea) {
  errorAccion.value = ''
  visor.value = {
    titulo: nombreDeArchivo(tarea.titulo),
    nombreArchivo: nombreDeArchivo(tarea.titulo),
    cargar: () => tareasApi.descargarArchivo(tarea.id),
  }
}

async function descargar(tarea) {
  errorAccion.value = ''
  try {
    const archivo = await tareasApi.descargarArchivo(tarea.id)
    guardarArchivo(archivo, nombreDeArchivo(tarea.titulo))
  } catch (e) {
    errorAccion.value = mensajeDeError(e)
  }
}
</script>

<template>
  <section :class="tono">
    <RouterLink :to="rutaLista" class="enlace-volver">← Mis materias</RouterLink>

    <p v-if="cargando" class="suave">Cargando la materia…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <template v-else-if="materia">
      <header class="banner">
        <h1>{{ materia.nombre }}</h1>
        <p v-if="materia.descripcion" class="banner__descripcion">{{ materia.descripcion }}</p>
        <p class="banner__meta">
          <span>Prof. {{ materia.docente?.nombre }}</span>
          <span>{{ materia.alumnos_count }} alumno(s)</span>
        </p>
        <CodigoAcceso v-if="materia.codigo_acceso" :codigo="materia.codigo_acceso" />
      </header>

      <!-- Layout de dos columnas -->
      <div class="layout-columnas">
        
        <!-- Columna Izquierda: Panel de Tareas y Anuncios -->
        <aside class="columna-lateral">
          
          <!-- Bloque de Próximas Entregas -->
          <div class="tarjeta-lateral">
            <h3>Próximas entregas</h3>
            <ul v-if="proximasTareas.length" class="lista-lateral">
              <li v-for="tarea in proximasTareas" :key="tarea.id">
                <p class="item-titulo">{{ tarea.titulo }}</p>
                <p class="item-meta suave">Próximamente</p>
              </li>
            </ul>
            <p v-else class="suave text-sm">No hay entregas pendientes.</p>
          </div>

          <!-- Bloque de Anuncios (Nuevo) -->
          <div class="tarjeta-lateral mt-4">
            <h3>Anuncios</h3>
            <ul v-if="anunciosRecientes.length" class="lista-lateral">
              <li v-for="anuncio in anunciosRecientes" :key="anuncio.id">
                <!-- Se usa titulo o contenido dependiendo de la base de datos de StudIA -->
                <p class="item-titulo">{{ anuncio.titulo || anuncio.contenido || 'Aviso' }}</p>
                <p class="item-meta suave">Reciente</p>
              </li>
            </ul>
            <p v-else class="suave text-sm">No hay anuncios.</p>
          </div>

        </aside>

        <!-- Columna Derecha: Feed Principal y Acciones -->
        <div class="columna-principal">
          <div v-if="esDocente" class="barra acciones">
            <button type="button" class="boton" @click="abrirModal('tarea')">Nueva tarea</button>
            <button type="button" class="boton boton--secundario" @click="abrirModal('anuncio')">Nuevo anuncio</button>
            <button type="button" class="boton boton--secundario" @click="abrirModal('seccion')">Nueva sección</button>
            <RouterLink :to="{ name: 'docente.promedios', params: { id } }" class="boton boton--secundario">
              Promedios del grupo
            </RouterLink>
          </div>

          <p v-if="aviso" class="alerta alerta--exito aviso" role="status">{{ aviso }}</p>
          <p v-if="errorAccion" class="alerta aviso" role="alert">{{ errorAccion }}</p>

          <p v-if="!grupos.length" class="suave aviso">
            {{
              esDocente
                ? 'Esta materia aún no tiene contenido. Empieza con una tarea, un anuncio o una sección.'
                : 'Esta materia aún no tiene contenido.'
            }}
          </p>

          <div v-if="hayVariosGrupos" class="barra-secciones">
            <button type="button" class="boton boton--fantasma boton--chico" @click="alternarTodo">
              {{ todoOculto ? 'Mostrar todas las secciones' : 'Ocultar todas las secciones' }}
            </button>
          </div>

          <section v-for="grupo in grupos" :key="grupo.clave" class="grupo">
            <header class="grupo__encabezado">
              <h2>
                <button
                  type="button"
                  class="grupo__alternar"
                  :aria-expanded="estaAbierto(grupo)"
                  :aria-controls="`cuerpo-${grupo.clave}`"
                  @click="alternarGrupo(grupo)"
                >
                  <svg
                    class="grupo__flecha"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                  >
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                  <span>{{ grupo.nombre }}</span>
                  <span class="grupo__cuenta">{{ grupo.items.length }}</span>
                </button>
              </h2>

              <div v-if="esDocente && grupo.seccionId" class="grupo__acciones">
                <button
                  :id="`subir-${grupo.seccionId}`"
                  type="button"
                  class="boton boton--fantasma boton--chico"
                  :aria-label="`Subir la sección ${grupo.nombre}`"
                  :disabled="!puedeMover(grupo, -1) || reordenando"
                  @click="mover(grupo, -1)"
                >↑</button>
                <button
                  :id="`bajar-${grupo.seccionId}`"
                  type="button"
                  class="boton boton--fantasma boton--chico"
                  :aria-label="`Bajar la sección ${grupo.nombre}`"
                  :disabled="!puedeMover(grupo, 1) || reordenando"
                  @click="mover(grupo, 1)"
                >↓</button>
                <button type="button" class="boton boton--secundario boton--chico" @click="abrirModal('tarea', null, grupo.seccionId)">+ Tarea</button>
                <button type="button" class="boton boton--secundario boton--chico" @click="abrirModal('anuncio', null, grupo.seccionId)">+ Anuncio</button>
                <button type="button" class="boton boton--secundario boton--chico" @click="abrirModal('seccion', { id: grupo.seccionId, nombre: grupo.nombre })">Renombrar</button>
                <button type="button" class="boton boton--peligro boton--chico" @click="pedirEliminar('seccion', { seccionId: grupo.seccionId, nombre: grupo.nombre })">Eliminar</button>
              </div>
            </header>

            <div v-show="estaAbierto(grupo)" :id="`cuerpo-${grupo.clave}`" class="grupo__cuerpo">
            <p v-if="!grupo.items.length" class="suave">Aún no hay contenido en esta sección.</p>
            <ul v-else class="items">
              <ElementoClase
                v-for="item in grupo.items"
                :key="item.clave"
                :item="item"
                :gestionable="esDocente"
                :enlace="item.tipo === 'tarea' ? { name: esDocente ? 'docente.tarea' : 'alumno.tarea', params: { id: item.id } } : null"
                @editar="abrirModal(item.tipo, item)"
                @eliminar="pedirEliminar(item.tipo, item)"
                @descargar="descargar"
                @abrir="abrirPdf"
              />
            </ul>
            </div>
          </section>
        </div>
      </div>
    </template>

    <ModalSeccion v-if="modal?.tipo === 'seccion'" :materia-id="id" :seccion="modal.elemento" @guardado="alGuardar" @cerrar="cerrarModal" />
    <ModalAnuncio v-if="modal?.tipo === 'anuncio'" :materia-id="id" :anuncio="modal.elemento" :secciones="secciones" :seccion-inicial="modal.seccionId" @guardado="alGuardar" @cerrar="cerrarModal" />
    <ModalTarea v-if="modal?.tipo === 'tarea'" :materia-id="id" :tarea="modal.elemento" :secciones="secciones" :rubricas="rubricas" :seccion-inicial="modal.seccionId" @guardado="alGuardar" @cerrar="cerrarModal" />

    <VisorPdf
      v-if="visor"
      :titulo="visor.titulo"
      :nombre-archivo="visor.nombreArchivo"
      :cargar="visor.cargar"
      @cerrar="visor = null"
    />

    <ModalConfirmacion
      v-if="eliminacion"
      :titulo="ELIMINAR[eliminacion.tipo].titulo"
      texto-confirmar="Eliminar"
      peligro
      :cargando="eliminando"
      :error="errorEliminar"
      @confirmar="confirmarEliminar"
      @cerrar="eliminacion = null"
    >
      <p>{{ textoEliminacion }}</p>
    </ModalConfirmacion>
  </section>
</template>

<style scoped>
/* Layout de dos columnas: panel lateral + contenido */
.layout-columnas {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.5rem;
  margin-top: 1.5rem;
  align-items: start;
}

/* Clases unificadas para las tarjetas laterales (Tareas y Anuncios) */
.tarjeta-lateral {
  background: var(--superficie);
  border: 1px solid var(--borde, rgba(255,255,255,0.05));
  border-radius: 16px;
  padding: 1.25rem;
}

.tarjeta-lateral h3 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  color: var(--texto);
}

.lista-lateral {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.lista-lateral li {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding-left: 0.5rem;
  border-left: 2px solid var(--h);
}

.item-titulo {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 500;
  color: var(--texto);
  /* Truncar texto largo a 2 líneas */
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.item-meta {
  margin: 0;
  font-size: 0.8rem;
}

.mt-4 {
  margin-top: 1.5rem;
}

/* Ajustes de componentes existentes en la columna principal */
.barra {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: flex-start;
  margin-bottom: 1.5rem;
}

.aviso {
  margin-top: 1rem;
}

.grupo {
  margin-bottom: 2rem;
}

.grupo:first-of-type {
  margin-top: 0;
}

.grupo__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.grupo__encabezado h2 {
  margin: 0;
}

.grupo__alternar {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.3rem 0.6rem 0.3rem 0.4rem;
  border: 0;
  border-radius: var(--radio);
  background: transparent;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s var(--curva);
}

.grupo__alternar:hover {
  background: var(--superficie-alta);
}

.grupo__flecha {
  flex-shrink: 0;
  width: 1.1rem;
  height: 1.1rem;
  color: var(--h);
  transition: transform 0.2s var(--curva);
}

.grupo__alternar[aria-expanded='false'] .grupo__flecha {
  transform: rotate(-90deg);
}

.grupo__cuenta {
  min-width: 1.6rem;
  padding: 0.05rem 0.5rem;
  border-radius: 999px;
  background: var(--superficie-alta);
  color: var(--texto-suave);
  font-family: var(--fuente);
  font-size: 0.8rem;
  font-weight: 600;
  text-align: center;
}

.grupo__cuerpo {
  padding-left: 0.4rem;
}

.barra-secciones {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 0.5rem;
}

.grupo__acciones {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

/* Responsivo para apilar columnas en pantallas pequeñas */
@media (max-width: 850px) {
  .layout-columnas {
    grid-template-columns: 1fr;
  }
}
</style>