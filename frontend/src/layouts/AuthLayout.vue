<script setup>
import LogoStudIA from '@/components/LogoStudIA.vue'
import ToggleTema from '@/components/ToggleTema.vue'
</script>

<template>
  <div class="auth">
    <aside class="auth__marca">
      <span class="auth__orbe auth__orbe--a" aria-hidden="true"></span>
      <span class="auth__orbe auth__orbe--b" aria-hidden="true"></span>

      <div class="auth__logo-fila">
        <LogoStudIA class="auth__logo" />
        <span class="auth__nombre">Stud<b>IA</b></span>
      </div>

      <div class="auth__mensaje">
        <h2>Tus clases, en un solo lugar.</h2>
        <p>
          Consulta materias, entrega tareas y revisa tus calificaciones con ayuda de inteligencia
          artificial.
        </p>
      </div>

      <small class="auth__pie">Sistema Universitario Dirigido por IA</small>

      <LogoStudIA class="auth__marca-agua" />
    </aside>

    <main class="auth__contenido">
      <ToggleTema class="auth__tema" />
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.auth {
  display: grid;
  grid-template-columns: 1.05fr 1fr;
  min-height: 100vh;
  min-height: 100dvh;
}

.auth__marca {
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 2rem;
  padding: clamp(1.75rem, 5vw, 4rem);
  overflow: hidden;
  background:
    radial-gradient(circle at 15% 10%, rgba(122, 71, 255, 0.35), transparent 50%),
    linear-gradient(160deg, #1d1440 0%, #120c2b 100%);
  color: #ffffff;
}

.auth__marca > *:not(.auth__orbe, .auth__marca-agua) {
  position: relative;
  z-index: 1;
}

.auth__logo-fila {
  display: flex;
  align-items: center;
  gap: 0.7rem;
}

.auth__logo {
  width: 40px;
  height: 40px;
  color: #b9a0ff;
}

.auth__nombre {
  font-family: var(--fuente-titulo);
  font-size: 1.9rem;
  font-weight: 700;
  letter-spacing: -0.02em;
}

.auth__nombre b {
  color: #b9a0ff;
}

.auth__mensaje h2 {
  max-width: 13ch;
  margin: 0;
  font-size: clamp(2.1rem, 4.4vw, 3.5rem);
  line-height: 1.04;
  animation: entrar 0.7s var(--curva) both;
}

.auth__mensaje p {
  max-width: 38ch;
  margin: 1rem 0 0;
  color: #cbc3e6;
  font-size: 1.05rem;
  animation: entrar 0.7s 0.12s var(--curva) both;
}

.auth__pie {
  color: #a79fcb;
}

.auth__marca-agua {
  position: absolute;
  right: -70px;
  bottom: -70px;
  width: 400px;
  height: 400px;
  color: #6a35f0;
  opacity: 0.32;
  animation: flotar 7s ease-in-out infinite;
}

.auth__orbe {
  position: absolute;
  border-radius: 50%;
  filter: blur(50px);
  pointer-events: none;
}

.auth__orbe--a {
  top: -90px;
  left: -60px;
  width: 280px;
  height: 280px;
  background: #6a35f0;
  opacity: 0.35;
  animation: flotar 9s ease-in-out infinite;
}

.auth__orbe--b {
  right: 10%;
  bottom: 18%;
  width: 180px;
  height: 180px;
  background: #3cd1bf;
  opacity: 0.12;
  animation: flotar 11s 1s ease-in-out infinite;
}

.auth__contenido {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem clamp(1.25rem, 5vw, 4rem);
  background: var(--superficie);
}

.auth__tema {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

@media (max-width: 860px) {
  .auth {
    grid-template-columns: 1fr;
    grid-template-rows: auto 1fr;
  }

  .auth__marca {
    gap: 1.25rem;
    padding: 1.5rem 1.25rem 1.75rem;
  }

  .auth__logo {
    width: 32px;
    height: 32px;
  }

  .auth__nombre {
    font-size: 1.5rem;
  }

  .auth__mensaje h2 {
    max-width: 16ch;
    font-size: 1.9rem;
  }

  .auth__mensaje p {
    margin-top: 0.6rem;
    font-size: 0.97rem;
  }

  .auth__pie {
    display: none;
  }

  .auth__marca-agua {
    right: -50px;
    bottom: -60px;
    width: 210px;
    height: 210px;
  }

  .auth__contenido {
    align-items: flex-start;
    padding: 1.75rem 1.25rem 2.5rem;
  }
}
</style>
