<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { tareasApi } from '@/api/tareas'
import { useAuthStore } from '@/stores/auth'
import { formatearFechaHora } from '@/utils/fechas'
import { porRevisarDocente, proximasTareas } from '@/utils/inicio'
import IconoNav from '@/components/IconoNav.vue'

// La campana resume lo que requiere atención con los datos que ya existen:
// al alumno, sus próximas entregas; al docente, las entregas por revisar.
const auth = useAuthStore()

const abierto = ref(false)
const grupos = ref([])
const raiz = ref(null)

const esDocente = computed(() => auth.rol === 'docente')

const avisos = computed(() => {
  if (esDocente.value) {
    return porRevisarDocente(grupos.value, 5).map((t) => ({
      id: t.id,
      titulo: t.titulo,
      detalle: `${t.materia.nombre} · ${t.por_revisar} por revisar`,
      a: { name: 'docente.tarea', params: { id: t.id } },
    }))
  }

  return proximasTareas(grupos.value, 5).map((t) => ({
    id: t.id,
    titulo: t.titulo,
    detalle: `${t.materia.nombre} · ${formatearFechaHora(t.fecha_limite)}`,
    a: { name: 'alumno.tarea', params: { id: t.id } },
  }))
})

function alPulsarFuera(evento) {
  if (abierto.value && raiz.value && !raiz.value.contains(evento.target)) abierto.value = false
}

function alTeclear(evento) {
  if (evento.key === 'Escape') abierto.value = false
}

onMounted(async () => {
  document.addEventListener('pointerdown', alPulsarFuera)
  document.addEventListener('keydown', alTeclear)

  try {
    grupos.value = await tareasApi.misTareas()
  } catch {
    // Sin conexión o sin permisos: la campana simplemente queda vacía
  }
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', alPulsarFuera)
  document.removeEventListener('keydown', alTeclear)
})
</script>

<template>
  <div ref="raiz" class="campana">
    <button
      type="button"
      class="boton boton--fantasma boton--icono campana__boton"
      :aria-expanded="abierto ? 'true' : 'false'"
      aria-haspopup="true"
      :aria-label="avisos.length ? `Notificaciones, ${avisos.length} pendientes` : 'Notificaciones'"
      @click="abierto = !abierto"
    >
      <IconoNav nombre="campana" />
      <span v-if="avisos.length" class="campana__punto" aria-hidden="true"></span>
    </button>

    <Transition name="panel">
      <div v-if="abierto" class="campana__panel tarjeta">
        <h2>{{ esDocente ? 'Por revisar' : 'Próximas entregas' }}</h2>

        <ul v-if="avisos.length" class="campana__lista">
          <li v-for="aviso in avisos" :key="aviso.id">
            <RouterLink :to="aviso.a" class="campana__aviso" @click="abierto = false">
              <strong>{{ aviso.titulo }}</strong>
              <small>{{ aviso.detalle }}</small>
            </RouterLink>
          </li>
        </ul>
        <p v-else class="suave campana__vacio">
          {{ esDocente ? 'No tienes entregas por revisar.' : 'No tienes tareas pendientes.' }}
        </p>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.campana {
  position: relative;
}

.campana__boton {
  position: relative;
}

.campana__punto {
  position: absolute;
  top: 0.4rem;
  right: 0.45rem;
  width: 0.55rem;
  height: 0.55rem;
  border: 2px solid var(--fondo);
  border-radius: 50%;
  background: var(--peligro);
}

.campana__panel {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  z-index: 40;
  width: min(340px, calc(100vw - 2rem));
  padding: 1rem;
  transform-origin: top right;
}

.campana__panel h2 {
  margin: 0 0 0.6rem;
  font-size: 1rem;
}

.campana__lista {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.campana__aviso {
  display: flex;
  flex-direction: column;
  padding: 0.6rem 0.7rem;
  border-radius: var(--radio);
  color: var(--texto);
  text-decoration: none;
}

.campana__aviso:hover {
  background: var(--morado-suave);
}

.campana__aviso small {
  color: var(--texto-suave);
}

.campana__vacio {
  margin: 0;
}

.panel-enter-active,
.panel-leave-active {
  transition:
    opacity 0.15s,
    transform 0.18s var(--curva);
}

.panel-enter-from,
.panel-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}
</style>
