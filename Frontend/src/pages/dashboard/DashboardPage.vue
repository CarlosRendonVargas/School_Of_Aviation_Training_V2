<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Briefing de Bienvenida ══ -->
    <div class="dashboard-header q-mb-xl rac-page-header">
      <div>
        <div class="font-mono text-grey-6 uppercase tracking-widest q-mb-xs" style="font-size:10px">
           {{ saludoHora }} · STATUS: <span class="text-emerald">OPERATIVO</span>
        </div>
        <h1 class="welcome-title text-weight-bolder text-white font-head q-my-none tracking-tighter">
          {{ auth.nombre.split(' ')[0] }} <span class="text-red-9">.</span>
        </h1>
        <div class="row items-center q-mt-sm flex-wrap q-gutter-sm">
           <q-badge color="red-9" class="font-mono text-weight-bold q-px-sm q-py-xs shadow-10" style="font-size:10px">
             {{ rolLabel.toUpperCase() }}
           </q-badge>
           <div class="text-grey-5 font-mono" style="font-size:11px">
             <q-icon name="event" size="14px" class="q-mr-xs" />{{ fechaHoy }}
           </div>
        </div>
      </div>
      
      <q-btn 
        v-if="auth.esDirOps || auth.esAdmin"
        flat dense rounded
        color="white" icon="sync" 
        label="Sincronizar" 
        class="premium-glass-card q-px-lg font-mono text-weight-bold sync-btn"
        style="font-size:10px"
        @click="sincronizar"
        :loading="sincronizando"
      />
    </div>

    <!-- ── RENDERIZADO DINÁMICO DE DASHBOARDS POR ROL ──────────────── -->
    <div class="animate-slide-up">
      <template v-if="auth.esEstudiante">
        <DashboardEstudiante :data="dashboardData" :cargando="loadingData" />
      </template>

      <template v-else-if="auth.esInstructor">
        <DashboardInstructor :data="dashboardData" :cargando="loadingData" />
      </template>

      <template v-else-if="auth.esDirOps">
        <DashboardDirOps :data="dashboardData" :cargando="loadingData" />
      </template>

      <template v-else-if="auth.esMantenimiento">
        <DashboardMantenimiento :data="dashboardData" :cargando="loadingData" />
      </template>

      <template v-else-if="auth.esAdmin">
        <DashboardAdmin :data="dashboardData" :cargando="loadingData" />
      </template>

      <template v-else-if="auth.esAuditor">
        <DashboardAuditor :data="dashboardData" :cargando="loadingData" />
      </template>
    </div>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'

dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.locale('es')

import DashboardEstudiante   from 'components/dashboard/DashboardEstudiante.vue'
import DashboardInstructor   from 'components/dashboard/DashboardInstructor.vue'
import DashboardDirOps       from 'components/dashboard/DashboardDirOps.vue'
import DashboardMantenimiento from 'components/dashboard/DashboardMantenimiento.vue'
import DashboardAdmin        from 'components/dashboard/DashboardAdmin.vue'
import DashboardAuditor      from 'components/dashboard/DashboardAuditor.vue'

const $q     = useQuasar()
const auth   = useAuthStore()
const vStore = useVencimientosStore()

const sincronizando = ref(false)
const loadingData   = ref(false)
const dashboardData = ref(null)

async function cargarDashboard() {
  loadingData.value = true
  try {
    const { data } = await api.get('/dashboard')
    dashboardData.value = data.data
  } catch (err) {
    console.error("Error cargando dashboard:", err)
  } finally {
    loadingData.value = false
  }
}

onMounted(() => {
  cargarDashboard()
})

const fechaHoy = computed(() => dayjs().tz('America/Bogota').format('DD/MM/YYYY [·] dddd, D [de] MMMM'))

const saludoHora = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Control de Vuelo: Buenos días'
  if (h < 18) return 'Control de Vuelo: Buenas tardes'
  return 'Control de Vuelo: Buenas noches'
})

const rolLabel = computed(() => ({
  estudiante:    'Estudiante en fase PIA',
  instructor:    'Instructor de Vuelo Autorizado',
  admin:         'Administrador de Centro de Instrucción',
  dir_ops:       'Director de Operaciones Aéreas',
  mantenimiento: 'Ingeniero de Mantenimiento',
  auditor_uaeac: 'Auditor Gubernamental UAEAC',
}[auth.rol] || auth.rol))

async function sincronizar() {
  sincronizando.value = true
  try {
    await api.post('/vencimientos/sincronizar')
    await vStore.cargar()
    $q.notify({ 
      color: 'red-9', 
      message: 'Inteligencia de alertas sincronizada correctamente.',
      icon: 'verified' 
    })
  } catch {
    $q.notify({ 
      color: 'negative', 
      message: 'Fallo en la sincronización de datos.' 
    })
  } finally {
    sincronizando.value = false
  }
}
</script>

<style lang="scss" scoped>
.animate-fade      { animation: fadeIn 0.8s ease-out; }
.animate-slide-up  { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1); }
@keyframes fadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.text-emerald { color: #10b981; }

// Responsive dashboard header
.dashboard-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
}

// Responsive welcome title
.welcome-title {
  font-size: 3rem;
  @media (max-width: 599px) { font-size: 2rem !important; }
}

// Sync button on mobile
.sync-btn {
  @media (max-width: 599px) { width: 100%; justify-content: center; font-size: 10px; }
}
</style>
