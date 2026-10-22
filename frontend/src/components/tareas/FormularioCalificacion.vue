<script setup>
import { computed, reactive, ref } from 'vue'
import { entregasApi } from '@/api/entregas'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { formatearPuntos } from '@/utils/estados'
import {
  armarRetroalimentacion,
  MAX_RETROALIMENTACION,
  validarCalificacion,
} from '@/utils/revision'
import CampoTexto from '@/components/CampoTexto.vue'

const props = defineProps({
  entrega: { type: Object, required: true },
  puntajeMaximo: { type: Number, required: true },
})

const emit = defineEmits(['calificada'])

const yaCalificada = computed(() => props.entrega.estado === 'calificado')

const form = reactive({
  calificacion: yaCalificada.value ? formatearPuntos(props.entrega.calificacion_final) : '',
  retroalimentacion: yaCalificada.value ? (props.entrega.retroalimentacion_final ?? '') : '',
})

const errores = ref({})
const error = ref('')
const aviso = ref('')
const guardando = ref(false)

/** Copia la nota y el texto que sugirió la IA al formulario; el docente los revisa antes de publicar. */
function aplicarSugerencia(evaluacion) {
  form.calificacion = formatearPuntos(evaluacion.calificacion_sugerida)
  form.retroalimentacion = armarRetroalimentacion(evaluacion)
  errores.value = {}
  error.value = ''
  aviso.value = 'Se cargó la sugerencia de la IA. Revísala y ajústala antes de publicar.'
}

defineExpose({ aplicarSugerencia })

async function guardar() {
  error.value = ''
  aviso.value = ''

  const nuevos = {}
  const problemaNota = validarCalificacion(form.calificacion, props.puntajeMaximo)
  if (problemaNota) nuevos.calificacion_final = problemaNota
  if (!form.retroalimentacion.trim()) {
    nuevos.retroalimentacion_final = 'Escribe la retroalimentación para el alumno.'
  }

  errores.value = nuevos
  if (Object.keys(nuevos).length > 0) return

  guardando.value = true

  try {
    const mensajeExito = yaCalificada.value
      ? 'La calificación se actualizó.'
      : 'La calificación se publicó. El alumno ya puede verla.'

    const entrega = await entregasApi.calificar(props.entrega.id, {
      calificacion_final: Number(String(form.calificacion).trim()),
      retroalimentacion_final: form.retroalimentacion.trim(),
    })

    aviso.value = mensajeExito
    emit('calificada', entrega)
  } catch (e) {
    errores.value = erroresDeCampos(e)
    if (Object.keys(errores.value).length === 0) error.value = mensajeDeError(e)
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <form class="formulario" novalidate @submit.prevent="guardar">
    <p v-if="aviso" class="alerta alerta--exito" role="status">{{ aviso }}</p>
    <p v-if="error" class="alerta" role="alert">{{ error }}</p>

    <CampoTexto
      v-model="form.calificacion"
      etiqueta="Calificación"
      tipo="number"
      :error="errores.calificacion_final"
      :ayuda="`De 0 a ${puntajeMaximo} puntos, con hasta dos decimales.`"
      min="0"
      :max="puntajeMaximo"
      step="0.01"
      inputmode="decimal"
      required
    />
    <CampoTexto
      v-model="form.retroalimentacion"
      etiqueta="Retroalimentación para el alumno"
      area
      :error="errores.retroalimentacion_final"
      :maxlength="MAX_RETROALIMENTACION"
      required
    />

    <div class="acciones">
      <button type="submit" class="boton" :disabled="guardando">
        {{
          guardando
            ? 'Guardando…'
            : yaCalificada
              ? 'Actualizar calificación'
              : 'Publicar calificación'
        }}
      </button>
    </div>
  </form>
</template>
