<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom: 100px">

    <!-- ══ Encabezado de Flota Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="flight_takeoff" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">GESTIÓN DE FLOTA · RAC 91 / 121 / 141</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Registro de Aeronaves HK</h1>
        </div>
      </div>
      <q-btn v-if="authStore.esDirOps || authStore.esAdmin" unelevated color="red-9" icon="add"
        label="Ingresar Nueva Aeronave" no-caps @click="dialogNueva=true"
        class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" />
    </div>

    <!-- ══ Filtros de Alta Operacional ══ -->
    <q-card class="premium-glass-card q-mb-xl border-red-low shadow-24 rounded-20 welcome-hero overflow-hidden">
      <div class="hero-glow"></div>
      <div class="row items-center relative-position q-pa-md q-pa-sm-lg chips-container">
        <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mr-md" style="font-size:10px; white-space: nowrap;">ESTADO:</div>
        <div class="row q-gutter-sm no-wrap scroll-x full-width-mobile">
          <q-btn unelevated no-caps :color="filtroEstado===null?'red-10':'transparent'"
            :class="filtroEstado===null?'shadow-24 text-weight-bolder':'glass-btn text-grey-6'"
            @click="filtroEstado=null" label="TODAS" class="q-px-lg rounded-12 font-mono" />
          <q-btn unelevated no-caps :color="filtroEstado==='disponible'?'emerald':'transparent'"
            :class="filtroEstado==='disponible'?'shadow-24 text-weight-bolder':'glass-btn text-grey-6'"
            @click="filtroEstado='disponible'" label="OPERATIVAS" class="q-px-lg rounded-12 font-mono" />
          <q-btn unelevated no-caps :color="filtroEstado==='mantenimiento'?'orange-9':'transparent'"
            :class="filtroEstado==='mantenimiento'?'shadow-24 text-weight-bolder':'glass-btn text-grey-6'"
            @click="filtroEstado='mantenimiento'" label="MX (GROUNDED)" class="q-px-lg rounded-12 font-mono" />
        </div>
        <div class="col-12 col-sm-auto q-ml-sm-auto q-mt-md q-mt-sm-none text-right">
          <q-badge color="red-10" :label="`${aeronaves.length} HKs`" class="font-mono text-weight-bolder q-px-md q-py-xs shadow-24" />
        </div>
      </div>
    </q-card>

    <!-- ══ Grid de Aeronaves Premium ══ -->
    <div class="row q-col-gutter-xl">
      <!-- Skeletons de carga -->
      <div v-if="cargando" v-for="n in 3" :key="n" class="col-12 col-sm-6 col-lg-4">
        <q-skeleton type="rect" height="280px" dark class="rounded-20" />
      </div>

      <!-- Tarjetas de Aeronave -->
      <div class="col-12 col-md-6 col-lg-4 animate-slide-up"
        v-for="(av, idx) in aeronavesFiltradas" :key="av.id"
        :style="`animation-delay: ${idx * 0.07}s`">

        <q-card class="premium-glass-card cursor-pointer fleet-card shadow-24 overflow-hidden rounded-20 border-red-low"
          @click="$router.push(`/aeronaves/${av.id}`)">

          <!-- Banda de Estado Superior -->
          <div class="status-stripe" :style="`background: ${colorBandaEstado(av.estado)}`"></div>

          <q-card-section class="q-pa-md q-pa-sm-xl q-pt-lg">
            <!-- Matrícula y Modelo -->
            <div class="row items-start justify-between q-mb-lg flex-wrap">
              <div class="col-12 col-sm-auto q-mb-sm q-mb-sm-none">
                <div class="font-mono text-white text-weight-bolder uppercase tracking-tighter line-height-1" style="font-size:min(32px, 8vw)">
                  {{ av.matricula }}
                </div>
                <div class="text-grey-5 font-head uppercase tracking-widest q-mt-xs" style="font-size:9px; letter-spacing:1px">
                  {{ av.fabricante }} {{ av.modelo }}
                </div>
              </div>
              <div class="column items-end q-gutter-xs">
                <q-chip dense :color="colorChipEstado(av.estado)" text-color="white"
                  :label="av.estado?.toUpperCase()" class="font-mono text-weight-bolder shadow-24" style="font-size:9px" />
                <q-chip dense outline color="grey-6" :label="av.clase?.toUpperCase()" class="font-mono" style="font-size:8px" />
              </div>
            </div>

            <!-- Dashboard de Horas Técnicas -->
            <div class="row q-col-gutter-md q-mb-lg bg-black-20 rounded-12 q-pa-md shadow-inner border-red-low">
              <div class="col-4 text-center">
                <div class="text-caption text-grey-6 uppercase font-mono q-mb-xs" style="font-size:8px">TTAE CÉLULA</div>
                <div class="text-h6 text-white text-weight-bolder font-mono">{{ Number(av.horas_celula_total || 0).toFixed(0) }}<span class="text-caption text-grey-6">h</span></div>
              </div>
              <div class="col-4 text-center border-column-x">
                <div class="text-caption text-grey-6 uppercase font-mono q-mb-xs" style="font-size:8px">SMOH MOTOR</div>
                <div class="text-h6 text-red-9 text-weight-bolder font-mono">{{ Number(av.horas_desde_oh || 0).toFixed(0) }}<span class="text-caption text-grey-6">h</span></div>
              </div>
              <div class="col-4 text-center">
                <div class="text-caption text-grey-6 uppercase font-mono q-mb-xs" style="font-size:8px">A/W VENCE</div>
                <div class="text-h6 text-weight-bolder font-mono" :style="`color:${colorDiasVenc(diasAirworthiness(av))}`">
                  {{ diasAirworthiness(av) }}<span class="text-caption text-grey-6">d</span>
                </div>
              </div>
            </div>

            <!-- Alertas Técnicas MEL / ADs -->
            <div class="row q-col-gutter-xs items-center">
              <div v-if="av.mel_abiertos_count > 0" class="col-auto">
                <q-badge color="orange-9" class="q-pa-xs font-mono text-weight-bolder shadow-24">
                  <q-icon name="warning" size="12px" class="q-mr-xs" /> {{ av.mel_abiertos_count }} MEL
                </q-badge>
              </div>
              <div v-if="av.ads_pendientes_count > 0" class="col-auto">
                <q-badge color="red-10" class="q-pa-xs font-mono text-weight-bolder shadow-24">
                  <q-icon name="error" size="12px" class="q-mr-xs" /> {{ av.ads_pendientes_count }} ADs
                </q-badge>
              </div>
              <div v-if="!av.mel_abiertos_count && !av.ads_pendientes_count" class="col-auto">
                <q-badge color="emerald-9" class="q-pa-xs font-mono text-weight-bolder shadow-24">
                  <q-icon name="verified" size="12px" class="q-mr-xs" /> SIN ALERTAS MX
                </q-badge>
              </div>
              <div class="col-auto">
                <q-badge outline :color="diasSeguro(av) < 30 ? 'red-9' : 'grey-7'" class="q-pa-xs font-mono">
                  <q-icon name="shield" size="12px" class="q-mr-xs" /> SEGURO: {{ diasSeguro(av) }}d
                </q-badge>
              </div>
            </div>
          </q-card-section>

          <!-- Ícono de Mantenimiento Giratorio -->
          <q-icon v-if="av.estado === 'mantenimiento'" name="settings_suggest" color="orange-8"
            size="28px" class="absolute-top-right q-ma-md spin-slow" />
        </q-card>
      </div>

      <!-- Estado vacío -->
      <div v-if="!cargando && !aeronavesFiltradas.length" class="col-12">
        <div class="text-center q-pa-xl premium-glass-card border-red-low rounded-20 shadow-24">
          <q-icon name="flight_takeoff" size="100px" class="opacity-10 q-mb-xl" />
          <div class="text-h5 text-grey-6 font-head uppercase tracking-widest">Ninguna aeronave identificada con los parámetros seleccionados</div>
        </div>
      </div>
    </div>

    <!-- ════ DIÁLOGO: REGISTRAR NUEVA AERONAVE ════ -->
    <q-dialog v-model="dialogNueva" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl border-red-low shadow-24 rounded-20" style="width:min(800px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
          <div class="row items-center">
            <q-icon name="plus_one" color="red-9" size="32px" class="q-mr-md glow-primary" />
            <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Alta de Aeronave HK</div>
          </div>
          <q-btn flat round dense icon="close" @click="dialogNueva = false" color="grey-6" class="bg-black-20 hover-red" />
        </div>

        <q-form @submit.prevent="guardarNueva" class="q-gutter-y-lg">
          <div class="row q-col-gutter-lg">
            <!-- Datos Básicos -->
            <div class="col-12 col-md-4">
              <q-input v-model="form.matricula" label="MATRÍCULA" filled dark class="premium-input-login" stack-label placeholder="HK-1234" />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.modelo" label="MODELO" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.fabricante" label="FABRICANTE" filled dark class="premium-input-login" stack-label />
            </div>

            <!-- Especificaciones -->
            <div class="col-12 col-md-3">
              <q-input v-model="form.serie" label="NÚMERO DE SERIE" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model.number="form.anio" type="number" label="AÑO" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-3">
              <q-select v-model="form.categoria" :options="['normal', 'utilidad', 'acrobatico']" label="CATEGORÍA" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-3">
              <q-select v-model="form.clase" :options="['monomotor', 'multimotor']" label="CLASE" filled dark class="premium-input-login" stack-label />
            </div>

            <!-- Horas Técnicas -->
            <div class="col-4">
              <q-input v-model.number="form.horas_celula_total" type="number" label="TTAE CÉLULA" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-4">
              <q-input v-model.number="form.horas_motor_total" type="number" label="TTE MOTOR" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-4">
              <q-input v-model.number="form.horas_desde_oh" type="number" label="SMOH motor" filled dark class="premium-input-login" stack-label />
            </div>

            <!-- Documentación Critica -->
            <div class="col-12 col-md-4">
              <q-input v-model="form.cert_airworthiness" label="CERT. NAVEGABILIDAD" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.venc_airworthiness" type="date" label="VENC. A/W" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="form.venc_seguro" type="date" label="VENC. SEGURO" filled dark class="premium-input-login" stack-label />
            </div>
          </div>

          <q-btn type="submit" color="red-10" icon="save" label="Integrar a la Línea de Vuelo" class="full-width premium-btn q-py-lg shadow-24" :loading="guardando" />
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const authStore   = useAuthStore()
const aeronaves   = ref([])
const cargando    = ref(false)
const filtroEstado = ref(null)
const dialogNueva  = ref(false)
const guardando    = ref(false)

const form = ref({
  matricula: '',
  modelo: '',
  fabricante: '',
  serie: '',
  anio: 2020,
  categoria: 'normal',
  clase: 'monomotor',
  horas_celula_total: 0,
  horas_motor_total: 0,
  horas_desde_oh: 0,
  cert_airworthiness: '',
  venc_airworthiness: '',
  venc_seguro: '',
})

async function guardarNueva() {
  guardando.value = true
  try {
    const { data } = await api.post('/aeronaves', form.value)
    $q.notify({ color: 'emerald', message: data.mensaje || 'Aeronave integrada a la flota.' })
    dialogNueva.value = false
    cargar()
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'Error al registrar aeronave.'
    $q.notify({ color: 'negative', message: msg })
  } finally { guardando.value = false }
}

const aeronavesFiltradas = computed(() =>
  filtroEstado.value ? aeronaves.value.filter(a => a.estado === filtroEstado.value) : aeronaves.value
)

const diasAirworthiness = (av) => dayjs(av.venc_airworthiness).diff(dayjs(), 'day')
const diasSeguro        = (av) => dayjs(av.venc_seguro).diff(dayjs(), 'day')

const colorDiasVenc = (dias) => {
  if (dias <= 0)  return '#ef4444'
  if (dias <= 30) return '#ef4444'
  if (dias <= 60) return '#f59e0b'
  return '#22c55e'
}

const colorBandaEstado = (e) => ({ disponible: '#10b981', mantenimiento: '#f97316', baja: '#64748b' }[e] || '#64748b')
const colorChipEstado  = (e) => ({ disponible: 'positive', mantenimiento: 'orange-9', baja: 'grey-7' }[e] || 'grey')

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/aeronaves')
    aeronaves.value = (data.data || []).map(av => ({
      ...av,
      mel_abiertos_count:   av.mel_abiertos?.length || 0,
      ads_pendientes_count: av.ads_pendientes?.length || 0,
    }))
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>
.hover-red { transition: color 0.2s; &:hover { color: #A10B13 !important; } }
.bg-black-20 { background: rgba(0,0,0,0.2); }
.pb-md { padding-bottom: 16px; }
.border-bottom-border { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes slideUp { from { opacity: 0; transform: translateY(25px); } to { opacity: 1; transform: translateY(0); } }

.glass-btn { background: rgba(255,255,255,0.03) !important; border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.2s; &:hover { background: rgba(161,11,19,0.1) !important; } }
</style>
