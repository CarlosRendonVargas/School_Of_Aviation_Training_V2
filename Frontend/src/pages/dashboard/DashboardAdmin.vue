<template>
  <div>
    <!-- KPI Header Cards -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3" v-for="k in kpis" :key="k.label">
        <div class="stat-card cursor-pointer premium-hover" @click="$router.push(k.to)">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="row items-start justify-between">
              <div>
                <div class="stat-num" :style="`color:${k.color}`">{{ k.valor }}</div>
                <div class="stat-label text-grey-5 font-mono uppercase-bold" style="font-size: 10px">{{ k.label }}</div>
              </div>
              <q-icon :name="k.icono" :style="`color:${k.color}; opacity:.4`" size="26px" />
            </div>
          </template>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Resumen Financiero -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac glass-card-dark">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:16px; font-weight:700">
                💰 Estado de Caja y Facturación
              </div>
              <q-btn flat dense no-caps to="/financiero" color="primary" size="sm" 
                label="Módulo Administrativo" icon-right="chevron_right" />
            </div>

            <q-skeleton v-if="cargando" type="rect" height="150px" dark />
            <div v-else class="row q-col-gutter-sm">
              <div class="col-6">
                <div class="finance-box positive">
                  <div class="text-h6 text-positive font-head text-weight-bold">{{ formatCOP(data?.ingresos_mes || 0) }}</div>
                  <div class="text-caption text-grey-6 font-mono">INGRESOS DEL MES</div>
                </div>
              </div>
              <div class="col-6">
                <div class="finance-box warning">
                  <div class="text-h6 text-warning font-head text-weight-bold">{{ data?.facturas_pendientes || 0 }}</div>
                  <div class="text-caption text-grey-6 font-mono">FACTS. PENDIENTES</div>
                </div>
              </div>
              <div class="col-6">
                <div class="finance-box negative">
                  <div class="text-h6 text-negative font-head text-weight-bold">{{ data?.facturas_vencidas || 0 }}</div>
                  <div class="text-caption text-grey-6 font-mono">FACTS. VENCIDAS</div>
                </div>
              </div>
              <div class="col-6">
                <div class="finance-box primary">
                  <div class="text-h6 text-primary font-head text-weight-bold">{{ data?.nuevas_matriculas_mes || 0 }}</div>
                  <div class="text-caption text-grey-6 font-mono">NUEVAS MATRÍCULAS</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Gráfico de Flujo de Matrículas (Interactive) -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac glass-card-dark" style="height: 100%">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:16px; font-weight:700">
                📈 Flujo de Matrículas (Histórico)
              </div>
              <q-chip outline color="purple-4" size="xs" label="Últimos 6 meses" />
            </div>
            
            <div style="height: 200px" class="relative-position">
              <q-inner-loading :showing="cargando" dark>
                <q-spinner-dots color="purple-4" size="40px" />
              </q-inner-loading>
              <canvas id="chartMatriculas"></canvas>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Acciones de Gestión Críticas -->
      <div class="col-12">
        <q-card flat class="card-rac glass-card-dark">
          <q-card-section>
            <div class="font-head text-white q-mb-md text-weight-bold" style="font-size:16px">Acciones de Control Directo</div>
            <div class="row q-col-gutter-sm">
                <div v-for="ac in acciones" :key="ac.label" class="col-12 col-sm-6 col-md-3">
                    <q-btn 
                        unelevated no-caps 
                        :color="ac.color" 
                        :icon="ac.icono" 
                        :label="ac.label"
                        :to="ac.to"
                        class="full-width premium-btn" />
                </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'
Chart.register(...registerables)

const props = defineProps({
  data:     { type: Object,  default: null },
  cargando: { type: Boolean, default: false },
})

let chartInst = null

const updateChart = () => {
    const el = document.getElementById('chartMatriculas')
    if (!el || !props.data?.flujo_matriculas) return
    
    if (chartInst) chartInst.destroy()
    
    chartInst = new Chart(el, {
        type: 'line',
        data: {
            labels: props.data.flujo_matriculas.map(x => x.mes),
            datasets: [{
                label: 'Matrículas Registradas',
                data: props.data.flujo_matriculas.map(x => x.total),
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.15)',
                tension: 0.45,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { family: 'Syne' },
                    bodyFont: { family: 'JetBrains Mono' }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 10 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false }, ticks: { color: '#64748b', font: { size: 10 }, stepSize: 1 } }
            }
        }
    })
}

watch(() => props.data, () => {
    setTimeout(updateChart, 150)
}, { deep: true })

onMounted(() => {
    if (props.data) updateChart()
})

const kpis = computed(() => [
  { label: 'Egresados / Graduados', valor: '24', icono: 'military_tech', color: '#10b981', to: '/academico' },
  { label: 'Auditoría Pendiente', valor: '3', icono: 'gavel', color: '#ef4444', to: '/seguridad' },
  { label: 'Ingresos Mensuales', valor: formatCOP(props.data?.ingresos_mes || 0, true), icono: 'account_balance_wallet', color: '#f59e0b', to: '/financiero' },
  { label: 'Matrículas del Mes', valor: props.data?.nuevas_matriculas_mes || 0, icono: 'history_edu', color: '#8b5cf6', to: '/financiero' }
])

const acciones = [
  { label: 'Nueva Matrícula', icono: 'add_circle', color: 'purple-7', to: '/financiero' },
  { label: 'Procesar Factura', icono: 'receipt_long', color: 'positive', to: '/financiero' },
  { label: 'Seguimiento Notas', icono: 'analytics', color: 'blue-8', to: '/academico' },
  { label: 'Logs Sistema', icono: 'terminal', color: 'grey-9', to: '/seguridad' }
]

function formatCOP(v, abr=false) {
    const n = parseFloat(v || 0)
    if (abr && n >= 1000000) return '$' + (n/1000000).toFixed(1) + 'M'
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(n)
}
</script>

<style lang="scss" scoped>
.glass-card-dark {
    background: rgba(15, 23, 42, 0.6) !important;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
}
.finance-box {
    padding: 16px;
    border-radius: 10px;
    margin-bottom: 8px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
    &:hover { background: rgba(255,255,255, 0.05); transform: scale(1.02); }
    
    &.positive { border-left: 4px solid #10b981; background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0.02) 100%); }
    &.warning { border-left: 4px solid #f59e0b; background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(245, 158, 11, 0.02) 100%); }
    &.negative { border-left: 4px solid #ef4444; background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(239, 68, 68, 0.02) 100%); }
    &.primary { border-left: 4px solid #8b5cf6; background: linear-gradient(135deg, rgba(139, 92, 246, 0.08) 0%, rgba(139, 92, 246, 0.02) 100%); }
}
.stat-card {
    background: #0f1218;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
}
.premium-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    &:hover { transform: translateY(-4px); border-color: rgba(255, 255, 255, 0.15); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5); }
}
.premium-btn {
    border-radius: 10px;
    height: 56px;
    font-weight: 600;
    letter-spacing: 0.5px;
}
.uppercase-bold { text-transform: uppercase; font-weight: 700; }
</style>
