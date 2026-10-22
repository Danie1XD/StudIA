<script setup>
import { nombreDeArchivo } from '@/utils/descarga'
import { formatearFechaHora } from '@/utils/fechas'

defineProps({
  item: { type: Object, required: true },
  gestionable: { type: Boolean, default: false },
  enlace: { type: [String, Object], default: null }, // si viene, el título de la tarea es un enlace
})

defineEmits(['editar', 'eliminar', 'descargar', 'abrir'])
</script>

<template>
  <li :class="['item', 'tarjeta', { 'item--tarea': item.tipo === 'tarea' }]">
    <div class="item__encabezado">
      <span class="insignia">{{ item.tipo === 'tarea' ? 'Tarea' : 'Anuncio' }}</span>
      <strong v-if="item.tipo === 'tarea'" class="item__titulo">
        <RouterLink v-if="enlace" :to="enlace" class="item__enlace">{{ item.titulo }}</RouterLink>
        <template v-else>{{ item.titulo }}</template>
      </strong>
      <span v-else class="suave">
        {{ item.autor?.nombre }} · {{ formatearFechaHora(item.created_at) }}
      </span>
    </div>

    <p v-if="item.tipo === 'anuncio'" class="item__texto">{{ item.contenido }}</p>
    <template v-else>
      <p class="item__texto item__texto--corto suave">{{ item.instrucciones }}</p>
      <p class="item__meta suave">
        <span>
          {{
            item.fecha_limite
              ? `Entrega: ${formatearFechaHora(item.fecha_limite)}`
              : 'Sin fecha límite'
          }}
        </span>
        <span>{{ item.puntaje_maximo }} puntos</span>
        <span>Parcial {{ item.parcial }}</span>
      </p>
    </template>

    <!-- El PDF de la tarea: el nombre y «Abrir» lo muestran en el visor; «Descargar» lo guarda -->
    <div v-if="item.tipo === 'tarea' && item.tiene_archivo" class="adjunto">
      <button
        type="button"
        class="adjunto__nombre"
        :aria-label="`Abrir el PDF de ${item.titulo}`"
        @click="$emit('abrir', item)"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z" />
          <path d="M14 3v5h5" />
        </svg>
        <span>{{ nombreDeArchivo(item.titulo) }}</span>
      </button>
      <button
        type="button"
        class="boton boton--secundario boton--chico"
        :aria-label="`Abrir el PDF de ${item.titulo}`"
        @click="$emit('abrir', item)"
      >
        Abrir
      </button>
      <button
        type="button"
        class="boton boton--fantasma boton--chico"
        :aria-label="`Descargar el PDF de ${item.titulo}`"
        @click="$emit('descargar', item)"
      >
        Descargar
      </button>
    </div>

    <div v-if="gestionable" class="acciones">
        <button
          type="button"
          class="boton boton--secundario boton--chico"
          :aria-label="`Editar ${item.tipo === 'tarea' ? item.titulo : 'el anuncio'}`"
          @click="$emit('editar', item)"
        >
          Editar
        </button>
        <button
          type="button"
          class="boton boton--peligro boton--chico"
          :aria-label="`Eliminar ${item.tipo === 'tarea' ? item.titulo : 'el anuncio'}`"
          @click="$emit('eliminar', item)"
        >
          Eliminar
        </button>
    </div>
  </li>
</template>

<style scoped>
.item {
  position: relative;
  padding-left: 1.5rem;
}

.item::before {
  content: '';
  position: absolute;
  top: 0.8rem;
  bottom: 0.8rem;
  left: 0;
  width: 4px;
  border-radius: 0 4px 4px 0;
  background: var(--h, var(--morado));
}

.item--tarea::before {
  background: var(--h, var(--morado));
}

.item:not(.item--tarea)::before {
  background: var(--texto-suave);
  opacity: 0.45;
}

.item__encabezado {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
}

.item__titulo {
  font-family: var(--fuente-titulo);
  font-size: 1.05rem;
}

.item__enlace {
  color: var(--texto);
  text-decoration: none;
}

.item__enlace:hover {
  color: var(--morado-texto);
}

.item__texto {
  margin: 0.5rem 0 0;
  white-space: pre-line;
  overflow-wrap: anywhere;
}

.item__texto--corto {
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  line-clamp: 3;
}

.item__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin: 0.5rem 0 0;
  font-size: 0.9rem;
}

.acciones {
  margin-top: 0.75rem;
}

.adjunto {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.adjunto__nombre {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  max-width: 100%;
  padding: 0.35rem 0.75rem;
  border: 1px solid var(--borde);
  border-radius: 999px;
  background: var(--superficie-alta);
  color: var(--morado-texto);
  font: inherit;
  font-size: 0.9rem;
  cursor: pointer;
  transition: border-color 0.2s var(--curva);
}

.adjunto__nombre:hover {
  border-color: var(--morado-texto);
}

.adjunto__nombre svg {
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
}

.adjunto__nombre span {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
