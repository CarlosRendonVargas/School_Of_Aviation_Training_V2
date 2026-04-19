<template>
  <q-page padding style="padding-bottom:80px">

    <!-- Header aeronave -->
    <q-card flat class="card-rac q-mb-lg" style="background:#0f1218">
      <q-card-section>
        <q-skeleton v-if="cargando" type="rect" height="70px" dark />
        <div v-else class="row items-center q-gutter-md flex-wrap">
          <div style="width:52px; height:52px; background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.25);
            border-radius:12px; display:flex; align-items:center; justify-content:center">
            <q-icon name="flight_takeoff" color="primary" size="26px" />
          </div>
          <div class="col">
            <div class="font-mono text-white" style="font-size:22px; font-weight:700; letter-spacing:1px">
              {{ aeronave?.matricula }}
            </div>
            <div style="font-size:13px; color:#64748b">
              {{ aeronave?.fabricante }} {{ aeronave?.modelo }} · {{ aeronave?.anio }}
            </div>
          </div>
          <div class="row q-gutter-sm items-center">
            <q-chip dense :color="estadoColor" :label="aeronave?.estado?.toUpperCase()" text-color="white" />
            <q-chip dense :color="aeronave?.airworthiness_vigente ? 'positive' : 'negative'"
              :icon="aeronave?.airworthiness_vigente ? 'verified' : 'warning'"
              :label="aeronave?.airworthiness_vigente ? 'Airworthy' : 'Vencida'"
              text-color="white" style="font-size:10px" />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Stats de la aeronave -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <div class="stat-card text-center">
          <div class="font-mono" :style="`font-size:18px; font-weight:700; color:${s.color}`">{{ s.valor }}</div>
          <div class="stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <q-tabs v-model="tab" dense align="left" class="q-mb-md"
      active-color="primary" indicator-color="primary"
      style="border-bottom:1px solid rgba(255,255,255,.08)">
      <q-tab name="info"          icon="info"           label="General"      no-caps />
      <q-tab name="mantenimiento" icon="build"          label="Mantenimiento" no-caps />
      <q-tab name="mel"           icon="report_problem" label="MEL"          no-caps
        :alert="melAbiertos > 0" alert-color="warning" />
      <q-tab name="ads"           icon="gavel"          label="ADs"          no-caps
        :alert="adsPendientes > 0" alert-color="negative" />
      <q-tab name="bitacora"      icon="library_books"  label="Bitácora técnica" no-caps />
    </q-tabs>

    <q-tab-panels v-model="tab" animated dark style="background:transparent">

      <!-- ─── Información general ─── -->
      <q-tab-panel name="info" class="q-pa-none">
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-6">
            <q-card flat class="card-rac" style="background:#0f1218">
              <q-card-section>
                <div class="font-mono q-mb-sm" style="font-size:10px; color:#475569; letter-spacing:2px">DATOS TÉCNICOS</div>
                <div v-for="campo in camposTecnicos" :key="campo.label"
                  class="row justify-between q-py-xs" style="border-bottom:1px solid rgba(255,255,255,.05)">
                  <span style="font-size:12.5px; color:#64748b">{{ campo.label }}</span>
                  <span class="font-mono" :style="`font-size:12.5px; color:${campo.color || '#e2e8f0'}`">{{ campo.valor }}</span>
                </div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-6">
            <q-card flat class="card-rac" style="background:#0f1218">
              <q-card-section>
                <div class="font-mono q-mb-sm" style="font-size:10px; color:#475569; letter-spacing:2px">CERTIFICACIONES</div>
                <div v-for="cert in certsDocs" :key="cert.label"
                  class="row justify-between items-center q-py-sm" style="border-bottom:1px solid rgba(255,255,255,.05)">
                  <div>
                    <div style="font-size:12.5px; color:#e2e8f0">{{ cert.label }}</div>
                    <div class="font-mono" style="font-size:11px; color:#475569">Vence: {{ cert.vencimiento }}</div>
                  </div>
                  <q-chip dense :color="cert.vigente ? 'positive' : 'negative'"
                    :label="cert.vigente ? 'VIGENTE' : 'VENCIDO'" text-color="white" style="font-size:9px" />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-tab-panel>

      <!-- ─── Mantenimiento ─── -->
      <q-tab-panel name="mantenimiento" class="q-pa-none">
        <div class="row justify-end q-mb-md" v-if="puedeRegistrarMx">
          <q-btn unelevated color="orange" icon="build" label="Registrar mantenimiento" no-caps
            style="border-radius:8px" @click="dialogMx = true" />
        </div>
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="mx in mantenimientos" :key="mx.id" style="padding:14px 16px">
            <q-item-section avatar>
              <q-chip dense color="orange" :label="mx.tipo.replace(/_/g,' ').toUpperCase()"
                text-color="white" style="font-size:9px; max-width:100px" />
            </q-item-section>
            <q-item-section>
              <q-item-label style="font-size:13px; color:#e2e8f0">{{ mx.descripcion?.slice(0,80) }}</q-item-label>
              <q-item-label caption style="font-size:11px">
                {{ mx.fecha_realizado }} · {{ mx.horas_aeronave }}h · {{ mx.realizado_por }}
              </q-item-label>
            </q-item-section>
            <q-item-section side v-if="mx.proxima_fecha">
              <div class="text-right font-mono" style="font-size:10px; color:#f59e0b">
                Próximo:<br>{{ mx.proxima_fecha }}
              </div>
            </q-item-section>
          </q-item>
          <q-item v-if="!mantenimientos.length" style="padding:32px">
            <q-item-section class="text-center" style="color:#475569; font-size:13px">Sin registros de mantenimiento</q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>

      <!-- ─── MEL ─── -->
      <q-tab-panel name="mel" class="q-pa-none">
        <div class="row justify-end q-mb-md" v-if="puedeRegistrarMx">
          <q-btn unelevated color="warning" icon="add" label="Abrir ítem MEL" no-caps
            style="border-radius:8px" @click="dialogMel = true" />
        </div>
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="m in mel" :key="m.id" style="padding:14px 16px">
            <q-item-section avatar>
              <div style="width:32px; height:32px; border-radius:8px; background:rgba(245,158,11,.15);
                display:flex; align-items:center; justify-content:center">
                <span class="font-mono text-warning" style="font-size:14px; font-weight:700">{{ m.categoria }}</span>
              </div>
            </q-item-section>
            <q-item-section>
              <q-item-label style="font-size:13px; color:#e2e8f0">{{ m.item_ata }} — {{ m.descripcion?.slice(0,70) }}</q-item-label>
              <q-item-label caption>Apertura: {{ m.fecha_apertura }} · Límite:
                <span :style="`color:${esMelVencido(m) ? '#ef4444' : '#f59e0b'}`">{{ m.fecha_limite }}</span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-chip dense :color="m.estado === 'abierto' ? 'warning' : 'positive'"
                :label="m.estado.toUpperCase()" text-color="white" style="font-size:10px" />
            </q-item-section>
          </q-item>
          <q-item v-if="!mel.length" style="padding:32px">
            <q-item-section class="text-center" style="color:#22c55e; font-size:13px">
              <q-icon name="check_circle" size="28px" class="q-mb-xs" /><br>Sin ítems MEL abiertos
            </q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>

      <!-- ─── ADs ─── -->
      <q-tab-panel name="ads" class="q-pa-none">
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="ad in ads" :key="ad.id" style="padding:14px 16px">
            <q-item-section>
              <q-item-label>
                <span class="font-mono text-white" style="font-size:12px">{{ ad.numero_ad }}</span>
                <span style="font-size:13px; color:#94a3b8; margin-left:8px">{{ ad.titulo?.slice(0,60) }}</span>
              </q-item-label>
              <q-item-label caption>
                Emitido: {{ ad.fecha_emision }}
                <span v-if="ad.fecha_limite" :style="`color:${esAdVencido(ad) ? '#ef4444' : '#f59e0b'}`">
                  · Límite: {{ ad.fecha_limite }}
                </span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <div class="row items-center q-gutter-xs">
                <q-chip dense
                  :color="ad.estado === 'cumplido' ? 'positive' : ad.estado === 'pendiente' ? 'negative' : 'grey'"
                  :label="ad.estado.toUpperCase()" text-color="white" style="font-size:10px" />
                <q-btn v-if="ad.estado === 'pendiente' && puedeRegistrarMx"
                  flat dense round icon="check" color="positive" size="sm"
                  @click="cumplirAd(ad)" />
              </div>
            </q-item-section>
          </q-item>
          <q-item v-if="!ads.length" style="padding:32px">
            <q-item-section class="text-center" style="color:#475569; font-size:13px">Sin ADs registradas</q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>

      <!-- ─── Bitácora técnica ─── -->
      <q-tab-panel name="bitacora" class="q-pa-none">
        <div class="row q-col-gutter-sm q-mb-md">
          <div class="col-4">
            <div class="stat-card text-center" style="padding:10px">
              <div class="font-mono text-primary" style="font-size:18px; font-weight:700">{{ Number(horasRegistradas || 0).toFixed(0) }}</div>
              <div style="font-size:9px; color:#475569; font-family:monospace">H. en sistema</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-card text-center" style="padding:10px">
              <div class="font-mono text-teal" style="font-size:18px; font-weight:700">{{ totalVuelos }}</div>
              <div style="font-size:9px; color:#475569; font-family:monospace">Vuelos</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-card text-center" style="padding:10px">
              <div class="font-mono text-amber" style="font-size:18px; font-weight:700">{{ Number(aeronave?.horas_desde_oh || 0).toFixed(0) }}</div>
              <div style="font-size:9px; color:#475569; font-family:monospace">SMOH</div>
            </div>
          </div>
        </div>
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="b in bitacorasTecnicas" :key="b.id" style="padding:10px 16px">
            <q-item-section avatar>
              <div class="font-mono text-primary" style="font-size:13px; font-weight:600">{{ Number(b.horas_totales || 0).toFixed(1) }}h</div>
              <div style="font-size:10px; color:#475569">{{ b.fecha }}</div>
            </q-item-section>
            <q-item-section>
              <q-item-label style="font-size:13px; color:#e2e8f0">{{ b.origen_icao }} → {{ b.destino_icao }}</q-item-label>
              <q-item-label caption style="font-size:11px">
                {{ b.estudiante?.persona?.nombres }} · Dual {{ Number(b.horas_dual || 0).toFixed(1) }}h
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>
    </q-tab-panels>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const route     = useRoute()
const $q        = useQuasar()
const authStore = useAuthStore()
const id        = route.params.id

const aeronave         = ref(null)
const mantenimientos   = ref([])
const mel              = ref([])
const ads              = ref([])
const bitacorasTecnicas = ref([])
const horasData        = ref(null)
const cargando         = ref(false)
const tab              = ref('info')
const dialogMx         = ref(false)
const dialogMel        = ref(false)

const puedeRegistrarMx = computed(() => ['mantenimiento', 'dir_ops'].includes(authStore.rol))
const melAbiertos      = computed(() => mel.value.filter(m => m.estado === 'abierto').length)
const adsPendientes    = computed(() => ads.value.filter(a => a.estado === 'pendiente').length)
const horasRegistradas = computed(() => horasData.value?.horas_totales || 0)
const totalVuelos      = computed(() => horasData.value?.total_vuelos  || 0)

const estadoColor = computed(() => ({
  disponible: 'positive', mantenimiento: 'warning', baja: 'grey'
}[aeronave.value?.estado] || 'grey'))

const stats = computed(() => [
  { label: 'TTAE (Célula)', valor: Number(aeronave.value?.horas_celula_total || 0).toFixed(0) + 'h', color: '#3b82f6' },
  { label: 'Motor total',   valor: Number(aeronave.value?.horas_motor_total || 0).toFixed(0) + 'h',  color: '#14b8a6' },
  { label: 'SMOH',          valor: Number(aeronave.value?.horas_desde_oh || 0).toFixed(0) + 'h',     color: '#f59e0b' },
  { label: 'MEL abiertos',  valor: melAbiertos.value, color: melAbiertos.value > 0 ? '#f59e0b' : '#22c55e' },
])

const camposTecnicos = computed(() => [
  { label: 'Matrícula',    valor: aeronave.value?.matricula },
  { label: 'Modelo',       valor: aeronave.value?.modelo },
  { label: 'Fabricante',   valor: aeronave.value?.fabricante },
  { label: 'Año',          valor: aeronave.value?.anio },
  { label: 'Número serie', valor: aeronave.value?.serie },
  { label: 'Categoría',    valor: aeronave.value?.categoria },
  { label: 'Clase',        valor: aeronave.value?.clase },
])

const certsDocs = computed(() => [
  {
    label: 'Certificado de aeronavegabilidad',
    vencimiento: aeronave.value?.venc_airworthiness,
    vigente: aeronave.value?.airworthiness_vigente,
  },
  {
    label: 'Seguro aeronáutico',
    vencimiento: aeronave.value?.venc_seguro,
    vigente: dayjs(aeronave.value?.venc_seguro).isAfter(dayjs()),
  },
])

const esMelVencido = (m) => dayjs(m.fecha_limite).isBefore(dayjs())
const esAdVencido  = (a) => a.fecha_limite && dayjs(a.fecha_limite).isBefore(dayjs())

async function cumplirAd(ad) {
  try {
    await api.put(`/aeronaves/${id}/ads/${ad.id}`, {
      estado: 'cumplido', metodo_cumplimiento: 'Cumplido según manual de mantenimiento',
    })
    $q.notify({ type: 'positive', message: 'AD marcada como cumplida.' })
    cargar()
  } catch { $q.notify({ type: 'negative', message: 'Error al actualizar AD.' }) }
}

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
    aeronave.value         = aeroRes.data.data
    mantenimientos.value   = mxRes.data.data?.data || []
    mel.value              = melRes.data.data || []
    ads.value              = adsRes.data.data || []
    horasData.value        = horasRes.data.data
    bitacorasTecnicas.value = bitRes.data.data?.data || []
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>
