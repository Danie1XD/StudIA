import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { resolverAcceso } from './guards'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/login' },
    {
      path: '/login',
      component: () => import('@/layouts/AuthLayout.vue'),
      meta: { soloInvitados: true },
      children: [
        { path: '', name: 'login', component: () => import('@/views/auth/LoginView.vue') },
      ],
    },
    {
      path: '/privacidad',
      name: 'privacidad',
      component: () => import('@/views/PrivacidadView.vue'),
    },

    {
      path: '/alumno',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiereSesion: true, roles: ['alumno'] },
      children: [
        {
          path: '',
          name: 'alumno.inicio',
          component: () => import('@/views/alumno/InicioView.vue'),
        },
        {
          path: 'materias',
          name: 'alumno.materias',
          component: () => import('@/views/materias/MateriasView.vue'),
        },
        {
          path: 'materias/:id',
          name: 'alumno.materia',
          component: () => import('@/views/materias/MateriaDetalleView.vue'),
          props: true,
        },
        {
          path: 'tareas',
          name: 'alumno.tareas',
          component: () => import('@/views/alumno/TareasPendientesView.vue'),
        },
        {
          path: 'tareas/:id',
          name: 'alumno.tarea',
          component: () => import('@/views/alumno/TareaDetalleView.vue'),
          props: true,
        },
        {
          path: 'promedios',
          name: 'alumno.promedios',
          component: () => import('@/views/alumno/PromediosAlumnoView.vue'),
        },
        {
          path: 'asistente',
          name: 'alumno.asistente',
          component: () => import('@/views/compartido/AsistenteView.vue'),
        },
        {
          path: 'perfil',
          name: 'alumno.perfil',
          component: () => import('@/views/compartido/PerfilView.vue'),
        },
      ],
    },

    {
      path: '/docente',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiereSesion: true, roles: ['docente'] },
      children: [
        {
          path: '',
          name: 'docente.inicio',
          component: () => import('@/views/docente/InicioView.vue'),
        },
        {
          path: 'materias',
          name: 'docente.materias',
          component: () => import('@/views/materias/MateriasView.vue'),
        },
        {
          path: 'materias/:id',
          name: 'docente.materia',
          component: () => import('@/views/materias/MateriaDetalleView.vue'),
          props: true,
        },
        {
          path: 'materias/:id/promedios',
          name: 'docente.promedios',
          component: () => import('@/views/docente/PromediosMateriaView.vue'),
          props: true,
        },
        {
          path: 'tareas',
          name: 'docente.tareas',
          component: () => import('@/views/docente/TareasView.vue'),
        },
        {
          path: 'tareas/:id',
          name: 'docente.tarea',
          component: () => import('@/views/docente/TareaEntregasView.vue'),
          props: true,
        },
        {
          path: 'tareas/:id/entregas/:entregaId',
          name: 'docente.entrega',
          component: () => import('@/views/docente/RevisionView.vue'),
          props: true,
        },
        {
          path: 'rubricas',
          name: 'docente.rubricas',
          component: () => import('@/views/docente/RubricasView.vue'),
        },
        {
          path: 'rubricas/nueva',
          name: 'docente.rubrica.nueva',
          component: () => import('@/views/docente/RubricaEditorView.vue'),
          props: true,
        },
        {
          path: 'rubricas/:id',
          name: 'docente.rubrica',
          component: () => import('@/views/docente/RubricaEditorView.vue'),
          props: true,
        },
        {
          path: 'asistente',
          name: 'docente.asistente',
          component: () => import('@/views/compartido/AsistenteView.vue'),
        },
        {
          path: 'perfil',
          name: 'docente.perfil',
          component: () => import('@/views/compartido/PerfilView.vue'),
        },
      ],
    },

    {
      path: '/admin',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { requiereSesion: true, roles: ['admin'] },
      children: [
        { path: '', name: 'admin.inicio', redirect: { name: 'admin.usuarios' } },
        {
          path: 'usuarios',
          name: 'admin.usuarios',
          component: () => import('@/views/admin/UsuariosView.vue'),
        },
        {
          path: 'perfil',
          name: 'admin.perfil',
          component: () => import('@/views/compartido/PerfilView.vue'),
        },
      ],
    },

    ...(import.meta.env.DEV
      ? [{ path: '/estilos', name: 'estilos', component: () => import('@/views/EstilosView.vue') }]
      : []),
    {
      path: '/:pathMatch(.*)*',
      name: 'no-encontrada',
      component: () => import('@/views/NotFoundView.vue'),
    },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  return resolverAcceso(to, { autenticado: auth.autenticado, rol: auth.rol })
})

export default router
