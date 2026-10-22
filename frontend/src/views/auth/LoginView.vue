<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { mensajeDeError } from '@/api/errores'
import { rutaInicio } from '@/router/guards'
import IconoNav from '@/components/IconoNav.vue'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const email = ref('')
const password = ref('')
const cargando = ref(false)
const error = ref('')

const sesionExpirada = Boolean(route.query.expirada)

/** Solo se vuelve a rutas internas: evita que un enlace malicioso mande a otro sitio. */
function destinoSeguro() {
  const destino = route.query.redirect
  const esInterno =
    typeof destino === 'string' && destino.startsWith('/') && !destino.startsWith('//')
  return esInterno ? destino : null
}

async function enviar() {
  error.value = ''
  cargando.value = true

  try {
    const usuario = await auth.iniciarSesion({
      email: email.value.trim(),
      password: password.value,
    })
    // Si el destino no corresponde a su rol, el guardia lo manda a su inicio
    await router.push(destinoSeguro() ?? rutaInicio(usuario.rol))
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <form class="login" novalidate @submit.prevent="enviar">
    <div class="login__titulo">
      <h1>Inicia sesión</h1>
      <p class="suave">Ingresa con tu cuenta institucional.</p>
    </div>

    <p v-if="sesionExpirada" class="alerta alerta--aviso" role="status">
      Tu sesión expiró. Vuelve a iniciar sesión.
    </p>
    <p v-if="error" class="alerta" role="alert">{{ error }}</p>

    <div class="campo-grupo">
      <label for="login-correo" class="campo-grupo__etiqueta">Correo electrónico</label>
      <div class="login__entrada">
        <IconoNav nombre="correo" class="login__icono" />
        <input
          id="login-correo"
          v-model="email"
          class="campo login__campo"
          type="email"
          autocomplete="username"
          placeholder="nombre@studia.com"
          required
        />
      </div>
    </div>

    <div class="campo-grupo">
      <label for="login-password" class="campo-grupo__etiqueta">Contraseña</label>
      <div class="login__entrada">
        <IconoNav nombre="candado" class="login__icono" />
        <input
          id="login-password"
          v-model="password"
          class="campo login__campo"
          type="password"
          autocomplete="current-password"
          placeholder="Tu contraseña"
          required
        />
      </div>
    </div>

    <button class="boton login__enviar" type="submit" :disabled="cargando">
      <span v-if="cargando" class="login__spinner" aria-hidden="true"></span>
      {{ cargando ? 'Entrando…' : 'Iniciar sesión' }}
    </button>

    <p class="suave login__ayuda">¿Olvidaste tu contraseña? Contacta al administrador.</p>
    <p class="suave login__ayuda">
      Conoce cómo tratamos tus datos en el
      <RouterLink to="/privacidad">aviso de privacidad</RouterLink>.
    </p>
  </form>
</template>

<style scoped>
.login {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
  width: 100%;
  max-width: 400px;
  animation: entrar 0.5s var(--curva) both;
}

.login__titulo h1 {
  margin: 0;
  font-size: 2rem;
}

.login__titulo p {
  margin: 0.35rem 0 0;
}

.login__entrada {
  position: relative;
}

.login__icono {
  position: absolute;
  top: 50%;
  left: 0.95rem;
  width: 1.15rem;
  height: 1.15rem;
  color: var(--texto-suave);
  transform: translateY(-50%);
  pointer-events: none;
}

.login__campo {
  min-height: 3rem;
  padding-left: 2.8rem;
}

.login__enviar {
  min-height: 3rem;
  margin-top: 0.25rem;
  font-size: 1rem;
}

.login__spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

@keyframes girar {
  to {
    transform: rotate(360deg);
  }
}

.login__ayuda {
  margin: 0;
  font-size: 0.9rem;
}
</style>
