<template>
  <q-form ref="formRef" @submit.prevent="handleSubmit" greedy>

    <!-- ── SECCIÓN 1: Fecha y Horario ── -->
    <div class="form-section-title">Fecha y Horario</div>
    <div class="row q-col-gutter-md">
      <div class="col-12 col-sm-4">
        <label class="campo-label required">Fecha</label>
        <q-input v-model="formData.fecha" type="date" outlined dense dark
          :min="hoy" :rules="[v => !!v || 'Requerido']" lazy-rules
          @update:model-value="onFechaHoraCambia" />
      </div>
      <div class="col-6 col-sm-4">
        <label class="campo-label required">Hora inicio</label>
        <q-input v-model="formData.hora_inicio" type="time" outlined dense dark
          :rules="[v => !!v || 'Requerido']" lazy-rules
          @update:model-value="onFechaHoraCambia" />
      </div>
      <div class="col-6 col-sm-4">
        <label class="campo-label required">Hora fin</label>
        <q-input v-model="formData.hora_fin" type="time" outlined dense dark
          :rules="[v => !!v || 'Requerido', v => v > formData.hora_inicio || 'Debe ser después de inicio']"
          lazy-rules @update:model-value="onFechaHoraCambia" />
      </div>
    </div>

    <!-- ── SECCIÓN 2: Tipo y Recursos ── -->
    <div class="form-section">
      <div class="form-section-title">Tipo de Vuelo y Recursos</div>
      <div class="row q-col-gutter-md">

        <!-- Tipo de reserva -->
        <div class="col-12">
          <label class="campo-label required">Tipo de instrucción</label>
          <q-btn-toggle
            v-model="formData.tipo"
            :options="tiposReserva"
            no-caps unelevated dense
            toggle-color="red-9" color="grey-9" text-color="grey-4"
            class="corporate-toggle"
          />
        </div>

        <!-- Aeronave -->
        <div class="col-12">
          <label class="campo-label required">
            Aeronave
            <span v-if="verificando" class="q-ml-xs text-grey-6" style="font-size:9px; letter-spacing:0">
              <q-spinner size="10px" /> verificando…
            </span>
            <span v-else-if="aeronavesDispo.length" class="q-ml-xs text-emerald" style="font-size:9px; letter-spacing:0">
              {{ aeronavesDispo.length }} disponibles
            </span>
            <span v-else-if="buscoBispo && !aeronavesDispo.length" class="q-ml-xs" style="font-size:9px; color:#ef4444; letter-spacing:0">
              Sin disponibilidad en ese horario
            </span>
          </label>
          <q-select
            v-model="formData.aeronave_id"
            :options="aeronavesDispo.length ? aeronavesDispo : todasAeronaves"
            option-value="id"
            :option-label="av => `${av.matricula} · ${av.modelo}`"
            emit-value map-options outlined dense dark
            :rules="[v => !!v || 'Seleccione una aeronave']"
            lazy-rules
            :loading="verificando"
          >
            <template #prepend><q-icon name="flight" color="grey-6" size="18px" /></template>
            <template #option="{ itemProps, opt }">
              <q-item v-bind="itemProps">
                <q-item-section>
                  <q-item-label class="font-mono text-white">{{ opt.matricula }}</q-item-label>
                  <q-item-label caption>{{ opt.modelo }} · {{ opt.clase }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip dense color="positive" text-color="white" label="Disponible" style="font-size:9px" />
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>

        <!-- Instructor -->
        <div class="col-12">
          <label class="campo-label" :class="formData.tipo === 'instruccion' ? 'required' : ''">
            Instructor
            <span v-if="formData.tipo !== 'instruccion'" style="font-size:9px; color:#475569; letter-spacing:0; text-transform:none; font-family:'Outfit',sans-serif">
              — opcional para vuelo solo / simulador
            </span>
          </label>
          <q-select
            v-model="formData.instructor_id"
            :options="instructores"
            option-value="id"
            :option-label="i => `${i.persona?.nombres || ''} ${i.persona?.apellidos || ''} · ${i.num_licencia}`"
            emit-value map-options outlined dense dark
            clearable
            :rules="[v => formData.tipo !== 'instruccion' || !!v || 'Obligatorio para instrucción']"
            lazy-rules
          >
            <template #prepend><q-icon name="badge" color="grey-6" size="18px" /></template>
            <template #option="{ itemProps, opt }">
              <q-item v-bind="itemProps">
                <q-item-section>
                  <q-item-label class="text-white">{{ opt.persona?.nombres }} {{ opt.persona?.apellidos }}</q-item-label>
                  <q-item-label caption class="font-mono">{{ opt.num_licencia }} · {{ opt.tipo_licencia }}</q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-chip dense :color="instOk(opt) ? 'positive' : 'negative'" text-color="white"
                    :label="instOk(opt) ? 'Vigente' : 'Vencida'" style="font-size:9px" />
                </q-item-section>
              </q-item>
            </template>
          </q-select>
        </div>

      </div>
    </div>

    <!-- Errores de validación RAC -->
    <div v-if="erroresRac.length" class="q-mt-md">
      <div class="font-mono q-mb-xs" style="font-size:10px;color:#ef4444;letter-spacing:1px">
        ERRORES DE VALIDACIÓN RAC — La reserva no puede crearse
      </div>
      <div v-for="(err, i) in erroresRac" :key="i"
        class="q-pa-sm q-mb-xs rounded-borders"
        style="background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);border-radius:8px">
        <div class="row items-start q-gutter-xs">
          <q-icon name="error_outline" color="negative" size="14px" style="margin-top:2px" />
          <span style="font-size:12px;color:#fca5a5;flex:1">{{ err }}</span>
        </div>
      </div>
    </div>

    <!-- Botones -->
    <div class="row justify-end q-gutter-sm q-mt-lg">
      <q-btn flat label="Cancelar" no-caps color="grey-5" @click="$emit('cancelar')"
        style="border-radius:8px" />
      <q-btn unelevated color="primary" no-caps type="submit"
        label="Crear reserva" :loading="loading"
        :disable="!!erroresRac.length || loading"
        style="border-radius:8px;padding:8px 24px" />
    </div>

  </q-form>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useReserva } from '../../composables/useReserva'
import dayjs from 'dayjs'

const props = defineProps({
  estudianteId: { type: [Number, String], required: true },
  loading:      { type: Boolean, default: false },
})
const emit = defineEmits(['submit', 'cancelar'])

const { verificarDisponibilidad, aeronavesDispo, verificando, erroresValidacion } = useReserva()

const formRef       = ref(null)
const todasAeronaves = ref([])
const instructores   = ref([])
const erroresRac     = ref([])
const buscoBispo     = ref(false)
const hoy            = dayjs().format('YYYY-MM-DD')

const tiposReserva = [
  { label: 'Instrucción', value: 'instruccion' },
  { label: 'Solo',        value: 'solo'        },
  { label: 'Simulador',   value: 'simulador'   },
]

const formData = ref({
  estudiante_id: props.estudianteId,
  aeronave_id:   null,
  instructor_id: null,
  fecha:         hoy,
  hora_inicio:   '08:00',
  hora_fin:      '10:00',
  tipo:          'instruccion',
})

let debounceTimer = null
function onFechaHoraCambia() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(async () => {
    const { fecha, hora_inicio, hora_fin } = formData.value
    if (fecha && hora_inicio && hora_fin && hora_fin > hora_inicio) {
      buscoBispo.value = true
      await verificarDisponibilidad(fecha, hora_inicio, hora_fin)
      // Limpiar aeronave si ya no está disponible
      if (aeronavesDispo.value.length && !aeronavesDispo.value.find(a => a.id === formData.value.aeronave_id)) {
        formData.value.aeronave_id = null
      }
    }
  }, 600)
}

const instOk = (inst) => dayjs(inst.venc_licencia).isAfter(dayjs())

async function handleSubmit() {
  erroresRac.value = []
  emit('submit', { ...formData.value })
}

async function cargar() {
  const [avRes, instRes] = await Promise.all([
    api.get('/aeronaves'),
    api.get('/instructores?activo=1'),
  ])
  todasAeronaves.value = avRes.data.data || []
  instructores.value   = instRes.data.data?.data || instRes.data.data || []
}

onMounted(cargar)
</script>

<style scoped>
/* campo-label is defined globally in app.scss */
</style>
