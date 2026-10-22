<script setup>
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { usuariosApi } from '@/api/usuarios'
import { mensajeDeError } from '@/api/errores'
import { ROL_ETIQUETA } from '@/layouts/navegacion'
import { useAuthStore } from '@/stores/auth'
import { armarParametros, FILTRO_ESTADOS, FILTRO_ROLES, textoMaterias } from '@/utils/usuarios'
import CampoSelect from '@/components/CampoSelect.vue'
import CampoTexto from '@/components/CampoTexto.vue'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'
import ModalUsuario from '@/components/usuarios/ModalUsuario.vue'

const auth = useAuthStore()

const filtros = reactive({ rol: '', activo: '', buscar: '' })
const pagina = ref(1)
const usuarios = ref([])
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const cargando = ref(true)
const actualizando = ref(false)
const error = ref('')
const aviso = ref('')

const modal = ref(null) // { usuario: objeto | null }
const aDesactivar = ref(null)
const desactivando = ref(false)
const errorDesactivar = ref('')
const procesandoId = ref(null)

let ultimaPeticion = 0
let temporizador

async function cargar() {
  const actual = ++ultimaPeticion
  error.value = ''
  actualizando.value = true

  try {
    const respuesta = await usuariosApi.listar(
      armarParametros({ ...filtros, pagina: pagina.value }),
    )
    if (actual !== ultimaPeticion) return

    usuarios.value = respuesta.data
    meta.value = respuesta.meta

    // Si la página quedó vacía (por ejemplo, se desactivó al último de un filtro), se retrocede una
    if (!usuarios.value.length && pagina.value > 1) {
      pagina.value -= 1
      await cargar()
    }
  } catch (e) {
    if (actual !== ultimaPeticion) return
    error.value = mensajeDeError(e)
  } finally {
    if (actual === ultimaPeticion) {
      cargando.value = false
      actualizando.value = false
    }
  }
}

function irA(numero) {
  pagina.value = numero
  cargar()
}

// La búsqueda espera a que el usuario deje de escribir; los otros filtros actúan al instante
watch(
  () => filtros.buscar,
  () => {
    clearTimeout(temporizador)
    temporizador = setTimeout(() => {
      pagina.value = 1
      cargar()
    }, 350)
  },
)

watch(
  () => [filtros.rol, filtros.activo],
  () => {
    pagina.value = 1
    cargar()
  },
)

function abrirModal(usuario = null) {
  aviso.value = ''
  modal.value = { usuario }
}

async function alGuardar(tipo) {
  modal.value = null
  aviso.value = tipo === 'creado' ? 'El usuario se creó.' : 'Los cambios se guardaron.'
  await cargar()
}

function pedirDesactivar(usuario) {
  aviso.value = ''
  errorDesactivar.value = ''
  aDesactivar.value = usuario
}

async function confirmarDesactivar() {
  const usuario = aDesactivar.value
  desactivando.value = true
  errorDesactivar.value = ''

  try {
    await usuariosApi.desactivar(usuario.id)
    aDesactivar.value = null
    aviso.value = `Se desactivó la cuenta de «${usuario.nombre}».`
    await cargar()
  } catch (e) {
    errorDesactivar.value = mensajeDeError(e)
  } finally {
    desactivando.value = false
  }
}

async function activar(usuario) {
  aviso.value = ''
  error.value = ''
  procesandoId.value = usuario.id

  try {
    await usuariosApi.activar(usuario.id)
    aviso.value = `Se reactivó la cuenta de «${usuario.nombre}».`
    await cargar()
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    procesandoId.value = null
  }
}

onMounted(cargar)
onBeforeUnmount(() => clearTimeout(temporizador))
</script>

<template>
  <section>
    <header class="encabezado">
      <div>
        <h1>Usuarios</h1>
        <p class="suave">Da de alta docentes y administra las cuentas de la plataforma.</p>
      </div>
      <button type="button" class="boton" @click="abrirModal()">Nuevo usuario</button>
    </header>

    <p v-if="aviso" class="alerta alerta--exito" role="status">{{ aviso }}</p>

    <div class="filtros-usuarios">
      <CampoTexto
        v-model="filtros.buscar"
        etiqueta="Buscar por nombre o correo"
        tipo="search"
        autocomplete="off"
      />
      <CampoSelect v-model="filtros.rol" etiqueta="Rol" :opciones="FILTRO_ROLES" />
      <CampoSelect v-model="filtros.activo" etiqueta="Estado" :opciones="FILTRO_ESTADOS" />
    </div>

    <p v-if="cargando" class="suave">Cargando usuarios…</p>

    <template v-else>
      <p v-if="error" class="alerta" role="alert">{{ error }}</p>

      <div v-if="!usuarios.length" class="tarjeta vacio">
        <h2>No hay usuarios con esos filtros</h2>
        <p class="suave">Prueba con otra búsqueda o quita algún filtro.</p>
      </div>

      <template v-else>
        <p class="suave" role="status">
          {{ meta.total }} {{ meta.total === 1 ? 'usuario' : 'usuarios' }}
        </p>

        <div class="tabla-envoltura" :aria-busy="actualizando ? 'true' : 'false'">
          <table class="tabla">
            <caption class="solo-lectores">
              Usuarios de la plataforma
            </caption>
            <thead>
              <tr>
                <th scope="col">Usuario</th>
                <th scope="col">Rol</th>
                <th scope="col">Estado</th>
                <th scope="col">Materias</th>
                <th scope="col"><span class="solo-lectores">Acciones</span></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="usuario in usuarios" :key="usuario.id">
                <th scope="row">
                  {{ usuario.nombre }}
                  <small class="suave usuario-correo">{{ usuario.email }}</small>
                </th>
                <td>
                  <span class="insignia">{{ ROL_ETIQUETA[usuario.rol] }}</span>
                </td>
                <td>
                  <span
                    :class="['insignia', usuario.activo ? 'insignia--exito' : 'insignia--error']"
                  >
                    {{ usuario.activo ? 'Activo' : 'Desactivado' }}
                  </span>
                </td>
                <td class="suave">{{ textoMaterias(usuario) }}</td>
                <td>
                  <div class="acciones">
                    <button
                      type="button"
                      class="boton boton--secundario boton--chico"
                      :aria-label="`Editar a ${usuario.nombre}`"
                      @click="abrirModal(usuario)"
                    >
                      Editar
                    </button>
                    <template v-if="usuario.id !== auth.usuario.id">
                      <button
                        v-if="usuario.activo"
                        type="button"
                        class="boton boton--peligro boton--chico"
                        :aria-label="`Desactivar a ${usuario.nombre}`"
                        @click="pedirDesactivar(usuario)"
                      >
                        Desactivar
                      </button>
                      <button
                        v-else
                        type="button"
                        class="boton boton--secundario boton--chico"
                        :disabled="procesandoId === usuario.id"
                        :aria-label="`Activar a ${usuario.nombre}`"
                        @click="activar(usuario)"
                      >
                        Activar
                      </button>
                    </template>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <nav v-if="meta.last_page > 1" class="paginacion" aria-label="Paginación">
          <button
            type="button"
            class="boton boton--secundario boton--chico"
            :disabled="pagina <= 1 || actualizando"
            @click="irA(pagina - 1)"
          >
            Anterior
          </button>
          <span class="suave" role="status">
            Página {{ meta.current_page }} de {{ meta.last_page }}
          </span>
          <button
            type="button"
            class="boton boton--secundario boton--chico"
            :disabled="pagina >= meta.last_page || actualizando"
            @click="irA(pagina + 1)"
          >
            Siguiente
          </button>
        </nav>
      </template>
    </template>

    <ModalUsuario
      v-if="modal"
      :usuario="modal.usuario"
      @guardado="alGuardar"
      @cerrar="modal = null"
    />

    <ModalConfirmacion
      v-if="aDesactivar"
      titulo="Desactivar cuenta"
      texto-confirmar="Desactivar"
      peligro
      :cargando="desactivando"
      :error="errorDesactivar"
      @confirmar="confirmarDesactivar"
      @cerrar="aDesactivar = null"
    >
      <p>
        ¿Desactivar la cuenta de <strong>{{ aDesactivar.nombre }}</strong
        >? No podrá iniciar sesión y se cerrarán sus sesiones abiertas. Sus datos y su historial se
        conservan, y puedes reactivarla cuando quieras.
      </p>
    </ModalConfirmacion>
  </section>
</template>

<style scoped>
.filtros-usuarios {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.usuario-correo {
  display: block;
  font-weight: 400;
  overflow-wrap: anywhere;
}

.paginacion {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

@media (max-width: 700px) {
  .filtros-usuarios {
    grid-template-columns: 1fr;
  }
}
</style>
