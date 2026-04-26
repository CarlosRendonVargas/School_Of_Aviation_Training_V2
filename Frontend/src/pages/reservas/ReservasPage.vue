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
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(600px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon name="add_task" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Nueva Misión Académica</div>
           </div>
           <q-btn flat round dense icon="close" @click="cerrarDialog" color="grey-7" class="shadow-inner" />
        </div>

        <q-form @submit.prevent="crearReserva" class="q-gutter-y-lg">
           <div class="row q-col-gutter-lg">
             <div class="col-12">
               <q-select v-model="form.estudiante_id" :options="estudiantesOpc"
                 label="ALUMNO PILOTO (PIC) *" filled dark class="premium-input-login"
                 emit-value map-options use-input @filter="filtrarEstudiantes" stack-label
                 :rules="[v=>!!v||'Requerido']">
                 <template #prepend><q-icon name="person" color="red-9" /></template>
               </q-select>
             </div>
             <div class="col-12">
               <q-select v-model="form.instructor_id" :options="instructoresOpc"
                 label="INSTRUCTOR AL MANDO" filled dark class="premium-input-login"
                 emit-value map-options clearable stack-label>
                 <template #prepend><q-icon name="badge" color="red-9" /></template>
               </q-select>
             </div>
           </div>

           <q-input v-model="form.fecha" type="date" filled dark label="FECHA PROGRAMADA" class="premium-input-login" stack-label>
              <template #prepend><q-icon name="event" color="red-9" /></template>
           </q-input>
           
           <div class="row q-col-gutter-lg">
              <div class="col-6">
                 <q-input v-model="form.hora_inicio" filled dark label="HORA INICIO (ZULU)" class="premium-input-login" stack-label readonly>
                    <template #prepend><q-icon name="schedule" color="red-9" /></template>
                    <template #append>
                       <q-icon name="access_time" class="cursor-pointer" color="grey-5">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                             <q-time v-model="form.hora_inicio" format24h mask="HH:mm" color="red-9" dark>
                                <div class="row items-center justify-between q-pa-sm">
                                   <span class="text-grey-5 font-mono text-caption">UTC / ZULU</span>
                                   <q-btn v-close-popup label="Confirmar" color="red-9" flat dense />
                                </div>
                             </q-time>
                          </q-popup-proxy>
                       </q-icon>
                    </template>
                 </q-input>
              </div>
              <div class="col-6">
                 <q-input v-model="form.hora_fin" filled dark label="HORA FINAL (ZULU)" class="premium-input-login" stack-label readonly>
                    <template #prepend><q-icon name="event_repeat" color="red-9" /></template>
                    <template #append>
                       <q-icon name="access_time" class="cursor-pointer" color="grey-5">
                          <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                             <q-time v-model="form.hora_fin" format24h mask="HH:mm" color="red-9" dark>
                                <div class="row items-center justify-between q-pa-sm">
                                   <span class="text-grey-5 font-mono text-caption">UTC / ZULU</span>
                                   <q-btn v-close-popup label="Confirmar" color="red-9" flat dense />
                                </div>
                             </q-time>
                          </q-popup-proxy>
                       </q-icon>
                    </template>
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
              <template #append>
                <q-btn flat round dense icon="add_circle_outline" color="red-9" size="sm" @click.stop="agregarTipo">
                  <q-tooltip>Agregar modalidad</q-tooltip>
                </q-btn>
              </template>
              <template #option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section>
                    <q-item-label>{{ scope.opt.label }}</q-item-label>
                  </q-item-section>
                  <q-item-section v-if="!TIPOS_DEFECTO.includes(scope.opt.value)" side>
                    <q-btn flat round dense icon="close" size="xs" color="red-9" @click.stop="eliminarTipo(scope.opt.value)">
                      <q-tooltip>Eliminar</q-tooltip>
                    </q-btn>
                  </q-item-section>
                </q-item>
              </template>
           </q-select>

           <q-input
              v-model="form.objetivos"
              type="textarea"
              rows="3"
              filled dark
              label="OBJETIVOS DE LA ACTIVIDAD (opcional)"
              placeholder="Ej: El estudiante aprenderá procedimientos de despegue y aterrizaje..."
              class="premium-input-login"
              stack-label
           >
              <template #prepend><q-icon name="flag" color="red-9" /></template>
           </q-input>

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

    <!-- ════ DIÁLOGO: DETALLE DE RESERVA / BRIEFING ════ -->
    <q-dialog v-model="dialogDetalle" backdrop-filter="blur(15px)">
      <q-card v-if="reservaSel" class="premium-glass-card q-pa-md q-pa-sm-xl shadow-24 border-red-low rounded-20" style="width:min(800px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon name="flight_takeoff" color="red-9" size="32px" class="q-mr-md" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Manifiesto de Despacho</div>
           </div>
           <q-btn flat round dense icon="close" @click="dialogDetalle=false" color="grey-6" />
        </div>

        <div class="row q-col-gutter-xl">
          <!-- Columna Izquierda: Detalles -->
          <div class="col-12 col-md-6">
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-md">Detalles Operativos</div>
            
            <q-card class="bg-black-20 q-pa-md rounded-12 border-red-low q-mb-md">
              <div class="font-mono text-grey-6" style="font-size:10px">ALUMNO PIC</div>
              <div class="text-white text-weight-bold font-head">{{ reservaSel.estudiante?.persona?.nombres }} {{ reservaSel.estudiante?.persona?.apellidos }}</div>
              
              <div class="q-mt-md font-mono text-grey-6" style="font-size:10px">CAPITÁN INSTRUCTOR</div>
              <div class="text-white text-weight-bold font-head">{{ reservaSel.instructor?.persona?.nombres || 'SOLO' }} {{ reservaSel.instructor?.persona?.apellidos || '' }}</div>

              <div class="q-mt-md font-mono text-grey-6" style="font-size:10px">AERONAVE / FECHA / HORA</div>
              <div class="text-white text-weight-bold font-mono">
                <q-badge color="red-9" class="q-mr-sm">{{ reservaSel.aeronave?.matricula }}</q-badge>
                {{ formatFechaCO(reservaSel.fecha) }} ({{ reservaSel.hora_inicio }} - {{ reservaSel.hora_fin }} UTC)
              </div>
            </q-card>

            <!-- Cambiar Estado -->
            <div class="q-mt-lg">
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm">Actualizar Estado</div>
              <div class="row q-col-gutter-sm">
                <div class="col-8">
                  <q-select v-model="estadoTemp" :options="opcionesEstado" emit-value map-options
                    filled dark class="premium-input-login" dense />
                </div>
                <div class="col-4">
                  <q-btn color="red-9" icon="update" class="full-width full-height premium-btn" @click="cambiarEstado" :loading="guardando" />
                </div>
              </div>
              <q-input v-if="estadoTemp === 'cancelada'" v-model="motivoCancelacion"
                type="textarea" rows="2" filled dark class="premium-input-login q-mt-md"
                label="MOTIVO DE CANCELACIÓN" />
            </div>
          </div>

          <!-- Columna Derecha: Flujo Briefing -> Bitácora -->
          <div class="col-12 col-md-6 flex column">
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-md">Ciclo de Vuelo</div>

            <!-- Briefing Pre-vuelo -->
            <q-card class="bg-black-20 q-pa-md rounded-12 border-red-low q-mb-md text-center cursor-pointer hover-row"
              @click="abrirBriefing(reservaSel, 'pre_vuelo')">
              <q-icon name="co_present" size="48px" :color="tieneBriefing ? 'emerald' : 'orange-9'" class="q-mb-sm" />
              <div class="text-white text-weight-bolder font-head">BRIEFING PRE-VUELO</div>
              <div class="text-caption text-grey-5 font-mono">{{ tieneBriefing ? 'Realizado' : 'Pendiente' }}</div>
            </q-card>

            <!-- Bitácora -->
            <q-card class="bg-black-20 q-pa-md rounded-12 border-red-low q-mb-md text-center cursor-pointer hover-row"
              @click="irABitacora(reservaSel)">
              <q-icon name="flight_takeoff" size="48px" color="blue-9" class="q-mb-sm" />
              <div class="text-white text-weight-bolder font-head">REGISTRAR BITÁCORA</div>
              <div class="text-caption text-grey-5 font-mono">Ir al formato RAC 91.417</div>
            </q-card>

            <!-- Debriefing Post-vuelo -->
            <q-card class="bg-black-20 q-pa-md rounded-12 border-red-low text-center cursor-pointer hover-row"
              @click="abrirBriefing(reservaSel, 'post_vuelo')">
              <q-icon name="rate_review" size="48px" :color="tieneDebriefing ? 'emerald' : 'grey-7'" class="q-mb-sm" />
              <div class="text-white text-weight-bolder font-head">DEBRIEFING POST-VUELO</div>
              <div class="text-caption text-grey-5 font-mono">{{ tieneDebriefing ? 'Realizado' : 'Pendiente vuelo' }}</div>
            </q-card>

          </div>
        </div>
      </q-card>
    </q-dialog>

    <!-- ════ DIÁLOGO: BRIEFING / DEBRIEFING ════ -->
    <q-dialog v-model="dialogBriefing" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20" style="width:min(600px, 95vw);">
        <div class="row items-center justify-between q-mb-lg border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon :name="tipoBriefing === 'pre_vuelo' ? 'co_present' : 'rate_review'" color="red-9" size="32px" class="q-mr-md" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">
                {{ tipoBriefing === 'pre_vuelo' ? 'Briefing Pre-vuelo' : 'Debriefing Post-vuelo' }}
              </div>
           </div>
           <q-btn flat round dense icon="close" @click="dialogBriefing=false" color="grey-6" />
        </div>
        
        <q-form @submit.prevent="guardarBriefing" class="q-gutter-y-lg">
          <q-select v-model="formBriefing.tipo" :options="['general','maniobras','seguridad','evaluacion']"
            label="TIPO DE ENFOQUE" filled dark class="premium-input-login" stack-label />
          
          <q-input v-model="formBriefing.contenido" type="textarea" rows="4"
            :label="tipoBriefing === 'pre_vuelo' ? 'ÁREAS A DISCUTIR / OBJETIVOS' : 'EVALUACIÓN Y COMENTARIOS'"
            filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']" />
            
          <q-input v-model="formBriefing.areas_debiles" type="textarea" rows="2"
            label="ÁREAS DÉBILES / POR MEJORAR"
            filled dark class="premium-input-login" stack-label />
            
          <q-toggle v-model="formBriefing.firma_instructor" label="FIRMAR CONFORME RAC"
            color="emerald" dark class="font-mono text-weight-bolder" />
            
          <q-btn type="submit" color="red-10" icon="save" label="Guardar Registro"
            class="full-width premium-btn q-py-lg shadow-24 text-weight-bolder" :loading="guardando" />
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import { formatFechaCO } from 'src/utils/formatters'

const router    = useRouter()
const $q        = useQuasar()
const authStore = useAuthStore()

const reservas    = ref([])
const aeronaves   = ref([])
const estudiantesOpc = ref([])
const instructoresOpc = ref([])
const briefings   = ref([]) // De la reserva actual

const cargando    = ref(false)
const dialogNueva = ref(false)
const dialogDetalle = ref(false)
const dialogBriefing = ref(false)
const creando     = ref(false)
const guardando   = ref(false)
const erroresRac  = ref([])

const reservaSel  = ref(null)
const estadoTemp  = ref('')
const motivoCancelacion = ref('')
const tipoBriefing = ref('pre_vuelo') // 'pre_vuelo' o 'post_vuelo'

const hoy          = dayjs().format('YYYY-MM-DD')
const miInstructorId = ref(null)
const filtros = ref({ fecha: hoy, estado: null, tipo: null })
const form = ref({ fecha: hoy, hora_inicio: '', hora_fin: '', aeronave_id: null, tipo: 'instruccion', estudiante_id: null, instructor_id: null, objetivos: '' })
const formBriefing = ref({ reserva_id: null, pos_vuelo: 'pre_vuelo', tipo: 'general', contenido: '', areas_debiles: '', firma_instructor: true })

const opcionesEstado = [
  { label: 'PENDIENTE',  value: 'pendiente' },
  { label: 'CONFIRMADA', value: 'confirmada' },
  { label: 'COMPLETADA', value: 'completada' },
  { label: 'CANCELADA',  value: 'cancelada' },
]
const TIPOS_DEFECTO = ['instruccion', 'solo', 'simulador']
const opcionesTipo = ref(
  JSON.parse(localStorage.getItem('sat_tipos_entrenamiento') || 'null') || [
    { label: 'INSTRUCCIÓN DUAL', value: 'instruccion' },
    { label: 'SOLO / PIC',       value: 'solo' },
    { label: 'SIMULADOR',        value: 'simulador' },
  ]
)
function agregarTipo() {
  $q.dialog({
    title: 'Nueva modalidad',
    message: 'Nombre de la modalidad de entrenamiento:',
    prompt: { model: '', type: 'text', filled: true },
    cancel: { flat: true, label: 'Cancelar', color: 'grey' },
    ok: { label: 'Agregar', color: 'red-9' },
    dark: true,
  }).onOk(nombre => {
    const n = nombre?.trim()
    if (!n) return
    const val = n.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '')
    if (!opcionesTipo.value.some(o => o.value === val)) {
      opcionesTipo.value.push({ label: n.toUpperCase(), value: val })
      localStorage.setItem('sat_tipos_entrenamiento', JSON.stringify(opcionesTipo.value))
    }
    form.value.tipo = val
  })
}
function eliminarTipo(val) {
  opcionesTipo.value = opcionesTipo.value.filter(o => o.value !== val)
  localStorage.setItem('sat_tipos_entrenamiento', JSON.stringify(opcionesTipo.value))
  if (form.value.tipo === val) form.value.tipo = null
}

const opcionesAeronaves = computed(() => aeronaves.value.filter(a => a.estado === 'disponible').map(a => ({ value: a.id, label: `${a.matricula} · ${a.modelo}` })))
const colorEstadoReserva = (e) => ({ pendiente:'warning', confirmada:'emerald', completada:'blue-10', cancelada:'red-10' }[e]||'grey-8')

const tieneBriefing   = computed(() => briefings.value.some(b => b.pos_vuelo === 'pre_vuelo'))
const tieneDebriefing = computed(() => briefings.value.some(b => b.pos_vuelo === 'post_vuelo'))

async function verDetalle(r) {
  reservaSel.value = r
  estadoTemp.value = r.estado
  motivoCancelacion.value = r.motivo_cancelacion || ''
  dialogDetalle.value = true
  // Cargar briefings de esta reserva
  try {
    const { data } = await api.get(`/briefings?reserva_id=${r.id}`)
    briefings.value = data.data || []
  } catch { briefings.value = [] }
}

async function cambiarEstado() {
  guardando.value = true
  try {
    const payload = { estado: estadoTemp.value }
    if (estadoTemp.value === 'cancelada') payload.motivo_cancelacion = motivoCancelacion.value
    await api.put(`/reservas/${reservaSel.value.id}`, payload)
    $q.notify({ color: 'emerald', message: 'Estado de reserva actualizado.' })
    cargar()
    reservaSel.value.estado = estadoTemp.value
  } catch (e) { $q.notify({ color: 'negative', message: 'Error al actualizar.' }) }
  finally { guardando.value = false }
}

function abrirBriefing(reserva, pos) {
  tipoBriefing.value = pos
  // Buscar si ya existe para precargar
  const existente = briefings.value.find(b => b.pos_vuelo === pos)
  if (existente) {
    formBriefing.value = { ...existente }
  } else {
    formBriefing.value = { reserva_id: reserva.id, pos_vuelo: pos, tipo: 'general', contenido: '', areas_debiles: '', firma_instructor: true }
  }
  dialogBriefing.value = true
}

async function guardarBriefing() {
  guardando.value = true
  try {
    if (formBriefing.value.id) {
      await api.put(`/briefings/${formBriefing.value.id}`, formBriefing.value)
    } else {
      await api.post('/briefings', formBriefing.value)
    }
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Briefing registrado.' })
    dialogBriefing.value = false
    // Recargar briefings
    const { data } = await api.get(`/briefings?reserva_id=${reservaSel.value.id}`)
    briefings.value = data.data || []
  } catch (e) { $q.notify({ color: 'negative', message: 'Error al guardar briefing.' }) }
  finally { guardando.value = false }
}

function irABitacora(reserva) {
  // Guardamos en store o pasamos query params a la vista de BitácoraNueva
  // Por ahora lo más simple es usar query params
  router.push({
    path: '/vuelo/nueva-bitacora',
    query: {
      reserva_id: reserva.id,
      estudiante_id: reserva.estudiante_id,
      instructor_id: reserva.instructor_id,
      aeronave_id: reserva.aeronave_id,
      fecha: reserva.fecha
    }
  })
}

function limpiarFiltros() { filtros.value = { fecha: '', estado: null, tipo: null }; cargar() }

function cerrarDialog() {
  dialogNueva.value = false
  erroresRac.value = []
  form.value = { fecha: hoy, hora_inicio: '', hora_fin: '', aeronave_id: null, tipo: 'instruccion', estudiante_id: null, instructor_id: miInstructorId.value, objetivos: '' }
}

async function filtrarEstudiantes(val, update) {
  try {
    const { data } = await api.get('/estudiantes', { params: { buscar: val, estado: 'activo', per_page: 20 } })
    const lista = data.data?.data || data.data || []
    update(() => {
      estudiantesOpc.value = lista.map(e => ({
        label: `${e.persona?.nombres} ${e.persona?.apellidos} - ${e.num_expediente}`, value: e.id,
      }))
    })
  } catch { update(() => { estudiantesOpc.value = [] }) }
}

async function cargarCatalogos() {
  try {
    const [aer, inst] = await Promise.all([
      api.get('/aeronaves', { params: { estado: 'disponible' } }),
      api.get('/instructores', { params: { activo: 1 } })
    ])
    aeronaves.value = aer.data.data || []
    const listaInst = inst.data.data?.data || inst.data.data || []
    instructoresOpc.value = listaInst.map(i => ({
      label: `${i.persona?.nombres} ${i.persona?.apellidos}`, value: i.id,
      usuario_id: i.persona?.usuario_id
    }))
    // Pre-seleccionar si el usuario es instructor
    if (authStore.rol === 'instructor') {
      const meRes = await api.get('/auth/me')
      const userId = meRes.data?.data?.id
      const match = instructoresOpc.value.find(i => i.usuario_id === userId)
      if (match) {
        miInstructorId.value    = match.value
        form.value.instructor_id = match.value
      }
    }
  } catch {}
}

async function cargar() {
  cargando.value = true
  try {
    const params = { ...filtros.value }
    const { data } = await api.get('/reservas', { params })
    reservas.value = data.data.data || data.data || []
  } catch { /**/ } finally { cargando.value = false }
}

async function crearReserva() {
  erroresRac.value = []
  creando.value = true
  try {
    const payload = { ...form.value }
    await api.post('/reservas', payload)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Misión agendada exitosamente en el despacho operativo.' })
    cerrarDialog(); cargar()
  } catch (e) {
    if (e.response?.status === 422) {
      const data = e.response.data
      if (data.errores?.length) {
        // Errores de la ReservaService (validaciones RAC)
        erroresRac.value = data.errores
      } else if (data.errors) {
        // Errores de validación de Laravel ($request->validate)
        erroresRac.value = Object.values(data.errors).flat()
      } else {
        erroresRac.value = [data.message || 'Error de validación.']
      }
    } else {
      erroresRac.value = ['Error de servidor. Intente nuevamente.']
    }
  } finally { creando.value = false }
}

onMounted(() => { cargar(); cargarCatalogos() })
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
