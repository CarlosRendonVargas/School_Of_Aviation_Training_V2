<template>
  <q-page padding>
    <div class="text-h5 q-mb-md">Reportes Estadísticos UAEAC</div>

    <!-- Filtros período -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-6 col-sm-2">
          <q-input v-model="filtros.desde" type="date" label="Desde" dense outlined />
        </div>
        <div class="col-6 col-sm-2">
          <q-input v-model="filtros.hasta" type="date" label="Hasta" dense outlined />
        </div>
        <div class="col-6 col-sm-2">
          <q-btn icon="refresh" label="Actualizar" color="primary" unelevated @click="cargarTodo" :loading="loading" />
        </div>
        <div class="col-6 col-sm-2">
          <q-btn icon="picture_as_pdf" label="Exportar PDF" color="negative" outline @click="exportarPdf" />
        </div>
      </q-card-section>
    </q-card>

    <!-- KPIs Generales -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-6 col-sm-3" v-for="k in kpisGenerales" :key="k.label">
        <q-card flat bordered class="text-center q-pa-md">
          <q-icon :name="k.icon" :color="k.color" size="lg" class="q-mb-xs" />
          <div class="text-h4 text-weight-bold" :class="`text-${k.color}`">{{ k.value }}</div>
          <div class="text-caption text-grey-6">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Sección Académica -->
    <div class="text-subtitle1 text-weight-bold q-mb-sm">Módulo Académico</div>
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3" v-for="k in kpisAcademico" :key="k.label">
        <q-card flat bordered class="text-center q-pa-sm">
          <div class="text-h5 text-weight-bold text-primary">{{ k.value }}</div>
          <div class="text-caption text-grey-6">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Sección SMS -->
    <div class="text-subtitle1 text-weight-bold q-mb-sm">SMS — Seguridad Operacional</div>
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3" v-for="k in kpisSms" :key="k.label">
        <q-card flat bordered class="text-center q-pa-sm">
          <div class="text-h5 text-weight-bold" :class="k.alert ? 'text-negative' : 'text-primary'">{{ k.value }}</div>
          <div class="text-caption text-grey-6">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Sección Cumplimiento -->
    <div class="text-subtitle1 text-weight-bold q-mb-sm">Cumplimiento RAC 141</div>
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-3" v-for="k in kpisCumplimiento" :key="k.label">
        <q-card flat bordered class="text-center q-pa-sm">
          <div class="text-h5 text-weight-bold" :class="k.alert ? 'text-warning' : 'text-positive'">{{ k.value }}</div>
          <div class="text-caption text-grey-6">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Tabla Correspondencia Reciente -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="text-subtitle2 q-mb-sm">Correspondencia UAEAC Reciente</div>
        <q-table
          :rows="correspondencia"
          :columns="colsCorresp"
          flat dense
          :pagination="{ rowsPerPage: 5 }"
          :loading="loading"
        >
          <template #body-cell-estado="{ row }">
            <q-td>
              <q-badge :color="{ recibido: 'blue', en_revision: 'orange', respondido: 'positive', archivado: 'grey' }[row.estado]"
                :label="row.estado.replace('_', ' ')" />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Tabla Enmiendas -->
    <q-card flat bordered>
      <q-card-section>
        <div class="text-subtitle2 q-mb-sm">Enmiendas MOE/PIA</div>
        <q-table
          :rows="enmiendas"
          :columns="colsEnmiendas"
          flat dense
          :pagination="{ rowsPerPage: 5 }"
          :loading="loading"
        />
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()
const loading = ref(false)
const dashboard = ref({})
const smsDashboard = ref({})
const corrStats = ref({})
const correspondencia = ref([])
const enmiendas = ref([])
const vencimientos = ref({})

const filtros = ref({
  desde: new Date(new Date().getFullYear(), 0, 1).toISOString().slice(0, 10),
  hasta: new Date().toISOString().slice(0, 10),
})

const kpisGenerales = computed(() => [
  { label: 'Estudiantes Activos', value: dashboard.value.estudiantes_activos ?? 0, icon: 'school', color: 'primary' },
  { label: 'Aeronaves Activas', value: dashboard.value.aeronaves_activas ?? 0, icon: 'flight', color: 'blue' },
  { label: 'Horas Vuelo (año)', value: dashboard.value.horas_vuelo_anio ?? 0, icon: 'schedule', color: 'orange' },
  { label: 'Reportes SMS Abiertos', value: smsDashboard.value.abiertos ?? 0, icon: 'warning', color: 'negative' },
])

const kpisAcademico = computed(() => [
  { label: 'Matrículas Activas', value: dashboard.value.matriculas_activas ?? 0 },
  { label: 'Horas Vuelo Mes', value: dashboard.value.horas_vuelo_mes ?? 0 },
  { label: 'Certificados Emitidos', value: dashboard.value.certificados_emitidos ?? 0 },
  { label: 'Vencimientos Críticos', value: vencimientos.value.criticos ?? 0 },
])

const kpisSms = computed(() => [
  { label: 'Reportes Totales', value: smsDashboard.value.total_reportes ?? 0 },
  { label: 'En Investigación', value: smsDashboard.value.en_investigacion ?? 0, alert: (smsDashboard.value.en_investigacion ?? 0) > 5 },
  { label: 'Acciones Pendientes', value: smsDashboard.value.acciones_pendientes ?? 0, alert: (smsDashboard.value.acciones_pendientes ?? 0) > 3 },
  { label: 'Índice de Cierre', value: smsDashboard.value.indice_cierre ? smsDashboard.value.indice_cierre + '%' : '—' },
])

const kpisCumplimiento = computed(() => [
  { label: 'Correspondencia Pendiente', value: corrStats.value.pendientes ?? 0, alert: (corrStats.value.pendientes ?? 0) > 0 },
  { label: 'Correspondencia Vencida', value: corrStats.value.vencidos ?? 0, alert: (corrStats.value.vencidos ?? 0) > 0 },
  { label: 'Enmiendas en Trámite', value: enmiendas.value.filter(e => e.estado === 'enviada_uaeac').length },
  { label: 'Documentos Activos', value: dashboard.value.documentos_vigentes ?? 0 },
])

const colsCorresp = [
  { name: 'numero_radicado', label: 'Radicado', field: 'numero_radicado', align: 'left' },
  { name: 'asunto', label: 'Asunto', field: 'asunto', align: 'left' },
  { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'center' },
  { name: 'fecha_documento', label: 'Fecha', field: r => fmtFecha(r.fecha_documento), align: 'center' },
  { name: 'estado', label: 'Estado', field: 'estado', align: 'center' },
]

const colsEnmiendas = [
  { name: 'numero_enmienda', label: 'N° Enmienda', field: 'numero_enmienda', align: 'left' },
  { name: 'descripcion', label: 'Descripción', field: 'descripcion', align: 'left' },
  { name: 'estado', label: 'Estado', field: 'estado', align: 'center' },
  { name: 'fecha_envio', label: 'Fecha Envío', field: r => fmtFecha(r.fecha_envio), align: 'center' },
]

async function cargarTodo() {
  loading.value = true
  try {
    const [d, sms, cs, corr, enm, v] = await Promise.allSettled([
      api.get('/dashboard'),
      api.get('/sms/dashboard'),
      api.get('/correspondencia/estadisticas'),
      api.get('/correspondencia', { params: { per_page: 5, estado: 'recibido' } }),
      api.get('/enmiendas'),
      api.get('/vencimientos'),
    ])
    if (d.status === 'fulfilled') dashboard.value = d.value.data?.data ?? d.value.data ?? {}
    if (sms.status === 'fulfilled') smsDashboard.value = sms.value.data?.data ?? sms.value.data ?? {}
    if (cs.status === 'fulfilled') corrStats.value = cs.value.data ?? {}
    if (corr.status === 'fulfilled') correspondencia.value = corr.value.data?.data ?? corr.value.data ?? []
    if (enm.status === 'fulfilled') enmiendas.value = enm.value.data?.data ?? enm.value.data ?? []
    if (v.status === 'fulfilled') {
      const vdata = v.value.data?.data ?? v.value.data ?? []
      vencimientos.value = { criticos: Array.isArray(vdata) ? vdata.length : 0 }
    }
  } finally {
    loading.value = false
  }
}

async function exportarPdf() {
  $q.notify({ type: 'info', message: 'Generando reporte PDF…' })
}

function fmtFecha(d) {
  if (!d) return '—'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(cargarTodo)
</script>
