import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: LoginView,
      // Si ya tiene sesión abierta y quiere ir al login, lo mandamos al dashboard
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      component: DashboardView,
      // Esta meta indica que esta sección requiere estar logueado
      meta: { requiresAuth: true },
      children: [
        {
          path: 'inicio',
          name: 'inicio',
          component: () => import('../views/Dashboard/InicioView.vue')
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

// --- EL GUARDIA DE SEGURIDAD ---
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Si la ruta pide autenticación y NO hay token, lo mandamos al Login
  if (to.meta.requiresAuth && !token) {
    next({ name: 'login' })
  } 
  // Si es una ruta para invitados (como el login) y YA tiene token, lo mandamos al inicio
  else if (to.meta.requiresGuest && token) {
    next({ name: 'inicio' })
  } 
  else {
    next() // Todo bien, que pase
  }
})

export default router