<script setup>
import { onMounted, ref } from 'vue'
import { promediosApi } from '@/api/promedios'
import { mensajeDeError } from '@/api/errores'
import TarjetaPromedios from '@/components/promedios/TarjetaPromedios.vue'

const materias = ref([])
const reglas = ref({ vencidas_como_cero: true })
const cargando = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const respuesta = await promediosApi.mios()
    materias.value = respuesta.data
    reglas.value = respuesta.reglas ?? reglas.value
  } catch (e) {
    error.value = mensajeDeError(e)
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <section class="animar-fade">
    <header class="encabezado">
      <div>
        <h1>Promedios</h1>
        <p class="suave">
          Tus parciales y tu promedio final, calculados con tus calificaciones reales.
        </p>
      </div>
    </header>

    <p v-if="cargando" class="suave">Calculando tus promedios…</p>
    <p v-else-if="error" class="alerta" role="alert">{{ error }}</p>

    <div v-else-if="!materias.length" class="tarjeta vacio">
      <h2>Aún no estás en ninguna materia</h2>
      <p class="suave">Únete a una con el código de tu docente, desde «Mis materias».</p>
    </div>

    <template v-else>
      <details class="tarjeta como">
        <summary>¿Cómo se calcula?</summary>
        <ul>
          <li>
            Cada parcial es una regla de tres con los puntos de sus tareas: puntos obtenidos entre
            puntos posibles, por 100. Una tarea de 100 puntos pesa el doble que una de 50.
          </li>
          <li v-if="reglas.vencidas_como_cero">
            Cuentan las tareas ya calificadas y las que vencieron sin entrega, que valen 0.
          </li>
          <li v-else>
            Cuentan solo las tareas ya calificadas. Las que vencieron sin entrega no cuentan hasta
            que las entregues y se califiquen.
          </li>
          <li>Lo que ya entregaste pero tu docente aún no califica todavía no cuenta.</li>
          <li>
            El promedio final es el promedio de los parciales que ya tienen calificación, así que es
            provisional hasta que se califique todo.
          </li>
          <li>
            Los campos «Simular» son para probar números. No cambian tu calificación real, y
            «Restablecer» los regresa a tus valores.
          </li>
        </ul>
      </details>

      <div class="lista">
        <!-- Contenedor Acordeón para cada materia -->
        <details 
          v-for="datos in materias" 
          :key="datos.materia.id" 
          class="acordeon-materia"
        >
          <summary class="acordeon-materia__cabecera">
            <h3>{{ datos.materia.nombre }}</h3>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icono-flecha">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
          </summary>
          
          <div class="acordeon-materia__contenido">
            <TarjetaPromedios :datos="datos" />
          </div>
        </details>
      </div>
    </template>
  </section>
</template>

<style scoped>
.como {
  margin-bottom: 1.5rem;
}

.como summary {
  cursor: pointer;
  font-weight: 600;
  color: var(--h, #b19cf9);
  transition: color 0.2s ease;
}

.como summary:hover {
  color: var(--texto, #fff);
}

.como ul {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin: 0.75rem 0 0;
  padding-left: 1.25rem;
  color: var(--texto-suave);
  line-height: 1.5;
}

.lista {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* --- Estilos del Nuevo Acordeón de Materias --- */
.acordeon-materia {
  background: var(--superficie);
  border: 1px solid var(--borde, rgba(255,255,255,0.05));
  border-radius: 16px;
  overflow: hidden;
  transition: border-color 0.2s ease;
}

.acordeon-materia:hover {
  border-color: color-mix(in srgb, var(--h, #b19cf9) 40%, var(--borde));
}

.acordeon-materia__cabecera {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  cursor: pointer;
  background: var(--superficie-alta, rgba(255,255,255,0.02));
  list-style: none; /* Oculta la flecha nativa por defecto */
  user-select: none;
}

/* Ocultar la flecha nativa en navegadores webkit (Chrome/Safari) */
.acordeon-materia__cabecera::-webkit-details-marker {
  display: none;
}

.acordeon-materia__cabecera h3 {
  margin: 0;
  font-size: 1.15rem;
  color: var(--texto, #fff);
  font-weight: 600;
}

.icono-flecha {
  width: 20px;
  height: 20px;
  color: var(--texto-suave, #aaa);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s ease;
}

/* Cuando el <details> está abierto, rota la flecha y cambia su color */
.acordeon-materia[open] .icono-flecha {
  transform: rotate(180deg);
  color: var(--h, #b19cf9);
}

.acordeon-materia[open] .acordeon-materia__cabecera {
  border-bottom: 1px solid var(--borde, rgba(255,255,255,0.05));
}

.acordeon-materia__contenido {
  /* Dependiendo de cómo esté tu componente hijo, puedes ajustar este padding */
  padding: 1rem; 
  animation: deslizarAbajo 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

/* --- Animaciones Globales --- */
.animar-fade {
  animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes deslizarAbajo {
  from { 
    opacity: 0; 
    transform: translateY(-8px); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0); 
  }
}
</style>