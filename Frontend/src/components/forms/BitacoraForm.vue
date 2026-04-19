<template>
  <q-form ref="formRef" @submit.prevent="$emit('submit', formData)" greedy>
    <div class="row q-col-gutter-md">

      <!-- ── COLUMNA IZQUIERDA: Datos del vuelo ── -->
      <div class="col-12 col-md-6">

        <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
          <q-card-section>
            <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">
              DATOS DEL VUELO
            </div>

            <!-- Aeronave -->
            <div class="q-mb-md">
              <div class="campo-label">Aeronave *</div>
              <q-select
                v-model="formData.aeronave_id"
                :options="aeronaves"
                option-value="id"
                :option-label="av => `${av.matricula} · ${av.modelo}`"
                emit-value map-options outlined dense dark bg-color="grey-10"
                :rules="[v => !!v || 'Seleccione una aeronave']"
                lazy-rules
                @update:model-value="verificarAeronave"
              >
                <template #prepend><q-icon name="flight" color="grey-6" size="18px" /></template>
                <template #option="{ itemProps, opt }">
                  <q-item v-bind="itemProps">
                    <q-item-section>
                      <q-item-label class="font-mono">{{ opt.matricula }}</q-item-label>
                      <q-item-label caption>{{ opt.modelo }}</q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-chip dense :color="opt.estado === 'disponible' ? 'positive' : 'warning'"
                        text-color="white" :label="opt.estado" style="font-size:9px" />
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
              <!-- Alerta airworthiness -->
              <div v-if="aeronaveSeleccionada && !aeronaveSeleccionada.airworthiness_vigente"
                class="q-mt-xs" style="font-size:11px;color:#ef4444">
                ⚠ Aeronavegabilidad vencida — RAC 141.51
              </div>
            </div>

            <!-- Fecha -->
            <div class="q-mb-md">
              <div class="campo-label">Fecha del vuelo *</div>
              <q-input v-model="formData.fecha" type="date" outlined dense dark bg-color="grey-10"
                :max="hoy" :rules="[v => !!v || 'Requerido']" lazy-rules />
            </div>

            <!-- Horario -->
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-6">
                <div class="campo-label">Hora salida (UTC) *</div>
                <q-input v-model="formData.hora_salida" type="time" outlined dense dark bg-color="grey-10"
                  :rules="[v => !!v || 'Requerido']" lazy-rules />
              </div>
              <div class="col-6">
                <div class="campo-label">Hora llegada (UTC) *</div>
                <q-input v-model="formData.hora_llegada" type="time" outlined dense dark bg-color="grey-10"
                  :rules="[v => !!v || 'Requerido', v => v > formData.hora_salida || 'Debe ser después de salida']"
                  lazy-rules />
              </div>
            </div>

            <!-- Ruta -->
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-6">
                <div class="campo-label">Origen (ICAO) *</div>
                <q-input v-model="formData.origen_icao" outlined dense dark bg-color="grey-10"
                  placeholder="SKBO" maxlength="4"
                  :rules="[v => /^[A-Za-z]{4}$/.test(v) || 'Código ICAO 4 letras']"
                  lazy-rules @update:model-value="v => formData.origen_icao = v.toUpperCase()">
                  <template #prepend><q-icon name="flight_takeoff" color="grey-6" size="16px" /></template>
                </q-input>
              </div>
              <div class="col-6">
                <div class="campo-label">Destino (ICAO) *</div>
                <q-input v-model="formData.destino_icao" outlined dense dark bg-color="grey-10"
                  placeholder="SKMD" maxlength="4"
                  :rules="[v => /^[A-Za-z]{4}$/.test(v) || 'Código ICAO 4 letras']"
                  lazy-rules @update:model-value="v => formData.destino_icao = v.toUpperCase()">
                  <template #prepend><q-icon name="flight_land" color="grey-6" size="16px" /></template>
                </q-input>
              </div>
            </div>

            <!-- Tipo de vuelo y condiciones -->
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-7">
                <div class="campo-label">Tipo de vuelo *</div>
                <q-select v-model="formData.tipo_vuelo" :options="tiposVuelo" emit-value map-options
                  outlined dense dark bg-color="grey-10"
                  :rules="[v => !!v || 'Seleccione tipo']" lazy-rules />
              </div>
              <div class="col-5">
                <div class="campo-label">Condiciones</div>
                <div class="row items-center q-mt-sm q-gutter-sm">
                  <q-btn-toggle
                    v-model="formData.condiciones_vmc"
                    :options="[{label:'VMC',value:true},{label:'IMC',value:false}]"
                    no-caps dense unelevated
                    toggle-color="primary" color="grey-9" text-color="grey-5"
                    style="border-radius:6px"
                  />
                </div>
              </div>
            </div>

            <!-- Aterrizajes -->
            <div class="q-mb-md">
              <div class="campo-label">Número de aterrizajes *</div>
              <q-input v-model.number="formData.aterrizajes" type="number" min="1" max="99"
                outlined dense dark bg-color="grey-10"
                :rules="[v => v >= 1 || 'Mínimo 1 aterrizaje']" lazy-rules>
                <template #prepend><q-icon name="flight_land" color="grey-6" size="16px" /></template>
              </q-input>
            </div>

            <!-- Instructor -->
            <div class="q-mb-md">
              <div class="campo-label">Instructor (si aplica)</div>
              <q-select v-model="formData.instructor_id" :options="instructores"
                option-value="id"
                :option-label="i => `${i.persona?.nombres} ${i.persona?.apellidos}`"
                emit-value map-options outlined dense dark bg-color="grey-10"
                clearable placeholder="Vuelo solo si no aplica">
                <template #prepend><q-icon name="badge" color="grey-6" size="16px" /></template>
              </q-select>
            </div>

          </q-card-section>
        </q-card>

        <!-- Observaciones -->
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="campo-label q-mb-sm">Observaciones del vuelo</div>
            <q-input v-model="formData.observaciones" type="textarea" outlined dark bg-color="grey-10"
              placeholder="Condiciones meteorológicas, maniobras practicadas, novedades…"
              rows="3" dense />
          </q-card-section>
        </q-card>

      </div>

      <!-- ── COLUMNA DERECHA: Horas por categoría ── -->
      <div class="col-12 col-md-6">

        <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-mono" style="font-size:10px;color:#475569;letter-spacing:2px">
                DISTRIBUCIÓN DE HORAS RAC 61
              </div>
              <!-- Total calculado automáticamente -->
              <div class="row items-center q-gutter-xs">
                <div class="font-mono" style="font-size:10px;color:#64748b">TOTAL:</div>
                <div class="font-mono" :style="`font-size:14px;font-weight:700;color:${colorTotal}`">
                  {{ horasTotal.toFixed(1) }}h
                </div>
              </div>
            </div>

            <!-- Horas totales (campo principal) -->
            <div class="q-mb-lg">
              <div class="campo-label">Horas totales del vuelo *</div>
              <q-input v-model.number="formData.horas_totales" type="number" step="0.1" min="0.1" max="24"
                outlined dense dark bg-color="grey-10"
                :rules="[v => v > 0 || 'Requerido', v => v <= 24 || 'Máx 24h']" lazy-rules>
                <template #prepend><q-icon name="schedule" color="primary" size="18px" /></template>
                <template #append>
                  <span class="font-mono" style="font-size:12px;color:#64748b">h</span>
                </template>
              </q-input>
              <!-- Barra visual del total -->
              <div class="q-mt-xs" style="height:4px;background:rgba(255,255,255,.06);border-radius:2px">
                <div :style="`width:${Math.min((formData.horas_totales/8)*100,100)}%;height:100%;background:#3b82f6;border-radius:2px;transition:width .3s`"></div>
              </div>
            </div>

            <!-- Subcategorías -->
            <div class="q-gutter-md">

              <div v-for="cat in categorias" :key="cat.key">
                <div class="row items-center justify-between q-mb-xs">
                  <div class="row items-center q-gutter-xs">
                    <q-icon :name="cat.icono" color="grey-6" size="14px" />
                    <span style="font-size:12px;color:#94a3b8">{{ cat.label }}</span>
                  </div>
                  <span class="font-mono" style="font-size:11px"
                    :style="`color:${(formData[cat.key]||0) > 0 ? '#60a5fa' : '#334155'}`">
                    {{ (formData[cat.key] || 0).toFixed(1) }}h
                  </span>
                </div>
                <q-slider
                  v-model="formData[cat.key]"
                  :min="0" :max="formData.horas_totales || 8"
                  :step="0.1" :color="cat.color"
                  track-color="grey-9"
                  style="padding:0"
                />
              </div>

            </div>

            <!-- Validación: suma de subcategorías -->
            <div v-if="errorHoras" class="q-mt-md q-pa-sm rounded-borders"
              style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:8px">
              <div style="font-size:12px;color:#fca5a5">
                ⚠ La suma de horas dual + solo ({{ sumaDualSolo.toFixed(1) }}h)
                supera las horas totales ({{ (formData.horas_totales||0).toFixed(1) }}h)
              </div>
            </div>

            <!-- Preview de acumulado del estudiante -->
            <div v-if="progresoActual" class="q-mt-lg">
              <div class="font-mono q-mb-sm" style="font-size:10px;color:#475569;letter-spacing:2px">
                ACUMULADO DEL ESTUDIANTE DESPUÉS DE ESTE VUELO
              </div>
              <div class="row q-col-gutter-sm">
                <div class="col-6" v-for="item in previewAcumulado" :key="item.label">
                  <div style="font-size:10px;color:#64748b;margin-bottom:2px">{{ item.label }}</div>
                  <div class="row items-center q-gutter-xs">
                    <span class="font-mono" style="font-size:12px;color:#60a5fa">{{ item.nuevo.toFixed(1) }}h</span>
                    <span style="font-size:10px;color:#475569">/ {{ item.req.toFixed(1) }}h</span>
                    <q-icon v-if="item.nuevo >= item.req" name="check_circle" color="positive" size="12px" />
                  </div>
                  <q-linear-progress
                    :value="Math.min(item.nuevo / item.req, 1)"
                    :color="item.nuevo >= item.req ? 'positive' : 'primary'"
                    track-color="grey-9" size="4px" rounded class="q-mt-xs"
                  />
                </div>
              </div>
            </div>

          </q-card-section>
        </q-card>

        <!-- Reserva vinculada (si viene de una reserva) -->
        <q-card v-if="reservaId" flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center q-gutter-sm">
              <q-icon name="event" color="teal" size="18px" />
              <div>
                <div style="font-size:13px;color:#e2e8f0;font-weight:500">
                  Vinculada a reserva #{{ reservaId }}
                </div>
                <div style="font-size:11px;color:#64748b">
                  La reserva se marcará como completada al guardar
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>

      </div>

    </div>

    <!-- Botones de acción -->
    <div class="row justify-end q-gutter-sm q-mt-md">
      <q-btn flat label="Cancelar" no-caps color="grey-5"
        @click="$emit('cancelar')" style="border-radius:8px" />
      <q-btn unelevated color="primary" label="Registrar bitácora" no-caps type="submit"
        :loading="loading" :disable="errorHoras || loading"
        style="border-radius:8px;padding:8px 24px">
        <template #loading>
          <q-spinner-tail color="white" size="1em" />
        </template>
      </q-btn>
    </div>

  </q-form>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const props = defineProps({
  estudianteId: { type: [Number, String], default: null },
  reservaId:    { type: [Number, String], default: null },
  loading:      { type: Boolean, default: false },
})

const emit = defineEmits(['submit', 'cancelar'])

const formRef = ref(null)
const aeronaves = ref([])
const instructores = ref([])
const progresoActual = ref(null)
const aeronaveSeleccionada = ref(null)

const hoy = dayjs().format('YYYY-MM-DD')

const formData = ref({
  estudiante_id:   props.estudianteId,
  aeronave_id:     null,
  instructor_id:   null,
  reserva_id:      props.reservaId,
  fecha:           hoy,
  hora_salida:     '',
  hora_llegada:    '',
  origen_icao:     '',
  destino_icao:    '',
  horas_totales:   0,
  horas_dual:      0,
  horas_solo:      0,
  horas_noche:     0,
  horas_ifr:       0,
  horas_simulador: 0,
  tipo_vuelo:      'local',
  condiciones_vmc: true,
  aterrizajes:     1,
  observaciones:   '',
})

const tiposVuelo = [
  { label: 'Vuelo local',     value: 'local'      },
  { label: 'Navegación',      value: 'navegacion'  },
  { label: 'Vuelo nocturno',  value: 'noche'       },
  { label: 'IFR',             value: 'ifr'         },
  { label: 'Simulador (FTD)', value: 'sim'         },
]

const categorias = [
  { key: 'horas_dual',      label: 'Instrucción dual',  icono: 'supervisor_account', color: 'primary' },
  { key: 'horas_solo',      label: 'Vuelo solo',        icono: 'person',             color: 'teal'    },
  { key: 'horas_noche',     label: 'Vuelo nocturno',    icono: 'nights_stay',        color: 'purple'  },
  { key: 'horas_ifr',       label: 'Instrumentos IFR',  icono: 'radar',              color: 'amber'   },
  { key: 'horas_simulador', label: 'Simulador',         icono: 'computer',           color: 'grey'    },
]

// Horas totales según sliders
const horasTotal = computed(() =>
  Math.max(
    formData.value.horas_totales || 0,
    formData.value.horas_dual + formData.value.horas_solo
  )
)

const sumaDualSolo = computed(() =>
  (formData.value.horas_dual || 0) + (formData.value.horas_solo || 0)
)

const errorHoras = computed(() =>
  sumaDualSolo.value > (formData.value.horas_totales || 0) + 0.05
)

const colorTotal = computed(() =>
  errorHoras.value ? '#ef4444' : '#22c55e'
)

// Preview de acumulado después de este vuelo
const previewAcumulado = computed(() => {
  if (!progresoActual.value) return []
  const prog = progresoActual.value.categorias || {}
  return [
    {
      label: 'Total', req: prog.vuelo_total?.requerido || 0,
      nuevo: (prog.vuelo_total?.acumulado || 0) + (formData.value.horas_totales || 0),
    },
    {
      label: 'Dual', req: prog.dual?.requerido || 0,
      nuevo: (prog.dual?.acumulado || 0) + (formData.value.horas_dual || 0),
    },
    {
      label: 'Solo', req: prog.solo?.requerido || 0,
      nuevo: (prog.solo?.acumulado || 0) + (formData.value.horas_solo || 0),
    },
    {
      label: 'Noche', req: prog.noche?.requerido || 0,
      nuevo: (prog.noche?.acumulado || 0) + (formData.value.horas_noche || 0),
    },
    {
      label: 'IFR', req: prog.ifr?.requerido || 0,
      nuevo: (prog.ifr?.acumulado || 0) + (formData.value.horas_ifr || 0),
    },
  ].filter(i => i.req > 0)
})

// Cuando cambia tipo de vuelo, ajustar automáticamente
watch(() => formData.value.tipo_vuelo, (tipo) => {
  if (tipo === 'noche' && formData.value.horas_totales > 0) {
    formData.value.horas_noche = formData.value.horas_totales
  }
  if (tipo === 'ifr' && formData.value.horas_totales > 0) {
    formData.value.horas_ifr = formData.value.horas_totales
  }
  if (tipo === 'sim' && formData.value.horas_totales > 0) {
    formData.value.horas_simulador = formData.value.horas_totales
    formData.value.horas_dual = 0
    formData.value.horas_solo = 0
  }
})

function verificarAeronave(id) {
  aeronaveSeleccionada.value = aeronaves.value.find(a => a.id === id) || null
}

async function cargarDatos() {
  const [avRes, instRes] = await Promise.all([
    api.get('/aeronaves?estado=disponible'),
    api.get('/instructores?activo=1'),
  ])
  aeronaves.value    = avRes.data.data || []
  instructores.value = instRes.data.data?.data || instRes.data.data || []

  // Cargar progreso del estudiante para preview
  if (props.estudianteId) {
    try {
      const { data } = await api.get(`/estudiantes/${props.estudianteId}/progreso-horas`)
      progresoActual.value = data.data
    } catch {}
  }
}

onMounted(cargarDatos)
</script>

<style scoped>
.campo-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 10px;
  color: #64748b;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 6px;
}
</style>
