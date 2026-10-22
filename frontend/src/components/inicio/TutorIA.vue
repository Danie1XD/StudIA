<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAsistenteStore } from '@/stores/asistente'
import { useAuthStore } from '@/stores/auth'
import IconoNav from '@/components/IconoNav.vue'

const auth = useAuthStore()
const asistente = useAsistenteStore()
const router = useRouter()

const pregunta = ref('')

function preguntar() {
  asistente.dejarPregunta(pregunta.value) // se envía al llegar al asistente
  router.push(`/${auth.rol}/asistente`)
}
</script>

<template>
  <section class="tutor" aria-labelledby="tutor-titulo">
    <IconoNav nombre="asistente" class="tutor__icono" />
    <h2 id="tutor-titulo">Tutor IA</h2>

    <form class="tutor__form" @submit.prevent="preguntar">
      <label for="pregunta-tutor" class="solo-lectores">Pregúntale algo al tutor</label>
      <input
        id="pregunta-tutor"
        v-model="pregunta"
        class="tutor__campo"
        type="text"
        maxlength="500"
        placeholder="Pregunta a la IA…"
        autocomplete="off"
      />
      <button type="submit" class="tutor__boton">Preguntar a la IA</button>
    </form>
  </section>
</template>

<style scoped>
.tutor {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 1.6rem 1.25rem;
  overflow: hidden;
  border-radius: var(--radio-grande);
  background:
    radial-gradient(circle at 85% 0%, rgba(122, 71, 255, 0.6), transparent 55%),
    linear-gradient(160deg, #1d1440 0%, #2b1670 100%);
  box-shadow: var(--sombra);
  color: #ffffff;
  text-align: center;
}

.tutor__icono {
  width: 2.4rem;
  height: 2.4rem;
  color: #d4c4ff;
  animation: destello 3s ease-in-out infinite;
}

@keyframes destello {
  0%,
  100% {
    transform: scale(1) rotate(0deg);
  }
  50% {
    transform: scale(1.12) rotate(8deg);
  }
}

.tutor h2 {
  margin: 0;
  font-size: 1.3rem;
}

.tutor__form {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  width: 100%;
}

.tutor__campo {
  width: 100%;
  min-height: 2.7rem;
  padding: 0.6rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.28);
  border-radius: var(--radio);
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  font: inherit;
  text-align: center;
}

.tutor__campo::placeholder {
  color: rgba(255, 255, 255, 0.78);
}

.tutor__campo:focus-visible,
.tutor__boton:focus-visible {
  outline: 2px solid #ffffff;
  outline-offset: 2px;
}

.tutor__boton {
  min-height: 2.6rem;
  padding: 0.6rem 1rem;
  border: 0;
  border-radius: var(--radio);
  background: #ffffff;
  color: #3b1a9e;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
  transition:
    background-color 0.15s,
    transform 0.1s;
}

.tutor__boton:hover {
  background: #efe9ff;
}

.tutor__boton:active {
  transform: translateY(1px);
}
</style>
