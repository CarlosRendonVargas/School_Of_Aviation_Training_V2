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
          <q-avatar size="34px" class="glow-avatar" :style="`background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})`">
            <span class="text-white text-weight-bold" style="font-size:12px">{{ iniciales }}</span>
          </q-avatar>
          
          <q-menu anchor="bottom right" self="top right" class="premium-glass-card q-mt-sm" style="min-width:240px">
            <div class="q-pa-lg text-white">
              <div class="row items-center q-mb-md">
                 <q-avatar size="48px" class="q-mr-md" :style="`background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})`">
                   <span class="text-h6 text-white">{{ iniciales }}</span>
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
                <q-item clickable v-ripple @click="$router.push('/perfil')" class="border-radius-8">
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
    <q-page-container class="main-container">
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
    <div v-if="$q.screen.lt.md" class="mobile-nav-bar glass-morphism">
      <q-tabs
        dense
        active-color="primary"
        indicator-color="transparent"
        class="text-grey-6 full-width"
        style="height: 60px"
      >
        <q-route-tab v-for="item in menuMovil" :key="item.to" 
          :to="item.to" :icon="item.icono" :label="item.label" no-caps />
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

const $q        = useQuasar()
const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const vencimientosStore = useVencimientosStore()

const drawerAbierto = ref($q.screen.gt.sm)
const miniDrawer    = ref(false)

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
  const v = vencimientosStore.totalAlertas
  return [
    { to: '/dashboard',     label: 'Dashboard',    icono: 'dashboard', roles: ['all'] },
    { to: '/vencimientos',  label: 'Alertas RAC',  icono: 'shutter_speed', roles: ['all'], badge: v || null, badgeColor: 'red-5' },
    
    { separador: true, sectionLabel: 'Operatividad' },
    { to: '/vuelo',         label: 'Vuelo y Diario',icono: 'flight_takeoff', roles: ['all'], sublabel: 'Bitácoras RAC 91.417' },
    { to: '/academico',     label: 'Académico',    icono: 'auto_stories', roles: ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'] },
    { to: '/aula-virtual',  label: 'Aula Virtual', icono: 'school', roles: ['estudiante'] },
    { to: '/sms',           label: 'Segur. SMS',   icono: 'local_hospital', roles: ['all'], sublabel: 'Seguridad Operacional' },
    
    { separador: true, sectionLabel: 'Administración' },
    { to: '/financiero',    label: 'Financiero',   icono: 'account_balance_wallet', roles: ['admin', 'dir_ops'] },
    { to: '/mantenimiento', label: 'Flota MX',     icono: 'settings_suggest', roles: ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'] },
    { to: '/instructores',  label: 'Talento I/P',  icono: 'supervisor_account', roles: ['admin', 'dir_ops'] },
    
    { separador: true, sectionLabel: 'Configuración' },
    { to: '/seguridad',     label: 'Acceso y Logs', icono: 'vpn_key', roles: ['admin'] },
  ]
})

const menuFiltrado = computed(() =>
  menuCompleto.value.filter(item => {
    if (item.separador) return true
    return (item.roles || []).includes('all') || (item.roles || []).includes(authStore.rol)
  })
)

const menuMovil = computed(() => {
  const r = authStore.rol
  const items = [{ to: '/dashboard', label: 'Inicio', icono: 'home' }]
  if (['admin', 'dir_ops'].includes(r)) items.push({ to: '/financiero', label: 'Admin', icono: 'payments' })
  items.push({ to: '/vuelo', label: 'Vuelo', icono: 'flight' })
  items.push({ to: '/sms', label: 'SMS', icono: 'medical_services' })
  items.push({ to: '/vencimientos', label: 'Alertas', icono: 'timer' })
  return items.slice(0, 5)
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

onMounted(() => {
  vencimientosStore.cargar()
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

.glow-avatar {
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.premium-avatar-btn:hover .glow-avatar {
  transform: scale(1.1);
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

.border-radius-8 { border-radius: 8px; }

.footer-info {
  background: linear-gradient(0deg, rgba(255,255,255,0.02) 0%, transparent 100%);
}

.mobile-nav-bar {
  position: fixed;
  bottom: 0px;
  width: 100%;
  height: 60px;
  background: rgba(5, 7, 10, 0.85);
  backdrop-filter: blur(15px);
  border-top: 1px solid rgba(255,255,255,0.06);
  z-index: 2000;
}

/* Transitions */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.25s ease-out;
}
.fade-slide-enter-from { opacity: 0; transform: translateY(10px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.hide-mobile {
  @media (max-width: 600px) { display: none; }
}
</style>
