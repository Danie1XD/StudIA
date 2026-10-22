<script setup>
import { onMounted, ref } from 'vue'
import { evaluacionesApi } from '@/api/evaluaciones'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { formatearPuntos } from '@/utils/estados'
import { formatearFechaHora } from '@/utils/fechas'

const props = defineProps({
  entregaId: { type: [String, Number], required: true },
  tieneEvaluacion: { type: Boolean, default: false },
  puedeGenerar: { type: Boolean, default: true },
  puntajeMaximo: { type: Number, required: true },
  tipoTexto: { type: String, default: '' },
  rubricaNombre: { type: String, default: '' },
})

const emit = defineEmits(['usar'])

const evaluacion = ref(null)
const cargando = ref(props.tieneEvaluacion)
const generando = ref(false)
const error = ref('')

onMounted(async () => {
  if (!props.tieneEvaluacion) return

  try {
    evaluacion.value = await evaluacionesApi.obtener(props.entregaId)
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})

async function generar() {
  error.value = ''
  generando.value = true

  try {
    evaluacion.value = await evaluacionesApi.generar(props.entregaId)
  } catch (e) {
    // Los 422 (nada que revisar, archivo muy grande, ya calificada) traen su mensaje en español
    const campos = erroresDeCampos(e)
    error.value = campos.entrega ?? campos.archivo ?? mensajeDeError(e)
  } finally {
    generando.value = false
  }
}
</script>

<template>
  <section class="panel-ia tarjeta" :aria-busy="generando ? 'true' : 'false'">
    <h2>Pre-evaluación con IA</h2>
    <p class="suave">
      Es solo una ayuda: puede equivocarse y la calificación final la decides tú. El alumno no ve
      esta sugerencia, solo la calificación y la retroalimentación que tú publiques.
    </p>
    <p class="suave panel-ia__contexto">
      <span>Tipo de trabajo: {{ tipoTexto || 'General' }}</span>
      <span>
        {{
          rubricaNombre
            ? `Rúbrica: ${rubricaNombre}`
            : 'Sin rúbrica: se usan las instrucciones y el puntaje máximo'
        }}
      </span>
    </p>

    <p v-if="error" class="alerta" role="alert">{{ error }}</p>
    <p v-if="generando" class="alerta alerta--aviso" role="status">
      Revisando el trabajo con la IA. Puede tardar hasta un minuto…
    </p>
    <p v-if="cargando" class="suave">Cargando la pre-evaluación…</p>

    <template v-if="evaluacion">
      <p class="panel-ia__nota">
        <span class="panel-ia__valor">{{ formatearPuntos(evaluacion.calificacion_sugerida) }}</span>
        <span class="suave">de {{ puntajeMaximo }} puntos sugeridos</span>
      </p>

      <div v-if="evaluacion.retroalimentacion.puntos_fuertes">
        <h3>Puntos fuertes</h3>
        <p class="texto-libre">{{ evaluacion.retroalimentacion.puntos_fuertes }}</p>
      </div>

      <div v-if="evaluacion.retroalimentacion.areas_mejora">
        <h3>Áreas de mejora</h3>
        <p class="texto-libre">{{ evaluacion.retroalimentacion.areas_mejora }}</p>
      </div>

      <div v-if="evaluacion.retroalimentacion.retroalimentacion_general">
        <h3>Mensaje para el alumno</h3>
        <p class="texto-libre">{{ evaluacion.retroalimentacion.retroalimentacion_general }}</p>
      </div>

      <div v-if="evaluacion.retroalimentacion.criterios?.length">
        <h3>Por criterio de la rúbrica</h3>
        <ul class="panel-ia__criterios">
          <li v-for="criterio in evaluacion.retroalimentacion.criterios" :key="criterio.nombre">
            <strong>{{ criterio.nombre }}</strong>
            <span class="insignia">
              {{ formatearPuntos(criterio.puntos_obtenidos) }} / {{ criterio.puntos_maximos }}
            </span>
            <span v-if="criterio.comentario" class="suave texto-libre">{{
              criterio.comentario
            }}</span>
          </li>
        </ul>
      </div>

      <p class="suave">
        Generada el {{ formatearFechaHora(evaluacion.generada_at) }} con {{ evaluacion.modelo }}.
      </p>
    </template>

    <div class="acciones acciones--izquierda">
      <button v-if="evaluacion" type="button" class="boton" @click="emit('usar', evaluacion)">
        Usar como base de la calificación
      </button>
      <button
        v-if="puedeGenerar"
        type="button"
        :class="['boton', evaluacion ? 'boton--secundario' : '']"
        :disabled="generando || cargando"
        @click="generar"
      >
        {{ generando ? 'Generando…' : evaluacion ? 'Volver a generar' : 'Generar pre-evaluación' }}
      </button>
    </div>

    <p v-if="puedeGenerar" class="suave panel-ia__privacidad">
      Al generarla, el trabajo se envía a Gemini (Google).
      <RouterLink to="/privacidad" target="_blank" rel="noopener"
        >Ver aviso de privacidad</RouterLink
      >.
    </p>
    <p v-else-if="!evaluacion" class="suave">Esta entrega ya fue calificada.</p>
  </section>
</template>

<style scoped>
.panel-ia {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.panel-ia h2,
.panel-ia h3,
.panel-ia p {
  margin: 0;
}

.panel-ia h3 {
  font-size: 1rem;
}

.panel-ia__contexto {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.9rem;
}

.panel-ia__nota {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.panel-ia__valor {
  color: var(--morado-texto);
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.panel-ia__criterios {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin: 0.25rem 0 0;
  padding: 0;
  list-style: none;
}

.panel-ia__criterios li {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 0.5rem;
}

.panel-ia__criterios .texto-libre {
  flex-basis: 100%;
}

.panel-ia__privacidad {
  font-size: 0.85rem;
}
</style>
