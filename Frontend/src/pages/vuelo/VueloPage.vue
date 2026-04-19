<template>
  <q-page padding style="padding-bottom: 80px">

    <!-- ══ Encabezado ══ -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 61 · RAC 91.417 · Módulo 03
        </div>
        <div class="font-head text-white" style="font-size:22px;font-weight:700">Control de Vuelo</div>
      </div>
      <q-btn
        v-if="puedeRegistrar"
        unelevated no-caps icon="add" label="Nueva bitácora"
        style="background:#A10B13;color:white;border-radius:8px;padding:8px 16px;font-weight:600"
        @click="$router.push('/vuelo/nueva-bitacora')"
      />
    </div>

    <!-- ══ KPIs ══ -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3" v-for="kpi in kpis" :key="kpi.label">
        <div class="kpi-card">
          <div class="kpi-icon" :style="`background:${kpi.bg}`">
            <q-icon :name="kpi.icon" :color="kpi.color" size="22px" />
          </div>
          <div>
            <div class="kpi-num" :style="`color:${kpi.textColor || '#e2e8f0'}`">
              <q-skeleton v-if="cargando" type="text" width="50px" dark />
              <span v-else>{{ kpi.valor }}</span>
            </div>
            <div class="kpi-lbl">{{ kpi.label }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Flota operativa ══ -->
    <div class="section-title q-mb-sm">FLOTA OPERATIVA</div>
    <div class="row q-col-gutter-sm q-mb-xl">
      <template v-if="cargandoAeronaves">
        <div class="col-12 col-sm-6 col-md-4" v-for="n in 3" :key="n">
          <q-skeleton type="rect" height="100px" dark />
        </div>
      </template>
      <template v-else>
        <div class="col-12 col-sm-6 col-md-4"
          v-for="av in aeronaves" :key="av.id"
        >
          <div class="aeronave-card" :class="estadoClass(av.estado)">
            <div class="row items-start justify-between q-mb-xs">
              <div>
                <div class="font-head text-white" style="font-size:16px;font-weight:700">{{ av.matricula }}</div>
                <div style="font-size:12px;color:#94a3b8">{{ av.modelo }} · {{ av.fabricante }}</div>
              </div>
              <q-chip dense :color="estadoColor(av.estado)" text-color="white"
                :label="av.estado?.toUpperCase()" style="font-size:10px;font-family:monospace" />
            </div>
            <div class="row q-col-gutter-xs q-mt-sm">
              <div class="col-6">
                <div class="av-stat-lbl">HORAS CÉLULA</div>
                <div class="av-stat-val">{{ Number(av.horas_celula_total || 0).toFixed(1) }}h</div>
              </div>
              <div class="col-6">
                <div class="av-stat-lbl">DESDE O/H</div>
                <div class="av-stat-val">{{ Number(av.horas_desde_oh || 0).toFixed(1) }}h</div>
              </div>
            </div>
            <div class="row q-mt-sm items-center q-gutter-xs">
              <q-chip v-if="av.mel_abiertos_count > 0" dense color="warning" text-color="dark"
                :label="`${av.mel_abiertos_count} MEL`" style="font-size:10px" />
              <q-chip v-if="av.ads_pendientes_count > 0" dense color="negative" text-color="white"
                :label="`${av.ads_pendientes_count} AD`" style="font-size:10px" />
              <span v-if="!av.mel_abiertos_count && !av.ads_pendientes_count"
                style="font-size:11px;color:#4ade80">✓ Sin ítems abiertos</span>
            </div>
          </div>
        </div>
        <div v-if="!aeronaves.length" class="col-12 text-center q-py-xl text-grey-6">
          <q-icon name="flight_off" size="40px" class="q-mb-sm" /><br>Sin aeronaves registradas
        </div>
      </template>
    </div>

    <!-- ══ Bitácoras recientes ══ -->
    <div class="row items-center justify-between q-mb-sm">
      <div class="section-title">BITÁCORAS DE VUELO</div>
      <q-input
        v-model="busqueda"
        dense dark outlined placeholder="Buscar..."
        style="width:180px"
        input-style="font-size:12px"
        bg-color="grey-10"
      >
        <template #prepend><q-icon name="search" color="grey-6" size="16px" /></template>
      </q-input>
    </div>

    <q-table
      :rows="bitacorasFiltradas"
      :columns="columnas"
      row-key="id"
      dark flat
      :loading="cargandoBitacoras"
      :rows-per-page-options="[10, 20, 50]"
      :rows-per-page="10"
      style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
      table-header-style="color:#475569;font-size:10px;letter-spacing:1px;font-family:'JetBrains Mono',monospace"
    >
      <template #body-cell-tipo_vuelo="props">
        <q-td :props="props">
          <q-chip dense :color="colorTipoVuelo(props.value)" text-color="white"
            :label="props.value?.toUpperCase()" style="font-size:10px;font-family:monospace" />
        </q-td>
      </template>

      <template #body-cell-horas_totales="props">
        <q-td :props="props" class="font-mono" style="color:#f8fafc; font-weight:700">
          {{ Number(props.value).toFixed(1) }}h
        </q-td>
      </template>

      <template #body-cell-firma_instructor="props">
        <q-td :props="props">
          <q-icon :name="props.value ? 'verified' : 'pending'"
            :color="props.value ? 'positive' : 'grey-6'" size="18px" />
        </q-td>
      </template>

      <template #body-cell-estudiante="props">
        <q-td :props="props" style="font-size:12px;color:#e2e8f0">
          {{ nombreCorto(props.row.estudiante?.persona) }}
        </q-td>
      </template>

      <template #body-cell-aeronave="props">
        <q-td :props="props" class="font-mono" style="color:#94a3b8;font-size:12px">
          {{ props.row.aeronave?.matricula }}
        </q-td>
      </template>

      <template #no-data>
        <div class="full-width text-center text-grey-6 q-py-xl">
          <q-icon name="menu_book" size="36px" class="q-mb-sm" /><br>Sin bitácoras registradas
        </div>
      </template>
    </q-table>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const authStore = useAuthStore()
const cargando          = ref(true)
const cargandoAeronaves = ref(true)
const cargandoBitacoras = ref(true)
const aeronaves         = ref([])
const bitacoras         = ref([])
const busqueda          = ref('')

const puedeRegistrar = computed(() =>
  ['instructor', 'admin', 'dir_ops'].includes(authStore.rol)
)

// ── KPIs ──────────────────────────────────────────────────────
const totalHoras = computed(() =>
  bitacoras.value.reduce((s, b) => s + (Number(b.horas_totales) || 0), 0).toFixed(1)
)
const totalVuelos  = computed(() => bitacoras.value.length)
const disponibles  = computed(() => aeronaves.value.filter(a => a.estado === 'disponible').length)
const aterrizajes  = computed(() =>
  bitacoras.value.reduce((s, b) => s + (b.aterrizajes || 0), 0)
)

const kpis = computed(() => [
  { label: 'Horas de vuelo', valor: `${totalHoras.value}h`, icon: 'schedule', color: 'red-4',   bg: 'rgba(161,11,19,.12)',     textColor: '#fca5a5' },
  { label: 'Vuelos totales', valor: totalVuelos.value,      icon: 'flight',   color: 'red-8',   bg: 'rgba(107,7,12,.12)',     textColor: '#94a3b8' },
  { label: 'Aeronaves disp.', valor: disponibles.value,    icon: 'airplanemode_active', color: 'green-4', bg: 'rgba(74,222,128,.12)', textColor: '#4ade80' },
  { label: 'Aterrizajes',    valor: aterrizajes.value,     icon: 'moving',   color: 'amber-4',  bg: 'rgba(251,191,36,.12)',     textColor: '#fbbf24' },
])

// ── Tabla bitácoras ────────────────────────────────────────────
const columnas = [
  { name: 'fecha',          label: 'FECHA',       field: 'fecha',          align: 'left',   sortable: true },
  { name: 'estudiante',     label: 'ESTUDIANTE',  field: 'estudiante',     align: 'left' },
  { name: 'aeronave',       label: 'AERONAVE',    field: 'aeronave',       align: 'left' },
  { name: 'origen_icao',    label: 'ORIGEN',      field: 'origen_icao',    align: 'center', style: 'font-family:monospace' },
  { name: 'destino_icao',   label: 'DESTINO',     field: 'destino_icao',   align: 'center', style: 'font-family:monospace' },
  { name: 'tipo_vuelo',     label: 'TIPO',        field: 'tipo_vuelo',     align: 'center' },
  { name: 'horas_totales',  label: 'HORAS',       field: 'horas_totales',  align: 'center', sortable: true },
  { name: 'aterrizajes',    label: 'ATZ',         field: 'aterrizajes',    align: 'center' },
  { name: 'firma_instructor', label: 'FIRMA',     field: 'firma_instructor', align: 'center' },
]

const bitacorasFiltradas = computed(() => {
  if (!busqueda.value) return bitacoras.value
  const q = busqueda.value.toLowerCase()
  return bitacoras.value.filter(b =>
    b.aeronave?.matricula?.toLowerCase().includes(q) ||
    b.origen_icao?.toLowerCase().includes(q) ||
    b.destino_icao?.toLowerCase().includes(q) ||
    b.tipo_vuelo?.toLowerCase().includes(q)
  )
})

// ── Helpers ────────────────────────────────────────────────────
const nombreCorto = (persona) => {
  if (!persona) return '—'
  const n = persona.nombres?.split(' ')[0] || ''
  const a = persona.apellidos?.split(' ')[0] || ''
  return `${n} ${a}`.trim() || '—'
}

const estadoClass = (e) => ({
  disponible:    'estado-disponible',
  mantenimiento: 'estado-mantenimiento',
  baja:          'estado-baja',
}[e] || '')

const estadoColor = (e) => ({
  disponible:    'positive',
  mantenimiento: 'warning',
  baja:          'negative',
}[e] || 'grey')

const colorTipoVuelo = (t) => ({
  local:      'blue-8',
  navegacion: 'cyan-8',
  noche:      'purple-8',
  ifr:        'deep-orange-8',
  sim:        'grey-7',
}[t] || 'grey-7')

// ── Carga de datos ─────────────────────────────────────────────
async function cargarDatos() {
  cargando.value = true
  await Promise.all([cargarAeronaves(), cargarBitacoras()])
  cargando.value = false
}

async function cargarAeronaves() {
  cargandoAeronaves.value = true
  try {
    const { data } = await api.get('/aeronaves')
    aeronaves.value = data.data || []
  } catch { aeronaves.value = [] }
  finally { cargandoAeronaves.value = false }
}

async function cargarBitacoras() {
  cargandoBitacoras.value = true
  try {
    const { data } = await api.get('/bitacoras')
    bitacoras.value = data.data?.data || data.data || []
  } catch { bitacoras.value = [] }
  finally { cargandoBitacoras.value = false }
}

onMounted(cargarDatos)
</script>

<style scoped>
.section-title {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  color: #475569;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 8px;
}

/* KPI cards */
.kpi-card {
  background: #0f1218;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.kpi-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.kpi-num {
  font-size: 22px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  line-height: 1;
}
.kpi-lbl {
  font-size: 10px;
  color: #475569;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-top: 4px;
}

/* Aeronave cards */
.aeronave-card {
  background: #0f1218;
  border-radius: 12px;
  padding: 16px;
  border: 1px solid rgba(255,255,255,.08);
  transition: border-color .2s;
}
.aeronave-card:hover { border-color: rgba(255,255,255,.18); }
.estado-disponible    { border-left: 3px solid #4ade80; }
.estado-mantenimiento { border-left: 3px solid #fbbf24; }
.estado-baja          { border-left: 3px solid #ef4444; }

.av-stat-lbl {
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px; color: #475569;
  letter-spacing: 1px; text-transform: uppercase;
}
.av-stat-val {
  font-family: 'JetBrains Mono', monospace;
  font-size: 14px; color: #e2e8f0;
  font-weight: 600;
}
</style>
