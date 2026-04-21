<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="request_quote" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="rac-page-subtitle">MÓDULO 08 · ADMINISTRACIÓN & FINANZAS</div>
          <h1 class="rac-page-title">Gestión Capital</h1>
        </div>
      </div>
    </div>

    <!-- ══ Contenedor Principal Premium ══ -->
    <q-card class="premium-glass-card">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-5"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        style="padding-left: 10px"
      >
        <q-tab name="matriculas" icon="how_to_reg" label="Matrículas" />
        <q-tab name="facturas" icon="receipt_long" label="Facturación" />
        <q-tab name="cartera" icon="money_off" label="Cartera Vencida" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
        
        <!-- ════ MATRÍCULAS ════ -->
        <q-tab-panel name="matriculas" class="q-pa-lg">
          <div class="row justify-between items-center q-mb-lg">
              <div>
                <div class="text-h6 font-head">Registro de Matrículas</div>
                <div class="text-caption text-grey-6 font-mono">Control de estudiantes inscritos en programas (PIA)</div>
              </div>
              <q-btn color="red-9" icon="add" label="Nueva Matrícula" class="premium-btn shadow-10" @click="dialogMatricula = true" />
          </div>

          <q-table
            :rows="matriculas"
            :columns="columnsMatriculas"
            row-key="id"
            class="rac-table"
            flat
            :loading="loadingMatriculas"
          >
            <template v-slot:body-cell-estado="props">
              <q-td :props="props">
                <q-badge :color="props.row.estado === 'activa' ? 'emerald' : 'red-9'" :label="props.row.estado.toUpperCase()" class="text-weight-bold" />
              </q-td>
            </template>
            <template v-slot:body-cell-valor_total="props">
              <q-td :props="props" class="font-mono text-weight-bold text-white text-right">
                {{ formatMoney(props.row.valor_total) }}
              </q-td>
            </template>
            <template v-slot:body-cell-acciones="props">
              <q-td :props="props" class="text-right">
                <q-btn flat round color="red-9" icon="manage_accounts" size="sm" @click="abrirEditarMatricula(props.row)">
                  <q-tooltip>Gestionar Matrícula</q-tooltip>
                </q-btn>
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ FACTURACIÓN ════ -->
        <q-tab-panel name="facturas" class="q-pa-lg">
          <div class="row justify-between items-center q-mb-lg">
              <div>
                <div class="text-h6 font-head">Facturación Electrónica</div>
                <div class="text-caption text-grey-6 font-mono">Emisión y control de documentos DIAN</div>
              </div>
              <q-btn color="red-9" icon="receipt" label="Generar Factura" class="premium-btn" @click="prepararNuevaFactura" />
          </div>

          <q-table
            :rows="facturas"
            :columns="columnsFacturas"
            row-key="id"
            class="rac-table"
            flat
            :loading="loadingFacturas"
          >
            <template v-slot:body-cell-estado="props">
              <q-td :props="props">
                <q-badge 
                  :color="props.row.estado === 'pagada' ? 'emerald' : (props.row.estado === 'vencida' ? 'red-9' : 'orange-9')" 
                  :label="props.row.estado.toUpperCase()" />
              </q-td>
            </template>
            <template v-slot:body-cell-total="props">
              <q-td :props="props" class="text-weight-bold font-mono text-right">{{ formatMoney(props.row.total) }}</q-td>
            </template>
            <template v-slot:body-cell-saldo="props">
              <q-td :props="props" class="text-weight-bold text-red-9 font-mono text-right">
                {{ formatMoney(props.row.total - (props.row.total_pagado || 0)) }}
              </q-td>
            </template>
            <template v-slot:body-cell-acciones="props">
              <q-td :props="props" class="text-right">
                <q-btn flat round color="red-9" icon="payments" size="sm" @click="abrirAbono(props.row)" :disable="props.row.estado === 'pagada'">
                  <q-tooltip>Registrar Abono</q-tooltip>
                </q-btn>
                <q-btn flat round color="grey-5" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)">
                  <q-tooltip>Exportar DIAN (PDF)</q-tooltip>
                </q-btn>
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ CARTERA ════ -->
        <q-tab-panel name="cartera" class="q-pa-lg text-center" v-if="cartera.length === 0">
            <q-icon name="check_circle" size="80px" color="emerald" class="opacity-20 q-mb-md" />
            <div class="text-h5 font-head text-emerald">Cartera al día</div>
            <div class="text-grey-6 font-mono">No hay facturas pendientes por cobro vencido.</div>
        </q-tab-panel>

        <q-tab-panel name="cartera" class="q-pa-lg" v-else>
            <div class="row justify-between items-center q-mb-lg">
                <div class="text-h6 font-head text-red-9">Control de Carteras Vencidas</div>
                <q-btn flat color="grey-6" icon="refresh" label="Sincronizar Datos" size="sm" @click="cargarCartera" no-caps />
            </div>
            <q-table :rows="cartera" :columns="columnsFacturas" row-key="id" class="rac-table" flat />
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

    <!-- ══ DIALOGS PREMIUM (Red Theme) ══ -->
    <q-dialog v-model="dialogMatricula" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card shadow-24" style="min-width: 500px">
        <q-card-section class="q-pa-lg text-white">
          <div class="text-h6 font-head q-mb-lg">Registrar Matrícula de Estudiante</div>
          <q-form @submit="guardarMatricula" class="row q-col-gutter-md">
            <div class="col-12"><q-select v-model="formMat.persona_id" :options="estudiantes" option-value="id" :option-label="opt => opt.persona?.nombres + ' ' + opt.persona?.apellidos" label="Seleccionar Estudiante *" outlined /></div>
            <div class="col-12"><q-select v-model="formMat.programa_id" :options="programas" option-value="id" :option-label="opt => opt.codigo + ' - ' + opt.nombre" label="Programa Académico (PIA) *" outlined /></div>
            <div class="col-6"><q-input v-model="formMat.fecha_matricula" type="date" label="Fecha de Registro" outlined stack-label /></div>
            <div class="col-6"><q-select v-model="formMat.forma_pago" :options="['Contado', 'Cuotas', 'Crédito Estudiantil']" label="Modalidad de Pago *" outlined /></div>
            <div class="col-12"><q-input v-model.number="formMat.valor_total" type="number" label="Costo Total Programa (COP) *" outlined /></div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Formalizar Matrícula" color="red-9" class="premium-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogFactura" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card shadow-24" style="min-width: 500px">
        <q-card-section class="q-pa-lg text-white">
          <div class="text-h6 font-head q-mb-lg">Generar Nueva Factura de Vuelo / Curso</div>
          <q-form @submit="guardarFactura" class="row q-col-gutter-md">
            <div class="col-12"><q-select v-model="formFact.matricula_id" :options="matriculas" option-value="id" :option-label="opt => opt.estudiante?.persona?.nombres + ' (Mat. #' + opt.id + ')'" label="Matrícula Asociada *" outlined /></div>
            <div class="col-6"><q-input v-model="formFact.numero_factura" label="Folio Factura" outlined /></div>
            <div class="col-6"><q-input v-model="formFact.concepto" label="Concepto General *" outlined /></div>
            <div class="col-12"><q-input v-model.number="formFact.total" type="number" label="Valor a Cobrar (COP) *" outlined /></div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Descartar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Emitir Factura Electrónica" color="red-9" class="premium-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogAbono" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card border-emerald shadow-24" style="min-width: 450px">
        <q-card-section class="q-pa-lg text-white">
          <div class="text-h6 font-head q-mb-md">Registrar Ingreso de Caja</div>
          <div class="text-caption text-grey-5 q-mb-lg font-mono">Factura: {{ abonoTemp?.numero_factura }} · Pendiente: {{ formatMoney(abonoTemp?.total - (abonoTemp?.total_pagado || 0)) }}</div>
          <q-form @submit="guardarAbono" class="row q-col-gutter-md">
            <div class="col-12"><q-input v-model.number="formAbono.valor" type="number" label="Valor Recibido *" outlined color="emerald" /></div>
            <div class="col-12"><q-select v-model="formAbono.metodo" :options="['Transferencia', 'Efectivo', 'Tarjeta', 'Cheque']" label="Método *" outlined color="emerald" /></div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cerrar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Guardar Recibo" color="emerald" class="text-dark premium-btn text-weight-bolder" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const tab = ref('matriculas')

const matriculas = ref([])
const facturas = ref([])
const cartera = ref([])
const estudiantes = ref([])
const programas = ref([])
const loadingMatriculas = ref(false)
const loadingFacturas = ref(false)

const dialogMatricula = ref(false)
const dialogFactura = ref(false)
const dialogEditMatricula = ref(false)
const dialogAbono = ref(false)

const formMat = ref({ persona_id: '', programa_id: '', fecha_matricula: '', valor_total: 0, forma_pago: 'Contado' })
const formMatEdit = ref({ id: null, estado: '', descuento: 0, observaciones: '' })

const formFact = ref({ matricula_id: '', numero_factura: '', concepto: '', fecha_factura: '', fecha_vencimiento_pago: '', subtotal: 0, iva: 0, total: 0 })
const formAbono = ref({ valor: 0, metodo: 'Transferencia', referencia: '', fecha_pago: '' })
const abonoTemp = ref(null)

const columnsMatriculas = [
  { name: 'fecha_matricula', label: 'Fecha', align: 'left', field: 'fecha_matricula', sortable: true },
  { name: 'estudiante', label: 'Beneficiario', align: 'left', field: row => row.estudiante?.persona?.nombres },
  { name: 'programa', label: 'Programa PIA', align: 'left', field: row => row.programa?.nombre },
  { name: 'estado', label: 'Estado RAC', align: 'center', field: 'estado' },
  { name: 'valor_total', label: 'Total Inversión', align: 'right', field: 'valor_total' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const columnsFacturas = [
  { name: 'numero_factura', label: 'Nº Folio', align: 'left', field: 'numero_factura', sortable: true },
  { name: 'estudiante', label: 'Titular', align: 'left', field: row => row.matricula?.estudiante?.persona?.nombres },
  { name: 'concepto', label: 'Concepto de Vuelo', align: 'left', field: 'concepto' },
  { name: 'total', label: 'Total', align: 'right', field: 'total' },
  { name: 'saldo', label: 'Remanente', align: 'right' },
  { name: 'estado', label: 'Estado', align: 'center', field: 'estado' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const formatMoney = (val) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val || 0)

const cargarTodo = async () => {
    loadingMatriculas.value = true
    loadingFacturas.value = true
    try {
        const [mats, facts, carts, users, progs] = await Promise.all([
            api.get('/matriculas'),
            api.get('/facturas'),
            api.get('/facturas/cartera/vencida'),
            api.get('/usuarios'),
            api.get('/programas')
        ])
        matriculas.value = mats.data.data || []
        facturas.value = facts.data.data || []
        cartera.value = carts.data.data || []
        programas.value = progs.data.data || []
        
        const usuariosList = users.data?.data?.data || users.data?.data || []
        estudiantes.value = usuariosList.filter(u => u.rol && u.rol.nombre.toLowerCase().includes('estudiante')).map(u => ({
            id: u.persona?.id,
            persona: u.persona
        }))
    } finally {
        loadingMatriculas.value = false
        loadingFacturas.value = false
    }
}

const guardarMatricula = async () => {
    try {
        await api.post('/matriculas', formMat.value)
        $q.notify({ type: 'positive', message: 'Matrícula exitosa' })
        dialogMatricula.value = false
        cargarTodo()
    } catch { $q.notify({ type: 'negative', message: 'Error de registro' }) }
}

const prepararNuevaFactura = async () => {
    formFact.value = { 
        matricula_id: '', numero_factura: 'F-GEN', concepto: 'Servicios Aeronáuticos', 
        fecha_factura: new Date().toISOString().split('T')[0], total: 0 
    }
    dialogFactura.value = true
}

const guardarFactura = async () => {
    try {
        await api.post('/facturas', formFact.value)
        $q.notify({ type: 'positive', message: 'Factura emitida' })
        dialogFactura.value = false
        cargarTodo()
    } catch { $q.notify({ type: 'negative', message: 'Error DIAN' }) }
}

const abrirAbono = (row) => {
    abonoTemp.value = row
    formAbono.value = { valor: row.total - (row.total_pagado || 0), metodo: 'Transferencia', fecha_pago: new Date().toISOString().split('T')[0] }
    dialogAbono.value = true
}

const guardarAbono = async () => {
    try {
        await api.post(`/facturas/${abonoTemp.value.id}/pagos`, formAbono.value)
        $q.notify({ color: 'emerald', message: 'Recibo de caja generado.', textColor: 'dark', icon: 'verified' })
        dialogAbono.value = false
        cargarTodo()
    } catch { $q.notify({ type: 'negative', message: 'Error en proceso' }) }
}

onMounted(cargarTodo)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.text-emerald { color: #10b981; }
.opacity-20 { opacity: 0.2; }
.border-emerald { border-left: 5px solid #10b981 !important; }
.bg-transparent { background: transparent !important; }
</style>
