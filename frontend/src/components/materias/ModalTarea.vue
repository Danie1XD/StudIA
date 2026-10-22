<script setup>
import { computed, reactive, ref, useId } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoSelect from '@/components/CampoSelect.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { tareasApi } from '@/api/tareas'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { deIsoAInputLocal } from '@/utils/fechas'
import { armarFormularioTarea, TAMANO_MAXIMO_PDF, TIPOS_EVALUACION } from '@/utils/formularioTarea'

const props = defineProps({
  materiaId: { type: [String, Number], required: true },
  tarea: { type: Object, default: null }, // null = crear una nueva
  secciones: { type: Array, default: () => [] },
  rubricas: { type: Array, default: () => [] },
  seccionInicial: { type: [String, Number], default: '' },
})

const emit = defineEmits(['guardado', 'cerrar'])

const editando = props.tarea !== null
const idArchivo = useId()

const form = reactive({
  titulo: props.tarea?.titulo ?? '',
  instrucciones: props.tarea?.instrucciones ?? '',
  fecha_limite: deIsoAInputLocal(props.tarea?.fecha_limite),
  puntaje_maximo: String(props.tarea?.puntaje_maximo ?? 100),
  parcial: String(props.tarea?.parcial ?? 1),
  tipo_evaluacion: props.tarea?.tipo_evaluacion ?? 'general',
  seccion_id: String(props.tarea?.seccion_id ?? props.seccionInicial ?? ''),
  rubrica_id: String(props.tarea?.rubrica_id ?? ''),
  rubrica_visible: Boolean(props.tarea?.rubrica_visible),
})

const opcionesSeccion = [
  { valor: '', texto: 'Sin sección (General)' },
  ...props.secciones.map((s) => ({ valor: String(s.id), texto: s.nombre })),
]

const opcionesRubrica = [
  { valor: '', texto: 'Sin rúbrica' },
  ...props.rubricas.map((r) => ({
    valor: String(r.id),
    texto: `${r.nombre} (${r.puntos_total} pts)`,
  })),
]

const opcionesParcial = [
  { valor: '1', texto: 'Parcial 1' },
  { valor: '2', texto: 'Parcial 2' },
  { valor: '3', texto: 'Parcial 3' },
]

const ayudaRubrica = computed(() =>
  props.rubricas.length
    ? 'La IA usa la rúbrica para sugerir la calificación criterio por criterio.'
    : 'Aún no tienes rúbricas. Créalas en la sección «Rúbricas» del menú.',
)

const archivo = ref(null)
const quitarArchivo = ref(false)
const errorArchivo = ref('')

const cargando = ref(false)
const error = ref('')
const errores = ref({})

const tieneArchivoActual = computed(() => editando && props.tarea.tiene_archivo)

function alElegirArchivo(evento) {
  const elegido = evento.target.files?.[0] ?? null
  errorArchivo.value = ''
  archivo.value = null

  if (!elegido) return

  if (elegido.size > TAMANO_MAXIMO_PDF) {
    errorArchivo.value = 'El PDF no puede pesar más de 10 MB.'
    evento.target.value = ''
    return
  }

  archivo.value = elegido
  quitarArchivo.value = false
}

/** Revisa lo básico antes de enviar: así los mensajes salen en español. */
function validar() {
  const nuevos = {}

  if (!form.titulo.trim()) nuevos.titulo = 'El título es obligatorio.'
  if (!form.instrucciones.trim()) nuevos.instrucciones = 'Las instrucciones son obligatorias.'

  const puntaje = Number(form.puntaje_maximo)
  if (
    String(form.puntaje_maximo).trim() === '' ||
    !Number.isInteger(puntaje) ||
    puntaje < 1 ||
    puntaje > 1000
  ) {
    nuevos.puntaje_maximo = 'Escribe un puntaje entero entre 1 y 1000.'
  }

  return nuevos
}

async function guardar() {
  error.value = ''
  errores.value = validar()

  if (Object.keys(errores.value).length > 0 || errorArchivo.value) return

  cargando.value = true

  try {
    const datos = armarFormularioTarea(form, {
      archivo: archivo.value,
      quitarArchivo: quitarArchivo.value,
      edicion: editando,
    })

    if (editando) await tareasApi.actualizar(props.tarea.id, datos)
    else await tareasApi.crear(props.materiaId, datos)

    emit('guardado', editando)
  } catch (e) {
    errores.value = erroresDeCampos(e)
    if (Object.keys(errores.value).length === 0) error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <BaseModal :titulo="editando ? 'Editar tarea' : 'Nueva tarea'" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="guardar">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="form.titulo"
        etiqueta="Título"
        :error="errores.titulo"
        maxlength="255"
        autofocus
        required
      />
      <CampoTexto
        v-model="form.instrucciones"
        etiqueta="Instrucciones"
        area
        :error="errores.instrucciones"
        maxlength="10000"
        required
      />
      <CampoTexto
        v-model="form.fecha_limite"
        etiqueta="Fecha límite (opcional)"
        tipo="datetime-local"
        :error="errores.fecha_limite"
        ayuda="En tu hora local."
      />

      <div class="fila-campos">
        <CampoTexto
          v-model="form.puntaje_maximo"
          etiqueta="Puntaje máximo"
          tipo="number"
          :error="errores.puntaje_maximo"
          min="1"
          max="1000"
          step="1"
          required
        />
        <CampoSelect
          v-model="form.parcial"
          etiqueta="Parcial"
          :opciones="opcionesParcial"
          :error="errores.parcial"
        />
      </div>

      <CampoSelect
        v-model="form.tipo_evaluacion"
        etiqueta="Tipo de trabajo"
        :opciones="TIPOS_EVALUACION"
        :error="errores.tipo_evaluacion"
        ayuda="Define en qué se fija la IA al pre-evaluar."
      />
      <CampoSelect
        v-model="form.seccion_id"
        etiqueta="Sección"
        :opciones="opcionesSeccion"
        :error="errores.seccion_id"
      />
      <CampoSelect
        v-model="form.rubrica_id"
        etiqueta="Rúbrica"
        :opciones="opcionesRubrica"
        :error="errores.rubrica_id"
        :ayuda="ayudaRubrica"
      />
      <label v-if="form.rubrica_id" class="casilla">
        <input v-model="form.rubrica_visible" type="checkbox" />
        <span>
          Compartir la rúbrica con los alumnos
          <small class="suave">Podrán ver los criterios y los puntos de cada uno desde la tarea.</small>
        </span>
      </label>

      <div class="campo-grupo">
        <label :for="idArchivo" class="campo-grupo__etiqueta">PDF de apoyo (opcional)</label>
        <input
          :id="idArchivo"
          class="campo"
          type="file"
          accept="application/pdf,.pdf"
          :aria-invalid="errorArchivo || errores.archivo ? 'true' : undefined"
          @change="alElegirArchivo"
        />
        <p v-if="errorArchivo || errores.archivo" class="campo-grupo__error" role="alert">
          {{ errorArchivo || errores.archivo }}
        </p>
        <p v-else class="campo-grupo__ayuda">
          {{
            tieneArchivoActual
              ? 'La tarea ya tiene un PDF. Si eliges otro, lo reemplaza. Máximo 10 MB.'
              : 'Máximo 10 MB.'
          }}
        </p>

        <label v-if="tieneArchivoActual && !archivo" class="casilla">
          <input v-model="quitarArchivo" type="checkbox" />
          Quitar el PDF actual
        </label>
      </div>

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="cargando">
          {{ cargando ? 'Guardando…' : editando ? 'Guardar cambios' : 'Publicar tarea' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
