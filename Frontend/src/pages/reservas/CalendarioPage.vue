<template>
  <q-page padding style="padding-bottom:80px">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">Scheduling · RAC 141</div>
        <div class="font-head text-white" style="font-size:20px; font-weight:700">Calendario de Vuelos</div>
      </div>
      <div class="row items-center q-gutter-sm">
        <q-btn flat round dense icon="chevron_left" color="grey-5" @click="mesAnterior" />
        <span class="font-head text-white" style="font-size:15px; font-weight:600; min-width:150px; text-align:center">
          {{ mesLabel }}
        </span>
        <q-btn flat round dense icon="chevron_right" color="grey-5" @click="mesSiguiente" />
        <q-btn flat round dense icon="today" color="primary" @click="irHoy" />
      </div>
    </div>

    <!-- Leyenda -->
    <div class="row q-gutter-md q-mb-md flex-wrap">
      <div v-for="l in leyenda" :key="l.label" class="row items-center q-gutter-xs">
        <div style="width:10px; height:10px; border-radius:50%" :style="`background:${l.color}`"></div>
        <span style="font-size:11px; color:#64748b">{{ l.label }}</span>
      </div>
    </div>

    <!-- Calendario grid -->
    <q-card flat class="card-rac" style="background:#0f1218">
      <q-card-section class="q-pa-sm">
        <!-- Encabezado días -->
        <div class="row q-mb-xs">
          <div v-for="dia in diasSemana" :key="dia" class="col text-center font-mono"
            style="font-size:10px; color:#475569; letter-spacing:1px; padding:6px 0">
            {{ dia }}
          </div>
        </div>
        <!-- Semanas -->
        <div v-for="(semana, si) in semanas" :key="si" class="row q-mb-xs">
          <div v-for="dia in semana" :key="dia.key" class="col"
            style="min-height:70px; padding:3px; border:1px solid rgba(255,255,255,.04); border-radius:6px; cursor:pointer"
            :style="dia.esHoy ? 'border-color:rgba(59,130,246,.4); background:rgba(59,130,246,.04)' : ''"
            :class="!dia.esMesActual ? 'opacity-40' : ''"
            @click="seleccionarDia(dia)">
            <div class="row items-center justify-between q-px-xs q-pt-xs">
              <span :class="dia.esHoy ? 'text-primary' : 'text-white'"
                style="font-size:12px; font-weight:600">{{ dia.dia }}</span>
              <q-badge v-if="dia.reservas.length > 2" color="grey-7"
                :label="`+${dia.reservas.length - 2}`" style="font-size:9px" />
            </div>
            <div v-for="r in dia.reservas.slice(0,2)" :key="r.id"
              class="q-px-xs q-py-none q-mb-xs"
              style="border-radius:3px; font-size:9px; color:white; white-space:nowrap; overflow:hidden; text-overflow:ellipsis"
              :style="`background:${colorReserva(r.estado)}33; border-left:2px solid ${colorReserva(r.estado)}`">
              {{ r.hora_inicio?.slice(0,5) }} {{ r.aeronave?.matricula }}
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Panel lateral del día seleccionado -->
    <q-dialog v-model="dialogDia" position="right" :maximized="$q.screen.lt.sm">
      <q-card dark style="background:#0f1218; border:1px solid rgba(255,255,255,.1); width:360px; max-width:100vw">
        <q-toolbar style="background:#151920">
          <q-toolbar-title class="font-head" style="font-size:15px">{{ diaSeleccionado?.label }}</q-toolbar-title>
          <q-btn flat round dense icon="close" @click="dialogDia = false" color="grey-5" />
        </q-toolbar>
        <q-scroll-area style="height:calc(100vh - 60px); max-height:500px">
          <q-card-section>
            <div v-if="!diaSeleccionado?.reservas?.length" class="text-center q-py-xl" style="color:#475569; font-size:13px">
              Sin vuelos programados
            </div>
            <div v-for="r in (diaSeleccionado?.reservas || [])" :key="r.id"
              class="q-pa-sm q-mb-sm rounded-borders"
              :style="`background:${colorReserva(r.estado)}11; border:1px solid ${colorReserva(r.estado)}33`">
              <div class="row items-center justify-between q-mb-xs">
                <span class="font-mono text-white" style="font-size:13px; font-weight:600">
                  {{ r.hora_inicio }} – {{ r.hora_fin }}
                </span>
                <q-chip dense :color="colorReserva(r.estado) === '#22c55e' ? 'positive' : 'warning'"
                  :label="r.estado" text-color="white" style="font-size:9px" />
              </div>
              <div style="font-size:12px; color:#94a3b8">
                ✈ {{ r.aeronave?.matricula }} · {{ r.aeronave?.modelo }}
              </div>
              <div style="font-size:12px; color:#94a3b8">
                🎓 {{ r.estudiante?.persona?.nombres }} {{ r.estudiante?.persona?.apellidos }}
              </div>
              <div v-if="r.instructor" style="font-size:12px; color:#94a3b8">
                👨‍✈️ {{ r.instructor?.persona?.nombres }} {{ r.instructor?.persona?.apellidos }}
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
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

const mesActual      = ref(dayjs())
const reservasData   = ref([])
const cargando       = ref(false)
const dialogDia      = ref(false)
const diaSeleccionado = ref(null)

const diasSemana = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']

const mesLabel = computed(() => mesActual.value.format('MMMM YYYY').replace(/^./, c => c.toUpperCase()))

const semanas = computed(() => {
  const inicio    = mesActual.value.startOf('month')
  const fin       = mesActual.value.endOf('month')
  const inicioGrid = inicio.startOf('week').add(1, 'day') // Lunes
  const finGrid   = fin.endOf('week').add(1, 'day')

  const semanas = []
  let dia = inicioGrid

  while (dia.isBefore(finGrid) || dia.isSame(finGrid, 'day')) {
    const semana = []
    for (let i = 0; i < 7; i++) {
      const fechaStr = dia.format('YYYY-MM-DD')
      semana.push({
        key:         fechaStr,
        dia:         dia.date(),
        label:       dia.format('dddd D [de] MMMM'),
        esHoy:       dia.isSame(dayjs(), 'day'),
        esMesActual: dia.month() === mesActual.value.month(),
        reservas:    reservasData.value.filter(r => r.fecha === fechaStr),
        fecha:       dia.clone(),
      })
      dia = dia.add(1, 'day')
    }
    semanas.push(semana)
  }
  return semanas
})

const leyenda = [
  { label: 'Pendiente',  color: '#f59e0b' },
  { label: 'Confirmada', color: '#22c55e' },
  { label: 'Completada', color: '#3b82f6' },
  { label: 'Cancelada',  color: '#ef4444' },
]

const colorReserva = (e) => ({
  pendiente: '#f59e0b', confirmada: '#22c55e', completada: '#3b82f6', cancelada: '#ef4444',
}[e] || '#6b7280')

function seleccionarDia(dia) {
  diaSeleccionado.value = dia
  dialogDia.value = true
}

function mesAnterior()  { mesActual.value = mesActual.value.subtract(1, 'month'); cargar() }
function mesSiguiente() { mesActual.value = mesActual.value.add(1, 'month');      cargar() }
function irHoy()        { mesActual.value = dayjs();                              cargar() }

async function cargar() {
  cargando.value = true
  try {
    const mes = mesActual.value.format('YYYY-MM')
    const { data } = await api.get(`/reservas/calendario?mes=${mes}`)
    reservasData.value = data.data || []
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>
