export const ROL_ETIQUETA = {
  alumno: 'Alumno',
  docente: 'Docente',
  admin: 'Administrador',
}

// nombre: texto del menú lateral · corto: texto de la barra inferior (móvil)
export const NAVEGACION = {
  alumno: [
    { nombre: 'Inicio', corto: 'Inicio', icono: 'inicio', a: '/alumno', exacto: true },
    { nombre: 'Mis materias', corto: 'Clases', icono: 'materias', a: '/alumno/materias' },
    { nombre: 'Mis tareas', corto: 'Tareas', icono: 'tareas', a: '/alumno/tareas' },
    { nombre: 'Promedios', corto: 'Notas', icono: 'promedios', a: '/alumno/promedios' },
    { nombre: 'Asistente', corto: 'IA', icono: 'asistente', a: '/alumno/asistente' },
    { nombre: 'Mi perfil', corto: 'Perfil', icono: 'perfil', a: '/alumno/perfil' },
  ],
  docente: [
    { nombre: 'Inicio', corto: 'Inicio', icono: 'inicio', a: '/docente', exacto: true },
    { nombre: 'Mis materias', corto: 'Clases', icono: 'materias', a: '/docente/materias' },
    { nombre: 'Entregas', corto: 'Entregas', icono: 'entregas', a: '/docente/tareas' },
    { nombre: 'Rúbricas', corto: 'Rúbricas', icono: 'rubricas', a: '/docente/rubricas' },
    { nombre: 'Asistente', corto: 'IA', icono: 'asistente', a: '/docente/asistente' },
    { nombre: 'Mi perfil', corto: 'Perfil', icono: 'perfil', a: '/docente/perfil' },
  ],
  admin: [
    { nombre: 'Usuarios', corto: 'Usuarios', icono: 'usuarios', a: '/admin/usuarios' },
    { nombre: 'Mi perfil', corto: 'Perfil', icono: 'perfil', a: '/admin/perfil' },
  ],
}
