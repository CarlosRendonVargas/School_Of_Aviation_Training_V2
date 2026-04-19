<template>
  <q-page padding style="padding-bottom:80px">

    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          Facturación electrónica DIAN
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Facturación y Pagos</div>
      </div>
      <q-btn unelevated color="primary" icon="add" label="Nueva factura"
        @click="dialogNueva = true" no-caps style="border-radius:8px" />
    </div>

    <!-- Tabs -->
    <q-tabs v-model="tab" dense align="left" class="q-mb-md"
      active-color="primary" indicator-color="primary"
      style="border-bottom:1px solid rgba(255,255,255,.08)">
      <q-tab name="todas"   label="Todas"         no-caps />
      <q-tab name="pend"    label="Pendientes"     no-caps />
      <q-tab name="venc"    label="Vencidas"       no-caps />
      <q-tab name="pagadas" label="Pagadas"        no-caps />
      <q-tab name="cartera" label="Cartera vencida" no-caps />
    </q-tabs>

    <!-- KPIs del mes -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-6 col-md-3" v-for="k in kpis" :key="k.label">
        <div class="stat-card">
          <div class="stat-num" :style="`color:${k.color}`">{{ k.valor }}</div>
          <div class="stat-label">{{ k.label }}</div>
        </div>
      </div>
    </div>

    <!-- Tabla de facturas -->
    <q-table
      :rows="facturasFiltradas"
      :columns="columnas"
      :loading="cargando"
      flat dark class="tabla-rac"
      style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
      row-key="id"
      :rows-per-page-options="[20,50]"
    >
      <template #top>
        <div class="row full-width q-col-gutter-sm items-center">
          <div class="col-12 col-sm-5">
            <q-input v-model="buscar" outlined dense dark bg-color="grey-10"
              placeholder="Buscar número de factura, CUFE…" clearable>
              <template #prepend><q-icon name="search" color="grey-6" size="18px" /></template>
            </q-input>
          </div>
          <div class="col-6 col-sm-3">
            <q-input v-model="fechaDesde" outlined dense dark bg-color="grey-10"
              type="date" label="Desde" stack-label />
          </div>
          <div class="col-6 col-sm-3">
            <q-input v-model="fechaHasta" outlined dense dark bg-color="grey-10"
              type="date" label="Hasta" stack-label />
          </div>
        </div>
      </template>

      <template #body-cell-estado="{ value }">
        <q-td>
          <q-chip dense :color="colorEstado(value)" text-color="white"
            :label="value.toUpperCase()" style="font-size:10px;font-family:monospace" />
        </q-td>
      </template>
      <template #body-cell-total="{ row }">
        <q-td>
          <span class="font-mono" :class="row.estado === 'pagada' ? 'text-positive' : 'text-white'">
            {{ formatCOP(row.total) }}
          </span>
        </q-td>
      </template>
      <template #body-cell-acciones="{ row }">
        <q-td>
          <q-btn flat round dense icon="visibility" color="grey-5" size="sm" @click="verFactura(row)" />
          <q-btn flat round dense icon="payments" color="positive" size="sm"
            @click="registrarPago(row)" v-if="row.estado === 'pendiente' || row.estado === 'vencida'" />
          <q-btn flat round dense icon="picture_as_pdf" color="primary" size="sm"
            @click="generarPdf(row)" />
        </q-td>
      </template>
      <template #no-data>
        <div class="full-width text-center q-py-xl" style="color:#475569;font-size:13px">
          <q-icon name="receipt" size="48px" class="q-mb-sm" /><br>
          Sin facturas para los filtros seleccionados
        </div>
      </template>
    </q-table>

    <!-- Dialog detalle + registrar pago -->
    <q-dialog v-model="dialogPago" persistent>
      <q-card dark style="min-width:360px;background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:12px">
        <q-toolbar style="background:#151920;border-bottom:1px solid rgba(255,255,255,.08)">
          <q-toolbar-title class="font-head">Registrar pago</q-toolbar-title>
          <q-btn flat round dense icon="close" @click="dialogPago=false" color="grey-5" />
        </q-toolbar>
        <q-card-section class="q-pa-lg" v-if="facturaSeleccionada">
          <div class="q-mb-md" style="background:rgba(255,255,255,.03);border-radius:8px;padding:12px">
            <div class="font-mono text-primary" style="font-size:13px;font-weight:600">{{ facturaSeleccionada.numero_factura }}</div>
            <div style="font-size:12px;color:#94a3b8;margin-top:3px">Total: {{ formatCOP(facturaSeleccionada.total) }}</div>
          </div>
          <div class="q-gutter-md">
            <q-input v-model="formPago.valor" outlined dark bg-color="grey-10"
              label="Valor del pago *" type="number" dense
              :hint="`Máx: ${formatCOP(facturaSeleccionada.total)}`" />
            <q-select v-model="formPago.metodo" outlined dark bg-color="grey-10"
              label="Método de pago *" dense emit-value map-options
              :options="[
                { label:'Efectivo',       value:'efectivo'     },
                { label:'Transferencia',  value:'transferencia'},
                { label:'Tarjeta',        value:'tarjeta'      },
                { label:'Cheque',         value:'cheque'       },
              ]" />
            <q-input v-model="formPago.referencia" outlined dark bg-color="grey-10"
              label="Referencia / No. transacción" dense />
            <q-input v-model="formPago.fecha_pago" outlined dark bg-color="grey-10"
              type="date" label="Fecha de pago *" dense />
          </div>
        </q-card-section>
        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancelar" @click="dialogPago=false" no-caps color="grey-5" />
          <q-btn unelevated color="positive" label="Registrar pago" no-caps
            @click="guardarPago" :loading="guardando" style="border-radius:6px" />
        </q-card-actions>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()

const facturas          = ref([])
const cargando          = ref(false)
const tab               = ref('todas')
const buscar            = ref('')
const fechaDesde        = ref('')
const fechaHasta        = ref('')
const dialogNueva       = ref(false)
const dialogPago        = ref(false)
const facturaSeleccionada = ref(null)
const guardando         = ref(false)

const formPago = ref({ valor: '', metodo: null, referencia: '', fecha_pago: new Date().toISOString().slice(0,10) })

const estadoFiltro = computed(() =>
  ({ todas: null, pend: 'pendiente', venc: 'vencida', pagadas: 'pagada', cartera: 'vencida' }[tab.value])
)

const facturasFiltradas = computed(() => {
  let lista = facturas.value
  if (estadoFiltro.value) lista = lista.filter(f => f.estado === estadoFiltro.value)
  if (buscar.value) {
    const b = buscar.value.toLowerCase()
    lista = lista.filter(f => f.numero_factura?.toLowerCase().includes(b) || f.cufe?.toLowerCase().includes(b))
  }
  if (fechaDesde.value) lista = lista.filter(f => f.fecha_factura >= fechaDesde.value)
  if (fechaHasta.value) lista = lista.filter(f => f.fecha_factura <= fechaHasta.value)
  return lista
})

const columnas = [
  { name:'numero_factura', label:'Factura',  field:'numero_factura', align:'left', sortable:true },
  { name:'fecha_factura',  label:'Fecha',    field:'fecha_factura',  align:'left', sortable:true },
  { name:'concepto',       label:'Concepto', field: row => row.concepto?.slice(0,40), align:'left' },
  { name:'total',          label:'Total',    field:'total',          align:'right', sortable:true },
  { name:'estado',         label:'Estado',   field:'estado',         align:'center' },
  { name:'acciones',       label:'',         field:'id',             align:'right' },
]

const kpis = computed(() => {
  const pendientes = facturas.value.filter(f => f.estado === 'pendiente')
  const vencidas   = facturas.value.filter(f => f.estado === 'vencida')
  const pagadas    = facturas.value.filter(f => f.estado === 'pagada')
  const totalPend  = pendientes.reduce((a, f) => a + parseFloat(f.total || 0), 0)
  return [
    { label:'Pendientes',       valor: pendientes.length,    color:'#f59e0b' },
    { label:'Valor pendiente',  valor: formatCOP(totalPend, true), color:'#f59e0b' },
    { label:'Vencidas',         valor: vencidas.length,      color:'#ef4444' },
    { label:'Pagadas (total)',  valor: pagadas.length,       color:'#22c55e' },
  ]
})

const colorEstado = (e) => ({ pendiente:'warning', pagada:'positive', anulada:'negative', vencida:'red' }[e] || 'grey')

function formatCOP(val, abrev=false) {
  const n = parseFloat(val || 0)
  if (abrev && n >= 1_000_000) return `$${(n/1_000_000).toFixed(1)}M`
  return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0 }).format(n)
}

function verFactura(f) { facturaSeleccionada.value = f }

function registrarPago(f) {
  facturaSeleccionada.value = f
  formPago.value = { valor: f.total, metodo: null, referencia: '', fecha_pago: new Date().toISOString().slice(0,10) }
  dialogPago.value = true
}

async function guardarPago() {
  if (!formPago.value.metodo || !formPago.value.valor) {
    $q.notify({ type: 'warning', message: 'Complete los campos requeridos.' })
    return
  }
  guardando.value = true
  try {
    await api.post(`/facturas/${facturaSeleccionada.value.id}/pagos`, formPago.value)
    $q.notify({ type: 'positive', message: 'Pago registrado correctamente.' })
    dialogPago.value = false
    cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al registrar el pago.' })
  } finally { guardando.value = false }
}

async function generarPdf(f) {
  $q.loading.show({ message: 'Generando PDF…' })
  try {
    await api.get(`/facturas/${f.id}/pdf`)
    $q.notify({ type: 'positive', message: 'PDF generado.' })
  } finally { $q.loading.hide() }
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/facturas', { params: { per_page: 100 } })
    facturas.value = data.data?.data || []
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>
