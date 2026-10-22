<script setup>
import { computed, useId } from 'vue'

defineOptions({ inheritAttrs: false }) // required, maxlength, autofocus, etc. van al campo, no al contenedor

const props = defineProps({
  etiqueta: { type: String, required: true },
  tipo: { type: String, default: 'text' },
  area: { type: Boolean, default: false },
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

    <textarea
      v-if="area"
      rows="4"
      v-bind="$attrs"
      :id="id"
      v-model="modelo"
      class="campo campo--area"
      :aria-invalid="error ? 'true' : undefined"
      :aria-describedby="descritoPor"
    ></textarea>
    <input
      v-else
      v-bind="$attrs"
      :id="id"
      v-model="modelo"
      class="campo"
      :type="tipo"
      :aria-invalid="error ? 'true' : undefined"
      :aria-describedby="descritoPor"
    />

    <p v-if="error" :id="`${id}-error`" class="campo-grupo__error" role="alert">{{ error }}</p>
    <p v-else-if="ayuda" :id="`${id}-ayuda`" class="campo-grupo__ayuda">{{ ayuda }}</p>
  </div>
</template>
