import { describe, expect, it } from 'vitest'
import { resolverAcceso, rutaInicio } from '../guards'

const invitado = { autenticado: false, rol: null }
const alumno = { autenticado: true, rol: 'alumno' }
const ruta = (meta, fullPath = '/x') => ({ meta, fullPath })

describe('resolverAcceso', () => {
  it('manda a un invitado al login y recuerda a dónde iba', () => {
    const destino = ruta({ requiereSesion: true, roles: ['alumno'] }, '/alumno/tareas')
    expect(resolverAcceso(destino, invitado)).toEqual({
      name: 'login',
      query: { redirect: '/alumno/tareas' },
    })
  })

  it('no agrega redirect cuando el destino es la raíz', () => {
    const destino = ruta({ requiereSesion: true }, '/')
    expect(resolverAcceso(destino, invitado)).toEqual({ name: 'login', query: {} })
  })

  it('manda al inicio de su rol a quien ya inició sesión y abre el login', () => {
    expect(resolverAcceso(ruta({ soloInvitados: true }), alumno)).toBe('/alumno')
  })

  it('manda al inicio propio a quien pide una sección de otro rol', () => {
    const destino = ruta({ requiereSesion: true, roles: ['docente'] }, '/docente')
    expect(resolverAcceso(destino, alumno)).toBe('/alumno')
  })

  it('deja pasar a quien tiene el rol correcto', () => {
    const destino = ruta({ requiereSesion: true, roles: ['alumno'] }, '/alumno')
    expect(resolverAcceso(destino, alumno)).toBe(true)
  })

  it('deja pasar a cualquiera en una ruta pública', () => {
    expect(resolverAcceso(ruta({}), invitado)).toBe(true)
    expect(resolverAcceso({ fullPath: '/y' }, invitado)).toBe(true)
  })

  it('no provoca un bucle si el rol no tiene inicio conocido', () => {
    const rolNuevo = { autenticado: true, rol: 'coordinador' }
    expect(rutaInicio('coordinador')).toBe('/login')
    expect(resolverAcceso(ruta({ soloInvitados: true }), rolNuevo)).toBe(true)
  })
})
