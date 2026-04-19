<template>
  <q-page class="rac-page">

    <div class="page-header q-mb-lg">
      <q-btn flat round dense icon="arrow_back" to="/bitacoras" class="q-mr-sm" />
      <div>
        <h1 class="page-title">Nueva Bitácora de Vuelo</h1>
        <div class="page-sub mono" style="font-size:11px;color:rgba(255,255,255,.4)">RAC 91.417 · RAC 141.71</div>
      </div>
    </div>

    <q-form @submit.prevent="guardar" greedy>
      <div class="form-grid">

        <!-- ── Col 1: Datos del vuelo ─────────────────────────────── -->
        <div class="form-col">
          <div class="form-section-title">Datos del vuelo</div>

          <q-select
            v-model="form.estudiante_id"
            :options="estudiantesOpts"
            option-value="value"
            option-label="label"
            emit-value map-options
            label="Estudiante *"
            outlined dense dark class="q-mb-sm"
            use-input input-debounce="300"
            @filter="filtrarEstudiantes"
            :rules="[v => !!v || 'Requerido']"
          />

          <q-select
            v-model="form.aeronave_id"
            :options="aeronavesOpts"
            option-value="value"
            option-label="label"
            emit-value map-options
            label="Aeronave *"
            outlined dense dark class="q-mb-sm"
            :rules="[v => !!v || 'Requerido']"
          />

          <q-input
            v-model="form.fecha"
            label="Fecha *"
            type="date"
            outlined dense dark class="q-mb-sm"
            :max="hoy"
            :rules="[v => !!v || 'Requerido']"
          />

          <div class="row q-gutter-sm q-mb-sm">
            <q-input v-model="form.hora_salida"  label="Salida (UTC) *" type="time" outlined dense dark class="col" :rules="[v => !!v || 'Req']" />
            <q-input v-model="form.hora_llegada" label="Llegada (UTC) *" type="time" outlined dense dark class="col" :rules="[v => !!v || 'Req']" />
          </div>

          <div class="row q-gutter-sm q-mb-sm">
            <q-input v-model="form.origen_icao"  label="Origen ICAO *" maxlength="4" outlined dense dark class="col" style="text-transform:uppercase" :rules="[v => v?.length === 4 || '4 chars']" />
            <q-input v-model="form.destino_icao" label="Destino ICAO *" maxlength="4" outlined dense dark class="col" style="text-transform:uppercase" :rules="[v => v?.length === 4 || '4 chars']" />
          </div>

          <q-select
            v-model="form.tipo_vuelo"
            :options="tiposVuelo"
            option-value="value" option-label="label"
            emit-value map-options
            label="Tipo de vuelo *"
            outlined dense dark class="q-mb-sm"
          />

          <div class="row q-gutter-sm q-mb-sm">
            <q-toggle v-model="form.condiciones_vmc" label="VMC" color="positive" class="col-auto" />
            <q-input v-model.number="form.aterrizajes" label="Aterrizajes" type="number" min="0" outlined dense dark class="col" />
          </div>
        </div>

        <!-- ── Col 2: Horas RAC 61 ────────────────────────────────── -->
        <div class="form-col">
          <div class="form-section-title">
            Horas de vuelo
            <span class="mono" style="font-size:10px;color:rgba(255,255,255,.4);margin-left:8px">RAC 61</span>
          </div>

          <div class="hora-total-display q-mb-md">
            <div class="hora-total-val">{{ form.horas_totales || '0.0' }}h</div>
            <div class="hora-total-lbl">Total del vuelo</div>
          </div>

          <q-input v-model.number="form.horas_totales"  label="Horas totales *"   type="number" step="0.1" min="0" outlined dense dark class="q-mb-xs" :rules="[v => v > 0 || 'Requerido']" />
          <q-input v-model.number="form.horas_dual"     label="Horas dual"        type="number" step="0.1" min="0" outlined dense dark class="q-mb-xs" />
          <q-input v-model.number="form.horas_solo"     label="Horas solo"        type="number" step="0.1" min="0" outlined dense dark class="q-mb-xs" />
          <q-input v-model.number="form.horas_noche"    label="Horas nocturnas"   type="number" step="0.1" min="0" outlined dense dark class="q-mb-xs" />
          <q-input v-model.number="form.horas_ifr"      label="Horas IFR"         type="number" step="0.1" min="0" outlined dense dark class="q-mb-xs" />
          <q-input v-model.number="form.horas_simulador" label="Horas simulador"  type="number" step="0.1" min="0" outlined dense dark class="q-mb-sm" />

          <!-- Validación en tiempo real -->
          <div v-if="errorHoras" class="alert-vencimiento critico q-mb-sm">
            <q-icon name="error" size="15px" />
            <span style="font-size:12px">{{ errorHoras }}</span>
          </div>

          <div class="form-section-title q-mt-md">Observaciones</div>
          <q-input
            v-model="form.observaciones"
            type="textarea"
            label="Notas de la sesión..."
            outlined dense dark
            rows="4"
            class="q-mb-sm"
          />
        </div>

      </div>

      <!-- Botones -->
      <div class="form-actions q-mt-lg">
        <q-btn flat label="Cancelar" to="/bitacoras" color="grey-5" />
        <q-btn
          type="submit"
          label="Registrar bitácora"
          icon="save"
          color="primary"
          unelevated
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
  estudiante_id: null,
  instructor_id: null,
  aeronave_id:   null,
  reserva_id:    null,
  fecha:         hoy,
  hora_salida:   '',
  hora_llegada:  '',
  origen_icao:   '',
  destino_icao:  '',
  horas_totales:  0,
  horas_dual:     0,
  horas_solo:     0,
  horas_noche:    0,
  horas_ifr:      0,
  horas_simulador:0,
  tipo_vuelo:    'local',
  condiciones_vmc: true,
  aterrizajes:   1,
  observaciones: '',
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
  const p = form.value.horas_dual + form.value.horas_solo
  if (p > form.value.horas_totales + 0.05) {
    return `Dual (${form.value.horas_dual}h) + Solo (${form.value.horas_solo}h) supera el total (${form.value.horas_totales}h)`
  }
  return null
})

function filtrarEstudiantes(val, update) {
  update(() => {
    const q = val.toLowerCase()
    estudiantesOpts.value = todosEstudiantes.value.filter(e =>
      e.label.toLowerCase().includes(q)
    )
  })
}

async function guardar() {
  if (errorHoras.value) return
  guardando.value = true
  try {
    const payload = {
      ...form.value,
      origen_icao:  form.value.origen_icao.toUpperCase(),
      destino_icao: form.value.destino_icao.toUpperCase(),
    }
    await api.post('/bitacoras', payload)
    $q.notify({ type: 'positive', message: 'Bitácora registrada correctamente (RAC 91.417).' })
    router.push('/bitacoras')
  } catch (err) {
    const msg = err.response?.data?.mensaje || 'Error al guardar la bitácora.'
    $q.notify({ type: 'negative', message: msg })
  } finally {
    guardando.value = false
  }
}

onMounted(async () => {
  try {
    const [estRes, aerRes] = await Promise.all([
      api.get('/estudiantes?estado=activo&per_page=100'),
      api.get('/aeronaves?estado=disponible'),
    ])
    todosEstudiantes.value = estRes.data.data.data.map(e => ({
      value: e.id,
      label: `${e.persona?.nombres} ${e.persona?.apellidos} — ${e.num_expediente}`,
    }))
    estudiantesOpts.value = [...todosEstudiantes.value]
    aeronavesOpts.value   = aerRes.data.data.map(a => ({
      value: a.id,
      label: `${a.matricula} · ${a.modelo}`,
    }))
  } catch (e) {
    console.error(e)
  }
})
</script>

<style lang="scss" scoped>
.rac-page { padding: 24px; background: #0a0c10; min-height: 100vh; @media(max-width:600px){ padding:16px; } }
.page-header { display: flex; align-items: center; }
.page-title  { font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 700; margin: 0; }
.page-sub    { margin-top: 2px; }

.form-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
  @media (max-width: 768px) { grid-template-columns: 1fr; }
}
.form-col {
  background: #0f1218; border: 1px solid rgba(255,255,255,.07);
  border-radius: 12px; padding: 20px;
}
.form-section-title {
  font-family: 'Syne', sans-serif; font-size: 14px; font-weight: 700;
  color: #fff; margin-bottom: 14px;
  display: flex; align-items: center;
}
.hora-total-display {
  background: rgba(59,130,246,.08); border: 1px solid rgba(59,130,246,.2);
  border-radius: 10px; padding: 14px; text-align: center;
  .hora-total-val { font-family: 'Syne', sans-serif; font-size: 36px; font-weight: 800; color: #60a5fa; }
  .hora-total-lbl { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }
}
.form-actions {
  display: flex; justify-content: flex-end; gap: 12px;
  padding-top: 16px; border-top: 1px solid rgba(255,255,255,.07);
}
</style>
