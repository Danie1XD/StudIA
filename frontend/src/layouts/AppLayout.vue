<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { iniciales } from '@/utils/inicio'
import IconoNav from '@/components/IconoNav.vue'
import LogoStudIA from '@/components/LogoStudIA.vue'
import NotificacionesCampana from '@/components/NotificacionesCampana.vue'
import ToggleTema from '@/components/ToggleTema.vue'
import { NAVEGACION, ROL_ETIQUETA } from './navegacion'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const enlaces = computed(() => NAVEGACION[auth.rol] ?? [])
const inicio = computed(() => `/${auth.rol}`)
const perfil = computed(() => `/${auth.rol}/perfil`)

// El administrador no tiene panel de inicio con materias: no hay nada que buscar
const conBusqueda = computed(() => auth.rol !== 'admin')
const busqueda = ref(String(route.query.q ?? ''))

function buscar() {
  const texto = busqueda.value.trim()
  router.push({ path: inicio.value, query: texto ? { q: texto } : {} })
}

async function salir() {
  await auth.cerrarSesion()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app">
    <a href="#contenido" class="saltar">Saltar al contenido</a>

    <aside class="lateral">
      <RouterLink :to="inicio" class="marca">
        <LogoStudIA class="marca__logo" />
        <span>Stud<b>IA</b></span>
      </RouterLink>

      <nav class="lateral__nav" aria-label="Principal">
        <RouterLink
          v-for="enlace in enlaces"
          :key="enlace.a"
          :to="enlace.a"
          class="enlace"
          :active-class="enlace.exacto ? undefined : 'activo'"
          :exact-active-class="enlace.exacto ? 'activo' : undefined"
        >
          <IconoNav :nombre="enlace.icono" class="enlace__icono" />
          <span>{{ enlace.nombre }}</span>
        </RouterLink>

        <RouterLink to="/privacidad" class="enlace">
          <IconoNav nombre="politicas" class="enlace__icono" />
          <span>Políticas y privacidad</span>
        </RouterLink>
      </nav>

      <div class="pie">
        <div class="persona">
          <span class="avatar" aria-hidden="true">{{ iniciales(auth.usuario.nombre) }}</span>
          <div class="persona__datos">
            <RouterLink :to="perfil" class="persona__nombre">{{ auth.usuario.nombre }}</RouterLink>
            <small>{{ ROL_ETIQUETA[auth.rol] }}</small>
          </div>
          <button
            type="button"
            class="boton boton--fantasma boton--icono"
            aria-label="Cerrar sesión"
            title="Cerrar sesión"
            @click="salir"
          >
            <IconoNav nombre="salir" />
          </button>
        </div>
      </div>
    </aside>

    <div class="principal">
      <header class="superior">
        <RouterLink :to="inicio" class="marca superior__marca">
          <LogoStudIA class="marca__logo" />
          <span>Stud<b>IA</b></span>
        </RouterLink>

        <form v-if="conBusqueda" class="superior__busqueda" role="search" @submit.prevent="buscar">
          <label for="busqueda-global" class="solo-lectores">Buscar materias y tareas</label>
          <IconoNav nombre="buscar" class="superior__lupa" />
          <input
            id="busqueda-global"
            v-model="busqueda"
            type="search"
            class="superior__campo"
            placeholder="Buscar materias o tareas…"
            autocomplete="off"
          />
        </form>
        <span v-else class="superior__relleno"></span>

        <div class="superior__herramientas">
          <ToggleTema />
          <NotificacionesCampana v-if="conBusqueda" />
          <button
            type="button"
            class="boton boton--fantasma boton--icono superior__salir"
            aria-label="Cerrar sesión"
            @click="salir"
          >
            <IconoNav nombre="salir" />
          </button>
          <RouterLink :to="perfil" class="avatar" :aria-label="`Mi perfil, ${auth.usuario.nombre}`">
            {{ iniciales(auth.usuario.nombre) }}
          </RouterLink>
        </div>
      </header>

      <main id="contenido" class="contenido" tabindex="-1">
        <RouterView />
      </main>
    </div>

    <nav class="inferior" aria-label="Principal">
      <RouterLink
        v-for="enlace in enlaces"
        :key="enlace.a"
        :to="enlace.a"
        class="inferior__enlace"
        :active-class="enlace.exacto ? undefined : 'activo'"
        :exact-active-class="enlace.exacto ? 'activo' : undefined"
      >
        <IconoNav :nombre="enlace.icono" class="inferior__icono" />
        <span>{{ enlace.corto }}</span>
      </RouterLink>
    </nav>
  </div>
</template>

<style scoped>
.app {
  display: grid;
  grid-template-columns: 268px minmax(0, 1fr);
  min-height: 100vh;
  min-height: 100dvh;
}

.saltar {
  position: absolute;
  top: -4rem;
  left: 1rem;
  z-index: 100;
  padding: 0.6rem 1rem;
  border-radius: var(--radio);
  background: var(--morado);
  color: var(--sobre-morado);
  font-weight: 600;
}

.saltar:focus {
  top: 1rem;
}

/* ---------- Menú lateral (escritorio) ---------- */
.lateral {
  position: sticky;
  top: 0;
  align-self: start;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  height: 100vh;
  height: 100dvh;
  padding: 1.35rem 1rem 1rem;
  background: var(--lateral);
  border-right: 1px solid var(--lateral-borde);
}

.marca {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0 0.6rem;
  color: var(--texto);
  font-family: var(--fuente-titulo);
  font-size: 1.6rem;
  font-weight: 700;
  letter-spacing: -0.02em;
  text-decoration: none;
}

.marca b {
  color: var(--morado-texto);
  font-weight: 700;
}

.marca__logo {
  width: 34px;
  height: 34px;
  color: var(--morado-texto);
  transition: transform 0.3s var(--curva);
}

.marca:hover .marca__logo {
  transform: rotate(-8deg) scale(1.08);
}

.lateral__nav {
  display: flex;
  flex: 1;
  flex-direction: column;
  gap: 0.2rem;
  overflow-y: auto;
}

.enlace {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.7rem 0.9rem;
  border-radius: var(--radio);
  color: var(--texto-suave);
  font-weight: 500;
  text-decoration: none;
  transition:
    background-color 0.15s,
    color 0.15s,
    transform 0.15s var(--curva);
}

.enlace:hover {
  background: var(--morado-suave);
  color: var(--texto);
}

.enlace:hover .enlace__icono {
  transform: scale(1.1);
}

.enlace.activo {
  background: var(--lateral-activo);
  color: var(--lateral-activo-texto);
  font-weight: 700;
}

.enlace__icono {
  flex: none;
  width: 1.3rem;
  height: 1.3rem;
  transition: transform 0.2s var(--curva);
}

.pie {
  padding-top: 0.9rem;
  border-top: 1px solid var(--lateral-borde);
}

.persona {
  display: flex;
  align-items: center;
  gap: 0.7rem;
  min-width: 0;
}

.persona__datos {
  display: flex;
  flex: 1;
  flex-direction: column;
  min-width: 0;
}

.persona__nombre {
  color: var(--texto);
  font-size: 0.92rem;
  font-weight: 700;
  line-height: 1.25;
  overflow-wrap: anywhere;
  text-decoration: none;
}

.persona__nombre:hover {
  color: var(--morado-texto);
}

.persona small {
  color: var(--texto-suave);
}

.avatar {
  display: grid;
  flex: none;
  place-items: center;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: var(--degradado-marca);
  color: var(--sobre-marca);
  font-size: 0.9rem;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.2s var(--curva);
}

a.avatar:hover {
  transform: scale(1.06);
}

/* ---------- Zona principal ---------- */
.principal {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.superior {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.85rem clamp(1rem, 3vw, 2.5rem);
  border-bottom: 1px solid var(--borde);
  background: color-mix(in srgb, var(--fondo) 85%, transparent);
  backdrop-filter: blur(12px);
}

.superior__marca {
  display: none;
}

.superior__busqueda,
.superior__relleno {
  position: relative;
  flex: 1;
  max-width: 560px;
}

.superior__lupa {
  position: absolute;
  top: 50%;
  left: 1rem;
  width: 1.1rem;
  height: 1.1rem;
  color: var(--texto-suave);
  transform: translateY(-50%);
  pointer-events: none;
}

.superior__campo {
  width: 100%;
  min-height: 2.75rem;
  padding: 0.6rem 1rem 0.6rem 2.8rem;
  border: 1px solid var(--borde);
  border-radius: 999px;
  background: var(--superficie);
  color: var(--texto);
  font: inherit;
  transition:
    border-color 0.15s,
    box-shadow 0.15s;
}

.superior__campo::placeholder {
  color: var(--texto-suave);
  opacity: 1;
}

.superior__campo:focus {
  border-color: var(--morado-texto);
  box-shadow: 0 0 0 3px var(--morado-suave);
}

.superior__campo:focus-visible {
  outline: none;
}

.superior__herramientas {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  margin-left: auto;
}

.superior__salir {
  display: none;
}

.contenido {
  flex: 1;
  min-width: 0;
  width: 100%;
  max-width: 1240px;
  margin-inline: auto;
  padding: 1.75rem clamp(1rem, 3vw, 2.5rem) 4rem;
}

.contenido:focus {
  outline: none;
}

/* ---------- Barra inferior (móvil) ---------- */
.inferior {
  position: fixed;
  right: 0.6rem;
  bottom: calc(0.6rem + env(safe-area-inset-bottom, 0px));
  left: 0.6rem;
  z-index: 30;
  display: none;
  gap: 0.1rem;
  padding: 0.4rem;
  border: 1px solid var(--lateral-borde);
  border-radius: 24px;
  background: var(--lateral);
  box-shadow: 0 18px 40px -12px rgba(15, 8, 40, 0.45);
}

.inferior__enlace {
  display: flex;
  flex: 1;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
  min-width: 0;
  padding: 0.45rem 0.1rem;
  border-radius: 16px;
  color: var(--texto-suave);
  font-size: 0.68rem;
  font-weight: 600;
  text-decoration: none;
  transition:
    background-color 0.15s,
    color 0.15s;
}

.inferior__enlace.activo {
  background: var(--lateral-activo);
  color: var(--lateral-activo-texto);
}

.inferior__icono {
  width: 1.3rem;
  height: 1.3rem;
}

.inferior__enlace.activo .inferior__icono {
  animation: rebote 0.35s var(--curva);
}

@keyframes rebote {
  50% {
    transform: translateY(-3px) scale(1.12);
  }
}

@media (max-width: 900px) {
  .app {
    grid-template-columns: minmax(0, 1fr);
  }

  .lateral {
    display: none;
  }

  .superior {
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.7rem 1rem;
  }

  .superior__marca {
    display: flex;
    padding: 0;
    font-size: 1.35rem;
  }

  .superior__marca .marca__logo {
    width: 28px;
    height: 28px;
  }

  .superior__busqueda {
    order: 3;
    flex: 1 1 100%;
    max-width: none;
  }

  .superior__relleno {
    display: none;
  }

  .superior__salir {
    display: inline-flex;
  }

  .contenido {
    padding: 1.1rem 1rem 7rem;
  }

  .inferior {
    display: flex;
  }
}
</style>
