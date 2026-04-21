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
        class="text-grey-5 border-bottom-border"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        outside-arrows
        mobile-arrows
        style="padding-left: 10px"
      >
        <q-tab name="matriculas" icon="how_to_reg" :label="$q.screen.gt.xs ? 'Matrículas' : 'Matrículas'" />
        <q-tab name="facturas"   icon="receipt_long" :label="$q.screen.gt.xs ? 'Facturación' : 'Facturas'" />
        <q-tab name="cartera"   icon="money_off" :label="$q.screen.gt.xs ? 'Cartera Vencida' : 'Cartera'" />
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
            :grid="$q.screen.lt.md"
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

            <!-- Modo Grid (Cards) para Matrículas -->
            <template v-slot:item="props">
              <div class="col-12 q-pa-xs grid-style-transition">
                 <q-card class="premium-glass-card shadow-24 q-mb-sm p-0 border-red-low">
                    <q-card-section>
                       <div class="row items-center justify-between">
                         <q-badge :color="props.row.estado === 'activa' ? 'emerald' : 'red-9'" :label="props.row.estado.toUpperCase()" class="text-weight-bold" />
                         <span class="font-mono text-grey-5">{{ props.row.fecha_matricula ? props.row.fecha_matricula.slice(0,10) : '' }}</span>
                       </div>
                       <div class="text-white text-h6 font-head q-mt-sm">{{ props.row.estudiante?.persona?.nombres }} {{ props.row.estudiante?.persona?.apellidos }}</div>
                       <div class="text-caption text-grey-4">Programa: {{ props.row.programa?.nombre }}</div>
                       <q-separator dark class="opacity-5 q-my-sm" />
                       <div class="row justify-between items-center">
                         <div class="font-mono text-weight-bold text-emerald">{{ formatMoney(props.row.valor_total) }}</div>
                         <q-btn flat round outline color="red-9" icon="manage_accounts" size="sm" @click="abrirEditarMatricula(props.row)" />
                       </div>
                    </q-card-section>
                 </q-card>
              </div>
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
            :grid="$q.screen.lt.md"
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
                <q-btn flat round color="red-9" icon="visibility" size="sm" @click="verFactura(props.row)">
                  <q-tooltip>Ver Detalles</q-tooltip>
                </q-btn>
                <q-btn flat round color="emerald" icon="payments" size="sm" @click="abrirAbono(props.row)" :disable="props.row.estado === 'pagada'">
                  <q-tooltip>Registrar Abono</q-tooltip>
                </q-btn>

                <q-btn flat round color="grey-5" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)">
                  <q-tooltip>Exportar DIAN (PDF)</q-tooltip>
                </q-btn>
              </q-td>
            </template>

            <!-- Modo Grid Facturas -->
            <template v-slot:item="props">
              <div class="col-12 q-pa-xs grid-style-transition">
                 <q-card class="premium-glass-card shadow-24 q-mb-sm p-0" :style="props.row.estado === 'pagada' ? 'border-left: 2px solid #10b981' : (props.row.estado === 'vencida' ? 'border-left: 2px solid #f43f5e' : 'border-left: 2px solid #f97316')">
                    <q-card-section>
                       <div class="row items-center justify-between">
                         <span class="text-white font-mono text-weight-bolder">FOLIO N° {{ props.row.numero_factura }}</span>
                         <q-badge :color="props.row.estado === 'pagada' ? 'emerald' : (props.row.estado === 'vencida' ? 'red-9' : 'orange-9')" :label="props.row.estado.toUpperCase()" />
                       </div>
                       <div class="text-grey-3 font-head q-mt-sm" style="font-size:16px">{{ props.row.matricula?.estudiante?.persona?.nombres }} {{ props.row.matricula?.estudiante?.persona?.apellidos || 'Titular Principal' }}</div>
                       <div class="text-caption text-grey-5 font-mono">{{ props.row.concepto }}</div>
                       <q-separator dark class="opacity-5 q-my-sm" />
                       <div class="row justify-between items-center">
                         <div class="column">
                            <span class="font-mono text-weight-bold text-white">TTL: {{ formatMoney(props.row.total) }}</span>
                            <span class="font-mono text-weight-bold text-red-9">D/A: {{ formatMoney(props.row.total - (props.row.total_pagado || 0)) }}</span>
                         </div>
                         <div class="q-gutter-x-xs">
                             <q-btn flat round outline color="red-9" icon="visibility" size="sm" @click="verFactura(props.row)" />
                             <q-btn flat round outline color="emerald" icon="payments" size="sm" @click="abrirAbono(props.row)" v-if="props.row.estado !== 'pagada'" />
                             <q-btn flat round outline color="grey-3" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)" />
                         </div>

                       </div>
                    </q-card-section>
                 </q-card>
              </div>
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
            <q-table :rows="cartera" :columns="columnsFacturas" row-key="id" class="rac-table" flat :grid="$q.screen.lt.md">
              <template v-slot:body-cell-estado="props">
                <q-td :props="props">
                  <q-badge color="red-9" label="VENCIDA" />
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
                  <q-btn flat round color="red-9" icon="payments" size="sm" @click="abrirAbono(props.row)">
                    <q-tooltip>Registrar Abono</q-tooltip>
                  </q-btn>
                  <q-btn flat round color="grey-5" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)">
                    <q-tooltip>Exportar DIAN (PDF)</q-tooltip>
                  </q-btn>
                </q-td>
              </template>
              <template v-slot:item="props">
                <div class="col-12 q-pa-xs grid-style-transition">
                 <q-card class="premium-glass-card shadow-24 q-mb-sm p-0" style="border-left: 2px solid #f43f5e">
                    <q-card-section>
                       <div class="row items-center justify-between">
                         <span class="text-white font-mono text-weight-bolder">MOROSO FOLIO N° {{ props.row.numero_factura }}</span>
                         <q-badge color="red-9" label="VENCIDA" />
                       </div>
                       <div class="text-grey-3 font-head q-mt-sm" style="font-size:16px">{{ props.row.matricula?.estudiante?.persona?.nombres }} {{ props.row.matricula?.estudiante?.persona?.apellidos || 'Titular Principal' }}</div>
                       <q-separator dark class="opacity-5 q-my-sm" />
                       <div class="row justify-between items-center">
                         <div class="column">
                            <span class="font-mono text-weight-bold text-red-9">ADEUDA: {{ formatMoney(props.row.total - (props.row.total_pagado || 0)) }}</span>
                         </div>
                         <div class="q-gutter-x-xs">
                             <q-btn flat round outline color="emerald" icon="payments" size="sm" @click="abrirAbono(props.row)" />
                             <q-btn flat round outline color="grey-3" icon="picture_as_pdf" size="sm" @click="generarPDF(props.row)" />
                         </div>
                       </div>
                    </q-card-section>
                 </q-card>
                </div>
              </template>
            </q-table>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

    <!-- ══ DIÁLOGO: VISOR DE FACTURA ELECTRÓNICA ══ -->
    <q-dialog v-model="dialogDetalleFactura" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width: min(700px, 95vw);">
         <div class="rac-dialog-header q-pa-xl">
            <div class="row items-center justify-between">
               <div class="row items-center">
                  <q-icon name="visibility" color="red-9" size="32px" class="q-mr-md glow-primary" />
                  <div class="text-h5 text-white font-head text-weight-bolder uppercase line-height-1">Manifiesto Fiscal DIAN</div>
               </div>
               <q-btn flat round dense icon="close" v-close-popup color="grey-7" class="bg-black-20 hover-red" />
            </div>
         </div>
         
         <div class="q-pa-xl">
            <div class="q-mb-xl q-pa-xl bg-black-20 rounded-16 border-red-low shadow-inner">
               <div class="row q-col-gutter-lg">
                  <div class="col-12 col-md-6 border-right-border">
                     <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:9px">FOLIO AERONÁUTICO</div>
                     <div class="text-h5 text-white font-mono text-weight-bolder">{{ facturaSeleccionada?.numero_factura }}</div>
                  </div>
                  <div class="col-12 col-md-6 text-right-md">
                     <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:9px">ESTADO CONTABLE</div>
                     <q-badge :color="facturaSeleccionada?.estado === 'pagada' ? 'emerald' : (facturaSeleccionada?.estado === 'vencida' ? 'red-9' : 'orange-9')" :label="facturaSeleccionada?.estado?.toUpperCase()" class="font-mono q-px-md text-weight-bold" />

                  </div>
               </div>
            </div>

            <div class="row q-col-gutter-xl q-mb-xl">
               <div class="col-12">
                  <div class="text-caption text-grey-6 font-mono uppercase q-mb-sm" style="font-size:9px">CONCEPTO DE OPERACIÓN</div>
                  <div class="text-h6 text-grey-3 font-head">{{ facturaSeleccionada?.concepto || 'Entrenamiento de Vuelo / PIA' }}</div>
               </div>
            </div>

            <div class="row q-col-gutter-xl q-mb-xl border-bottom-border pb-md">
               <div class="col-6">
                  <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:9px">VALOR BRUTO (COP)</div>
                  <div class="text-h5 text-white font-mono text-weight-bolder">{{ formatMoney(facturaSeleccionada?.total) }}</div>
               </div>
               <div class="col-6 text-right">
                  <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:9px">RECAUDO CERTIFICADO</div>
                  <div class="text-h5 text-emerald font-mono text-weight-bolder">{{ formatMoney(facturaSeleccionada?.total_pagado || 0) }}</div>
               </div>
            </div>

            <div class="row q-gutter-md q-mt-xl justify-center">
               <q-btn color="red-9" icon="picture_as_pdf" label="Descargar PDF" class="premium-btn q-px-xl" @click="generarPDF(facturaSeleccionada)" />
               <q-btn outline color="emerald" icon="point_of_sale" label="Registrar Recaudo" class="premium-btn q-px-xl" 
                  v-if="facturaSeleccionada?.estado !== 'pagada'"
                  @click="abrirAbono(facturaSeleccionada)" />
            </div>
         </div>
      </q-card>
    </q-dialog>


    <!-- ══ DIALOGS PREMIUM (Red Theme) ══ -->
    <q-dialog v-model="dialogMatricula" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card shadow-24 pb-md" :class="$q.screen.lt.md ? 'q-pa-sm' : ''" style="width: 500px; max-width: 90vw;">
        <q-card-section class="text-white" :class="$q.screen.lt.md ? 'q-pa-md' : 'q-pa-lg'">
          <div class="text-h6 font-head q-mb-lg">Registrar Matrícula de Estudiante</div>
          <q-form @submit="guardarMatricula" class="row q-col-gutter-md">
            <div class="col-12"><q-select v-model="formMat.persona_id" :options="estudiantes" option-value="id" :option-label="opt => opt && opt.persona ? `${opt.persona.nombres} ${opt.persona.apellidos}` : ''" label="Seleccionar Estudiante *" outlined emit-value map-options behavior="menu" popup-content-class="bg-secondary text-white rac-popup" /></div>
            <div class="col-12"><q-select v-model="formMat.programa_id" :options="programas" option-value="id" :option-label="opt => opt && opt.codigo ? `${opt.codigo} - ${opt.nombre}` : ''" label="Programa Académico (PIA) *" outlined emit-value map-options behavior="menu" popup-content-class="bg-secondary text-white rac-popup" /></div>
            <div class="col-6"><q-input v-model="formMat.fecha_matricula" type="date" label="Fecha de Registro" outlined stack-label /></div>
            <div class="col-6"><q-select v-model="formMat.forma_pago" :options="['Contado', 'Cuotas', 'Crédito Estudiantil']" label="Modalidad de Pago *" outlined behavior="menu" popup-content-class="bg-secondary text-white rac-popup" /></div>
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
      <q-card class="premium-glass-card shadow-24 pb-md" :class="$q.screen.lt.md ? 'q-pa-sm' : ''" style="width: 500px; max-width: 90vw;">
        <q-card-section class="text-white" :class="$q.screen.lt.md ? 'q-pa-md' : 'q-pa-lg'">
          <div class="text-h6 font-head q-mb-lg">Generar Nueva Factura de Vuelo / Curso</div>
          <q-form @submit="guardarFactura" class="row q-col-gutter-md">
            <div class="col-12"><q-select v-model="formFact.matricula_id" :options="matriculas" option-value="id" :option-label="opt => opt && opt.estudiante?.persona ? `${opt.estudiante.persona.nombres} ${opt.estudiante.persona.apellidos} (Mat. #${opt.id})` : ''" label="Matrícula Asociada *" outlined emit-value map-options behavior="menu" popup-content-class="bg-secondary text-white rac-popup" /></div>
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

    <q-dialog v-model="dialogEditMatricula" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card shadow-24 pb-md" :class="$q.screen.lt.md ? 'q-pa-sm' : ''" style="width: 500px; max-width: 90vw;">
        <q-card-section class="text-white" :class="$q.screen.lt.md ? 'q-pa-md' : 'q-pa-lg'">
          <div class="text-h6 font-head q-mb-lg">Editar Matrícula</div>
          <q-form @submit="guardarMatriculaEdit" class="row q-col-gutter-md">
            <div class="col-6"><q-select v-model="formMatEdit.estado" :options="[{label: 'Activa', value: 'activa'}, {label: 'Suspendida', value: 'suspendida'}, {label: 'Terminada', value: 'terminada'}]" option-value="value" option-label="label" label="Estado *" outlined emit-value map-options behavior="menu" popup-content-class="bg-secondary text-white rac-popup" /></div>
            <div class="col-6"><q-input v-model.number="formMatEdit.descuento" type="number" label="Descuento %" outlined /></div>
            <div class="col-12"><q-input v-model="formMatEdit.observaciones" label="Observaciones" outlined type="textarea" rows="3" /></div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Actualizar Matrícula" color="red-9" class="premium-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogAbono" persistent backdrop-filter="blur(10px)">
      <q-card class="premium-glass-card border-red-low shadow-24 pb-md" :class="$q.screen.lt.md ? 'q-pa-md' : 'q-pa-lg'" style="width: 450px; max-width: 90vw;">
        <q-card-section class="text-white">
          <div class="text-h6 font-head q-mb-md">Registrar Ingreso de Caja</div>
          <div class="text-caption text-grey-5 q-mb-lg font-mono">
            Factura: <span class="text-white">{{ abonoTemp?.numero_factura }}</span> · 
            Pendiente: <span class="text-red-9 text-weight-bold">{{ formatMoney(abonoTemp?.total - (abonoTemp?.total_pagado || 0)) }}</span>
          </div>
          <q-form @submit="guardarAbono" class="row q-col-gutter-md">
            <div class="col-12">
              <q-input v-model.number="formAbono.valor" type="number" label="Valor Recibido *" outlined color="red-9" prefix="$" />
            </div>
            <div class="col-12">
              <q-select v-model="formAbono.metodo" :options="['Transferencia', 'Efectivo', 'Tarjeta', 'Cheque']" label="Método *" outlined color="red-9" behavior="menu" popup-content-class="bg-secondary text-white rac-popup" />
            </div>
            <div class="col-12">
              <q-input v-model="formAbono.referencia" label="Referencia / N° Operación" outlined color="red-9" dense />
            </div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cerrar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Guardar Recibo" color="red-9" class="premium-btn text-weight-bolder" />
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
import { useQuasar, exportFile } from 'quasar'

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
const dialogDetalleFactura = ref(false)
const facturaSeleccionada = ref(null)


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

const verFactura = (row) => {
    facturaSeleccionada.value = row
    dialogDetalleFactura.value = true
}


const guardarAbono = async () => {
    try {
        await api.post(`/facturas/${abonoTemp.value.id}/pagos`, formAbono.value)
        $q.notify({ color: 'emerald', message: 'Recibo de caja generado.', textColor: 'dark', icon: 'verified' })
        dialogAbono.value = false
        cargarTodo()
    } catch { $q.notify({ type: 'negative', message: 'Error en proceso' }) }
}

const abrirEditarMatricula = (row) => {
    formMatEdit.value = { ...row, estado: row.estado || 'activa' }
    dialogEditMatricula.value = true
}

const guardarMatriculaEdit = async () => {
    try {
        await api.patch(`/matriculas/${formMatEdit.value.id}`, formMatEdit.value)
        $q.notify({ color: 'positive', message: 'Matrícula actualizada', icon: 'edit' })
        dialogEditMatricula.value = false
        cargarTodo()
    } catch {
        $q.notify({ color: 'negative', message: 'Error al actualizar', icon: 'error' })
    }
}

const generarPDF = async (row) => {
    $q.loading.show({ message: 'Conectando con plataforma DIAN...' })
    try {
        const response = await api.get(`/facturas/${row.id}/pdf`, { responseType: 'arraybuffer' })
        const fileName = row.numero_factura ? `Factura_${row.numero_factura}.pdf` : `Documento_DIAN_${row.id}.pdf`
        
        const success = exportFile(fileName, response.data, 'application/pdf')
        if (!success) {
            throw new Error('Navegador bloqueó la descarga')
        }
        
        $q.notify({ color: 'emerald', message: 'Documento exportado correctamente', icon: 'verified', textColor: 'dark' })
    } catch (error) {
        $q.notify({ color: 'info', message: 'Exportación visual simulada (Error de servidor origin).', icon: 'info' })
    } finally {
        $q.loading.hide()
    }
}

const cargarCartera = async () => {
    loadingFacturas.value = true
    try {
        const { data } = await api.get('/facturas/cartera/vencida')
        cartera.value = data.data || []
        $q.notify({ color: 'positive', message: 'Cartera sincronizada', icon: 'sync' })
    } catch {
        $q.notify({ color: 'negative', message: 'Error al sincronizar', icon: 'error' })
    } finally {
        loadingFacturas.value = false
    }
}

onMounted(cargarTodo)

</script>

<style lang="scss" scoped>

.opacity-20 { opacity: 0.2; }
.border-emerald { border-left: 5px solid #10b981 !important; }
.bg-transparent { background: transparent !important; }
</style>
