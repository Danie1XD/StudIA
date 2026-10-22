<script setup>
import { computed, ref, watch } from 'vue'
import { materiasApi } from '@/api/materias'
import { promediosApi } from '@/api/promedios'
import { mensajeDeError } from '@/api/errores'
import { armarCsv } from '@/utils/csv'
import { guardarArchivo, nombreDeArchivo } from '@/utils/descarga'
import { formatearPuntos } from '@/utils/estados'
import { detalleParcial, filasParaExportar, promedioDelGrupo } from '@/utils/promedios'

const props = defineProps({
  id: { type: [String, Number], required: true },
})

const materia = ref(null)
const filas = ref([])
const reglas = ref({ vencidas_como_cero: true })
const cargando = ref(true)
const error = ref('')

const promedioGrupo = computed(() => promedioDelGrupo(filas.value))
const conDatos = computed(() => filas.value.filter((f) => f.promedio_final !== null).length)

function parcialDe(fila, numero) {
  return fila.parciales.find((p) => p.parcial === numero)
}

let ultimaPeticion = 0

async function cargar() {
  const actual = ++ultimaPeticion // si el usuario cambia de materia rápido, se ignora la respuesta vieja
  cargando.value = true
  error.value = ''
  materia.value = null
  filas.value = []

  try {
    const respuesta = await promediosApi.deMateria(props.id)
    if (actual !== ultimaPeticion) return

    // El nombre de la materia solo adorna la página: si falla, la página sigue
    const datosMateria = await materiasApi.mostrar(props.id).catch(() => null)
    if (actual !== ultimaPeticion) return

    filas.value = respuesta.data
    reglas.value = respuesta.reglas ?? reglas.value
    materia.value = datosMateria
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) cargando.value = false
  }
}

function exportar() {
  const csv = armarCsv(filasParaExportar(filas.value))
  const archivo = new Blob([csv], { type: 'text/csv;charset=utf-8' })

  guardarArchivo(archivo, nombreDeArchivo(`promedios ${materia.value?.nombre ?? ''}`, 'csv'))
}

watch(() => props.id, cargar, { immediate: true })
</script>

<template>
  <section>
    <RouterLink :to="{ name: 'docente.materia', params: { id } }" class="enlace-volver">
      ← {{ materia ? materia.nombre : 'Materia' }}
    </RouterLink>

    <p v-if="cargando" class="suave">Calculando los promedios…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <template v-else>
      <header class="encabezado">
        <div>
          <h1>Promedios del grupo</h1>
          <p class="suave">
            Calculados con las calificaciones que ya publicaste, sin que tengas que sacar cuentas.
          </p>
        </div>
        <button v-if="filas.length" type="button" class="boton boton--secundario" @click="exportar">
          Descargar CSV
        </button>
      </header>

      <p v-if="!filas.length" class="suave">Aún no hay alumnos inscritos en esta materia.</p>

      <template v-else>
        <p class="resumen" role="status">
          <span class="insignia"> Promedio del grupo: {{ formatearPuntos(promedioGrupo) }} </span>
          <span class="suave">
            {{ conDatos }} de {{ filas.length }}
            {{ filas.length === 1 ? 'alumno con calificaciones' : 'alumnos con calificaciones' }}
          </span>
        </p>

        <div class="tabla-envoltura">
          <table class="tabla">
            <caption class="solo-lectores">
              Promedios por parcial de
              {{
                materia?.nombre
              }}
            </caption>
            <thead>
              <tr>
                <th scope="col">Alumno</th>
                <th scope="col">Parcial 1</th>
                <th scope="col">Parcial 2</th>
                <th scope="col">Parcial 3</th>
                <th scope="col">Promedio final</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="fila in filas" :key="fila.alumno.id">
                <th scope="row">{{ fila.alumno.nombre }}</th>
                <td v-for="n in 3" :key="n">
                  <strong>{{ formatearPuntos(parcialDe(fila, n)?.promedio) }}</strong>
                  <small class="celda-detalle suave">{{
                    detalleParcial(parcialDe(fila, n))
                  }}</small>
                </td>
                <td>
                  <strong class="final">{{ formatearPuntos(fila.promedio_final) }}</strong>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="suave nota-calculo">
          Cada parcial es una regla de tres con los puntos de sus tareas. Cuentan las tareas ya
          calificadas{{
            reglas.vencidas_como_cero
              ? ' y las que vencieron sin entrega, que valen 0'
              : '; las que vencieron sin entrega no cuentan hasta que se entreguen y se califiquen'
          }}. Lo entregado sin calificar todavía no cuenta. El promedio final es el de los parciales
          que ya tienen calificación.
        </p>
      </template>
    </template>
  </section>
</template>

<style scoped>
.resumen {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 0.75rem;
}

.celda-detalle {
  display: block;
  font-weight: 400;
}

.final {
  color: var(--morado-texto);
  font-size: 1.1rem;
}

.nota-calculo {
  margin-top: 1rem;
  font-size: 0.9rem;
}
</style>
