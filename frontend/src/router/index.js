import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      component: DashboardView,
      meta: { requiresAuth: true },
      children: [
        // Si entran directo a /dashboard, los redirigimos según su rol
        {
          path: '',
          redirect: () => {
            const authStore = useAuthStore()
            return authStore.user?.rol === 'docente' ? '/dashboard/docente' : '/dashboard/alumno'
          }
        },
        // Transformamos 'inicio' en una redirección automática para no importar archivos borrados
        {
          path: 'inicio',
          name: 'inicio',
          redirect: () => {
            const authStore = useAuthStore()
            return authStore.user?.rol === 'docente' ? { name: 'docente-inicio' } : { name: 'alumno-inicio' }
          }
        },
        {
          path: 'docente',
          name: 'docente-inicio',
          component: () => import('../views/Dashboard/DocenteInicioView.vue'),
          meta: { role: 'docente' }
        },
        {
          path: 'alumno',
          name: 'alumno-inicio',
          component: () => import('../views/Dashboard/AlumnoInicioView.vue'),
          meta: { role: 'alumno' }
        },
        {
          path: 'materias', 
          name: 'materias',
          component: () => import('../views/Dashboard/MateriasView.vue')
        },
        {
          path: 'tareas', 
          name: 'tareas',
          component: () => import('../views/Dashboard/TareasView.vue')
        },
        {
          path: 'tareas/:id', 
          name: 'tarea-detalle',
          component: () => import('../views/Dashboard/TareaDetalleView.vue')
        },
        {
          path: 'evaluacion-ia/:id', // <-- Añadimos /:id aquí
          name: 'evaluacion-ia',
          component: () => import('../views/Dashboard/EvaluacionIAView.vue'),
          meta: { role: 'docente' }
        },
        {
          path: 'perfil',
          name: 'perfil',
          component: () => import('../views/Dashboard/PerfilView.vue')
        },
        {
          path: 'materias/:id', 
          name: 'materia-detalle',
          component: () => import('../views/Dashboard/MateriaDetalleView.vue')
        }
      ]
    }
  ]
})

// --- GUARDIA DE SEGURIDAD Y ROLES CORREGIDO ---
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const authStore = useAuthStore()
  const rol = authStore.user?.rol

  // 1. Si la ruta pide autenticación y NO hay token, lo mandamos al Login
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'login' })
  } 
  
  // 2. Si intenta ir al login pero YA tiene token, lo mandamos a su inicio
  if (to.meta.requiresGuest && token) {
    if (rol === 'docente') return next({ name: 'docente-inicio' })
    return next({ name: 'alumno-inicio' })
  } 
  
  // 3. Si la ruta exige un rol y el usuario YA tiene su rol cargado pero no coincide
  if (to.meta.role && rol && rol !== to.meta.role) {
    if (rol === 'docente') return next({ name: 'docente-inicio' })
    return next({ name: 'alumno-inicio' })
  }

  // Todo correcto, permitir el paso
  next()
})

export default router