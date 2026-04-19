<template>
  <q-page padding style="padding-bottom:80px">

    <!-- Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">
          RAC 61 · RAC 91.417
        </div>
        <div class="font-head text-white" style="font-size:20px; font-weight:700">Bitácoras de Vuelo</div>
      </div>
      <q-btn v-if="puedeCrear" unelevated color="primary" icon="add" label="Nueva bitácora"
        @click="$router.push('/bitacoras/nueva')" style="border-radius:8px" no-caps />
    </div>

    <!-- Filtros -->
    <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
      <q-card-section>
        <div class="row q-col-gutter-sm items-end">
          <div class="col-12 col-sm-4">
            <q-input v-model="filtros.buscar" outlined dense dark bg-color="grey-10"
              placeholder="Buscar matrícula, estudiante…" clearable>
              <template #prepend><q-icon name="search" color="grey-6" size="18px" /></template>
            </q-input>
          </div>
          <div class="col-6 col-sm-3">
            <q-input v-model="filtros.fecha_desde" outlined dense dark bg-color="grey-10"
              placeholder="Desde" type="date" label="Fecha desde" stack-label>
            </q-input>
          </div>
          <div class="col-6 col-sm-3">
            <q-input v-model="filtros.fecha_hasta" outlined dense dark bg-color="grey-10"
              placeholder="Hasta" type="date" label="Fecha hasta" stack-label>
            </q-input>
          </div>
          <div class="col-12 col-sm-2">
            <q-select v-model="filtros.tipo_vuelo" outlined dense dark bg-color="grey-10"
              :options="tiposVuelo" emit-value map-options clearable label="Tipo" stack-label>
            </q-select>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Resumen de horas (visible para estudiante) -->
    <div v-if="authStore.esEstudiante" class="row q-col-gutter-sm q-mb-md">
      <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
        <div class="stat-card text-center" style="padding:10px">
          <div class="font-head text-primary" style="font-size:18px; font-weight:700">{{ h.valor.toFixed(1) }}</div>
          <div class="font-mono" style="font-size:9px; color:#64748b; letter-spacing:.5px">{{ h.label }}</div>
        </div>
      </div>
    </div>

    <!-- Tabla de bitácoras -->
    <q-table
      :rows="bitacoras"
      :columns="columnas"
      :loading="cargando"
      flat dark
      class="tabla-rac"
      style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px"
      :rows-per-page-options="[15, 25, 50]"
      row-key="id"
      v-model:pagination="paginacion"
      @request="onRequest"
    >
      <!-- Slot para celdas personalizadas -->
      <template #body-cell-tipo_vuelo="{ value }">
        <q-td>
          <q-chip dense :color="colorTipoVuelo(value)" text-color="white"
            :label="value.toUpperCase()" style="font-family:monospace; font-size:10px; padding:2px 8px" />
        </q-td>
      </template>

      <template #body-cell-horas_totales="{ row }">
        <q-td>
          <span class="font-mono text-primary">{{ row.horas_totales.toFixed(1) }}h</span>
        </q-td>
      </template>

      <template #body-cell-firmas="{ row }">
        <q-td>
          <div class="row q-gutter-xs">
            <q-icon :name="row.firma_instructor ? 'verified' : 'pending'" :color="row.firma_instructor ? 'positive' : 'grey-6'" size="16px">
              <q-tooltip>Instructor: {{ row.firma_instructor ? 'Firmado' : 'Pendiente' }}</q-tooltip>
            </q-icon>
            <q-icon :name="row.firma_estudiante ? 'verified' : 'pending'" :color="row.firma_estudiante ? 'positive' : 'grey-6'" size="16px">
              <q-tooltip>Estudiante: {{ row.firma_estudiante ? 'Firmado' : 'Pendiente' }}</q-tooltip>
            </q-icon>
          </div>
        </q-td>
      </template>

      <template #body-cell-acciones="{ row }">
        <q-td>
          <q-btn flat round dense icon="visibility" color="grey-5" size="sm"
            @click="verDetalle(row)" />
          <q-btn v-if="puedeFirmar(row)" flat round dense icon="draw" color="primary" size="sm"
            @click="firmarBitacora(row)" />
        </q-td>
      </template>

      <template #no-data>
        <div class="full-width text-center q-py-xl" style="color:#475569">
          <q-icon name="flight" size="48px" class="q-mb-md" />
          <div style="font-size:14px">Sin bitácoras registradas</div>
        </div>
      </template>

    </q-table>

    <!-- Dialog detalle bitácora -->
    <q-dialog v-model="dialogDetalle" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card dark style="background:#0a0c10">
        <q-toolbar style="background:#0f1218; border-bottom:1px solid rgba(255,255,255,.08)">
          <q-toolbar-title class="font-head">Detalle de Bitácora</q-toolbar-title>
          <q-btn flat round dense icon="close" @click="dialogDetalle = false" color="grey-5" />
        </q-toolbar>

        <q-card-section v-if="bitacoraSeleccionada" class="q-pa-lg">
          <div class="row q-col-gutter-lg">
            <!-- Datos del vuelo -->
            <div class="col-12 col-md-6">
              <div class="font-mono q-mb-sm" style="font-size:10px; color:#475569; letter-spacing:2px">DATOS DEL VUELO</div>
              <div class="row q-col-gutter-sm">
                <div class="col-6" v-for="campo in camposBitacora" :key="campo.label">
                  <div style="font-size:11px; color:#64748b; margin-bottom:3px">{{ campo.label }}</div>
                  <div class="font-mono text-white" style="font-size:13px">{{ campo.valor }}</div>
                </div>
              </div>
            </div>
            <!-- Horas por tipo -->
            <div class="col-12 col-md-6">
              <div class="font-mono q-mb-sm" style="font-size:10px; color:#475569; letter-spacing:2px">DISTRIBUCIÓN DE HORAS</div>
              <div v-for="h in horasBitacora" :key="h.label" class="q-mb-xs">
                <div class="row items-center justify-between q-mb-xs">
                  <span style="font-size:12px; color:#94a3b8">{{ h.label }}</span>
                  <span class="font-mono text-primary" style="font-size:12px">{{ h.valor.toFixed(1) }}h</span>
                </div>
                <q-linear-progress :value="h.valor / (bitacoraSeleccionada.horas_totales || 1)"
                  color="primary" size="4px" rounded />
              </div>
            </div>
          </div>

          <!-- Observaciones -->
          <div v-if="bitacoraSeleccionada.observaciones" class="q-mt-md">
            <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px">OBSERVACIONES</div>
            <div style="font-size:13px; color:#cbd5e1; line-height:1.6; background:rgba(255,255,255,.03); border-radius:8px; padding:12px">
              {{ bitacoraSeleccionada.observaciones }}
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q        = useQuasar()
const authStore = useAuthStore()

const bitacoras           = ref([])
const cargando            = ref(false)
const dialogDetalle       = ref(false)
const bitacoraSeleccionada = ref(null)

const filtros = ref({
  buscar: '', fecha_desde: '', fecha_hasta: '', tipo_vuelo: null,
})

const paginacion = ref({ page: 1, rowsPerPage: 15, rowsNumber: 0 })

const tiposVuelo = [
  { label: 'Local',      value: 'local' },
  { label: 'Navegación', value: 'navegacion' },
  { label: 'Noche',      value: 'noche' },
  { label: 'IFR',        value: 'ifr' },
  { label: 'Simulador',  value: 'sim' },
]

const puedeCrear = computed(() => ['instructor', 'dir_ops'].includes(authStore.rol))

const columnas = computed(() => {
  const cols = [
    { name: 'fecha',          label: 'Fecha',     field: 'fecha',         sortable: true, align: 'left' },
    { name: 'origen_icao',    label: 'Ruta',      field: row => `${row.origen_icao}→${row.destino_icao}`, align: 'left' },
    { name: 'aeronave',       label: 'Aeronave',  field: row => row.aeronave?.matricula || '-', align: 'left' },
    { name: 'horas_totales',  label: 'Horas',     field: 'horas_totales', sortable: true, align: 'center' },
    { name: 'tipo_vuelo',     label: 'Tipo',      field: 'tipo_vuelo',    align: 'center' },
    { name: 'firmas',         label: 'Firmas',    field: 'firma_instructor', align: 'center' },
    { name: 'acciones',       label: '',          field: 'id', align: 'right' },
  ]

  if (!authStore.esEstudiante) {
    cols.splice(2, 0, {
      name: 'estudiante', label: 'Estudiante',
      field: row => row.estudiante?.persona ? `${row.estudiante.persona.nombres} ${row.estudiante.persona.apellidos}` : '-',
      align: 'left',
    })
  }

  return cols
})

const resumenHoras = computed(() => {
  const total = bitacoras.value.reduce((acc, b) => {
    acc.total     += b.horas_totales  || 0
    acc.dual      += b.horas_dual     || 0
    acc.solo      += b.horas_solo     || 0
    acc.noche     += b.horas_noche    || 0
    acc.ifr       += b.horas_ifr      || 0
    acc.sim       += b.horas_simulador|| 0
    return acc
  }, { total: 0, dual: 0, solo: 0, noche: 0, ifr: 0, sim: 0 })

  return [
    { label: 'TOTAL', valor: total.total },
    { label: 'DUAL',  valor: total.dual  },
    { label: 'SOLO',  valor: total.solo  },
    { label: 'NOCHE', valor: total.noche },
    { label: 'IFR',   valor: total.ifr   },
    { label: 'SIM',   valor: total.sim   },
  ]
})

const camposBitacora = computed(() => {
  if (!bitacoraSeleccionada.value) return []
  const b = bitacoraSeleccionada.value
  return [
    { label: 'Fecha',    valor: b.fecha },
    { label: 'Ruta',     valor: `${b.origen_icao} → ${b.destino_icao}` },
    { label: 'Aeronave', valor: b.aeronave?.matricula || '-' },
    { label: 'Salida',   valor: b.hora_salida },
    { label: 'Llegada',  valor: b.hora_llegada },
    { label: 'Condición',valor: b.condiciones_vmc ? 'VMC' : 'IMC' },
    { label: 'Aterrizajes', valor: b.aterrizajes },
  ]
})

const horasBitacora = computed(() => {
  if (!bitacoraSeleccionada.value) return []
  const b = bitacoraSeleccionada.value
  return [
    { label: 'Dual',      valor: b.horas_dual      || 0 },
    { label: 'Solo',      valor: b.horas_solo      || 0 },
    { label: 'Noche',     valor: b.horas_noche     || 0 },
    { label: 'IFR',       valor: b.horas_ifr       || 0 },
    { label: 'Simulador', valor: b.horas_simulador || 0 },
  ].filter(h => h.valor > 0)
})

const colorTipoVuelo = (tipo) => ({
  local: 'primary', navegacion: 'teal', noche: 'purple',
  ifr: 'amber-9', sim: 'grey-7',
}[tipo] || 'grey')

async function cargar(pag = 1) {
  cargando.value = true
  try {
    const params = { page: pag, per_page: paginacion.value.rowsPerPage }
    if (filtros.value.fecha_desde)  params.fecha_desde = filtros.value.fecha_desde
    if (filtros.value.fecha_hasta)  params.fecha_hasta = filtros.value.fecha_hasta
    if (filtros.value.tipo_vuelo)   params.tipo_vuelo  = filtros.value.tipo_vuelo

    const { data } = await api.get('/bitacoras', { params })
    bitacoras.value          = data.data.data || []
    paginacion.value.rowsNumber = data.data.total || 0
  } finally {
    cargando.value = false
  }
}

function onRequest(props) {
  paginacion.value.page = props.pagination.page
  paginacion.value.rowsPerPage = props.pagination.rowsPerPage
  cargar(props.pagination.page)
}

function verDetalle(row) {
  bitacoraSeleccionada.value = row
  dialogDetalle.value = true
}

function puedeFirmar(row) {
  if (authStore.esInstructor && !row.firma_instructor) return true
  if (authStore.esEstudiante && !row.firma_estudiante) return true
  return false
}

async function firmarBitacora(row) {
  try {
    await api.post(`/bitacoras/${row.id}/firmar`)
    $q.notify({ type: 'positive', message: 'Bitácora firmada correctamente.' })
    cargar(paginacion.value.page)
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al firmar la bitácora.' })
  }
}

watch(filtros, () => cargar(1), { deep: true })
onMounted(() => cargar())
</script>
