<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:120px">

    <!-- ══ Encabezado de Registro de Misión ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-btn flat round icon="arrow_back" color="red-9" class="q-mr-md shadow-24" @click="$router.push('/vuelo')" />
        <div>
          <div class="rac-page-subtitle">REPORTE TÉCNICO DE MISIÓN · RAC 61 / 91.417 · UAEAC</div>
          <h1 class="rac-page-title">Nueva Bitácora de Vuelo</h1>
        </div>
      </div>
    </div>

    <q-form ref="formRef" @submit.prevent="guardar" greedy>
      <div class="row q-col-gutter-xl">

        <!-- ════ COLUMNA ALFA: TRIPULACIÓN Y AERONAVE ════ -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-low shadow-24 welcome-hero overflow-hidden">
            <div class="hero-glow"></div>
            <div class="row items-center q-mb-xl relative-position pb-md border-bottom-border">
               <q-icon name="group" color="red-9" size="28px" class="q-mr-md glow-primary" />
               <div class="text-h6 font-head text-white uppercase tracking-tighter text-weight-bolder">Configuración de Tripulación</div>
            </div>

            <div class="q-gutter-y-lg relative-position">
              <!-- Alumno -->
              <div class="q-mb-md">
                <q-select 
                  v-model="form.estudiante_id" 
                  filled dark 
                  class="premium-input-login"
                  label="ALUMNO PILOTO EN COMANDO (PIC) *"
                  stack-label
                  :options="estudiantesOpc" 
                  emit-value map-options use-input 
                  :loading="cargandoEstudiantes" 
                  @filter="filtrarEstudiantes"
                  @update:model-value="onEstudianteChange"
                  :rules="[v => !!v || 'Requerido']"
                >
                  <template #prepend><q-icon name="person_pin" color="red-9" /></template>
                  <template #option="{ opt, itemProps }">
                    <q-item v-bind="itemProps" class="premium-glass-option q-mb-xs rounded-8">
                      <q-item-section>
                        <q-item-label class="text-weight-bolder text-white">{{ opt.label }}</q-item-label>
                        <q-item-label caption class="font-mono text-grey-6 uppercase" style="font-size:9px">DOC: {{ opt.expediente }} · {{ opt.programa }}</q-item-label>
                      </q-item-section>
                      <q-item-section side v-if="!opt.medico_ok"><q-icon name="emergency_home" color="red-9" class="pulsate" /></q-item-section>
                    </q-item>
                  </template>
                </q-select>
                <div v-if="estudianteSeleccionado && !estudianteSeleccionado.medico_ok" class="error-panel-sms q-pa-lg q-mt-md flex items-center shadow-24">
                   <q-icon name="report_problem" color="white" size="24px" class="q-mr-md pulsate" />
                   <div class="text-subtitle2 text-white font-mono text-weight-bolder">ALERTA RAC 67: CERTIFICADO MÉDICO NO VIGENTE</div>
                </div>
              </div>

              <!-- Instructor -->
              <div class="q-mb-md">
                <q-select 
                  v-model="form.instructor_id" 
                  filled dark
                  class="premium-input-login"
                  label="CAPITÁN INSTRUCTOR AL MANDO"
                  stack-label
                  :options="instructoresOpc" 
                  emit-value map-options clearable
                  :loading="cargandoInstructores"
                >
                  <template #prepend><q-icon name="badge" color="red-9" /></template>
                </q-select>
              </div>

              <!-- Aeronave -->
              <div>
                <q-select 
                  v-model="form.aeronave_id" 
                  filled dark
                  class="premium-input-login"
                  label="AERONAVE ASIGNADA (HK) *"
                  stack-label
                  :options="aeronavesOpc" 
                  emit-value map-options
                  :loading="cargandoAeronaves"
                  @update:model-value="onAeronaveChange"
                  :rules="[v => !!v || 'Requerido']"
                >
                  <template #prepend><q-icon name="flight_land" color="red-9" /></template>
                </q-select>
                
                <transition name="q-transition--scale">
                  <div v-if="aeronaveSeleccionada" class="q-mt-xl q-pa-xl premium-glass-card rounded-20 bg-black-20 shadow-inner border-red-low">
                     <div class="row q-col-gutter-xl text-center">
                        <div class="col-4">
                           <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">Block Total (H)</div>
                           <div class="text-h5 text-white text-weight-bolder font-mono line-height-1">{{ Number(aeronaveSeleccionada.horas_celula_total || 0).toFixed(1) }}</div>
                        </div>
                        <div class="col-4">
                           <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">Venc. Airw.</div>
                           <div class="text-h5 font-mono text-weight-bolder line-height-1" :class="diasAw > 30 ? 'text-emerald' : 'text-red-9'">{{ aeronaveSeleccionada.venc_airworthiness }}</div>
                        </div>
                        <div class="col-4">
                           <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">Status Mto</div>
                           <div class="text-h5 text-weight-bolder font-mono line-height-1" :class="aeronaveSeleccionada.estado === 'disponible' ? 'text-emerald' : 'text-red-9'">{{ aeronaveSeleccionada.estado?.toUpperCase() }}</div>
                        </div>
                     </div>
                  </div>
                </transition>
              </div>
            </div>
          </q-card>

          <!-- DATOS OPERATIVOS DEL ARRIBO -->
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low">
             <div class="row items-center q-mb-xl pb-md border-bottom-border">
                <q-icon name="av_timer" color="red-9" size="28px" class="q-mr-md" />
                <div class="text-h6 font-head text-white uppercase tracking-tighter text-weight-bolder">Cronograma de Misión</div>
             </div>
             
             <div class="row q-col-gutter-lg">
                <div class="col-12">
                   <q-input v-model="form.fecha" type="date" filled dark class="premium-input-login" label="FECHA MISION" stack-label :max="hoy">
                      <template #prepend><q-icon name="event" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-6">
                   <q-input v-model="form.hora_salida" type="time" filled dark class="premium-input-login" label="BLOCK OUT (UTC)" stack-label>
                      <template #prepend><q-icon name="schedule" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-6">
                   <q-input v-model="form.hora_llegada" type="time" filled dark class="premium-input-login" label="BLOCK IN (UTC)" stack-label>
                      <template #prepend><q-icon name="event_repeat" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-6">
                   <q-input v-model="form.origen_icao" filled dark class="premium-input-login" label="ORIGEN (ICAO)" @update:model-value="v => form.origen_icao = v.toUpperCase()" maxlength="4" placeholder="SKBO">
                      <template #prepend><q-icon name="flight_takeoff" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-6">
                   <q-input v-model="form.destino_icao" filled dark class="premium-input-login" label="DESTINO (ICAO)" @update:model-value="v => form.destino_icao = v.toUpperCase()" maxlength="4" placeholder="SKSM">
                      <template #prepend><q-icon name="flight_land" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-4">
                   <q-select v-model="form.tipo_vuelo" :options="['local','navegacion','noche','ifr']" filled dark class="premium-input-login" label="TIPO DE VUELO" stack-label />
                </div>
                <div class="col-4">
                   <q-input v-model.number="form.aterrizajes" type="number" filled dark class="premium-input-login" label="ATERRIZAJES" stack-label>
                      <template #prepend><q-icon name="flight_land" color="red-9" /></template>
                   </q-input>
                </div>
                <div class="col-4 flex items-center">
                   <q-toggle v-model="form.condiciones_vmc" color="red-9" label="CONDICIONES VMC" class="text-weight-bold font-mono" dark />
                </div>
             </div>
          </q-card>
        </div>

        <!-- ════ COLUMNA BRAVO: CONTABILIDAD RAC 61 ════ -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl q-mb-xl shadow-24 border-red-low">
             <div class="row items-center q-mb-xl pb-md border-bottom-border">
                <q-icon name="query_builder" color="red-9" size="28px" class="q-mr-md pulsate" />
                <div class="text-h6 font-head text-white uppercase tracking-tighter text-weight-bolder">Contabilidad de Horas RAC 61</div>
             </div>

             <!-- Monitor de Vuelo Total de Cristal -->
             <div class="total-flight-center q-mb-xl q-pa-xl text-center welcome-hero overflow-hidden shadow-24 border-red-low">
                <div class="hero-glow"></div>
                <div class="relative-position">
                   <div class="text-h1 text-weight-bolder text-white font-mono line-height-1 glow-red">{{ Number(form.horas_totales || 0).toFixed(1) }}</div>
                   <div class="text-subtitle1 text-red-9 font-mono text-weight-bolder uppercase tracking-widest q-mt-sm">TOTAL BLOCK TIME CERTIFICADO</div>
                </div>
             </div>

             <div class="row q-col-gutter-lg q-mb-xl">
                <div class="col-6" v-for="h in [
                  { m: 'horas_dual', l: 'INSTRUCCIÓN DUAL', c: 'red-9', i: 'school' },
                  { m: 'horas_solo', l: 'SOLO / PIC', c: 'grey-2', i: 'person' },
                  { m: 'horas_noche', l: 'NOCTURNO', c: 'indigo-9', i: 'nights_stay' },
                  { m: 'horas_ifr', l: 'INSTRUMENTOS', c: 'emerald', i: 'visibility_off' },
                  { m: 'horas_simulador', l: 'SIMULADOR / FSTD', c: 'orange-9', i: 'computer' }
                ]" :key="h.m">
                   <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">{{ h.l }}</div>
                   <q-input v-model.number="form[h.m]" type="number" step="0.1" filled dark class="premium-input-login" placeholder="0.0">
                      <template #prepend><q-icon :name="h.i" color="grey-7" size="18px" /></template>
                      <template #append><div class="text-caption font-mono text-weight-bold" :style="`color: var(--q-${h.c})`">H</div></template>
                   </q-input>
                </div>
             </div>

             <q-separator dark class="q-my-xl opacity-5" />

             <div>
                <q-input v-model="form.observaciones" type="textarea" filled dark label="OBSERVACIONES TÉCNICAS Y MANIOBRAS" class="premium-input-login" stack-label rows="6" placeholder="Documente las maniobras, aproximaciones y progreso del estudiante..." />
             </div>
          </q-card>

          <!-- PANEL DE ERRORES RAC (MASTER CAUTION) -->
          <transition name="q-transition--jump-up">
            <div v-if="erroresRac.length" class="master-caution-panel q-pa-xl q-mb-xl shadow-24 border-red-glow">
              <div class="text-h6 text-white font-head q-mb-lg flex items-center text-weight-bolder">
                <q-icon name="report" size="32px" class="q-mr-md pulsate" /> ALERTA DE CUMPLIMIENTO RAC
              </div>
              <div v-for="e in erroresRac" :key="e" class="text-subtitle2 text-white font-mono q-mb-sm flex items-center">
                <q-icon name="arrow_right" color="red-9" /> {{ e }}
              </div>
            </div>
          </transition>

          <!-- ACCIONES DE DESPACHO -->
          <div class="flex justify-between items-center q-pa-xl premium-glass-card border-red-low shadow-24">
             <q-btn flat color="grey-6" label="Abortar Registro" @click="$router.push('/vuelo')" class="font-mono text-weight-bolder" no-caps />
             <q-btn type="submit" color="red-9" label="Certificar y Cerrar Misión" icon="verified" class="premium-btn q-px-xl shadow-24 q-py-lg text-weight-bolder" :loading="guardando" />
          </div>
        </div>

      </div>
    </q-form>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const router  = useRouter()
const $q      = useQuasar()
const formRef = ref(null)

const guardando          = ref(false)
const cargandoEstudiantes = ref(false)
const cargandoInstructores = ref(false)
const cargandoAeronaves  = ref(false)
const erroresRac         = ref([])

const estudiantesOpc  = ref([])
const instructoresOpc = ref([])
const aeronavesOpc    = ref([])

const estudianteSeleccionado = ref(null)
const aeronaveSeleccionada   = ref(null)
const hoy = dayjs().format('YYYY-MM-DD')

const form = ref({
  estudiante_id:  null, instructor_id:  null, aeronave_id: null, fecha: hoy,
  hora_salida: '', hora_llegada: '', origen_icao: '', destino_icao: '',
  horas_totales: 0, horas_dual: 0, horas_solo: 0, horas_noche: 0, horas_ifr: 0, horas_simulador: 0,
  tipo_vuelo: 'local', condiciones_vmc: true, aterrizajes: 1, observaciones: '',
})

const diasAw = computed(() => aeronaveSeleccionada.value ? dayjs(aeronaveSeleccionada.value.venc_airworthiness).diff(dayjs(), 'day') : 999)

function onEstudianteChange(id) { estudianteSeleccionado.value = estudiantesOpc.value.find(e => e.value === id) || null }

watch([() => form.value.hora_salida, () => form.value.hora_llegada], ([s, l]) => {
  if (s && l) {
    const start = dayjs(`2000-01-01 ${s}`); const end = dayjs(`2000-01-01 ${l}`);
    let diff = end.diff(start, 'minute'); if (diff < 0) diff += 1440;
    form.value.horas_totales = Number((diff / 60).toFixed(1))
  }
})

function onAeronaveChange(id) { aeronaveSeleccionada.value = aeronavesOpc.value.find(a => a.value === id) || null }

async function filtrarEstudiantes(val, update) {
  cargandoEstudiantes.value = true
  try {
    const { data } = await api.get('/estudiantes', { params: { buscar: val, estado: 'activo', per_page: 20 } })
    const lista = data.data?.data || data.data || []
    update(() => {
      estudiantesOpc.value = lista.map(e => ({
        label: `${e.persona?.nombres} ${e.persona?.apellidos}`, value: e.id,
        expediente: e.num_expediente, programa: e.programa?.codigo, medico_ok: e.tiene_medico ?? true,
      }))
    })
  } finally { cargandoEstudiantes.value = false }
}

async function cargarInstructores() {
  cargandoInstructores.value = true; const { data } = await api.get('/instructores')
  instructoresOpc.value = (data.data?.data || data.data || []).map(i => ({ label: `${i.persona?.nombres} · ${i.num_licencia}`, value: i.id }))
  cargandoInstructores.value = false
}

async function cargarAeronaves() {
  cargandoAeronaves.value = true; const { data } = await api.get('/aeronaves')
  aeronavesOpc.value = (data.data || []).map(a => ({ label: a.matricula, value: a.id, modelo: a.modelo, estado: a.estado, horas_celula_total: a.horas_celula_total, venc_airworthiness: a.venc_airworthiness, mel_abiertos_count: a.mel_abiertos?.length || 0 }))
  cargandoAeronaves.value = false
}

async function guardar() {
  const valid = await formRef.value?.validate(); if (!valid) return;
  erroresRac.value = []
  if (estudianteSeleccionado.value && !estudianteSeleccionado.value.medico_ok) erroresRac.value.push('FALTA DE APTITUD MÉRITOS: Certificado Médico RAC 67 Vencido.')
  if (form.value.horas_dual + form.value.horas_solo > form.value.horas_totales + 0.05) erroresRac.value.push('DESBALANCE DE HORAS: Dual + Solo excede el Block Time total.')
  if (aeronaveSeleccionada.value && (diasAw.value <= 0 || aeronaveSeleccionada.value.estado !== 'disponible')) erroresRac.value.push('RESTRICCIÓN TÉCNICA: Aeronave NO DISPONIBLE o con Certificado Vencido.')
  if (erroresRac.value.length) { $q.notify({ color: 'negative', icon: 'report', message: 'No se puede cerrar la bitácora debido a errores de cumplimiento RAC.' }); return; }

  guardando.value = true
  try {
    await api.post('/bitacoras', { ...form.value })
    $q.notify({ color: 'emerald', icon: 'flight_land', message: '¡MISIÓN CERTIFICADA EXITOSAMENTE! Sincronizada con base de datos técnica.', timeout: 5000 })
    router.push('/vuelo')
  } catch (e) { $q.notify({ color: 'negative', message: 'Error de sincronización con el servidor de la UAEAC.' }) }
  finally { guardando.value = false }
}

onMounted(() => { cargarInstructores(); cargarAeronaves() })
</script>

<style lang="scss" scoped>

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.15) 0%, transparent 50%); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.total-flight-center { border-radius: 20px; background: rgba(0,0,0,0.3); border-top: 4px solid #A10B13; }
.glow-red { text-shadow: 0 0 30px rgba(161, 11, 19, 0.6); }

.error-panel-sms { background: #A10B13; border-radius: 12px; border: 1px solid rgba(255,255,255,0.2); }
.master-caution-panel { background: rgba(161, 11, 19, 0.12); border-radius: 20px; }

.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }

</style>
