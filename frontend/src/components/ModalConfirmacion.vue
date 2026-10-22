<script setup>
import BaseModal from './BaseModal.vue'

defineProps({
  titulo: { type: String, required: true },
  textoConfirmar: { type: String, default: 'Confirmar' },
  peligro: { type: Boolean, default: false },
  cargando: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

defineEmits(['confirmar', 'cerrar'])
</script>

<template>
  <BaseModal :titulo="titulo" @cerrar="$emit('cerrar')">
    <slot />

    <p v-if="error" class="alerta" role="alert">{{ error }}</p>

    <div class="acciones">
      <!-- El foco empieza en Cancelar: así un Enter distraído no destruye nada -->
      <button type="button" class="boton boton--secundario" autofocus @click="$emit('cerrar')">
        Cancelar
      </button>
      <button
        type="button"
        :class="['boton', { 'boton--peligro': peligro }]"
        :disabled="cargando"
        @click="$emit('confirmar')"
      >
        {{ cargando ? 'Procesando…' : textoConfirmar }}
      </button>
    </div>
  </BaseModal>
</template>
