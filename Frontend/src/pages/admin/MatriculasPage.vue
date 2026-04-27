<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Gestión Académica Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="how_to_reg" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">ADMISIONES, REGISTROS Y CONTROL · RAC 141</div>
          <h1 class="rac-page-title">Gestión de Matrículas</h1>
        </div>
      </div>
      <q-btn color="red-9" icon="person_add" label="Aperturar Nueva Matrícula" class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" @click="resetForm(); dialogNuevo = true" />
    </div>

    <!-- ══ KPIs Financieros Tácticos Inmersivos ══ -->
    <div class="row q-col-gutter-xl q-mb-xl">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <q-card class="premium-glass-card q-pa-xl text-center border-red-low shadow-24 welcome-hero overflow-hidden">
          <div class="hero-glow"></div>
          <div class="relative-position">
            <div class="text-h4 text-weight-bolder font-mono q-mb-xs line-height-1" :style="`color:${s.color}`">{{ s.valor }}</div>
            <q-separator dark class="q-my-sm opacity-5" />
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ s.label }}</div>
          </div>
        </q-card>
      </div>
    </div>

    <!-- ══ Consola de Búsqueda de Cristal ══ -->
    <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-low shadow-24">
      <div class="row q-col-gutter-xl items-end">
        <div class="col-12 col-md-7">
          <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Búsqueda Global de Expedientes</div>
          <q-input v-model="buscar" filled dark class="premium-input-login" placeholder="Nombre completo, identificación o número de registro..." debounce="400">
            <template #prepend><q-icon name="manage_search" color="red-9" /></template>
          </q-input>
        </div>
        <div class="col-12 col-md-5">
          <div class="text-caption text-grey-6 font-mono q-mb-sm uppercase tracking-widest" style="font-size:9px">Estado de Vigencia Contractual</div>
          <q-select v-model="filtroEstado" :options="opcionesEstado" emit-value map-options clearable filled dark class="premium-input-login" placeholder="Filtrar por estatus..." />
        </div>
      </div>
    </q-card>

    <!-- ══ Manifiesto de Expedientes Académicos ══ -->
    <q-card class="premium-glass-card shadow-24 border-red-low rounded-20 overflow-hidden">
        <q-table 
           :rows="matriculas" 
           :columns="columnas" 
           :loading="cargando" 
           flat dark 
           class="rac-table"
           row-key="id"
        >
          <template #body-cell-estado="props">
            <q-td :props="props" class="text-center">
               <q-badge outline :color="colorEstado(props.value)" :label="props.value?.toUpperCase()" class="font-mono text-weight-bolder q-px-xl q-py-xs shadow-24" />
            </q-td>
          </template>
          <template #body-cell-id="props">
            <q-td :props="props">
               <div class="font-mono text-weight-bold text-grey-5 tracking-widest" style="font-size:11px">MAT-{{ props.value?.toString().padStart(5, '0') }}</div>
            </q-td>
          </template>
          <template #body-cell-valor="props">
            <q-td :props="props">
              <div class="font-mono text-weight-bolder text-white text-h6">{{ formatCOP(props.row.valor_total) }}</div>
            </q-td>
          </template>
          <template #body-cell-acciones="props">
            <q-td :props="props" class="text-right">
              <q-btn flat round dense icon="account_balance_wallet" color="red-9" size="md" @click="verFacturas(props.row)" class="shadow-inner">
                <q-tooltip class="bg-dark text-white font-mono uppercase">Estado de Cuenta</q-tooltip>
              </q-btn>
              <q-btn flat round dense icon="drive_file_rename_outline" color="grey-6" size="md" class="q-ml-sm shadow-inner" @click="abrirEditar(props.row)">
                <q-tooltip class="bg-dark text-white font-mono uppercase">Editar Matrícula</q-tooltip>
              </q-btn>
            </q-td>
          </template>
        </q-table>
    </q-card>

    <!-- ══ DIÁLOGO: REGISTRO DE ADMISIÓN DE ÉLITE ══ -->
    <q-dialog v-model="dialogNuevo" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(550px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
           <div class="row items-center">
              <q-icon name="verified_user" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Aperturar Matrícula RAC</div>
           </div>
           <q-btn flat round dense icon="close" v-close-popup color="grey-7" class="shadow-inner" />
        </div>

        <q-form @submit.prevent="crearMatricula" class="q-gutter-y-lg">
           <q-select
              v-model="form.persona_id"
              :options="personasFiltradas"
              filled dark label="BUSCAR ALUMNO (NOMBRE / DOCUMENTO)"
              class="premium-input-login"
              emit-value map-options stack-label
              use-input hide-selected fill-input
              input-debounce="0"
              :rules="[v => !!v || 'Seleccione un alumno']"
              @filter="filtrarPersonas"
           >
              <template #prepend><q-icon name="badge" color="red-9" /></template>
              <template #option="scope">
                <q-item v-bind="scope.itemProps">
                  <q-item-section avatar><q-icon name="person" color="red-9" size="18px" /></q-item-section>
                  <q-item-section>
                    <q-item-label class="text-white">{{ scope.opt.label }}</q-item-label>
                    <q-item-label caption class="text-grey-6 font-mono" style="font-size:9px">{{ scope.opt.rol?.toUpperCase() }}</q-item-label>
                  </q-item-section>
                </q-item>
              </template>
              <template #no-option><q-item><q-item-section class="text-grey-6 font-mono text-caption">Sin resultados</q-item-section></q-item></template>
           </q-select>
           
           <q-select 
              v-model="form.programa_id" 
              :options="programas" 
              filled dark label="SELECCIONAR PROGRAMA ACADÉMICO" 
              class="premium-input-login" 
              emit-value map-options stack-label 
           >
              <template #prepend><q-icon name="school" color="red-9" /></template>
           </q-select>

           <q-input v-model="form.fecha_matricula" type="date" filled dark label="FECHA DE LEGALIZACIÓN" class="premium-input-login" stack-label>
              <template #prepend><q-icon name="event_available" color="red-9" /></template>
           </q-input>
           
           <q-input v-model.number="form.valor_total" filled dark label="VALOR TOTAL DEL PROGRAMA (COP)" class="premium-input-login" stack-label type="number" prefix="$">
              <template #prepend><q-icon name="payments" color="red-9" /></template>
           </q-input>

           <q-input v-model.number="form.descuento" filled dark label="DESCUENTO APLICADO (COP)" class="premium-input-login" stack-label type="number" prefix="$">
              <template #prepend><q-icon name="local_offer" color="emerald" /></template>
           </q-input>

           <q-select 
              v-model="form.forma_pago" 
              :options="formasPago" 
              filled dark label="ESQUEMA DE FINANCIACIÓN ASIGNADO" 
              class="premium-input-login" 
              emit-value map-options stack-label 
           >
              <template #prepend><q-icon name="account_tree" color="red-9" /></template>
           </q-select>

           <q-input v-if="form.forma_pago === 'cuotas'" v-model.number="form.num_cuotas" filled dark label="NÚMERO DE CUOTAS" class="premium-input-login" stack-label type="number" />

           <q-input v-model="form.contrato_url" filled dark label="URL CONTRATO MATRÍCULA (PDF)" class="premium-input-login" stack-label>
              <template #prepend><q-icon name="link" color="red-9" /></template>
           </q-input>

           <q-input v-model="form.observaciones" filled dark type="textarea" label="OBSERVACIONES" class="premium-input-login" stack-label rows="2" />

           <q-btn type="submit" color="red-10" label="Autorizar y Legalizar Matrícula" icon="verified" class="full-width premium-btn q-py-lg shadow-24 text-weight-bolder" :loading="guardando" />
        </q-form>
      </q-card>
    </q-dialog>

    <!-- ══ DIÁLOGO: EDITAR MATRÍCULA ══ -->
    <q-dialog v-model="dialogEditar" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(520px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
          <div class="row items-center">
            <q-icon name="drive_file_rename_outline" color="red-9" size="28px" class="q-mr-md" />
            <div>
              <div class="text-h6 text-white font-head text-weight-bolder uppercase">Editar Matrícula</div>
              <div class="font-mono text-grey-6" style="font-size:10px">MAT-{{ String(editForm.id).padStart(5,'0') }}</div>
            </div>
          </div>
          <q-btn flat round dense icon="close" v-close-popup color="grey-7" />
        </div>

        <q-form @submit.prevent="guardarEdicion" class="q-gutter-y-md">
          <q-select v-model="editForm.estado" :options="opcionesEstado" emit-value map-options
            filled dark label="ESTADO DE LA MATRÍCULA" class="premium-input-login" stack-label>
            <template #prepend><q-icon name="flag" color="red-9" /></template>
          </q-select>

          <q-select v-model="editForm.forma_pago" :options="formasPago" emit-value map-options
            filled dark label="ESQUEMA DE FINANCIACIÓN" class="premium-input-login" stack-label>
            <template #prepend><q-icon name="account_tree" color="red-9" /></template>
          </q-select>

          <q-input v-if="editForm.forma_pago === 'cuotas'" v-model.number="editForm.num_cuotas"
            type="number" filled dark label="NÚMERO DE CUOTAS" class="premium-input-login" stack-label>
            <template #prepend><q-icon name="format_list_numbered" color="red-9" /></template>
          </q-input>

          <q-input v-model.number="editForm.descuento" type="number" filled dark
            label="DESCUENTO (COP)" class="premium-input-login" stack-label prefix="$">
            <template #prepend><q-icon name="local_offer" color="emerald" /></template>
          </q-input>

          <q-input v-model="editForm.observaciones" type="textarea" filled dark
            label="OBSERVACIONES" class="premium-input-login" stack-label rows="3" />

          <q-btn type="submit" color="red-10" label="Guardar Cambios" icon="save"
            class="full-width premium-btn q-py-md text-weight-bolder" :loading="guardandoEdit" />
        </q-form>
      </q-card>
    </q-dialog>

    <!-- ══ DIÁLOGO: ESTADO DE CUENTA ══ -->
    <q-dialog v-model="dialogFacturas" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(620px, 95vw);">
        <div class="row items-center justify-between q-pa-xl border-bottom-border">
          <div class="row items-center">
            <q-icon name="account_balance_wallet" color="red-9" size="28px" class="q-mr-md" />
            <div>
              <div class="text-h6 text-white font-head text-weight-bolder uppercase">Estado de Cuenta</div>
              <div class="font-mono text-grey-6" style="font-size:10px">
                {{ matriculaActiva?.estudiante?.persona?.nombres }} {{ matriculaActiva?.estudiante?.persona?.apellidos }}
              </div>
            </div>
          </div>
          <q-btn flat round dense icon="close" v-close-popup color="grey-7" />
        </div>

        <div class="q-pa-xl">
          <!-- Resumen financiero -->
          <div class="row q-col-gutter-md q-mb-xl">
            <div class="col-6 col-sm-3" v-for="kpi in resumenCuenta" :key="kpi.label">
              <div class="premium-glass-card q-pa-md text-center border-red-low rounded-12">
                <div class="font-mono text-weight-bolder q-mb-xs" :style="`color:${kpi.color}; font-size:18px`">{{ formatCOP(kpi.valor) }}</div>
                <div class="text-grey-7 font-mono uppercase" style="font-size:8px; letter-spacing:1px">{{ kpi.label }}</div>
              </div>
            </div>
          </div>

          <!-- Facturas -->
          <div v-if="cargandoFacturas" class="text-center q-pa-xl">
            <q-spinner-dots color="red-9" size="40px" />
          </div>
          <div v-else-if="!facturasMatricula.length" class="text-center q-pa-xl text-grey-6 font-mono" style="font-size:11px; letter-spacing:1px">
            SIN FACTURAS REGISTRADAS
          </div>
          <div v-else class="q-gutter-y-sm">
            <div v-for="f in facturasMatricula" :key="f.id"
              class="premium-glass-card q-pa-md border-red-low rounded-12">
              <div class="row items-center justify-between q-mb-sm">
                <div class="font-mono text-weight-bold text-grey-3" style="font-size:12px">FAC-{{ String(f.id).padStart(5,'0') }}</div>
                <q-badge outline :color="f.estado === 'pagada' ? 'emerald' : f.estado === 'vencida' ? 'red-9' : 'orange-9'"
                  :label="f.estado?.toUpperCase()" class="font-mono" />
              </div>
              <div class="row justify-between text-caption text-grey-6 font-mono">
                <span>Vence: {{ formatFechaCO(f.fecha_vencimiento) }}</span>
                <span class="text-white text-weight-bold">{{ formatCOP(f.monto) }}</span>
              </div>
            </div>
          </div>
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { formatFechaCO } from 'src/utils/formatters'

const $q       = useQuasar()
const matriculas  = ref([])
const programas   = ref([])
const cargando    = ref(false)
const guardando   = ref(false)
const dialogNuevo    = ref(false)
const dialogEditar   = ref(false)
const dialogFacturas = ref(false)
const buscar         = ref('')
const filtroEstado   = ref(null)
const guardandoEdit  = ref(false)
const cargandoFacturas = ref(false)
const facturasMatricula = ref([])
const matriculaActiva   = ref(null)

const editForm = ref({ id: null, estado: '', forma_pago: '', num_cuotas: 1, descuento: 0, observaciones: '' })

const resumenCuenta = computed(() => {
  if (!matriculaActiva.value) return []
  const m = matriculaActiva.value
  return [
    { label: 'Valor Total',    valor: m.valor_total || 0,      color: '#fff' },
    { label: 'Descuento',      valor: m.descuento || 0,        color: '#10b981' },
    { label: 'Valor Neto',     valor: m.valor_neto || 0,       color: '#A10B13' },
    { label: 'Saldo Pendiente',valor: m.saldo_pendiente || 0,  color: '#f97316' },
  ]
})

const personas         = ref([])
const personasFiltradas = ref([])

const form = ref({ persona_id: null, programa_id: null, fecha_matricula: '', valor_total: 0, descuento: 0, forma_pago: 'cuotas', num_cuotas: 1, contrato_url: '', observaciones: '' })

const opcionesEstado = [
  { label: 'ACTIVA / EN CURSO', value: 'activa' }, { label: 'SUSPENDIDA TÉCNICAMENTE', value: 'suspendida' },
  { label: 'FINALIZADA / GRADUADO', value: 'finalizada' }, { label: 'CANCELADA / RETIRADO', value: 'cancelada' },
]
const formasPago = [
  { label: 'CONTADO / UN SOLO PAGO', value: 'contado' }, { label: 'CUOTAS FINANCIERAS', value: 'cuotas' }, { label: 'CRÉDITO EDUCATIVO EXTERNO', value: 'financiado' },
]

const columnas = [
  { name: 'id',             label: 'REG. INTERNO',  field: 'id',           align: 'center' },
  { name: 'estudiante',     label: 'ESTUDIANTE ADMITIDO', field: row => row.estudiante?.persona ? `${row.estudiante.persona.nombres} ${row.estudiante.persona.apellidos}` : '-', align: 'left' },
  { name: 'programa',       label: 'PROGRAMA PNAC',   field: row => row.programa?.codigo, align: 'left' },
  { name: 'fecha_matricula',label: 'FECHA REG.',      field: row => formatFechaCO(row.fecha_matricula), align: 'left' },
  { name: 'valor',          label: 'INVERSIÓN TOTAL', field: 'valor_total',  align: 'right' },
  { name: 'estado',         label: 'ESTATUS',         field: 'estado',       align: 'center' },
  { name: 'acciones',       label: 'OPERACIONES',     field: 'id',           align: 'right' },
]

const stats = computed(() => {
  const activas     = matriculas.value.filter(m => m.estado === 'activa').length
  const finalizadas = matriculas.value.filter(m => m.estado === 'finalizada').length
  const canceladas  = matriculas.value.filter(m => m.estado === 'cancelada').length
  const total       = matriculas.value.reduce((a, m) => a + (m.valor_total || 0), 0)
  // Solo se calcula sobre los que ya terminaron (graduados + retirados), no sobre activos
  const concluidas     = finalizadas + canceladas
  const tasaAprobacion = concluidas > 0 ? Math.round((finalizadas / concluidas) * 100) + '%' : '—'
  return [
    { label: 'Estudiantes en Curso',   valor: activas,          color: '#A10B13' },
    { label: 'Matrículas Totales',     valor: matriculas.value.length, color: '#fff' },
    { label: 'Tasa de Graduación',     valor: tasaAprobacion,   color: '#10b981' },
    { label: 'Venta de Programas',     valor: formatCOP(total), color: '#ff4444' },
  ]
})

const colorEstado = (e) => ({ activa:'emerald', suspendida:'orange-10', finalizada:'blue-10', cancelada:'red-10' }[e] || 'grey-8')
const formatCOP   = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', notation:'compact', maximumFractionDigits:1 }).format(v)

async function verFacturas(m) {
  matriculaActiva.value = m
  facturasMatricula.value = []
  dialogFacturas.value = true
  cargandoFacturas.value = true
  try {
    const { data } = await api.get(`/matriculas/${m.id}/facturas`)
    facturasMatricula.value = data.data || []
  } finally {
    cargandoFacturas.value = false
  }
}

function abrirEditar(m) {
  editForm.value = {
    id:            m.id,
    estado:        m.estado,
    forma_pago:    m.forma_pago,
    num_cuotas:    m.num_cuotas ?? 1,
    descuento:     m.descuento ?? 0,
    observaciones: m.observaciones ?? '',
  }
  dialogEditar.value = true
}

async function guardarEdicion() {
  guardandoEdit.value = true
  try {
    await api.put(`/matriculas/${editForm.value.id}`, {
      estado:        editForm.value.estado,
      forma_pago:    editForm.value.forma_pago,
      num_cuotas:    editForm.value.forma_pago === 'cuotas' ? editForm.value.num_cuotas : null,
      descuento:     editForm.value.descuento,
      observaciones: editForm.value.observaciones,
    })
    $q.notify({ color: 'emerald', icon: 'save', message: 'Matrícula actualizada correctamente.' })
    dialogEditar.value = false
    cargar()
  } catch {
    $q.notify({ color: 'negative', icon: 'error', message: 'Error al actualizar la matrícula.' })
  } finally {
    guardandoEdit.value = false
  }
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/matriculas', { params: { buscar: buscar.value, estado: filtroEstado.value } })
    matriculas.value = data.data?.data || data.data || []
  } finally { cargando.value = false }
}

async function cargarProgramas() {
  const { data } = await api.get('/programas')
  programas.value = (data.data || []).map(p => ({ label: p.nombre, value: p.id }))
}

async function cargarPersonas() {
  try {
    const { data } = await api.get('/usuarios')
    personas.value = (data.data || [])
      .filter(u => u.persona)
      .map(u => ({
        label: `${u.persona.nombres} ${u.persona.apellidos} · ${u.persona.num_documento || u.email}`,
        value: u.persona.id,
        rol:   u.rol?.nombre || '',
      }))
    personasFiltradas.value = [...personas.value]
  } catch { /* silencioso */ }
}

function filtrarPersonas(val, update) {
  update(() => {
    if (!val.trim()) { personasFiltradas.value = personas.value; return }
    const q = val.toLowerCase()
    personasFiltradas.value = personas.value.filter(p => p.label.toLowerCase().includes(q))
  })
}

function resetForm() {
  form.value = { persona_id: null, programa_id: null, fecha_matricula: '', valor_total: 0, descuento: 0, forma_pago: 'cuotas', num_cuotas: 1, contrato_url: '', observaciones: '' }
}

async function crearMatricula() {
  guardando.value = true
  try {
    await api.post('/matriculas', form.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Legalización de matrícula certificada exitosamente.' })
    dialogNuevo.value = false
    resetForm()
    cargar()
  } catch (e) {
    const msg = e.response?.data?.message || 'Error en el proceso de legalización financiera.'
    $q.notify({ color: 'negative', icon: 'error', message: msg })
  }
  finally { guardando.value = false }
}

watch([buscar, filtroEstado], cargar)
onMounted(() => { cargar(); cargarProgramas(); cargarPersonas() })
</script>

<style lang="scss" scoped>

.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.15) 0%, transparent 50%); }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.98); opacity: 0.8; } }
</style>
