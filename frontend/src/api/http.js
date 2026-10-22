import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://localhost:8000/api/v1',
  headers: { Accept: 'application/json' },
  timeout: 120_000, // la pre-evaluación con IA puede tardar casi un minuto
})

let obtenerToken = () => null
let alSesionInvalida = () => {}

/** Se llama una vez al arrancar: así esta capa no depende de Pinia ni del router. */
export function configurarHttp(opciones) {
  obtenerToken = opciones.obtenerToken ?? obtenerToken
  alSesionInvalida = opciones.alSesionInvalida ?? alSesionInvalida
}

http.interceptors.request.use((config) => {
  const token = obtenerToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

const SIN_AVISO_DE_SESION = ['/login', '/logout']

http.interceptors.response.use(
  (respuesta) => respuesta,
  (error) => {
    const ruta = error.config?.url ?? ''
    // 401 = el token ya no vale (caducó, se cerró desde otro lado o la cuenta se desactivó)
    if (error.response?.status === 401 && !SIN_AVISO_DE_SESION.includes(ruta)) {
      alSesionInvalida()
    }
    return Promise.reject(error)
  },
)

export default http
