<script setup>
import { reactive, ref } from 'vue'
import BaseModal from '@/components/BaseModal.vue'
import CampoSelect from '@/components/CampoSelect.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import { usuariosApi } from '@/api/usuarios'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { useAuthStore } from '@/stores/auth'
import { armarDatosUsuario, ROLES, validarUsuario } from '@/utils/usuarios'

const props = defineProps({
  usuario: { type: Object, default: null }, // null = crear uno nuevo
})

const emit = defineEmits(['guardado', 'cerrar'])

const auth = useAuthStore()
const edicion = props.usuario !== null
const propio = edicion && props.usuario.id === auth.usuario.id

const form = reactive({
  nombre: props.usuario?.nombre ?? '',
  email: props.usuario?.email ?? '',
  rol: props.usuario?.rol ?? 'alumno',
  password: '',
})

const errores = ref({})
const error = ref('')
const guardando = ref(false)

async function guardar() {
  error.value = ''
  errores.value = validarUsuario(form, { edicion, propio })

  if (Object.keys(errores.value).length > 0) return

  guardando.value = true

  try {
    const datos = armarDatosUsuario(form, { edicion, propio })

    if (edicion) {
      const guardado = await usuariosApi.actualizar(props.usuario.id, datos)

      if (propio) {
        auth.actualizarUsuario({
          id: guardado.id,
          nombre: guardado.nombre,
          email: guardado.email,
          rol: guardado.rol,
        })
      }
    } else {
      await usuariosApi.crear(datos)
    }

    emit('guardado', edicion ? 'actualizado' : 'creado')
  } catch (e) {
    errores.value = erroresDeCampos(e)
    if (Object.keys(errores.value).length === 0) error.value = mensajeDeError(e)
  } finally {
    guardando.value = false
  }
}
</script>

<template>
  <BaseModal :titulo="edicion ? 'Editar usuario' : 'Nuevo usuario'" @cerrar="emit('cerrar')">
    <form class="formulario" novalidate @submit.prevent="guardar">
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <CampoTexto
        v-model="form.nombre"
        etiqueta="Nombre completo"
        :error="errores.nombre"
        maxlength="255"
        autofocus
        required
      />
      <CampoTexto
        v-model="form.email"
        etiqueta="Correo electrónico"
        tipo="email"
        :error="errores.email"
        maxlength="255"
        required
      />

      <template v-if="propio">
        <p class="suave">
          Tu rol y tu contraseña no se cambian desde aquí: usa «Mi perfil» para la contraseña.
        </p>
      </template>
      <template v-else>
        <CampoSelect v-model="form.rol" etiqueta="Rol" :opciones="ROLES" :error="errores.rol" />
        <CampoTexto
          v-model="form.password"
          etiqueta="Contraseña"
          tipo="password"
          :error="errores.password"
          :ayuda="
            edicion
              ? 'Déjala vacía para no cambiarla. Si la cambias, se cierran sus sesiones abiertas.'
              : 'Mínimo 8 caracteres. Pídele que la cambie en su perfil al entrar.'
          "
          autocomplete="new-password"
        />
      </template>

      <div class="acciones">
        <button type="button" class="boton boton--secundario" @click="emit('cerrar')">
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="guardando">
          {{ guardando ? 'Guardando…' : edicion ? 'Guardar cambios' : 'Crear usuario' }}
        </button>
      </div>
    </form>
  </BaseModal>
</template>
