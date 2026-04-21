<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Cabecera Responsive ══ -->
    <div class="rac-page-header">
      <div class="row items-center">
        <q-icon name="event_note" size="40px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">SCHEDULING & FLEET OPS · RAC 141</div>
          <h1 class="rac-page-title">Consola de Planificación</h1>
        </div>
      </div>

      <!-- Navegación del mes — adapta en mobile -->
      <div class="month-nav-wrap">
        <div class="premium-glass-card flex items-center q-pa-sm border-red-low shadow-24 rounded-12 welcome-hero overflow-hidden">
          <div class="hero-glow"></div>
          <q-btn flat round dense icon="chevron_left" color="red-9" @click="mesAnterior" class="shadow-inner" />
          <span class="font-head text-white text-weight-bolder uppercase q-mx-md relative-position month-label">
            {{ mesLabel }}
          </span>
          <q-btn flat round dense icon="chevron_right" color="red-9" @click="mesSiguiente" class="shadow-inner" />
        </div>
        <q-btn color="red-9" icon="today" label="Hoy" class="premium-btn shadow-24 q-px-lg q-py-md text-weight-bolder" @click="irHoy" />
      </div>
    </div>

    <!-- ══ Leyenda de Estados — wrap en mobile ══ -->
    <div class="row q-gutter-md q-mb-xl justify-center items-center q-pa-lg premium-glass-card border-red-low rounded-20 shadow-inner flex-wrap">
      <div v-for="l in leyenda" :key="l.label" class="row items-center q-gutter-x-sm">
        <div class="status-dot-premium shadow-24" :style="`background:${l.color}; box-shadow: 0 0 8px ${l.color}44`"></div>
        <span class="text-caption text-grey-5 font-mono uppercase tracking-widest text-weight-bolder" style="font-size:9px">{{ l.label }}</span>
      </div>
    </div>

    <!-- ══ Calendario: desktop 7 cols, mobile lista ══ -->

    <!-- DESKTOP: Grid de calendario (sm+) -->
    <q-card class="premium-glass-card shadow-24 border-red-low overflow-hidden rounded-20 bonus-grid gt-xs">
      <q-card-section class="q-pa-none">
        <!-- Días de la semana -->
        <div class="row bg-black-20 border-bottom-border">
          <div v-for="dia in diasSemana" :key="dia" class="col text-center q-pa-md border-column">
            <span class="font-mono text-grey-6 uppercase tracking-widest text-weight-bolder" style="font-size:10px">{{ dia }}</span>
          </div>
        </div>
        <!-- Semanas -->
        <div v-for="(semana, si) in semanas" :key="si" class="row">
          <div v-for="dia in semana" :key="dia.key"
            class="col calendar-cell-premium q-pa-sm border-column border-bottom-border relative-position"
            :class="[
               !dia.esMesActual ? 'opacity-10 pointer-none' : 'hover-cell',
               dia.esHoy ? 'today-slot' : ''
            ]"
            @click="seleccionarDia(dia)"
          >
            <div class="row items-center justify-between q-mb-sm">
              <span class="font-mono text-weight-bolder" :class="dia.esHoy ? 'text-red-9' : 'text-grey-4'" style="font-size:14px">{{ dia.dia }}</span>
              <q-avatar v-if="dia.reservas.length > 3" color="red-10" text-color="white" size="18px" class="font-mono shadow-24" style="font-size:9px">+{{ dia.reservas.length - 3 }}</q-avatar>
            </div>
            <div class="q-gutter-y-xs">
              <div v-for="r in dia.reservas.slice(0,3)" :key="r.id"
                class="reserva-slot-mini q-px-xs q-py-xs font-mono flex items-center no-wrap shadow-inner"
                :style="`border-left: 3px solid ${colorReserva(r.estado)}`"
              >
                <q-icon name="flight" size="9px" color="grey-7" class="q-mr-xs" />
                <span class="truncate-text" style="font-size:9px; color:rgba(255,255,255,0.8)">
                  {{ r.hora_inicio?.slice(0,5) }} · <b class="text-white">{{ r.aeronave?.matricula }}</b>
                </span>
              </div>
            </div>
            <div v-if="dia.esHoy" class="today-label font-mono uppercase text-weight-bolder">HOY</div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- MOBILE: Vista de lista por día (xs only) -->
    <div class="lt-sm">
      <div v-for="(semana, si) in semanas" :key="si">
        <div v-for="dia in semana" :key="dia.key">
          <div v-if="dia.esMesActual && dia.reservas.length > 0"
            class="premium-glass-card q-pa-lg q-mb-md border-red-low rounded-20 shadow-24 hover-cell"
            @click="seleccionarDia(dia)">
            <div class="row items-center justify-between q-mb-md">
              <div class="row items-center">
                <div class="day-circle q-mr-md" :class="dia.esHoy ? 'day-circle-today' : ''">
                  <span class="font-mono text-h6 text-weight-bolder">{{ dia.dia }}</span>
                </div>
                <div class="font-mono text-grey-4 uppercase text-weight-bolder" style="font-size:11px">{{ dia.label?.split(' ')[0] }}</div>
              </div>
              <q-badge color="red-10" :label="`${dia.reservas.length} VUELOS`" class="font-mono text-weight-bolder shadow-24" />
            </div>
            <div class="q-gutter-y-xs">
              <div v-for="r in dia.reservas.slice(0,2)" :key="r.id"
                class="reserva-slot-mini q-px-sm q-py-sm font-mono flex items-center no-wrap shadow-inner"
                :style="`border-left: 3px solid ${colorReserva(r.estado)}`">
                <q-icon name="flight" size="12px" color="grey-7" class="q-mr-sm" />
                <span style="font-size:11px; color:rgba(255,255,255,0.9)">
                  {{ r.hora_inicio?.slice(0,5) }} · <b class="text-white">{{ r.aeronave?.matricula }}</b>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Días sin reservas -->
      <div v-if="diasConReservas === 0" class="text-center q-pa-xl premium-glass-card border-red-low rounded-20 shadow-24">
        <q-icon name="event_busy" size="64px" color="grey-8" class="q-mb-md opacity-20" />
        <div class="text-grey-7 font-mono uppercase tracking-widest">Sin misiones agendadas este mes</div>
      </div>
    </div>

    <!-- ════ PANEL LATERAL: DOSSIER DE MISIÓN ════ -->
    <q-dialog v-model="dialogDia" :position="$q.screen.lt.sm ? 'bottom' : 'right'" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-left" :style="$q.screen.lt.sm ? 'width:100%;border-radius:20px 20px 0 0' : 'width:min(480px,95vw);height:100vh'">
        <div class="q-pa-xl border-bottom-border welcome-hero overflow-hidden">
          <div class="hero-glow"></div>
          <div class="row items-center justify-between relative-position">
            <div class="row items-center">
              <q-icon name="departure_board" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div>
                <div class="rac-page-subtitle">Manifiesto de Vuelo</div>
                <div class="text-h6 font-head text-white text-weight-bolder uppercase tracking-tighter">{{ diaSeleccionado?.label }}</div>
              </div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogDia = false" color="grey-6" class="shadow-inner" />
          </div>
        </div>

        <q-scroll-area :style="$q.screen.lt.sm ? 'height:60vh' : 'height:calc(100vh - 120px)'">
          <q-card-section class="q-pa-xl">
            <div v-if="!diaSeleccionado?.reservas?.length" class="text-center q-pa-xl border-red-low rounded-20 bg-black-20 shadow-inner">
              <q-icon name="event_busy" size="64px" color="grey-8" class="q-mb-md opacity-20" />
              <div class="text-grey-7 font-head uppercase tracking-widest">Sin misiones agendadas</div>
            </div>

            <div v-for="(r, idx) in (diaSeleccionado?.reservas || [])" :key="r.id"
              class="premium-glass-card q-pa-xl q-mb-lg border-red-low hover-row-premium shadow-24 animate-slide-up rounded-20"
              :style="`animation-delay: ${idx * 0.08}s`"
            >
              <div class="row items-center justify-between q-mb-lg">
                <div class="row items-center">
                  <q-badge color="red-10" class="q-mr-sm" rounded />
                  <div class="font-mono text-white text-weight-bolder" style="font-size:16px; letter-spacing:-0.5px">{{ r.hora_inicio }} — {{ r.hora_fin }}</div>
                </div>
                <q-badge :color="colorBadge(r.estado)" :label="r.estado?.toUpperCase()" class="font-mono text-weight-bolder q-px-md q-py-xs shadow-24 rounded-12" />
              </div>
              
              <div class="q-gutter-y-md q-pa-lg bg-black-20 rounded-12 shadow-inner border-red-low">
                <div class="row items-center q-gutter-x-md">
                  <q-icon name="flight" color="red-9" size="18px" />
                  <div class="col">
                    <div class="text-white text-weight-bolder font-head line-height-1">{{ r.aeronave?.matricula }}</div>
                    <div class="text-grey-6 font-mono text-caption uppercase" style="font-size:9px">{{ r.aeronave?.modelo }}</div>
                  </div>
                </div>
                <q-separator dark class="opacity-5" />
                <div class="row items-center q-gutter-x-md">
                  <q-avatar size="28px" class="shadow-24 border-red-low">
                    <img :src="`https://ui-avatars.com/api/?name=${r.estudiante?.persona?.nombres}&background=A10B13&color=fff`" />
                  </q-avatar>
                  <div class="col">
                    <div class="text-grey-3 font-head text-weight-bold" style="font-size:13px">Cad. {{ r.estudiante?.persona?.nombres }} {{ r.estudiante?.persona?.apellidos }}</div>
                    <div class="text-grey-6 font-mono uppercase" style="font-size:9px">Exp: {{ r.estudiante?.num_expediente }}</div>
                  </div>
                </div>
                <div v-if="r.instructor" class="row items-center q-gutter-x-md bg-red-10-opacity q-pa-sm rounded-8 border-red-low">
                  <q-icon name="stars" color="red-9" size="16px" />
                  <div class="text-red-9 font-mono text-weight-bolder uppercase" style="font-size:10px">CAPITÁN: {{ r.instructor?.persona?.nombres }}</div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-scroll-area>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

const $q = useQuasar()
const mesActual       = ref(dayjs())
const reservasData    = ref([])
const cargando        = ref(false)
const dialogDia       = ref(false)
const diaSeleccionado = ref(null)

// En mobile mostrar nombres cortos
const diasSemana = computed(() =>
  $q.screen.lt.sm
    ? ['L', 'M', 'X', 'J', 'V', 'S', 'D']
    : ['LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB', 'DOM']
)

const mesLabel = computed(() => mesActual.value.format('MMMM YYYY').toUpperCase())

const semanas = computed(() => {
  const inicio = mesActual.value.startOf('month')
  const fin    = mesActual.value.endOf('month')
  const inicioGrid = inicio.startOf('week').add(1, 'day')
  const finGrid    = fin.endOf('week').add(1, 'day')
  const semanas = []
  let dia = inicioGrid
  while (dia.isBefore(finGrid) || dia.isSame(finGrid, 'day')) {
    const semana = []
    for (let i = 0; i < 7; i++) {
      const fechaStr = dia.format('YYYY-MM-DD')
      semana.push({
        key: fechaStr,
        dia: dia.date(),
        label: dia.format('dddd D [de] MMMM').toUpperCase(),
        esHoy: dia.isSame(dayjs(), 'day'),
        esMesActual: dia.month() === mesActual.value.month(),
        reservas: reservasData.value.filter(r => r.fecha === fechaStr),
        fecha: dia.clone()
      })
      dia = dia.add(1, 'day')
    }
    semanas.push(semana)
  }
  return semanas
})

const diasConReservas = computed(() =>
  semanas.value.flat().filter(d => d.esMesActual && d.reservas.length > 0).length
)

const leyenda = [
  { label: 'PENDIENTE',  color: '#f59e0b' },
  { label: 'AUTORIZADA', color: '#10b981' },
  { label: 'COMPLETADA', color: '#0ea5e9' },
  { label: 'CANCELADA',  color: '#A10B13' },
]

const colorReserva = (e) => ({ pendiente: '#f59e0b', confirmada: '#10b981', completada: '#0ea5e9', cancelada: '#A10B13' }[e] || '#6b7280')
const colorBadge   = (e) => ({ pendiente: 'orange-10', confirmada: 'emerald', completada: 'blue-10', cancelada: 'red-10' }[e] || 'grey-8')

function seleccionarDia(dia) { diaSeleccionado.value = dia; dialogDia.value = true }
function mesAnterior()       { mesActual.value = mesActual.value.subtract(1, 'month'); cargar() }
function mesSiguiente()      { mesActual.value = mesActual.value.add(1, 'month'); cargar() }
function irHoy()             { mesActual.value = dayjs(); cargar() }

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/reservas/calendario', { params: { mes: mesActual.value.format('YYYY-MM') } })
    reservasData.value = data.data || []
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>

.border-column     { border-right: 1px solid rgba(255,255,255,0.05); &:last-child { border-right: none; } }

.bg-black-20       { background: rgba(0,0,0,0.2); }

.bg-red-10-opacity { background: rgba(161, 11, 19, 0.06); }
.opacity-20 { opacity: 0.2; }

// ── Page header responsive ──
.rac-page-header {
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
  gap: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 24px; margin-bottom: 24px;
  @media (max-width: 599px) { flex-direction: column; align-items: flex-start; padding-bottom: 16px; }
}

.month-nav-wrap {
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
  @media (max-width: 599px) { width: 100%; }
}

.month-label {
  font-size: 15px; min-width: 160px; text-align: center; letter-spacing: -0.5px;
  @media (max-width: 599px) { font-size: 13px; min-width: 120px; }
}

// ── Calendar cells ──
.calendar-cell-premium {
  min-height: 110px; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;
  @media (max-width: 900px) { min-height: 80px; padding: 6px !important; }
}
.hover-cell:hover {
  background: rgba(255,255,255,0.02);
  box-shadow: inset 0 0 20px rgba(161, 11, 19, 0.07);
}
.today-slot {
  background: rgba(161, 11, 19, 0.05) !important;
  border: 1px solid rgba(161, 11, 19, 0.3) !important;
  box-shadow: 0 0 15px rgba(161, 11, 19, 0.08);
}

.status-dot-premium { width: 10px; height: 10px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.2); flex-shrink: 0; }
.reserva-slot-mini {
  background: rgba(0,0,0,0.25); border-radius: 5px; margin-bottom: 3px;
  border: 1px solid rgba(255,255,255,0.04); overflow: hidden;
}
.truncate-text { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
.today-label { position: absolute; bottom: 4px; right: 6px; font-size: 7px; color: #A10B13; letter-spacing: 2px; }

// ── Mobile day circle ──
.day-circle {
  width: 36px; height: 36px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center;
}
.day-circle-today { border-color: #A10B13 !important; background: rgba(161,11,19,0.15) !important; }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.12) 0%, transparent 50%); pointer-events:none; }

.pulsate { animation: pulsate 2.5s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
.bonus-grid { background-image: radial-gradient(rgba(161, 11, 19, 0.04) 1px, transparent 1px); background-size: 28px 28px; }

.pointer-none { pointer-events: none; }
.opacity-10 { opacity: 0.08; }

.hover-row-premium { transition: all 0.25s; cursor:pointer; &:hover { background: rgba(255,255,255,0.03); border-color: rgba(161,11,19,0.4) !important; } }

@keyframes slideUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
.animate-slide-up { animation: slideUp 0.4s ease-out both; }
</style>
