<template>
  <div class="q-pa-md admin-dashboard animate-fade">
    
    <div v-if="cargando" class="flex flex-center" style="height: 60vh">
      <q-spinner-cube color="red-9" size="60px" />
    </div>

    <div v-else>
      <!-- ═══════ HERO: BRIEFING OPERATIVO ═══════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div class="col-12">
          <q-card class="premium-glass-card overflow-hidden welcome-hero">
            <div class="hero-glow"></div>
            <q-card-section class="q-pa-xl relative-position">
              <div class="row items-center">
                <div class="col-12 col-md-8">
                  <div class="text-overline text-red-9 font-mono tracking-widest q-mb-xs">MÓDULO DE INTELIGENCIA · RAC 141</div>
                  <h1 class="text-h3 font-head text-weight-bolder text-white q-my-none">Centro de Comando</h1>
                  <p class="text-subtitle1 text-grey-5 q-mt-md max-width-600 line-height-1">
                    Supervisión en tiempo real de la operación académica, estados de cartera y cumplimiento regulatorio.
                  </p>
                  <div class="row q-gutter-md q-mt-xl">
                    <q-btn unelevated color="red-9" label="Nueva Matrícula" icon="person_add" class="premium-btn shadow-10" to="/financiero" />
                    <q-btn outline color="white" label="Reporte Mensual" icon="analytics" class="premium-btn" @click="generarReporte" :loading="generandoReporte" />
                  </div>
                </div>
                <div class="col-md-4 hide-mobile text-right opacity-10">
                  <q-icon name="security" size="200px" />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- ═══════ GRID DE INDICADORES (KPIs) ═══════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpiCards" :key="kpi.label" class="col-12 col-sm-6 col-md-3">
          <q-card class="premium-glass-card kpi-modern-card">
            <q-card-section class="q-pa-lg">
              <div class="row items-center justify-between q-mb-md">
                <div class="kpi-icon" :style="`background: ${kpi.bg}`">
                  <q-icon :name="kpi.icon" :color="kpi.color" size="22px" />
                </div>
                <q-badge outline :color="kpi.trendColor" class="font-mono text-weight-bold" style="font-size:10px">
                  <q-icon :name="kpi.trendIcon" class="q-mr-xs" /> {{ kpi.trendText }}
                </q-badge>
              </div>
              <div class="text-h4 font-mono text-weight-bolder text-white q-mt-sm">
                {{ kpi.value }}
              </div>
              <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">
                {{ kpi.label }}
              </div>
            </q-card-section>
            <div class="kpi-accent-bar" :style="`background: ${kpi.colorHex}`"></div>
          </q-card>
        </div>
      </div>

      <!-- ═══════ ANÁLISIS Y ACCIÓN ═══════ -->
      <div class="row q-col-gutter-xl">
        <!-- Gráfica de Tendencias -->
        <div class="col-12 col-md-8">
          <q-card class="premium-glass-card q-pa-xl" style="height: 100%">
            <div class="row justify-between items-center q-mb-xl">
              <div>
                <div class="text-h6 font-head text-white">Tráfico de Admisiones</div>
                <div class="text-caption text-grey-6 font-mono">Fluctuación de registros históricos (6 meses)</div>
              </div>
              <q-icon name="insights" color="red-9" size="24px" />
            </div>
            
            <div class="chart-container">
               <canvas ref="chartCanvas" style="height: 320px; width: 100%"></canvas>
            </div>
          </q-card>
        </div>

        <!-- Acciones Directivas -->
        <div class="col-12 col-md-4">
          <q-card class="premium-glass-card q-pa-xl" style="height: 100%">
            <div class="text-h6 font-head text-white q-mb-xl">Briefing de Tareas</div>
            
            <q-list class="q-gutter-y-lg">
              <q-item v-for="action in quickActions" :key="action.label" 
                clickable v-ripple 
                class="premium-glass-card action-brief-item"
                :to="action.to"
              >
                <q-item-section avatar>
                  <div class="brief-icon-box" :style="`background: ${action.bg}`">
                    <q-icon :name="action.icon" :color="action.color" size="20px" />
                  </div>
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold text-grey-2">{{ action.label }}</q-item-label>
                  <q-item-label caption class="text-grey-6 font-mono" style="font-size:10px">{{ action.desc }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon name="chevron_right" color="red-9" />
                </q-item-section>
              </q-item>
            </q-list>

            <q-card class="q-mt-xl bg-red-10 border-radius-16 cursor-pointer glow-red" @click="$router.push('/seguridad')">
               <q-card-section class="q-pa-lg row items-center no-wrap">
                  <div class="col">
                    <div class="text-weight-bold text-white font-head uppercase tracking-wider">Cumplimiento RAC</div>
                    <div class="text-caption text-white opacity-70 font-mono" style="font-size:10px">Auditoría Digital UAEAC</div>
                  </div>
                  <q-btn round color="black-20" icon="security" size="sm" class="q-ml-md shadow-24" />
               </q-card-section>
            </q-card>
          </q-card>
        </div>
      </div>
    </div>

    <!-- ════ DIÁLOGO: REPORTE MENSUAL EJECUTIVO ════ -->
    <q-dialog v-model="dialogReporte" backdrop-filter="blur(25px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20 overflow-hidden" style="width: min(700px, 95vw)">
        <div class="rac-dialog-header welcome-hero q-pa-xl" style="padding: 40px !important;">
            <div class="hero-glow"></div>
            <div class="row items-center justify-between">
                <div class="col">
                   <div class="font-mono text-white opacity-50 uppercase tracking-widest" style="font-size:10px">Resumen Operativo Mensual</div>
                   <div class="text-h4 text-white font-head text-weight-bolder">REPORTE RAC 141</div>
                </div>
                <q-btn flat round icon="close" v-close-popup color="white" class="bg-white-10" />
            </div>
        </div>

        <div class="q-pa-xl q-gutter-y-lg text-white">
            <div class="row q-col-gutter-md">
                <div class="col-12 col-sm-6">
                    <div class="text-grey-5 font-mono uppercase q-mb-xs" style="font-size:10px">Ingresos del Mes</div>
                    <div class="text-h5 font-mono text-weight-bolder text-emerald" style="color: #10b981">{{ formatMoney(data.ingresos_mes) }}</div>
                </div>
                <div class="col-12 col-sm-6 text-right">
                    <div class="text-grey-5 font-mono uppercase q-mb-xs" style="font-size:10px">Nuevas Matrículas</div>
                    <div class="text-h5 font-mono text-weight-bolder text-red-4">{{ data.nuevas_matriculas_mes }}</div>
                </div>
            </div>

            <q-separator dark class="opacity-10" />

            <div class="row q-col-gutter-md">
                <div class="col-4 text-center">
                    <div class="text-h6 text-red-9">{{ data.estudiantes_activos }}</div>
                    <div class="text-caption text-grey-6 uppercase" style="font-size: 8px">Estudiantes</div>
                </div>
                <div class="col-4 text-center">
                    <div class="text-h6 text-amber">{{ data.facturas_pendientes }}</div>
                    <div class="text-caption text-grey-6 uppercase" style="font-size: 8px">Pendientes</div>
                </div>
                <div class="col-4 text-center">
                    <div class="text-h6 text-red-10">{{ data.facturas_vencidas }}</div>
                    <div class="text-caption text-grey-6 uppercase" style="font-size: 8px">Vencidas</div>
                </div>
            </div>

            <div class="q-mt-xl bg-black-20 q-pa-lg rounded-12 border-red-low" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(161, 11, 19, 0.2); border-radius: 12px;">
                <div class="text-caption text-grey-5 italic" style="font-size: 11px">
                    Este reporte consolida el flujo financiero y académico del mes en curso para la auditoría interna de la Escuela de Aviación.
                </div>
            </div>

            <div class="row justify-center q-mt-lg">
                <q-btn unelevated color="red-9" icon="print" label="Descargar / Imprimir" @click="imprimir" class="premium-btn q-px-xl q-py-md text-weight-bolder" no-caps />
            </div>
        </div>
      </q-card>
    </q-dialog>

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
  flujo_matriculas: []
})

let chartInst = null

const kpiCards = computed(() => [
  { 
    label: 'Recaudo Mensual', 
    value: formatMoney(data.value.ingresos_mes), 
    icon: 'account_balance', color: 'emerald', colorHex: '#10b981', bg: 'rgba(16,185,129,0.1)',
    trendIcon: 'trending_up', trendText: '+12%', trendColor: 'emerald'
  },
  { 
    label: 'Alumnos en Fase', 
    value: data.value.estudiantes_activos, 
    icon: 'groups', color: 'red-9', colorHex: '#A10B13', bg: 'rgba(161,11,19,0.1)',
    trendIcon: 'military_tech', trendText: 'Expedientes', trendColor: 'red-9'
  },
  { 
    label: 'Cartera Pendiente', 
    value: data.value.facturas_pendientes, 
    icon: 'pending_actions', color: 'orange-9', colorHex: '#f59e0b', bg: 'rgba(245,158,11,0.1)',
    trendIcon: 'schedule', trendText: 'Pendiente', trendColor: 'orange-9'
  },
  { 
    label: 'Cuentas Vencidas', 
    value: data.value.facturas_vencidas, 
    icon: 'warning', color: 'red-10', colorHex: '#6b070c', bg: 'rgba(107,7,12,0.1)',
    trendIcon: 'error', trendText: 'Crítico', trendColor: 'red-10'
  }
])

const quickActions = [
  { label: 'Caja y Pagos', desc: 'Saldos y Recaudos', icon: 'payments', color: 'emerald', bg: 'rgba(16,185,129,0.1)', to: '/financiero' },
  { label: 'Facturación', desc: 'Control de Facturas', icon: 'receipt', color: 'red-9', bg: 'rgba(161,11,19,0.1)', to: '/financiero' },
  { label: 'Matrículas', desc: 'Dossiers Académicos', icon: 'school', color: 'red-9', bg: 'rgba(161,11,19,0.1)', to: '/academico' }
]

const initChart = () => {
  if (!chartCanvas.value || !data.value.flujo_matriculas?.length) return
  if (chartInst) chartInst.destroy()

  const ctx = chartCanvas.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 300)
  gradient.addColorStop(0, 'rgba(161, 11, 19, 0.4)')
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
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: '#A10B13',
        pointBorderColor: '#fff',
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#64748b', font: { family: 'JetBrains Mono' } } },
        x: { grid: { display: false }, ticks: { color: '#64748b', font: { family: 'JetBrains Mono' } } }
      }
    }
  })
}

const formatMoney = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0)

const generandoReporte = ref(false)
const dialogReporte = ref(false)

const generarReporte = () => {
    generandoReporte.value = true
    setTimeout(() => {
        generandoReporte.value = false
        dialogReporte.value = true
    }, 1500)
}

const imprimir = () => window.print()

onMounted(async () => {
  try {
    const res = await api.get('/dashboard')
    if (res.data?.data) {
      data.value = res.data.data
      await nextTick()
      setTimeout(initChart, 300)
    }
  } finally { cargando.value = false }
})

onBeforeUnmount(() => { if (chartInst) chartInst.destroy() })
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.welcome-hero { position: relative; border-left: 5px solid #A10B13; }
.hero-glow {
  position: absolute; top:0; right:0; bottom:0; left:0;
  background: radial-gradient(circle at 70% 30%, rgba(161, 11, 19, 0.15) 0%, transparent 60%);
}
.kpi-icon {
  width: 44px; height: 44px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
}
.kpi-accent-bar { height: 4px; border-radius: 0 0 12px 12px; opacity: 0.8; }
.action-brief-item { border: none !important; margin-bottom: 8px; transition: all 0.2s; &:hover { transform: translateX(5px); background: rgba(255,255,255,0.05) !important; } }
.brief-icon-box { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.glow-red { box-shadow: 0 0 20px rgba(161, 11, 19, 0.2); }
.line-height-1 { line-height: 1.5; }
</style>
