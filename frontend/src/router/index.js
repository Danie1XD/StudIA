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
      path: '/dashboard/inicio',
      redirect: () => {
        const authStore = useAuthStore()
        return authStore.user?.rol === 'docente' ? { name: 'docente-inicio' } : { name: 'alumno-inicio' }
      }
    },
    {
      path: '/dashboard',
      component: DashboardView,
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
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
          path: 'evaluacion-ia/:id?',
          name: 'evaluacion-ia',
          component: () => import('../views/Dashboard/EvaluacionIAView.vue')
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

router.beforeEach((to, from) => {
  const authStore = useAuthStore()
  const token = localStorage.getItem('token')
  const rol = authStore.user?.rol

  if (to.meta.requiresAuth && !token) {
    return { name: 'login' }
  }

  if (to.meta.requiresGuest && token) {
    return rol === 'docente' ? { name: 'docente-inicio' } : { name: 'alumno-inicio' }
  }

  if (to.meta.role && rol && rol !== to.meta.role) {
    return rol === 'docente' ? { name: 'docente-inicio' } : { name: 'alumno-inicio' }
  }

  return true
})

export default router