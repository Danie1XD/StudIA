<script setup>
import { ref, watch } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { materiasApi } from '@/api/materias'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'

const emit = defineEmits(['unido', 'cerrar'])

const codigo = ref('')
const cargando = ref(false)
const error = ref('')
const errorCodigo = ref('')

// El código va en mayúsculas y sin espacios, aunque se escriba o se pegue de otra forma
watch(codigo, (valor) => {
  const limpio = valor.toUpperCase().replace(/\s+/g, '')
  if (limpio !== valor) codigo.value = limpio
})

async function unirse() {
  error.value = ''
  errorCodigo.value = ''
  cargando.value = true

  try {
    const materia = await materiasApi.unirse(codigo.value)
    emit('unido', materia)
  } catch (e) {
    errorCodigo.value = erroresDeCampos(e).codigo_acceso ?? ''
    if (!errorCodigo.value) error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <BaseModal titulo="Unirme a una materia" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="unirse">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="codigo"
        etiqueta="Código de la materia"
        ayuda="Son 6 caracteres y te los da tu docente."
        :error="errorCodigo"
        maxlength="6"
        autocomplete="off"
        autofocus
        required
      />

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="cargando">
          {{ cargando ? 'Uniéndome…' : 'Unirme' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
