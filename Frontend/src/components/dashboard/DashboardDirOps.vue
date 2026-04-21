<template>
  <div class="animate-fade">
    
    <div v-if="cargando" class="flex flex-center q-pa-xl" style="height: 60vh">
      <q-spinner-cube color="red-9" size="60px" />
    </div>

    <template v-else-if="data">
      <!-- ════════ GRID DE INDICADORES OPERATIVOS (KPIs) ════════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpisDirOps" :key="kpi.label" class="col-6 col-sm-4 col-md-2">
          <q-card class="premium-glass-card kpi-mini-card text-center overflow-hidden">
            <q-card-section class="q-pa-md">
              <div class="text-h4 font-mono text-weight-bolder text-white q-mt-xs">
                {{ kpi.valor }}
              </div>
              <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:8px">
                {{ kpi.label }}
              </div>
            </q-card-section>
            <div class="kpi-accent-bar" :style="`background: ${kpi.colorHex || '#A10B13'}`"></div>
          </q-card>
        </div>
      </div>

      <!-- ════════ FILA CENTRAL: ALERTAS Y SEGURIDAD ════════ -->
      <div class="row q-col-gutter-xl q-mb-xl">
        
        <!-- Bloque 1: Alertas RAC (Vencimientos) -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl full-height border-red-left">
            <div class="row items-center justify-between q-mb-xl">
              <div class="row items-center">
                <q-icon name="alarm" color="red-9" size="24px" class="q-mr-md" />
                <div class="text-h6 font-head text-white">Alertas de Cumplimiento (30D)</div>
              </div>
              <q-btn flat dense color="red-9" label="Ver Expedientes" to="/vencimientos" class="text-weight-bold font-mono" style="font-size:10px" />
            </div>

            <div v-if="!vencCriticos.length" class="flex flex-center q-pa-xl text-emerald op-50">
              <q-icon name="verified" size="48px" class="q-mb-md" />
              <div class="font-mono text-weight-bold">SIN VENCIMIENTOS CRÍTICOS</div>
            </div>

            <div v-else class="q-gutter-y-md">
              <div v-for="v in vencCriticos.slice(0, 5)" :key="v.id" 
                class="premium-glass-card alert-item-ops q-pa-md"
              >
                <div class="row items-center no-wrap">
                  <div class="alert-indicator" :class="v.nivel_calculado"></div>
                  <div class="col q-pl-md">
                    <div class="text-weight-bold text-grey-2">{{ v.descripcion }}</div>
                    <div class="text-caption font-mono text-grey-6" style="font-size:10px">
                      {{ dayjs(v.fecha_vencimiento).format('DD/MM/YYYY') }} · 
                      <span :class="v.dias_restantes <= 5 ? 'text-red-9' : 'text-amber'">{{ v.dias_restantes <= 0 ? 'VENCIDO' : `RESTAN ${v.dias_restantes} DÍAS` }}</span>
                    </div>
                  </div>
                  <q-icon :name="v.dias_restantes <= 0 ? 'gpp_bad' : 'warning'" :color="v.dias_restantes <= 0 ? 'red-9' : 'amber-9'" size="20px" />
                </div>
              </div>
            </div>
          </q-card>
        </div>

        <!-- Bloque 2: SMS & Seguridad Operacional -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <div class="row items-center justify-between q-mb-xl">
              <div class="row items-center">
                <q-icon name="security" color="emerald" size="24px" class="q-mr-md" />
                <div class="text-h6 font-head text-white">Inteligencia SMS</div>
              </div>
              <q-btn flat dense color="emerald" label="Monitor SMS" to="/sms" class="text-weight-bold font-mono" style="font-size:10px" />
            </div>

            <div class="sms-stat-grid q-mb-xl">
              <div class="sms-box" v-for="s in smsKpis" :key="s.label">
                <div class="text-h4 font-mono text-weight-bolder" :class="s.class">{{ s.val }}</div>
                <div class="text-caption text-grey-6 uppercase font-mono tracking-widest" style="font-size:8px">{{ s.label }}</div>
              </div>
            </div>

            <q-separator dark class="q-my-xl opacity-10" />

            <div class="text-overline text-grey-5 font-mono q-mb-md">ACCESO RÁPIDO OPERACIONES</div>
            <div class="row q-gutter-sm">
              <q-btn unelevated color="red-9" icon="flight_takeoff" label="Bitácora" to="/vuelo/nueva-bitacora" class="premium-btn-sm" />
              <q-btn outline color="emerald" icon="report" label="Reporte SMS" to="/sms/nuevo-reporte" class="premium-btn-sm" />
              <q-btn outline color="amber-9" icon="calendar_today" label="Reservas" to="/reservas" class="premium-btn-sm" />
            </div>
          </q-card>
        </div>
      </div>

      <!-- ════════ AERONAVES EN MANTENIMIENTO ════════ -->
      <div v-if="data.aeronaves_mantenimiento?.length" class="row q-mb-xl">
        <div class="col-12">
          <q-card class="premium-glass-card q-pa-xl border-amber-left">
            <div class="row items-center q-mb-xl">
              <q-icon name="handyman" color="amber-9" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Status de Flota en Taller</div>
            </div>

            <div class="row q-col-gutter-lg">
              <div v-for="a in data.aeronaves_mantenimiento" :key="a.id" class="col-12 col-sm-6 col-md-3">
                <q-card class="premium-glass-card q-pa-lg cursor-pointer maintenance-aero-card" @click="$router.push(`/aeronaves/${a.id}`)">
                  <div class="row items-center justify-between no-wrap">
                    <div>
                      <div class="text-h6 text-amber-9 font-head">{{ a.matricula }}</div>
                      <div class="text-caption text-grey-6 font-mono">{{ a.modelo }}</div>
                    </div>
                    <q-badge color="amber-9" label="TALLER" class="q-pa-xs font-mono" />
                  </div>
                  <div v-if="a.mel_abiertos?.length" class="text-caption text-red-5 q-mt-md font-mono" style="font-size:10px">
                    {{ a.mel_abiertos.length }} ITEMS MEL DIFERIDOS
                  </div>
                </q-card>
              </div>
            </div>
          </q-card>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

const props = defineProps({
  data:     { type: Object,  default: null },
  cargando: { type: Boolean, default: false },
})

const vencCriticos = computed(() => {
  return (props.data?.vencimientos_criticos || []).filter(v => v.dias_restantes <= 30)
})

const kpisDirOps = computed(() => [
  { label: 'Estudiantes',  valor: props.data?.resumen_escuela?.estudiantes_activos ?? 0, colorHex: '#A10B13' },
  { label: 'Instructores', valor: props.data?.resumen_escuela?.instructores_activos ?? 0, colorHex: '#2dd4bf' },
  { label: 'Aeronaves',    valor: props.data?.resumen_escuela?.aeronaves_disponibles ?? 0, colorHex: '#10b981' },
  { label: 'Vuelos Hoy',   valor: props.data?.resumen_escuela?.vuelos_hoy ?? 0, colorHex: '#f59e0b' },
  { label: 'SMS Pend.',    valor: props.data?.reportes_sms_nuevos ?? 0, colorHex: '#ef4444' },
  { label: 'Alertas RAC',  valor: props.data?.vencimientos_criticos?.length ?? 0, colorHex: '#a78bfa' }
])

const smsKpis = computed(() => [
  { label: 'Reportes (3M)', val: props.data?.sms_kpis?.total_reportes ?? 0, class: 'text-white' },
  { label: 'Nivel Rojo',    val: props.data?.sms_kpis?.inaceptables ?? 0,   class: props.data?.sms_kpis?.inaceptables > 0 ? 'text-red-9' : 'text-emerald op-50' },
  { label: 'Pendientes',    val: props.data?.sms_kpis?.acciones_pendientes ?? 0, class: 'text-amber' },
  { label: 'Vencidas',      val: props.data?.sms_kpis?.acciones_vencidas ?? 0,   class: props.data?.sms_kpis?.acciones_vencidas > 0 ? 'text-red-10' : 'text-emerald op-50' }
])
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.kpi-mini-card { border: none !important; position: relative; }
.kpi-accent-bar { height: 3px; position: absolute; bottom: 0; left: 0; right: 0; opacity: 0.8; }

.border-red-left { border-left: 5px solid #A10B13 !important; }
.border-amber-left { border-left: 5px solid #f59e0b !important; }

.alert-item-ops {
  border: none !important;
  transition: all 0.2s;
  &:hover { transform: translateX(5px); background: rgba(255,255,255,0.05) !important; }
}

.alert-indicator {
  width: 8px; height: 8px; border-radius: 50%;
  &.critico { background: #A10B13; box-shadow: 0 0 10px #A10B13; }
  &.advertencia { background: #f59e0b; box-shadow: 0 0 10px #f59e0b; }
  &.info { background: #10b981; }
}

.sms-stat-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;
  .sms-box { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; text-align: center; }
}

.maintenance-aero-card {
  border: none !important; transition: all 0.2s;
  &:hover { background: rgba(245, 158, 11, 0.05) !important; transform: translateY(-5px); }
}

.premium-btn-sm { font-size: 11px; font-family: 'JetBrains Mono'; font-weight: 700; border-radius: 8px; padding: 8px 16px; }
.op-50 { opacity: 0.5; }
</style>
