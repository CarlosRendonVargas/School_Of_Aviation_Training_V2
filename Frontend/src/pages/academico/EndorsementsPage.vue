<template>
  <q-page padding>
    <div class="text-h5 q-mb-md">Endorsements — Primer Vuelo Solo</div>

    <!-- Toolbar -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-12 col-sm-4">
          <q-input v-model="filtros.buscar" label="Buscar estudiante / aeronave" dense outlined clearable
            @update:model-value="cargar" debounce="400">
            <template #prepend><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-6 col-sm-3">
          <q-select v-model="filtros.tipo" :options="tipoOpts" label="Tipo" dense outlined clearable
            emit-value map-options @update:model-value="cargar" />
        </div>
        <div class="col-6 col-sm-2 text-right">
          <q-btn v-if="puedeCrear" icon="add" label="Registrar" color="primary" @click="abrirNuevo" unelevated />
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
      >
        <template #body-cell-tipo="{ row }">
          <q-td>
            <q-badge color="primary" :label="tipoLabel(row.tipo)" />
          </q-td>
        </template>
        <template #body-cell-firma="{ row }">
          <q-td class="text-center">
            <q-icon :name="row.firma_instructor ? 'verified' : 'pending'" :color="row.firma_instructor ? 'positive' : 'warning'" size="sm" />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Dialog Nuevo Endorsement -->
    <q-dialog v-model="dlgNuevo" persistent>
      <q-card style="min-width:500px">
        <q-card-section class="row items-center bg-primary text-white">
          <q-icon name="flight_takeoff" class="q-mr-sm" />
          <span class="text-h6">Nuevo Endorsement</span>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-select
                v-model="form.estudiante_id"
                :options="estudiantes"
                option-value="id"
                option-label="nombre_completo"
                label="Estudiante *"
                outlined dense emit-value map-options
                use-input
                @filter="filtrarEstudiantes"
              />
            </div>
            <div class="col-12">
              <q-select v-model="form.tipo" :options="tipoOpts" label="Tipo Endorsement *" outlined dense emit-value map-options />
            </div>
            <div class="col-6">
              <q-input v-model="form.fecha" type="date" label="Fecha *" outlined dense />
            </div>
            <div class="col-6">
              <q-input v-model="form.aeronave_matricula" label="Matrícula Aeronave" outlined dense />
            </div>
            <div class="col-12">
              <q-input v-model="form.aeropuerto_icao" label="Aeropuerto ICAO" outlined dense />
            </div>
            <div class="col-12">
              <q-input v-model="form.observaciones" label="Observaciones" type="textarea" rows="3" outlined dense />
            </div>
            <div class="col-12">
              <q-input v-model="form.firma_instructor" label="Firma Instructor (código/texto)" outlined dense />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn unelevated color="primary" label="Guardar" :loading="saving" @click="guardar" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { useAuthStore } from 'store/auth'

const $q = useQuasar()
const auth = useAuthStore()
const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dlgNuevo = ref(false)
const estudiantes = ref([])
const todosEstudiantes = ref([])

const filtros = ref({ buscar: '', tipo: null })

const tipoOpts = [
  { label: 'Primer Vuelo Solo', value: 'primer_vuelo_solo' },
  { label: 'Vuelo Solo Área', value: 'vuelo_solo_area' },
  { label: 'Vuelo Solo XC Corto', value: 'vuelo_solo_xc_corto' },
  { label: 'Vuelo Solo XC Largo', value: 'vuelo_solo_xc_largo' },
  { label: 'Vuelo Nocturno', value: 'vuelo_nocturno' },
  { label: 'Examen Vuelo', value: 'examen_vuelo' },
]

const puedeCrear = computed(() => ['admin', 'dir_ops', 'instructor'].includes(auth.rol))

const columnas = [
  { name: 'estudiante', label: 'Estudiante', field: r => r.estudiante?.nombres + ' ' + r.estudiante?.apellidos, align: 'left', sortable: true },
  { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'center', sortable: true },
  { name: 'fecha', label: 'Fecha', field: r => fmtFecha(r.fecha), align: 'center', sortable: true },
  { name: 'aeronave_matricula', label: 'Aeronave', field: 'aeronave_matricula', align: 'center' },
  { name: 'aeropuerto_icao', label: 'Aeropuerto', field: 'aeropuerto_icao', align: 'center' },
  { name: 'instructor', label: 'Instructor', field: r => r.instructor?.nombres + ' ' + r.instructor?.apellidos, align: 'left' },
  { name: 'firma', label: 'Firmado', field: 'firma_instructor', align: 'center' },
]

const emptyForm = () => ({
  estudiante_id: null,
  tipo: 'primer_vuelo_solo',
  fecha: new Date().toISOString().slice(0, 10),
  aeronave_matricula: '',
  aeropuerto_icao: '',
  observaciones: '',
  firma_instructor: '',
})
const form = ref(emptyForm())

async function cargar() {
  loading.value = true
  try {
    const params = {}
    if (filtros.value.tipo) params.tipo = filtros.value.tipo
    const res = await api.get('/endorsements', { params })
    rows.value = res.data.data ?? []
  } finally {
    loading.value = false
  }
}

async function cargarEstudiantes() {
  const res = await api.get('/estudiantes?per_page=200')
  todosEstudiantes.value = (res.data.data?.data ?? res.data.data ?? []).map(e => ({
    ...e,
    nombre_completo: `${e.nombres} ${e.apellidos}`,
  }))
  estudiantes.value = todosEstudiantes.value
}

function filtrarEstudiantes(val, update) {
  update(() => {
    const q = val.toLowerCase()
    estudiantes.value = q
      ? todosEstudiantes.value.filter(e => e.nombre_completo.toLowerCase().includes(q))
      : todosEstudiantes.value
  })
}

function abrirNuevo() {
  form.value = emptyForm()
  dlgNuevo.value = true
}

async function guardar() {
  saving.value = true
  try {
    await api.post('/endorsements', form.value)
    $q.notify({ type: 'positive', message: 'Endorsement registrado' })
    dlgNuevo.value = false
    cargar()
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error' })
  } finally {
    saving.value = false
  }
}

function tipoLabel(t) {
  return tipoOpts.find(o => o.value === t)?.label ?? t
}

function fmtFecha(d) {
  if (!d) return '—'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => {
  cargar()
  cargarEstudiantes()
})
</script>
