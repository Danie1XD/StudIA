<script setup>
import { computed } from 'vue'
import { parsearMarkdown } from '@/utils/markdown'
import TextoInline from './TextoInline.vue'

const props = defineProps({
  texto: { type: String, default: '' },
})

const bloques = computed(() => parsearMarkdown(props.texto))
</script>

<template>
  <div class="markdown">
    <template v-for="(bloque, i) in bloques" :key="i">
      <p v-if="bloque.tipo === 'parrafo'">
        <template v-for="(linea, j) in bloque.lineas" :key="j">
          <br v-if="j > 0" />
          <TextoInline :partes="linea" />
        </template>
      </p>
      <component :is="bloque.ordenada ? 'ol' : 'ul'" v-else>
        <li v-for="(item, j) in bloque.items" :key="j">
          <TextoInline :partes="item" />
        </li>
      </component>
    </template>
  </div>
</template>
