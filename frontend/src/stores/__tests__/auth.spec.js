import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from '../auth'
import { authApi } from '@/api/auth'
import { useAsistenteStore } from '../asistente'

vi.mock('@/api/auth', () => ({
  authApi: { login: vi.fn(), me: vi.fn(), logout: vi.fn() },
}))

const docente = { id: 2, nombre: 'Profesor Agustín', email: 'docente@studia.com', rol: 'docente' }

/** Almacenamiento en memoria: no depende de jsdom ni del localStorage experimental de Node. */
function crearAlmacenamiento() {
  const datos = new Map()
  return {
    getItem: (clave) => (datos.has(clave) ? datos.get(clave) : null),
    setItem: (clave, valor) => datos.set(clave, String(valor)),
    removeItem: (clave) => datos.delete(clave),
    clear: () => datos.clear(),
  }
}

describe('store de autenticación', () => {
  beforeEach(() => {
    vi.stubGlobal('localStorage', crearAlmacenamiento())
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.unstubAllGlobals()
  })

  it('cerrar sesión reinicia la conversación del asistente', async () => {
    authApi.login.mockResolvedValue({ user: docente, token: '1|abc' })
    authApi.logout.mockResolvedValue({})
    const auth = useAuthStore()
    const asistente = useAsistenteStore()
    await auth.iniciarSesion({ email: 'docente@studia.com', password: '12345678' })
    asistente.mensajes.push({ id: 99, rol: 'usuario', texto: 'Hola' })

    await auth.cerrarSesion()

    expect(asistente.mensajes).toHaveLength(1)
  })

  it('actualizarUsuario cambia los datos del usuario sin tocar el token', async () => {
    authApi.login.mockResolvedValue({ user: docente, token: '1|abc' })
    const auth = useAuthStore()
    await auth.iniciarSesion({ email: 'docente@studia.com', password: '12345678' })

    auth.actualizarUsuario({ ...docente, nombre: 'Agustín López' })

    expect(auth.usuario.nombre).toBe('Agustín López')
    expect(auth.token).toBe('1|abc')
  })

  it('iniciar sesión guarda el token y el usuario', async () => {
    authApi.login.mockResolvedValue({ user: docente, token: '1|abc' })
    const auth = useAuthStore()

    await auth.iniciarSesion({ email: 'docente@studia.com', password: '12345678' })

    expect(auth.autenticado).toBe(true)
    expect(auth.rol).toBe('docente')
    expect(localStorage.getItem('studia.token')).toBe('1|abc')
  })

  it('al recargar la página recupera al usuario con el token guardado', async () => {
    localStorage.setItem('studia.token', '1|abc')
    authApi.me.mockResolvedValue(docente)
    const auth = useAuthStore()

    await auth.restaurarSesion()

    expect(auth.autenticado).toBe(true)
    expect(auth.rol).toBe('docente')
    expect(auth.listo).toBe(true)
  })

  it('si el servidor responde 401, descarta el token', async () => {
    localStorage.setItem('studia.token', 'vencido')
    authApi.me.mockRejectedValue({ response: { status: 401 } })
    const auth = useAuthStore()

    await auth.restaurarSesion()

    expect(auth.autenticado).toBe(false)
    expect(localStorage.getItem('studia.token')).toBeNull()
    expect(auth.listo).toBe(true)
  })

  it('si no hay conexión, conserva el token para reintentar', async () => {
    localStorage.setItem('studia.token', '1|abc')
    authApi.me.mockRejectedValue(new Error('Network Error'))
    const auth = useAuthStore()

    await auth.restaurarSesion()

    expect(auth.autenticado).toBe(false)
    expect(localStorage.getItem('studia.token')).toBe('1|abc')
    expect(auth.listo).toBe(true)
  })

  it('cerrar sesión limpia todo aunque el servidor falle', async () => {
    authApi.login.mockResolvedValue({ user: docente, token: '1|abc' })
    authApi.logout.mockRejectedValue(new Error('Network Error'))
    const auth = useAuthStore()
    await auth.iniciarSesion({ email: 'docente@studia.com', password: '12345678' })

    await auth.cerrarSesion()

    expect(auth.autenticado).toBe(false)
    expect(localStorage.getItem('studia.token')).toBeNull()
  })
})
