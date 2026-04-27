<template>
  <q-layout view="lHh Lpr lFf">

    <!-- ═══════ HEADER PREMIUM ═══════ -->
    <q-header class="bg-transparent" style="backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.05)">
      <q-toolbar class="q-px-md" style="height: 64px">
        <q-btn flat round dense icon="menu" @click="toggleDrawer" class="q-mr-sm text-grey-4" />

        <div class="row items-center no-wrap cursor-pointer" @click="$router.push('/dashboard')">
          <img src="https://i.ibb.co/8DW6rNGm/LOGO.jpg"
            style="height:34px; width:auto; object-fit:contain; margin-right:12px; border-radius:6px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.1));"
          >
          <div class="column justify-center hide-mobile">
            <span class="font-head text-weight-bold text-gradient-white" style="font-size:14px; letter-spacing:1px; line-height:1">
              SCHOOL <span style="color: #A10B13;">OF</span> TRAINING
            </span>
            <span class="font-mono text-grey-6 text-uppercase" style="font-size:8px; letter-spacing:3px">AVIATION</span>
          </div>
        </div>

        <q-space />

        <!-- 🔔 Notification Center / Vencimientos -->
        <q-btn flat round dense class="q-mr-md text-grey-5" @click="$router.push('/vencimientos')">
          <q-icon name="notifications" :color="vencimientosStore.criticos.length > 0 ? 'red-5' : 'grey-5'" />
          <q-badge
            v-if="vencimientosStore.totalAlertas > 0"
            color="red-5"
            floating
            rounded
            :label="vencimientosStore.totalAlertas"
          />
          <q-tooltip anchor="bottom middle" self="top middle">Vencimientos RAC</q-tooltip>
        </q-btn>

        <!-- 👤 User Profile Dropdown -->
        <q-btn flat round dense class="premium-avatar-btn">
          <q-avatar size="34px" class="glow-avatar" :style="!authStore.foto ? `background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})` : ''">
            <img v-if="authStore.foto" :src="authStore.foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%" />
            <span v-else class="text-white text-weight-bold" style="font-size:12px">{{ iniciales }}</span>
          </q-avatar>

          <q-menu anchor="bottom right" self="top right" class="premium-glass-card q-mt-sm" style="min-width:240px">
            <div class="q-pa-lg text-white">
              <div class="row items-center q-mb-md">
                 <q-avatar size="48px" class="q-mr-md" :style="!authStore.foto ? `background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})` : ''">
                   <img v-if="authStore.foto" :src="authStore.foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%" />
                   <span v-else class="text-h6 text-white">{{ iniciales }}</span>
                 </q-avatar>
                 <div>
                   <div class="text-weight-bold text-subtitle1">{{ authStore.nombre }}</div>
                   <div class="text-caption text-grey-5 font-mono text-uppercase">{{ authStore.rol }}</div>
                 </div>
              </div>

              <q-separator dark class="q-my-sm" color="rgba(255,255,255,0.1)" />
              
              <q-list dense padding class="text-grey-3">
                <q-item clickable v-ripple @click="$router.push('/perfil')" class="border-radius-8">
                  <q-item-section avatar><q-icon name="person_outline" size="20px" /></q-item-section>
                  <q-item-section>Mi Perfil Aeronáutico</q-item-section>
                </q-item>
                <q-item clickable v-ripple @click="$router.push('/configuracion')" class="border-radius-8">
                  <q-item-section avatar><q-icon name="settings" size="20px" /></q-item-section>
                  <q-item-section>Configuración</q-item-section>
                </q-item>
                <q-separator dark class="q-my-sm" color="rgba(255,255,255,0.1)" />
                <q-item clickable v-ripple @click="cerrarSesion" class="border-radius-8 text-red-4">
                  <q-item-section avatar><q-icon name="logout" size="20px" /></q-item-section>
                  <q-item-section>Cerrar Sesión</q-item-section>
                </q-item>
              </q-list>
            </div>
          </q-menu>
        </q-btn>
      </q-toolbar>
    </q-header>

    <!-- ═══════ SIDEBAR NAVIGATION ═══════ -->
    <q-drawer
      v-model="drawerAbierto"
      :mini="miniDrawer && $q.screen.gt.sm"
      :overlay="$q.screen.lt.md"
      :breakpoint="960"
      class="bg-transparent drawer-glass"
      :width="260"
      :mini-width="80"
      style="border-right: 1px solid rgba(255,255,255,0.05)"
    >
      <div class="column full-height">
        <!-- Drawer Header toggle -->
        <div v-show="$q.screen.gt.sm" class="flex justify-end q-pa-sm">
          <q-btn flat round dense :icon="miniDrawer ? 'navigate_next' : 'navigate_before'"
            @click="miniDrawer = !miniDrawer" color="grey-6" size="sm" />
        </div>

        <q-scroll-area class="col">
          <q-list padding class="q-px-sm">
            <template v-for="(item, idx) in menuFiltrado" :key="idx">
              <!-- Separador con título -->
              <div v-if="item.separador" class="q-mt-md q-mb-sm q-px-md font-mono text-grey-7 text-uppercase" style="font-size: 10px; letter-spacing: 1px">
                {{ item.sectionLabel || 'General' }}
              </div>

              <!-- Item de menú -->
              <q-item v-else
                :to="item.to"
                exact
                clickable v-ripple
                class="menu-item q-mb-sm"
              >
                <q-item-section avatar>
                  <q-icon :name="item.icono" size="22px" />
                </q-item-section>
                
                <q-item-section v-if="!miniDrawer || $q.screen.lt.md">
                  <q-item-label class="text-weight-medium" style="font-size: 13.5px">{{ item.label }}</q-item-label>
                  <q-item-label v-if="item.sublabel" caption class="text-grey-6" style="font-size: 10.5px">{{ item.sublabel }}</q-item-label>
                </q-item-section>

                <q-item-section v-if="item.badge && (!miniDrawer || $q.screen.lt.md)" side>
                  <q-badge rounded :color="item.badgeColor || 'primary'" :label="item.badge" />
                </q-item-section>

                <q-tooltip v-if="miniDrawer && $q.screen.gt.sm" anchor="center right" self="center left" :offset="[14, 0]" class="bg-primary">
                  {{ item.label }}
                </q-tooltip>
              </q-item>
            </template>
          </q-list>
        </q-scroll-area>

        <!-- Sidebar Footer -->
        <div class="q-pa-md footer-info font-mono" v-if="!miniDrawer || $q.screen.lt.md">
          <div class="row items-center no-wrap text-grey-7" style="font-size: 9px">
            <q-icon name="verified" size="12px" class="q-mr-xs" />
            RAC 141 CERTIFIED · v2.0
          </div>
        </div>
      </div>
    </q-drawer>

    <!-- ═══════ MAIN VIEWPORT ═══════ -->
    <q-page-container class="main-container" :class="$q.screen.lt.md ? 'mobile-container' : ''">
      <router-view v-slot="{ Component, route: routeProp }">
        <transition 
          name="fade-slide"
          mode="out-in"
          @before-enter="scrollToTop"
        >
          <component :is="Component" :key="routeProp.path" />
        </transition>
      </router-view>
    </q-page-container>

    <!-- ═══════ MOBILE NAV ═══════ -->
    <div v-if="$q.screen.lt.md" class="mobile-nav-bar">
      <q-tabs
        dense
        active-color="red-9"
        indicator-color="red-9"
        class="text-grey-6 full-width"
        style="height: 56px"
        no-caps
      >
        <q-route-tab v-for="item in menuMovil" :key="item.to" 
          :to="item.to" :icon="item.icono" :label="item.label"
          class="mobile-tab-item" />
      </q-tabs>
    </div>

  </q-layout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'
import { api } from 'boot/axios'

const $q        = useQuasar()
const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const vencimientosStore = useVencimientosStore()

const drawerAbierto    = ref($q.screen.gt.sm)
const miniDrawer       = ref(false)
const vuelosPendientes = ref(0)

const toggleDrawer = () => {
  if ($q.screen.lt.md) drawerAbierto.value = !drawerAbierto.value
  else miniDrawer.value = !miniDrawer.value
}

const scrollToTop = () => {
  window.scrollTo(0,0)
}

const iniciales = computed(() => {
  const n = authStore.nombre || 'USER'
  return n.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const rolStyle = computed(() => {
  const map = {
    estudiante:    { bg: '#3b82f6', accent: '#60a5fa' },
    instructor:    { bg: '#0d9488', accent: '#2dd4bf' },
    admin:         { bg: '#7c3aed', accent: '#a78bfa' },
    dir_ops:       { bg: '#ea580c', accent: '#fb923c' },
    mantenimiento: { bg: '#2563eb', accent: '#3b82f6' },
    auditor_uaeac: { bg: '#16a34a', accent: '#4ade80' },
  }
  return map[authStore.rol] || { bg: '#475569', accent: '#94a3b8' }
})

const menuCompleto = computed(() => {
  const v  = vencimientosStore.totalAlertas
  const vp = vuelosPendientes.value
  return [
    // ── General ──────────────────────────────────────────────────────
    { to: '/dashboard',     label: 'Dashboard',       icono: 'dashboard',          roles: ['all'], modulo: 'dashboard',    sublabel: 'Resumen operacional' },
    { to: '/vencimientos',  label: 'Alertas RAC',     icono: 'shutter_speed',      roles: ['all'], modulo: 'vencimientos', badge: v || null, badgeColor: 'red-5', sublabel: 'Vencimientos de documentos' },
    { to: '/mensajes',      label: 'Mensajes',        icono: 'forum',              roles: ['all'], modulo: 'mensajes',     sublabel: 'Comunicaciones internas' },
    { to: '/normatividad',  label: 'Normatividad',    icono: 'menu_book',          roles: ['all'], modulo: 'normatividad', sublabel: 'Reglamentos RAC · UAEAC' },

    // ── Operaciones de Vuelo ─────────────────────────────────────────
    { separador: true, sectionLabel: 'Operaciones de Vuelo' },
    { to: '/cronograma',    label: 'Mi Cronograma',   icono: 'assignment',         roles: ['all'], modulo: 'cronograma',  sublabel: 'Planes de vuelo programados', badge: vp || null, badgeColor: 'purple' },
    { to: '/reservas',      label: 'Programar Vuelo', icono: 'add_circle_outline', roles: ['admin', 'dir_ops', 'instructor'], modulo: 'reservas',   sublabel: 'Agendar actividad de instrucción' },
    { to: '/calendario',    label: 'Calendario',      icono: 'event_available',    roles: ['all'], modulo: 'calendario',  sublabel: 'Vista mensual de actividades' },
    { to: '/vuelo',         label: 'Bitácoras',       icono: 'flight_takeoff',     roles: ['all'], modulo: 'vuelo',       sublabel: 'Diario de vuelo RAC 91.417' },

    // ── Formación Académica ──────────────────────────────────────────
    { separador: true, sectionLabel: 'Formación Académica' },
    { to: '/academico',     label: 'Gestión Académica',icono: 'auto_stories',      roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'], modulo: 'academico',               sublabel: 'Materias, notas y programas' },
    { to: '/aula-virtual',  label: 'Aula Virtual',    icono: 'cast_for_education', roles: ['estudiante'],                                      modulo: 'aula-virtual',            sublabel: 'Mis cursos y exámenes' },
    { to: '/mi-progreso',   label: 'Mi Progreso',     icono: 'trending_up',        roles: ['estudiante', 'admin'],                             modulo: 'mi-progreso',             sublabel: 'Horas y avance de carrera' },
    { to: '/estudiantes',   label: 'Estudiantes',     icono: 'groups',             roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'], modulo: 'estudiantes',             sublabel: 'Expedientes y progreso' },
    { to: '/certificados',  label: 'Certificados',    icono: 'workspace_premium',  roles: ['all'],                                             modulo: 'certificados',            sublabel: 'Constancias RAC 141.77' },
    { to: '/endorsements',  label: 'Endorsements',    icono: 'verified',           roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'], modulo: 'endorsements',            sublabel: 'Primer vuelo solo' },
    { to: '/evaluaciones-instructor', label: 'Eval. Instructores', icono: 'rate_review', roles: ['admin', 'dir_ops', 'auditor_uaeac'],         modulo: 'evaluaciones-instructor', sublabel: 'RAC 65 — Competencias' },

    // ── Seguridad Operacional ────────────────────────────────────────
    { separador: true, sectionLabel: 'Seguridad Operacional' },
    { to: '/sms',                label: 'Reportes SMS',       icono: 'warning_amber',         roles: ['all'], modulo: 'sms',                sublabel: 'OACI Anexo 19' },
    { to: '/sms/erg',            label: 'Plan ERG',           icono: 'local_fire_department', roles: ['all'], modulo: 'sms-erg',            sublabel: 'Respuesta a emergencias' },
    { to: '/sms/capacitaciones', label: 'Capacitaciones SMS', icono: 'school',                roles: ['all'], modulo: 'sms-capacitaciones', sublabel: 'Cultura de seguridad' },

    // ── Flota y Mantenimiento ────────────────────────────────────────
    { separador: true, sectionLabel: 'Flota y Mantenimiento' },
    { to: '/aeronaves',     label: 'Aeronaves',       icono: 'flight',           roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'], modulo: 'aeronaves',     sublabel: 'Registro y gestión de flota' },
    { to: '/mantenimiento', label: 'Control MX',      icono: 'settings_suggest', roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'], modulo: 'mantenimiento', sublabel: 'RETAC · Intervenciones' },

    // ── Cumplimiento UAEAC ───────────────────────────────────────────
    { separador: true, sectionLabel: 'Cumplimiento UAEAC' },
    { to: '/cumplimiento',                 label: 'Cumplimiento RAC',    icono: 'gavel',           roles: ['admin', 'dir_ops', 'auditor_uaeac'], modulo: 'cumplimiento',                 sublabel: 'Documentos RAC 141' },
    { to: '/cumplimiento/enmiendas',       label: 'Enmiendas MOE/PIA',   icono: 'edit_document',   roles: ['admin', 'dir_ops', 'auditor_uaeac'], modulo: 'cumplimiento-enmiendas',       sublabel: 'Control de cambios' },
    { to: '/cumplimiento/correspondencia', label: 'Correspondencia',     icono: 'mark_email_read', roles: ['admin', 'dir_ops', 'auditor_uaeac'], modulo: 'cumplimiento-correspondencia', sublabel: 'Comunicaciones UAEAC' },
    { to: '/cumplimiento/reportes',        label: 'Reportes UAEAC',      icono: 'bar_chart',       roles: ['admin', 'dir_ops', 'auditor_uaeac'], modulo: 'cumplimiento-reportes',        sublabel: 'Estadísticas regulatorias' },

    // ── Administración ───────────────────────────────────────────────
    { separador: true, sectionLabel: 'Administración' },
    { to: '/instructores',  label: 'Instructores',    icono: 'supervisor_account',      roles: ['admin', 'dir_ops', 'auditor_uaeac'], modulo: 'instructores', sublabel: 'Talento I/P · Licencias' },
    { to: '/financiero',    label: 'Financiero',      icono: 'account_balance_wallet',  roles: ['admin', 'dir_ops'], modulo: 'financiero',  sublabel: 'Resumen financiero' },
    { to: '/matriculas',    label: 'Matrículas',      icono: 'how_to_reg',              roles: ['admin', 'dir_ops'], modulo: 'matriculas',  sublabel: 'Inscripciones y pagos' },
    { to: '/facturacion',   label: 'Facturación',     icono: 'receipt',                 roles: ['admin', 'dir_ops'], modulo: 'facturacion', sublabel: 'Facturas y cobros' },
    { to: '/prospectos',    label: 'CRM Prospectos',  icono: 'people_alt',              roles: ['admin', 'dir_ops'], modulo: 'prospectos',  sublabel: 'Leads e inscripciones' },
    { to: '/nomina',        label: 'Nómina',          icono: 'payments',                roles: ['admin', 'dir_ops'], modulo: 'nomina',      sublabel: 'Gestión de personal' },
    { to: '/gastos',        label: 'Gastos / Caja',   icono: 'receipt_long',            roles: ['admin', 'dir_ops'], modulo: 'gastos',      sublabel: 'Caja menor y presupuesto' },

    // ── Configuración ────────────────────────────────────────────────
    { separador: true, sectionLabel: 'Configuración' },
    { to: '/seguridad',     label: 'Acceso y Logs',   icono: 'vpn_key',            roles: ['admin'], modulo: 'seguridad', sublabel: 'Usuarios y auditoría' },
  ]
})

const menuFiltrado = computed(() =>
  menuCompleto.value.filter(item => {
    if (item.separador) return true
    const rol = authStore.rol
    if (item.roles && !item.roles.includes('all') && !item.roles.includes(rol)) return false
    return !item.modulo || authStore.tieneModulo(item.modulo)
  })
)

const menuMovil = computed(() => {
  const r = authStore.rol
  if (r === 'estudiante') return [
    { to: '/dashboard',    label: 'Inicio',      icono: 'home' },
    { to: '/cronograma',   label: 'Cronograma',  icono: 'event_note' },
    { to: '/aula-virtual', label: 'Aula',        icono: 'cast_for_education' },
    { to: '/mi-progreso',  label: 'Progreso',    icono: 'insights' },
    { to: '/vencimientos', label: 'Alertas',     icono: 'timer' },
  ]
  if (r === 'instructor') return [
    { to: '/dashboard',    label: 'Inicio',      icono: 'home' },
    { to: '/cronograma',   label: 'Cronograma',  icono: 'event_note' },
    { to: '/vuelo',        label: 'Reservas',    icono: 'flight' },
    { to: '/sms',          label: 'SMS',         icono: 'medical_services' },
    { to: '/vencimientos', label: 'Alertas',     icono: 'timer' },
  ]
  if (['admin', 'dir_ops'].includes(r)) return [
    { to: '/dashboard',    label: 'Inicio',      icono: 'home' },
    { to: '/vuelo',        label: 'Reservas',    icono: 'flight' },
    { to: '/academico',    label: 'Académico',   icono: 'school' },
    { to: '/sms',          label: 'SMS',         icono: 'medical_services' },
    { to: '/vencimientos', label: 'Alertas',     icono: 'timer' },
  ]
  return [
    { to: '/dashboard',    label: 'Inicio',      icono: 'home' },
    { to: '/vuelo',        label: 'Vuelo',       icono: 'flight' },
    { to: '/sms',          label: 'SMS',         icono: 'medical_services' },
    { to: '/vencimientos', label: 'Alertas',     icono: 'timer' },
  ]
})

async function cerrarSesion() {
  $q.dialog({
    title: 'Cerrar Sesión',
    message: '¿Estás seguro de que quieres salir del sistema?',
    cancel: { flat: true, label: 'No, Volver', color: 'grey-6' },
    ok: { label: 'Sí, Salir', color: 'red-5' },
    dark: true,
    class: 'premium-glass-card'
  }).onOk(async () => {
    await authStore.logout()
    router.push('/login')
  })
}

async function cargarVuelosPendientes() {
  try {
    const { data } = await api.get('/reservas/cronograma')
    const reservas = data.data ?? []
    if (authStore.rol === 'estudiante') {
      vuelosPendientes.value = reservas.filter(
        r => r.confirmacion_estudiante === 'pendiente' && r.estado === 'pendiente'
      ).length
    } else {
      // Para admin/dir_ops/instructor: vuelos que esperan confirmación del estudiante
      vuelosPendientes.value = reservas.filter(
        r => r.confirmacion_estudiante === 'pendiente'
      ).length
    }
  } catch { /* silencioso */ }
}

onMounted(() => {
  vencimientosStore.cargar()
  cargarVuelosPendientes()
})
</script>

<style lang="scss" scoped>
.drawer-glass {
  background: rgba(15, 23, 42, 0.4) !important;
  backdrop-filter: blur(20px);
}

.main-container {
  background: radial-gradient(circle at 50% 0%, rgba(161, 11, 19, 0.04) 0%, transparent 50%);
  min-height: 100vh;
}

// Add bottom padding on mobile to avoid content hidden behind nav bar
.mobile-container {
  padding-bottom: 72px !important;
}

.glow-avatar {
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.premium-avatar-btn:hover .glow-avatar {
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(161, 11, 19, 0.3);
}

.border-radius-8 { border-radius: 8px; }

.footer-info {
  background: linear-gradient(0deg, rgba(255,255,255,0.02) 0%, transparent 100%);
}

// Mobile Bottom Navigation Bar
.mobile-nav-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  width: 100%;
  // Support iPhone notch / safe area
  padding-bottom: env(safe-area-inset-bottom, 0px);
  background: rgba(5, 7, 10, 0.92);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-top: 1px solid rgba(255,255,255,0.07);
  z-index: 2000;
  box-shadow: 0 -8px 30px rgba(0,0,0,0.4);
}

.mobile-tab-item {
  font-size: 10px !important;
  font-family: 'JetBrains Mono', monospace !important;
  text-transform: uppercase !important;
  letter-spacing: 0.5px;
  min-width: 0 !important;
  padding: 0 4px !important;
}

/* Page Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.22s ease-out;
}
.fade-slide-enter-from { opacity: 0; transform: translateY(8px); }
.fade-slide-leave-to   { opacity: 0; transform: translateY(-8px); }

.hide-mobile {
  @media (max-width: 600px) { display: none !important; }
}
</style>
