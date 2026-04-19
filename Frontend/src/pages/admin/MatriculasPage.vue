<template>
  <q-page padding style="padding-bottom:80px">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">Gestión Académica</div>
        <div class="font-head text-white" style="font-size:20px; font-weight:700">Matrículas</div>
      </div>
      <q-btn unelevated color="primary" icon="add" label="Nueva matrícula" no-caps
        @click="dialogNuevo = true" style="border-radius:8px" />
    </div>

    <!-- Stats rápidos -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <div class="stat-card">
          <div class="stat-num" :style="`color:${s.color}`">{{ s.valor }}</div>
          <div class="stat-label">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-12 col-sm-4">
        <q-input v-model="buscar" outlined dense dark bg-color="grey-10" placeholder="Buscar estudiante…" clearable debounce="400">
          <template #prepend><q-icon name="search" color="grey-6" size="18px"/></template>
        </q-input>
      </div>
      <div class="col-6 col-sm-3">
        <q-select v-model="filtroEstado" outlined dense dark bg-color="grey-10"
          :options="opcionesEstado" emit-value map-options clearable label="Estado" stack-label />
      </div>
    </div>

    <!-- Tabla -->
    <q-table :rows="matriculas" :columns="columnas" :loading="cargando" flat dark class="tabla-rac"
      style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px" row-key="id">
      <template #body-cell-estado="{ value }">
        <q-td><q-chip dense :color="colorEstado(value)" :label="value" text-color="white" style="font-size:10px" /></q-td>
      </template>
      <template #body-cell-valor="{ row }">
        <q-td><span class="font-mono text-primary">{{ formatCOP(row.valor_total) }}</span></q-td>
      </template>
      <template #body-cell-acciones="{ row }">
        <q-td>
          <q-btn flat round dense icon="receipt" color="teal" size="sm" @click="verFacturas(row)"
            title="Ver facturas" />
        </q-td>
      </template>
    </q-table>

    <!-- Dialog nueva matrícula -->
    <q-dialog v-model="dialogNuevo" persistent>
      <q-card dark style="background:#0f1218; border:1px solid rgba(255,255,255,.1); border-radius:14px; min-width:360px">
        <q-toolbar style="background:#151920">
          <q-toolbar-title class="font-head">Nueva Matrícula</q-toolbar-title>
          <q-btn flat round dense icon="close" @click="dialogNuevo = false" color="grey-5" />
        </q-toolbar>
        <q-card-section class="q-pa-lg">
          <q-form @submit.prevent="crearMatricula" class="q-gutter-md">
            <q-input v-model="form.estudiante_id" outlined dense dark bg-color="grey-10"
              label="ID Estudiante" stack-label type="number" />
            <q-select v-model="form.programa_id" outlined dense dark bg-color="grey-10"
              :options="programas" emit-value map-options label="Programa" stack-label />
            <q-input v-model="form.fecha_matricula" type="date" outlined dense dark bg-color="grey-10"
              label="Fecha matrícula" stack-label />
            <q-input v-model.number="form.valor_total" outlined dense dark bg-color="grey-10"
              label="Valor total (COP)" stack-label type="number" prefix="$" />
            <q-select v-model="form.forma_pago" outlined dense dark bg-color="grey-10"
              :options="formasPago" emit-value map-options label="Forma de pago" stack-label />
            <div class="row q-gutter-sm justify-end">
              <q-btn flat label="Cancelar" color="grey-5" @click="dialogNuevo = false" />
              <q-btn unelevated color="primary" label="Registrar" type="submit" :loading="guardando" style="border-radius:8px" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q       = useQuasar()
const matriculas  = ref([])
const programas   = ref([])
const cargando    = ref(false)
const guardando   = ref(false)
const dialogNuevo = ref(false)
const buscar      = ref('')
const filtroEstado = ref(null)

const form = ref({ estudiante_id: '', programa_id: null, fecha_matricula: '', valor_total: 0, forma_pago: 'cuotas' })

const opcionesEstado = [
  { label: 'Activa', value: 'activa' }, { label: 'Suspendida', value: 'suspendida' },
  { label: 'Finalizada', value: 'finalizada' }, { label: 'Cancelada', value: 'cancelada' },
]
const formasPago = [
  { label: 'Contado', value: 'contado' }, { label: 'Cuotas', value: 'cuotas' }, { label: 'Financiado', value: 'financiado' },
]

const columnas = [
  { name: 'id',             label: 'N°',        field: 'id',           align: 'center' },
  { name: 'estudiante',     label: 'Estudiante', field: row => row.estudiante?.persona ? `${row.estudiante.persona.nombres} ${row.estudiante.persona.apellidos}` : '-', align: 'left' },
  { name: 'programa',       label: 'Programa',   field: row => row.programa?.codigo, align: 'left' },
  { name: 'fecha_matricula',label: 'Fecha',      field: 'fecha_matricula', align: 'left' },
  { name: 'valor',          label: 'Valor',      field: 'valor_total',  align: 'right' },
  { name: 'estado',         label: 'Estado',     field: 'estado',       align: 'center' },
  { name: 'acciones',       label: '',           field: 'id',           align: 'right' },
]

const stats = computed(() => {
  const activas    = matriculas.value.filter(m => m.estado === 'activa').length
  const finalizadas = matriculas.value.filter(m => m.estado === 'finalizada').length
  const total      = matriculas.value.reduce((a, m) => a + (m.valor_total || 0), 0)
  return [
    { label: 'Activas',     valor: activas,               color: '#3b82f6' },
    { label: 'Finalizadas', valor: finalizadas,            color: '#22c55e' },
    { label: 'Este mes',    valor: matriculas.value.length, color: '#14b8a6' },
    { label: 'Total COP',   valor: formatCOP(total),       color: '#f59e0b' },
  ]
})

const colorEstado = (e) => ({ activa:'primary', suspendida:'warning', finalizada:'positive', cancelada:'grey' }[e] || 'grey')
const formatCOP   = (v) => new Intl.NumberFormat('es-CO', { notation:'compact', maximumFractionDigits:1 }).format(v)

function verFacturas(m) { /* TODO: abrir dialog */ }

async function cargar() {
  cargando.value = true
  try {
    const params = {}
    if (buscar.value)       params.buscar = buscar.value
    if (filtroEstado.value) params.estado = filtroEstado.value
    const { data } = await api.get('/matriculas', { params })
    matriculas.value = data.data?.data || []
  } finally { cargando.value = false }
}

async function cargarProgramas() {
  const { data } = await api.get('/programas')
  programas.value = (data.data || []).map(p => ({ label: p.nombre, value: p.id }))
}

async function crearMatricula() {
  guardando.value = true
  try {
    await api.post('/matriculas', form.value)
    $q.notify({ type: 'positive', message: 'Matrícula registrada.' })
    dialogNuevo.value = false
    cargar()
  } catch { $q.notify({ type: 'negative', message: 'Error al registrar.' }) }
  finally { guardando.value = false }
}

watch([buscar, filtroEstado], cargar)
onMounted(() => { cargar(); cargarProgramas() })
</script>
