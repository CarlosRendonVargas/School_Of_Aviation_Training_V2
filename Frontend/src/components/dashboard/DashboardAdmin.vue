<template>
  <div class="q-pa-lg">
    <div v-if="cargando" class="flex flex-center" style="height: 70vh">
      <q-spinner-cube color="primary" size="60px" />
    </div>

    <div v-else class="admin-dashboard animate-fade">
      <!-- ═══════ WELCOME HERO SECTION ═══════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div class="col-12">
          <q-card class="welcome-hero overflow-hidden">
            <div class="hero-bg"></div>
            <q-card-section class="row items-center q-pa-xl relative-position">
              <div class="col-12 col-md-8">
                <div class="text-overline text-red-3 font-mono tracking-wider q-mb-xs">MÓDULO 08 · ADMINISTRACIÓN FINANCIERA</div>
                <h1 class="text-h3 font-head text-weight-bolder text-white q-my-none">Resumen Directivo</h1>
                <p class="text-subtitle1 text-grey-4 q-mt-md max-width-600">
                  Control centralizado de ingresos, matrículas vigentes y carteras pendientes bajo estándares de cumplimiento RAC 141.
                </p>
                <div class="row q-gutter-sm q-mt-lg">
                  <q-btn unelevated color="primary" label="Nueva Matrícula" icon="add_circle" class="premium-btn" to="/financiero" />
                  <q-btn outline color="white" label="Ver Reportes" icon="analytics" class="premium-btn glass-btn" />
                </div>
              </div>
              <div class="col-md-4 hide-mobile text-right">
                <q-icon name="insights" size="180px" color="white" style="opacity: 0.05" />
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- ═══════ KPI GRID ═══════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpiCards" :key="kpi.label" class="col-12 col-sm-6 col-md-3">
          <q-card class="kpi-card-modern border-radius-16">
            <q-card-section class="q-pa-lg">
              <div class="row items-center justify-between q-mb-sm">
                <span class="text-caption text-grey-5 font-mono uppercase tracking-wider">{{ kpi.label }}</span>
                <div class="kpi-icon-box" :style="`background: ${kpi.bg}`">
                  <q-icon :name="kpi.icon" :color="kpi.color" size="20px" />
                </div>
              </div>
              <div class="text-h4 font-head text-weight-bold text-white q-mt-sm">
                {{ kpi.value }}
              </div>
              <div class="row items-center q-mt-sm">
                <q-icon :name="kpi.trendIcon" :color="kpi.trendColor" size="16px" />
                <span :class="`text-caption text-${kpi.trendColor} q-ml-xs font-mono`">{{ kpi.trendText }}</span>
              </div>
            </q-card-section>
            <div class="kpi-progress-bar" :style="`background: ${kpi.colorHex}; width: ${kpi.progress}%`"></div>
          </q-card>
        </div>
      </div>

      <!-- ═══════ TRENDS AND ACTIONS ═══════ -->
      <div class="row q-col-gutter-lg">
        <!-- Chart Section -->
        <div class="col-12 col-md-8">
          <q-card class="card-rac q-pa-lg" style="height: 100%">
            <div class="row justify-between items-center q-mb-xl">
              <div>
                <div class="text-h6 font-head text-weight-bold text-white">Flujo de Matrículas</div>
                <div class="text-caption text-grey-6">Comparativa histórica de admisiones (6 meses)</div>
              </div>
              <q-btn-dropdown flat color="grey-5" label="Filtros" icon="filter_list" size="sm" dropdown-icon="expand_more">
                <q-list dark style="background: #0f172a">
                  <q-item clickable v-close-popup><q-item-section>Este Año</q-item-section></q-item>
                  <q-item clickable v-close-popup><q-item-section>Año Pasado</q-item-section></q-item>
                </q-list>
              </q-btn-dropdown>
            </div>
            
            <div class="chart-wrapper rounded-borders overflow-hidden">
               <canvas ref="chartCanvas" style="height: 300px; width: 100%"></canvas>
            </div>
          </q-card>
        </div>

        <!-- Quick Actions Section -->
        <div class="col-12 col-md-4">
          <q-card class="card-rac q-pa-lg" style="height: 100%">
            <div class="text-h6 font-head text-weight-bold text-white q-mb-lg">Tareas de Control</div>
            
            <q-list class="q-gutter-y-md">
              <q-item v-for="action in quickActions" :key="action.label" 
                clickable v-ripple 
                class="action-item border-radius-12"
                :to="action.to"
              >
                <q-item-section avatar>
                  <div class="action-icon-circle" :style="`background: ${action.bg}`">
                    <q-icon :name="action.icon" :color="action.color" size="20px" />
                  </div>
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold text-white">{{ action.label }}</q-item-label>
                  <q-item-label caption class="text-grey-6">{{ action.desc }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" color="grey-8" />
                </q-item-section>
              </q-item>
            </q-list>

            <q-card class="bg-primary-gradient q-mt-xl border-radius-16 cursor-pointer" @click="$router.push('/cumplimiento')">
               <q-card-section class="q-pa-md row items-center no-wrap">
                  <div class="col">
                    <div class="text-weight-bold text-white">Auditoría RAC 141</div>
                    <div class="text-caption text-white opacity-70">Verificar cumplimiento de bitácoras</div>
                  </div>
                  <q-btn round color="black" icon="arrow_forward" size="sm" class="q-ml-md" to="/cumplimiento" />
               </q-card-section>
            </q-card>
          </q-card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { api } from 'boot/axios'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const cargando = ref(true)
const chartCanvas = ref(null)
const data = ref({
  estudiantes_activos: 0,
  ingresos_mes: 0,
  facturas_pendientes: 0,
  facturas_vencidas: 0,
  nuevas_matriculas_mes: 0,
  flujo_matriculas: []
})

let chartInst = null

const kpiCards = computed(() => [
  { 
    label: 'Ingresos del Mes', 
    value: formatMoney(data.value.ingresos_mes), 
    icon: 'account_balance_wallet', color: 'emerald', colorHex: '#10b981', bg: 'rgba(16,185,129,0.1)',
    trendIcon: 'trending_up', trendText: '+12% vs mes ant.', trendColor: 'emerald', progress: 75
  },
  { 
    label: 'Estudiantes Activos', 
    value: data.value.estudiantes_activos, 
    icon: 'school', color: 'red-5', colorHex: '#A10B13', bg: 'rgba(161,11,19,0.1)',
    trendIcon: 'group', trendText: 'Expedientes RAC', trendColor: 'grey-5', progress: 100
  },
  { 
    label: 'Facts. Pendientes', 
    value: data.value.facturas_pendientes, 
    icon: 'receipt_long', color: 'amber', colorHex: '#f59e0b', bg: 'rgba(245,158,11,0.1)',
    trendIcon: 'hourglass_empty', trendText: 'Por recaudar', trendColor: 'amber', progress: 40
  },
  { 
    label: 'Facts. Vencidas', 
    value: data.value.facturas_vencidas, 
    icon: 'warning', color: 'red-8', colorHex: '#6b070c', bg: 'rgba(107,7,12,0.1)',
    trendIcon: 'error_outline', trendText: 'Requiere atención', trendColor: 'red-4', progress: 15
  }
])

const quickActions = [
  { label: 'Caja y Pagos', desc: 'Registrar abonos y depósitos', icon: 'payments', color: 'emerald', bg: 'rgba(16,185,129,0.1)', to: '/financiero' },
  { label: 'Facturación', desc: 'Gestionar facturas electrónicas', icon: 'receipt', color: 'red-5', bg: 'rgba(161,11,19,0.1)', to: '/financiero' },
  { label: 'Matrículas', desc: 'Ver expedientes académicos', icon: 'group_add', color: 'red-4', bg: 'rgba(161,11,19,0.05)', to: '/academico' },
  { label: 'Seguridad', desc: 'Auditoría de accesos y logs', icon: 'security', color: 'grey-5', bg: 'rgba(255,255,255,0.05)', to: '/seguridad' }
]

const initChart = () => {
  if (!chartCanvas.value || !data.value.flujo_matriculas?.length) return
  if (chartInst) chartInst.destroy()

  const ctx = chartCanvas.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 300)
  gradient.addColorStop(0, 'rgba(161, 11, 19, 0.3)')
  gradient.addColorStop(1, 'rgba(161, 11, 19, 0)')

  chartInst = new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.value.flujo_matriculas.map(x => x.mes),
      datasets: [{
        label: 'Matrículas',
        data: data.value.flujo_matriculas.map(x => x.total),
        borderColor: '#A10B13',
        backgroundColor: gradient,
        borderWidth: 4,
        tension: 0.45,
        fill: true,
        pointBackgroundColor: '#fff',
        pointBorderColor: '#A10B13',
        pointBorderWidth: 3,
        pointRadius: 6,
        pointHoverRadius: 8
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b' } },
        x: { grid: { display: false }, ticks: { color: '#64748b' } }
      }
    }
  })
}

const formatMoney = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0)

onMounted(async () => {
  try {
    const res = await api.get('/dashboard')
    if (res.data?.data) {
      data.value = res.data.data
      await nextTick()
      setTimeout(initChart, 500)
    }
  } catch (e) { console.error(e) } finally { cargando.value = false }
})

onBeforeUnmount(() => { if (chartInst) chartInst.destroy() })
</script>

<style lang="scss" scoped>
.welcome-hero {
  background: #0f172a;
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 24px;
  position: relative;
  .hero-bg {
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background: radial-gradient(circle at 80% 20%, rgba(161, 11, 19, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 20% 80%, rgba(161, 11, 19, 0.1) 0%, transparent 40%);
    filter: blur(40px);
  }
}

.max-width-600 { max-width: 600px; }

.kpi-card-modern {
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.06);
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  &:hover {
    border-color: rgba(255,255,255,0.15);
    background: rgba(15, 23, 42, 0.8);
    transform: translateY(-5px);
  }
  .kpi-icon-box {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
  }
  .kpi-progress-bar {
    height: 3px; position: absolute; bottom: 0; left: 0;
    opacity: 0.7;
  }
}

.action-item {
  background: rgba(255,255,255,0.02);
  transition: all 0.2s;
  &:hover {
    background: rgba(255,255,255,0.05);
    transform: translateX(5px);
  }
}

.action-icon-circle {
  width: 44px; height: 44px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}

.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.opacity-70 { opacity: 0.7; }
</style>
