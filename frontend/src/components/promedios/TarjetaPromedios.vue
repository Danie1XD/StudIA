<script setup>
import { computed, reactive } from 'vue'
import { formatearPuntos } from '@/utils/estados'
import {
  detalleParcial,
  hayCambios,
  promedioFinal,
  textosIniciales,
  validarParcial,
  valoresSimulados,
} from '@/utils/promedios'
import CampoTexto from '@/components/CampoTexto.vue'

const props = defineProps({
  datos: { type: Object, required: true }, // { materia, parciales, promedio_final }
})

const textos = reactive(textosIniciales(props.datos.parciales))

const errores = computed(() => textos.map(validarParcial))
const cambio = computed(() => hayCambios(props.datos.parciales, textos))
const finalSimulado = computed(() => promedioFinal(valoresSimulados(props.datos.parciales, textos)))

function restablecer() {
  textos.splice(0, textos.length, ...textosIniciales(props.datos.parciales))
}
</script>

<template>
  <article class="promedio tarjeta">
    <header class="promedio__encabezado">
      <h2>{{ datos.materia.nombre }}</h2>
      <p class="promedio__final">
        <span class="promedio__valor">{{ formatearPuntos(datos.promedio_final) }}</span>
        <span class="suave">promedio final</span>
      </p>
    </header>

    <ul class="promedio__parciales">
      <li v-for="(parcial, i) in datos.parciales" :key="parcial.parcial" class="parcial">
        <h3>Parcial {{ parcial.parcial }}</h3>
        <p class="parcial__valor">{{ formatearPuntos(parcial.promedio) }}</p>
        <p class="suave parcial__detalle">{{ detalleParcial(parcial) }}</p>

        <CampoTexto
          v-model="textos[i]"
          :etiqueta="`Simular parcial ${parcial.parcial}`"
          :aria-label="`Simular parcial ${parcial.parcial} de ${datos.materia.nombre}`"
          :error="errores[i]"
          inputmode="decimal"
          maxlength="6"
        />
      </li>
    </ul>

    <div v-if="cambio" class="alerta alerta--aviso promedio__simulacion" role="status">
      <span>
        Con esta simulación tu promedio final sería
        <strong>{{ formatearPuntos(finalSimulado) }}</strong
        >. Tu calificación real no cambia.
      </span>
      <button type="button" class="boton boton--secundario boton--chico" @click="restablecer">
        Restablecer
      </button>
    </div>
  </article>
</template>

<style scoped>
.promedio {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.promedio h2,
.promedio h3,
.promedio p {
  margin: 0;
}

.promedio__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  justify-content: space-between;
  gap: 0.5rem;
}

.promedio__final {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.promedio__valor {
  color: var(--morado-texto);
  font-size: 2.25rem;
  font-weight: 700;
  line-height: 1;
}

.promedio__parciales {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.parcial {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.9rem;
  border-radius: var(--radio);
  background: var(--superficie-alta);
}

.parcial h3 {
  font-size: 0.95rem;
}

.parcial__valor {
  font-size: 1.6rem;
  font-weight: 700;
}

.parcial__detalle {
  min-height: 1.5em;
  font-size: 0.85rem;
}

.promedio__simulacion {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}
</style>
