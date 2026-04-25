<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado SMS Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="security" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">SISTEMA GESTIÓN SEGURIDAD OPERACIONAL · OACI ANEXO 19 · RAC 141</div>
          <h1 class="rac-page-title">Seguridad Operacional (SMS)</h1>
        </div>
      </div>
      <q-btn 
        color="red-9" icon="add_alert" label="Registrar Hallazgo SMS" 
        class="premium-btn shadow-24 pulse-red q-px-xl q-py-md" 
        @click="$router.push('/sms/nuevo-reporte')" 
      />
    </div>

    <!-- ══ Acceso Rápido Sub-Módulos SMS ══ -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-sm-3" v-for="link in smsLinks" :key="link.to">
        <q-card flat bordered class="text-center q-pa-md cursor-pointer sms-quick-card" @click="$router.push(link.to)">
          <q-icon :name="link.icon" :color="link.color" size="32px" class="q-mb-sm" />
          <div class="text-weight-bold text-white" style="font-size:13px">{{ link.label }}</div>
          <div class="text-caption text-grey-6">{{ link.sub }}</div>
        </q-card>
      </div>
    </div>

    <!-- ══ Pestañas de Gestión de Cristal ══ -->
    <q-card class="premium-glass-card overflow-hidden shadow-24 bonus-grid q-mb-xl">
      <q-tabs v-model="tabActivo" dense align="left" no-caps
        active-color="red-9" indicator-color="red-9" class="text-grey-5 border-bottom"
        outside-arrows mobile-arrows
        style="padding-left: 10px"
      >
        <q-tab name="dashboard" icon="analytics" :label="$q.screen.gt.xs ? 'Tendencias' : ''" />
        <q-tab name="reportes"  icon="inventory_2" :label="$q.screen.gt.xs ? 'Registro de Reportes' : 'Reportes'" />
        <q-tab name="acciones"  icon="fact_check" :label="$q.screen.gt.xs ? 'Acciones Mitigadoras' : 'Acciones'" />
        <q-tab name="matriz"    icon="grid_view" :label="$q.screen.gt.xs ? 'Matriz de Riesgo UAEAC' : 'Matriz'" />
      </q-tabs>

      <q-tab-panels v-model="tabActivo" animated dark class="bg-transparent min-h-600">

        <!-- ─── DASHBOARD DE PREVENCIÓN ─────────────────────────────────────────── -->
        <q-tab-panel name="dashboard" class="q-pa-md q-pa-md-xl">
          <div class="row q-col-gutter-md q-mb-xl">
            <div class="col-12 col-sm-6 col-md-3" v-for="kpi in kpisSms" :key="kpi.label">
              <q-card class="premium-glass-card q-pa-md q-pa-md-lg border-red-low shadow-inner flex items-center no-wrap welcome-hero overflow-hidden" style="min-height: 100px">
                <div class="hero-glow"></div>
                <div class="kpi-icon-premium q-mr-md q-mr-md-lg flex flex-center shadow-24" :style="`background: ${kpi.bg}; flex-shrink: 0`" :class="$q.screen.lt.md ? 'icon-sm' : ''">
                  <q-icon :name="kpi.icon" :color="kpi.color" :size="$q.screen.lt.md ? '20px' : '28px'" />
                </div>
                <div class="relative-position">
                  <div class="text-h4 text-weight-bolder font-mono text-white line-height-1">
                    {{ kpi.valor }}
                  </div>
                  <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" :style="$q.screen.lt.md ? 'font-size:8px; letter-spacing:1px' : 'font-size:9px'">
                    {{ kpi.label }}
                  </div>
                </div>
              </q-card>
            </div>
          </div>

          <q-card class="premium-glass-card border-red-low shadow-24">
            <q-card-section class="q-pa-xl">
              <div class="row items-center justify-between q-mb-xl">
                 <div class="row items-center">
                    <q-icon name="history" color="red-9" size="24px" class="q-mr-md shadow-inner" />
                    <div class="text-h5 font-head text-white text-weight-bolder uppercase tracking-tighter">Eventos de Seguridad Recientes</div>
                 </div>
                 <q-btn flat color="grey-6" label="Ver Historial Completo" size="sm" icon-right="chevron_right" @click="notificarProximamente" />
              </div>
              
              <q-list dark separator class="shadow-inner rounded-12 overflow-hidden border-red-low">
                <q-item v-for="r in reportesRecientes" :key="r.id" class="hover-row border-bottom" :class="$q.screen.lt.md ? 'q-pa-md' : 'q-pa-xl'">
                  <q-item-section avatar :class="$q.screen.lt.md ? 'q-pr-sm' : ''">
                    <div class="riesgo-hex-indicator shadow-24" :style="`background:${bgRiesgo(r.nivel_riesgo)}; ${$q.screen.lt.md ? 'width:32px; height:32px; font-size:12px' : ''}`">
                      {{ r.nivel_riesgo }}
                    </div>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold text-white font-head line-height-tight q-mb-xs" :class="$q.screen.lt.md ? 'text-subtitle2' : 'text-h6'">
                      {{ r.descripcion }}
                    </q-item-label>
                    <q-item-label caption class="font-mono text-grey-6 uppercase tracking-widest" :style="$q.screen.lt.md ? 'font-size:8px' : 'font-size:9px'">
                       {{ r.tipo || 'HALLAZGO' }} · #{{ r.id }} · {{ r.fecha_evento?.slice(0, 10) }}
                    </q-item-label>
                  </q-item-section>
                  <q-item-section side v-if="$q.screen.gt.xs">
                     <q-badge outline :color="colorEstado(r.estado)" :label="r.estado?.toUpperCase()" class="font-mono text-weight-bolder q-px-md shadow-24" />
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </q-tab-panel>

        <!-- ─── REGISTRO TÉCNICO DE REPORTES ──────────────────────────────────────── -->
        <q-tab-panel name="reportes" class="q-pa-xl">
          <div class="row items-center justify-between q-mb-xl">
             <div class="text-h5 font-head text-white text-weight-bolder">Archivo Maestro de Seguridad</div>
             <q-btn outline color="red-9" icon="file_download" :label="$q.screen.gt.sm ? 'Exportar Auditoría' : ''" class="font-mono shadow-24" @click="exportarSMS">
                <q-tooltip v-if="$q.screen.lt.md">Exportar Auditoría</q-tooltip>
             </q-btn>
          </div>
          <q-table :rows="reportes" :columns="columnasReportes" row-key="id" class="rac-table shadow-24 border-red-low" flat dark :grid="$q.screen.lt.md">
            <template #body-cell-nivel_riesgo="{ row }">
              <q-td class="text-center">
                <q-badge :style="`background:${bgRiesgo(row.nivel_riesgo)}`" class="font-mono text-weight-bolder q-px-md shadow-24">
                  {{ row.nivel_riesgo }}
                </q-badge>
              </q-td>
            </template>
            <template #body-cell-tipo="{ row }">
              <q-td>
                <q-chip dense :color="colorTipo(row.tipo)" text-color="white" :label="row.tipo?.toUpperCase()" size="xs" class="font-mono text-weight-bold q-px-sm" />
                <q-chip dense v-if="row.anonimo" color="grey-8" text-color="white" label="ANÓNIMO" icon="visibility_off" size="xs" class="font-mono text-weight-bold q-ml-sm" />
              </q-td>
            </template>
            <template #body-cell-notificado_uaeac="{ value }">
               <q-td class="text-center">
                  <q-icon :name="value ? 'check_circle' : 'cancel'" :color="value ? 'emerald' : 'grey-7'" size="20px" />
               </q-td>
            </template>
            <template #body-cell-fecha_evento="{ value }">
               <q-td class="font-mono text-grey-5">{{ value ? value.slice(0, 10) : '---' }}</q-td>
            </template>

            <!-- Card Móvil SMS -->
            <template v-slot:item="props">
              <div class="col-12 q-pa-xs">
                <q-card class="premium-glass-card shadow-24 q-mb-sm border-red-low">
                  <q-card-section>
                    <div class="row items-center justify-between q-mb-md">
                      <div class="riesgo-hex-indicator shadow-24" :style="`background:${bgRiesgo(props.row.nivel_riesgo)}; width:36px; height:36px; font-size:14px`" title="Nivel de Riesgo">
                        {{ props.row.nivel_riesgo }}
                      </div>
                      <span class="font-mono text-grey-6" style="font-size:11px">{{ props.row.fecha_evento?.slice(0, 10) }}</span>
                    </div>

                    <div class="text-white text-weight-bold q-mb-sm" style="line-height:1.3">
                      {{ props.row.descripcion }}
                    </div>

                    <div class="row items-center justify-between q-mt-md">
                      <div>
                         <q-chip dense :color="colorTipo(props.row.tipo)" text-color="white" :label="props.row.tipo?.toUpperCase()" size="xs" class="font-mono" />
                         <q-icon v-if="props.row.notificado_uaeac" name="assured_workload" color="emerald" class="q-ml-sm" size="18px"><q-tooltip>Notificado a la UAEAC</q-tooltip></q-icon>
                      </div>
                      <q-badge outline :color="colorEstado(props.row.estado)" :label="props.row.estado?.toUpperCase()" class="font-mono" style="font-size:9px" />
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ─── SEGUIMIENTO DE ACCIONES ───────────────────────────────────── -->
        <q-tab-panel name="acciones" class="q-pa-xl">
          <div class="text-h5 font-head text-white text-weight-bolder q-mb-xl">Gestión de Mitigaciones Pendientes</div>
          <q-table :rows="acciones" :columns="columnasAcciones" row-key="id" class="rac-table shadow-24 border-red-low" flat dark :grid="$q.screen.lt.md">
              <template #body-cell-fecha_limite="{ value }">
                 <q-td class="font-mono text-red-9 text-weight-bolder text-center">{{ value }}</q-td>
              </template>
              <template #body-cell-estado="{ value }">
                 <q-td class="text-center"><q-badge color="orange-10" :label="value?.toUpperCase()" class="font-mono q-px-md" /></q-td>
              </template>

              <!-- Card Móvil Acciones -->
              <template v-slot:item="props">
                <div class="col-12 q-pa-xs">
                  <q-card class="premium-glass-card shadow-24 q-mb-sm border-emerald-low">
                    <q-card-section>
                      <div class="row items-center justify-between q-mb-sm">
                        <q-badge color="orange-10" :label="props.row.estado?.toUpperCase()" class="font-mono" />
                        <div class="text-red-9 font-mono text-weight-bold" style="font-size:12px">LÍMITE: {{ props.row.fecha_limite }}</div>
                      </div>
                      <div class="text-white q-mt-sm">{{ props.row.descripcion }}</div>
                    </q-card-section>
                  </q-card>
                </div>
              </template>
          </q-table>
        </q-tab-panel>

        <!-- ─── MATRIZ DE RIESGO OACI 5X5 ────────────────────────────────────────────── -->
        <q-tab-panel name="matriz" class="q-pa-xl flex flex-center shadow-inner">
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low welcome-hero" style="max-width: 900px; width: 100%">
            <div class="hero-glow"></div>
            <div class="text-center q-mb-xl relative-position">
              <div class="text-h4 font-head text-white text-weight-bolder uppercase tracking-tighter">Matriz de Evaluación de Riesgos</div>
              <div class="text-grey-6 font-mono uppercase tracking-widest q-mt-sm" style="font-size:10px">Análisis Cuantitativo UAEAC · Severidad vs Probabilidad</div>
            </div>

            <div class="matriz-ops-container relative-position q-mb-xl">
              <div class="y-axis-label-premium font-mono">SEVERIDAD (S)</div>
              <div class="x-axis-label-premium font-mono">PROBABILIDAD (P)</div>
              
              <div class="matriz-grid-premium">
                <div v-for="s in [5,4,3,2,1]" :key="s" class="matriz-row-premium">
                  <div class="row-num-premium font-mono shadow-inner">S{{s}}</div>
                  <div v-for="p in 5" :key="p" class="matriz-cell-premium shadow-24" :class="cellClass(s*p)" :style="`opacity: ${0.4 + (s*p/25)}`">
                    <span class="font-mono text-weight-bolder" style="font-size:18px">{{ s * p }}</span>
                    <q-tooltip class="bg-dark text-white font-mono uppercase">RIESGO NIVEL {{ s*p }}: {{ riskConcept(s*p) }}</q-tooltip>
                  </div>
                </div>
                <div class="matriz-row-premium header-row-premium q-mt-md">
                  <div class="row-num-premium empty"></div>
                  <div v-for="p in 5" :key="p" class="matriz-cell-num-premium font-mono shadow-inner">P{{p}}</div>
                </div>
              </div>
            </div>

            <div class="row justify-center q-gutter-xl q-mt-xl relative-position">
                <div class="flex items-center"><div class="legend-box-premium risk-safe"></div><span class="font-mono text-grey-5 q-ml-sm uppercase" style="font-size:10px">ACEPTABLE</span></div>
                <div class="flex items-center"><div class="legend-box-premium risk-caution"></div><span class="font-mono text-grey-5 q-ml-sm uppercase" style="font-size:10px">TOLERABLE</span></div>
                <div class="flex items-center"><div class="legend-box-premium risk-danger"></div><span class="font-mono text-grey-5 q-ml-sm uppercase" style="font-size:10px">INACEPTABLE</span></div>
            </div>
          </q-card>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from 'boot/axios'
import { exportFile, useQuasar } from 'quasar'

const tabActivo = ref('dashboard')
const kpis = ref(null)
const reportes = ref([])
const acciones = ref([])

const $q = useQuasar()

const smsLinks = [
  { to: '/sms/erg',            label: 'Plan ERG',           icon: 'local_fire_department', color: 'red-9',    sub: 'Respuesta emergencias' },
  { to: '/sms/capacitaciones', label: 'Capacitaciones SMS', icon: 'cast_for_education',    color: 'blue',     sub: 'Cultura de seguridad' },
  { to: '/sms/nuevo-reporte',  label: 'Nuevo Reporte',      icon: 'add_alert',             color: 'orange',   sub: 'Registrar hallazgo' },
  { to: '/vencimientos',       label: 'Alertas Vencimiento',icon: 'timer',                 color: 'positive', sub: 'Radar RAC' },
]

function notificarProximamente() {
  $q.notify({ color: 'info', icon: 'info', message: 'Función en desarrollo para próxima versión.' })
}

async function exportarSMS() {
  try {
    const anio = new Date().getFullYear()
    const res = await api.get(`/sms/exportar-griaa?anio=${anio}`)
    const data = res.data.data || []
    if (!data.length) {
      $q.notify({ color: 'warning', message: 'No hay eventos GRIAA para exportar.' })
      return
    }
    const cols = ['ID', 'Tipo', 'Nivel Riesgo', 'Descripcion', 'Fecha Evento', 'Lugar', 'Notificado UAEAC', 'Estado']
    const filas = data.map(r => [
      r.id,
      '"' + (r.tipo || '') + '"',
      r.nivel_riesgo,
      '"' + (r.descripcion || '').replace(/"/g, '""') + '"',
      r.fecha_evento ? r.fecha_evento.slice(0, 10) : '',
      '"' + (r.lugar || '').replace(/"/g, '""') + '"',
      r.notificado_uaeac ? 'SI' : 'NO',
      '"' + (r.estado || '') + '"',
    ].join(','))
    const csv = '\ufeff' + [cols.join(','), ...filas].join('\r\n')
    const status = exportFile('GRIAA_' + anio + '_RAC141.csv', csv, 'text/csv;charset=utf-8;')
    if (status === true) {
      $q.notify({ color: 'positive', icon: 'download_done', message: 'GRIAA ' + anio + ' exportado: ' + data.length + ' eventos' })
    } else {
      $q.notify({ color: 'negative', icon: 'warning', message: 'El navegador bloqueó la descarga.' })
    }
  } catch {
    $q.notify({ color: 'negative', message: 'Error al exportar GRIAA.' })
  }
}

const kpisSms = computed(() => [
  { label: 'Eventos Capturados', valor: kpis.value?.total_reportes || 0, icon: 'radar', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
  { label: 'Amenazas Críticas', valor: kpis.value?.inaceptables || 0, icon: 'warning_amber', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
  { label: 'Mitigaciones OK', valor: kpis.value?.acciones_pendientes || 0, icon: 'verified_user', color: 'emerald', bg: 'rgba(16,185,129,0.05)' },
  { label: 'Vencimientos SMS', valor: kpis.value?.acciones_vencidas || 0, icon: 'timer_off', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
])

const columnasReportes = [
  { name: 'nivel_riesgo', label: 'RIESGO', field: 'nivel_riesgo', align: 'center' },
  { name: 'tipo', label: 'TIPO EVENTO', field: 'tipo', align: 'left' },
  { name: 'descripcion', label: 'HALLAZGO / OBSERVACIÓN TÉCNICA', field: 'descripcion', align: 'left' },
  { name: 'fecha_evento', label: 'FECHA REG.', field: 'fecha_evento', align: 'center' },
  { name: 'notificado_uaeac', label: 'UAEAC', field: 'notificado_uaeac', align: 'center' },
  { name: 'estado', label: 'ESTATUS', field: 'estado', align: 'center' }
]

const columnasAcciones = [
  { name: 'descripcion', label: 'ACCIÓN CORRECTIVA / PREVENTIVA REGISTRADA', field: 'descripcion', align: 'left' },
  { name: 'fecha_limite', label: 'FECHA LÍMITE', field: 'fecha_limite', align: 'center' },
  { name: 'estado', label: 'ESTADO ACTUAL', field: 'estado', align: 'center' }
]

const reportesRecientes = computed(() => reportes.value.slice(0, 8))

const bgRiesgo = (n) => {
  if (n <= 4) return '#10b981'
  if (n <= 9) return '#f59e0b'
  return '#A10B13'
}

const cellClass = (n) => {
  if (n <= 4) return 'cell-safe'
  if (n <= 9) return 'cell-caution'
  return 'cell-danger'
}

const riskConcept = (n) => {
  if (n <= 4) return 'RIESGO CONTROLADO / ACEPTABLE'
  if (n <= 9) return 'RIESGO BAJO MONITOREO / TOLERABLE'
  return 'RIESGO CRÍTICO / INACEPTABLE'
}

const colorTipo   = (t) => ({ peligro: 'orange-10', incidente: 'red-9', accidente: 'red-10' }[t] || 'grey-8')
const colorEstado = (e) => ({ nuevo: 'red-9', en_analisis: 'orange-10', cerrado: 'emerald' }[e] || 'grey-8')

const cargarDatos = async () => {
  try {
    const [d, r, a] = await Promise.all([
      api.get('/sms/dashboard'), api.get('/sms/reportes'), api.get('/sms/acciones?estado=pendiente')
    ])
    kpis.value = d.data.data; reportes.value = r.data.data?.data || r.data.data || []; acciones.value = a.data.data?.data || a.data.data || []
  } catch {}
}

onMounted(cargarDatos)
</script>

<style lang="scss" scoped>

.sms-quick-card { transition: all 0.2s; background: rgba(255,255,255,0.03); }
.sms-quick-card:hover { transform: translateY(-4px); border-color: rgba(161,11,19,0.4) !important; }
.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.border-bottom { border-bottom: 1px solid rgba(255,255,255,0.05); }
.min-h-600 { min-height: 600px; }

.kpi-icon-premium { width: 50px; height: 50px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.05); &.icon-sm { width: 38px; height: 38px; border-radius: 10px; } }
.riesgo-hex-indicator {
  width: 42px; height: 42px; border-radius: 10px; font-family: 'JetBrains Mono';
  font-weight: 900; color: white; display: flex; align-items: center; justify-content: center;
}

.hover-row { transition: all 0.2s; &:hover { background: rgba(255,255,255,0.03); transform: translateX(5px); } }

.matriz-ops-container { background: rgba(0,0,0,0.2); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05); @media (max-width: 600px) { padding: 15px 5px; } @media (min-width: 601px) { padding: 50px; } }
.matriz-grid-premium { display: flex; flex-direction: column; gap: 10px; }
.matriz-row-premium { display: flex; gap: 4px; align-items: center; @media (min-width: 601px) { gap: 10px; } }
.matriz-cell-premium {
  flex: 1; display: flex; align-items: center; justify-content: center;
  border-radius: 8px; color: white; transition: transform 0.2s;
  @media (max-width: 600px) { height: 40px; border-radius: 6px; font-size: 12px; }
  @media (min-width: 601px) { height: 60px; border-radius: 12px; }
  &:hover { transform: scale(1.05); z-index: 10; border: 1px solid white; cursor: help; }
}
.cell-safe { background: #10b981; }
.cell-caution { background: #f59e0b; }
.cell-danger { background: #A10B13; }

.row-num-premium { display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 11px; border-radius: 6px; @media (max-width: 600px) { width: 30px; height: 40px; } @media (min-width: 601px) { width: 50px; height: 60px; font-size: 14px; } }
.matriz-cell-num-premium { flex: 1; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 11px; border-radius: 6px; @media (max-width: 600px) { height: 30px; } @media (min-width: 601px) { height: 40px; font-size: 14px; } }
.y-axis-label-premium { position: absolute; left: -10px; top: 50%; transform: rotate(-90deg) translateY(-50%); font-size: 8px; color: #475569; letter-spacing: 2px; }
.x-axis-label-premium { text-align: center; margin-top: 30px; font-size: 10px; color: #475569; letter-spacing: 2px; }

.legend-box-premium { width: 18px; height: 18px; border-radius: 5px; }
.risk-safe { background: #10b981; }
.risk-caution { background: #f59e0b; }
.risk-danger { background: #A10B13; }

.pulse-red { animation: pulseRed 2s infinite; }
@keyframes pulseRed {
  0% { box-shadow: 0 0 0 0 rgba(161, 11, 19, 0.6); }
  70% { box-shadow: 0 0 0 15px rgba(161, 11, 19, 0); }
  100% { box-shadow: 0 0 0 0 rgba(161, 11, 19, 0); }
}

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
</style>
