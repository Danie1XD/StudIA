// Aplica el tema guardado antes de que cargue la página (evita un destello del tema equivocado)
;(function () {
  let tema = 'oscuro'

  try {
    const guardado = localStorage.getItem('studia.tema')
    if (guardado === 'claro' || guardado === 'oscuro') tema = guardado
  } catch {
    // El almacenamiento puede estar bloqueado (modo privado): se queda el tema por defecto
  }

  document.documentElement.dataset.tema = tema
})()