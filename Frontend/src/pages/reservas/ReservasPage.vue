<template>
  <q-page padding style="padding-bottom:80px">

    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 141 · Scheduling
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Reservas de Vuelo</div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn flat no-caps color="grey-5" icon="calendar_month" label="Calendario"
          @click="$router.push('/calendario')" style="border-radius:8px" />
        <q-btn unelevated color="primary" icon="add" label="Nueva reserva" no-caps
          @click="dialogNueva=true" style="border-radius:8px" />
      </div>
    </div>

    <!-- Filtros -->
    <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
      <q-card-section>
        <div class="row q-col-gutter-sm items-end">
          <div class="col-6 col-sm-3">
            <q-input v-model="filtros.fecha" type="date" outlined dense dark bg-color="grey-10"
              label="Fecha" stack-label @update:model-value="cargar" />
          </div>
          <div class="col-6 col-sm-3">
            <q-select v-model="filtros.estado" outlined dense dark bg-color="grey-10"
              :options="opcionesEstado" emit-value map-options clearable label="Estado" stack-label
              @update:model-value="cargar" />
          </div>
          <div class="col-12 col-sm-3">
            <q-select v-model="filtros.tipo" outlined dense dark bg-color="grey-10"
              :options="opcionesTipo" emit-value map-options clearable label="Tipo" stack-label
              @update:model-value="cargar" />
          </div>
          <div class="col-12 col-sm-3">
            <q-btn flat no-caps color="grey-6" icon="today" label="Hoy"
              @click="filtros.fecha=hoy; cargar()" />
            <q-btn flat no-caps color="grey-6" icon="filter_alt_off" label="Limpiar"
              @click="limpiarFiltros" />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Lista de reservas -->
    <q-list dark separator style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px">

      <q-item-label header style="color:#475569;font-size:11px;font-family:monospace;letter-spacing:1px;padding:12px 16px">
        {{ reservas.length }} reservas · {{ filtros.fecha || 'Todas las fechas' }}
      </q-item-label>

      <q-skeleton v-if="cargando" v-for="n in 5" :key="n" type="QItem" dark class="q-mb-xs" />

      <q-item v-for="r in reservas" :key="r.id" style="padding:14px 16px" clickable
        @click="verDetalle(r)">
        <q-item-section avatar>
          <div style="width:52px;text-align:center">
            <div class="font-mono text-primary" style="font-size:15px;font-weight:700">{{ r.hora_inicio }}</div>
            <div style="height:1px;background:rgba(255,255,255,.1);margin:3px 0"></div>
            <div class="font-mono" style="font-size:13px;color:#64748b">{{ r.hora_fin }}</div>
          </div>
        </q-item-section>

        <q-item-section>
          <q-item-label style="font-size:14px;color:#e2e8f0;font-weight:500">
            {{ r.aeronave?.matricula }} · {{ r.aeronave?.modelo }}
          </q-item-label>
          <q-item-label caption style="font-size:12px;margin-top:3px">
            <span style="color:#94a3b8">
              {{ r.estudiante?.persona?.nombres }} {{ r.estudiante?.persona?.apellidos }}
            </span>
            <span v-if="r.instructor" style="color:#64748b">
              · Instr: {{ r.instructor?.persona?.nombres }}
            </span>
          </q-item-label>
        </q-item-section>

        <q-item-section side>
          <div class="column items-end q-gutter-xs">
            <q-chip dense :color="colorEstadoReserva(r.estado)" text-color="white"
              :label="r.estado" style="font-size:10px;text-transform:capitalize" />
            <q-chip dense :color="colorTipoReserva(r.tipo)" text-color="white"
              :label="r.tipo" style="font-size:10px" />
          </div>
        </q-item-section>
      </q-item>

      <q-item v-if="!cargando && !reservas.length">
        <q-item-section class="text-center q-py-xl" style="color:#475569">
          <q-icon name="event_busy" size="40px" class="q-mb-sm" />
          <div>Sin reservas para esta búsqueda</div>
        </q-item-section>
      </q-item>
    </q-list>

    <!-- Dialog: Nueva Reserva -->
    <q-dialog v-model="dialogNueva" persistent>
      <q-card dark style="background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px;min-width:360px;max-width:520px;width:100%">
        <q-card-section style="border-bottom:1px solid rgba(255,255,255,.07)">
          <div class="row items-center justify-between">
            <div class="font-head text-white" style="font-size:17px;font-weight:700">Nueva Reserva</div>
            <q-btn flat round dense icon="close" color="grey-5" @click="cerrarDialog" />
          </div>
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <q-form @submit.prevent="crearReserva" class="q-gutter-md">

            <q-input v-model="form.fecha" type="date" outlined dark bg-color="grey-10"
              label="Fecha del vuelo" stack-label :rules="[v=>!!v||'Requerido']" />

            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-input v-model="form.hora_inicio" type="time" outlined dark bg-color="grey-10"
                  label="Hora inicio" stack-label :rules="[v=>!!v||'Requerido']" />
              </div>
              <div class="col-6">
                <q-input v-model="form.hora_fin" type="time" outlined dark bg-color="grey-10"
                  label="Hora fin" stack-label :rules="[v=>!!v||'Requerido']" />
              </div>
            </div>

            <q-select v-model="form.aeronave_id" outlined dark bg-color="grey-10"
              :options="opcionesAeronaves" option-value="id" option-label="label"
              emit-value map-options label="Aeronave" stack-label
              :rules="[v=>!!v||'Seleccione una aeronave']">
              <template #prepend><q-icon name="flight" color="grey-6" size="18px"/></template>
            </q-select>

            <q-select v-model="form.tipo" outlined dark bg-color="grey-10"
              :options="opcionesTipo" emit-value map-options label="Tipo de vuelo" stack-label
              :rules="[v=>!!v||'Requerido']" />

            <!-- Errores de validación RAC -->
            <div v-if="erroresRac.length">
              <q-banner v-for="(e,i) in erroresRac" :key="i" dense rounded
                style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:8px;margin-bottom:4px">
                <template #avatar><q-icon name="error_outline" color="negative" size="16px"/></template>
                <span style="font-size:12px;color:#fca5a5">{{ e }}</span>
              </q-banner>
            </div>

            <div class="row q-gutter-sm justify-end q-mt-sm">
              <q-btn flat no-caps label="Cancelar" color="grey-5" @click="cerrarDialog" />
              <q-btn unelevated color="primary" no-caps label="Crear reserva"
                type="submit" :loading="creando" style="border-radius:8px" />
            </div>

          </q-form>
        </q-card-section>
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
  { label: 'Pendiente',  value: 'pendiente' },
  { label: 'Confirmada', value: 'confirmada' },
  { label: 'Completada', value: 'completada' },
  { label: 'Cancelada',  value: 'cancelada' },
]
const opcionesTipo = [
  { label: 'Instrucción', value: 'instruccion' },
  { label: 'Solo',        value: 'solo' },
  { label: 'Simulador',   value: 'simulador' },
]

const opcionesAeronaves = computed(() =>
  aeronaves.value
    .filter(a => a.estado === 'disponible')
    .map(a => ({ id: a.id, label: `${a.matricula} · ${a.modelo}` }))
)

const colorEstadoReserva = (e) => ({ pendiente:'warning', confirmada:'positive', completada:'teal', cancelada:'grey-7' }[e]||'grey')
const colorTipoReserva   = (t) => ({ instruccion:'primary', solo:'purple', simulador:'grey-8' }[t]||'grey')

function verDetalle(r) {
  // TODO: Dialog de detalle / opciones
}

function limpiarFiltros() {
  filtros.value = { fecha: '', estado: null, tipo: null }
  cargar()
}

function cerrarDialog() {
  dialogNueva.value = false
  erroresRac.value = []
  form.value = { fecha: hoy, hora_inicio: '', hora_fin: '', aeronave_id: null, tipo: 'instruccion' }
}

async function cargar() {
  cargando.value = true
  try {
    const params = {}
    if (filtros.value.fecha)  params.fecha  = filtros.value.fecha
    if (filtros.value.estado) params.estado = filtros.value.estado
    if (filtros.value.tipo)   params.tipo   = filtros.value.tipo
    const { data } = await api.get('/reservas', { params })
    reservas.value = data.data.data || []
  } finally { cargando.value = false }
}

async function cargarAeronaves() {
  const { data } = await api.get('/aeronaves?estado=disponible')
  aeronaves.value = data.data || []
}

async function crearReserva() {
  erroresRac.value = []
  creando.value = true
  try {
    const payload = {
      ...form.value,
      estudiante_id: authStore.esEstudiante
        ? null  // backend lo resuelve desde el token
        : form.value.estudiante_id,
    }
    await api.post('/reservas', payload)
    $q.notify({ type: 'positive', message: 'Reserva creada correctamente.' })
    cerrarDialog()
    cargar()
  } catch (e) {
    if (e.response?.status === 422) {
      erroresRac.value = e.response.data.errores || []
    }
  } finally { creando.value = false }
}

onMounted(() => { cargar(); cargarAeronaves() })
</script>
