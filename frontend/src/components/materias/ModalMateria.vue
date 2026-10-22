<script setup>
import { reactive, ref } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { materiasApi } from '@/api/materias'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'

const props = defineProps({
  materia: { type: Object, default: null }, // null = crear una nueva
})

const emit = defineEmits(['guardado', 'cerrar'])

const editando = props.materia !== null
const form = reactive({
  nombre: props.materia?.nombre ?? '',
  descripcion: props.materia?.descripcion ?? '',
})

const cargando = ref(false)
const error = ref('')
const errores = ref({})

async function guardar() {
  error.value = ''
  errores.value = {}
  if (!form.nombre.trim()) {
    errores.value = { nombre: 'El nombre de la materia es obligatorio.' }
    return
  }
  cargando.value = true

  try {
    const datos = { nombre: form.nombre.trim(), descripcion: form.descripcion.trim() || null }

    if (editando) await materiasApi.actualizar(props.materia.id, datos)
    else await materiasApi.crear(datos)

    emit('guardado')
  } catch (e) {
    errores.value = erroresDeCampos(e)
    if (Object.keys(errores.value).length === 0) error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <BaseModal :titulo="editando ? 'Editar materia' : 'Nueva materia'" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="guardar">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="form.nombre"
        etiqueta="Nombre de la materia"
        :error="errores.nombre"
        maxlength="255"
        autofocus
        required
      />
      <CampoTexto
        v-model="form.descripcion"
        etiqueta="Descripción (opcional)"
        area
        :error="errores.descripcion"
        maxlength="2000"
      />

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="cargando">
          {{ cargando ? 'Guardando…' : editando ? 'Guardar cambios' : 'Crear materia' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
