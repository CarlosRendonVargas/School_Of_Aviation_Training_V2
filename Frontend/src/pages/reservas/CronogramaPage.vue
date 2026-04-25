<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">Mis Vuelos Programados</div>
        <h1 class="rac-page-title">Cronograma de <span class="text-red-9">Instrucción</span></h1>
      </div>
      <q-btn flat icon="refresh" label="Actualizar" color="grey-5" size="sm" @click="cargar" :loading="cargando" />
    </div>

    <!-- Cargando -->
    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-cube color="red-9" size="48px" />
    </div>

    <!-- Sin vuelos -->
    <div v-else-if="!reservas.length" class="flex flex-center column q-pa-xl text-center"
      style="min-height:300px">
      <q-icon name="flight_takeoff" size="64px" color="grey-8" />
      <div class="text-grey-5 q-mt-md" style="font-size:15px">No tienes vuelos programados pendientes</div>
      <div class="text-grey-7 q-mt-xs" style="font-size:12px">Tu instructor programará las actividades aquí</div>
    </div>

    <!-- Lista de vuelos -->
    <div v-else class="row q-col-gutter-md">
      <div v-for="r in reservas" :key="r.id" class="col-12 col-md-6 col-lg-4">
        <q-card class="premium-glass-card vuelo-card" style="border-radius:16px; overflow:hidden">

          <!-- Cabecera coloreada según estado -->
          <div class="vuelo-header q-pa-md row items-center justify-between"
            :style="headerStyle(r)">
            <div>
              <div class="text-white text-weight-bold" style="font-size:16px">
                {{ tipoLabel(r.tipo) }}
              </div>
              <div class="text-white q-mt-xs" style="font-size:12px; opacity:0.85">
                {{ r.aeronave?.matricula }} ({{ r.aeronave?.modelo }})
              </div>
            </div>
            <div class="column items-end q-gutter-y-xs">
              <q-badge :color="estadoColor(r)" :label="estadoLabel(r)" rounded />
              <q-badge v-if="r.confirmacion_estudiante === 'pendiente'" color="purple" rounded>
                <q-icon name="hourglass_empty" size="10px" class="q-mr-xs" />
                PENDIENTE CONFIRMACIÓN
              </q-badge>
            </div>
          </div>

          <!-- Cuerpo -->
          <div class="q-pa-md">

            <!-- Fecha e instructor -->
            <div class="row q-gutter-md q-mb-md">
              <div class="col">
                <div class="text-grey-5 font-mono" style="font-size:10px">FECHA Y HORA</div>
                <div class="text-white text-weight-medium" style="font-size:13px">
                  <q-icon name="event" size="14px" class="q-mr-xs" />{{ fmtFecha(r.fecha) }}
                </div>
                <div class="text-grey-4" style="font-size:12px">
                  {{ fmtHora(r.hora_inicio) }} → {{ fmtHora(r.hora_fin) }}
                </div>
              </div>
              <div class="col">
                <div class="text-grey-5 font-mono" style="font-size:10px">INSTRUCTOR</div>
                <div class="text-white text-weight-medium" style="font-size:13px">
                  <q-icon name="person" size="14px" class="q-mr-xs" />
                  {{ nombreInstructor(r) }}
                </div>
              </div>
            </div>

            <!-- Objetivos -->
            <div v-if="r.objetivos" class="q-mb-md">
              <div class="text-grey-5 font-mono q-mb-xs" style="font-size:10px">OBJETIVOS</div>
              <div class="text-grey-3" style="font-size:13px; line-height:1.5">{{ r.objetivos }}</div>
            </div>

            <!-- Countdown — solo si está pendiente de confirmación -->
            <template v-if="r.confirmacion_estudiante === 'pendiente' && r.estado === 'pendiente'">
              <div class="countdown-box q-pa-md q-mb-md" style="border-radius:10px; text-align:center">
                <div class="text-purple-3 font-mono q-mb-xs" style="font-size:10px; letter-spacing:2px">
                  <q-icon name="timer" size="12px" /> TIEMPO PARA CONFIRMAR
                </div>
                <div class="text-purple-2 text-weight-bold" style="font-size:28px; font-family:monospace; letter-spacing:4px">
                  {{ countdowns[r.id] || calcularCountdown(r) }}
                </div>
                <div class="text-grey-6 q-mt-xs" style="font-size:11px; line-height:1.5">
                  Tu instructor programó esta actividad y esperará <strong>24h</strong> para tu confirmación.
                  Si no respondes, el sistema la cancelará automáticamente.
                </div>
              </div>

              <!-- Botones -->
              <div class="column q-gutter-sm">
                <q-btn unelevated color="red-9" icon="check" label="Aceptar Plan de Vuelo"
                  class="full-width premium-btn" size="md"
                  :loading="procesando === r.id + '_aceptar'"
                  @click="aceptar(r)" />
                <q-btn outline color="red-9" icon="close" label="No puedo asistir"
                  class="full-width" size="md"
                  :loading="procesando === r.id + '_rechazar'"
                  @click="rechazar(r)" />
              </div>
            </template>

            <!-- Ya aceptado -->
            <div v-else-if="r.confirmacion_estudiante === 'aceptada'"
              class="text-center q-pa-sm" style="background:rgba(34,197,94,0.1); border-radius:8px">
              <q-icon name="check_circle" color="positive" size="20px" class="q-mr-xs" />
              <span class="text-positive" style="font-size:13px">Vuelo confirmado</span>
            </div>

          </div>
        </q-card>
      </div>
    </div>

    <!-- Dialog motivo rechazo -->
    <q-dialog v-model="dialogRechazo" persistent backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20" style="width:min(420px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h6 text-white">¿Por qué no puedes asistir?</div>
        </div>
        <div class="rac-dialog-body">
          <q-input v-model="motivoRechazo" dark outlined dense type="textarea" rows="3"
            label="Motivo (opcional)"
            placeholder="Ej: Tengo un compromiso médico ese día..." />
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="negative" icon="close" label="Confirmar rechazo"
            :loading="procesando?.includes('_rechazar')"
            @click="confirmarRechazo" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()

const cargando     = ref(false)
const reservas     = ref([])
const procesando   = ref(null)
const countdowns   = ref({})
const dialogRechazo = ref(false)
const motivoRechazo = ref('')
const reservaSeleccionada = ref(null)

let timer = null

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/reservas/cronograma')
    reservas.value = data.data ?? []
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar el cronograma.' })
  } finally {
    cargando.value = false
  }
}

function calcularCountdown(r) {
  if (!r.confirmacion_expira) return '00:00:00'
  const diff = new Date(r.confirmacion_expira) - new Date()
  if (diff <= 0) return '00:00:00'
  const h = Math.floor(diff / 3600000)
  const m = Math.floor((diff % 3600000) / 60000)
  const s = Math.floor((diff % 60000) / 1000)
  return [h, m, s].map(n => String(n).padStart(2, '0')).join(':')
}

function tickCountdowns() {
  reservas.value.forEach(r => {
    if (r.confirmacion_estudiante === 'pendiente' && r.confirmacion_expira) {
      countdowns.value[r.id] = calcularCountdown(r)
    }
  })
}

function aceptar(r) {
  $q.dialog({
    title: 'Confirmar plan de vuelo',
    message: `¿Confirmas tu asistencia al vuelo del <strong>${fmtFecha(r.fecha)}</strong> a las ${fmtHora(r.hora_inicio)}?`,
    html: true,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { unelevated: true, label: 'Sí, acepto', color: 'red-9', icon: 'check' },
  }).onOk(async () => {
    procesando.value = r.id + '_aceptar'
    try {
      await api.post(`/reservas/${r.id}/aceptar`)
      $q.notify({ type: 'positive', message: '¡Plan de vuelo aceptado! ¡Hasta el vuelo!' })
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al aceptar el plan de vuelo.' })
    } finally {
      procesando.value = null
    }
  })
}

function rechazar(r) {
  reservaSeleccionada.value = r
  motivoRechazo.value = ''
  dialogRechazo.value = true
}

async function confirmarRechazo() {
  const r = reservaSeleccionada.value
  procesando.value = r.id + '_rechazar'
  try {
    await api.post(`/reservas/${r.id}/rechazar`, { motivo: motivoRechazo.value || null })
    $q.notify({ type: 'info', message: 'Se notificó al instructor. Vuelo cancelado.' })
    dialogRechazo.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al rechazar el plan de vuelo.' })
  } finally {
    procesando.value = null
  }
}

/* ─── Helpers de formato ─── */

function tipoLabel(t) {
  return { instruccion: 'Instrucción de Vuelo', solo: 'Vuelo Solo', simulador: 'Simulador' }[t] ?? t
}

function estadoLabel(r) {
  if (r.confirmacion_estudiante === 'aceptada') return 'CONFIRMADO'
  if (r.confirmacion_estudiante === 'rechazada') return 'RECHAZADO'
  return 'PROGRAMADO'
}

function estadoColor(r) {
  if (r.confirmacion_estudiante === 'aceptada') return 'positive'
  if (r.confirmacion_estudiante === 'rechazada') return 'negative'
  return 'grey-7'
}

function headerStyle(r) {
  if (r.confirmacion_estudiante === 'aceptada')
    return 'background: linear-gradient(135deg,#166534,#15803d)'
  return 'background: linear-gradient(135deg,#7f1d1d,#A10B13)'
}

function nombreInstructor(r) {
  const p = r.instructor?.persona
  if (!p) return 'Sin asignar'
  return `${p.nombres ?? ''} ${p.apellidos ?? ''}`.trim()
}

function fmtFecha(d) {
  if (!d) return '—'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-CO',
    { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })
}

function fmtHora(h) {
  if (!h) return '—'
  return String(h).slice(0, 5)
}

onMounted(() => {
  cargar()
  timer = setInterval(tickCountdowns, 1000)
})

onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.vuelo-header { min-height: 80px; }
.countdown-box {
  background: rgba(147, 51, 234, 0.1);
  border: 1px solid rgba(147, 51, 234, 0.3);
}
</style>
