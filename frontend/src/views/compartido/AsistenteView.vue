<script setup>
import { nextTick, onMounted, ref, watch } from 'vue'
import { useAsistenteStore } from '@/stores/asistente'
import { MAX_MENSAJE } from '@/utils/asistente'
import MensajeMarkdown from '@/components/MensajeMarkdown.vue'

const asistente = useAsistenteStore()

const texto = ref('')
const lista = ref(null)
const campo = ref(null)

function irAlFinal() {
  nextTick(() => {
    if (lista.value) lista.value.scrollTop = lista.value.scrollHeight
  })
}

watch(() => [asistente.mensajes.length, asistente.enviando], irAlFinal)
onMounted(() => {
  irAlFinal()

  // Pregunta hecha desde el panel de inicio: se envía al llegar
  const pregunta = asistente.tomarPregunta()
  if (pregunta) asistente.enviar(pregunta)
})

async function enviar() {
  const contenido = texto.value
  if (!contenido.trim() || asistente.enviando) return

  texto.value = ''
  const ok = await asistente.enviar(contenido)

  // Si falló, se devuelve lo escrito para reintentar sin volver a teclearlo
  if (!ok && asistente.error && !texto.value) texto.value = contenido

  campo.value?.focus()
}

function alTeclear(evento) {
  if (evento.key === 'Enter' && !evento.shiftKey && !evento.isComposing) {
    evento.preventDefault()
    enviar()
  }
}
</script>

<template>
  <section class="asistente-layout">
    <!-- Cabecera de la sección -->
    <header class="encabezado-moderno">
      <div class="encabezado__texto">
        <h1>Asistente Inteligente</h1>
        <p class="suave">
          Resuelve dudas sobre tus estudios. Las respuestas son generadas por IA, confirma lo crítico con tu docente.
        </p>
      </div>
      <button type="button" class="boton boton--secundario boton--chico" @click="asistente.reiniciar()" title="Borrar conversación">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icono-btn"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
        Limpiar
      </button>
    </header>

    <!-- Contenedor principal del chat -->
    <div class="ventana-chat tarjeta">
      
      <!-- Lista de Mensajes -->
      <div
        ref="lista"
        class="chat-historial"
        role="log"
        aria-live="polite"
        aria-label="Conversación con el asistente"
        tabindex="0"
      >
        <!-- Estado Vacío: Robot Animado -->
        <div v-if="asistente.mensajes.length === 0" class="estado-vacio animar-fade">
          <div class="robot-contenedor">
            <svg viewBox="0 0 120 120" class="robot-svg">
              <!-- Antena -->
              <line x1="60" y1="25" x2="60" y2="10" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
              <circle cx="60" cy="8" r="4" fill="var(--h, #b19cf9)"/>
              <!-- Cabeza -->
              <rect x="35" y="25" width="50" height="40" rx="12" fill="var(--superficie)" stroke="currentColor" stroke-width="4"/>
              <!-- Ojos (Animados) -->
              <circle cx="48" cy="40" r="4" fill="currentColor" class="ojo-parpadeo"/>
              <circle cx="72" cy="40" r="4" fill="currentColor" class="ojo-parpadeo"/>
              <!-- Sonrisa -->
              <path d="M 50 52 Q 60 58 70 52" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
              <!-- Cuerpo -->
              <rect x="40" y="70" width="40" height="35" rx="8" fill="none" stroke="currentColor" stroke-width="4"/>
              <!-- Brazo Izquierdo -->
              <path d="M 35 80 C 20 80, 20 100, 35 100" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
              <!-- Brazo Derecho (Saludando) -->
              <g class="brazo-saludo">
                <path d="M 85 80 C 105 60, 115 85, 100 95" fill="none" stroke="var(--h, #b19cf9)" stroke-width="5" stroke-linecap="round"/>
              </g>
            </svg>
          </div>
          <h2>¡Hola! Soy tu asistente de StudIA</h2>
          <p class="suave">Escribe tu duda abajo y te ayudaré a resolverla de inmediato.</p>
        </div>

        <!-- Burbujas de chat -->
        <div
          v-for="mensaje in asistente.mensajes"
          :key="mensaje.id"
          :class="['burbuja', `burbuja--${mensaje.rol}`]"
        >
          <div v-if="mensaje.rol === 'asistente'" class="burbuja-avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <div class="burbuja-contenido">
            <span class="solo-lectores">{{ mensaje.rol === 'usuario' ? 'Tú' : 'Asistente' }}:</span>
            <p v-if="mensaje.rol === 'usuario'" class="texto-libre">{{ mensaje.texto }}</p>
            <MensajeMarkdown v-else :texto="mensaje.texto" />
          </div>
        </div>

        <!-- Indicador de escribiendo -->
        <div v-if="asistente.enviando" class="burbuja burbuja--asistente" role="status">
          <div class="burbuja-avatar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <div class="burbuja-contenido puntos-contenedor">
            <span class="solo-lectores">El asistente está escribiendo…</span>
            <span class="puntos" aria-hidden="true"><i></i><i></i><i></i></span>
          </div>
        </div>
      </div>

      <!-- Alertas -->
      <p v-if="asistente.error" class="alerta m-2" role="alert">{{ asistente.error }}</p>

      <!-- Zona de Input -->
      <div class="zona-input">
        <form class="formulario-input" @submit.prevent="enviar">
          <label for="mensaje-asistente" class="solo-lectores">Tu mensaje</label>
          <textarea
            id="mensaje-asistente"
            ref="campo"
            v-model="texto"
            class="campo campo--flotante"
            rows="1"
            :maxlength="MAX_MENSAJE"
            placeholder="Pregúntale algo a tu asistente..."
            @keydown="alTeclear"
          ></textarea>
          
          <button 
            type="submit" 
            class="boton-enviar-icono" 
            :disabled="asistente.enviando || !texto.trim()"
            title="Enviar mensaje"
          >
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3.478 2.404a.75.75 0 00-.926.941l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.404z"></path></svg>
          </button>
        </form>
        
        <p class="suave nota">
          Enter para enviar · Shift + Enter para saltar línea · No compartas datos sensibles.
        </p>
      </div>

    </div>
  </section>
</template>

<style scoped>
/* Contenedor Principal */
.asistente-layout {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  max-width: 900px;
  margin: 0 auto;
  width: 100%;
}

.encabezado-moderno {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.encabezado__texto h1 {
  margin: 0 0 0.25rem 0;
  font-size: 1.7rem;
}

.encabezado__texto p {
  margin: 0;
  font-size: 0.95rem;
}

.icono-btn {
  width: 18px;
  height: 18px;
  margin-right: 0.25rem;
}

/* Tarjeta y Área de Chat */
.ventana-chat {
  display: flex;
  flex-direction: column;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid var(--borde, rgba(255,255,255,0.1));
  background: var(--superficie);
  height: calc(100vh - 200px);
  min-height: 500px;
  max-height: 700px;
}

.chat-historial {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  scroll-behavior: smooth;
}

/* Robot y Estado Vacío */
.estado-vacio {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 100%;
  padding: 2rem;
}

.robot-contenedor {
  width: 140px;
  height: 140px;
  color: var(--texto-suave, #888);
  margin-bottom: 1rem;
}

.estado-vacio h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.4rem;
  color: var(--texto, #fff);
}

/* Animaciones del Robot SVG */
.ojo-parpadeo {
  animation: parpadeo 4s infinite;
  transform-origin: center;
}

.brazo-saludo {
  transform-origin: 85px 80px;
  animation: saludar 2.5s infinite;
}

@keyframes parpadeo {
  0%, 46%, 48%, 100% { transform: scaleY(1); }
  47% { transform: scaleY(0.1); }
}

@keyframes saludar {
  0% { transform: rotate(0deg); }
  15% { transform: rotate(25deg); }
  30% { transform: rotate(-15deg); }
  45% { transform: rotate(25deg); }
  60% { transform: rotate(-15deg); }
  75% { transform: rotate(0deg); }
  100% { transform: rotate(0deg); }
}

/* Estilos de Burbujas */
.burbuja {
  display: flex;
  gap: 1rem;
  max-width: 88%;
  animation: entrar 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.burbuja p { margin: 0; }

.burbuja-avatar {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  background: var(--superficie-alta, rgba(255,255,255,0.05));
  border: 1px solid var(--borde, rgba(255,255,255,0.1));
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--h, #b19cf9);
}

.burbuja-avatar svg {
  width: 20px;
  height: 20px;
}

.burbuja-contenido {
  line-height: 1.6;
  font-size: 0.95rem;
}

.burbuja--asistente {
  align-self: flex-start;
  color: var(--texto, #fff);
}

.burbuja--usuario {
  align-self: flex-end;
  background: var(--morado, #7c3aed);
  color: #fff;
  padding: 0.85rem 1.25rem;
  border-radius: 20px 20px 4px 20px;
}

.burbuja--usuario .burbuja-contenido {
  font-size: 1rem;
}

/* Animación de carga (Puntos) */
.puntos-contenedor {
  display: flex;
  align-items: center;
  height: 100%;
}

.puntos {
  display: inline-flex;
  gap: 0.3rem;
}

.puntos i {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--h, #b19cf9);
  animation: saltar 1.4s infinite ease-in-out both;
}

.puntos i:nth-child(1) { animation-delay: -0.32s; }
.puntos i:nth-child(2) { animation-delay: -0.16s; }

@keyframes saltar {
  0%, 80%, 100% { transform: scale(0); }
  40% { transform: scale(1); }
}

@keyframes entrar {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animar-fade { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

/* Zona de Input y Formulario */
.zona-input {
  padding: 1rem 1.5rem;
  background: var(--superficie);
  border-top: 1px solid var(--borde, rgba(255,255,255,0.05));
}

.formulario-input {
  position: relative;
  display: flex;
  align-items: center;
}

.campo--flotante {
  flex: 1;
  resize: none;
  padding: 1rem 4rem 1rem 1.25rem; /* Espacio para el botón interno */
  border-radius: 24px;
  border: 1px solid var(--borde, rgba(255,255,255,0.2));
  background: var(--superficie-alta, rgba(255,255,255,0.03));
  color: var(--texto, #fff);
  font-family: inherit;
  font-size: 1rem;
  line-height: 1.5;
  transition: border-color 0.2s, box-shadow 0.2s;
  overflow: hidden;
}

.campo--flotante:focus {
  outline: none;
  border-color: var(--h, #b19cf9);
  box-shadow: 0 0 0 3px rgba(177, 156, 249, 0.15);
}

.boton-enviar-icono {
  position: absolute;
  right: 8px;
  bottom: 8px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--h, #b19cf9);
  color: var(--superficie, #000);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.2s, opacity 0.2s;
}

.boton-enviar-icono svg {
  width: 18px;
  height: 18px;
  margin-right: 2px; /* Centrado visual fino para el icono de enviar */
}

.boton-enviar-icono:disabled {
  background: var(--superficie-alta, rgba(255,255,255,0.1));
  color: var(--texto-suave, #666);
  cursor: not-allowed;
  transform: none;
}

.boton-enviar-icono:hover:not(:disabled) {
  transform: scale(1.05);
  opacity: 0.9;
}

.nota {
  margin: 0.75rem 0 0 0;
  font-size: 0.8rem;
  text-align: center;
}

.m-2 { margin: 0 1.5rem 1rem 1.5rem; }

@media (max-width: 640px) {
  .encabezado-moderno { flex-direction: column; align-items: stretch; }
  .ventana-chat { height: calc(100vh - 240px); }
  .burbuja { max-width: 95%; }
}
</style>