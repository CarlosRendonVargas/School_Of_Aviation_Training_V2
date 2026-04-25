<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">Presupuesto y Caja Menor</div>
        <h1 class="rac-page-title">Gastos <span class="text-red-9">Operativos</span></h1>
      </div>
      <q-btn unelevated color="red-9" icon="add_circle" label="Registrar Gasto"
        class="premium-btn" @click="abrirForm()" />
    </div>

    <!-- KPIs por tipo -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="t in resumenTipos" :key="t.tipo" class="col-6 col-md-2">
        <q-card class="premium-glass-card q-pa-sm text-center">
          <q-icon :name="iconoTipo(t.tipo)" color="red-5" size="24px" />
          <div class="text-weight-bold text-white q-mt-xs" style="font-size:13px">{{ formatCOP(t.total) }}</div>
          <div class="font-mono text-grey-6" style="font-size:9px; text-transform:uppercase">{{ t.tipo.replace('_', ' ') }}</div>
        </q-card>
      </div>
      <div class="col-12 col-md-2">
        <q-card class="premium-glass-card q-pa-sm text-center" style="border: 1px solid rgba(74,222,128,0.3)">
          <q-icon name="account_balance" color="green-5" size="24px" />
          <div class="text-h6 text-green-5 text-weight-bold q-mt-xs">{{ formatCOP(totalMes) }}</div>
          <div class="font-mono text-grey-6" style="font-size:9px">TOTAL MES</div>
        </q-card>
      </div>
    </div>

    <!-- Filtros -->
    <q-card class="premium-glass-card q-mb-md">
      <q-card-section class="row items-center q-gutter-md">
        <q-input v-model="filtroMes" type="month" dark outlined dense label="Mes" style="min-width:160px" @update:model-value="cargarResumen" />
        <q-select v-model="filtroTipo" :options="tiposOpts" emit-value map-options dark outlined dense label="Tipo" clearable style="min-width:160px" />
      </q-card-section>
    </q-card>

    <!-- Tabla -->
    <q-card class="premium-glass-card">
      <div v-if="cargando" class="flex flex-center q-pa-xl"><q-spinner-cube color="red-9" size="40px" /></div>
      <q-table v-else :rows="gastosFiltrados" :columns="columnas" row-key="id" dark flat
        :rows-per-page-options="[20, 50]" no-data-label="Sin gastos registrados" class="bg-transparent">
        <template #body-cell-tipo="{ row }">
          <q-td><q-badge color="red-9" class="font-mono" style="font-size:9px">{{ row.tipo.replace('_',' ').toUpperCase() }}</q-badge></q-td>
        </template>
        <template #body-cell-valor="{ row }">
          <q-td class="text-amber-5 font-mono text-weight-bold">{{ formatCOP(row.valor) }}</q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(500px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h5 text-white font-head text-weight-bolder">Registrar Gasto Operativo</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Tipo</div>
            <q-select v-model="form.tipo" :options="tiposOpts" emit-value map-options dark outlined dense />
          </div>
          <div>
            <div class="campo-label required">Concepto</div>
            <q-input v-model="form.concepto" dark outlined dense placeholder="Descripción del gasto..." />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Valor</div>
              <q-input v-model.number="form.valor" type="number" dark outlined dense prefix="$" />
            </div>
            <div class="col-6">
              <div class="campo-label required">Fecha</div>
              <q-input v-model="form.fecha" type="date" dark outlined dense />
            </div>
          </div>
          <div>
            <div class="campo-label required">Responsable</div>
            <q-input v-model="form.responsable" dark outlined dense placeholder="Nombre de quien autoriza..." />
          </div>
          <div>
            <div class="campo-label">Observaciones</div>
            <q-input v-model="form.observaciones" dark outlined dense type="textarea" rows="2" />
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" label="Registrar Gasto" class="premium-btn" :loading="guardando" @click="guardar" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q       = useQuasar()
const cargando = ref(false)
const guardando= ref(false)
const gastos   = ref([])
const resumen  = ref({ total: 0, tipos: {} })
const filtroMes   = ref(new Date().toISOString().slice(0, 7))
const filtroTipo  = ref(null)
const dialogForm  = ref(false)

const tiposOpts = [
  { value: 'caja_menor',    label: 'Caja Menor' },
  { value: 'proveedor',     label: 'Proveedor' },
  { value: 'servicio',      label: 'Servicio' },
  { value: 'mantenimiento', label: 'Mantenimiento' },
  { value: 'administrativo',label: 'Administrativo' },
  { value: 'otro',          label: 'Otro' },
]

const iconoTipo = t => ({ caja_menor: 'point_of_sale', proveedor: 'local_shipping', servicio: 'build', mantenimiento: 'settings', administrativo: 'business_center', otro: 'receipt' }[t] || 'receipt')

const formVacio = () => ({ tipo: 'caja_menor', concepto: '', valor: 0, fecha: new Date().toISOString().slice(0,10), responsable: '', observaciones: '' })
const form = ref(formVacio())

const formatCOP = v => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0)

const totalMes       = computed(() => resumen.value.total || 0)
const resumenTipos   = computed(() => Object.entries(resumen.value.tipos || {}).map(([tipo, total]) => ({ tipo, total })))
const gastosFiltrados = computed(() => {
  let r = gastos.value
  if (filtroTipo.value) r = r.filter(g => g.tipo === filtroTipo.value)
  return r
})

const columnas = [
  { name: 'fecha',       label: 'Fecha',        field: 'fecha',        align: 'left'  },
  { name: 'tipo',        label: 'Tipo',         field: 'tipo',         align: 'left'  },
  { name: 'concepto',    label: 'Concepto',     field: 'concepto',     align: 'left'  },
  { name: 'valor',       label: 'Valor',        field: 'valor',        align: 'right' },
  { name: 'responsable', label: 'Responsable',  field: 'responsable',  align: 'left'  },
]

async function cargar() {
  cargando.value = true
  try {
    const params = { fecha_desde: `${filtroMes.value}-01`, fecha_hasta: `${filtroMes.value}-31` }
    const { data } = await api.get('/gastos', { params })
    gastos.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar gastos.' })
  } finally {
    cargando.value = false
  }
}

async function cargarResumen() {
  try {
    const { data } = await api.get('/gastos/resumen', { params: { mes: filtroMes.value } })
    resumen.value = data.data
  } catch { /* silencioso */ }
  await cargar()
}

async function guardar() {
  if (!form.value.concepto || !form.value.valor || !form.value.fecha || !form.value.responsable) {
    $q.notify({ type: 'warning', message: 'Concepto, valor, fecha y responsable son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    await api.post('/gastos', form.value)
    $q.notify({ type: 'positive', message: 'Gasto registrado.' })
    dialogForm.value = false
    await cargarResumen()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al registrar el gasto.' })
  } finally {
    guardando.value = false
  }
}

function abrirForm() { form.value = formVacio(); dialogForm.value = true }

onMounted(cargarResumen)
</script>
