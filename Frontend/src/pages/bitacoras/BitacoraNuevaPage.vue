<template>
  <q-page padding style="padding-bottom:100px">

    <div class="row items-center q-gutter-md q-mb-lg">
      <q-btn flat round dense icon="arrow_back" color="grey-5" @click="$router.push('/bitacoras')" />
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 61 · RAC 91.417
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Nueva Bitácora de Vuelo</div>
      </div>
    </div>

    <q-form ref="formRef" @submit.prevent="guardar">
      <div class="row q-col-gutter-md">

        <!-- Columna izquierda: datos del vuelo -->
        <div class="col-12 col-md-6">

          <!-- Sección: quién y qué aeronave -->
          <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">PARTICIPANTES</div>

              <!-- Estudiante -->
              <div class="q-mb-md">
                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Estudiante *</div>
                <q-select v-model="form.estudiante_id" outlined dark bg-color="grey-10" dense
                  :options="estudiantesOpc" emit-value map-options use-input
                  :loading="cargandoEstudiantes" input-debounce="300"
                  @filter="filtrarEstudiantes"
                  @update:model-value="onEstudianteChange"
                  :rules="[v => !!v || 'Seleccione un estudiante']" lazy-rules>
                  <template #option="{ opt, itemProps }">
                    <q-item v-bind="itemProps">
                      <q-item-section>
                        <q-item-label>{{ opt.label }}</q-item-label>
                        <q-item-label caption class="font-mono">{{ opt.expediente }} · {{ opt.programa }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-icon v-if="!opt.medico_ok" name="warning" color="negative" size="16px">
                          <q-tooltip>Sin certificado médico vigente (RAC 67)</q-tooltip>
                        </q-icon>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>

                <!-- Alerta médico -->
                <div v-if="estudianteSeleccionado && !estudianteSeleccionado.medico_ok"
                  class="q-mt-xs q-pa-sm rounded-borders alert-critico" style="font-size:12px; border-radius:6px">
                  ⚠️ El estudiante no tiene certificado médico vigente (RAC 67). No puede volar.
                </div>
              </div>

              <!-- Instructor (opcional si es solo) -->
              <div class="q-mb-md">
                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Instructor (dejar vacío si es vuelo solo)</div>
                <q-select v-model="form.instructor_id" outlined dark bg-color="grey-10" dense
                  :options="instructoresOpc" emit-value map-options clearable
                  :loading="cargandoInstructores">
                </q-select>
              </div>

              <!-- Aeronave -->
              <div>
                <div style="font-size:11px;color:#64748b;margin-bottom:4px">Aeronave *</div>
                <q-select v-model="form.aeronave_id" outlined dark bg-color="grey-10" dense
                  :options="aeronavesOpc" emit-value map-options
                  :loading="cargandoAeronaves"
                  @update:model-value="onAeronaveChange"
                  :rules="[v => !!v || 'Seleccione una aeronave']" lazy-rules>
                  <template #option="{ opt, itemProps }">
                    <q-item v-bind="itemProps">
                      <q-item-section>
                        <q-item-label class="font-mono">{{ opt.label }}</q-item-label>
                        <q-item-label caption>{{ opt.modelo }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-chip dense :color="opt.estado === 'disponible' ? 'positive' : 'warning'"
                          text-color="white" :label="opt.estado" style="font-size:9px" />
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>

                <!-- Info aeronave seleccionada -->
                <div v-if="aeronaveSeleccionada" class="q-mt-xs q-pa-sm rounded-borders"
                  style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:6px">
                  <div class="row q-gutter-md">
                    <div>
                      <div style="font-size:10px;color:#64748b">TTAE</div>
                      <div class="font-mono text-primary" style="font-size:12px">{{ Number(aeronaveSeleccionada.horas_celula_total || 0).toFixed(0) }}h</div>
                    </div>
                    <div>
                      <div style="font-size:10px;color:#64748b">Venc. airworthiness</div>
                      <div class="font-mono" style="font-size:12px"
                        :style="`color:${diasAw > 30 ? '#22c55e' : '#ef4444'}`">
                        {{ aeronaveSeleccionada.venc_airworthiness }}
                      </div>
                    </div>
                    <div v-if="aeronaveSeleccionada.mel_abiertos_count > 0">
                      <div style="font-size:10px;color:#f59e0b">MEL abiertos</div>
                      <div class="font-mono text-warning" style="font-size:12px">{{ aeronaveSeleccionada.mel_abiertos_count }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Sección: fecha, hora, ruta -->
          <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">DATOS DEL VUELO</div>

              <div class="row q-col-gutter-sm">
                <div class="col-12">
                  <q-input v-model="form.fecha" outlined dark bg-color="grey-10" dense
                    type="date" label="Fecha del vuelo *"
                    :max="hoy"
                    :rules="[v => !!v || 'Requerido']" lazy-rules stack-label />
                </div>
                <div class="col-6">
                  <q-input v-model="form.hora_salida" outlined dark bg-color="grey-10" dense
                    type="time" label="Hora salida (UTC) *"
                    :rules="[v => !!v || 'Requerido']" lazy-rules stack-label />
                </div>
                <div class="col-6">
                  <q-input v-model="form.hora_llegada" outlined dark bg-color="grey-10" dense
                    type="time" label="Hora llegada (UTC) *"
                    :rules="[v => !!v || 'Requerido', v => v > form.hora_salida || 'Debe ser mayor a salida']"
                    lazy-rules stack-label />
                </div>
                <div class="col-6">
                  <q-input v-model="form.origen_icao" outlined dark bg-color="grey-10" dense
                    label="Origen ICAO *" maxlength="4"
                    :rules="[v => !!v && v.length === 4 || '4 caracteres']"
                    lazy-rules placeholder="SKBO" style="text-transform:uppercase"
                    @update:model-value="v => form.origen_icao = v.toUpperCase()" />
                </div>
                <div class="col-6">
                  <q-input v-model="form.destino_icao" outlined dark bg-color="grey-10" dense
                    label="Destino ICAO *" maxlength="4"
                    :rules="[v => !!v && v.length === 4 || '4 caracteres']"
                    lazy-rules placeholder="SKMD" style="text-transform:uppercase"
                    @update:model-value="v => form.destino_icao = v.toUpperCase()" />
                </div>
                <div class="col-6">
                  <q-select v-model="form.tipo_vuelo" outlined dark bg-color="grey-10" dense
                    label="Tipo de vuelo *" emit-value map-options
                    :options="tiposVuelo"
                    :rules="[v => !!v || 'Requerido']" lazy-rules />
                </div>
                <div class="col-6">
                  <q-input v-model.number="form.aterrizajes" outlined dark bg-color="grey-10" dense
                    type="number" label="Aterrizajes *" min="0"
                    :rules="[v => v >= 0 || 'Mínimo 0']" lazy-rules />
                </div>
                <div class="col-12">
                  <q-toggle v-model="form.condiciones_vmc" dark color="primary"
                    :label="`Condiciones: ${form.condiciones_vmc ? 'VMC (visual)' : 'IMC (instrumentos)'}`" />
                </div>
              </div>
            </q-card-section>
          </q-card>

        </div>

        <!-- Columna derecha: horas RAC 61 -->
        <div class="col-12 col-md-6">

          <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">
                HORAS DE VUELO — RAC 61
              </div>

              <!-- Horas totales -->
              <div class="q-mb-md q-pa-sm rounded-borders"
                style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:8px">
                <q-input v-model.number="form.horas_totales" outlined dark bg-color="transparent" dense
                  type="number" step="0.1" min="0.1" max="24"
                  label="Horas totales *"
                  :rules="[v => v > 0 || 'Debe ser mayor a 0', v => v <= 24 || 'Máximo 24h']"
                  lazy-rules>
                  <template #append>
                    <span class="font-mono text-primary" style="font-size:13px">h</span>
                  </template>
                </q-input>
              </div>

              <!-- Distribución de horas -->
              <div class="q-gutter-md">
                <div class="row q-col-gutter-sm">
                  <div class="col-6">
                    <q-input v-model.number="form.horas_dual" outlined dark bg-color="grey-10" dense
                      type="number" step="0.1" min="0" label="Horas dual (instrucción)"
                      :hint="`Quedan: ${Number(horasDisponibles || 0).toFixed(1)}h`"
                      :rules="[v => v >= 0 || 'Mínimo 0', v => (v + form.horas_solo) <= form.horas_totales + 0.05 || 'Dual + solo > total']"
                      lazy-rules>
                      <template #append><span class="font-mono" style="font-size:11px;color:#14b8a6">h</span></template>
                    </q-input>
                  </div>
                  <div class="col-6">
                    <q-input v-model.number="form.horas_solo" outlined dark bg-color="grey-10" dense
                      type="number" step="0.1" min="0" label="Horas solo"
                      :rules="[v => v >= 0 || 'Mínimo 0']" lazy-rules>
                      <template #append><span class="font-mono" style="font-size:11px;color:#f59e0b">h</span></template>
                    </q-input>
                  </div>
                  <div class="col-6">
                    <q-input v-model.number="form.horas_noche" outlined dark bg-color="grey-10" dense
                      type="number" step="0.1" min="0" label="Horas nocturnas"
                      :rules="[v => v >= 0 || 'Mínimo 0', v => v <= form.horas_totales || 'No puede superar total']"
                      lazy-rules>
                      <template #append><span class="font-mono" style="font-size:11px;color:#8b5cf6">h</span></template>
                    </q-input>
                  </div>
                  <div class="col-6">
                    <q-input v-model.number="form.horas_ifr" outlined dark bg-color="grey-10" dense
                      type="number" step="0.1" min="0" label="Horas IFR"
                      :rules="[v => v >= 0 || 'Mínimo 0', v => v <= form.horas_totales || 'No puede superar total']"
                      lazy-rules>
                      <template #append><span class="font-mono" style="font-size:11px;color:#ef4444">h</span></template>
                    </q-input>
                  </div>
                  <div class="col-12">
                    <q-input v-model.number="form.horas_simulador" outlined dark bg-color="grey-10" dense
                      type="number" step="0.1" min="0" label="Horas simulador (FTD/FFS)"
                      hint="Solo si el vuelo fue en simulador" lazy-rules>
                      <template #append><span class="font-mono" style="font-size:11px;color:#94a3b8">h</span></template>
                    </q-input>
                  </div>
                </div>
              </div>

              <!-- Resumen visual de horas -->
              <div class="q-mt-md q-pa-sm rounded-borders"
                style="background:rgba(255,255,255,.02);border-radius:8px">
                <div style="font-size:10px;color:#475569;margin-bottom:6px;font-family:monospace;letter-spacing:1px">
                  RESUMEN DE DISTRIBUCIÓN
                </div>
                <div class="row q-gutter-sm">
                  <div v-for="h in resumenHoras" :key="h.label" class="text-center"
                    style="min-width:50px">
                    <div class="font-mono" style="font-size:14px; font-weight:700"
                      :style="`color:${h.color}`">{{ Number(h.valor || 0).toFixed(1) }}</div>
                    <div style="font-size:9px; color:#475569; margin-top:1px">{{ h.label }}</div>
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>

          <!-- Observaciones -->
          <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-sm" style="font-size:10px;color:#475569;letter-spacing:2px">OBSERVACIONES</div>
              <q-input v-model="form.observaciones" outlined dark bg-color="grey-10" dense
                type="textarea" rows="4" label="Observaciones y maniobras del vuelo"
                placeholder="Circuitos, navegación, procedimientos realizados, condiciones del tiempo, incidentes..." />
            </q-card-section>
          </q-card>

          <!-- Errores RAC -->
          <q-card v-if="erroresRac.length" flat
            style="background:rgba(239,68,68,.07); border:1px solid rgba(239,68,68,.25); border-radius:12px; margin-bottom:16px">
            <q-card-section>
              <div class="font-head" style="font-size:14px; font-weight:700; color:#ef4444; margin-bottom:8px">
                Errores de validación RAC
              </div>
              <div v-for="e in erroresRac" :key="e" class="q-mb-xs" style="font-size:12px; color:#fca5a5">
                ✘ {{ e }}
              </div>
            </q-card-section>
          </q-card>

        </div>
      </div>

      <!-- Botones de acción -->
      <div class="row justify-end q-gutter-sm q-mt-md">
        <q-btn flat no-caps color="grey-5" label="Cancelar" @click="$router.push('/bitacoras')" style="border-radius:8px" />
        <q-btn type="submit" unelevated color="primary" label="Registrar bitácora"
          :loading="guardando" no-caps style="border-radius:8px; padding:10px 28px; font-weight:600" />
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
  estudiante_id:  null,
  instructor_id:  null,
  aeronave_id:    null,
  reserva_id:     null,
  fecha:          hoy,
  hora_salida:    '',
  hora_llegada:   '',
  origen_icao:    '',
  destino_icao:   '',
  horas_totales:  0,
  horas_dual:     0,
  horas_solo:     0,
  horas_noche:    0,
  horas_ifr:      0,
  horas_simulador:0,
  tipo_vuelo:     'local',
  condiciones_vmc: true,
  aterrizajes:    1,
  observaciones:  '',
})

const tiposVuelo = [
  { label: 'Vuelo local',      value: 'local'      },
  { label: 'Navegación',       value: 'navegacion' },
  { label: 'Vuelo nocturno',   value: 'noche'      },
  { label: 'Vuelo IFR',        value: 'ifr'        },
  { label: 'Simulador',        value: 'sim'        },
]

const horasDisponibles = computed(() =>
  Math.max(0, form.value.horas_totales - form.value.horas_dual - form.value.horas_solo)
)

const resumenHoras = computed(() => [
  { label: 'TOTAL', valor: form.value.horas_totales,  color: '#3b82f6' },
  { label: 'DUAL',  valor: form.value.horas_dual,     color: '#14b8a6' },
  { label: 'SOLO',  valor: form.value.horas_solo,     color: '#f59e0b' },
  { label: 'NOCHE', valor: form.value.horas_noche,    color: '#8b5cf6' },
  { label: 'IFR',   valor: form.value.horas_ifr,      color: '#ef4444' },
  { label: 'SIM',   valor: form.value.horas_simulador,color: '#64748b' },
])

const diasAw = computed(() =>
  aeronaveSeleccionada.value
    ? dayjs(aeronaveSeleccionada.value.venc_airworthiness).diff(dayjs(), 'day')
    : 999
)

function onEstudianteChange(id) {
  estudianteSeleccionado.value = estudiantesOpc.value.find(e => e.value === id) || null
}

// ── Auto-cálculo de horas (RAC 61) ───────────────────────────
watch([() => form.value.hora_salida, () => form.value.hora_llegada], ([s, l]) => {
  if (s && l) {
    const start = dayjs(`2000-01-01 ${s}`)
    const end   = dayjs(`2000-01-01 ${l}`)
    let diff = end.diff(start, 'minute')
    if (diff < 0) diff += 1440 // Cruce de medianoche
    form.value.horas_totales = Number((diff / 60).toFixed(1))
  }
})

function onAeronaveChange(id) {
  aeronaveSeleccionada.value = aeronavesOpc.value.find(a => a.value === id) || null
}

async function filtrarEstudiantes(val, update) {
  cargandoEstudiantes.value = true
  try {
    const { data } = await api.get('/estudiantes', {
      params: { buscar: val, estado: 'activo', per_page: 20 },
    })
    const lista = data.data?.data || data.data || []
    update(() => {
      estudiantesOpc.value = lista.map(e => ({
        label:     `${e.persona?.nombres} ${e.persona?.apellidos}`,
        value:     e.id,
        expediente:e.num_expediente,
        programa:  e.programa?.codigo,
        medico_ok: e.tiene_medico ?? true,
      }))
    })
  } finally { cargandoEstudiantes.value = false }
}

async function cargarInstructores() {
  cargandoInstructores.value = true
  const { data } = await api.get('/instructores', { params: { per_page: 100 } })
  instructoresOpc.value = (data.data?.data || data.data || []).map(i => ({
    label: `${i.persona?.nombres} ${i.persona?.apellidos} · ${i.num_licencia}`,
    value: i.id,
  }))
  cargandoInstructores.value = false
}

async function cargarAeronaves() {
  cargandoAeronaves.value = true
  const { data } = await api.get('/aeronaves')
  aeronavesOpc.value = (data.data || []).map(a => ({
    label:    a.matricula,
    value:    a.id,
    modelo:   a.modelo,
    estado:   a.estado,
    horas_celula_total: a.horas_celula_total,
    venc_airworthiness: a.venc_airworthiness,
    mel_abiertos_count: a.mel_abiertos?.length || 0,
  }))
  cargandoAeronaves.value = false
}

async function guardar() {
  const valid = await formRef.value?.validate()
  if (!valid) return

  // Validación extra RAC
  erroresRac.value = []
  if (estudianteSeleccionado.value && !estudianteSeleccionado.value.medico_ok) {
    erroresRac.value.push('El estudiante no tiene certificado médico vigente (RAC 67).')
  }
  if (form.value.horas_dual + form.value.horas_solo > form.value.horas_totales + 0.05) {
    erroresRac.value.push('La suma de horas dual + solo no puede superar las horas totales.')
  }
  if (form.value.origen_icao.length !== 4) erroresRac.value.push('El código ICAO de origen debe tener 4 caracteres.')
  if (form.value.destino_icao.length !== 4) erroresRac.value.push('El código ICAO de destino debe tener 4 caracteres.')
  if (aeronaveSeleccionada.value && diasAw.value <= 0) {
    erroresRac.value.push('La aeronave tiene el certificado de aeronavegabilidad vencido (RAC 141.51).')
  }
  if (erroresRac.value.length) return

  guardando.value = true
  try {
    await api.post('/bitacoras', {
      ...form.value,
      horas_totales:   parseFloat(form.value.horas_totales   || 0),
      horas_dual:      parseFloat(form.value.horas_dual      || 0),
      horas_solo:      parseFloat(form.value.horas_solo      || 0),
      horas_noche:     parseFloat(form.value.horas_noche     || 0),
      horas_ifr:       parseFloat(form.value.horas_ifr       || 0),
      horas_simulador: parseFloat(form.value.horas_simulador || 0),
      aterrizajes:     parseInt(form.value.aterrizajes       || 1),
    })

    $q.notify({
      type: 'positive',
      message: '✔ Bitácora registrada correctamente (RAC 91.417).',
      icon: 'flight',
      timeout: 5000,
    })
    router.push('/vuelo')
  } catch (e) {
    const errores = e.response?.data?.errors || {}
    erroresRac.value = Object.values(errores).flat()
    if (!erroresRac.value.length) {
      erroresRac.value = [e.response?.data?.mensaje || 'Error al registrar la bitácora.']
    }
    $q.notify({ type: 'negative', message: 'Error de validación. Revise los campos marcados.' })
  } finally { guardando.value = false }
}

onMounted(() => { cargarInstructores(); cargarAeronaves() })
</script>
