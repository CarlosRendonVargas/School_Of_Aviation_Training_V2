<template>
  <q-page padding style="padding-bottom:80px">

    <!-- Header -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 141.67 · 141.77
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Estudiantes</div>
      </div>
      <q-btn v-if="puedeCrear" unelevated color="primary" icon="person_add"
        label="Nuevo estudiante" no-caps @click="abrirFormNuevo"
        style="border-radius:8px" />
    </div>

    <!-- Filtros -->
    <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
      <q-card-section class="q-pa-md">
        <div class="row q-col-gutter-sm items-end">
          <div class="col-12 col-sm-5">
            <q-input v-model="filtros.buscar" outlined dense dark bg-color="grey-10"
              placeholder="Nombre, documento, expediente…" clearable @update:model-value="cargar">
              <template #prepend><q-icon name="search" color="grey-6" size="18px"/></template>
            </q-input>
          </div>
          <div class="col-6 col-sm-3">
            <q-select v-model="filtros.programa_id" outlined dense dark bg-color="grey-10"
              :options="programas" option-value="id" option-label="nombre"
              emit-value map-options clearable label="Programa" stack-label
              @update:model-value="cargar" />
          </div>
          <div class="col-6 col-sm-2">
            <q-select v-model="filtros.estado" outlined dense dark bg-color="grey-10"
              :options="opcionesEstado" emit-value map-options clearable label="Estado" stack-label
              @update:model-value="cargar" />
          </div>
          <div class="col-12 col-sm-2 row justify-end">
            <q-btn-toggle v-model="vista" :options="[{icon:'view_list',value:'lista'},{icon:'grid_view',value:'tarjetas'}]"
              flat dense dark toggle-color="primary" color="grey-7" />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Estadísticas rápidas -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <div class="stat-card">
          <div class="stat-num" :style="`color:${s.color}`">{{ s.valor }}</div>
          <div class="stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- Vista lista -->
    <q-table v-if="vista==='lista'"
      :rows="estudiantes" :columns="columnas" :loading="cargando"
      flat dark class="tabla-rac"
      style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
      :rows-per-page-options="[15,25,50]" row-key="id"
      v-model:pagination="paginacion" @request="onRequest">

      <template #body-cell-nombre="{ row }">
        <q-td>
          <div class="row items-center q-gutter-sm">
            <q-avatar size="32px" :color="colorEstado(row.estado)" text-color="white" style="font-size:12px">
              {{ iniciales(row) }}
            </q-avatar>
            <div>
              <div class="text-white" style="font-size:13px;font-weight:500">
                {{ row.persona?.nombres }} {{ row.persona?.apellidos }}
              </div>
              <div class="font-mono" style="font-size:10px;color:#64748b">{{ row.num_expediente }}</div>
            </div>
          </div>
        </q-td>
      </template>

      <template #body-cell-programa="{ row }">
        <q-td>
          <q-chip dense color="blue-9" text-color="white"
            :label="row.programa?.codigo || '-'" style="font-size:10px;font-family:monospace" />
        </q-td>
      </template>

      <template #body-cell-estado="{ row }">
        <q-td>
          <q-chip dense :color="colorEstado(row.estado)" text-color="white"
            :label="row.estado" style="font-size:10px;text-transform:capitalize" />
        </q-td>
      </template>

      <template #body-cell-medico="{ row }">
        <q-td class="text-center">
          <q-icon :name="row.tiene_medico ? 'check_circle' : 'cancel'"
            :color="row.tiene_medico ? 'positive' : 'negative'" size="18px">
            <q-tooltip>Certificado médico: {{ row.tiene_medico ? 'Vigente' : 'Vencido/Sin cert.' }}</q-tooltip>
          </q-icon>
        </q-td>
      </template>

      <template #body-cell-acciones="{ row }">
        <q-td>
          <q-btn flat round dense icon="folder_open" color="primary" size="sm"
            @click="$router.push(`/estudiantes/${row.id}`)" >
            <q-tooltip>Ver expediente</q-tooltip>
          </q-btn>
          <q-btn flat round dense icon="flight" color="teal" size="sm"
            @click="verHoras(row)">
            <q-tooltip>Progreso de horas</q-tooltip>
          </q-btn>
        </q-td>
      </template>

      <template #no-data>
        <div class="full-width text-center q-py-xl" style="color:#475569">
          <q-icon name="school" size="48px" class="q-mb-sm" /><br>
          Sin estudiantes con esos filtros
        </div>
      </template>
    </q-table>

    <!-- Vista tarjetas (mejor en móvil) -->
    <div v-else class="row q-col-gutter-md">
      <div class="col-12 col-sm-6 col-md-4" v-for="e in estudiantes" :key="e.id">
        <q-card flat class="card-rac cursor-pointer" style="background:#0f1218"
          @click="$router.push(`/estudiantes/${e.id}`)">
          <q-card-section>
            <div class="row items-center q-gutter-md q-mb-sm">
              <q-avatar size="44px" :color="colorEstado(e.estado)" text-color="white">
                {{ iniciales(e) }}
              </q-avatar>
              <div class="col">
                <div class="text-white" style="font-size:14px;font-weight:600">
                  {{ e.persona?.nombres }} {{ e.persona?.apellidos }}
                </div>
                <div class="font-mono" style="font-size:10px;color:#64748b">{{ e.num_expediente }}</div>
              </div>
              <q-chip dense :color="colorEstado(e.estado)" text-color="white"
                :label="e.estado" style="font-size:10px" />
            </div>
            <q-separator dark class="q-my-sm" />
            <div class="row justify-between">
              <div>
                <div class="font-mono" style="font-size:9px;color:#475569;letter-spacing:1px">PROGRAMA</div>
                <div class="text-primary font-mono" style="font-size:12px">{{ e.programa?.codigo }}</div>
              </div>
              <div class="text-right">
                <div class="font-mono" style="font-size:9px;color:#475569;letter-spacing:1px">MÉDICO</div>
                <q-icon :name="e.tiene_medico ? 'check_circle' : 'cancel'"
                  :color="e.tiene_medico ? 'positive' : 'negative'" size="18px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Dialog: Progreso de horas -->
    <q-dialog v-model="dialogHoras">
      <q-card dark style="background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px;min-width:340px;max-width:520px;width:100%">
        <q-card-section style="border-bottom:1px solid rgba(255,255,255,.07)">
          <div class="row items-center justify-between">
            <div class="font-head text-white" style="font-size:16px;font-weight:700">
              Progreso RAC 61
            </div>
            <q-btn flat round dense icon="close" color="grey-5" @click="dialogHoras=false" />
          </div>
          <div class="font-mono" style="font-size:11px;color:#64748b;margin-top:4px">
            {{ estudianteSeleccionado?.persona?.nombres }} {{ estudianteSeleccionado?.persona?.apellidos }}
          </div>
        </q-card-section>
        <q-card-section class="q-pa-lg">
          <q-skeleton v-if="cargandoHoras" v-for="n in 5" :key="n" type="QItem" dark class="q-mb-sm" />
          <template v-else-if="progresoHoras">
            <div v-for="cat in categoriasProgreso" :key="cat.key" class="q-mb-md">
              <div class="row items-center justify-between q-mb-xs">
                <span style="font-size:13px;color:#cbd5e1">{{ cat.label }}</span>
                <div>
                  <span class="font-mono text-primary" style="font-size:11px">{{ Number(cat.acumulado || 0).toFixed(1) }}h</span>
                  <span class="font-mono" style="font-size:11px;color:#475569"> / {{ Number(cat.requerido || 0).toFixed(1) }}h</span>
                </div>
              </div>
              <q-linear-progress
                :value="Math.min((cat.porcentaje || 0)/100, 1)"
                :color="cat.cumplido ? 'positive' : cat.porcentaje > 60 ? 'warning' : 'primary'"
                rounded size="8px" />
            </div>
            <q-banner v-if="progresoHoras.listo_para_examen" dense rounded
              style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:8px;margin-top:8px">
              <template #avatar><q-icon name="verified" color="positive" /></template>
              <span style="font-size:13px;color:#86efac">Listo para examen UAEAC</span>
            </q-banner>
          </template>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Dialog: Nuevo Estudiante -->
    <q-dialog v-model="dialogNuevo">
      <q-card dark style="background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px;min-width:400px;max-width:600px;width:100%">
        <q-card-section style="border-bottom:1px solid rgba(255,255,255,.07)">
          <div class="row items-center justify-between">
            <div class="font-head text-white" style="font-size:16px;font-weight:700">
              Registrar Nuevo Estudiante
            </div>
            <q-btn flat round dense icon="close" color="grey-5" @click="dialogNuevo=false" />
          </div>
        </q-card-section>
        <q-form @submit.prevent="guardarNuevo">
          <q-card-section class="q-pa-lg q-gutter-md">
            <!-- Datos Persona -->
            <div class="text-primary font-mono q-mb-sm" style="font-size:11px;letter-spacing:1px">DATOS PERSONALES</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-input v-model="formNuevo.nombres" label="Nombres" outlined dense dark bg-color="grey-10"
                  :rules="[val => !!val || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formNuevo.apellidos" label="Apellidos" outlined dense dark bg-color="grey-10"
                  :rules="[val => !!val || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="formNuevo.tipo_documento" :options="['CC', 'CE', 'Pasaporte']"
                  label="Tipo de Documento" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formNuevo.num_documento" label="Número de Documento" outlined dense dark bg-color="grey-10"
                  :rules="[val => !!val || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formNuevo.fecha_nacimiento" type="date" label="Fecha Nacimiento"
                  outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" stack-label />
              </div>
            </div>

            <!-- Datos Academicos -->
            <q-separator dark class="q-my-md" />
            <div class="text-primary font-mono q-mb-sm" style="font-size:11px;letter-spacing:1px">DATOS ACADÉMICOS</div>
            <div class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-select v-model="formNuevo.programa_id" :options="programas" option-value="id" option-label="nombre"
                  emit-value map-options label="Programa de Formación" outlined dense dark bg-color="grey-10"
                  :rules="[val => !!val || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formNuevo.fecha_ingreso" type="date" label="Fecha Ingreso"
                  outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" stack-label />
              </div>
              <div class="col-12">
                <q-input v-model="formNuevo.observaciones" type="textarea" label="Observaciones (opcional)"
                  outlined dense dark bg-color="grey-10" rows="3" />
              </div>
            </div>
          </q-card-section>
          <q-card-actions align="right" class="q-pa-md" style="border-top:1px solid rgba(255,255,255,.07)">
            <q-btn flat label="Cancelar" color="grey-5" no-caps @click="dialogNuevo=false" />
            <q-btn unelevated label="Registrar Estudiante" color="primary" type="submit" no-caps :loading="guardandoNuevo" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q        = useQuasar()
const authStore = useAuthStore()

const estudiantes          = ref([])
const programas            = ref([])
const cargando             = ref(false)
const cargandoHoras        = ref(false)
const dialogHoras          = ref(false)
const estudianteSeleccionado = ref(null)
const progresoHoras        = ref(null)
const vista                = ref('lista')

const dialogNuevo          = ref(false)
const guardandoNuevo       = ref(false)
const formNuevo            = ref({
  nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '',
  fecha_nacimiento: '', programa_id: null, fecha_ingreso: '', observaciones: ''
})

const filtros = ref({ buscar: '', programa_id: null, estado: null })
const paginacion = ref({ page: 1, rowsPerPage: 15, rowsNumber: 0 })

const opcionesEstado = [
  { label: 'Activo',     value: 'activo' },
  { label: 'Suspendido', value: 'suspendido' },
  { label: 'Graduado',   value: 'graduado' },
  { label: 'Retirado',   value: 'retirado' },
]

const puedeCrear = computed(() => ['admin', 'dir_ops'].includes(authStore.rol))

const columnas = [
  { name: 'nombre',    label: 'Estudiante',   field: 'id',          align: 'left', style: 'min-width:200px' },
  { name: 'programa',  label: 'Programa',     field: 'programa_id', align: 'center' },
  { name: 'ingreso',   label: 'Ingreso',      field: 'fecha_ingreso',align: 'left' },
  { name: 'estado',    label: 'Estado',       field: 'estado',      align: 'center' },
  { name: 'medico',    label: 'Médico',       field: 'tiene_medico',align: 'center' },
  { name: 'acciones',  label: '',             field: 'id',          align: 'right' },
]

const stats = computed(() => {
  const total     = paginacion.value.rowsNumber
  const activos   = estudiantes.value.filter(e => e.estado === 'activo').length
  const sinMedico = estudiantes.value.filter(e => !e.tiene_medico).length
  const graduados = estudiantes.value.filter(e => e.estado === 'graduado').length
  return [
    { label: 'Total',      valor: total,     color: '#60a5fa' },
    { label: 'Activos',    valor: activos,   color: '#22c55e' },
    { label: 'Sin médico', valor: sinMedico, color: '#ef4444' },
    { label: 'Graduados',  valor: graduados, color: '#a78bfa' },
  ]
})

const categoriasProgreso = computed(() => {
  const cats = progresoHoras.value?.categorias || {}
  return Object.entries(cats)
    .filter(([, v]) => (v.requerido || 0) > 0)
    .map(([key, v]) => ({ key, ...v }))
})

const iniciales = (e) => {
  const n = e.persona?.nombres?.[0] || ''
  const a = e.persona?.apellidos?.[0] || ''
  return (n + a).toUpperCase()
}

const colorEstado = (estado) => ({
  activo: 'primary', suspendido: 'warning', graduado: 'positive', retirado: 'grey-7',
}[estado] || 'grey-7')

async function cargar(pag = 1) {
  cargando.value = true
  try {
    const params = { page: pag, per_page: paginacion.value.rowsPerPage }
    if (filtros.value.buscar)      params.buscar      = filtros.value.buscar
    if (filtros.value.programa_id) params.programa_id = filtros.value.programa_id
    if (filtros.value.estado)      params.estado      = filtros.value.estado

    const { data } = await api.get('/estudiantes', { params })
    estudiantes.value            = data.data.data || []
    paginacion.value.rowsNumber  = data.data.total || 0
  } finally { cargando.value = false }
}

async function cargarProgramas() {
  const { data } = await api.get('/programas?activos=1')
  programas.value = data.data || []
}

async function verHoras(estudiante) {
  estudianteSeleccionado.value = estudiante
  dialogHoras.value = true
  cargandoHoras.value = true
  progresoHoras.value = null
  try {
    const { data } = await api.get(`/estudiantes/${estudiante.id}/progreso-horas`)
    progresoHoras.value = data.data
  } finally { cargandoHoras.value = false }
}

function abrirFormNuevo() {
  dialogNuevo.value = true
  formNuevo.value = {
    nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '',
    fecha_nacimiento: '', programa_id: null, fecha_ingreso: new Date().toISOString().split('T')[0],
    observaciones: ''
  }
}

async function guardarNuevo() {
  guardandoNuevo.value = true
  try {
    await api.post('/estudiantes', formNuevo.value)
    $q.notify({ type: 'positive', message: 'Estudiante registrado correctamente' })
    dialogNuevo.value = false
    cargar()
  } catch (err) {
    const defaultMsg = 'Error al registrar al estudiante.'
    const validationMsg = err.response?.data?.message || defaultMsg
    $q.notify({ type: 'negative', message: validationMsg })
  } finally {
    guardandoNuevo.value = false
  }
}

function onRequest(props) {
  paginacion.value.page = props.pagination.page
  paginacion.value.rowsPerPage = props.pagination.rowsPerPage
  cargar(props.pagination.page)
}

onMounted(() => { cargar(); cargarProgramas() })
</script>
