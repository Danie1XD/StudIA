<template>
  <main class="no-encontrada">
    <h1>Página no encontrada</h1>
    <p class="suave">La dirección que buscas no existe.</p>
    <RouterLink to="/" class="boton">Volver al inicio</RouterLink>
  </main>
</template>

<style scoped>
.no-encontrada {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 3rem 1rem;
  text-align: center;
}

.no-encontrada h1,
.no-encontrada p {
  margin: 0;
}
</style>
