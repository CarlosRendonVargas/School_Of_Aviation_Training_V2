<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Header Hero de la Aeronave ══ -->
    <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-left shadow-24 welcome-hero overflow-hidden rounded-20" v-if="aeronave">
      <div class="hero-glow"></div>
      <div class="row items-center q-gutter-xl relative-position">
        <div class="aero-icon-container shadow-24">
           <q-icon name="flight_takeoff" color="red-9" size="44px" class="pulsate" />
        </div>
        
        <div class="col">
          <div class="font-mono text-grey-6 uppercase tracking-widest q-mb-xs" style="font-size:10px">
            REGISTRO DE AERONAVE · UAEAC {{ aeronave.categoria?.toUpperCase() }}
          </div>
          <div class="font-mono text-white text-weight-bolder uppercase tracking-tighter line-height-1" style="font-size:48px">
            {{ aeronave.matricula }}
          </div>
          <div class="text-grey-5 font-head q-mt-xs" style="font-size:14px; letter-spacing:1px">
            {{ aeronave.fabricante }} {{ aeronave.modelo }} · S/N: {{ aeronave.serie }}
          </div>
        </div>

        <div class="column items-end q-gutter-md">
          <q-badge :color="estadoColor" :label="aeronave.estado?.toUpperCase()"
            class="font-mono text-weight-bolder q-px-xl q-py-sm shadow-24" style="font-size:11px" />
          <q-badge :color="aeronave.airworthiness_vigente ? 'emerald-9' : 'red-10'"
            class="font-mono text-weight-bolder q-px-lg q-py-sm shadow-24" style="font-size:11px">
            <q-icon :name="aeronave.airworthiness_vigente ? 'verified' : 'warning'" size="16px" class="q-mr-sm" />
            {{ aeronave.airworthiness_vigente ? 'HK — AIRWORTHY' : 'ALERTA — UNSERVICEABLE' }}
          </q-badge>
          <div class="row q-gutter-sm">
            <q-btn v-if="puedeEditar" unelevated color="red-9" icon="edit" label="Editar"
              class="premium-btn font-mono" no-caps @click="abrirEditar" />
            <q-btn flat color="grey-6" icon="arrow_back" label="Volver a Flota"
              class="font-mono" @click="$router.push('/aeronaves')" />
          </div>
        </div>
      </div>
    </q-card>

    <!-- Skeleton Header -->
    <q-skeleton v-else type="rect" height="160px" dark class="rounded-20 q-mb-xl" />

    <!-- ══ KPIs de Rendimiento Técnico ══ -->
    <div class="row q-col-gutter-xl q-mb-xl">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <q-card class="premium-glass-card q-pa-xl text-center border-red-low shadow-24 welcome-hero overflow-hidden rounded-20">
          <div class="hero-glow"></div>
          <div class="relative-position">
            <div class="text-h3 text-weight-bolder font-mono q-mb-xs line-height-1"
              :style="`color:${s.color}; text-shadow: 0 0 15px ${s.color}44`">{{ s.valor }}</div>
            <q-separator dark class="q-my-sm opacity-5" />
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ s.label }}</div>
          </div>
        </q-card>
      </div>
    </div>

    <!-- ══ Navegación Técnica de Dossier ══ -->
    <q-card class="premium-glass-card shadow-24 overflow-hidden rounded-20 bonus-grid">
      <q-tabs v-model="tab" dense class="text-grey-5 border-bottom-border"
        active-color="red-9" indicator-color="red-9" align="left" no-caps
        style="padding-left: 10px">
        <q-tab name="info"         icon="settings_suggest"  label="Especificaciones" />
        <q-tab name="mantenimiento" icon="handyman"          label="Control MX" />
        <q-tab name="mel"          icon="report_problem"    label="MEL / Diferidos"
          :alert="melAbiertos > 0" alert-color="red-9" />
        <q-tab name="ads"          icon="gavel"             label="ADs / Directivas"
          :alert="adsPendientes > 0" alert-color="red-10" />
        <q-tab name="bitacora"     icon="auto_stories"      label="Expediente de Vuelo" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white">

        <!-- ════ ESPECIFICACIONES ════ -->
        <q-tab-panel name="info" class="q-pa-xl">
          <div class="row q-col-gutter-xl">
            <!-- Parámetros de Célula -->
            <div class="col-12 col-md-6">
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-lg border-bottom-border pb-md" style="font-size:10px">
                Parámetros de Célula y Motor
              </div>
              <div class="q-gutter-y-sm">
                <div v-for="campo in camposTecnicos" :key="campo.label"
                  class="premium-glass-card q-pa-lg flex items-center justify-between border-red-low hover-row rounded-12">
                  <span class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:10px">{{ campo.label }}</span>
                  <span class="text-white text-weight-bolder font-mono text-subtitle2">{{ campo.valor || '—' }}</span>
                </div>
              </div>
            </div>
            <!-- Certificaciones UAEAC -->
            <div class="col-12 col-md-6">
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-lg border-bottom-border pb-md" style="font-size:10px">
                Certificaciones y Documentos UAEAC
              </div>
              <div class="q-gutter-y-md">
                <div v-for="cert in certsDocs" :key="cert.label"
                  class="premium-glass-card q-pa-xl border-red-low rounded-12 shadow-24"
                  :class="cert.vigente ? '' : 'border-danger-glow'">
                  <div class="row items-center justify-between q-mb-sm">
                    <div class="row items-center">
                      <q-icon :name="cert.vigente ? 'verified_user' : 'warning'" :color="cert.vigente ? 'emerald-9' : 'red-9'" size="20px" class="q-mr-md" />
                      <span class="text-grey-2 text-weight-bolder font-head">{{ cert.label }}</span>
                    </div>
                    <q-badge :color="cert.vigente ? 'emerald-9' : 'red-10'"
                      :label="cert.vigente ? 'VIGENTE' : 'VENCIDO'"
                      class="font-mono text-weight-bolder q-px-lg q-py-xs shadow-24" />
                  </div>
                  <div class="text-caption text-grey-6 font-mono q-ml-xl uppercase tracking-widest" style="font-size:10px">
                    FIN DE VIGENCIA: <span class="text-white text-weight-bold">{{ cert.vencimiento }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ CONTROL DE MANTENIMIENTO ════ -->
        <q-tab-panel name="mantenimiento" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl border-bottom-border pb-md">
            <div>
              <div class="text-h6 text-white font-head text-weight-bolder uppercase tracking-tighter">Historial de Intervenciones Técnicas</div>
              <div class="text-caption text-grey-7 font-mono" style="font-size:10px">Registro de mantenimiento preventivo y correctivo certificado bajo RETAC.</div>
            </div>
            <q-btn v-if="puedeRegistrarMx" color="red-9" icon="add"
              label="Reportar Intervención" class="premium-btn shadow-24 q-px-xl"
              @click="dialogMx = true" />
          </div>
          <div class="q-gutter-y-md">
            <div v-if="!mantenimientos.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
              <q-icon name="handyman" size="64px" class="opacity-10 q-mb-md" />
              <div>Sin intervenciones MX registradas.</div>
            </div>
            <div v-for="mx in mantenimientos" :key="mx.id"
              class="premium-glass-card q-pa-xl flex items-center no-wrap border-red-left hover-row shadow-24 rounded-12">
              <div class="mx-icon-circle q-mr-xl shadow-24">
                <q-icon name="build" color="red-9" size="24px" />
              </div>
              <div class="col">
                <div class="row items-center q-gutter-x-md q-mb-xs">
                  <q-badge outline color="red-8" :label="mx.tipo?.toUpperCase()" class="font-mono text-weight-bolder" />
                  <div class="text-subtitle1 text-white font-head text-weight-bold">{{ mx.descripcion }}</div>
                </div>
                <div class="text-caption text-grey-6 font-mono">
                  {{ formatFechaCO(mx.fecha_realizado) }} · <span class="text-red-9 text-weight-bold">{{ mx.horas_aeronave }}h</span> · TEC: {{ mx.realizado_por }}
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ MEL / DIFERIDOS ════ -->
        <q-tab-panel name="mel" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl border-bottom-border pb-md">
            <div>
              <div class="text-h6 text-white font-head text-weight-bolder uppercase tracking-tighter">Lista de Equipo Mínimo (MEL)</div>
              <div class="text-caption text-grey-7 font-mono" style="font-size:10px">Ítems diferidos conforme RETAC — Categorías A, B, C y D.</div>
            </div>
            <q-btn v-if="puedeRegistrarMx" color="orange-9" outline icon="report_problem"
              label="Abrir Ítem MEL" no-caps @click="dialogMel = true"
              class="font-mono text-weight-bolder" />
          </div>
          <div class="q-gutter-y-md">
            <div v-if="!mel.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
              <q-icon name="verified" size="64px" color="emerald-9" class="q-mb-md" />
              <div class="text-emerald">Sin ítems MEL abiertos. Aeronave en configuración estándar.</div>
            </div>
            <div v-for="m in mel" :key="m.id"
              class="premium-glass-card q-pa-xl flex items-center no-wrap rounded-12 shadow-24"
              :class="m.estado === 'abierto' ? 'border-danger-glow' : 'border-red-low'">
              <div class="mel-cat-badge q-mr-xl shadow-inner flex items-center justify-center">
                <span class="font-mono text-h5 text-red-9 text-weight-bolder">{{ m.categoria }}</span>
              </div>
              <div class="col">
                <div class="text-subtitle1 text-white font-head text-weight-bold q-mb-xs">ATA {{ m.item_ata }} — {{ m.descripcion }}</div>
                <div class="text-caption text-grey-6 font-mono">
                  APERTURA: {{ formatFechaCO(m.fecha_apertura) }} · LÍMITE: <span class="text-red-9 text-weight-bolder">{{ formatFechaCO(m.fecha_limite) }}</span>
                </div>
              </div>
              <q-badge :color="m.estado === 'abierto' ? 'red-10' : 'emerald-9'"
                :label="m.estado?.toUpperCase()"
                class="font-mono text-weight-bolder q-px-xl q-py-sm shadow-24" />
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ ADs / DIRECTIVAS ════ -->
        <q-tab-panel name="ads" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl border-bottom-border pb-md">
            <div>
              <div class="text-h6 text-white font-head text-weight-bolder uppercase tracking-tighter">Directivas de Aeronavegabilidad (ADs)</div>
              <div class="text-caption text-grey-7 font-mono" style="font-size:10px">FAA / EASA / RAC — Obligatorio cumplimiento según RAC 39.</div>
            </div>
          </div>
          <div class="q-gutter-y-md">
            <div v-if="!ads.length" class="text-center q-pa-xl text-grey-7 font-mono">
              <q-icon name="gavel" size="64px" class="opacity-10 q-mb-md" />
              <div>Sin ADs pendientes registradas.</div>
            </div>
            <div v-for="ad in ads" :key="ad.id"
              class="premium-glass-card q-pa-xl flex items-start no-wrap border-red-left shadow-24 rounded-12 hover-row">
              <div class="mx-icon-circle bg-danger-low q-mr-xl shadow-inner flex items-center justify-center">
                <q-icon name="gavel" color="red-9" size="24px" />
              </div>
              <div class="col">
                <div class="row items-center q-gutter-x-md q-mb-xs">
                  <q-badge color="red-10" :label="ad.numero_ad" class="font-mono text-weight-bolder shadow-24" />
                  <div class="text-subtitle1 text-white font-head text-weight-bold">{{ ad.descripcion }}</div>
                </div>
                <div class="text-caption text-grey-6 font-mono">LÍMITE: {{ formatFechaCO(ad.fecha_limite) }} · Horas: {{ ad.horas_limite }}h</div>
              </div>
              <q-badge :color="ad.estado === 'pendiente' ? 'red-10' : 'emerald-9'"
                :label="ad.estado?.toUpperCase()"
                class="font-mono text-weight-bolder q-px-xl q-py-sm shadow-24 q-ml-xl" />
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ EXPEDIENTE DE VUELO ════ -->
        <q-tab-panel name="bitacora" class="q-pa-xl">
          <div class="q-mb-xl border-bottom-border pb-md">
            <div class="text-h6 text-white font-head text-weight-bolder uppercase tracking-tighter">Trazabilidad de Vuelo Reciente</div>
            <div class="text-caption text-grey-7 font-mono" style="font-size:10px">Últimas 30 entradas del expediente técnico conforme RAC 91.417.</div>
          </div>
          <q-table :rows="bitacorasTecnicas"
            :columns="[
              { name:'fecha',  label:'FECHA',   field:'fecha',  align:'left', sortable:true },
              { name:'ruta',   label:'RUTA',    field: r => `${r.origen_icao} → ${r.destino_icao}`, align:'center' },
              { name:'horas',  label:'BLOCK',   field:'horas_totales',  align:'right', sortable:true },
              { name:'piloto', label:'PIC',     field: r => r.instructor?.persona?.nombres || r.estudiante?.persona?.nombres, align:'left' },
            ]"
            dark flat class="rac-table shadow-24 rounded-20" row-key="id">
            <template #body-cell-horas="props">
              <q-td :props="props" class="font-mono text-red-9 text-weight-bolder text-h6">{{ props.value }}H</q-td>
            </template>
          </q-table>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

    <!-- ════ DIÁLOGO: EDITAR AERONAVE ════ -->
    <q-dialog v-model="dialogEditar" backdrop-filter="blur(15px)">
      <q-card style="width:min(640px,95vw)" class="premium-glass-card q-pa-xl border-red-low shadow-24 rounded-20">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
          <div class="row items-center">
            <q-icon name="edit" color="red-9" size="28px" class="q-mr-md" />
            <div class="text-h6 text-white font-head text-weight-bolder uppercase">Editar — {{ aeronave?.matricula }}</div>
          </div>
          <q-btn flat round dense icon="close" @click="dialogEditar = false" color="grey-6" />
        </div>

        <q-form @submit.prevent="guardarEditar" class="q-gutter-y-lg">
          <div class="row q-col-gutter-lg">
            <div class="col-12 col-md-4">
              <q-select v-model="formEditar.estado"
                :options="[{label:'Disponible',value:'disponible'},{label:'Mantenimiento',value:'mantenimiento'},{label:'Baja',value:'baja'}]"
                label="ESTADO" filled dark class="premium-input-login" stack-label emit-value map-options />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="formEditar.venc_airworthiness" type="date" label="VENC. AIRWORTHINESS"
                filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="formEditar.venc_seguro" type="date" label="VENC. SEGURO"
                filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-4">
              <q-input v-model.number="formEditar.horas_celula_total" type="number" min="0"
                label="TTAE CÉLULA" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-4">
              <q-input v-model.number="formEditar.horas_motor_total" type="number" min="0"
                label="TTE MOTOR" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-4">
              <q-input v-model.number="formEditar.horas_desde_oh" type="number" min="0"
                label="SMOH MOTOR" filled dark class="premium-input-login" stack-label />
            </div>
          </div>

          <q-btn type="submit" color="red-10" icon="save" label="Guardar Cambios"
            class="full-width premium-btn q-py-lg shadow-24" :loading="guardandoEditar" />
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import { formatFechaCO } from 'src/utils/formatters'
import dayjs from 'dayjs'

const route     = useRoute()
const $q        = useQuasar()
const authStore = useAuthStore()
const id        = route.params.id

const aeronave          = ref(null)
const mantenimientos    = ref([])
const mel               = ref([])
const ads               = ref([])
const bitacorasTecnicas = ref([])
const horasData         = ref(null)
const cargando          = ref(false)
const tab               = ref('info')
const dialogMx          = ref(false)
const dialogMel         = ref(false)
const dialogEditar      = ref(false)
const guardandoEditar   = ref(false)
const formEditar        = ref({})

const puedeRegistrarMx = computed(() => ['mantenimiento', 'dir_ops', 'admin'].includes(authStore.rol))
const puedeEditar      = computed(() => ['dir_ops', 'admin'].includes(authStore.rol))
const melAbiertos      = computed(() => mel.value.filter(m => m.estado === 'abierto').length)
const adsPendientes    = computed(() => ads.value.filter(a => a.estado === 'pendiente').length)

const estadoColor = computed(() =>
  ({ disponible: 'emerald-9', mantenimiento: 'orange-9', baja: 'grey-8' }[aeronave.value?.estado] || 'grey-8')
)

const stats = computed(() => [
  { label: 'TTAE CÉLULA',   valor: Number(aeronave.value?.horas_celula_total || 0).toFixed(1) + 'h', color: '#A10B13' },
  { label: 'HRS MOTOR',     valor: Number(aeronave.value?.horas_motor_total || 0).toFixed(0) + 'h',  color: '#fff' },
  { label: 'SMOH MOTOR',    valor: Number(aeronave.value?.horas_desde_oh || 0).toFixed(1) + 'h',     color: '#A10B13' },
  { label: 'DISPONIBILIDAD',valor: aeronave.value?.estado === 'disponible' ? 'ONLINE' : 'OFFLINE',   color: aeronave.value?.estado === 'disponible' ? '#10b981' : '#ef4444' },
])

const camposTecnicos = computed(() => [
  { label: 'Matrícula Oficial', valor: aeronave.value?.matricula },
  { label: 'Fabricante / OEM',  valor: aeronave.value?.fabricante },
  { label: 'Modelo Técnico',    valor: aeronave.value?.modelo },
  { label: 'Año de Manufactura',valor: aeronave.value?.anio },
  { label: 'Serial Number',     valor: aeronave.value?.serie },
  { label: 'Categoría RAC',     valor: aeronave.value?.categoria },
  { label: 'Clase',             valor: aeronave.value?.clase },
])

const certsDocs = computed(() => [
  { label: 'Certificado de Aeronavegabilidad', vencimiento: aeronave.value?.venc_airworthiness, vigente: aeronave.value?.airworthiness_vigente },
  { label: 'Póliza de Responsabilidad Civil',  vencimiento: aeronave.value?.venc_seguro,         vigente: dayjs(aeronave.value?.venc_seguro).isAfter(dayjs()) },
])

async function cargar() {
  cargando.value = true
  try {
    const [aeroRes, mxRes, melRes, adsRes, horasRes, bitRes] = await Promise.all([
      api.get(`/aeronaves/${id}`),
      api.get(`/aeronaves/${id}/mantenimientos`),
      api.get(`/aeronaves/${id}/mel`),
      api.get(`/aeronaves/${id}/ads`),
      api.get(`/aeronaves/${id}/horas-acumuladas`),
      api.get(`/bitacoras?aeronave_id=${id}&per_page=30`),
    ])
    aeronave.value          = aeroRes.data.data
    mantenimientos.value    = mxRes.data.data?.data || mxRes.data.data || []
    mel.value               = melRes.data.data || []
    ads.value               = adsRes.data.data || []
    horasData.value         = horasRes.data.data
    bitacorasTecnicas.value = bitRes.data.data?.data || []
  } finally { cargando.value = false }
}

function abrirEditar() {
  formEditar.value = {
    estado: aeronave.value.estado,
    venc_airworthiness: aeronave.value.venc_airworthiness ?? '',
    venc_seguro: aeronave.value.venc_seguro ?? '',
    horas_celula_total: aeronave.value.horas_celula_total ?? 0,
    horas_motor_total: aeronave.value.horas_motor_total ?? 0,
    horas_desde_oh: aeronave.value.horas_desde_oh ?? 0,
  }
  dialogEditar.value = true
}

async function guardarEditar() {
  guardandoEditar.value = true
  try {
    await api.put(`/aeronaves/${id}`, formEditar.value)
    $q.notify({ color: 'positive', message: 'Aeronave actualizada correctamente.' })
    dialogEditar.value = false
    cargar()
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.message ?? 'Error al actualizar.' })
  } finally { guardandoEditar.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

.border-danger-glow  { border: 1px solid rgba(161, 11, 19, 0.5) !important; box-shadow: 0 0 20px rgba(161, 11, 19, 0.12); }

.shadow-inner        { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }

.opacity-10          { opacity: 0.1; }

.bg-danger-low       { background: rgba(161, 11, 19, 0.1); }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.12) 0%, transparent 50%); pointer-events: none; }

.aero-icon-container {
  width: 90px; height: 90px; flex-shrink: 0;
  background: rgba(161, 11, 19, 0.07); border: 1px solid rgba(161, 11, 19, 0.25);
  border-radius: 24px; display: flex; align-items: center; justify-content: center;
}

.mx-icon-circle {
  width: 56px; height: 56px; flex-shrink: 0; border-radius: 16px;
  background: rgba(161, 11, 19, 0.08); border: 1px solid rgba(161, 11, 19, 0.2);
}

.mel-cat-badge {
  width: 60px; height: 60px; flex-shrink: 0; border-radius: 16px;
  background: rgba(161, 11, 19, 0.08); border: 1px solid rgba(161, 11, 19, 0.3);
}

.hover-row { transition: all 0.25s ease; cursor: pointer; &:hover { background: rgba(255,255,255,0.03); transform: translateX(4px); } }
</style>
