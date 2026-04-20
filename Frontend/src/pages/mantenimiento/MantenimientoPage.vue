<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="settings_suggest" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">MANTENIMIENTO & FLOTA · RAC 43 / 91</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Centro de Ingeniería</h1>
        </div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn-dropdown
          v-if="puedeRegistrar"
          unelevated no-caps icon="add" label="Registrar Operación"
          class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" color="red-9"
        >
          <q-list dark class="premium-glass-card" style="min-width:220px">
            <q-item clickable v-close-popup @click="abrirDialogMantenimiento">
              <q-item-section avatar><q-icon name="build" color="red-9" size="18px"/></q-item-section>
              <q-item-section><q-item-label class="text-weight-bold">Registro RAC 43</q-item-label></q-item-section>
            </q-item>
            <q-item clickable v-close-popup @click="abrirDialogMel">
              <q-item-section avatar><q-icon name="warning" color="orange-9" size="18px"/></q-item-section>
              <q-item-section><q-item-label class="text-weight-bold">Ítem MEL</q-item-label></q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </div>
    </div>

    <!-- ══ KPIs Premium ══ -->
    <div class="row q-col-gutter-md q-mb-xl">
      <div class="col-6 col-md-3" v-for="kpi in kpis" :key="kpi.label">
        <div class="premium-glass-card q-pa-xl flex items-center no-wrap shadow-24 rounded-20 border-red-low">
          <div class="kpi-icon q-mr-md" :style="`background:${kpi.bg}`">
            <q-icon :name="kpi.icon" :color="kpi.color" size="24px" />
          </div>
          <div>
            <div class="text-h5 text-weight-bolder font-mono text-white line-height-1">
              <q-skeleton v-if="cargando" type="text" width="40px" dark />
              <span v-else>{{ kpi.valor }}</span>
            </div>
            <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">{{ kpi.label }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Pestañas Premium (Glass Card) ══ -->
    <q-card class="premium-glass-card shadow-24 overflow-hidden q-mb-xl rounded-20">
      <q-tabs v-model="tab" dark dense align="left" no-caps
        active-color="red-9" indicator-color="red-9" class="text-weight-bolder border-bottom-border"
        style="padding-left: 10px"
      >
        <q-tab name="aeronaves"   icon="flight"         label="Flota Activa" />
        <q-tab name="registros"   icon="history"        label="Historial RAC 43" />
        <q-tab name="mel"         icon="warning"        label="Control MEL" />
        <q-tab name="ads"         icon="policy"         label="Directivas (ADs)" />
      </q-tabs>
    </q-card>

    <q-tab-panels v-model="tab" dark animated class="bg-transparent">

      <!-- ════ FLOTA ════ -->
      <q-tab-panel name="aeronaves" class="q-pa-none">
        <div class="row q-col-gutter-lg">
          <div class="col-12 col-sm-6 col-lg-4 animate-slide-up"
            v-for="av in aeronaves" :key="av.id"
          >
            <q-card class="premium-glass-card" :class="estadoClass(av.estado)">
              <q-card-section class="q-pa-lg">
                <div class="row items-center justify-between q-mb-md">
                    <div>
                      <div class="font-mono text-white text-weight-bold" style="font-size:22px">{{ av.matricula }}</div>
                      <div class="text-red-7 font-head text-weight-bold text-uppercase" style="font-size:11px">{{ av.modelo }}</div>
                    </div>
                    <q-chip dense :color="estadoColor(av.estado)" text-color="white"
                      :label="av.estado?.toUpperCase()" class="font-mono text-weight-bold" style="font-size:9px" />
                </div>

                <div class="row q-col-gutter-md q-mb-lg">
                    <div class="col-4 text-center">
                        <div class="text-caption text-grey-6 uppercase font-mono" style="font-size:8px">Célula</div>
                        <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(av.horas_celula_total || 0).toFixed(0) }}h</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-caption text-grey-6 uppercase font-mono" style="font-size:8px">Motor</div>
                        <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(av.horas_motor_total || 0).toFixed(0) }}h</div>
                    </div>
                    <div class="col-4 text-center">
                        <div class="text-caption text-grey-6 uppercase font-mono" style="font-size:8px">Desde O/H</div>
                        <div class="text-subtitle1 font-mono text-weight-bold" :style="av.horas_desde_oh > 800 ? 'color:#f97316' : 'color:#4ade80'">
                          {{ Number(av.horas_desde_oh || 0).toFixed(0) }}h
                        </div>
                    </div>
                </div>

                <div class="row items-center q-gutter-xs q-mb-md">
                    <q-badge v-if="av.mel_abiertos_count > 0" color="orange-9" class="q-px-sm">
                        <q-icon name="warning" size="12px" class="q-mr-xs" /> {{ av.mel_abiertos_count }} MEL
                    </q-badge>
                    <q-badge v-if="av.ads_pendientes_count > 0" color="red-10" class="q-px-sm">
                        <q-icon name="error" size="12px" class="q-mr-xs" /> {{ av.ads_pendientes_count }} ADs
                    </q-badge>
                    <div v-if="!av.mel_abiertos_count && !av.ads_pendientes_count" class="flex items-center text-emerald">
                        <q-icon name="verified" size="16px" class="q-mr-xs" />
                        <span class="font-mono text-weight-bold" style="font-size:10px">TODO EN ORDEN</span>
                    </div>
                </div>

                <div class="q-pt-md border-top-border">
                    <div class="row justify-between q-mb-sm">
                      <span class="text-grey-6 font-mono" style="font-size:9px">AERONAVEGABILIDAD</span>
                      <span class="text-white font-mono text-weight-bold" style="font-size:9px">{{ av.venc_airworthiness }}</span>
                    </div>
                    <q-linear-progress
                      :value="airworthinessProgress(av)"
                      color="red-9"
                      track-color="white-1"
                      rounded size="6px"
                    />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>

      <!-- ════ REGISTROS ════ -->
      <q-tab-panel name="registros" class="q-pa-none">
         <div class="row items-center q-col-gutter-sm q-mb-md">
          <div class="col-auto">
            <q-select v-model="filtroAeronave" :options="opcionesAeronave"
              option-value="id" option-label="matricula" emit-value map-options
              label="Filtrar Aeronave" dense dark outlined clearable
              style="min-width:200px" color="red-9">
            </q-select>
          </div>
        </div>
        <q-table :rows="registrosFiltrados" :columns="colsRegistros" row-key="id" class="rac-table" flat />
      </q-tab-panel>

      <q-tab-panel name="mel" class="q-pa-none">
        <q-table :rows="melItems" :columns="colsMel" row-key="id" class="rac-table" flat />
      </q-tab-panel>

      <q-tab-panel name="ads" class="q-pa-none">
        <q-table :rows="adItems" :columns="colsAds" row-key="id" class="rac-table" flat />
      </q-tab-panel>

    </q-tab-panels>

    <q-dialog v-model="dialogMant" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(800px, 95vw);">
        <div class="q-pa-xl border-bottom-border pb-md">
          <div class="row items-center justify-between q-col-gutter-md">
            <div class="row items-center col">
              <q-icon name="build" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter line-height-1">Registro RAC 43</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogMant = false" color="grey-6" class="bg-black-20 hover-red" />
          </div>
        </div>
        <div class="q-pa-xl">
        
        <q-form @submit.prevent="guardarMantenimiento" class="row q-col-gutter-lg">
          <div class="col-12">
            <q-select v-model="formMant.aeronave_id" :options="opcionesAeronave" option-value="id" option-label="matricula" 
              emit-value map-options label="SELECCIONAR AERONAVE" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-select v-model="formMant.tipo" :options="tiposMant" label="TIPO DE OPERACIÓN" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-input v-model="formMant.fecha_realizado" type="date" label="FECHA DE REALIZACIÓN" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-input v-model="formMant.horas_aeronave" type="number" step="0.1" label="HORAS TTAE (TOTAL TIME AIRFRAME)" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-input v-model="formMant.costo" type="number" label="COSTO OPERACIÓN (COP)" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12">
            <q-input v-model="formMant.realizado_por" label="PERSONAL TÉCNICO / TALLER AUTORIZADO" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12">
            <q-input v-model="formMant.descripcion" label="DESCRIPCIÓN TÉCNICA DEL TRABAJO" type="textarea" rows="3" filled dark class="premium-input-login" stack-label />
          </div>
          
          <div class="col-12 row justify-end q-mt-xl">
             <q-btn type="submit" label="Certificar y Registrar RAC 43" color="red-10" class="premium-btn q-px-xl q-py-lg shadow-24 full-width" :loading="guardando" />
          </div>
        </q-form>
      </div>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogMel" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(600px, 95vw);">
        <div class="q-pa-xl border-bottom-border pb-md">
          <div class="row items-center justify-between q-col-gutter-md">
            <div class="row items-center col">
              <q-icon name="warning" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter line-height-1">Reporte Ítem MEL</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogMel = false" color="grey-6" class="bg-black-20 hover-red" />
          </div>
        </div>
        <div class="q-pa-xl">

        <q-form @submit.prevent="guardarMel" class="row q-col-gutter-lg">
          <div class="col-12">
            <q-select v-model="formMel.aeronave_id" :options="opcionesAeronave" option-value="id" option-label="matricula" 
              emit-value map-options label="AERONAVE AFECTADA" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-input v-model="formMel.item_ata" label="CAPÍTULO ATA" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12 col-md-6">
            <q-select v-model="formMel.categoria" :options="['A','B','C','D']" label="CATEGORÍA DE REPARACIÓN" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12">
            <q-input v-model="formMel.descripcion" label="DESCRIPCIÓN DE LA DISCREPANCIA" filled dark class="premium-input-login" stack-label />
          </div>
          <div class="col-12">
            <q-input v-model="formMel.fecha_apertura" type="date" label="FECHA DE REPORTE" filled dark class="premium-input-login" stack-label />
          </div>
          
          <div class="col-12 row justify-end q-mt-xl">
            <q-btn type="submit" label="Abrir Diferido MEL" color="orange-9" class="premium-btn q-px-xl q-py-lg shadow-24 full-width" :loading="guardando" />
          </div>
        </q-form>
      </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q        = useQuasar()
const authStore = useAuthStore()

const tab       = ref('aeronaves')
const cargando  = ref(true)

const aeronaves        = ref([])
const registros        = ref([])
const melItems         = ref([])
const adItems          = ref([])

const cargandoAeronaves = ref(true)
const cargandoRegistros = ref(true)
const cargandoMel       = ref(true)
const cargandoAds       = ref(true)

const filtroAeronave = ref(null)
const filtroTipoMant = ref(null)

const dialogMant = ref(false)
const dialogMel  = ref(false)
const guardando  = ref(false)

const formMant = ref({ aeronave_id: null, tipo: null, descripcion: '', fecha_realizado: '', horas_aeronave: '', realizado_por: '', licencia_tecnico: '', proxima_fecha: '', proximas_horas: '', costo: '' })
const formMel  = ref({ aeronave_id: null, item_ata: '', descripcion: '', categoria: null, fecha_apertura: '', procedimiento_o: '' })

const puedeRegistrar = computed(() =>
  ['admin', 'dir_ops', 'mantenimiento'].includes(authStore.rol)
)

const melAbiertos     = computed(() => melItems.value.filter(m => m.estado === 'abierto').length)
const adsPendientes   = computed(() => adItems.value.filter(a => !a.cumplido).length)
const totalRegistros  = computed(() => registros.value.length)
const totalAeronaves  = computed(() => aeronaves.value.filter(a => a.estado !== 'baja').length)

const kpis = computed(() => [
  { label: 'Aeronaves activas',  valor: totalAeronaves.value,  icon: 'flight', color: 'red-9',   bg: 'rgba(161,11,19,0.1)' },
  { label: 'Registros RAC 43',   valor: totalRegistros.value,  icon: 'build',  color: 'red-9',   bg: 'rgba(161,11,19,0.1)' },
  { label: 'MEL abiertos',       valor: melAbiertos.value,     icon: 'warning',color: 'orange-9',bg: 'rgba(249,115,22,0.1)' },
  { label: 'ADs pendientes',     valor: adsPendientes.value,   icon: 'policy', color: 'red-9',   bg: 'rgba(161,11,19,0.1)' },
])

const opcionesAeronave = computed(() => aeronaves.value.map(a => ({ id: a.id, matricula: a.matricula, label: a.matricula })))
const tiposMant = ['inspeccion_50h','inspeccion_100h','anual','AD','SB','correctivo','preventivo']

const registrosFiltrados = computed(() => {
  let r = registros.value
  if (filtroAeronave.value) r = r.filter(x => x.aeronave_id === filtroAeronave.value)
  return r
})

const colsRegistros = [
  { name: 'tipo', label: 'TIPO', field: 'tipo', align: 'left' },
  { name: 'fecha_realizado', label: 'FECHA', field: 'fecha_realizado', align: 'left' },
  { name: 'realizado_por', label: 'TÉCNICO', field: 'realizado_por', align: 'left' },
  { name: 'horas_aeronave', label: 'HORAS', field: 'horas_aeronave', align: 'center' },
  { name: 'descripcion', label: 'DESCRIPCIÓN', field: 'descripcion', align: 'left' },
]

const colsMel = [
  { name: 'item_ata', label: 'ATA', field: 'item_ata', align: 'left' },
  { name: 'categoria', label: 'CAT', field: 'categoria', align: 'center' },
  { name: 'descripcion', label: 'FALLA', field: 'descripcion', align: 'left' },
  { name: 'fecha_apertura', label: 'FECHA', field: 'fecha_apertura', align: 'center' },
  { name: 'estado', label: 'ESTADO', field: 'estado', align: 'center' },
]

const colsAds = [
  { name: 'numero_ad', label: 'No. AD', field: 'numero_ad', align: 'left' },
  { name: 'titulo', label: 'TÍTULO', field: 'titulo', align: 'left' },
  { name: 'cumplido', label: 'ESTADO', field: 'cumplido', align: 'center' },
]

const estadoClass = (e) => ({'border-red-glow': e === 'mantenimiento'})
const estadoColor = (e) => (e === 'disponible' ? 'positive' : 'orange-9')

const airworthinessProgress = (av) => {
  if (!av.venc_airworthiness) return 0
  const diff = new Date(av.venc_airworthiness) - new Date()
  return Math.min(Math.max((diff / 86400000) / 365, 0), 1)
}

function abrirDialogMantenimiento() { dialogMant.value = true }
function abrirDialogMel()           { dialogMel.value  = true }

async function guardarMantenimiento() {
  guardando.value = true
  try {
    await api.post(`/aeronaves/${formMant.value.aeronave_id}/mantenimientos`, formMant.value)
    $q.notify({ type: 'positive', message: 'Guardado correctamente.' })
    dialogMant.value = false
    cargarTodo()
  } finally { guardando.value = false }
}

async function guardarMel() {
  guardando.value = true
  try {
    await api.post(`/aeronaves/${formMel.value.aeronave_id}/mel`, formMel.value)
    $q.notify({ type: 'positive', message: 'MEL abierto.' })
    dialogMel.value = false
    cargarTodo()
  } finally { guardando.value = false }
}

async function cargarTodo() {
  cargando.value = true
  try {
    const { data } = await api.get('/aeronaves')
    aeronaves.value = data.data || []
    // Carga simplificada de otros datos...
  } finally { cargando.value = false; cargandoAeronaves.value = false }
}

onMounted(cargarTodo)
</script>

<style lang="scss" scoped>
.hover-red { transition: color 0.2s; &:hover { color: #A10B13 !important; } }
.bg-black-20 { background: rgba(0,0,0,0.2); }
.pb-md { padding-bottom: 16px; }
.border-bottom-border { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.rounded-20 { border-radius: 20px !important; }

.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
