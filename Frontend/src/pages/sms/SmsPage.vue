<template>
  <q-page padding style="padding-bottom:80px">

    <!-- Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">
          OACI Anexo 19 · RAC 141
        </div>
        <div class="font-head text-white" style="font-size:20px; font-weight:700">
          Sistema de Gestión de Seguridad
        </div>
      </div>
      <q-btn unelevated color="positive" icon="add_alert" label="Reportar" no-caps
        @click="$router.push('/sms/nuevo-reporte')" style="border-radius:8px" />
    </div>

    <!-- Tabs -->
    <q-tabs v-model="tabActivo" dense align="left" class="q-mb-md"
      active-color="primary" indicator-color="primary" style="border-bottom:1px solid rgba(255,255,255,.08)">
      <q-tab name="dashboard" icon="bar_chart"   label="Dashboard" no-caps />
      <q-tab name="reportes"  icon="report"      label="Reportes"  no-caps />
      <q-tab name="acciones"  icon="task_alt"    label="Acciones"  no-caps />
      <q-tab name="matriz"    icon="grid_on"     label="Matriz OACI" no-caps />
    </q-tabs>

    <q-tab-panels v-model="tabActivo" animated dark style="background:transparent">

      <!-- ─── Dashboard SMS ─────────────────────────────────────────── -->
      <q-tab-panel name="dashboard" class="q-pa-none">
        <div class="row q-col-gutter-md q-mb-md">
          <div class="col-6 col-md-3" v-for="kpi in kpisSms" :key="kpi.label">
            <div class="stat-card">
              <div class="stat-num" :style="`color:${kpi.color}`">{{ kpi.valor }}</div>
              <div class="stat-label">{{ kpi.label }}</div>
            </div>
          </div>
        </div>

        <!-- Reportes recientes -->
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              Reportes recientes por nivel de riesgo
            </div>
            <q-skeleton v-if="cargandoKpis" v-for="n in 3" :key="n" type="rect" dark class="q-mb-sm border-radius-8" height="60px" />
            <q-list v-else dark separator>
              <q-item v-for="r in reportesRecientes" :key="r.id" class="q-px-none"
                clickable @click="verReporte(r)">
                <q-item-section avatar>
                  <div class="row items-center justify-center"
                    style="width:36px; height:36px; border-radius:8px"
                    :style="`background:${bgRiesgo(r.nivel_riesgo)}`">
                    <span class="font-mono text-white" style="font-size:13px; font-weight:700">
                      {{ r.nivel_riesgo }}
                    </span>
                  </div>
                </q-item-section>
                <q-item-section>
                  <q-item-label style="font-size:13px; color:#e2e8f0">{{ r.descripcion?.slice(0,80) }}…</q-item-label>
                  <q-item-label caption style="font-size:11px">
                    <q-chip dense :color="colorTipo(r.tipo)" text-color="white" :label="r.tipo.toUpperCase()"
                      style="font-size:9px; padding:2px 6px" />
                    · {{ r.fecha_evento?.slice(0,10) }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip dense :color="colorEstado(r.estado)" text-color="white"
                    :label="r.estado" style="font-size:10px" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <!-- ─── Lista de reportes ──────────────────────────────────────── -->
      <q-tab-panel name="reportes" class="q-pa-none">
        <q-table
          :rows="reportes"
          :columns="columnasReportes"
          :loading="cargandoReportes"
          flat dark class="tabla-rac"
          style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px"
          row-key="id"
        >
          <template #top>
            <div class="row full-width q-col-gutter-sm items-center">
              <div class="col-auto">
                <q-select v-model="filtroTipo" outlined dense dark bg-color="grey-10"
                  :options="opcionesTipo" emit-value map-options clearable label="Tipo" style="min-width:130px">
                </q-select>
              </div>
              <div class="col-auto">
                <q-select v-model="filtroEstado" outlined dense dark bg-color="grey-10"
                  :options="opcionesEstado" emit-value map-options clearable label="Estado" style="min-width:130px">
                </q-select>
              </div>
              <q-space />
              <div class="col-auto font-mono" style="font-size:11px; color:#475569">
                {{ reportes.length }} reportes
              </div>
            </div>
          </template>

          <template #body-cell-nivel_riesgo="{ row }">
            <q-td>
              <div class="row items-center justify-center"
                style="width:32px; height:32px; border-radius:6px; margin:auto"
                :style="`background:${bgRiesgo(row.nivel_riesgo)}`">
                <span class="font-mono text-white" style="font-size:12px; font-weight:700">{{ row.nivel_riesgo }}</span>
              </div>
            </q-td>
          </template>

          <template #body-cell-tipo="{ value }">
            <q-td>
              <q-chip dense :color="colorTipo(value)" text-color="white" :label="value.toUpperCase()" style="font-size:10px" />
            </q-td>
          </template>

          <template #body-cell-estado="{ value }">
            <q-td>
              <q-chip dense :color="colorEstado(value)" text-color="white" :label="value" style="font-size:10px" />
            </q-td>
          </template>

          <template #body-cell-acciones="{ row }">
            <q-td>
              <q-btn flat round dense icon="visibility" color="grey-5" size="sm" @click="verReporte(row)" />
            </q-td>
          </template>
        </q-table>
      </q-tab-panel>

      <!-- ─── Acciones correctivas ───────────────────────────────────── -->
      <q-tab-panel name="acciones" class="q-pa-none">
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="ac in acciones" :key="ac.id" style="padding:12px 16px">
            <q-item-section>
              <q-item-label style="font-size:13px; color:#e2e8f0">{{ ac.descripcion }}</q-item-label>
              <q-item-label caption style="font-size:11px; margin-top:4px">
                <span style="color:#64748b">Límite:</span>
                <span class="font-mono q-ml-xs" :style="`color:${esVencida(ac.fecha_limite) ? '#ef4444' : '#f59e0b'}`">
                  {{ ac.fecha_limite }}
                </span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-chip dense :color="colorAccion(ac.estado)" text-color="white" :label="ac.estado" style="font-size:10px" />
            </q-item-section>
          </q-item>
          <q-item v-if="!acciones.length" style="padding:32px">
            <q-item-section class="text-center" style="color:#475569; font-size:13px">
              Sin acciones correctivas pendientes
            </q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>

      <!-- ─── Matriz de riesgo OACI ──────────────────────────────────── -->
      <q-tab-panel name="matriz" class="q-pa-none">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-xs" style="font-size:15px; font-weight:700">
              Matriz de Riesgo OACI 5×5
            </div>
            <div class="font-mono q-mb-md" style="font-size:11px; color:#475569">
              Severidad × Probabilidad → Nivel de riesgo (1–25)
            </div>

            <!-- Eje Y: Severidad -->
            <div class="row items-center q-mb-xs">
              <div style="width:70px"></div>
              <div class="row q-col-gutter-xs" style="flex:1">
                <div v-for="p in 5" :key="p" class="col text-center font-mono"
                  style="font-size:10px; color:#64748b">P{{ p }}</div>
              </div>
            </div>

            <div v-for="s in [5,4,3,2,1]" :key="s" class="row items-center q-mb-xs">
              <div class="font-mono text-right q-pr-sm" style="width:70px; font-size:10px; color:#64748b">
                S{{ s }}
              </div>
              <div class="row q-col-gutter-xs" style="flex:1">
                <div v-for="p in 5" :key="p" class="col">
                  <div class="text-center font-mono text-white"
                    style="border-radius:6px; padding:8px 4px; font-size:13px; font-weight:700; cursor:default"
                    :style="`background:${bgRiesgo(s*p)}; min-height:36px; line-height:1.5`">
                    {{ s * p }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Leyenda -->
            <div class="row q-col-gutter-md q-mt-md">
              <div class="col-auto row items-center q-gutter-xs">
                <div style="width:14px; height:14px; border-radius:3px; background:#22c55e"></div>
                <span class="font-mono" style="font-size:10px; color:#94a3b8">1–4 Aceptable</span>
              </div>
              <div class="col-auto row items-center q-gutter-xs">
                <div style="width:14px; height:14px; border-radius:3px; background:#f59e0b"></div>
                <span class="font-mono" style="font-size:10px; color:#94a3b8">5–9 Tolerable</span>
              </div>
              <div class="col-auto row items-center q-gutter-xs">
                <div style="width:14px; height:14px; border-radius:3px; background:#ef4444"></div>
                <span class="font-mono" style="font-size:10px; color:#94a3b8">10–25 Inaceptable</span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>

    </q-tab-panels>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const tabActivo       = ref('dashboard')
const cargandoKpis    = ref(false)
const cargandoReportes = ref(false)
const kpis            = ref(null)
const reportes        = ref([])
const acciones        = ref([])
const filtroTipo      = ref(null)
const filtroEstado    = ref(null)

const opcionesTipo   = [
  { label: 'Peligro',   value: 'peligro'   },
  { label: 'Incidente', value: 'incidente' },
  { label: 'Accidente', value: 'accidente' },
  { label: 'Near miss', value: 'near_miss' },
]
const opcionesEstado = [
  { label: 'Nuevo',       value: 'nuevo'       },
  { label: 'En análisis', value: 'en_analisis' },
  { label: 'Cerrado',     value: 'cerrado'     },
]

const columnasReportes = [
  { name: 'nivel_riesgo', label: 'Riesgo', field: 'nivel_riesgo', align: 'center', sortable: true },
  { name: 'tipo',         label: 'Tipo',   field: 'tipo',         align: 'left' },
  { name: 'descripcion',  label: 'Descripción', field: row => row.descripcion?.slice(0,60)+'…', align: 'left' },
  { name: 'fecha_evento', label: 'Fecha',  field: row => row.fecha_evento?.slice(0,10), align: 'left' },
  { name: 'estado',       label: 'Estado', field: 'estado',       align: 'center' },
  { name: 'acciones',     label: '',       field: 'id',           align: 'right' },
]

const kpisSms = computed(() => {
  if (!kpis.value) return []
  return [
    { label: 'Total reportes',      valor: kpis.value.total_reportes       || 0, color: '#60a5fa' },
    { label: 'Riesgo inaceptable',  valor: kpis.value.inaceptables         || 0, color: '#ef4444' },
    { label: 'Acciones pendientes', valor: kpis.value.acciones_pendientes  || 0, color: '#f59e0b' },
    { label: 'Acciones vencidas',   valor: kpis.value.acciones_vencidas    || 0, color: '#ef4444' },
  ]
})

const reportesRecientes = computed(() => reportes.value.slice(0, 8))

const bgRiesgo = (nivel) => {
  if (nivel <= 4)  return 'rgba(34,197,94,.25)'
  if (nivel <= 9)  return 'rgba(245,158,11,.3)'
  return 'rgba(239,68,68,.35)'
}
const colorTipo   = (t) => ({ peligro:'warning', incidente:'orange', accidente:'negative', near_miss:'deep-purple' }[t] || 'grey')
const colorEstado = (e) => ({ nuevo:'info', en_analisis:'warning', cerrado:'positive' }[e] || 'grey')
const colorAccion = (e) => ({ pendiente:'warning', en_proceso:'info', cerrada:'positive', verificada:'teal' }[e] || 'grey')
const esVencida   = (fecha) => dayjs(fecha).isBefore(dayjs())

function verReporte(r) {
  // TODO: abrir dialog de detalle
  console.log('Ver reporte:', r.id)
}

async function cargarKpis() {
  cargandoKpis.value = true
  try {
    const { data } = await api.get('/sms/dashboard')
    kpis.value = data.data
  } finally { cargandoKpis.value = false }
}

async function cargarReportes() {
  cargandoReportes.value = true
  try {
    const params = {}
    if (filtroTipo.value)   params.tipo   = filtroTipo.value
    if (filtroEstado.value) params.estado = filtroEstado.value
    const { data } = await api.get('/sms/reportes', { params })
    reportes.value = data.data.data || []
  } finally { cargandoReportes.value = false }
}

async function cargarAcciones() {
  const { data } = await api.get('/sms/acciones?estado=pendiente')
  acciones.value = data.data.data || []
}

watch([filtroTipo, filtroEstado], cargarReportes)
watch(tabActivo, (t) => { if (t === 'acciones') cargarAcciones() })
onMounted(() => { cargarKpis(); cargarReportes() })
</script>
