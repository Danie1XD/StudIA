<script setup>
import { reactive, ref } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoSelect from '@/components/CampoSelect.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { anunciosApi } from '@/api/anuncios'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'

const props = defineProps({
  materiaId: { type: [String, Number], required: true },
  anuncio: { type: Object, default: null }, // null = crear uno nuevo
  secciones: { type: Array, default: () => [] },
  seccionInicial: { type: [String, Number], default: '' },
})

const emit = defineEmits(['guardado', 'cerrar'])

const editando = props.anuncio !== null

const form = reactive({
  contenido: props.anuncio?.contenido ?? '',
  seccion_id: String(props.anuncio?.seccion_id ?? props.seccionInicial ?? ''),
})

const opcionesSeccion = [
  { valor: '', texto: 'Sin sección (General)' },
  ...props.secciones.map((s) => ({ valor: String(s.id), texto: s.nombre })),
]

const cargando = ref(false)
const error = ref('')
const errores = ref({})

async function guardar() {
  error.value = ''
  errores.value = {}

  if (!form.contenido.trim()) {
    errores.value = { contenido: 'El anuncio no puede estar vacío.' }
    return
  }

  cargando.value = true

  try {
    const datos = {
      contenido: form.contenido.trim(),
      seccion_id: form.seccion_id === '' ? null : Number(form.seccion_id),
    }

    if (editando) await anunciosApi.actualizar(props.anuncio.id, datos)
    else await anunciosApi.crear(props.materiaId, datos)

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
  <BaseModal :titulo="editando ? 'Editar anuncio' : 'Nuevo anuncio'" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="guardar">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="form.contenido"
        etiqueta="Anuncio"
        area
        :error="errores.contenido"
        maxlength="5000"
        autofocus
        required
      />
      <CampoSelect
        v-model="form.seccion_id"
        etiqueta="Sección"
        :opciones="opcionesSeccion"
        :error="errores.seccion_id"
      />

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="cargando">
          {{ cargando ? 'Guardando…' : editando ? 'Guardar cambios' : 'Publicar anuncio' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
