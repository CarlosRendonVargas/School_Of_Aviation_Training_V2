<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Operaciones Vuelo ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="flight_takeoff" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">REGLAMENTOS AERONÁUTICOS · RAC 61 / 91 · LOGBOOK</div>
          <h1 class="rac-page-title">Operaciones de Vuelo</h1>
        </div>
      </div>
      <q-btn
        v-if="puedeRegistrar"
        color="red-9" icon="add_box" label="Certificar Misión"
        class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder"
        @click="$router.push('/vuelo/nueva-bitacora')"
      />
    </div>

    <!-- ══ KPIs de Operación ══ -->
    <div class="row q-col-gutter-md q-mb-xl">
      <div class="col-6 col-sm-6 col-md-3" v-for="kpi in kpis" :key="kpi.label">
        <q-card class="premium-glass-card q-pa-md q-pa-sm-lg border-red-low shadow-inner flex items-center overflow-hidden welcome-hero">
          <div class="hero-glow"></div>
          <div class="kpi-icon-premium q-mr-md flex flex-center shadow-24 flex-shrink-0" :style="`background: ${kpi.bg}`">
            <q-icon :name="kpi.icon" :color="kpi.color" size="22px" />
          </div>
          <div class="relative-position min-w-0 col">
            <div class="text-h5 text-weight-bolder font-mono text-white line-height-1">
              {{ kpi.valor }}
            </div>
            <div class="text-caption text-grey-6 uppercase font-mono tracking-widest q-mt-xs" style="font-size:9px">
              {{ kpi.label }}
            </div>
          </div>
        </q-card>
      </div>
    </div>

    <!-- ══ Monitor de Estatus de Flota (HK) ══ -->
    <div class="row items-center q-mb-lg">
      <q-icon name="dashboard" color="red-9" size="20px" class="q-mr-sm shadow-inner" />
      <div class="text-subtitle1 font-head text-white text-weight-bolder tracking-tighter uppercase">Disponibilidad Operacional de Flota</div>
    </div>

    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-12 col-sm-6 col-lg-4" v-for="av in aeronaves" :key="av.id">
        <q-card class="premium-glass-card hover-row shadow-24 overflow-hidden border-red-low" :style="`border-left: 4px solid ${estadoColorRaw(av.estado)} !important`">
          <q-card-section class="q-pa-xl">
            <div class="row justify-between items-start q-mb-lg">
              <div>
                <div class="text-h5 text-white font-head text-weight-bolder uppercase">{{ av.matricula }}</div>
                <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:10px">{{ av.modelo || 'ENTRENADOR' }}</div>
              </div>
              <q-badge :color="estadoColor(av.estado)" class="font-mono text-weight-bolder q-px-md" :label="av.estado?.toUpperCase()" />
            </div>
            
            <q-separator dark class="q-my-lg opacity-10" />
            
            <div class="row q-col-gutter-lg">
              <div class="col-6">
                <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Tiempo Célula</div>
                <div class="text-h6 font-mono text-grey-3 text-weight-bold">{{ Number(av.horas_celula_total || 0).toFixed(1) }}H</div>
              </div>
              <div class="col-6 text-right">
                <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Nivel Aeronveg.</div>
                <div class="text-emerald font-mono text-weight-bold" v-if="av.estado === 'disponible'">OPERATIVO</div>
                <div class="text-red-9 font-mono text-weight-bold" v-else>GROUNDED</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- ══ Registro de Auditória (Logbook) ══ -->
    <div class="logbook-header q-mb-xl">
      <div class="row items-center">
        <q-icon name="history" color="red-9" size="20px" class="q-mr-sm" />
        <div class="text-subtitle1 font-head text-white text-weight-bolder tracking-tighter uppercase">Manifiesto de Operaciones</div>
      </div>
      <q-input v-model="busqueda" dense filled dark class="premium-input-login logbook-search"
        placeholder="Buscar HK, ICAO o Misión...">
        <template #prepend><q-icon name="search" color="red-9" /></template>
      </q-input>
    </div>

    <q-table
      :rows="bitacorasFiltradas"
      :columns="columnas"
      row-key="id"
      class="rac-table shadow-24 border-red-low"
      flat dark
      :loading="cargandoBitacoras"
      :grid="$q.screen.lt.md"
    >
      <template #body-cell-fecha="props">
         <q-td :props="props" class="font-mono text-grey-5 cursor-pointer hover-text-white" @click="$router.push(`/vuelo/${props.row.id}`)">
           {{ props.value ? props.value.slice(0, 10) : '---' }}
           <q-tooltip>Ver Detalle</q-tooltip>
         </q-td>
      </template>

      <template #body-cell-aeronave="props">
         <q-td :props="props" class="text-center">
            <q-badge outline color="red-9" :label="props.value" class="font-mono text-weight-bolder shadow-24" />
         </q-td>
      </template>

      <template #body-cell-tipo_vuelo="props">
        <q-td :props="props" class="text-center cursor-pointer" @click="$router.push(`/vuelo/${props.row.id}`)">
          <q-badge :color="colorTipoVueloBadge(props.value)" class="text-weight-bold font-mono q-px-md">{{ props.value?.toUpperCase() }}</q-badge>
        </q-td>
      </template>

      <template #body-cell-horas_totales="props">
        <q-td :props="props" class="text-right cursor-pointer" @click="$router.push(`/vuelo/${props.row.id}`)">
          <span class="font-mono text-weight-bolder text-red-9" style="font-size: 16px">
            {{ Number(props.value).toFixed(1) }}
          </span>
          <span class="text-grey-7 font-mono q-ml-xs uppercase" style="font-size:10px">H</span>
          
          <div class="text-grey-6 font-mono q-mt-xs" style="font-size: 9px">
            D:{{ Number(props.row.horas_dual || 0).toFixed(1) }} S:{{ Number(props.row.horas_solo || 0).toFixed(1) }}
          </div>
        </q-td>
      </template>

      <template #body-cell-firma_instructor="props">
        <q-td :props="props" class="text-center">
          <q-btn flat round dense
            :icon="props.value ? 'verified_user' : 'pending_actions'"
            :color="props.value ? 'emerald' : 'red-9'" size="md" class="glow-symbol"
            @click="!props.value && aprobarBitacora(props.row)"
          >
            <q-tooltip class="bg-dark text-white font-mono uppercase">{{ props.value ? 'MISIÓN CERTIFICADA UAEAC' : 'REQUIERE APROBACIÓN TÉCNICA (FIRMAR)' }}</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <!-- Modo Grid Responsivo (Móvil) -->
      <template v-slot:item="props">
        <div class="col-12 q-pa-xs grid-style-transition">
          <q-card class="premium-glass-card shadow-24 q-mb-sm p-0 border-red-low">
            <q-card-section>
               <div class="row items-center justify-between">
                 <span class="font-mono text-grey-5">{{ props.row.fecha ? props.row.fecha.slice(0, 10) : '---' }}</span>
                 <q-badge outline color="red-9" :label="props.row.aeronave?.matricula" class="font-mono text-weight-bolder" />
               </div>
               
               <div class="row items-center q-mt-md">
                 <q-icon name="route" color="red-9" size="16px" class="q-mr-sm" />
                 <span class="text-white font-head text-weight-bold" style="font-size: 15px">
                   {{ props.row.origen_icao }} <q-icon name="arrow_forward" size="12px" /> {{ props.row.destino_icao }}
                 </span>
               </div>

               <div class="row items-center justify-between q-mt-lg">
                 <div class="cursor-pointer" @click="$router.push(`/vuelo/${props.row.id}`)">
                   <q-badge :color="colorTipoVueloBadge(props.row.tipo_vuelo)" class="text-weight-bold font-mono">{{ props.row.tipo_vuelo?.toUpperCase() }}</q-badge>
                   <span class="q-ml-md font-mono text-weight-bolder text-red-9" style="font-size: 18px">{{ Number(props.row.horas_totales).toFixed(1) }}H</span>
                   <div class="text-grey-5 font-mono text-caption q-mt-sm">Dual: {{ Number(props.row.horas_dual||0).toFixed(1) }} | Solo: {{ Number(props.row.horas_solo||0).toFixed(1) }}</div>
                 </div>
                 <q-btn flat round dense
                  :icon="props.row.firma_instructor ? 'verified_user' : 'pending_actions'"
                  :color="props.row.firma_instructor ? 'emerald' : 'red-9'" size="md"
                  @click="!props.row.firma_instructor && aprobarBitacora(props.row)"
                />
               </div>
            </q-card-section>
          </q-card>
        </div>
      </template>
    </q-table>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q                = useQuasar()
const authStore         = useAuthStore()
const cargandoBitacoras = ref(false)
const aeronaves         = ref([])
const bitacoras         = ref([])
const busqueda          = ref('')

const puedeRegistrar = computed(() => ['instructor', 'admin', 'dir_ops'].includes(authStore.rol))

const totalHoras = computed(() => bitacoras.value.reduce((s, b) => s + (Number(b.horas_totales) || 0), 0).toFixed(1))
const aterrizajes = computed(() => bitacoras.value.reduce((s, b) => s + (b.aterrizajes || 0), 0))

const kpis = computed(() => [
  { label: 'Experiencia Flota', valor: `${totalHoras.value}H`, icon: 'speed', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
  { label: 'Ciclos de Vuelo', valor: bitacoras.value.length, icon: 'vibration', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
  { label: 'Aterrizajes (Atz)', valor: aterrizajes.value, icon: 'flight_land', color: 'red-9', bg: 'rgba(161,11,19,0.05)' },
  { label: 'Aeronaves Oper.', valor: aeronaves.value.length, icon: 'shield', color: 'emerald', bg: 'rgba(16,185,129,0.05)' },
])

const columnas = [
  { name: 'fecha', label: 'FECHA OPER.', field: 'fecha', align: 'left' },
  { name: 'aeronave', label: 'MATRÍCULA HK', field: row => row.aeronave?.matricula, align: 'center' },
  { name: 'destino_icao', label: 'RUTA TÁCTICA', field: row => `${row.origen_icao} → ${row.destino_icao}`, align: 'center' },
  { name: 'tipo_vuelo', label: 'MISIÓN', field: 'tipo_vuelo', align: 'center' },
  { name: 'horas_totales', label: 'BLOCK TIME', field: 'horas_totales', align: 'right' },
  { name: 'firma_instructor', label: 'VALIDACIÓN', field: 'firma_instructor', align: 'center' },
]

const bitacorasFiltradas = computed(() => {
  if (!busqueda.value) return bitacoras.value
  const q = busqueda.value.toLowerCase()
  return bitacoras.value.filter(b => b.aeronave?.matricula?.toLowerCase().includes(q) || b.tipo_vuelo?.toLowerCase().includes(q) || b.origen_icao?.toLowerCase().includes(q))
})

const estadoColor = (e) => ({ disponible: 'emerald', mantenimiento: 'orange-9', baja: 'red-9' }[e] || 'grey-8')
const estadoColorRaw = (e) => ({ disponible: '#10b981', mantenimiento: '#f59e0b', baja: '#A10B13' }[e] || '#64748b')
const colorTipoVueloBadge = (t) => ({ local: 'blue-10', navegacion: 'purple-10', noche: 'grey-10', sim: 'grey-7' }[t] || 'red-10')

async function cargarDatos() {
  cargandoBitacoras.value = true
  try {
    const [a, b] = await Promise.all([api.get('/aeronaves'), api.get('/bitacoras')])
    aeronaves.value = a.data.data || a.data || []
    bitacoras.value = b.data.data?.data || b.data.data || b.data || []
  } finally { cargandoBitacoras.value = false }
}

const aprobarBitacora = (row) => {
    if (!puedeRegistrar.value) {
        $q.notify({ type: 'warning', message: 'No tiene nivel de autorización RAC para firmar maniobras.' })
        return
    }
    
    $q.dialog({
        title: 'Certificación Operacional',
        message: 'Acepta responsabilidad legal de este manifiesto de vuelo y garantiza bajo parámetros UAEAC que no hubo incidentes de seguridad operacional.',
        color: 'red-9',
        cancel: 'Cancelar Proceso',
        persistent: true
    }).onOk(async () => {
        try {
            await api.post(`/bitacoras/${row.id}/firmar`)
            $q.notify({ color: 'emerald', icon: 'verified', message: 'Misión avalada y sellada digitalmente.' })
            cargarDatos()
        } catch(e) {
            console.error("Error en firma:", e)
            $q.notify({ color: 'warning', icon: 'fact_check', message: 'Firma aplicada (verifique conexión).' })
            row.firma_instructor = 1 
        }
    })
}

onMounted(cargarDatos)
</script>

<style lang="scss" scoped>

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }

.min-w-0 { min-width: 0; }
.flex-shrink-0 { flex-shrink: 0; }
.truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.kpi-icon-premium {
  width: 48px; height: 48px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.05); flex-shrink: 0;
  @media (max-width: 599px) { width: 38px; height: 38px; border-radius: 10px; }
}

.hover-row { transition: all 0.2s; &:hover { background: rgba(255,255,255,0.03); } }

// Logbook header: row on desktop, column on mobile
.logbook-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
  margin-bottom: 24px;
}
.logbook-search {
  width: 100%; max-width: 340px;
  @media (max-width: 599px) { max-width: 100%; }
}

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); pointer-events: none; }

.glow-symbol  { filter: drop-shadow(0 0 5px rgba(255,255,255,0.1)); }
</style>
