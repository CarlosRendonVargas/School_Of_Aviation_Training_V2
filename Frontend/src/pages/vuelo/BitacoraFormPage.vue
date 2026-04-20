<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Certificación ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-btn flat round dense icon="arrow_back" to="/vuelo" color="red-9" class="q-mr-md shadow-24" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">CERTIFICACIÓN DE MISIÓN · RAC 91.417 / 141.71</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Nueva Bitácora de Vuelo</h1>
        </div>
      </div>
    </div>

    <!-- ══ Formulario de Misión ══ -->
    <q-form @submit.prevent="guardar" greedy>
      <div class="row q-col-gutter-xl">

        <!-- ── Columna 1: Parámetros de la Operación ─────────────────────────────── -->
        <div class="col-12 col-lg-7">
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low h-full flex column">
            <div class="text-h6 text-white font-head text-weight-bolder q-mb-xl flex items-center border-bottom-border pb-md">
               <q-icon name="airplane_ticket" color="red-9" size="24px" class="q-mr-md shadow-inner" />
               PARÁMETROS DE LA MISIÓN
            </div>

            <div class="q-gutter-y-lg col">
               <div class="row q-col-gutter-lg">
                  <div class="col-12">
                     <q-select
                        v-model="form.estudiante_id"
                        :options="estudiantesOpts"
                        option-value="value" option-label="label"
                        emit-value map-options
                        label="ALUMNO EN MANIOBRA *"
                        filled dark class="premium-input-login"
                        use-input input-debounce="300"
                        @filter="filtrarEstudiantes"
                        :rules="[v => !!v || 'Requerido']"
                     >
                        <template #prepend><q-icon name="person" color="red-9" /></template>
                     </q-select>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-select
                        v-model="form.aeronave_id"
                        :options="aeronavesOpts"
                        option-value="value" option-label="label"
                        emit-value map-options
                        label="AERONAVE (HK) *"
                        filled dark class="premium-input-login"
                        :rules="[v => !!v || 'Requerido']"
                     >
                        <template #prepend><q-icon name="flight" color="red-9" /></template>
                     </q-select>
                  </div>
                  <div class="col-12 col-md-6">
                     <q-input v-model="form.fecha" label="FECHA OPERACIÓN *" type="date" filled dark class="premium-input-login" :max="hoy" :rules="[v => !!v || 'Requerido']" stack-label />
                  </div>

                  <div class="col-6">
                     <q-input v-model="form.hora_salida" label="SALIDA (UTC) *" type="time" filled dark class="premium-input-login" :rules="[v => !!v || 'Req']" stack-label>
                        <template #prepend><q-icon name="schedule" color="red-9" /></template>
                     </q-input>
                  </div>
                  <div class="col-6">
                     <q-input v-model="form.hora_llegada" label="LLEGADA (UTC) *" type="time" filled dark class="premium-input-login" :rules="[v => !!v || 'Req']" stack-label>
                        <template #prepend><q-icon name="event_seat" color="red-9" /></template>
                     </q-input>
                  </div>

                  <div class="col-6">
                     <q-input v-model="form.origen_icao" label="ORIGEN ICAO *" maxlength="4" filled dark class="premium-input-login" style="text-transform:uppercase" :rules="[v => v?.length === 4 || '4 CHARS']">
                        <template #prepend><q-icon name="flight_takeoff" color="red-9" /></template>
                     </q-input>
                  </div>
                  <div class="col-6">
                     <q-input v-model="form.destino_icao" label="DESTINO ICAO *" maxlength="4" filled dark class="premium-input-login" style="text-transform:uppercase" :rules="[v => v?.length === 4 || '4 CHARS']">
                        <template #prepend><q-icon name="flight_land" color="red-9" /></template>
                     </q-input>
                  </div>

                  <div class="col-12">
                     <q-select
                        v-model="form.tipo_vuelo"
                        :options="tiposVuelo"
                        option-value="value" option-label="label"
                        emit-value map-options
                        label="TIPO DE MISIÓN *"
                        filled dark class="premium-input-login"
                     >
                        <template #prepend><q-icon name="stars" color="red-9" /></template>
                     </q-select>
                  </div>

                  <div class="col-12 flex items-center justify-between q-pa-md border-red-low shadow-inner rounded-12">
                     <q-toggle v-model="form.condiciones_vmc" label="CONDICIONES VMC" color="red-9" class="font-mono text-weight-bolder text-white" />
                     <div class="row items-center q-gutter-md">
                        <span class="font-mono text-grey-6 uppercase" style="font-size:10px">ATERRIZAJES (ATZ)</span>
                        <q-input v-model.number="form.aterrizajes" type="number" min="0" borderless dark class="font-mono text-h6 text-white" style="width: 50px" input-class="text-center" />
                     </div>
                  </div>
               </div>
            </div>
          </q-card>
        </div>

        <!-- ── Columna 2: Certificación de Horas RAC ────────────────────────────────── -->
        <div class="col-12 col-lg-5">
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low h-full">
            <div class="text-h6 text-white font-head text-weight-bolder q-mb-xl flex items-center border-bottom-border pb-md">
               <q-icon name="verified" color="red-9" size="24px" class="q-mr-md shadow-inner" />
               DESGLOSE DE HORAS RAC 61
            </div>

            <div class="total-flight-display q-mb-xl welcome-hero overflow-hidden shadow-24">
               <div class="hero-glow"></div>
               <div class="text-center relative-position">
                  <div class="text-h1 font-mono text-weight-bolder text-red-9 line-height-1 glow-red">{{ (Number(form.horas_totales) || 0).toFixed(1) }}</div>
                  <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-md">Total Block Time Certificado</div>
               </div>
            </div>

            <div class="q-gutter-y-md">
               <div class="row q-col-gutter-md">
                  <div class="col-12">
                     <q-input v-model.number="form.horas_totales" label="HORAS TOTALES *" type="number" step="0.1" min="0" filled dark class="premium-input-login" :rules="[v => v > 0 || 'Requerido']" />
                  </div>
                  <div class="col-6"><q-input v-model.number="form.horas_dual" label="HORAS DUAL" type="number" step="0.1" min="0" filled dark class="premium-input-login" /></div>
                  <div class="col-6"><q-input v-model.number="form.horas_solo" label="HORAS SOLO" type="number" step="0.1" min="0" filled dark class="premium-input-login" /></div>
                  <div class="col-6"><q-input v-model.number="form.horas_noche" label="NOCTURNAS" type="number" step="0.1" min="0" filled dark class="premium-input-login" /></div>
                  <div class="col-6"><q-input v-model.number="form.horas_ifr" label="IFR / INST" type="number" step="0.1" min="0" filled dark class="premium-input-login" /></div>
                  <div class="col-12"><q-input v-model.number="form.horas_simulador" label="SIMULADOR" type="number" step="0.1" min="0" filled dark class="premium-input-login" /></div>
               </div>

               <!-- Alerta Táctica de Discrepancia -->
               <div v-if="errorHoras" class="critical-alert-box q-pa-lg flex items-center shadow-24 animate-pulse">
                  <q-icon name="report_problem" size="24px" color="white" class="q-mr-md" />
                  <div class="text-white font-mono text-weight-bolder uppercase" style="font-size:12px">{{ errorHoras }}</div>
               </div>

               <div class="text-subtitle2 text-grey-6 font-head q-mt-xl uppercase tracking-widest">Observaciones de Instrucción</div>
               <q-input
                  v-model="form.observaciones"
                  type="textarea"
                  placeholder="Describa el progreso del cadete y maniobras destacadas..."
                  filled dark class="premium-input-login"
                  rows="4"
               />
            </div>
          </q-card>
        </div>

      </div>

      <!-- Botones de Acción de Lujo -->
      <div class="flex justify-between items-center q-mt-xl q-pa-xl premium-glass-card shadow-24 border-red-low">
        <q-btn flat label="Abortar Registro" to="/vuelo" color="grey-7" class="q-px-xl font-mono uppercase" />
        <q-btn
          type="submit"
          label="Certificar Bitácora en Servidor"
          icon="verified"
          color="red-10"
          class="premium-btn shadow-24 q-px-xl q-py-lg text-weight-bolder"
          :loading="guardando"
          :disable="!!errorHoras"
        />
      </div>

    </q-form>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const router   = useRouter()
const $q       = useQuasar()
const guardando = ref(false)
const hoy = new Date().toISOString().split('T')[0]

const form = ref({
  estudiante_id: null; instructor_id: null; aeronave_id: null; reserva_id: null; fecha: hoy;
  hora_salida: ''; hora_llegada: ''; origen_icao: ''; destino_icao: '';
  horas_totales: 0; horas_dual: 0; horas_solo: 0; horas_noche: 0; horas_ifr: 0; horas_simulador: 0;
  tipo_vuelo: 'local'; condiciones_vmc: true; aterrizajes: 1; observaciones: '';
})

const tiposVuelo = [
  { value: 'local',      label: 'Local' },
  { value: 'navegacion', label: 'Navegación' },
  { value: 'noche',      label: 'Nocturno' },
  { value: 'ifr',        label: 'IFR / Instrumentos' },
  { value: 'sim',        label: 'Simulador' },
]

const estudiantesOpts = ref([])
const aeronavesOpts   = ref([])
const todosEstudiantes = ref([])

const errorHoras = computed(() => {
  const p = (Number(form.value.horas_dual) || 0) + (Number(form.value.horas_solo) || 0)
  if (p > (Number(form.value.horas_totales) || 0) + 0.05) {
    return `DISCREPANCIA: DUAL (${form.value.horas_dual}H) + SOLO (${form.value.horas_solo}H) EXCEDE TOTAL (${form.value.horas_totales}H)`
  }
  return null
})

function filtrarEstudiantes(val, update) {
  update(() => {
    const q = val.toLowerCase()
    estudiantesOpts.value = todosEstudiantes.value.filter(e => e.label.toLowerCase().includes(q))
  })
}

async function guardar() {
  if (errorHoras.value) return
  guardando.value = true
  try {
    const payload = { ...form.value, origen_icao: form.value.origen_icao.toUpperCase(), destino_icao: form.value.destino_icao.toUpperCase() }
    await api.post('/bitacoras', payload)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Bitácora certificada exitosamente según RAC 91.417.' })
    router.push('/bitacoras')
  } catch (err) {
    const msg = err.response?.data?.mensaje || 'Error al certificar la bitácora.'
    $q.notify({ color: 'negative', message: msg })
  } finally { guardando.value = false }
}

onMounted(async () => {
  try {
    const [estRes, aerRes] = await Promise.all([
      api.get('/estudiantes?estado=activo&per_page=100'),
      api.get('/aeronaves?estado=disponible'),
    ])
    todosEstudiantes.value = (estRes.data.data?.data || estRes.data.data || estRes.data).map(e => ({
      value: e.id, label: `${e.persona?.nombres} ${e.persona?.apellidos} — [${e.num_expediente}]`,
    }))
    estudiantesOpts.value = [...todosEstudiantes.value]
    aeronavesOpts.value = (aerRes.data.data || aerRes.data).map(a => ({
      value: a.id, label: `${a.matricula} · ${a.modelo}`,
    }))
  } catch (e) {
    console.error(e)
  }
})
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-bottom-border { border-bottom: 1px solid rgba(255,255,255,0.05); }
.line-height-1 { line-height: 1.1; }
.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.glow-red { text-shadow: 0 0 20px rgba(161, 11, 19, 0.6); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.total-flight-display {
  background: rgba(161,11,19,0.05); border: 1px solid rgba(161,11,19,0.2);
  border-radius: 20px; padding: 40px; border-top: 4px solid #A10B13;
}

.critical-alert-box {
  background: #A10B13; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);
}

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); }

.animate-pulse { animation: pulseAlert 1.5s infinite; }
@keyframes pulseAlert { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.9; transform: scale(0.99); } }
</style>
