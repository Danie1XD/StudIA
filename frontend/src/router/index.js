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
        {
          path: 'inicio',
          name: 'inicio',
          component: () => import('../views/Dashboard/InicioView.vue')
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
          path: 'evaluacion-ia',
          name: 'evaluacion-ia',
          component: () => import('../views/Dashboard/EvaluacionIAView.vue'),
          meta: { role: 'docente' }
        },
        {
          path: 'perfil',
          name: 'perfil',
          component: () => import('../views/Dashboard/PerfilView.vue')
        }
      ]
    }
  ]
})

// --- GUARDIA DE SEGURIDAD Y ROLES ---
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
  } else if (to.meta.requiresGuest && token) {
    // Si ya está logueado y va al login, lo redirigimos según su rol actual
    const rol = authStore.user?.rol
    if (rol === 'docente') next({ name: 'docente-inicio' })
    else if (rol === 'alumno') next({ name: 'alumno-inicio' })
    else next({ name: 'inicio' })
  } else if (to.meta.role && authStore.user?.rol !== to.meta.role) {
    // Si intenta entrar a una sección que no le corresponde por rol, lo rebotamos a su inicio
    const rol = authStore.user?.rol
    if (rol === 'docente') next({ name: 'docente-inicio' })
    else next({ name: 'alumno-inicio' })
  } else {
    next()
  }
})

export default router