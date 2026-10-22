<script setup>
import { computed, reactive, ref } from 'vue'
import { perfilApi } from '@/api/perfil'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { ROL_ETIQUETA } from '@/layouts/navegacion'
import { useAuthStore } from '@/stores/auth'
import { validarCambioPassword, validarPerfil } from '@/utils/perfil'
import CampoTexto from '@/components/CampoTexto.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const auth = useAuthStore()

// --- Lógica visual añadida ---
const inicialUsuario = computed(() => auth.usuario?.nombre?.charAt(0).toUpperCase() || 'U')
const handleUsuario = computed(() => {
  if (!auth.usuario?.email) return ''
  return `@${auth.usuario.email.split('@')[0]}`
})

// --- Lógica original mantenida intacta ---
async function salir() {
  await auth.cerrarSesion()
  router.push({ name: 'login' })
}

const datos = reactive({ nombre: auth.usuario.nombre, email: auth.usuario.email })
const erroresDatos = ref({})
const errorDatos = ref('')
const avisoDatos = ref('')
const guardandoDatos = ref(false)

const clave = reactive({ actual: '', nueva: '', confirmacion: '' })
const erroresClave = ref({})
const errorClave = ref('')
const avisoClave = ref('')
const cambiandoClave = ref(false)

async function guardarDatos() {
  errorDatos.value = ''
  avisoDatos.value = ''
  erroresDatos.value = validarPerfil(datos)

  if (Object.keys(erroresDatos.value).length > 0) return

  guardandoDatos.value = true

  try {
    const usuario = await perfilApi.actualizar({
      nombre: datos.nombre.trim(),
      email: datos.email.trim(),
    })

    auth.actualizarUsuario(usuario)
    datos.nombre = usuario.nombre
    datos.email = usuario.email
    avisoDatos.value = 'Tus datos se guardaron correctamente.'
  } catch (e) {
    erroresDatos.value = erroresDeCampos(e)
    if (Object.keys(erroresDatos.value).length === 0) errorDatos.value = mensajeDeError(e)
  } finally {
    guardandoDatos.value = false
  }
}

async function cambiarClave() {
  errorClave.value = ''
  avisoClave.value = ''
  erroresClave.value = validarCambioPassword(clave)

  if (Object.keys(erroresClave.value).length > 0) return

  cambiandoClave.value = true

  try {
    const respuesta = await perfilApi.cambiarPassword({
      password_actual: clave.actual,
      password: clave.nueva,
      password_confirmation: clave.confirmacion,
    })

    avisoClave.value = respuesta.message ?? 'Tu contraseña se actualizó.'
    Object.assign(clave, { actual: '', nueva: '', confirmacion: '' })
  } catch (e) {
    erroresClave.value = erroresDeCampos(e)
    if (Object.keys(erroresClave.value).length === 0) errorClave.value = mensajeDeError(e)
  } finally {
    cambiandoClave.value = false
  }
}
</script>

<template>
  <section class="perfil animar-fade">
    <header class="encabezado">
      <div>
        <h1>Mi perfil</h1>
        <p class="suave">Gestiona tu información personal y las preferencias de la cuenta.</p>
      </div>
    </header>

    <div class="perfil__grid">
      <!-- Columna Izquierda: Tarjeta de Presentación -->
      <aside class="perfil__lateral">
        <div class="tarjeta tarjeta--centrada animar-slide" style="animation-delay: 0.1s">
          <div class="avatar">{{ inicialUsuario }}</div>
          <h2 class="nombre-usuario">{{ auth.usuario.nombre }}</h2>
          <p class="handle-usuario suave">{{ handleUsuario }}</p>
          <span class="insignia insignia--rol mt-2">{{ ROL_ETIQUETA[auth.rol] }}</span>
        </div>

        <button 
          class="tarjeta boton-salir animar-slide mt-3" 
          style="animation-delay: 0.15s" 
          @click="salir"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icono-chico">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Cerrar sesión
        </button>
      </aside>

      <!-- Columna Derecha: Formularios e Información -->
      <div class="perfil__principal">

        <!-- Formulario de Datos Personales -->
        <form class="tarjeta formulario animar-slide" style="animation-delay: 0.2s" novalidate @submit.prevent="guardarDatos">
          <div class="tarjeta__cabecera">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icono-medio"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <h3>Datos personales</h3>
          </div>

          <p v-if="avisoDatos" class="alerta alerta--exito" role="status">{{ avisoDatos }}</p>
          <p v-if="errorDatos" class="alerta" role="alert">{{ errorDatos }}</p>

          <div class="grid-2-col">
            <CampoTexto
              v-model="datos.nombre"
              etiqueta="Nombre completo"
              :error="erroresDatos.nombre"
              autocomplete="name"
              maxlength="255"
              required
            />
            <CampoTexto
              v-model="datos.email"
              etiqueta="Correo electrónico"
              tipo="email"
              :error="erroresDatos.email"
              autocomplete="email"
              maxlength="255"
              required
            />
          </div>

          <div class="acciones alineado-derecha mt-2">
            <button type="submit" class="boton" :disabled="guardandoDatos">
              {{ guardandoDatos ? 'Guardando…' : 'Guardar cambios' }}
            </button>
          </div>
        </form>

        <!-- Formulario de Contraseña -->
        <form class="tarjeta formulario animar-slide mt-4" style="animation-delay: 0.3s" novalidate @submit.prevent="cambiarClave">
          <div class="tarjeta__cabecera">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icono-medio"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <h3>Seguridad y contraseña</h3>
          </div>
          <p class="suave mb-3">Al cambiarla se cerrarán tus sesiones abiertas en otros dispositivos.</p>

          <p v-if="avisoClave" class="alerta alerta--exito" role="status">{{ avisoClave }}</p>
          <p v-if="errorClave" class="alerta" role="alert">{{ errorClave }}</p>

          <div class="grid-3-col">
            <CampoTexto
              v-model="clave.actual"
              etiqueta="Contraseña actual"
              tipo="password"
              :error="erroresClave.password_actual"
              autocomplete="current-password"
              required
            />
            <CampoTexto
              v-model="clave.nueva"
              etiqueta="Contraseña nueva"
              tipo="password"
              :error="erroresClave.password"
              ayuda="Mínimo 8 caracteres."
              autocomplete="new-password"
              required
            />
            <CampoTexto
              v-model="clave.confirmacion"
              etiqueta="Confirma la nueva"
              tipo="password"
              autocomplete="new-password"
              required
            />
          </div>

          <div class="acciones alineado-derecha mt-2">
            <button type="submit" class="boton boton--secundario" :disabled="cambiandoClave">
              {{ cambiandoClave ? 'Cambiando…' : 'Cambiar contraseña' }}
            </button>
          </div>
        </form>

      </div>
    </div>
  </section>
</template>

<style scoped>
/* Contenedores y Grid Principal */
.perfil {
  display: flex;
  flex-direction: column;
  gap: 0;
  max-width: 1100px;
  margin: 0 auto;
  width: 100%;
}

.perfil__grid {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.5rem;
  align-items: start;
}

/* Tarjetas (el aspecto base viene de main.css) */
.tarjeta__cabecera {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  border-bottom: 1px solid var(--borde, rgba(255,255,255,0.05));
  padding-bottom: 1rem;
}

.tarjeta__cabecera h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--texto, #fff);
}

/* Columna Lateral (Presentación) */
.tarjeta--centrada {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 2.5rem 1.5rem;
}

.avatar {
  width: 90px;
  height: 90px;
  background-color: var(--morado, #b19cf9);
  color: #1a1a1a;
  font-size: 2.5rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 28px;
  margin-bottom: 1rem;
}

.nombre-usuario {
  margin: 0;
  font-size: 1.3rem;
  color: var(--texto, #fff);
}

.handle-usuario {
  margin: 0.2rem 0 0 0;
  font-size: 0.9rem;
}

.insignia--rol {
  padding: 0.3rem 1rem;
  font-size: 0.85rem;
  border-radius: 99px;
  background: rgba(74, 222, 128, 0.1);
  color: #4ade80;
  border: 1px solid rgba(74, 222, 128, 0.2);
}

/* Botón de Cerrar Sesión (Estilo Ghost/Peligro sutil) */
.boton-salir {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: transparent;
  color: #ff6b6b;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
  font-size: 1rem;
}

.boton-salir:hover {
  background: rgba(255, 107, 107, 0.05);
  border-color: rgba(255, 107, 107, 0.2);
}

/* Formularios y Grillas Internas */
.grid-2-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.grid-3-col {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
}

.alineado-derecha {
  display: flex;
  justify-content: flex-end;
}

/* Utilidades */
.icono-chico { width: 18px; height: 18px; }
.icono-medio { width: 22px; height: 22px; color: var(--morado, #b19cf9); }
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 1rem; }

/* --- Animaciones --- */
.animar-fade {
  animation: fadeIn 0.4s ease-out forwards;
}

.animar-slide {
  opacity: 0;
  animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsivo */
@media (max-width: 900px) {
  .perfil__grid {
    grid-template-columns: 1fr;
  }
  .grid-2-col {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}
</style>