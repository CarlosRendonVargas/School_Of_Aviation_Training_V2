<template>
  <div class="animate-fade">
    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-cube color="red-9" size="40px" />
    </div>

    <template v-else-if="data">
      <!-- ══ KPI ROW: BRIEFING INDIVIDUAL ══ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div class="col-6 col-md-3" v-for="stat in quickStats" :key="stat.label">
          <q-card class="premium-glass-card q-pa-lg text-center">
            <div class="kpi-icon-round q-mx-auto q-mb-md" :style="`background: ${stat.bg}`">
              <q-icon :name="stat.icon" :color="stat.color" size="24px" />
            </div>
            <div class="text-h4 font-mono text-weight-bolder text-white line-height-1">
              {{ stat.value }}
            </div>
            <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">
              {{ stat.label }}
            </div>
          </q-card>
        </div>
      </div>

      <!-- ══ PROGRESO DE ENTRENAMIENTO RAC 61 ══ -->
      <q-card class="premium-glass-card q-mb-xl q-pa-xl border-red-left">
        <div class="row items-center q-mb-xl">
          <q-icon name="analytics" color="red-9" size="24px" class="q-mr-md" />
          <div>
            <div class="text-h6 font-head text-white">Progreso de Instrucción (Syllabus)</div>
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:10px">Cumplimiento RAC 61 · {{ data.progreso_horas?.programa }}</div>
          </div>
        </div>

        <div class="row q-col-gutter-xl">
          <div v-for="(cat, key) in categorias" :key="key" class="col-12 col-md-4">
             <div class="row justify-between items-end q-mb-xs">
                <div class="text-caption text-grey-4 font-mono" style="font-size:11px">{{ cat.label }}</div>
                <div class="font-mono text-weight-bold" style="font-size:12px">
                  <span class="text-white">{{ cat.acumulado }}h</span>
                  <span class="text-grey-7 q-ml-xs">/ {{ cat.requerido || cat.limite_max }}h</span>
                </div>
             </div>
             <q-linear-progress :value="Math.min(cat.porcentaje, 100) / 100" color="red-9" class="q-mt-xs" rounded size="8px" />
             <div class="row justify-between q-mt-xs">
                <div class="text-caption font-mono" :class="cat.cumplido ? 'text-emerald' : 'text-grey-6'" style="font-size:9px">
                  {{ cat.cumplido ? '✓ COMPLETADO' : `FALTAN ${cat.faltante}H` }}
                </div>
                <div class="text-caption text-grey-7 font-mono" style="font-size:10px">{{ cat.porcentaje }}%</div>
             </div>
          </div>
        </div>
      </q-card>

      <!-- ══ PRÓXIMA MISIÓN & ALERTAS ══ -->
      <div class="row q-col-gutter-xl">
        <!-- Próxima Reserva -->
        <div class="col-12 col-md-7">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <div class="row items-center q-mb-xl">
              <q-icon name="flight_takeoff" color="emerald" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Siguiente Misión Programada</div>
            </div>

            <template v-if="data.proxima_reserva">
              <div class="mission-brief-card q-pa-xl">
                <div class="row items-center">
                  <div class="col">
                    <div class="text-h4 font-head text-weight-bolder text-white">{{ formatFecha(data.proxima_reserva.fecha) }}</div>
                    <div class="text-h6 font-mono text-red-9">{{ data.proxima_reserva.hora_inicio }} – {{ data.proxima_reserva.hora_fin }}</div>
                  </div>
                  <div class="col-auto text-right">
                    <q-badge color="emerald" class="q-pa-sm font-mono text-weight-bold">CONFIRMADO</q-badge>
                  </div>
                </div>
                
                <q-separator dark class="q-my-xl opacity-10" />
                
                <div class="row q-col-gutter-lg">
                  <div class="col-6">
                    <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:9px">Aeronave HK</div>
                    <div class="text-h6 text-grey-2 font-head">{{ data.proxima_reserva.aeronave?.matricula }}</div>
                  </div>
                  <div class="col-6">
                    <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:9px">Instructor al Mando</div>
                    <div class="text-h6 text-grey-2 font-head">{{ data.proxima_reserva.instructor?.persona?.nombres }}</div>
                  </div>
                </div>
              </div>
            </template>
            <div v-else class="text-center q-pa-xl text-grey-6">
              <q-icon name="event_busy" size="48px" class="q-mb-md" />
              <div class="font-mono">Sin vuelos programados en las próximas 48 horas.</div>
              <q-btn flat color="red-9" label="Solicitar Nueva Reserva" class="q-mt-md" to="/reservas" />
            </div>
          </q-card>
        </div>

        <!-- Alertas Médicas y Licencias -->
        <div class="col-12 col-md-5">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <div class="row items-center q-mb-xl">
              <q-icon name="notification_important" color="red-9" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Vencimientos Críticos</div>
            </div>

            <div v-for="alerta in data.vencimientos" :key="alerta.id" class="alert-item premium-glass-card q-pa-lg q-mb-md">
               <div class="row items-center no-wrap">
                  <q-icon :name="iconAlerta(alerta.nivel_calculado)" :color="alerta.nivel_calculado === 'critico' ? 'red-9' : 'orange-9'" size="28px" class="q-mr-md" />
                  <div>
                    <div class="text-weight-bold text-grey-2">{{ alerta.descripcion }}</div>
                    <div class="text-caption font-mono text-grey-6" style="font-size:11px">EXPIRA: {{ dayjs(alerta.fecha_vencimiento).format('DD/MM/YYYY') }} · {{ alerta.dias_restantes }} DÍAS</div>
                  </div>
               </div>
            </div>

            <div v-if="!data.vencimientos?.length" class="text-center q-pa-xl text-emerald">
               <q-icon name="verified" size="48px" class="q-mb-md" />
               <div class="font-mono text-weight-bold">STATUS OPERATIVO: CLEAR</div>
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

const quickStats = computed(() => [
  { label: 'Horas Voladas', value: `${props.data?.progreso_horas?.categorias?.vuelo_total?.acumulado ?? 0}h`, icon: 'speed', color: 'red-9', bg: 'rgba(161,11,19,0.1)' },
  { label: 'Status Médico', value: props.data?.tiene_medico ? 'VIGENTE' : 'VENCIDO', icon: 'medical_services', color: props.data?.tiene_medico ? 'emerald' : 'red-9', bg: 'rgba(161,11,19,0.1)' },
  { label: 'Misiones Realizadas', value: props.data?.progreso_horas?.total_vuelos ?? 0, icon: 'vibration', color: 'red-9', bg: 'rgba(161,11,19,0.1)' },
  { label: 'Check UAEAC', value: props.data?.listo_para_examen ? 'LISTO' : 'FASE', icon: 'military_tech', color: 'red-9', bg: 'rgba(161,11,19,0.1)' },
])

const categorias = computed(() => {
  const cats = props.data?.progreso_horas?.categorias || {}
  const labels = {
    vuelo_total: 'Experiencia Total',
    dual:        'Instrucción Dual',
    solo:        'Vuelo Solo / Pic',
    noche:       'Vuelo Nocturno',
    ifr:         'Instrucción IFR',
    simulador:   'Simulador (FTD)',
  }
  return Object.entries(cats)
    .filter(([, v]) => (v.requerido || v.limite_max) > 0)
    .reduce((acc, [k, v]) => {
      acc[k] = { ...v, label: labels[k] || k }
      return acc
    }, {})
})

function formatFecha(fecha) { return dayjs(fecha).format('dddd, D [de] MMMM') }
function iconAlerta(nivel) { return { critico: 'error', advertencia: 'warning', info: 'info' }[nivel] || 'info' }
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.kpi-icon-round { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.line-height-1 { line-height: 1.1; }
.text-emerald { color: #10b981; }
.border-red-left { border-left: 5px solid #A10B13 !important; }
.mission-brief-card { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; }
.alert-item { border: none !important; transition: all 0.2s; &:hover { transform: scale(1.02); background: rgba(161,11,19,0.05) !important; } }
</style>
