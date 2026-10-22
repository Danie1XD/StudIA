<script setup>
import { useRouter } from 'vue-router'
import LogoStudIA from '@/components/LogoStudIA.vue'
import ToggleTema from '@/components/ToggleTema.vue'

// Datos que la institución debe completar antes de poner el sistema en producción
const RESPONSABLE = '[Nombre de la institución responsable]'
const CONTACTO = '[Correo de contacto para asuntos de privacidad]'
const PLAZO_CONSERVACION = '[Plazo de conservación definido por la institución]'
const ACTUALIZACION = 'Octubre de 2026'

const router = useRouter()

function volver() {
  if (window.history.state?.back) router.back()
  else router.push('/')
}
</script>

<template>
  <div class="privacidad">
    <header class="privacidad__barra">
      <div class="privacidad__marca">
        <LogoStudIA class="privacidad__logo" />
        <span>StudIA</span>
      </div>
      <div class="privacidad__acciones">
        <ToggleTema />
        <button type="button" class="boton boton--secundario" @click="volver">Volver</button>
      </div>
    </header>

    <main class="privacidad__documento tarjeta">
      <h1>Aviso de privacidad y términos de uso</h1>
      <p class="suave">Última actualización: {{ ACTUALIZACION }}</p>

      <h2>1. Responsable del tratamiento</h2>
      <p>
        {{ RESPONSABLE }} es responsable del tratamiento de los datos personales que se recaban a
        través de StudIA, el sistema universitario de gestión de tareas con apoyo de inteligencia
        artificial. Para cualquier asunto relacionado con este aviso, escribe a {{ CONTACTO }}.
      </p>

      <h2>2. Datos que tratamos</h2>
      <ul>
        <li>
          <strong>Datos de la cuenta:</strong> nombre, correo electrónico, rol (alumno, docente o
          administrador) y contraseña, que se guarda cifrada y no puede consultarse.
        </li>
        <li>
          <strong>Datos académicos:</strong> materias en las que participas, tareas, entregas
          (archivos y textos), calificaciones, retroalimentación y comentarios del foro de cada
          tarea.
        </li>
        <li>
          <strong>Conversaciones con el asistente:</strong> los mensajes que escribes se envían para
          generar la respuesta. La plataforma no guarda el historial de esas conversaciones.
        </li>
        <li>
          <strong>Datos técnicos:</strong> registros de funcionamiento y de errores, limitados a
          información técnica.
        </li>
      </ul>

      <h2>3. Para qué usamos tus datos</h2>
      <p>
        Para autenticarte y darte acceso según tu rol; permitir la inscripción en materias, la
        entrega de tareas y su calificación; generar sugerencias de evaluación y retroalimentación
        para los docentes; ofrecer el asistente virtual; y mantener la seguridad y el buen
        funcionamiento de la plataforma. No usamos tus datos con fines publicitarios ni los
        vendemos.
      </p>

      <h2>4. Uso de inteligencia artificial</h2>
      <ul>
        <li>
          <strong>Pre-evaluación de entregas.</strong> Para generar una pre-evaluación, el docente
          puede enviar una entrega a Gemini, el servicio de inteligencia artificial de Google. Se
          envían el trabajo (archivo o texto), las instrucciones de la tarea, la rúbrica si existe y
          tu nombre de pila. No se envían tus apellidos ni tu correo.
        </li>
        <li>
          <strong>La sugerencia es una ayuda, no una decisión.</strong> Solo la ve el docente, no es
          una calificación y puede contener errores. La calificación final y la retroalimentación
          oficial las decide siempre el docente.
        </li>
        <li>
          <strong>Asistente virtual.</strong> Además de tu mensaje, si eres alumno se comparten el
          título, la materia y la fecha límite de tus tareas pendientes, para que pueda responder
          con datos reales. El asistente orienta; no toma decisiones académicas.
        </li>
        <li>
          Google trata la información que recibe conforme a los términos y la política de privacidad
          del servicio de inteligencia artificial que utiliza la institución.
        </li>
      </ul>

      <h2>5. Con quién compartimos tus datos</h2>
      <p>
        Los docentes de tus materias ven tus entregas y calificaciones. Tus compañeros solo ven tu
        nombre y tu rol cuando comentas en el foro de una tarea. Los administradores de la
        plataforma gestionan las cuentas. Google participa como proveedor de inteligencia
        artificial, únicamente para lo descrito en la sección 4. No compartimos tus datos con otros
        terceros, salvo por obligación legal.
      </p>

      <h2>6. Seguridad</h2>
      <p>
        La plataforma controla el acceso por rol y por materia, guarda las contraseñas con cifrado
        irreversible, conserva los archivos en un almacenamiento privado que solo pueden descargar
        las personas con permiso, y cierra las sesiones abiertas cuando se cambia la contraseña o se
        desactiva una cuenta. Ningún sistema es completamente infalible, por eso te pedimos cuidar
        tus credenciales y no compartirlas.
      </p>

      <h2>7. Conservación y eliminación</h2>
      <p>
        Conservamos tus datos mientras tu cuenta esté activa y durante {{ PLAZO_CONSERVACION }}
        después, para mantener el historial académico. Mientras una entrega no esté calificada
        puedes anularla. Cuando un docente elimina una tarea o una materia, también se eliminan sus
        entregas y los archivos asociados. Las cuentas no se borran: se desactivan, para conservar
        el historial académico.
      </p>

      <h2>8. Tus derechos</h2>
      <p>
        Puedes solicitar el acceso, la rectificación o la cancelación de tus datos personales, así
        como oponerte a su tratamiento, conforme a la normativa aplicable. Para ejercer estos
        derechos escribe a {{ CONTACTO }}.
      </p>

      <h2>9. Uso adecuado de la plataforma</h2>
      <ul>
        <li>Tus credenciales son personales: no las compartas.</li>
        <li>
          Proporciona información veraz y entrega trabajos propios, conforme a las normas académicas
          de tu institución.
        </li>
        <li>
          No subas contenido ilícito, ofensivo o que viole derechos de terceros, y respeta a tus
          compañeros en el foro.
        </li>
        <li>
          Las respuestas del asistente son orientativas y pueden contener errores: confirma la
          información importante con tu docente o con las áreas oficiales de la institución.
        </li>
        <li>La institución puede desactivar las cuentas que incumplan estas condiciones.</li>
      </ul>

      <h2>10. Cambios a este aviso</h2>
      <p>Podemos actualizar este aviso. La fecha al inicio indica cuál es la versión vigente.</p>
    </main>
  </div>
</template>

<style scoped>
.privacidad {
  min-height: 100vh;
  padding: 1rem 1rem 4rem;
}

.privacidad__barra {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 800px;
  margin: 0 auto 1.5rem;
}

.privacidad__marca {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-family: var(--fuente-titulo);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--morado-texto);
}

.privacidad__logo {
  width: 32px;
  height: 32px;
}

.privacidad__acciones {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.privacidad__documento {
  max-width: 800px;
  margin: 0 auto;
  animation: entrar 0.45s var(--curva) both;
  padding: 1.5rem 1.75rem 2rem;
}

.privacidad__documento h2 {
  margin: 1.75rem 0 0.5rem;
}

.privacidad__documento p,
.privacidad__documento li {
  line-height: 1.65;
}

.privacidad__documento ul {
  padding-left: 1.25rem;
}

.privacidad__documento li + li {
  margin-top: 0.5rem;
}
</style>
