<script setup>
import { ref } from 'vue'
import CampoTexto from '@/components/CampoTexto.vue'
import LogoStudIA from '@/components/LogoStudIA.vue'
import ToggleTema from '@/components/ToggleTema.vue'

const nombre = ref('')
const correo = ref('correo-sin-arroba')

const colores = [
  { nombre: 'Fondo', variable: '--fondo' },
  { nombre: 'Superficie', variable: '--superficie' },
  { nombre: 'Superficie alta', variable: '--superficie-alta' },
  { nombre: 'Borde', variable: '--borde' },
  { nombre: 'Texto', variable: '--texto' },
  { nombre: 'Texto suave', variable: '--texto-suave' },
  { nombre: 'Morado', variable: '--morado' },
  { nombre: 'Morado fuerte', variable: '--morado-fuerte' },
  { nombre: 'Morado de texto', variable: '--morado-texto' },
  { nombre: 'Éxito', variable: '--exito-texto' },
  { nombre: 'Aviso', variable: '--aviso-texto' },
  { nombre: 'Error', variable: '--error-texto' },
]
</script>

<template>
  <main class="guia">
    <header class="guia__encabezado">
      <div class="guia__titulo">
        <LogoStudIA class="guia__logo" />
        <div>
          <h1>Guía de estilos</h1>
          <p class="suave">Solo visible en desarrollo.</p>
        </div>
      </div>
      <ToggleTema />
    </header>

    <section class="tarjeta">
      <h2>Colores</h2>
      <div class="guia__colores">
        <div v-for="color in colores" :key="color.variable">
          <div class="guia__muestra" :style="{ background: `var(${color.variable})` }"></div>
          <small>{{ color.nombre }}</small>
        </div>
      </div>
    </section>

    <section class="tarjeta guia__fila">
      <h2>Botones</h2>
      <button class="boton" type="button">Primario</button>
      <button class="boton boton--secundario" type="button">Secundario</button>
      <button class="boton boton--fantasma" type="button">Fantasma</button>
      <button class="boton boton--peligro" type="button">Eliminar</button>
      <button class="boton" type="button" disabled>Deshabilitado</button>
    </section>

    <section class="tarjeta guia__campos">
      <h2>Campos</h2>
      <CampoTexto v-model="nombre" etiqueta="Nombre" ayuda="Como aparecerá en tus materias." />
      <CampoTexto
        v-model="correo"
        etiqueta="Correo electrónico"
        tipo="email"
        error="Escribe un correo válido."
      />
    </section>

    <section class="tarjeta guia__campos">
      <h2>Alertas e insignias</h2>
      <p class="alerta" role="alert">Error: las credenciales son incorrectas.</p>
      <p class="alerta alerta--aviso">Aviso: tu sesión está por expirar.</p>
      <p class="alerta alerta--exito">Éxito: la tarea se publicó.</p>
      <p>
        <span class="insignia">Pendiente</span>
        <span class="insignia">Calificada</span>
        <span class="insignia">Parcial 2</span>
      </p>
      <p>Un <a href="#">enlace de ejemplo</a> dentro de un párrafo.</p>
    </section>
  </main>
</template>

<style scoped>
.guia {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  max-width: 820px;
  margin: 0 auto;
  padding: 2rem 1rem 4rem;
}

.guia__encabezado,
.guia__titulo,
.guia__fila {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.guia__encabezado {
  justify-content: space-between;
}

.guia__logo {
  width: 40px;
  height: 40px;
  color: var(--morado-texto);
}

.guia__fila {
  flex-wrap: wrap;
}

.guia__fila h2 {
  flex-basis: 100%;
  margin: 0;
}

.guia__campos {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.guia__campos h2,
.guia h2 {
  margin: 0 0 0.5rem;
}

.guia__colores {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 0.75rem;
}

.guia__muestra {
  height: 56px;
  margin-bottom: 0.25rem;
  border: 1px solid var(--borde);
  border-radius: var(--radio);
}
</style>
