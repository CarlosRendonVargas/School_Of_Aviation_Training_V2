<template>
  <q-page padding style="padding-bottom:80px">

    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 43 · RAC 91 · RAC 141.51
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Aeronaves</div>
      </div>
      <q-btn v-if="authStore.esDirOps" unelevated color="primary" icon="add"
        label="Registrar aeronave" no-caps @click="dialogNueva=true" style="border-radius:8px" />
    </div>

    <!-- Filtros rápidos -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-auto">
        <q-btn-group flat>
          <q-btn flat no-caps :color="filtroEstado===null?'primary':'grey-6'"
            @click="filtroEstado=null" label="Todas" style="border-radius:6px 0 0 6px" />
          <q-btn flat no-caps :color="filtroEstado==='disponible'?'primary':'grey-6'"
            @click="filtroEstado='disponible'" label="Disponibles" />
          <q-btn flat no-caps :color="filtroEstado==='mantenimiento'?'orange':'grey-6'"
            @click="filtroEstado='mantenimiento'" label="En MX" style="border-radius:0 6px 6px 0" />
        </q-btn-group>
      </div>
    </div>

    <!-- Grid de aeronaves -->
    <q-skeleton v-if="cargando" v-for="n in 3" :key="n" type="rect" height="180px" dark class="q-mb-md" />

    <div v-else class="row q-col-gutter-md">
      <div class="col-12 col-md-6 col-lg-4"
        v-for="av in aeronavesFiltradas" :key="av.id">
        <q-card flat class="card-rac cursor-pointer" style="background:#0f1218;position:relative;overflow:hidden"
          @click="$router.push(`/aeronaves/${av.id}`)">

          <!-- Banda de estado -->
          <div style="height:3px;width:100%" :style="`background:${colorBandaEstado(av.estado)}`" />

          <q-card-section>
            <div class="row items-start justify-between q-mb-md">
              <div>
                <div class="font-mono text-white" style="font-size:20px;font-weight:700;letter-spacing:1px">
                  {{ av.matricula }}
                </div>
                <div style="font-size:13px;color:#94a3b8;margin-top:2px">{{ av.modelo }}</div>
              </div>
              <div class="column items-end q-gutter-xs">
                <q-chip dense :color="colorChipEstado(av.estado)" text-color="white"
                  :label="av.estado.toUpperCase()" style="font-size:10px;font-family:monospace" />
                <q-chip dense :color="av.clase==='multimotor'?'purple-9':'blue-9'" text-color="white"
                  :label="av.clase" style="font-size:9px;font-family:monospace" />
              </div>
            </div>

            <!-- Horas -->
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-4 text-center" style="background:rgba(255,255,255,.03);border-radius:6px;padding:8px">
                <div class="font-mono text-primary" style="font-size:14px;font-weight:700">
                  {{ Number(av.horas_celula_total || 0).toFixed(0) }}
                </div>
                <div style="font-size:9px;color:#475569;letter-spacing:.5px">TTAE</div>
              </div>
              <div class="col-4 text-center" style="background:rgba(255,255,255,.03);border-radius:6px;padding:8px">
                <div class="font-mono text-teal" style="font-size:14px;font-weight:700">
                  {{ Number(av.horas_desde_oh || 0).toFixed(0) }}
                </div>
                <div style="font-size:9px;color:#475569;letter-spacing:.5px">SMOH</div>
              </div>
              <div class="col-4 text-center" style="background:rgba(255,255,255,.03);border-radius:6px;padding:8px">
                <div class="font-mono" style="font-size:14px;font-weight:700"
                  :style="`color:${colorDiasVenc(diasAirworthiness(av))}`">
                  {{ diasAirworthiness(av) }}d
                </div>
                <div style="font-size:9px;color:#475569;letter-spacing:.5px">AW VENCE</div>
              </div>
            </div>

            <!-- Alertas MEL/ADs -->
            <div class="row q-col-gutter-xs">
              <div v-if="av.mel_abiertos_count > 0" class="col-auto">
                <q-chip dense color="orange" text-color="white" icon="warning"
                  :label="`${av.mel_abiertos_count} MEL`" style="font-size:10px" />
              </div>
              <div v-if="av.ads_pendientes_count > 0" class="col-auto">
                <q-chip dense color="negative" text-color="white" icon="error"
                  :label="`${av.ads_pendientes_count} AD`" style="font-size:10px" />
              </div>
              <div v-if="!av.mel_abiertos_count && !av.ads_pendientes_count" class="col-auto">
                <q-chip dense color="positive" text-color="white" icon="check"
                  label="Sin alertas MX" style="font-size:10px" />
              </div>
              <!-- Seguro -->
              <div class="col-auto">
                <q-chip dense :color="diasSeguro(av) < 30 ? 'negative' : 'grey-8'"
                  text-color="white" icon="shield"
                  :label="`Seguro ${diasSeguro(av)}d`" style="font-size:10px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="!aeronavesFiltradas.length" class="col-12 text-center q-py-xl" style="color:#475569">
        <q-icon name="flight_takeoff" size="48px" class="q-mb-sm" /><br>
        Sin aeronaves con ese filtro
      </div>
    </div>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const authStore = useAuthStore()
const aeronaves = ref([])
const cargando  = ref(false)
const filtroEstado = ref(null)
const dialogNueva  = ref(false)

const aeronavesFiltradas = computed(() =>
  filtroEstado.value
    ? aeronaves.value.filter(a => a.estado === filtroEstado.value)
    : aeronaves.value
)

const diasAirworthiness = (av) => dayjs(av.venc_airworthiness).diff(dayjs(), 'day')
const diasSeguro        = (av) => dayjs(av.venc_seguro).diff(dayjs(), 'day')

const colorDiasVenc = (dias) => {
  if (dias <= 0)  return '#ef4444'
  if (dias <= 30) return '#ef4444'
  if (dias <= 60) return '#f59e0b'
  return '#22c55e'
}

const colorBandaEstado = (e) => ({
  disponible: '#22c55e', mantenimiento: '#f97316', baja: '#64748b',
}[e] || '#64748b')

const colorChipEstado = (e) => ({
  disponible: 'positive', mantenimiento: 'orange', baja: 'grey-7',
}[e] || 'grey')

const colorChipClase = (c) => c === 'multimotor' ? 'purple' : 'primary'

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/aeronaves')
    // Calcular conteos de MEL/ADs
    aeronaves.value = (data.data || []).map(av => ({
      ...av,
      mel_abiertos_count:  av.mel_abiertos?.length || 0,
      ads_pendientes_count: av.ads_pendientes?.length || 0,
    }))
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>
