<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Gestión de Cadetes ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="group" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">REGISTRO DE PILOTOS EN FORMACIÓN · RAC 141.67</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Directorio de Estudiantes</h1>
        </div>
      </div>
      <q-btn v-if="puedeCrear" color="red-9" icon="person_add" label="Alta Estudiante" 
        class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" @click="abrirFormNuevo" />
    </div>

    <!-- ══ Panel de Filtros Táctico ══ -->
    <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-low shadow-24 rounded-20 welcome-hero overflow-hidden">
      <div class="row q-col-gutter-lg items-end">
        <div class="col-12 col-md-5">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">BUSCAR EN BITÁCORA CENTRAL</div>
          <q-input v-model="filtros.buscar" filled dark dense class="premium-input-login" placeholder="Nombre, CC o Expediente..." @update:model-value="cargar">
            <template #prepend><q-icon name="search" color="red-9" /></template>
          </q-input>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">PROGRAMA ACADÉMICO</div>
          <q-select v-model="filtros.programa_id" :options="programas" option-value="id" option-label="nombre" emit-value map-options clearable filled dark dense class="premium-input-login" @update:model-value="cargar" />
        </div>
        <div class="col-6 col-md-2">
          <div class="text-caption text-grey-7 font-mono q-mb-sm uppercase" style="font-size:9px">Estatus Operativo</div>
          <q-select v-model="filtros.estado" :options="opcionesEstado" emit-value map-options clearable filled dark dense class="premium-input-login" @update:model-value="cargar" />
        </div>
        <div class="col-12 col-md-2 flex justify-end">
           <q-btn-toggle v-model="vista" :options="[{icon:'list',value:'lista'},{icon:'grid_view',value:'tarjetas'}]" flat dense dark toggle-color="red-9" color="grey-7" class="premium-glass-card border-red-low" />
        </div>
      </div>
    </q-card>

    <!-- ══ KPIs de Admisión y Cumplimiento ══ -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-6 col-md-3" v-for="s in stats" :key="s.label">
        <q-card class="premium-glass-card q-pa-lg text-center border-red-low shadow-inner welcome-hero overflow-hidden">
          <div class="hero-glow"></div>
          <div class="text-h4 text-weight-bolder font-mono q-mb-xs relative-position" :style="`color:${s.color}`">{{ s.valor }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest relative-position" style="font-size:10px">{{ s.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- ══ Monitor de Estudiantes (Lista) ══ -->
    <q-table v-if="vista==='lista'"
      :rows="estudiantes" :columns="columnas" :loading="cargando"
      flat dark class="rac-table shadow-24"
      row-key="id" v-model:pagination="paginacion" @request="onRequest">

      <template #body-cell-nombre="props">
        <q-td :props="props">
          <div class="row items-center q-gutter-md">
            <q-avatar size="44px" class="shadow-24 border-red-low" style="background: rgba(255,255,255,0.03)">
              <div class="font-mono text-white text-weight-bolder">{{ iniciales(props.row) }}</div>
            </q-avatar>
            <div>
              <div class="text-subtitle1 text-white font-head text-weight-bold">{{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}</div>
              <div class="font-mono text-grey-6" style="font-size:10px">EXP: {{ props.row.num_expediente }}</div>
            </div>
          </div>
        </q-td>
      </template>

      <template #body-cell-programa="props">
        <q-td :props="props" class="text-center">
          <q-badge outline color="red-9" :label="props.row.programa?.codigo || '-'" class="font-mono text-weight-bold" />
        </q-td>
      </template>

      <template #body-cell-estado="props">
        <q-td :props="props" class="text-center">
          <q-badge :color="colorEstado(props.value)" :label="props.value.toUpperCase()" class="font-mono text-weight-bold q-px-md" />
        </q-td>
      </template>

      <template #body-cell-medico="props">
        <q-td :props="props" class="text-center">
          <q-icon :name="props.row.tiene_medico ? 'verified_user' : 'report_problem'" :color="props.row.tiene_medico ? 'emerald' : 'red-9'" size="20px">
            <q-tooltip class="bg-dark text-white">CERT. MÉDICO: {{ props.row.tiene_medico ? 'VIGENTE' : 'VENCIDO/FALTA' }}</q-tooltip>
          </q-icon>
        </q-td>
      </template>

      <template #body-cell-acciones="props">
        <q-td :props="props" class="text-right">
          <div class="row q-gutter-x-sm justify-end">
             <q-btn flat round dense icon="folder_shared" color="red-9" size="sm" @click="$router.push(`/estudiantes/${props.row.id}`)" />
             <q-btn flat round dense icon="insights" color="grey-6" size="sm" @click="verHoras(props.row)" />
          </div>
        </q-td>
      </template>
    </q-table>

    <!-- Vista Tarjetas de Cristal (Grid) -->
    <div v-else class="row q-col-gutter-lg">
      <div class="col-12 col-sm-6 col-md-4 col-lg-3" v-for="e in estudiantes" :key="e.id">
        <q-card class="premium-glass-card border-red-low hover-row cursor-pointer shadow-24" @click="$router.push(`/estudiantes/${e.id}`)">
          <q-card-section class="q-pa-lg">
            <div class="row items-center q-gutter-md q-mb-lg">
              <q-avatar size="52px" class="shadow-24" style="background: rgba(161,11,19,0.05); border: 1px solid rgba(161,11,19,0.2)">
                 <span class="font-mono text-white text-weight-bolder">{{ iniciales(e) }}</span>
              </q-avatar>
              <div class="col">
                 <div class="text-white text-weight-bolder font-head line-height-1" style="font-size:15px">{{ e.persona?.nombres }} {{ e.persona?.apellidos }}</div>
                 <div class="font-mono text-grey-6 q-mt-xs" style="font-size:10px">{{ e.num_expediente }}</div>
              </div>
            </div>
            <q-separator dark class="q-my-md opacity-10" />
            <div class="row justify-between items-center">
              <div>
                 <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">PROGRAMA</div>
                 <q-badge outline color="red-9" :label="e.programa?.codigo" class="font-mono" />
              </div>
              <div class="text-right">
                 <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">ESTADO</div>
                 <q-badge :color="colorEstado(e.estado)" :label="e.estado" class="font-mono" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-dialog v-model="dialogHoras" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(600px, 95vw);">
        <div class="q-pa-xl border-bottom-border pb-md">
           <div class="row items-center justify-between q-col-gutter-md">
              <div class="row items-center col">
                 <q-icon name="analytics" color="red-9" size="32px" class="q-mr-md glow-primary" />
                 <div class="text-h5 text-white font-head text-weight-bolder uppercase line-height-1">Progreso de Formación</div>
              </div>
              <q-btn flat round dense icon="close" v-close-popup color="grey-6" class="bg-black-20 hover-red" />
           </div>
        </div>
        <div class="q-pa-xl">

        <div class="text-subtitle1 text-grey-4 font-mono q-mb-xl">CADETE: {{ estudianteSeleccionado?.persona?.nombres }} {{ estudianteSeleccionado?.persona?.apellidos }}</div>

        <div v-for="cat in categoriasProgreso" :key="cat.key" class="q-mb-lg last-no-margin">
           <div class="row justify-between items-end q-mb-sm">
              <span class="text-grey-3 text-weight-bold">{{ cat.label }}</span>
              <div>
                 <span class="font-mono text-red-9 text-weight-bolder">{{ Number(cat.acumulado || 0).toFixed(1) }}H</span>
                 <span class="font-mono text-grey-6" style="font-size:11px"> / {{ Number(cat.requerido || 0).toFixed(1) }}H</span>
              </div>
           </div>
           <q-linear-progress :value="Math.min((cat.porcentaje || 0)/100, 1)" :color="cat.cumplido ? 'emerald' : 'red-9'" size="6px" rounded track-color="white-1" />
        </div>
        
        <q-banner v-if="progresoHoras?.listo_para_examen" class="q-mt-xl bg-emerald text-white rounded-12 shadow-24">
           <template #avatar><q-icon name="verified" color="white" /></template>
           <span class="font-head text-weight-bold">LISTO PARA EXAMEN DE COMPETENCIA UAEAC</span>
        </q-banner>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogNuevo" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(900px, 95vw);">
        <div class="q-pa-xl border-bottom-border pb-md">
           <div class="row items-center justify-between q-col-gutter-md">
              <div class="row items-center col">
                 <q-icon name="how_to_reg" color="red-9" size="32px" class="q-mr-md glow-primary" />
                 <div class="text-h5 text-white font-head text-weight-bolder uppercase line-height-1">Inscripción RAC 141</div>
              </div>
              <q-btn flat round dense icon="close" v-close-popup color="grey-6" class="bg-black-20 hover-red" />
           </div>
        </div>
        <div class="q-pa-xl">

        <q-form @submit.prevent="guardarNuevo" class="q-gutter-y-lg">
          <div class="row q-col-gutter-lg">
            <div class="col-12 col-md-6"><q-input v-model="formNuevo.nombres" label="NOMBRES" filled dark class="premium-input-login" stack-label /></div>
            <div class="col-12 col-md-6"><q-input v-model="formNuevo.apellidos" label="APELLIDOS" filled dark class="premium-input-login" stack-label /></div>
            <div class="col-12 col-md-6"><q-select v-model="formNuevo.tipo_documento" :options="['CC', 'CE', 'PAS']" label="TIPO DOC" filled dark class="premium-input-login" stack-label /></div>
            <div class="col-12 col-md-6"><q-input v-model="formNuevo.num_documento" label="IDENTIFICACIÓN" filled dark class="premium-input-login" stack-label /></div>
            <div class="col-12 col-md-6"><q-select v-model="formNuevo.programa_id" :options="programas" option-value="id" option-label="nombre" label="PROGRAMA" filled dark class="premium-input-login" emit-value map-options stack-label /></div>
            <div class="col-12 col-md-6"><q-input v-model="formNuevo.fecha_ingreso" type="date" label="ADMISIÓN" filled dark class="premium-input-login" stack-label /></div>
          </div>
          <q-btn type="submit" color="red-9" label="Completar Alta de Estudiante" icon="person_add" class="full-width premium-btn q-py-md shadow-24 q-mt-md" :loading="guardandoNuevo" />
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
const formNuevo            = ref({ nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '', fecha_nacimiento: '', programa_id: null, fecha_ingreso: '', observaciones: '' })

const filtros = ref({ buscar: '', programa_id: null, estado: null })
const paginacion = ref({ page: 1, rowsPerPage: 15, rowsNumber: 0 })

const opcionesEstado = [ { label: 'Activo', value: 'activo' }, { label: 'Suspendido', value: 'suspendido' }, { label: 'Graduado', value: 'graduado' }, { label: 'Retirado', value: 'retirado' } ]
const puedeCrear = computed(() => ['admin', 'dir_ops'].includes(authStore.rol))

const columnas = [
  { name: 'nombre', label: 'NOMBRE DEL CANDIDATO', field: 'id', align: 'left' },
  { name: 'programa', label: 'CURSO', field: 'programa_id', align: 'center' },
  { name: 'ingreso', label: 'ADMISIÓN', field: 'fecha_ingreso', align: 'left' },
  { name: 'estado', label: 'STATUS', field: 'estado', align: 'center' },
  { name: 'medico', label: 'RAC 67', field: 'tiene_medico', align: 'center' },
  { name: 'acciones', label: 'ACCIONES', field: 'id', align: 'right' },
]

const stats = computed(() => [
  { label: 'Matrículas Totales', valor: paginacion.value.rowsNumber, color: 'white' },
  { label: 'Cadetes Activos', valor: estudiantes.value.filter(e => e.estado === 'activo').length, color: '#10b981' },
  { label: 'Alertas Médicas', valor: estudiantes.value.filter(e => !e.tiene_medico).length, color: '#A10B13' },
  { label: 'Graduados Élite', valor: estudiantes.value.filter(e => e.estado === 'graduado').length, color: '#60a5fa' },
])

const categoriasProgreso = computed(() => {
  const cats = progresoHoras.value?.categorias || {}
  return Object.entries(cats).filter(([, v]) => (v.requerido || 0) > 0).map(([key, v]) => ({ key, ...v }))
})

const iniciales = (e) => ((e.persona?.nombres?.[0] || '') + (e.persona?.apellidos?.[0] || '')).toUpperCase()
const colorEstado = (estado) => ({ activo: 'emerald', suspendido: 'orange-9', graduado: 'blue-9', retirado: 'grey-8' }[estado] || 'grey-8')

async function cargar(pag = 1) {
  cargando.value = true
  try {
    const { data } = await api.get('/estudiantes', { params: { page: pag, per_page: paginacion.value.rowsPerPage, ...filtros.value } })
    estudiantes.value = data.data.data || data.data || []
    paginacion.value.rowsNumber = data.data.total || 0
  } finally { cargando.value = false }
}

async function cargarProgramas() { const { data } = await api.get('/programas?activos=1'); programas.value = data.data || [] }

async function verHoras(estudiante) {
  estudianteSeleccionado.value = estudiante; dialogHoras.value = true; cargandoHoras.value = true; progresoHoras.value = null
  try { const { data } = await api.get(`/estudiantes/${estudiante.id}/progreso-horas`); progresoHoras.value = data.data } finally { cargandoHoras.value = false }
}

function abrirFormNuevo() { dialogNuevo.value = true; formNuevo.value = { nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '', fecha_nacimiento: '', programa_id: null, fecha_ingreso: new Date().toISOString().split('T')[0], observaciones: '' } }

async function guardarNuevo() {
  guardandoNuevo.value = true
  try {
    await api.post('/estudiantes', formNuevo.value)
    $q.notify({ color: 'emerald', message: 'Estudiante admitido correctamente' }); dialogNuevo.value = false; cargar()
  } catch { $q.notify({ color: 'negative', message: 'Error en la admisión del estudiante.' }) } 
  finally { guardandoNuevo.value = false }
}

function onRequest(props) { paginacion.value.page = props.pagination.page; paginacion.value.rowsPerPage = props.pagination.rowsPerPage; cargar(props.pagination.page) }

onMounted(() => { cargar(); cargarProgramas() })
</script>

<style lang="scss" scoped>
.hover-red { transition: color 0.2s; &:hover { color: #A10B13 !important; } }
.bg-black-20 { background: rgba(0,0,0,0.2); }
.pb-md { padding-bottom: 16px; }
.border-bottom-border { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
.rounded-20 { border-radius: 20px !important; }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
</style>
