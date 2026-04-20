<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Gestión Financiera Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="account_balance_wallet" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">GESTIÓN DE TESORERÍA Y CAJA · FACTURACIÓN ELECTRÓNICA DIAN</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Finanzas y Recaudos</h1>
        </div>
      </div>
      <q-btn color="red-9" icon="add" label="Emitir Nueva Factura" class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" @click="dialogNueva = true" />
    </div>

    <!-- ══ Navegación Financiera de Cristal ══ -->
    <q-card class="premium-glass-card shadow-24 overflow-hidden q-mb-xl rounded-20 bonus-grid">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-5 border-bottom-border"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        style="padding-left: 10px"
      >
        <q-tab name="todas" label="BALANCE CONSOLIDADO" />
        <q-tab name="pend" label="MISIONES POR COBRAR" />
        <q-tab name="venc" label="ALERTAS DE CARTERA" />
        <q-tab name="pagadas" label="RECAUDOS CERTIFICADOS" />
        <q-tab name="cartera" label="ANÁLISIS ESTRATÉGICO" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white min-h-10">
        <q-tab-panel name="todas" class="q-pa-none"></q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- ══ KPIs de Tesorería Aeroportuaria Inmersivos ══ -->
    <div class="row q-col-gutter-xl q-mb-xl">
      <div class="col-6 col-md-3" v-for="k in kpis" :key="k.label">
        <q-card class="premium-glass-card q-pa-xl text-center border-red-low shadow-24 welcome-hero overflow-hidden">
          <div class="hero-glow"></div>
          <div class="relative-position">
             <div class="text-h4 text-weight-bolder font-mono q-mb-xs line-height-1" :style="`color:${k.color}; text-shadow: 0 0 10px ${k.color}44`">{{ k.valor }}</div>
             <q-separator dark class="q-my-sm opacity-5" />
             <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ k.label }}</div>
          </div>
        </q-card>
      </div>
    </div>

    <!-- ══ Consola de Facturación de Cristal ══ -->
    <q-card class="premium-glass-card shadow-24 border-red-low rounded-20 overflow-hidden">
        <q-table 
           :rows="facturasFiltradas" 
           :columns="columnas" 
           :loading="cargando" 
           flat dark 
           class="rac-table"
           row-key="id"
           :rows-per-page-options="[20,50]"
        >
          <template #top>
            <div class="row full-width q-col-gutter-xl items-center q-pa-xl border-bottom-border bg-black-20">
              <div class="col-12 col-md-4">
                <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Localizador de Factura / CUFE</div>
                <q-input v-model="buscar" filled dark class="premium-input-login" placeholder="Buscar por referencia...">
                  <template #prepend><q-icon name="manage_search" color="red-9" /></template>
                </q-input>
              </div>
              <div class="col-6 col-md-4">
                <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Desde el Periodo</div>
                <q-input v-model="fechaDesde" filled dark class="premium-input-login" type="date" stack-label>
                   <template #prepend><q-icon name="calendar_today" color="red-9" /></template>
                </q-input>
              </div>
              <div class="col-6 col-md-4">
                <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Hasta el Periodo</div>
                <q-input v-model="fechaHasta" filled dark class="premium-input-login" type="date" stack-label>
                   <template #prepend><q-icon name="event" color="red-9" /></template>
                </q-input>
              </div>
            </div>
          </template>

          <template #body-cell-estado="props">
            <q-td :props="props" class="text-center">
               <q-badge outline :color="colorEstado(props.value)" :label="props.value.toUpperCase()" class="font-mono text-weight-bolder q-px-xl q-py-xs shadow-24" />
            </q-td>
          </template>

          <template #body-cell-total="props">
            <q-td :props="props">
              <div class="font-mono text-weight-bolder text-h6" :class="props.row.estado === 'pagada' ? 'text-emerald' : 'text-white'">
                {{ formatCOP(props.row.total) }}
              </div>
            </q-td>
          </template>

          <template #body-cell-numero_factura="props">
            <q-td :props="props">
               <div class="font-mono text-weight-bolder text-grey-5" style="letter-spacing: 1px">{{ props.value }}</div>
            </q-td>
          </template>

          <template #body-cell-acciones="props">
            <q-td :props="props" class="text-right">
              <div class="row q-gutter-x-sm justify-end">
                 <q-btn flat round dense icon="visibility" color="grey-6" size="md" @click="verFactura(props.row)" class="shadow-inner" />
                 <q-btn flat round dense icon="point_of_sale" color="emerald" size="md" class="shadow-inner"
                   @click="registrarPago(props.row)" v-if="props.row.estado === 'pendiente' || props.row.estado === 'vencida'">
                   <q-tooltip class="bg-emerald text-white font-mono uppercase">REGISTRAR RECAUDO EN CAJA</q-tooltip>
                 </q-btn>
                 <q-btn flat round dense icon="picture_as_pdf" color="red-9" size="md" @click="generarPdf(props.row)" class="shadow-inner">
                   <q-tooltip class="bg-red-9 text-white font-mono uppercase">DESCARGAR SOPORTE FISCAL PDF</q-tooltip>
                 </q-btn>
              </div>
            </q-td>
          </template>
        </q-table>
    </q-card>

    <!-- ══ DIÁLOGO: ARQUEO DE CAJA Y RECAUDO ══ -->
    <q-dialog v-model="dialogPago" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(500px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon name="receipt_long" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Recaudos y Pagos</div>
           </div>
           <q-btn flat round dense icon="close" v-close-popup color="grey-7" class="shadow-inner" />
        </div>

        <q-card-section class="q-pa-none" v-if="facturaSeleccionada">
          <div class="q-mb-xl q-pa-xl text-center premium-glass-card border-red-low shadow-inner welcome-hero overflow-hidden">
             <div class="hero-glow"></div>
             <div class="relative-position">
                <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm" style="font-size:9px">FACTURA Nº {{ facturaSeleccionada.numero_factura }}</div>
                <div class="text-h3 text-weight-bolder text-white font-mono line-height-1 glow-red">{{ formatCOP(facturaSeleccionada.total) }}</div>
                <div class="text-caption text-red-9 font-mono text-weight-bolder q-mt-sm uppercase">Pendiente de Liquidación</div>
             </div>
          </div>

          <q-form class="q-gutter-y-lg">
             <q-input v-model="formPago.valor" filled dark label="VALOR A REGISTRAR EN CAJA" type="number" class="premium-input-login" stack-label>
                <template #prepend><q-icon name="payments" color="emerald" /></template>
             </q-input>
             
             <q-select 
                v-model="formPago.metodo" 
                :options="[
                  { label:'Efectivo en Caja', value:'efectivo' },
                  { label:'Transferencia Bancaria', value:'transferencia' },
                  { label:'Tarjeta de Crédito/Débito', value:'tarjeta' },
                  { label:'Cheque / Otros', value:'cheque' }
                ]" 
                filled dark label="MÉTODO DE PAGO CERTIFICADO" 
                class="premium-input-login" 
                emit-value map-options stack-label 
             >
                <template #prepend><q-icon name="account_balance" color="red-9" /></template>
             </q-select>

             <q-input v-model="formPago.referencia" filled dark label="REFERENCIA DE TRANSACCIÓN" class="premium-input-login" stack-label placeholder="Nº de aprobación, consignación..." />
             
             <q-input v-model="formPago.fecha_pago" type="date" filled dark label="FECHA DE CAJA" class="premium-input-login" stack-label />
             
             <q-btn color="emerald" label="Confirmar y Sincronizar Pago" icon="verified" class="full-width premium-btn q-py-lg shadow-24 text-weight-bolder" @click="guardarPago" :loading="guardando" />
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

const estadoFiltro = computed(() => ({ todas: null, pend: 'pendiente', venc: 'vencida', pagadas: 'pagada', cartera: 'vencida' }[tab.value]))

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
  { name:'numero_factura', label:'REF. FISCAL',  field:'numero_factura', align:'left', sortable:true },
  { name:'fecha_factura',  label:'EMISIÓN DIAN', field:'fecha_factura',  align:'left', sortable:true },
  { name:'concepto',       label:'CONCEPTO OPERATIVO', field: row => row.concepto || 'Entrenamiento de Vuelo / PIA', align:'left' },
  { name:'total',          label:'VALOR BRUTO',    field:'total',          align:'right', sortable:true },
  { name:'estado',         label:'ESTATUS',        field:'estado',         align:'center' },
  { name:'acciones',       label:'OPERACIONES',    field:'id',             align:'right' },
]

const kpis = computed(() => {
  const pendientes = facturas.value.filter(f => f.estado === 'pendiente')
  const vencidas   = facturas.value.filter(f => f.estado === 'vencida')
  const pagadas    = facturas.value.filter(f => f.estado === 'pagada')
  const totalPend  = pendientes.reduce((a, f) => a + parseFloat(f.total || 0), 0)
  return [
    { label:'Misiones por Cobrar', valor: pendientes.length, color:'#A10B13' },
    { label:'Cartera Aeroportuaria', valor: formatCOP(totalPend, true), color:'#fff' },
    { label:'Alertas de Cartera', valor: vencidas.length, color:'#orange-10' },
    { label:'Recaudos Certificados', valor: pagadas.length, color:'#10b981' },
  ]
})

const colorEstado = (e) => ({ pendiente:'orange-10', pagada:'emerald', anulada:'red-10', vencida:'red-9' }[e] || 'grey-8')

function formatCOP(val, abrev=false) {
  const n = parseFloat(val || 0)
  if (abrev && n >= 1_000_000) return `$${(n/1_000_000).toFixed(1)}M`
  return new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0 }).format(n)
}

function verFactura(f) { facturaSeleccionada.value = f; $q.notify({ color: 'red-9', icon: 'visibility', message: 'Sincronizando visor electrónico...' }) }

function registrarPago(f) {
  facturaSeleccionada.value = f
  formPago.value = { valor: f.total, metodo: null, referencia: '', fecha_pago: new Date().toISOString().slice(0,10) }
  dialogPago.value = true
}

async function guardarPago() {
  if (!formPago.value.metodo || !formPago.value.valor) { $q.notify({ color: 'orange-10', message: 'Complete los parámetros de liquidación.' }); return }
  guardando.value = true
  try {
    await api.post(`/facturas/${facturaSeleccionada.value.id}/pagos`, formPago.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Liquidación de factura certificada exitosamente.' })
    dialogPago.value = false; cargar()
  } catch { $q.notify({ color: 'negative', message: 'Error en la sincronización contable.' }) } 
  finally { guardando.value = false }
}

async function generarPdf(f) {
  $q.loading.show({ message: 'Compilando Soporte PDF UAEAC/DIAN...' })
  try { await api.get(`/facturas/${f.id}/pdf`); $q.notify({ color: 'red-9', icon: 'picture_as_pdf', message: 'PDF Exportado a archivos locales.' }) } 
  finally { $q.loading.hide() }
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/facturas', { params: { per_page: 100 } })
    facturas.value = data.data?.data || data.data || []
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.98); opacity: 0.8; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-top { border-top: 5px solid #A10B13 !important; }
.border-red-left { border-left: 5px solid #A10B13 !important; }
.border-bottom-border { border-bottom: 1px solid rgba(255,255,255,0.05); }
.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.rounded-20 { border-radius: 20px; }
.line-height-1 { line-height: 1.1; }
.min-h-10 { min-height: 10px; }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.15) 0%, transparent 50%); }
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.glow-red { text-shadow: 0 0 20px rgba(161, 11, 19, 0.6); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}
</style>
