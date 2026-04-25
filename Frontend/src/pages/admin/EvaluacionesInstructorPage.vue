<template>
  <q-page padding>
    <div class="text-h5 q-mb-md">Evaluaciones de Instructores — RAC 65</div>

    <!-- Toolbar -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-12 col-sm-5">
          <q-select
            v-model="filtros.instructor_id"
            :options="instructores"
            option-value="id"
            option-label="nombre_completo"
            label="Filtrar por Instructor"
            dense outlined clearable emit-value map-options
            use-input @filter="filtrarInstructores"
            @update:model-value="cargar"
          />
        </div>
        <div class="col-6 col-sm-3">
          <q-select v-model="filtros.tipo" :options="tipoOpts" label="Tipo" dense outlined clearable
            emit-value map-options @update:model-value="cargar" />
        </div>
        <div class="col-6 col-sm-2 text-right">
          <q-btn v-if="puedeCrear" icon="add" label="Nueva Evaluación" color="primary" @click="abrirNuevo" unelevated />
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla -->
    <q-card flat bordered>
      <q-table
        :rows="rows"
        :columns="columnas"
        :loading="loading"
        row-key="id"
        flat
        :pagination="{ rowsPerPage: 20 }"
        @row-click="(_, row) => verDetalle(row)"
      >
        <template #body-cell-resultado="{ row }">
          <q-td class="text-center">
            <q-badge :color="resultadoColor(row.resultado)" :label="row.resultado" />
          </q-td>
        </template>
        <template #body-cell-puntaje="{ row }">
          <q-td class="text-center">
            <q-linear-progress :value="row.puntaje / 100" :color="puntajeColor(row.puntaje)"
              class="q-my-xs" style="height:8px;border-radius:4px;" />
            <span class="text-caption">{{ row.puntaje }}%</span>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Dialog Nueva/Detalle -->
    <q-dialog v-model="dlgForm" persistent>
      <q-card style="min-width:560px">
        <q-card-section class="row items-center bg-primary text-white">
          <q-icon name="school" class="q-mr-sm" />
          <span class="text-h6">{{ form.id ? 'Detalle Evaluación' : 'Nueva Evaluación' }}</span>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-select
                v-model="form.instructor_id"
                :options="instructoresFiltrados"
                option-value="id"
                option-label="nombre_completo"
                label="Instructor *"
                outlined dense emit-value map-options
                :readonly="!!form.id"
                use-input @filter="filtrarInstructores2"
              />
            </div>
            <div class="col-12 col-sm-6">
              <q-select v-model="form.tipo" :options="tipoOpts" label="Tipo *" outlined dense emit-value map-options :readonly="!!form.id" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha" type="date" label="Fecha *" outlined dense :readonly="!!form.id" />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model.number="form.puntaje" type="number" min="0" max="100" label="Puntaje (0-100) *" outlined dense :readonly="!!form.id" />
            </div>
            <div class="col-12 col-sm-6">
              <q-select v-model="form.resultado" :options="resultadoOpts" label="Resultado *" outlined dense emit-value map-options :readonly="!!form.id" />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="form.proxima_evaluacion" type="date" label="Próxima Evaluación" outlined dense :readonly="!!form.id" />
            </div>
            <div class="col-12">
              <div class="text-subtitle2 q-mb-sm">Áreas Evaluadas</div>
              <div class="row q-col-gutter-xs">
                <div v-for="area in areasDisponibles" :key="area.value" class="col-6 col-sm-4">
                  <q-checkbox v-model="form.areas_evaluadas" :val="area.value" :label="area.label" dense :disable="!!form.id" />
                </div>
              </div>
            </div>
            <div class="col-12">
              <q-input v-model="form.observaciones" label="Observaciones" type="textarea" rows="3" outlined dense :readonly="!!form.id" />
            </div>
            <div class="col-12">
              <q-input v-model="form.acciones_requeridas" label="Acciones Requeridas" type="textarea" rows="2" outlined dense :readonly="!!form.id" />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cerrar" v-close-popup />
          <q-btn v-if="!form.id" unelevated color="primary" label="Registrar" :loading="saving" @click="guardar" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { useAuthStore } from 'stores/auth'

const $q = useQuasar()
const auth = useAuthStore()
const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dlgForm = ref(false)
const todosInstructores = ref([])
const instructores = ref([])
const instructoresFiltrados = ref([])

const filtros = ref({ instructor_id: null, tipo: null })

const puedeCrear = computed(() => ['admin', 'dir_ops'].includes(auth.user?.rol))

const tipoOpts = [
  { label: 'Periódica', value: 'periodica' },
  { label: 'Observación en Vuelo', value: 'observacion_vuelo' },
  { label: 'Evaluación de Competencia', value: 'evaluacion_competencia' },
  { label: 'Desempeño', value: 'desempeno' },
  { label: 'Recurrente', value: 'recurrente' },
]
const resultadoOpts = [
  { label: 'Aprobado', value: 'aprobado' },
  { label: 'Aprobado con Observaciones', value: 'aprobado_observaciones' },
  { label: 'Reprobado', value: 'reprobado' },
  { label: 'En Proceso', value: 'en_proceso' },
]
const areasDisponibles = [
  { label: 'Conocimiento Técnico', value: 'conocimiento_tecnico' },
  { label: 'Habilidad de Vuelo', value: 'habilidad_vuelo' },
  { label: 'Metodología Enseñanza', value: 'metodologia' },
  { label: 'CRM', value: 'crm' },
  { label: 'Procedimientos de Emergencia', value: 'emergencias' },
  { label: 'Meteorología', value: 'meteorologia' },
  { label: 'Normativa RAC', value: 'normativa_rac' },
  { label: 'Comunicaciones', value: 'comunicaciones' },
]

const columnas = [
  { name: 'instructor', label: 'Instructor', field: r => (r.instructor?.nombres ?? '') + ' ' + (r.instructor?.apellidos ?? ''), align: 'left', sortable: true },
  { name: 'tipo', label: 'Tipo', field: r => tipoOpts.find(o => o.value === r.tipo)?.label ?? r.tipo, align: 'left' },
  { name: 'fecha', label: 'Fecha', field: r => fmtFecha(r.fecha), align: 'center', sortable: true },
  { name: 'puntaje', label: 'Puntaje', field: 'puntaje', align: 'center' },
  { name: 'resultado', label: 'Resultado', field: 'resultado', align: 'center' },
  { name: 'proxima', label: 'Próxima Eval.', field: r => fmtFecha(r.proxima_evaluacion), align: 'center' },
  { name: 'evaluador', label: 'Evaluado por', field: r => (r.evaluador?.nombres ?? '') + ' ' + (r.evaluador?.apellidos ?? ''), align: 'left' },
]

const emptyForm = () => ({
  id: null,
  instructor_id: null,
  tipo: 'periodica',
  fecha: new Date().toISOString().slice(0, 10),
  puntaje: 0,
  resultado: 'aprobado',
  areas_evaluadas: [],
  observaciones: '',
  acciones_requeridas: '',
  proxima_evaluacion: null,
})
const form = ref(emptyForm())

async function cargar() {
  loading.value = true
  try {
    const params = {}
    if (filtros.value.instructor_id) params.instructor_id = filtros.value.instructor_id
    if (filtros.value.tipo) params.tipo = filtros.value.tipo
    const res = await api.get('/evaluaciones-instructor', { params })
    rows.value = res.data
  } finally {
    loading.value = false
  }
}

async function cargarInstructores() {
  const res = await api.get('/instructores')
  todosInstructores.value = (res.data.data ?? res.data).map(i => ({
    ...i,
    nombre_completo: `${i.nombres} ${i.apellidos}`,
  }))
  instructores.value = todosInstructores.value
  instructoresFiltrados.value = todosInstructores.value
}

function filtrarInstructores(val, update) {
  update(() => {
    const q = val.toLowerCase()
    instructores.value = q
      ? todosInstructores.value.filter(i => i.nombre_completo.toLowerCase().includes(q))
      : todosInstructores.value
  })
}

function filtrarInstructores2(val, update) {
  update(() => {
    const q = val.toLowerCase()
    instructoresFiltrados.value = q
      ? todosInstructores.value.filter(i => i.nombre_completo.toLowerCase().includes(q))
      : todosInstructores.value
  })
}

function abrirNuevo() {
  form.value = emptyForm()
  dlgForm.value = true
}

function verDetalle(row) {
  form.value = {
    id: row.id,
    instructor_id: row.instructor_id,
    tipo: row.tipo,
    fecha: row.fecha,
    puntaje: row.puntaje,
    resultado: row.resultado,
    areas_evaluadas: Array.isArray(row.areas_evaluadas) ? row.areas_evaluadas : [],
    observaciones: row.observaciones ?? '',
    acciones_requeridas: row.acciones_requeridas ?? '',
    proxima_evaluacion: row.proxima_evaluacion ?? null,
  }
  dlgForm.value = true
}

async function guardar() {
  saving.value = true
  try {
    await api.post('/evaluaciones-instructor', form.value)
    $q.notify({ type: 'positive', message: 'Evaluación registrada' })
    dlgForm.value = false
    cargar()
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error' })
  } finally {
    saving.value = false
  }
}

function resultadoColor(r) {
  return { aprobado: 'positive', aprobado_observaciones: 'warning', reprobado: 'negative', en_proceso: 'blue' }[r] ?? 'grey'
}

function puntajeColor(p) {
  if (p >= 80) return 'positive'
  if (p >= 60) return 'warning'
  return 'negative'
}

function fmtFecha(d) {
  if (!d) return '—'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => {
  cargar()
  cargarInstructores()
})
</script>
