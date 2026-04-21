<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Despacho Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="schedule_send" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">GESTIÓN DE SLOTS Y DESPACHO · RAC 141.201</div>
          <h1 class="rac-page-title">Despacho de Reservas</h1>
        </div>
      </div>
      <div class="row q-gutter-md">
        <q-btn flat color="grey-6" icon="calendar_month" label="Consola de Calendario" @click="$router.push('/calendario')" class="font-mono text-weight-bolder uppercase" />
        <q-btn color="red-9" icon="add" label="Programar Nueva Misión" class="premium-btn shadow-24 q-px-xl q-py-md" @click="dialogNueva=true" />
      </div>
    </div>

    <!-- ══ Barra de Filtros Táctica de Cristal ══ -->
    <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-low shadow-24 welcome-hero overflow-hidden">
      <div class="hero-glow"></div>
      <div class="row q-col-gutter-xl items-end relative-position">
        <div class="col-12 col-md-3">
          <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Fecha de Operación</div>
          <q-input v-model="filtros.fecha" type="date" filled dark class="premium-input-login" @update:model-value="cargar" stack-label />
        </div>
        <div class="col-12 col-md-3">
          <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Estado de la Reserva</div>
          <q-select v-model="filtros.estado" :options="opcionesEstado" emit-value map-options clearable filled dark class="premium-input-login" @update:model-value="cargar" stack-label />
        </div>
        <div class="col-12 col-md-3">
          <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Tipo de Instrucción</div>
          <q-select v-model="filtros.tipo" :options="opcionesTipo" emit-value map-options clearable filled dark class="premium-input-login" @update:model-value="cargar" stack-label />
        </div>
        <div class="col-12 col-md-3 flex justify-end q-gutter-sm">
           <q-btn flat round icon="today" color="red-9" @click="filtros.fecha=hoy; cargar()" class="shadow-inner" />
           <q-btn flat round icon="filter_alt_off" color="grey-7" @click="limpiarFiltros" class="shadow-inner" />
        </div>
      </div>
    </q-card>

    <!-- ══ Monitor de Salidas (Lista de Slots) ══ -->
    <div class="q-gutter-y-lg">
      <div v-if="cargando" class="row justify-center q-pa-xl"><q-spinner-dots color="red-9" size="48px" /></div>
      
      <div v-else-if="!reservas.length" class="text-center q-pa-xl premium-glass-card border-red-low shadow-24 rounded-20">
          <q-icon name="event_busy" size="64px" color="grey-8" class="q-mb-md opacity-20" />
          <div class="text-h6 text-grey-7 font-head uppercase tracking-widest">No se identifican misiones agendadas para el periodo.</div>
      </div>

      <div v-for="(r, idx) in reservas" :key="r.id" 
        class="premium-glass-card q-pa-xl flex items-center no-wrap hover-row border-red-left shadow-24 animate-slide-up"
        :style="`animation-delay: ${idx * 0.05}s`"
        @click="verDetalle(r)"
      >
        <!-- Luxury Time Slot -->
        <div class="time-slot-premium q-mr-xl text-center shadow-24 border-red-low flex flex-center column">
           <div class="text-h5 text-weight-bolder text-white font-mono line-height-1">{{ r.hora_inicio }}</div>
           <q-separator dark class="q-my-xs full-width opacity-10" />
           <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:9px">Block Out</div>
        </div>

        <div class="col">
           <div class="row items-center q-gutter-x-lg">
              <div class="text-h5 text-white font-head text-weight-bolder tracking-tighter">{{ r.aeronave?.matricula }}</div>
              <div class="q-px-md q-py-xs bg-black-20 rounded-8 border-red-low text-caption text-grey-5 font-mono uppercase tracking-widest" style="font-size:10px">{{ r.aeronave?.modelo }}</div>
           </div>
           <div class="row items-center q-mt-md q-gutter-x-md">
              <q-avatar size="32px" class="shadow-24 border-red-low">
                <img :src="`https://ui-avatars.com/api/?name=${r.estudiante?.persona?.nombres}&background=A10B13&color=fff`" />
              </q-avatar>
              <div>
                <div class="text-subtitle1 text-white font-head text-weight-bold line-height-1">Alu. {{ r.estudiante?.persona?.nombres }} {{ r.estudiante?.persona?.apellidos }}</div>
                <div v-if="r.instructor" class="text-caption text-red-9 font-mono text-weight-bolder uppercase tracking-widest q-mt-xs" style="font-size:9px">Cpt. {{ r.instructor?.persona?.nombres }} {{ r.instructor?.persona?.apellidos }}</div>
                <div v-else class="text-caption text-emerald font-mono text-weight-bolder uppercase tracking-widest q-mt-xs" style="font-size:9px">MISIÓN SOLO CERTIFICADA</div>
              </div>
           </div>
        </div>

        <div class="side-badges-premium flex column items-end q-gutter-y-md">
           <q-badge :color="colorEstadoReserva(r.estado)" :label="r.estado?.toUpperCase()" class="font-mono text-weight-bolder q-px-xl q-py-sm shadow-24 rounded-12" />
           <div class="flex items-center">
              <q-icon :name="r.tipo === 'instruccion' ? 'school' : 'person'" color="grey-6" size="14px" class="q-mr-sm" />
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:10px">{{ r.tipo }}</div>
           </div>
        </div>
      </div>
    </div>

    <!-- ════ DIÁLOGO: NUEVA PROGRAMACIÓN OPERATIVA ════ -->
    <q-dialog v-model="dialogNueva" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(550px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon name="add_task" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Nueva Misión Académica</div>
           </div>
           <q-btn flat round dense icon="close" @click="cerrarDialog" color="grey-7" class="shadow-inner" />
        </div>

        <q-form @submit.prevent="crearReserva" class="q-gutter-y-lg">
           <q-input v-model="form.fecha" type="date" filled dark label="FECHA PROGRAMADA" class="premium-input-login" stack-label>
              <template #prepend><q-icon name="event" color="red-9" /></template>
           </q-input>
           
           <div class="row q-col-gutter-lg">
              <div class="col-6">
                 <q-input v-model="form.hora_inicio" type="time" filled dark label="HORA INICIO (UTC)" class="premium-input-login" stack-label>
                    <template #prepend><q-icon name="schedule" color="red-9" /></template>
                 </q-input>
              </div>
              <div class="col-6">
                 <q-input v-model="form.hora_fin" type="time" filled dark label="HORA FINAL (UTC)" class="premium-input-login" stack-label>
                    <template #prepend><q-icon name="event_repeat" color="red-9" /></template>
                 </q-input>
              </div>
           </div>

           <q-select 
              v-model="form.aeronave_id" 
              :options="opcionesAeronaves" 
              filled dark label="SELECCIONAR AERONAVE DISPONIBLE" 
              class="premium-input-login" 
              emit-value map-options stack-label 
           >
              <template #prepend><q-icon name="flight" color="red-9" /></template>
           </q-select>

           <q-select 
              v-model="form.tipo" 
              :options="opcionesTipo" 
              filled dark label="MODALIDAD DE ENTRENAMIENTO" 
              class="premium-input-login" 
              emit-value map-options stack-label 
           >
              <template #prepend><q-icon name="stars" color="red-9" /></template>
           </q-select>

           <transition name="q-transition--scale">
             <div v-if="erroresRac.length" class="q-pa-xl bg-black-20 rounded-12 border-red-low shadow-inner">
                <div class="text-caption text-red-9 font-mono text-weight-bolder uppercase q-mb-md">Restricciones de Despacho Encontradas:</div>
                <div v-for="(e,i) in erroresRac" :key="i" class="text-caption text-white font-mono q-mb-xs">▶ {{ e }}</div>
             </div>
           </transition>

           <q-btn type="submit" color="red-10" label="Confirmar y Agendar en Registro" icon="event_available" class="full-width premium-btn q-py-lg shadow-24 text-weight-bolder" :loading="creando" />
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const $q        = useQuasar()
const authStore = useAuthStore()

const reservas    = ref([])
const aeronaves   = ref([])
const cargando    = ref(false)
const dialogNueva = ref(false)
const creando     = ref(false)
const erroresRac  = ref([])

const hoy = dayjs().format('YYYY-MM-DD')
const filtros = ref({ fecha: hoy, estado: null, tipo: null })
const form = ref({ fecha: hoy, hora_inicio: '', hora_fin: '', aeronave_id: null, tipo: 'instruccion' })

const opcionesEstado = [
  { label: 'PENDIENTE',  value: 'pendiente' },
  { label: 'CONFIRMADA', value: 'confirmada' },
  { label: 'COMPLETADA', value: 'completada' },
  { label: 'CANCELADA',  value: 'cancelada' },
]
const opcionesTipo = [
  { label: 'INSTRUCCIÓN DUAL', value: 'instruccion' },
  { label: 'SOLO / PIC',        value: 'solo' },
  { label: 'SIMULADOR',         value: 'simulador' },
]

const opcionesAeronaves = computed(() => aeronaves.value.filter(a => a.estado === 'disponible').map(a => ({ value: a.id, label: `${a.matricula} · ${a.modelo}` })))
const colorEstadoReserva = (e) => ({ pendiente:'warning', confirmada:'emerald', completada:'blue-10', cancelada:'red-10' }[e]||'grey-8')

function verDetalle(r) { /* Detalle Dialog placeholder */ }

function limpiarFiltros() { filtros.value = { fecha: '', estado: null, tipo: null }; cargar() }

function cerrarDialog() { dialogNueva.value = false; erroresRac.value = []; form.value = { fecha: hoy, hora_inicio: '', hora_fin: '', aeronave_id: null, tipo: 'instruccion' } }

async function cargar() {
  cargando.value = true
  try {
    const params = { ...filtros.value }
    const { data } = await api.get('/reservas', { params })
    reservas.value = data.data.data || data.data || []
  } catch { /**/ } finally { cargando.value = false }
}

async function cargarAeronaves() {
  const { data } = await api.get('/aeronaves', { params: { estado: 'disponible' } })
  aeronaves.value = data.data || []
}

async function crearReserva() {
  erroresRac.value = []
  creando.value = true
  try {
    const payload = { ...form.value, estudiante_id: authStore.esEstudiante ? null : form.value.estudiante_id }
    await api.post('/reservas', payload)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Misión agendada exitosamente en el despacho operativo.' })
    cerrarDialog(); cargar()
  } catch (e) {
    if (e.response?.status === 422) erroresRac.value = e.response.data.errores || ['Error de validación UAEAC.']
  } finally { creando.value = false }
}

onMounted(() => { cargar(); cargarAeronaves() })
</script>

<style lang="scss" scoped>

.animate-slide-up { animation: slideUp 0.5s cubic-bezier(0.23, 1, 0.32, 1) both; }

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.bg-black-20 { background: rgba(0,0,0,0.2); }

.hover-row { 
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;
  &:hover { background: rgba(255,255,255,0.03); transform: scale(1.008); border-color: #A10B13 !important; } 
}

.time-slot-premium { 
  width: 100px; height: 100px; border-radius: 20px; background: rgba(0,0,0,0.3); border-top: 2px solid #A10B13;
}

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}
</style>
