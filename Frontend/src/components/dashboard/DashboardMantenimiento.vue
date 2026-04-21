<template>
  <div class="animate-fade">
    
    <div v-if="cargando" class="flex flex-center q-pa-xl" style="height: 60vh">
      <q-spinner-cube color="amber-9" size="60px" />
    </div>

    <template v-else-if="data">
      <!-- ════════ KPI ROW: STATUS DE INGENIERÍA ════════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpisMantenimiento" :key="kpi.label" class="col-6 col-sm-4 col-md-3">
          <q-card class="premium-glass-card q-pa-lg text-center border-amber-low">
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
        <!-- Columna Izquierda: Flota HK -->
        <div class="col-12 col-md-8">
          <q-card class="premium-glass-card q-pa-xl border-red-left">
            <div class="row items-center justify-between q-mb-xl">
              <div class="row items-center">
                <q-icon name="airplane_ticket" color="red-9" size="24px" class="q-mr-md" />
                <div class="text-h6 font-head text-white">Estado de Aeronavegabilidad (Flota HK)</div>
              </div>
              <q-btn flat dense color="red-9" label="Gestionar Flota" to="/aeronaves" class="font-mono text-weight-bold" style="font-size:10px" />
            </div>

            <div class="q-gutter-y-md">
              <div v-for="a in data.aeronaves" :key="a.id" class="fleet-status-item q-pa-lg premium-glass-card">
                <div class="row items-center no-wrap">
                  <div class="col-auto">
                    <div class="text-h6 text-white font-head">{{ a.matricula }}</div>
                    <div class="text-caption text-grey-6 font-mono">{{ a.modelo }}</div>
                  </div>
                  
                  <q-separator vertical dark class="q-mx-xl opacity-10" />
                  
                  <div class="col">
                    <div class="text-caption text-grey-5 uppercase font-mono" style="font-size:8px">Horas Célula</div>
                    <div class="text-weight-bold text-grey-2 font-mono">{{ a.horas_celula_total }} HT</div>
                  </div>

                  <div class="col">
                    <div class="text-caption text-grey-5 uppercase font-mono" style="font-size:8px">Airworthiness Exp.</div>
                    <div class="text-weight-bold" :class="isVencido(a.venc_airworthiness) ? 'text-red-9' : 'text-grey-2'">
                      {{ dayjs(a.venc_airworthiness).format('DD/MM/YYYY') }}
                    </div>
                  </div>

                  <div class="col-auto">
                    <q-badge :color="statusAeroColor(a.estado)" class="q-pa-sm font-mono text-weight-bold">
                      {{ a.estado.toUpperCase() }}
                    </q-badge>
                  </div>
                </div>
              </div>
            </div>
          </q-card>
        </div>

        <!-- Columna Derecha: Tareas & Diferidos -->
        <div class="col-12 col-md-4">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <!-- Mantenimientos Próximos -->
            <div class="row items-center q-mb-lg">
              <q-icon name="event_repeat" color="amber-9" size="24px" class="q-mr-md" />
              <div class="text-subtitle1 font-head text-white">Próximos Servicios</div>
            </div>
            
            <div class="q-gutter-y-sm q-mb-xl">
              <div v-for="m in data.mantenimientos_proximos" :key="m.id" class="task-item q-pa-md">
                <div class="text-weight-bold text-grey-3">{{ m.descripcion }}</div>
                <div class="text-caption font-mono text-amber-9 uppercase" style="font-size:10px">
                  PROYECTADO: {{ dayjs(m.proxima_fecha).format('DD/MM/YYYY') }}
                </div>
              </div>
              <div v-if="!data.mantenimientos_proximos?.length" class="text-center text-grey-6 italic q-pa-md">No hay servicios proyectados</div>
            </div>

            <q-separator dark class="q-my-xl opacity-10" />

            <!-- MEL / ADs -->
            <div class="row items-center q-mb-lg">
              <q-icon name="warning_amber" color="red-9" size="24px" class="q-mr-md" />
              <div class="text-subtitle1 font-head text-white">Ítems MEL / Directivas AD</div>
            </div>

            <div class="q-gutter-y-sm">
                <q-chip v-for="mel in data.mel_abiertos" :key="mel.id" 
                  outline color="red-9" text-color="white" icon="error_outline" class="full-width q-ma-none q-mb-sm"
                >
                  <div class="ellipsis">MEL: {{ mel.descripcion }}</div>
                </q-chip>

                <q-chip v-for="ad in data.ads_pendientes" :key="ad.id" 
                  outline color="amber-9" text-color="white" icon="report_problem" class="full-width q-ma-none q-mb-sm"
                >
                  <div class="ellipsis">AD: {{ ad.referencia }}</div>
                </q-chip>

                <div v-if="!data.mel_abiertos?.length && !data.ads_pendientes?.length" class="text-center text-emerald q-pa-md font-mono" style="font-size:11px">
                  <q-icon name="check_circle" class="q-mr-sm" /> SIN PENDIENTES CRÍTICOS
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

const kpisMantenimiento = computed(() => [
  { label: 'Flota Operativa', valor: props.data?.aeronaves?.length ?? 0, icon: 'flight', color: 'emerald', bg: 'rgba(16,185,129,0.1)' },
  { label: 'En Mantenimiento', valor: props.data?.aeronaves?.filter(a => a.estado === 'mantenimiento').length ?? 0, icon: 'handyman', color: 'amber-9', bg: 'rgba(245,158,11,0.1)' },
  { label: 'Items MEL', valor: props.data?.mel_abiertos?.length ?? 0, icon: 'error_outline', color: 'red-9', bg: 'rgba(161,11,19,0.1)' },
  { label: 'ADs Pendientes', valor: props.data?.ads_pendientes?.length ?? 0, icon: 'article', color: 'red-10', bg: 'rgba(161,11,19,0.1)' },
])

const isVencido = (f) => dayjs(f).isBefore(dayjs())
const statusAeroColor = (s) => ({ disponible: 'emerald', mantenimiento: 'amber-9', tierra: 'red-9' }[s] || 'grey')
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.kpi-icon-round { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.border-amber-low { border: 1px solid rgba(245, 158, 11, 0.2) !important; }
.border-red-left { border-left: 5px solid #A10B13 !important; }

.fleet-status-item {
  border: none !important;
  transition: all 0.2s;
  &:hover { transform: scale(1.01); background: rgba(255,255,255,0.05) !important; }
}

.task-item {
  background: rgba(245, 158, 11, 0.05);
  border: 1px solid rgba(245, 158, 11, 0.1);
  border-radius: 12px;
}
</style>
