<template>
  <div class="animate-fade">
    
    <div v-if="cargando" class="flex flex-center q-pa-xl" style="height: 60vh">
      <q-spinner-cube color="indigo-9" size="60px" />
    </div>

    <template v-else-if="data">
      <!-- ════════ KPI ROW: RESUMEN INSTITUCIONAL ════════ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div v-for="kpi in kpisAuditor" :key="kpi.label" class="col-6 col-md-3">
          <q-card class="premium-glass-card q-pa-lg text-center border-indigo-low">
            <div class="text-h3 font-mono text-weight-bolder text-white">
              {{ kpi.valor }}
            </div>
            <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">
              {{ kpi.label }}
            </div>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-xl">
        <!-- Columna Izquierda: Cumplimiento & Vencimientos -->
        <div class="col-12 col-md-7">
          <q-card class="premium-glass-card q-pa-xl border-red-left">
            <div class="row items-center q-mb-xl">
              <q-icon name="gavel" color="red-9" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Alertas de No-Cumplimiento (Urgente)</div>
            </div>

            <div v-if="!data.vencidos_criticos?.length" class="text-center q-pa-xl text-emerald">
              <q-icon name="verified_user" size="48px" class="q-mb-md opacity-40" />
              <div class="font-mono text-weight-bold">INSTITUCIÓN EN CUMPLIMIENTO TOTAL</div>
            </div>

            <div v-else class="q-gutter-y-md">
              <div v-for="v in data.vencidos_criticos" :key="v.id" class="alert-item-auditor q-pa-lg premium-glass-card">
                <div class="row items-center no-wrap">
                  <q-icon name="cancel" color="red-9" size="24px" class="q-mr-md" />
                  <div class="col">
                    <div class="text-weight-bold text-grey-2">{{ v.descripcion }}</div>
                    <div class="text-caption font-mono text-red-9 uppercase" style="font-size:10px">
                      VENCIDO EL {{ dayjs(v.fecha_vencimiento).format('DD/MM/YYYY') }}
                    </div>
                  </div>
                  <q-btn flat dense color="grey-6" icon="open_in_new" :to="`/estudiantes/${v.entidad_id}`" v-if="v.tipo_entidad === 'estudiante'" />
                </div>
              </div>
            </div>

            <q-separator dark class="q-my-xl opacity-10" />

            <div class="row items-center q-mb-lg">
              <q-icon name="badge" color="orange-9" size="20px" class="q-mr-md" />
              <div class="text-subtitle1 font-head text-white">Instructores con Licencia Vencida</div>
            </div>

            <div class="q-gutter-y-sm">
              <div v-for="inst in data.instructores_vencidos" :key="inst.id" class="q-pa-md bg-black-20 rounded-12 border-red-low">
                <div class="row items-center justify-between">
                  <div class="text-weight-bold text-grey-3">{{ inst.persona?.nombres }} {{ inst.persona?.apellidos }}</div>
                  <div class="text-caption font-mono text-red-9">LIC: {{ inst.num_licencia }}</div>
                </div>
              </div>
              <div v-if="!data.instructores_vencidos?.length" class="text-center text-grey-6 italic font-mono" style="font-size:11px">
                Cero instructores con licencias vencidas detectados.
              </div>
            </div>
          </q-card>
        </div>

        <!-- Columna Derecha: SMS & Documentación -->
        <div class="col-12 col-md-5">
          <q-card class="premium-glass-card q-pa-xl full-height">
            <div class="row items-center q-mb-xl">
              <q-icon name="safety_check" color="emerald" size="24px" class="q-mr-md" />
              <div class="text-h6 font-head text-white">Desempeño SMS (12 Meses)</div>
            </div>

            <div class="sms-auditor-stats">
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <div class="stat-box-auditor">
                    <div class="text-h4 text-white font-mono">{{ data.sms_resumen?.total_reportes ?? 0 }}</div>
                    <div class="text-caption text-grey-6 uppercase font-mono" style="font-size:8px">Reportes Totales</div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="stat-box-auditor">
                    <div class="text-h4 text-red-9 font-mono">{{ data.sms_resumen?.inaceptables ?? 0 }}</div>
                    <div class="text-caption text-grey-6 uppercase font-mono" style="font-size:8px">Índice Crítico</div>
                  </div>
                </div>
              </div>
            </div>

            <q-separator dark class="q-my-xl opacity-10" />

            <div class="row items-center q-mb-lg">
              <q-icon name="description" color="indigo-4" size="24px" class="q-mr-md" />
              <div class="text-subtitle1 font-head text-white">Documentación RAC Vigente</div>
            </div>

            <q-list dense class="q-gutter-y-sm">
                <q-item v-for="doc in data.documentos_vigentes" :key="doc.id" class="q-pa-md bg-white-5 rounded-8">
                  <q-item-section avatar>
                    <q-icon name="verified" color="emerald" size="20px" />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold text-grey-3">{{ doc.titulo }}</q-item-label>
                    <q-item-label caption class="text-grey-6 font-mono" style="font-size:10px">VIGENCIA: {{ dayjs(doc.vencimiento).format('MMM YYYY') }}</q-item-label>
                  </q-item-section>
                </q-item>
                <div v-if="!data.documentos_vigentes?.length" class="text-center text-grey-6 italic">Sin registros de biblioteca RAC.</div>
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

const kpisAuditor = computed(() => [
  { label: 'Estudiantes Registrados', valor: props.data?.totales?.estudiantes ?? 0 },
  { label: 'Instructores Certificados', valor: props.data?.totales?.instructores ?? 0 },
  { label: 'Aeronaves en Flota', valor: props.data?.totales?.aeronaves ?? 0 },
  { label: 'Egresados / Pilotos', valor: props.data?.totales?.egresados ?? 0 },
])
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.border-indigo-low { border: 1px solid rgba(111, 44, 245, 0.2) !important; }
.border-red-left { border-left: 5px solid #A10B13 !important; }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.1); }

.alert-item-auditor {
  border: none !important;
  background: rgba(161, 11, 19, 0.05) !important;
  border-radius: 16px;
}

.stat-box-auditor {
  background: rgba(0,0,0,0.2);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
}

.bg-black-20 { background: rgba(0,0,0,0.2); }
.bg-white-5 { background: rgba(255,255,255,0.05); }
.rounded-12 { border-radius: 12px; }
.rounded-8 { border-radius: 8px; }
</style>
