<script setup>
import { computed, nextTick, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { rubricasApi } from '@/api/rubricas'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import {
  armarDatosRubrica,
  criterioVacio,
  desdeRubrica,
  MAX_CRITERIOS,
  mover,
  totalPuntos,
  validarRubrica,
} from '@/utils/rubricas'
import CampoTexto from '@/components/CampoTexto.vue'

const props = defineProps({
  id: { type: [String, Number], default: null }, // null = rúbrica nueva
})

const router = useRouter()
const editando = computed(() => props.id !== null)

const form = reactive({ nombre: '', descripcion: '', criterios: [criterioVacio()] })
const errores = ref(erroresVacios())
const error = ref('')
const errorCarga = ref('')
const cargando = ref(false)
const guardando = ref(false)
const botonAgregar = ref(null)

const total = computed(() => totalPuntos(form.criterios))

function erroresVacios() {
  return { nombre: '', criterios: '', porCriterio: [] }
}

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion
  error.value = ''
  errorCarga.value = ''
  errores.value = erroresVacios()

  if (!editando.value) {
    Object.assign(form, { nombre: '', descripcion: '', criterios: [criterioVacio()] })
    cargando.value = false
    return
  }

  cargando.value = true

  try {
    const rubrica = await rubricasApi.mostrar(props.id)
    if (actual !== ultimaPeticion) return
    Object.assign(form, desdeRubrica(rubrica))
  } catch (e) {
    if (actual !== ultimaPeticion) return
    errorCarga.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

watch(() => props.id, cargar, { immediate: true })

function agregar() {
  if (form.criterios.length >= MAX_CRITERIOS) return

  form.criterios.push(criterioVacio())
  nextTick(() => {
    const filas = document.querySelectorAll('.criterio')
    filas[filas.length - 1]?.querySelector('input')?.focus()
  })
}

function quitar(indice) {
  if (form.criterios.length <= 1) return

  form.criterios.splice(indice, 1)
  errores.value.porCriterio.splice(indice, 1) // los errores siguen alineados con sus criterios
  nextTick(() => botonAgregar.value?.focus())
}

async function moverCriterio(indice, delta) {
  const destino = indice + delta
  if (destino < 0 || destino >= form.criterios.length) return

  const clave = form.criterios[indice].clave
  form.criterios = mover(form.criterios, indice, delta)
  errores.value.porCriterio = mover(errores.value.porCriterio, indice, delta)

  // Al moverse el nodo se pierde el foco: se devuelve al botón que se acaba de usar
  await nextTick()
  const igual = document.getElementById(`${delta < 0 ? 'subir' : 'bajar'}-${clave}`)
  const otro = document.getElementById(`${delta < 0 ? 'bajar' : 'subir'}-${clave}`)
  ;(igual && !igual.disabled ? igual : otro)?.focus()
}

async function guardar() {
  error.value = ''
  errores.value = validarRubrica(form)

  if (!errores.value.valido) return

  guardando.value = true

  try {
    const datos = armarDatosRubrica(form)

    if (editando.value) await rubricasApi.actualizar(props.id, datos)
    else await rubricasApi.crear(datos)

    await router.push({
      name: 'docente.rubricas',
      query: { aviso: editando.value ? 'actualizada' : 'creada' },
    })
  } catch (e) {
    const campos = erroresDeCampos(e)
    const hayCampos = Object.keys(campos).length > 0
    const otro = Object.entries(campos).find(([campo]) => campo !== 'nombre')

    errores.value = { ...erroresVacios(), nombre: campos.nombre ?? '' }
    error.value = hayCampos ? (otro?.[1] ?? '') : mensajeDeError(e)
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <section>
    <RouterLink :to="{ name: 'docente.rubricas' }" class="enlace-volver">← Rúbricas</RouterLink>

    <p v-if="cargando" class="suave">Cargando la rúbrica…</p>
    <p v-else-if="errorCarga" class="alerta" role="alert">{{ errorCarga }}</p>

    <form v-else class="formulario editor" novalidate @submit.prevent="guardar">
      <h1>{{ editando ? 'Editar rúbrica' : 'Nueva rúbrica' }}</h1>

      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="form.nombre"
        etiqueta="Nombre de la rúbrica"
        :error="errores.nombre"
        maxlength="255"
        required
      />
      <CampoTexto
        v-model="form.descripcion"
        etiqueta="Descripción (opcional)"
        area
        rows="2"
        maxlength="2000"
        ayuda="Para recordar en qué tipo de tareas la usas."
      />

      <fieldset class="criterios">
        <legend>Criterios</legend>

        <div class="criterios__contenido">
          <p class="suave">
            Cada criterio vale puntos. La suma no tiene que coincidir con el puntaje de la tarea: la
            calificación sugerida se ajusta de forma proporcional.
          </p>
          <p v-if="errores.criterios" class="campo-grupo__error" role="alert">
            {{ errores.criterios }}
          </p>

          <ol class="criterios__lista">
            <li
              v-for="(criterio, i) in form.criterios"
              :key="criterio.clave"
              class="criterio tarjeta"
            >
              <div class="criterio__encabezado">
                <strong>Criterio {{ i + 1 }}</strong>
                <div class="criterio__acciones">
                  <button
                    :id="`subir-${criterio.clave}`"
                    type="button"
                    class="boton boton--fantasma boton--chico"
                    :aria-label="`Subir el criterio ${i + 1}`"
                    :disabled="i === 0"
                    @click="moverCriterio(i, -1)"
                  >
                    ↑
                  </button>
                  <button
                    :id="`bajar-${criterio.clave}`"
                    type="button"
                    class="boton boton--fantasma boton--chico"
                    :aria-label="`Bajar el criterio ${i + 1}`"
                    :disabled="i === form.criterios.length - 1"
                    @click="moverCriterio(i, 1)"
                  >
                    ↓
                  </button>
                  <button
                    type="button"
                    class="boton boton--peligro boton--chico"
                    :aria-label="`Quitar el criterio ${i + 1}`"
                    :disabled="form.criterios.length === 1"
                    @click="quitar(i)"
                  >
                    Quitar
                  </button>
                </div>
              </div>

              <CampoTexto
                v-model="criterio.nombre"
                etiqueta="Nombre del criterio"
                :error="errores.porCriterio[i]?.nombre"
                maxlength="150"
                required
              />
              <CampoTexto
                v-model="criterio.descripcion"
                etiqueta="Qué se espera (opcional)"
                area
                rows="2"
                maxlength="1000"
                ayuda="Entre más claro lo describas, mejor lo aplica la IA."
              />
              <CampoTexto
                v-model="criterio.puntos"
                etiqueta="Puntos"
                :error="errores.porCriterio[i]?.puntos"
                inputmode="numeric"
                maxlength="4"
                required
              />
            </li>
          </ol>

          <div class="acciones acciones--izquierda">
            <button
              ref="botonAgregar"
              type="button"
              class="boton boton--secundario"
              :disabled="form.criterios.length >= MAX_CRITERIOS"
              @click="agregar"
            >
              Agregar criterio
            </button>
          </div>

          <p class="total" role="status">
            Total: {{ total }} {{ total === 1 ? 'punto' : 'puntos' }}
          </p>
        </div>
      </fieldset>

      <div class="acciones">
        <RouterLink :to="{ name: 'docente.rubricas' }" class="boton boton--secundario">
          Cancelar
        </RouterLink>
        <button type="submit" class="boton" :disabled="guardando">
          {{ guardando ? 'Guardando…' : editando ? 'Guardar cambios' : 'Crear rúbrica' }}
        </button>
      </div>
    </form>
  </section>
</template>

<style scoped>
.editor {
  max-width: 760px;
}

.editor h1 {
  margin: 0;
}

.criterios {
  min-width: 0;
  margin: 0;
  padding: 0;
  border: 0;
}

.criterios legend {
  padding: 0;
  font-size: 1.1rem;
  font-weight: 700;
}

.criterios__contenido {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.criterios__contenido p {
  margin: 0;
}

.criterios__lista {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.criterio {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.criterio__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.criterio__acciones {
  display: flex;
  gap: 0.35rem;
}

.total {
  font-weight: 700;
}
</style>
