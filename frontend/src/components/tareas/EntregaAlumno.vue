<script setup>
import { computed, ref, useId } from 'vue'
import { entregasApi } from '@/api/entregas'
import { erroresDeCampos, mensajeDeError } from '@/api/errores'
import { entregadaTarde, formatearPuntos } from '@/utils/estados'
import { guardarArchivo, nombreDeEntrega } from '@/utils/descarga'
import VisorPdf from '@/components/VisorPdf.vue'
import { formatearFechaHora } from '@/utils/fechas'
import {
  armarFormularioEntrega,
  TIPOS_ENTREGA,
  validarArchivo,
  validarEntrega,
} from '@/utils/formularioEntrega'
import CampoTexto from '@/components/CampoTexto.vue'
import ModalConfirmacion from '@/components/ModalConfirmacion.vue'

import { ESTADO_CLASE, ESTADO_TEXTO } from '@/utils/estados'

const props = defineProps({
  tarea: Object,
  entrega: Object,
  estado: String,
  conRetraso: Boolean
})

const emit = defineEmits(['entregada', 'anulada'])

const idArchivo = useId()

const editando = ref(false)
const texto = ref('')
const archivo = ref(null)
const errorTexto = ref('')
const errorArchivo = ref('')
const errorGeneral = ref('')
const aviso = ref('')
const enviando = ref(false)

const confirmandoAnular = ref(false)
const anulando = ref(false)
const errorAnular = ref('')

const calificada = computed(() => props.entrega?.estado === 'calificado')
const mostrarFormulario = computed(() => !props.entrega || editando.value)
const hayArchivoPrevio = computed(() => Boolean(props.entrega?.tiene_archivo))
const conRetraso = computed(() => entregadaTarde(props.tarea, props.entrega))
const vencida = computed(
  () => Boolean(props.tarea.fecha_limite) && new Date(props.tarea.fecha_limite) < new Date(),
)

function limpiarErrores() {
  errorTexto.value = ''
  errorArchivo.value = ''
  errorGeneral.value = ''
}

function abrirEdicion() {
  aviso.value = ''
  limpiarErrores()
  texto.value = props.entrega?.contenido ?? ''
  archivo.value = null
  editando.value = true
}

function cancelarEdicion() {
  limpiarErrores()
  editando.value = false
}

function alElegirArchivo(evento) {
  const elegido = evento.target.files?.[0] ?? null

  archivo.value = null
  errorArchivo.value = validarArchivo(elegido)

  if (errorArchivo.value) {
    evento.target.value = ''
    return
  }

  archivo.value = elegido
}

async function enviar() {
  errorTexto.value = ''
  errorGeneral.value = ''

  const problema = validarEntrega({
    texto: texto.value,
    archivo: archivo.value,
    tieneArchivoPrevio: hayArchivoPrevio.value,
  })

  if (problema) {
    errorTexto.value = problema
    return
  }
  if (errorArchivo.value) return

  enviando.value = true

  try {
    const eraEdicion = Boolean(props.entrega)
    const entrega = await entregasApi.entregar(
      props.tarea.id,
      armarFormularioEntrega(texto.value, archivo.value),
    )

    editando.value = false
    aviso.value = eraEdicion ? 'Tu entrega se actualizó.' : 'Tu entrega se envió.'
    emit('entregada', entrega)
  } catch (e) {
    const campos = erroresDeCampos(e)
    errorTexto.value = campos.contenido ?? ''
    errorArchivo.value = campos.archivo ?? ''
    errorGeneral.value = campos.entrega ?? ''

    if (!errorTexto.value && !errorArchivo.value && !errorGeneral.value) {
      errorGeneral.value = mensajeDeError(e)
    }
  } finally {
    enviando.value = false
  }
}

const visor = ref(null)

function abrirPdf() {
  errorGeneral.value = ''
  const nombre = nombreDeEntrega(props.tarea.titulo, 'application/pdf')
  visor.value = {
    titulo: nombre,
    nombreArchivo: nombre,
    cargar: () => entregasApi.descargarArchivo(props.entrega.id),
  }
}

async function descargar() {
  errorGeneral.value = ''

  try {
    const blob = await entregasApi.descargarArchivo(props.entrega.id)
    guardarArchivo(blob, nombreDeEntrega(props.tarea.titulo, blob.type))
  } catch (e) {
    errorGeneral.value = mensajeDeError(e)
  }
}

async function anular() {
  anulando.value = true
  errorAnular.value = ''

  try {
    await entregasApi.anular(props.tarea.id)
    confirmandoAnular.value = false
    aviso.value = 'Tu entrega se anuló. Puedes entregar de nuevo cuando quieras.'
    emit('anulada')
  } catch (e) {
    errorAnular.value = mensajeDeError(e)
  } finally {
    anulando.value = false
  }
}
</script>

<template>
  <section class="entrega tarjeta">
    <div class="entrega__encabezado">
      <h2>Tu entrega</h2>

      <div class="entrega__insignias">
        <span :class="['insignia', ESTADO_CLASE[estado]]">{{ ESTADO_TEXTO[estado] }}</span>
        <span v-if="conRetraso" class="insignia insignia--aviso">Con retraso</span>
      </div>
    </div>

    <p v-if="aviso" class="alerta alerta--exito" role="status">{{ aviso }}</p>
    <p v-if="errorGeneral" class="alerta" role="alert">{{ errorGeneral }}</p>

    <!-- Ya entregó: resumen, y la nota si ya fue calificada -->
    <template v-if="entrega && !editando">
      <p>
        Entregaste el {{ formatearFechaHora(entrega.entregada_at) }}.
        <span v-if="conRetraso" class="insignia insignia--aviso">Con retraso</span>
      </p>

      <p v-if="entrega.contenido" class="texto-libre">{{ entrega.contenido }}</p>

      <div v-if="entrega.tiene_archivo" class="acciones acciones--izquierda">
        <button
          v-if="entrega.archivo_es_pdf"
          type="button"
          class="boton boton--secundario boton--chico"
          @click="abrirPdf"
        >
          Abrir mi PDF
        </button>
        <button
          type="button"
          :class="['boton', 'boton--chico', entrega.archivo_es_pdf ? 'boton--fantasma' : 'boton--secundario']"
          @click="descargar"
        >
          {{ entrega.archivo_es_pdf ? 'Descargar' : 'Descargar mi archivo' }}
        </button>
      </div>

      <template v-if="calificada">
        <h3>Calificación</h3>
        <p class="nota">
          <span class="nota__valor">{{ formatearPuntos(entrega.calificacion_final) }}</span>
          <span class="suave">de {{ tarea.puntaje_maximo }} puntos</span>
        </p>

        <h3>Retroalimentación de tu docente</h3>
        <p class="texto-libre">{{ entrega.retroalimentacion_final }}</p>
        <p class="suave">Calificada el {{ formatearFechaHora(entrega.calificada_at) }}.</p>
      </template>

      <template v-else>
        <p class="suave">
          Tu docente aún no la califica. Mientras tanto puedes modificarla o anularla.
        </p>
        <div class="acciones acciones--izquierda">
          <button type="button" class="boton boton--secundario" @click="abrirEdicion">
            Modificar entrega
          </button>
          <button type="button" class="boton boton--secundario" @click="confirmandoAnular = true">
            Anular entrega
          </button>
        </div><!-- Contenedor del título con flexbox para que queden lado a lado -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
  <h2 style="margin: 0;">Tu entrega</h2>

  <!-- Insignias alineadas a la derecha -->
  <div style="display: flex; gap: 0.5rem;">
    <span :class="['insignia', ESTADO_CLASE[estado]]">{{ ESTADO_TEXTO[estado] }}</span>
    <span v-if="conRetraso" class="insignia insignia--aviso">Con retraso</span>
  </div>
</div>

<p>Entregaste el 2 oct 2026, 9:36 p.m..</p>
<!-- Aquí estaba la "x", ¡bórrala! -->
      </template>
    </template>

    <!-- Formulario: primera entrega o modificación -->
    <form v-if="mostrarFormulario" class="formulario" novalidate @submit.prevent="enviar">
      <p v-if="vencida" class="alerta alerta--aviso" role="status">
        La fecha límite ya pasó. Puedes entregar, pero tu docente verá que fue con retraso.
      </p>

      <CampoTexto
        v-model="texto"
        etiqueta="Enlace o comentario"
        area
        :error="errorTexto"
        ayuda="Opcional si adjuntas un archivo. Si entregas un enlace, verifica que tu docente pueda abrirlo."
        maxlength="20000"
      />

      <div class="campo-grupo">
        <label :for="idArchivo" class="campo-grupo__etiqueta">Archivo</label>
        <input
          :id="idArchivo"
          class="campo"
          type="file"
          :accept="TIPOS_ENTREGA"
          :aria-invalid="errorArchivo ? 'true' : undefined"
          :aria-describedby="`${idArchivo}-ayuda`"
          @change="alElegirArchivo"
        />
        <p v-if="errorArchivo" class="campo-grupo__error" role="alert">{{ errorArchivo }}</p>
        <p :id="`${idArchivo}-ayuda`" class="campo-grupo__ayuda">
          PDF, Word, PowerPoint, Excel, ZIP o imagen. Máximo 20 MB.
          {{
            hayArchivoPrevio ? 'Ya tienes un archivo entregado; si eliges otro, lo reemplaza.' : ''
          }}
        </p>
      </div>

      <div class="acciones">
        <button
          v-if="entrega"
          type="button"
          class="boton boton--secundario"
          @click="cancelarEdicion"
        >
          Cancelar
        </button>
        <button type="submit" class="boton" :disabled="enviando">
          {{ enviando ? 'Enviando…' : entrega ? 'Guardar cambios' : 'Entregar' }}
        </button>
      </div>
    </form>

    <ModalConfirmacion
      v-if="confirmandoAnular"
      titulo="Anular entrega"
      texto-confirmar="Anular entrega"
      peligro
      :cargando="anulando"
      :error="errorAnular"
      @confirmar="anular"
      @cerrar="confirmandoAnular = false"
    >
      <p>
        Se eliminará tu entrega y el archivo que subiste. Podrás entregar de nuevo cuando quieras;
        si la fecha límite ya pasó, se marcará con retraso.
      </p>
    </ModalConfirmacion>

    <VisorPdf
      v-if="visor"
      :titulo="visor.titulo"
      :nombre-archivo="visor.nombreArchivo"
      :cargar="visor.cargar"
      @cerrar="visor = null"
    />
  </section>
</template>

<style scoped>
.entrega {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.entrega__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.entrega__insignias {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.entrega h2,
.entrega h3,
.entrega p {
  margin: 0;
}

.entrega h3 {
  font-size: 1rem;
}

.nota {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.nota__valor {
  color: var(--morado-texto);
  font-size: 2.25rem;
  font-weight: 700;
  line-height: 1;
}
</style>
