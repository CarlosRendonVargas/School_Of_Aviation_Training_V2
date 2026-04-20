<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Trazabilidad RAC ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="history_edu" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">REGISTRO OFICIAL DE VUELO · RAC 61 / 91.417</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Bitácoras de Instrucción</h1>
        </div>
      </div>
      <q-btn 
        v-if="puedeCrear" 
        color="red-9" icon="add" label="Nueva Bitácora" 
        class="premium-btn shadow-24" 
        @click="$router.push('/bitacoras/nueva')" 
      />
    </div>

    <!-- ══ Panel de Filtros Táctico ══ -->
    <q-card class="premium-glass-card q-pa-lg q-mb-xl border-red-low shadow-inner">
      <div class="row q-col-gutter-lg items-end">
        <div class="col-12 col-md-4">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">BUSCAR POR AERONAVE / PILOTO</div>
          <q-input v-model="filtros.buscar" filled dark dense class="premium-input-login" placeholder="HK-XXXX / Nombre...">
            <template #prepend><q-icon name="search" color="red-9" /></template>
          </q-input>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">PERIODO DE VUELO</div>
          <div class="row q-col-gutter-xs">
             <div class="col-6"><q-input v-model="filtros.fecha_desde" type="date" filled dark dense class="premium-input-login" /></div>
             <div class="col-6"><q-input v-model="filtros.fecha_hasta" type="date" filled dark dense class="premium-input-login" /></div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">MODALIDAD</div>
          <q-select v-model="filtros.tipo_vuelo" :options="tiposVuelo" emit-value map-options clearable filled dark dense class="premium-input-login" />
        </div>
        <div class="col-12 col-md-2 flex justify-end">
           <q-btn flat round icon="refresh" color="grey-7" @click="cargar(1)" />
        </div>
      </div>
    </q-card>

    <!-- ══ Resumen de Horas (Dossier del Estudiante) ══ -->
    <div v-if="authStore.esEstudiante" class="row q-col-gutter-lg q-mb-xl">
      <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
        <q-card class="premium-glass-card q-pa-lg text-center border-red-low shadow-inner">
          <div class="text-h5 text-weight-bolder text-red-9 font-mono line-height-1">{{ h.valor.toFixed(1) }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase q-mt-xs" style="font-size:10px">{{ h.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- ══ Monitor de Bitácoras de Cristal ══ -->
    <q-table
      :rows="bitacoras"
      :columns="columnas"
      :loading="cargando"
      flat dark
      class="rac-table shadow-24"
      row-key="id"
      v-model:pagination="paginacion"
      @request="onRequest"
    >
      <template #body-cell-tipo_vuelo="props">
        <q-td :props="props" class="text-center">
          <q-badge :color="colorTipoVuelo(props.value)" :label="props.value.toUpperCase()" class="font-mono text-weight-bold q-px-md shadow-24" />
        </q-td>
      </template>

      <template #body-cell-horas_totales="props">
        <q-td :props="props">
          <span class="font-mono text-weight-bold text-red-9">{{ props.row.horas_totales.toFixed(1) }}H</span>
        </q-td>
      </template>

      <template #body-cell-firmas="props">
        <q-td :props="props" class="text-center">
          <div class="row q-gutter-x-sm justify-center">
            <q-icon :name="props.row.firma_instructor ? 'verified_user' : 'pending_actions'" :color="props.row.firma_instructor ? 'emerald' : 'orange-9'" size="20px">
              <q-tooltip class="bg-dark text-white">INSTR: {{ props.row.firma_instructor ? 'CERTIFICADO' : 'PENDIENTE' }}</q-tooltip>
            </q-icon>
            <q-icon :name="props.row.firma_estudiante ? 'verified' : 'pending_actions'" :color="props.row.firma_estudiante ? 'emerald' : 'grey-7'" size="20px">
              <q-tooltip class="bg-dark text-white">ALU: {{ props.row.firma_estudiante ? 'CERTIFICADO' : 'PENDIENTE' }}</q-tooltip>
            </q-icon>
          </div>
        </q-td>
      </template>

      <template #body-cell-acciones="props">
        <q-td :props="props" class="text-right">
          <div class="row q-gutter-x-sm justify-end">
             <q-btn flat round dense icon="pageview" color="grey-7" size="sm" @click="verDetalle(props.row)" />
             <q-btn v-if="puedeFirmar(props.row)" flat round dense icon="draw" color="red-9" size="sm" @click="firmarBitacora(props.row)">
                <q-tooltip class="bg-red-9">FIRMAR REGISTRO</q-tooltip>
             </q-btn>
          </div>
        </q-td>
      </template>
    </q-table>

    <!-- ════ DIÁLOGO: DETALLE DE BITÁCORA (EXPEDIENTE) ════ -->
    <q-dialog v-model="dialogDetalle" maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-dark-page text-white overflow-hidden">
        <q-toolbar class="q-py-md bg-transparent" style="backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255,255,255,0.05)">
           <q-icon name="article" color="red-9" size="32px" class="q-mr-md" />
           <q-toolbar-title class="font-head text-weight-bolder">Expediente de Vuelo #{{ bitacoraSeleccionada?.id }}</q-toolbar-title>
           <q-btn flat round dense icon="close" v-close-popup color="grey-6" />
        </q-toolbar>

        <q-card-section v-if="bitacoraSeleccionada" class="q-pa-xl">
           <div class="row q-col-gutter-xl">
              <!-- Datos Primarios -->
              <div class="col-12 col-md-7">
                <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-lg">Parámetros Operativos de la Misión</div>
                <div class="row q-col-gutter-md">
                   <div class="col-6" v-for="campo in camposBitacora" :key="campo.label">
                      <div class="premium-glass-card q-pa-md border-red-low">
                         <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">{{ campo.label }}</div>
                         <div class="text-white text-weight-bold font-mono">{{ campo.valor }}</div>
                      </div>
                   </div>
                </div>

                <div v-if="bitacoraSeleccionada.observaciones" class="q-mt-xl">
                   <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-lg">Maniobras y Observaciones</div>
                   <div class="premium-glass-card q-pa-xl border-red-left font-mono line-height-1" style="font-size:13px; color:#cbd5e1">
                      {{ bitacoraSeleccionada.observaciones }}
                   </div>
                </div>
              </div>

              <!-- Distribución Táctica -->
              <div class="col-12 col-md-5">
                <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-lg">Contabilidad RAC 61</div>
                <div class="premium-glass-card q-pa-xl border-red-low shadow-inner">
                   <div v-for="h in horasBitacora" :key="h.label" class="q-mb-lg last-no-margin">
                      <div class="row items-center justify-between q-mb-sm">
                         <span class="text-weight-bold text-grey-4">{{ h.label }}</span>
                         <span class="font-mono text-red-9 text-weight-bolder">{{ h.valor.toFixed(1) }}H</span>
                      </div>
                      <q-linear-progress :value="h.valor / (bitacoraSeleccionada.horas_totales || 1)" color="red-9" size="6px" rounded track-color="white-1" />
                   </div>
                </div>
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

const filtros = ref({ buscar: '', fecha_desde: '', fecha_hasta: '', tipo_vuelo: null })
const paginacion = ref({ page: 1, rowsPerPage: 15, rowsNumber: 0 })

const tiposVuelo = [
  { label: 'Vuelo Local', value: 'local' },
  { label: 'Navegación', value: 'navegacion' },
  { label: 'Vuelo Nocturno', value: 'noche' },
  { label: 'Vuelo IFR', value: 'ifr' },
  { label: 'Simulador (FTD)', value: 'sim' },
]

const puedeCrear = computed(() => ['instructor', 'dir_ops', 'admin'].includes(authStore.rol))

const columnas = computed(() => {
  const cols = [
    { name: 'fecha', label: 'FECHA MISIÓN', field: 'fecha', sortable: true, align: 'left' },
    { name: 'origen_icao', label: 'RUTA TACTICA', field: row => `${row.origen_icao} > ${row.destino_icao}`, align: 'left' },
    { name: 'aeronave', label: 'HK ASIGNADA', field: row => row.aeronave?.matricula || '-', align: 'left' },
    { name: 'horas_totales', label: 'BLOCK TIME', field: 'horas_totales', sortable: true, align: 'center' },
    { name: 'tipo_vuelo', label: 'MODALIDAD', field: 'tipo_vuelo', align: 'center' },
    { name: 'firmas', label: 'CERT. RAC', field: 'firma_instructor', align: 'center' },
    { name: 'acciones', label: 'DERECHOS', field: 'id', align: 'right' },
  ]
  if (!authStore.esEstudiante) {
    cols.splice(2, 0, { name: 'estudiante', label: 'ESTUDIANTE', field: row => row.estudiante?.persona ? `${row.estudiante.persona.nombres} ${row.estudiante.persona.apellidos}` : '-', align: 'left' })
  }
  return cols
})

const resumenHoras = computed(() => {
  const total = bitacoras.value.reduce((acc, b) => {
    acc.total += b.horas_totales || 0; acc.dual += b.horas_dual || 0; acc.solo += b.horas_solo || 0;
    acc.noche += b.horas_noche || 0; acc.ifr += b.horas_ifr || 0; acc.sim += b.horas_simulador || 0;
    return acc
  }, { total: 0, dual: 0, solo: 0, noche: 0, ifr: 0, sim: 0 })
  return [
    { label: 'TOTAL', valor: total.total }, { label: 'DUAL', valor: total.dual }, { label: 'SOLO', valor: total.solo },
    { label: 'NOCHE', valor: total.noche }, { label: 'IFR', valor: total.ifr }, { label: 'SIM', valor: total.sim },
  ]
})

const camposBitacora = computed(() => {
  if (!bitacoraSeleccionada.value) return []
  const b = bitacoraSeleccionada.value
  return [
    { label: 'FECHA OPERACIÓN', valor: b.fecha }, { label: 'ESTUDIANTE', valor: b.estudiante?.persona?.nombres },
    { label: 'AERONAVE / HK', valor: b.aeronave?.matricula || '-' }, { label: 'INSTRUCTOR', valor: b.instructor?.persona?.nombres || 'SOLO' },
    { label: 'OUT-UTC', valor: b.hora_salida }, { label: 'IN-UTC', valor: b.hora_llegada },
    { label: 'CONDICIÓN', valor: b.condiciones_vmc ? 'VMC (Visual)' : 'IMC (Instr)' }, { label: 'ATERRIZAJES', valor: b.aterrizajes },
  ]
})

const horasBitacora = computed(() => {
  if (!bitacoraSeleccionada.value) return []
  const b = bitacoraSeleccionada.value
  return [ { label: 'Vuelo Dual', valor: b.horas_dual || 0 }, { label: 'Vuelo Solo / PIC', valor: b.horas_solo || 0 }, { label: 'Vuelo Nocturno', valor: b.horas_noche || 0 }, { label: 'Vuelo IFR', valor: b.horas_ifr || 0 }, { label: 'Simulador', valor: b.horas_simulador || 0 } ].filter(h => h.valor > 0)
})

const colorTipoVuelo = (tipo) => ({ local: 'red-9', navegacion: 'blue-10', noche: 'purple-9', ifr: 'orange-10', sim: 'grey-8' }[tipo] || 'grey-8')

async function cargar(pag = 1) {
  cargando.value = true
  try {
    const params = { page: pag, per_page: paginacion.value.rowsPerPage, ...filtros.value }
    const { data } = await api.get('/bitacoras', { params })
    bitacoras.value = data.data.data || []
    paginacion.value.rowsNumber = data.data.total || 0
  } finally { cargando.value = false }
}

function onRequest(props) { paginacion.value.page = props.pagination.page; paginacion.value.rowsPerPage = props.pagination.rowsPerPage; cargar(props.pagination.page) }

function verDetalle(row) { bitacoraSeleccionada.value = row; dialogDetalle.value = true }

function puedeFirmar(row) {
  if (authStore.esInstructor && !row.firma_instructor) return true
  if (authStore.esEstudiante && !row.firma_estudiante) return true
  return false
}

async function firmarBitacora(row) {
  try {
    await api.post(`/bitacoras/${row.id}/firmar`)
    $q.notify({ color: 'emerald', message: 'Bitácora firmada operacionalmente.' }); cargar(paginacion.value.page)
  } catch { $q.notify({ color: 'negative', message: 'Error al firmar registro.' }) }
}

watch(filtros, () => cargar(1), { deep: true })
onMounted(() => cargar())
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-left { border-left: 4px solid #A10B13 !important; }
.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
.bg-dark-page { background: #05070a; }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.text-emerald { color: #10b981; }
.line-height-1 { line-height: 1.1; }
.last-no-margin:last-child { margin-bottom: 0 !important; }
</style>
