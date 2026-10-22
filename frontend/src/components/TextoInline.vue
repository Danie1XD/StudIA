<script setup>
defineProps({
  partes: { type: Array, required: true },
})
</script>

<template>
  <template v-for="(parte, i) in partes" :key="i">
    <strong v-if="parte.negrita">{{ parte.texto }}</strong>
    <em v-else-if="parte.cursiva">{{ parte.texto }}</em>
    <code v-else-if="parte.codigo">{{ parte.texto }}</code>
    <template v-else>{{ parte.texto }}</template>
  </template>
</template>
