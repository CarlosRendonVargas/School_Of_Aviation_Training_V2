<template>
  <q-page class="q-pa-md rac-page">
    <div class="row items-center q-mb-md">
      <q-icon name="request_quote" size="32px" color="blue-4" class="q-mr-sm icon-blue-glow" />
      <div>
        <h1 class="text-h5 text-white font-head q-my-none">Módulo 08: Administrativo y Financiero</h1>
        <div class="text-grey-5 font-mono text-caption">Control de Matrículas, Facturación Electrónica DIAN y Pagos</div>
      </div>
    </div>
    
    <div class="q-mt-md">
      <q-card class="premium-glass-card shadow-12 custom-tabs-card">
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="matriculas" icon="school" label="Matrículas" />
          <q-tab name="facturas" icon="receipt_long" label="Facturación" />
          <q-tab name="cartera" icon="money_off" label="Cartera Vencida" />
        </q-tabs>

        <q-separator color="grey-8" />

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
          <!-- MATRÍCULAS PANEL -->
          <q-tab-panel name="matriculas">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Estudiantes Matriculados</div>
                <q-btn color="primary" icon="add" label="Nueva Matrícula" outline rounded class="glass-btn" @click="dialogMatricula = true" />
            </div>

            <q-table
              :rows="matriculas"
              :columns="columnsMatriculas"
              row-key="id"
              class="rac-table bg-transparent text-white"
              flat
              bordered
              card-class="bg-transparent"
              table-header-class="text-grey-4"
              :loading="loadingMatriculas"
            >
              <template v-slot:body-cell-estado="props">
                <q-td :props="props">
                  <q-chip :color="props.row.estado === 'activa' ? 'green-8' : 'red-8'" text-color="white" size="sm">
                    {{ props.row.estado.toUpperCase() }}
                  </q-chip>
                </q-td>
              </template>
              <template v-slot:body-cell-estudiante="props">
                <q-td :props="props" class="text-weight-bold">
                  {{ props.row.estudiante?.persona?.nombres }} {{ props.row.estudiante?.persona?.apellidos }}
                </q-td>
              </template>
              <template v-slot:body-cell-valor_total="props">
                <q-td :props="props">{{ formatMoney(props.row.valor_total) }}</q-td>
              </template>
              <template v-slot:body-cell-acciones="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="blue-4" icon="edit" size="sm" @click="abrirEditarMatricula(props.row)" />
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>

          <!-- FACTURAS PANEL -->
          <q-tab-panel name="facturas">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Gestión de Facturación</div>
                <q-btn color="amber-5" icon="receipt" label="Generar Factura" outline rounded class="glass-btn text-amber-5" @click="prepararNuevaFactura" />
            </div>

            <q-table
              :rows="facturas"
              :columns="columnsFacturas"
              row-key="id"
              class="rac-table bg-transparent text-white"
              flat
              bordered
              table-header-class="text-grey-4"
              :loading="loadingFacturas"
            >
              <template v-slot:body-cell-estado="props">
                <q-td :props="props">
                  <q-chip 
                    :color="props.row.estado === 'pagada' ? 'green-8' : (props.row.estado === 'vencida' ? 'red-8' : 'amber-8')" 
                    text-color="white" size="sm">
                    {{ props.row.estado.toUpperCase() }}
                  </q-chip>
                </q-td>
              </template>
              <template v-slot:body-cell-total="props">
                <q-td :props="props" class="text-weight-bold text-amber">{{ formatMoney(props.row.total) }}</q-td>
              </template>
              <template v-slot:body-cell-saldo="props">
                <q-td :props="props" class="text-weight-bold text-red-4">{{ formatMoney(props.row.total - (props.row.total_pagado || 0)) }}</q-td>
              </template>
              <template v-slot:body-cell-acciones="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="primary" icon="attach_money" size="sm" @click="abrirAbono(props.row)" :disable="props.row.estado === 'pagada' || (props.row.total - (props.row.total_pagado || 0)) <= 0">
                    <q-tooltip>Registrar Pago / Abono</q-tooltip>
                  </q-btn>
                  <q-btn flat round color="grey-5" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)">
                    <q-tooltip>Exportar PDF (Formato DIAN)</q-tooltip>
                  </q-btn>
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>
          <!-- CARTERA PANEL -->
          <q-tab-panel name="cartera">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head text-red-4">Control de Cobros (Vencidos)</div>
                <q-btn flat color="grey-5" icon="refresh" label="Actualizar Cartera" size="sm" @click="cargarCartera" />
            </div>
            
            <q-table
              :rows="cartera"
              :columns="columnsFacturas"
              row-key="id"
              class="rac-table bg-transparent text-white"
              flat
              bordered
              :loading="loadingCartera"
            >
              <template v-slot:body-cell-estudiante="props">
                <q-td :props="props">
                  {{ props.row.matricula?.estudiante?.persona?.nombres }}
                </q-td>
              </template>
              <template v-slot:body-cell-total="props">
                <q-td :props="props" class="text-weight-bold">{{ formatMoney(props.row.total) }}</q-td>
              </template>
              <template v-slot:body-cell-saldo="props">
                <q-td :props="props" class="text-weight-bold text-red-5">{{ formatMoney(props.row.saldo) }}</q-td>
              </template>
              <template v-slot:body-cell-acciones="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="primary" icon="attach_money" size="sm" @click="abrirAbono(props.row)" />
                  <q-btn flat round color="grey-5" icon="mail" size="sm">
                    <q-tooltip>Enviar Notificación de Cobro</q-tooltip>
                  </q-btn>
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>

    <!-- Modal Crear Matrícula -->
    <q-dialog v-model="dialogMatricula" persistent>
      <q-card style="width: 500px; max-width: 90vw;" class="bg-dark text-white">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Registrar Nueva Matrícula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardarMatricula" class="q-gutter-md">
            <q-select 
              v-model="formMat.persona_id" 
              :options="estudiantes" 
              option-value="id"
              :option-label="opt => opt.persona?.nombres + ' ' + opt.persona?.apellidos + ' (' + opt.persona?.num_documento + ')'"
              emit-value
              map-options
              label="Seleccionar Estudiante" 
              outlined dark color="blue" 
            />
            <q-select 
              v-model="formMat.programa_id" 
              :options="programas" 
              option-value="id"
              :option-label="opt => opt.codigo + ' - ' + opt.nombre"
              emit-value
              map-options
              label="Seleccionar Programa" 
              outlined dark color="blue" 
            />
            
            <q-input v-model="formMat.fecha_matricula" type="date" label="Fecha" outlined dark color="blue" stack-label />
            
            <div class="row" style="gap: 16px">
                <q-input v-model.number="formMat.valor_total" type="number" label="Valor Total (COP)" outlined dark color="blue" class="col" />
                <q-select v-model="formMat.forma_pago" :options="['Contado', 'Cuotas', 'Crédito Estudiantil']" label="Forma de Pago" outlined dark color="blue" class="col" />
            </div>

            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Registrar" color="blue-4" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal Generar Factura -->
    <q-dialog v-model="dialogFactura" persistent>
      <q-card style="width: 500px; max-width: 90vw;" class="bg-dark text-white">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Generar Factura</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardarFactura" class="q-gutter-md">
            <q-select 
              v-model="formFact.matricula_id" 
              :options="matriculas" 
              option-value="id"
              :option-label="opt => opt.estudiante?.persona?.nombres + ' ' + opt.estudiante?.persona?.apellidos + ' (Matrícula #' + opt.id + ')'"
              emit-value
              map-options
              label="Seleccionar Matrícula (Estudiante)" 
              outlined dark color="amber" 
            />
            <q-input v-model="formFact.numero_factura" label="Número de Factura" outlined dark color="amber" />
            <q-input v-model="formFact.concepto" label="Concepto (Matrícula, Vuelo, etc.)" outlined dark color="amber" />
            <q-input v-model="formFact.fecha_factura" type="date" label="Fecha Emisión" outlined dark color="amber" stack-label />
            <q-input v-model="formFact.fecha_vencimiento_pago" type="date" label="Vencimiento" outlined dark color="amber" stack-label />
            <div class="row" style="gap: 16px">
              <q-input v-model.number="formFact.subtotal" type="number" label="Subtotal" outlined dark color="amber" class="col" />
              <q-input v-model.number="formFact.iva" type="number" label="IVA" outlined dark color="amber" class="col" />
            </div>
            <q-input v-model.number="formFact.total" type="number" label="Total" outlined dark color="amber" readonly />

            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Generar" color="amber-5" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal Editar Matrícula -->
    <q-dialog v-model="dialogEditMatricula" persistent>
      <q-card style="width: 400px; max-width: 90vw;" class="bg-dark text-white border-blue-left">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Modificar Matrícula</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit="actualizarMatricula" class="q-gutter-md">
            <q-select v-model="formMatEdit.estado" :options="['activa', 'retirado', 'suspendido']" label="Estado RAC" outlined dark color="blue" />
            <q-input v-model.number="formMatEdit.descuento" type="number" label="Descuento Comercial" outlined dark color="blue" />
            <q-input v-model="formMatEdit.observaciones" type="textarea" rows="3" label="Observaciones / Novedades" outlined dark color="blue" />
            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Guardar Cambios" color="blue-4" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal Registrar Abono Factura -->
    <q-dialog v-model="dialogAbono" persistent>
      <q-card style="width: 450px; max-width: 90vw;" class="bg-dark text-white border-green-left">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Registrar Pago a Factura</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <p class="text-grey-4">Factura <strong>{{ abonoTemp?.numero_factura }}</strong>. Saldo pendiente: <span class="text-amber">{{ formatMoney(abonoTemp?.total - (abonoTemp?.total_pagado || 0)) }}</span></p>
          <q-form @submit="guardarAbono" class="q-gutter-md">
            <q-input v-model="formAbono.fecha_pago" type="date" label="Fecha del Depósito" outlined dark color="green" stack-label />
            <q-input v-model.number="formAbono.valor" type="number" label="Valor a Abonar (COP)" outlined dark color="green" />
            <q-select v-model="formAbono.metodo" :options="['Transferencia', 'Efectivo', 'Tarjeta C/D', 'Cheque']" label="Método de Pago" outlined dark color="green" />
            <q-input v-model="formAbono.referencia" label="Número de Aprobación/Referencia" outlined dark color="green" />
            
            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Registrar Abono" color="green-4" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
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
const loadingCartera = ref(false)

const dialogMatricula = ref(false)
const dialogFactura = ref(false)
const dialogEditMatricula = ref(false)
const dialogAbono = ref(false)

const formMat = ref({ persona_id: '', programa_id: '', fecha_matricula: '', valor_total: 0, forma_pago: 'Contado' })
const formMatEdit = ref({ id: null, estado: '', descuento: 0, observaciones: '' })

const formFact = ref({ matricula_id: '', numero_factura: '', concepto: '', fecha_factura: '', fecha_vencimiento_pago: '', subtotal: 0, iva: 0, total: 0 })
const formAbono = ref({ valor: 0, metodo: 'Transferencia', referencia: '', fecha_pago: '' })
const abonoTemp = ref(null)

import { watch } from 'vue'
watch(() => [formFact.value.subtotal, formFact.value.iva], ([s, i]) => formFact.value.total = (s||0) + (i||0))

const columnsMatriculas = [
  { name: 'fecha_matricula', label: 'Fecha', align: 'left', field: 'fecha_matricula', sortable: true },
  { name: 'estudiante', label: 'Estudiante', align: 'left', field: row => row.estudiante?.persona?.nombres },
  { name: 'programa', label: 'Programa', align: 'left', field: row => row.programa?.nombre },
  { name: 'estado', label: 'Estado', align: 'center', field: 'estado' },
  { name: 'valor_total', label: 'Valor Total', align: 'right', field: 'valor_total' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const columnsFacturas = [
  { name: 'numero_factura', label: 'Nº Factura', align: 'left', field: 'numero_factura', sortable: true },
  { name: 'estudiante', label: 'Cliente', align: 'left', field: row => row.matricula?.estudiante?.persona?.nombres + ' ' + row.matricula?.estudiante?.persona?.apellidos },
  { name: 'fecha_factura', label: 'F. Emisión', align: 'left', field: 'fecha_factura' },
  { name: 'fecha_vencimiento_pago', label: 'F. Vencimiento', align: 'left', field: 'fecha_vencimiento_pago' },
  { name: 'concepto', label: 'Concepto', align: 'left', field: 'concepto' },
  { name: 'total', label: 'Total', align: 'right', field: 'total' },
  { name: 'saldo', label: 'Saldo Deuda', align: 'right', field: 'saldo' },
  { name: 'estado', label: 'Estado', align: 'center', field: 'estado' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const formatMoney = (val) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(val || 0)

const cargarMatriculas = async () => {
    loadingMatriculas.value = true
    try {
        const { data } = await api.get('/matriculas')
        matriculas.value = data.data || []
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error cargando matrículas' })
    } finally { loadingMatriculas.value = false }
}

const cargarFacturas = async () => {
    loadingFacturas.value = true
    try {
        const { data } = await api.get('/facturas')
        facturas.value = data.data || []
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error cargando facturas' })
    } finally { loadingFacturas.value = false }
}

const cargarCartera = async () => {
    loadingCartera.value = true
    try {
        const { data } = await api.get('/facturas/cartera/vencida')
        cartera.value = data.data || []
    } catch (e) {
        console.error('Error cargando cartera', e)
    } finally { loadingCartera.value = false }
}

const cargarEstudiantes = async () => {
    try {
        const { data } = await api.get('/usuarios')
        // Filtrar usuarios que su rol contenga la palabra estudiante
        const usuariosList = data.data?.data || data.data || []
        estudiantes.value = usuariosList.filter(u => u.rol && u.rol.nombre.toLowerCase().includes('estudiante')).map(u => ({
            id: u.persona?.id, // Use persona_id
            persona: u.persona
        })).filter(u => u.id)
    } catch (e) {
        console.error('Error cargando potenciales estudiantes', e)
    }
}

const cargarProgramas = async () => {
    try {
        const { data } = await api.get('/programas')
        programas.value = data.data || []
    } catch (e) {
        console.error('Error cargando programas', e)
    }
}

const guardarMatricula = async () => {
    try {
        const { data } = await api.post('/matriculas', formMat.value)
        matriculas.value.push(data.data)
        $q.notify({ color: 'positive', message: 'Matrícula guardada' })
        dialogMatricula.value = false
        cargarMatriculas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'No se pudo guardar la matrícula. Revisa IDs.' })
    }
}

const guardarFactura = async () => {
    try {
        const { data } = await api.post('/facturas', formFact.value)
        facturas.value.unshift(data.data)
        $q.notify({ color: 'positive', message: 'Factura generada' })
        dialogFactura.value = false
        cargarFacturas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'No se pudo generar factura. Revisa datos.' })
    }
}

const prepararNuevaFactura = async () => {
    // Reset form
    formFact.value = { 
        matricula_id: '', 
        numero_factura: 'Cargando...', 
        concepto: 'Pago de Matrícula / Servicios', 
        fecha_factura: new Date().toISOString().split('T')[0], 
        fecha_vencimiento_pago: new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0], 
        subtotal: 0, 
        iva: 0, 
        total: 0 
    }
    dialogFactura.value = true
    
    try {
        const { data } = await api.get('/facturas/proximo-numero')
        formFact.value.numero_factura = data.data
    } catch (e) {
        formFact.value.numero_factura = ''
        console.error('Error obteniendo proximo numero', e)
    }
}

const abrirEditarMatricula = (row) => {
    formMatEdit.value = {
        id: row.id,
        estado: row.estado,
        descuento: row.descuento || 0,
        observaciones: row.observaciones || ''
    }
    dialogEditMatricula.value = true
}

const actualizarMatricula = async () => {
    try {
        await api.put(`/matriculas/${formMatEdit.value.id}`, formMatEdit.value)
        $q.notify({ color: 'positive', message: 'Matrícula actualizada' })
        dialogEditMatricula.value = false
        cargarMatriculas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al actualizar.' })
    }
}

const abrirAbono = (row) => {
    abonoTemp.value = row
    formAbono.value = { 
        valor: row.total - (row.total_pagado || 0), 
        metodo: 'Transferencia', 
        referencia: '', 
        fecha_pago: new Date().toISOString().split('T')[0] 
    }
    dialogAbono.value = true
}

const guardarAbono = async () => {
    try {
        await api.post(`/facturas/${abonoTemp.value.id}/pagos`, formAbono.value)
        $q.notify({ color: 'positive', message: 'Abono registrado verificablemente.' })
        dialogAbono.value = false
        cargarFacturas() // Refresca recálculos de saldo
    } catch (e) {
        if(e.response?.status === 422) {
             $q.notify({ color: 'negative', message: e.response.data.mensaje || 'Error. Quizá factura ya pagada.' })
        } else {
             $q.notify({ color: 'negative', message: 'Error al registrar el depósito.' })
        }
    }
}

const generarPDF = async (factura) => {
    $q.notify({ 
        color: 'info', 
        message: `Generando PDF para Factura ${factura.numero_factura}...`,
        icon: 'hourglass_empty'
    })
    
    try {
        const { data } = await api.get(`/facturas/${factura.id}/pdf`)
        if (data.ok) {
            $q.notify({ 
                color: 'positive', 
                message: 'PDF Generado. El archivo se abrirá en una nueva pestaña.',
                icon: 'check_circle'
            })
            // En una app real, aquí abriríamos la URL del PDF
            // window.open(data.url, '_blank')
        }
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al generar el PDF regulatorio.' })
    }
}

onMounted(() => {
    cargarMatriculas()
    cargarFacturas()
    cargarCartera()
    cargarEstudiantes()
    cargarProgramas()
})

</script>

<style lang="scss" scoped>
.rac-page { background: #0a0c10; min-height: 100vh; }
.font-head { font-family: 'Syne', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }
.icon-blue-glow { filter: drop-shadow(0 0 10px rgba(96, 165, 250, 0.7)); }

.premium-glass-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;
}

.custom-tabs-card {
  overflow: hidden;
}

::v-deep(.q-table__card) {
  background-color: transparent !important;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
::v-deep(.q-table th) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-family: 'JetBrains Mono', monospace;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.05em;
}

.border-blue-left { border-left: 3px solid #60a5fa; }
.border-green-left { border-left: 3px solid #4ade80; }
::v-deep(.q-table td) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.glass-btn {
  background: rgba(255,255,255, 0.05);
  backdrop-filter: blur(4px);
  transition: all 0.3s ease;
  &:hover {
    background: rgba(255,255,255, 0.1);
    transform: translateY(-2px);
  }
}
</style>
