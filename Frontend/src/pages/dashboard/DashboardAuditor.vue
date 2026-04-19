<template>
  <div>

    <!-- Banner auditor -->
    <q-banner dense rounded class="q-mb-lg"
      style="background:rgba(34,197,94,.07); border:1px solid rgba(34,197,94,.2); border-radius:10px">
      <template #avatar><q-icon name="verified_user" color="positive" /></template>
      <span style="font-size:13px; color:#86efac">
        Acceso de solo lectura — Vista de auditoría UAEAC · RAC 141.77
      </span>
    </q-banner>

    <!-- KPIs generales -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3" v-for="k in totales" :key="k.label">
        <div class="stat-card">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="stat-num" :style="`color:${k.color}`">{{ k.valor }}</div>
            <div class="stat-label">{{ k.label }}</div>
          </template>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-md">

      <!-- Vencidos críticos -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              🚨 Vencimientos críticos detectados
            </div>
            <q-skeleton v-if="cargando" v-for="n in 4" :key="n" type="rect" height="48px" dark class="q-mb-xs" />
            <VencimientoBadge
              v-for="v in vencidos" :key="v.id"
              :descripcion="v.descripcion"
              :tipo-entidad="v.tipo_entidad"
              :dias-restantes="-1"
              nivel="vencido"
            />
            <div v-if="!cargando && !vencidos.length"
              class="text-center q-py-md" style="color:#475569; font-size:13px">
              <q-icon name="check_circle" color="positive" size="28px" class="q-mb-xs" /><br>
              Sin vencimientos detectados
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Documentos vigentes -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              📄 Documentos RAC vigentes
            </div>
            <q-skeleton v-if="cargando" v-for="n in 4" :key="n" type="QItem" dark class="q-mb-xs" />
            <q-list v-else dark dense>
              <q-item v-for="doc in documentos" :key="doc.id" style="padding:8px 0">
                <q-item-section avatar>
                  <q-chip dense :color="colorDoc(doc.tipo)" text-color="white"
                    :label="doc.tipo" style="font-size:9px; font-family:monospace" />
                </q-item-section>
                <q-item-section>
                  <q-item-label style="font-size:12px; color:#e2e8f0">{{ doc.titulo?.slice(0,55) }}</q-item-label>
                  <q-item-label caption>v{{ doc.version }} · {{ doc.fecha_documento }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon v-if="doc.aprobado_uaeac" name="verified" color="positive" size="16px">
                    <q-tooltip>Aprobado UAEAC</q-tooltip>
                  </q-icon>
                </q-item-section>
              </q-item>
              <q-item v-if="!documentos.length">
                <q-item-section class="text-center q-py-md" style="color:#475569">Sin documentos</q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Instructores con licencia vencida -->
      <div v-if="instructoresVencidos.length" class="col-12">
        <q-card flat style="background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.2); border-radius:12px">
          <q-card-section>
            <div class="font-head q-mb-md" style="font-size:15px; font-weight:700; color:#ef4444">
              ⚠️ Instructores con licencia vencida (RAC 65)
            </div>
            <q-list dark dense>
              <q-item v-for="inst in instructoresVencidos" :key="inst.id">
                <q-item-section avatar>
                  <q-icon name="badge" color="negative" size="20px" />
                </q-item-section>
                <q-item-section>
                  <q-item-label style="font-size:13px; color:#fca5a5">
                    {{ inst.persona?.nombres }} {{ inst.persona?.apellidos }}
                  </q-item-label>
                  <q-item-label caption>
                    <span class="font-mono" style="color:#ef4444">Vencida: {{ inst.venc_licencia }}</span>
                    · {{ inst.num_licencia }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- SMS Resumen -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                🛡️ SMS — Últimos 12 meses
              </div>
              <q-btn flat dense no-caps to="/sms" color="positive" size="sm" label="Ver SMS" icon-right="arrow_forward" />
            </div>
            <div v-if="smsResumen" class="row q-col-gutter-sm">
              <div class="col-6" v-for="s in smsStats" :key="s.label">
                <div :style="`background:${s.bg}; border:1px solid ${s.border}; border-radius:8px; padding:12px`">
                  <div class="font-head" :style="`font-size:20px; font-weight:700; color:${s.color}`">{{ s.valor }}</div>
                  <div class="font-mono" style="font-size:10px; color:#64748b; margin-top:2px">{{ s.label }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Botones de exportación -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              📤 Exportar para inspección UAEAC
            </div>
            <div class="q-gutter-sm">
              <q-btn v-for="exp in exportaciones" :key="exp.label"
                unelevated no-caps class="full-width" :icon="exp.icono"
                :label="exp.label" :color="exp.color"
                style="border-radius:8px; margin-bottom:8px; justify-content:flex-start; padding:0 16px; height:44px"
                @click="exportar(exp.endpoint)" />
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import VencimientoBadge from 'components/common/VencimientoBadge.vue'

const $q    = useQuasar()
const props = defineProps({
  data:     { type: Object,  default: null },
  cargando: { type: Boolean, default: false },
})

const vencidos             = computed(() => props.data?.vencidos_criticos || [])
const documentos           = computed(() => props.data?.documentos_vigentes || [])
const instructoresVencidos = computed(() => props.data?.instructores_vencidos || [])
const smsResumen           = computed(() => props.data?.sms_resumen || null)

const totales = computed(() => {
  const t = props.data?.totales || {}
  return [
    { label: 'Estudiantes',  valor: t.estudiantes  || 0, color: '#3b82f6' },
    { label: 'Instructores', valor: t.instructores || 0, color: '#14b8a6' },
    { label: 'Aeronaves',    valor: t.aeronaves    || 0, color: '#22c55e' },
    { label: 'Egresados',    valor: t.egresados    || 0, color: '#f59e0b' },
  ]
})

const smsStats = computed(() => {
  const s = smsResumen.value || {}
  return [
    { label:'Total reportes',     valor: s.total_reportes      || 0, color:'#60a5fa', bg:'rgba(59,130,246,.06)',  border:'rgba(59,130,246,.15)' },
    { label:'Riesgo inaceptable', valor: s.inaceptables        || 0, color:'#ef4444', bg:'rgba(239,68,68,.06)',   border:'rgba(239,68,68,.15)'  },
    { label:'Acciones pend.',     valor: s.acciones_pendientes || 0, color:'#f59e0b', bg:'rgba(245,158,11,.06)',  border:'rgba(245,158,11,.15)' },
    { label:'Notif. UAEAC',       valor: s.notificados_uaeac   || 0, color:'#22c55e', bg:'rgba(34,197,94,.06)',   border:'rgba(34,197,94,.15)'  },
  ]
})

const exportaciones = [
  { label: 'Informe general UAEAC',    icono:'download', color:'teal',    endpoint:'/reportes/exportar-uaeac'          },
  { label: 'Registro de egresados',    icono:'school',   color:'primary', endpoint:'/reportes/egresados'              },
  { label: 'Historial SMS (12 meses)', icono:'security', color:'positive',endpoint:'/reportes/sms-anual'              },
  { label: 'Resumen de instructores',  icono:'badge',    color:'grey-7',  endpoint:'/reportes/instructores-resumen'   },
]

const colorDoc = (t) => ({ MOE:'primary', PIA:'teal', enmienda:'amber-9', circular:'purple', certificado:'positive' }[t] || 'grey')

async function exportar(endpoint) {
  $q.loading.show({ message: 'Generando informe…' })
  try {
    await api.get(endpoint)
    $q.notify({ type: 'positive', message: 'Informe generado. Revise su bandeja de descargas.' })
  } finally { $q.loading.hide() }
}
</script>
