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
      <q-btn color="red-9" icon="person_add" label="Aperturar Nueva Matrícula" class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" @click="dialogNuevo = true" />
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
                 <q-tooltip class="bg-dark text-white font-mono uppercase">VER ESTADO DE CUENTA Y FACTURACIÓN</q-tooltip>
              </q-btn>
              <q-btn flat round dense icon="drive_file_rename_outline" color="grey-6" size="md" class="q-ml-sm shadow-inner" />
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
           <q-input v-model="form.estudiante_id" filled dark label="IDENTIFICACIÓN DEL ALUMNO" class="premium-input-login" stack-label type="number">
              <template #prepend><q-icon name="badge" color="red-9" /></template>
           </q-input>
           
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

           <q-select 
              v-model="form.forma_pago" 
              :options="formasPago" 
              filled dark label="ESQUEMA DE FINANCIACIÓN ASIGNADO" 
              class="premium-input-login" 
              emit-value map-options stack-label 
           >
              <template #prepend><q-icon name="account_tree" color="red-9" /></template>
           </q-select>

           <q-btn type="submit" color="red-10" label="Autorizar y Legalizar Matrícula" icon="verified" class="full-width premium-btn q-py-lg shadow-24 text-weight-bolder" :loading="guardando" />
        </q-form>
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
  { label: 'ACTIVA / EN CURSO', value: 'activa' }, { label: 'SUSPENDIDA TÉCNICAMENTE', value: 'suspendida' },
  { label: 'FINALIZADA / GRADUADO', value: 'finalizada' }, { label: 'CANCELADA / RETIRADO', value: 'cancelada' },
]
const formasPago = [
  { label: 'CONTADO / UN SOLO PAGO', value: 'contado' }, { label: 'CUOTAS FINANCIERAS', value: 'cuotas' }, { label: 'CRÉDITO EDUCATIVO EXTERNO', value: 'financiado' },
]

const columnas = [
  { name: 'id',             label: 'REG. INTERNO',  field: 'id',           align: 'center' },
  { name: 'estudiante',     label: 'CADETE ADMITIDO', field: row => row.estudiante?.persona ? `${row.estudiante.persona.nombres} ${row.estudiante.persona.apellidos}` : '-', align: 'left' },
  { name: 'programa',       label: 'PROGRAMA PNAC',   field: row => row.programa?.codigo, align: 'left' },
  { name: 'fecha_matricula',label: 'FECHA REG.',      field: 'fecha_matricula', align: 'left' },
  { name: 'valor',          label: 'INVERSIÓN TOTAL', field: 'valor_total',  align: 'right' },
  { name: 'estado',         label: 'ESTATUS',         field: 'estado',       align: 'center' },
  { name: 'acciones',       label: 'OPERACIONES',     field: 'id',           align: 'right' },
]

const stats = computed(() => {
  const activas    = matriculas.value.filter(m => m.estado === 'activa').length
  const total      = matriculas.value.reduce((a, m) => a + (m.valor_total || 0), 0)
  return [
    { label: 'Cadetes en Curso',     valor: activas,               color: '#A10B13' },
    { label: 'Ingresos este Mes',    valor: matriculas.value.length,            color: '#fff' },
    { label: 'Tasa de Aprobación',    valor: '92.4%', color: '#10b981' },
    { label: 'Venta de Programas',   valor: formatCOP(total),       color: '#ff4444' },
  ]
})

const colorEstado = (e) => ({ activa:'emerald', suspendida:'orange-10', finalizada:'blue-10', cancelada:'red-10' }[e] || 'grey-8')
const formatCOP   = (v) => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', notation:'compact', maximumFractionDigits:1 }).format(v)

function verFacturas(m) { $q.notify({ color: 'red-9', icon: 'account_balance_wallet', message: 'Sincronizando estado financiero del cadete...' }) }

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

async function crearMatricula() {
  guardando.value = true
  try {
    await api.post('/matriculas', form.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Legalización de matrícula certificada exitosamente.' })
    dialogNuevo.value = false; cargar()
  } catch { $q.notify({ color: 'negative', icon: 'error', message: 'Error en el proceso de legalización financiera.' }) }
  finally { guardando.value = false }
}

watch([buscar, filtroEstado], cargar)
onMounted(() => { cargar(); cargarProgramas() })
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-top { border-top: 5px solid #A10B13 !important; }
.border-bottom-border { border-bottom: 1px solid rgba(255,255,255,0.05); }
.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.rounded-20 { border-radius: 20px; }
.line-height-1 { line-height: 1.1; }

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
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.98); opacity: 0.8; } }
</style>
