// src/router/index.js
import { route } from 'quasar/wrappers'
import { createRouter, createWebHashHistory } from 'vue-router'
import { createPinia, setActivePinia } from 'pinia'
import { useAuthStore } from 'store/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('pages/auth/LoginPage.vue'),
    meta: { publica: true },
  },
  {
    path: '/recuperar-password',
    name: 'recuperar-password',
    component: () => import('pages/auth/RecuperarPasswordPage.vue'),
    meta: { publica: true },
  },
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    meta: { requiereAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'dashboard', component: () => import('pages/dashboard/DashboardPage.vue') },
      { path: 'vencimientos', name: 'vencimientos', component: () => import('pages/dashboard/VencimientosPage.vue') },

      // 01: Seguridad
      { path: 'seguridad', name: 'seguridad', component: () => import('pages/admin/SeguridadPage.vue'), meta: { roles: ['admin'] } },

      // 02: Académico
      { path: 'academico', name: 'academico', component: () => import('pages/academico/AcademicoPage.vue'), meta: { roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'] } },
      { path: 'aula-virtual', name: 'aula-virtual', component: () => import('pages/academico/AulaVirtualPage.vue'), meta: { roles: ['estudiante'] } },

      // 03: Vuelo y Bitácoras
      { path: 'vuelo', name: 'vuelo', component: () => import('pages/vuelo/VueloPage.vue') },
      { path: 'bitacoras', redirect: '/vuelo' }, // Redirección legado
      { path: 'vuelo/nueva-bitacora', name: 'nueva-bitacora', component: () => import('pages/bitacoras/BitacoraNuevaPage.vue') },
      { path: 'vuelo/:id', name: 'vuelo-detalle', component: () => import('pages/vuelo/BitacoraDetallePage.vue') },

      // 04: Instructores
      { path: 'instructores', name: 'instructores', component: () => import('pages/instructores/InstructoresPage.vue'), meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },
      { path: 'instructores/:id', name: 'instructor-detalle', component: () => import('pages/instructores/InstructorDetallePage.vue'), meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },

      // 05: Operaciones y Mantenimiento
      { path: 'mantenimiento', name: 'mantenimiento', component: () => import('pages/mantenimiento/MantenimientoPage.vue'), meta: { roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'] } },

      // 06: SMS
      { path: 'sms', name: 'sms', component: () => import('pages/sms/SmsPage.vue') },
      { path: 'sms/nuevo-reporte', name: 'sms-nuevo', component: () => import('pages/sms/NuevoReportePage.vue') },


      // 07: Cumplimiento
      { path: 'cumplimiento', name: 'cumplimiento', component: () => import('pages/cumplimiento/CumplimientoPage.vue'), meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },

      // 08: Administrativo y Financiero
      { path: 'financiero', name: 'financiero', component: () => import('pages/admin/AdministrativoPage.vue'), meta: { roles: ['admin', 'dir_ops'] } },

      { path: 'normatividad', name: 'normatividad', component: () => import('pages/normatividad/NormatividadPage.vue') },
      { path: 'configuracion', name: 'configuracion', component: () => import('pages/admin/ConfiguracionPage.vue') },
      { path: 'perfil',         name: 'perfil',         component: () => import('pages/auth/PerfilPage.vue') },

      // 09: Estudiantes (directorio + expediente detalle)
      { path: 'estudiantes',     name: 'estudiantes',     component: () => import('pages/estudiantes/EstudiantesPage.vue'),    meta: { roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'] } },
      { path: 'estudiantes/:id', name: 'estudiante-detalle', component: () => import('pages/estudiantes/EstudianteDetallePage.vue'), meta: { roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'] } },
      { path: 'mi-progreso',    name: 'mi-progreso',    component: () => import('pages/estudiantes/MiProgresPage.vue'),       meta: { roles: ['estudiante', 'admin'] } },

      // 10: Aeronaves (flota + detalle)
      { path: 'aeronaves',      name: 'aeronaves',      component: () => import('pages/aeronaves/AeronavesPage.vue'),         meta: { roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'] } },
      { path: 'aeronaves/:id',  name: 'aeronave-detalle', component: () => import('pages/aeronaves/AeronaveDetallePage.vue'), meta: { roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'] } },

      // 11: Reservas y Calendario
      { path: 'reservas',       name: 'reservas',       component: () => import('pages/reservas/ReservasPage.vue') },
      { path: 'calendario',     name: 'calendario',     component: () => import('pages/reservas/CalendarioPage.vue') },
      { path: 'cronograma',     name: 'cronograma',     component: () => import('pages/reservas/CronogramaPage.vue'), meta: { roles: ['estudiante', 'instructor', 'admin', 'dir_ops'] } },

      // 12: Admin Financiero (sub-módulos)
      { path: 'matriculas',     name: 'matriculas',     component: () => import('pages/admin/MatriculasPage.vue'),            meta: { roles: ['admin', 'dir_ops'] } },
      { path: 'facturacion',    name: 'facturacion',    component: () => import('pages/admin/FacturacionPage.vue'),           meta: { roles: ['admin', 'dir_ops'] } },

      // 13: SMS (form de reporte)
      { path: 'sms/reporte',    name: 'sms-reporte',    component: () => import('pages/sms/SmsReporteFormPage.vue') },

      // 14: Certificados y Constancias
      { path: 'certificados',   name: 'certificados',   component: () => import('pages/academico/CertificadosPage.vue'), meta: { roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac', 'estudiante'] } },

      // 15: Mensajería interna
      { path: 'mensajes',       name: 'mensajes',       component: () => import('pages/mensajes/MensajesPage.vue') },

      // 16: CRM Prospectos
      { path: 'prospectos',     name: 'prospectos',     component: () => import('pages/admin/ProspectosPage.vue'),    meta: { roles: ['admin', 'dir_ops'] } },

      // 17: Nómina
      { path: 'nomina',         name: 'nomina',         component: () => import('pages/admin/NominaPage.vue'),        meta: { roles: ['admin', 'dir_ops'] } },

      // 18: Gastos Operativos / Caja Menor
      { path: 'gastos',         name: 'gastos',         component: () => import('pages/admin/GastosPage.vue'),        meta: { roles: ['admin', 'dir_ops'] } },

      // 19: SMS — ERG y Capacitaciones
      { path: 'sms/erg',              name: 'sms-erg',              component: () => import('pages/sms/ErgPage.vue') },
      { path: 'sms/capacitaciones',   name: 'sms-capacitaciones',   component: () => import('pages/sms/CapacitacionesSmsPage.vue') },

      // 20: Cumplimiento — Enmiendas, Correspondencia, Reportes UAEAC
      { path: 'cumplimiento/enmiendas',      name: 'enmiendas',      component: () => import('pages/cumplimiento/EnmiendasPage.vue'),      meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },
      { path: 'cumplimiento/correspondencia',name: 'correspondencia', component: () => import('pages/cumplimiento/CorrespondenciaPage.vue'), meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },
      { path: 'cumplimiento/reportes',       name: 'reportes-uaeac', component: () => import('pages/cumplimiento/ReportesUaeacPage.vue'),   meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },

      // 21: Académico — Endorsements y Evaluaciones Instructor
      { path: 'endorsements',          name: 'endorsements',          component: () => import('pages/academico/EndorsementsPage.vue'),           meta: { roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'] } },
      { path: 'evaluaciones-instructor',name: 'evaluaciones-instructor',component: () => import('pages/admin/EvaluacionesInstructorPage.vue'),   meta: { roles: ['admin', 'dir_ops', 'auditor_uaeac'] } },
    ],
  },
  { path: '/:catchAll(.*)*', component: () => import('pages/ErrorNotFound.vue') },
]

export default route(function ({ store }) {
  let pinia = store
  if (!pinia) {
    pinia = createPinia()
    setActivePinia(pinia)
  }

  const router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    history: createWebHashHistory(),
    routes,
  })

  router.beforeEach((to) => {
    const auth = useAuthStore(pinia)
    if (to.meta.publica) {
      if (auth.autenticado && to.name === 'login') return { name: 'dashboard' }
      return true
    }
    if (!auth.autenticado) return { name: 'login', query: { redirect: to.fullPath } }
    if (to.meta.roles?.length && !to.meta.roles.includes(auth.rol)) return { name: 'dashboard' }
    return true
  })

  return router
})
