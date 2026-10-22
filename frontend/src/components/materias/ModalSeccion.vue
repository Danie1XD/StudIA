<script setup>
import { ref } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { seccionesApi } from '@/api/secciones'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'

const props = defineProps({
  materiaId: { type: [String, Number], required: true },
  seccion: { type: Object, default: null }, // null = crear una nueva
})

const emit = defineEmits(['guardado', 'cerrar'])

const editando = props.seccion !== null
const nombre = ref(props.seccion?.nombre ?? '')
const cargando = ref(false)
const error = ref('')
const errorNombre = ref('')

async function guardar() {
  error.value = ''
  errorNombre.value = ''

  if (!nombre.value.trim()) {
    errorNombre.value = 'El nombre de la sección es obligatorio.'
    return
  }

  cargando.value = true

  try {
    if (editando) await seccionesApi.actualizar(props.seccion.id, nombre.value.trim())
    else await seccionesApi.crear(props.materiaId, nombre.value.trim())

    emit('guardado', editando)
  } catch (e) {
    errorNombre.value = erroresDeCampos(e).nombre ?? ''
    if (!errorNombre.value) error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <BaseModal :titulo="editando ? 'Renombrar sección' : 'Nueva sección'" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="guardar">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="nombre"
        etiqueta="Nombre de la sección"
        ayuda="Por ejemplo: Unidad 1, Proyecto final o Material de apoyo."
        :error="errorNombre"
        maxlength="100"
        autofocus
        required
      />

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="cargando">
          {{ cargando ? 'Guardando…' : editando ? 'Guardar' : 'Crear sección' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
