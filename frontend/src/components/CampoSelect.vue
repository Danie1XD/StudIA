<script setup>
import { computed, useId } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  etiqueta: { type: String, required: true },
  opciones: { type: Array, required: true }, // [{ valor, texto }]
  error: { type: String, default: '' },
  ayuda: { type: String, default: '' },
})

const modelo = defineModel({ type: [String, Number], default: '' })
const id = useId()

const descritoPor = computed(() => {
  if (props.error) return `${id}-error`
  if (props.ayuda) return `${id}-ayuda`
  return undefined
})
</script>

<template>
  <div class="campo-grupo">
    <label :for="id" class="campo-grupo__etiqueta">{{ etiqueta }}</label>
    <select
      v-bind="$attrs"
      :id="id"
      v-model="modelo"
      class="campo"
      :aria-invalid="error ? 'true' : undefined"
      :aria-describedby="descritoPor"
    >
      <option v-for="opcion in opciones" :key="`${opcion.valor}`" :value="opcion.valor">
        {{ opcion.texto }}
      </option>
    </select>
    <p v-if="error" :id="`${id}-error`" class="campo-grupo__error" role="alert">{{ error }}</p>
    <p v-else-if="ayuda" :id="`${id}-ayuda`" class="campo-grupo__ayuda">{{ ayuda }}</p>
  </div>
</template>
