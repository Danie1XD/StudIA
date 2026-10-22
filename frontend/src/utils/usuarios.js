import { esCorreo, MIN_PASSWORD } from './perfil'

export const POR_PAGINA = 15

export const ROLES = [
  { valor: 'alumno', texto: 'Alumno' },
  { valor: 'docente', texto: 'Docente' },
  { valor: 'admin', texto: 'Administrador' },
]

export const FILTRO_ROLES = [{ valor: '', texto: 'Todos los roles' }, ...ROLES]

export const FILTRO_ESTADOS = [
  { valor: '', texto: 'Todos' },
  { valor: '1', texto: 'Activos' },
  { valor: '0', texto: 'Desactivados' },
]

/** Parámetros de la consulta: solo los filtros que se usan. */
export function armarParametros({ rol = '', activo = '', buscar = '', pagina = 1 } = {}) {
  const params = { por_pagina: POR_PAGINA }

  if (rol) params.rol = rol
  if (activo !== '') params.activo = activo
  if (buscar.trim()) params.buscar = buscar.trim()
  if (pagina > 1) params.page = pagina

  return params
}

/** `propio`: el administrador se edita a sí mismo, así que no se valida rol ni contraseña. */
export function validarUsuario(form, { edicion = false, propio = false } = {}) {
  const errores = {}

  if (!form.nombre.trim()) errores.nombre = 'El nombre es obligatorio.'

  const correo = form.email.trim()
  if (!correo) errores.email = 'El correo es obligatorio.'
  else if (!esCorreo(correo)) errores.email = 'Escribe un correo válido.'

  if (!propio) {
    if (!ROLES.some((r) => r.valor === form.rol)) errores.rol = 'Elige un rol.'

    if (!edicion && !form.password) {
      errores.password = 'La contraseña es obligatoria.'
    } else if (form.password && form.password.length < MIN_PASSWORD) {
      errores.password = `La contraseña debe tener al menos ${MIN_PASSWORD} caracteres.`
    }
  }

  return errores
}

/** Lo que se manda al servidor: al editar, la contraseña solo si se escribió; el propio usuario, ni rol ni contraseña. */
export function armarDatosUsuario(form, { edicion = false, propio = false } = {}) {
  const datos = { nombre: form.nombre.trim(), email: form.email.trim() }

  if (!propio) datos.rol = form.rol
  if (!propio && (!edicion || form.password !== '')) datos.password = form.password

  return datos
}

export function textoMaterias(usuario) {
  if (usuario.rol === 'docente') {
    const n = usuario.materias_impartidas_count ?? 0
    return n === 1 ? 'Imparte 1 materia' : `Imparte ${n} materias`
  }

  if (usuario.rol === 'alumno') {
    const n = usuario.materias_inscritas_count ?? 0
    return n === 1 ? 'Inscrito en 1 materia' : `Inscrito en ${n} materias`
  }

  return '—'
}
