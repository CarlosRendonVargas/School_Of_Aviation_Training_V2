<template>
  <div class="animate-fade">
    
    <div v-if="cargando" class="flex flex-center q-pa-xl" style="height: 60vh">
      <q-spinner-cube color="red-9" size="60px" />
    </div>

    <template v-else-if="data">
      <!-- ════════ KPI ROW: BRIEFING DEL INSTRUCTOR ════════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpisInstructor" :key="kpi.label" class="col-6 col-sm-4 col-md-4">
          <q-card class="premium-glass-card q-pa-lg text-center border-red-low">
            <div class="kpi-icon-round q-mx-auto q-mb-md" :style="`background: ${kpi.bg}`">
              <q-icon :name="kpi.icon" :color="kpi.color" size="24px" />
            </div>
            <div class="text-h4 font-mono text-weight-bolder text-white">
              {{ kpi.valor }}
            </div>
            <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">
              {{ kpi.label }}
            </div>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-xl">
        <!-- Columna Izquierda: Vuelos de Hoy (Briefing) -->
        <div class="col-12 col-md-8">
          <q-card class="premium-glass-card q-pa-xl border-red-left">
            <div class="row items-center justify-between q-mb-xl">
              <div class="row items-center">
                <q-icon name="flight_takeoff" color="red-9" size="24px" class="q-mr-md" />
                <div class="text-h6 font-head text-white">Misiones Programadas (Hoy)</div>
              </div>
              <q-btn flat dense color="red-9" label="Ver Bitácoras" to="/vuelo" class="font-mono text-weight-bold" style="font-size:10px" />
            </div>

            <div v-if="!data.vuelos_hoy?.length" class="text-center q-pa-xl text-grey-6">
              <q-icon name="event_busy" size="48px" class="q-mb-md opacity-30" />
              <div class="font-mono">No hay vuelos programados para usted en la jornada de hoy.</div>
            </div>

            <div v-else class="q-gutter-y-md">
              <div v-for="v in data.vuelos_hoy" :key="v.id" class="mission-item q-pa-lg premium-glass-card">
                <div class="row items-center no-wrap">
                  <div class="col-auto">
                    <div class="text-h6 text-red-9 font-mono">{{ v.hora_inicio }} – {{ v.hora_fin }}</div>
                    <div class="text-caption text-grey-6 font-mono">HORARIO LOCAL</div>
                  </div>
                  
                  <q-separator vertical dark class="q-mx-xl opacity-10" />
                  
                  <div class="col">
                    <div class="text-caption text-grey-5 uppercase font-mono" style="font-size:8px">Estudiante</div>
                    <div class="text-weight-bold text-grey-2 font-head">{{ v.estudiante?.persona?.nombres }} {{ v.estudiante?.persona?.apellidos }}</div>
                  </div>

                  <div class="col">
                    <div class="text-caption text-grey-5 uppercase font-mono" style="font-size:8px">Aeronave HK</div>
                    <div class="text-weight-bold text-grey-2 font-mono">{{ v.aeronave?.matricula }}</div>
                  </div>

                  <div class="col-auto">
                    <q-btn unelevated color="white" text-color="black" label="Briefing" size="sm" class="font-mono text-weight-bolder" />
                  </div>
                </div>
              </div>
            </div>
          </q-card>
        </div>

        <!-- Columna Derecha: Alertas & Estudiantes -->
        <div class="col-12 col-md-4">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <div class="row items-center q-mb-xl">
              <q-icon name="notification_important" color="red-9" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Alertas Personales</div>
            </div>

            <div v-for="v in data.mis_vencimientos" :key="v.id" class="alert-item-inst q-pa-md q-mb-md">
                <div class="row items-center no-wrap">
                  <q-icon :name="v.dias_restantes <= 15 ? 'error' : 'warning'" :color="v.dias_restantes <= 15 ? 'red-9' : 'amber-9'" size="20px" class="q-mr-md" />
                  <div class="col">
                    <div class="text-weight-bold text-grey-2" style="font-size:12px">{{ v.descripcion }}</div>
                    <div class="text-caption font-mono text-grey-6" style="font-size:10px">EXP: {{ dayjs(v.fecha_vencimiento).format('DD/MM/YYYY') }}</div>
                  </div>
                </div>
            </div>
            
            <q-separator dark class="q-my-xl opacity-10" />

            <div class="row items-center q-mb-lg">
              <q-icon name="groups" color="grey-5" size="20px" class="q-mr-md" />
              <div class="text-subtitle1 font-head text-white">Estudiantes Asignados</div>
            </div>

            <q-list dense class="q-gutter-y-sm">
                <q-item v-for="est in data.estudiantes_asignados" :key="est.id" class="q-pa-sm bg-white-5 rounded-8">
                  <q-item-section avatar>
                    <q-avatar size="28px" color="red-9" text-color="white">{{ est.persona?.nombres[0] }}</q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold text-grey-3" style="font-size:12px">{{ est.persona?.nombres }}</q-item-label>
                  </q-item-section>
                </q-item>
            </q-list>
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

const kpisInstructor = computed(() => [
  { label: 'Instrucción Mes', valor: `${props.data?.horas_mes?.toFixed(1) ?? '0.0'}h`, icon: 'history_edu', color: 'red-9', bg: 'rgba(161,11,19,0.1)' },
  { label: 'Alumnos Activos', valor: props.data?.estudiantes_asignados?.length ?? 0, icon: 'groups', color: 'emerald', bg: 'rgba(16,185,129,0.1)' },
  { label: 'Estatus Licencia', valor: `${props.data?.instructor?.dias_venc ?? '–'}d`, icon: 'badge', color: (props.data?.instructor?.dias_venc < 30 ? 'red-10' : 'amber-9'), bg: 'rgba(245,158,11,0.1)' },
])
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.kpi-icon-round { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-left { border-left: 5px solid #A10B13 !important; }

.mission-item {
  border: none !important;
  transition: all 0.2s;
  &:hover { transform: scale(1.01); background: rgba(255,255,255,0.05) !important; }
}

.alert-item-inst { border-radius: 12px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); }
.bg-white-5 { background: rgba(255,255,255,0.05); }
.rounded-8 { border-radius: 8px; }
</style>
