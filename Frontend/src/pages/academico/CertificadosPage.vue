<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado ══ -->
    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">RAC 141.77 · Registros 5 años</div>
        <h1 class="rac-page-title">Certificados <span class="text-red-9">y Constancias</span></h1>
      </div>
      <q-btn v-if="auth.esAdmin || auth.esDirOps"
        unelevated color="red-9" icon="workspace_premium" label="Emitir Certificado"
        class="premium-btn" @click="abrirForm()" />
    </div>

    <!-- ══ KPIs ══ -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="k in kpis" :key="k.label" class="col-6 col-md-3">
        <q-card class="premium-glass-card q-pa-md text-center">
          <q-icon :name="k.icon" :color="k.color" size="28px" />
          <div class="text-h5 text-white text-weight-bold q-mt-sm">{{ k.valor }}</div>
          <div class="font-mono text-grey-6" style="font-size:10px">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- ══ Filtros ══ -->
    <q-card class="premium-glass-card q-mb-md">
      <q-card-section class="row q-col-gutter-md">
        <div class="col-12 col-sm-4">
          <q-select v-model="filtroTipo" :options="tiposOpciones" emit-value map-options
            dark outlined dense label="Tipo de certificado" clearable />
        </div>
        <div class="col-12 col-sm-4">
          <q-input v-model="busqueda" dark outlined dense placeholder="Buscar estudiante..."
            clearable><template #prepend><q-icon name="search" color="grey-6" /></template></q-input>
        </div>
      </q-card-section>
    </q-card>

    <!-- ══ Tabla ══ -->
    <q-card class="premium-glass-card">
      <div v-if="cargando" class="flex flex-center q-pa-xl">
        <q-spinner-cube color="red-9" size="40px" />
      </div>

      <q-table v-else
        :rows="certsFiltrados" :columns="columnas"
        row-key="id" dark flat
        :rows-per-page-options="[15, 30, 50]"
        no-data-label="Sin certificados emitidos"
        class="bg-transparent"
      >
        <template #body-cell-numero_certificado="{ row }">
          <q-td>
            <span class="font-mono text-amber-5" style="font-size:12px">{{ row.numero_certificado }}</span>
          </q-td>
        </template>

        <template #body-cell-tipo="{ row }">
          <q-td>
            <q-badge :color="colorTipo(row.tipo)" class="font-mono" style="font-size:9px">
              {{ labelTipo(row.tipo) }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-estudiante="{ row }">
          <q-td>
            <div class="text-white text-weight-medium">
              {{ row.estudiante?.persona?.nombres }} {{ row.estudiante?.persona?.apellidos }}
            </div>
            <div class="font-mono text-grey-6" style="font-size:10px">
              {{ row.estudiante?.num_expediente }}
            </div>
          </q-td>
        </template>

        <template #body-cell-anulado="{ row }">
          <q-td>
            <q-badge :color="row.anulado ? 'red-9' : 'green-7'" class="font-mono" style="font-size:9px">
              {{ row.anulado ? 'ANULADO' : 'VÁLIDO' }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-acciones="{ row }">
          <q-td class="row no-wrap q-gutter-xs">
            <q-btn flat round dense icon="picture_as_pdf" color="red-5" size="sm"
              @click="descargarPdf(row)" :loading="descargando === row.id">
              <q-tooltip>Descargar PDF</q-tooltip>
            </q-btn>
            <q-btn v-if="!row.anulado && (auth.esAdmin || auth.esDirOps)"
              flat round dense icon="block" color="orange-5" size="sm"
              @click="anular(row)">
              <q-tooltip>Anular certificado</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- ══ Diálogo emitir certificado ══ -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(560px,95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase tracking-widest q-mb-xs" style="font-size:10px">
            RAC 141.77 · Registros académicos
          </div>
          <div class="text-h5 text-white font-head text-weight-bolder">Emitir Certificado</div>
        </div>

        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Tipo de Certificado</div>
            <q-select v-model="form.tipo" :options="tiposOpciones" emit-value map-options
              dark outlined dense />
          </div>
          <div>
            <div class="campo-label required">Estudiante</div>
            <q-select v-model="form.estudiante_id" :options="estudiantesOps" emit-value map-options
              dark outlined dense use-input input-debounce="0"
              @filter="filtrarEstudiantes" />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label">Etapa</div>
              <q-select v-model="form.etapa_id" :options="etapasOps" emit-value map-options
                dark outlined dense clearable />
            </div>
            <div class="col-6">
              <div class="campo-label required">Fecha de Emisión</div>
              <q-input v-model="form.fecha_emision" type="date" dark outlined dense />
            </div>
          </div>
          <div>
            <div class="campo-label">Descripción / Detalle</div>
            <q-input v-model="form.descripcion" dark outlined dense type="textarea" rows="3"
              placeholder="Descripción del logro o motivo del certificado..." />
          </div>
        </div>

        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" label="Emitir Certificado" class="premium-btn"
            :loading="guardando" @click="guardar" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q   = useQuasar()
const auth = useAuthStore()

const cargando   = ref(false)
const guardando  = ref(false)
const descargando = ref(null)
const certs      = ref([])
const busqueda   = ref('')
const filtroTipo = ref(null)
const dialogForm = ref(false)

const estudiantes   = ref([])
const estudiantesOps = ref([])
const etapas        = ref([])

const formVacio = () => ({ tipo: '', estudiante_id: null, etapa_id: null, fecha_emision: new Date().toISOString().slice(0,10), descripcion: '' })
const form = ref(formVacio())

const tiposOpciones = [
  { value: 'finalizacion_etapa',    label: 'Finalización de Etapa' },
  { value: 'finalizacion_programa', label: 'Graduación / Finalización de Programa' },
  { value: 'constancia_horas',      label: 'Constancia de Horas de Vuelo' },
  { value: 'constancia_matricula',  label: 'Constancia de Matrícula' },
  { value: 'aprobacion_examen',     label: 'Aprobación de Examen UAEAC' },
  { value: 'endorsement',           label: 'Endorsement de Instrucción' },
]

const colorTipo = t => ({ finalizacion_programa: 'amber-7', finalizacion_etapa: 'teal-6', endorsement: 'blue-6', aprobacion_examen: 'green-7', constancia_horas: 'purple-6', constancia_matricula: 'grey-6' }[t] || 'grey-6')
const labelTipo = t => tiposOpciones.find(o => o.value === t)?.label?.split(' ')[0] || t

const kpis = computed(() => {
  const total  = certs.value.length
  const validos = certs.value.filter(c => !c.anulado).length
  const anulados = certs.value.filter(c => c.anulado).length
  const hoy    = certs.value.filter(c => c.fecha_emision === new Date().toISOString().slice(0,10)).length
  return [
    { label: 'TOTAL EMITIDOS', valor: total,   icon: 'workspace_premium', color: 'amber-5' },
    { label: 'VÁLIDOS',        valor: validos,  icon: 'verified',          color: 'green-5' },
    { label: 'ANULADOS',       valor: anulados, icon: 'block',             color: 'red-5'   },
    { label: 'HOY',            valor: hoy,      icon: 'today',             color: 'blue-5'  },
  ]
})

const certsFiltrados = computed(() => {
  let r = certs.value
  if (filtroTipo.value) r = r.filter(c => c.tipo === filtroTipo.value)
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    r = r.filter(c => {
      const n = `${c.estudiante?.persona?.nombres} ${c.estudiante?.persona?.apellidos}`.toLowerCase()
      return n.includes(q) || c.numero_certificado.toLowerCase().includes(q)
    })
  }
  return r
})

const columnas = [
  { name: 'numero_certificado', label: 'N.º Cert.', field: 'numero_certificado', align: 'left' },
  { name: 'tipo',               label: 'Tipo',       field: 'tipo',              align: 'left' },
  { name: 'estudiante',         label: 'Estudiante', field: row => `${row.estudiante?.persona?.nombres} ${row.estudiante?.persona?.apellidos}`, align: 'left' },
  { name: 'fecha_emision',      label: 'Emisión',    field: 'fecha_emision',     align: 'left' },
  { name: 'anulado',            label: 'Estado',     field: 'anulado',           align: 'center' },
  { name: 'acciones',           label: '',           field: 'id',                align: 'center' },
]

const etapasOps = computed(() =>
  etapas.value.map(e => ({ value: e.id, label: e.nombre }))
)

function filtrarEstudiantes(val, update) {
  update(() => {
    const q = val.toLowerCase()
    estudiantesOps.value = estudiantes.value
      .filter(e => `${e.persona?.nombres} ${e.persona?.apellidos}`.toLowerCase().includes(q))
      .map(e => ({ value: e.id, label: `${e.persona?.nombres} ${e.persona?.apellidos} — ${e.num_expediente}` }))
  })
}

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/certificados')
    certs.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar certificados.' })
  } finally {
    cargando.value = false
  }
}

async function abrirForm() {
  form.value = formVacio()
  if (!estudiantes.value.length) {
    const [estRes, etaRes] = await Promise.all([
      api.get('/estudiantes'),
      api.get('/programas'),
    ])
    estudiantes.value = estRes.data.data
    estudiantesOps.value = estudiantes.value.map(e => ({
      value: e.id,
      label: `${e.persona?.nombres} ${e.persona?.apellidos} — ${e.num_expediente}`,
    }))
    etapas.value = etaRes.data.data?.flatMap(p => p.etapas || []) || []
  }
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.tipo || !form.value.estudiante_id || !form.value.fecha_emision) {
    $q.notify({ type: 'warning', message: 'Tipo, estudiante y fecha son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    await api.post('/certificados', form.value)
    $q.notify({ type: 'positive', message: 'Certificado emitido correctamente.' })
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al emitir el certificado.' })
  } finally {
    guardando.value = false
  }
}

async function descargarPdf(cert) {
  descargando.value = cert.id
  try {
    const res = await api.get(`/certificados/${cert.id}/pdf`, { responseType: 'blob' })
    const url = URL.createObjectURL(res.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `Certificado_${cert.numero_certificado}.pdf`
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al generar el PDF.' })
  } finally {
    descargando.value = null
  }
}

function anular(cert) {
  $q.dialog({
    title: 'Anular Certificado',
    message: 'Indique el motivo de anulación:',
    prompt: { model: '', type: 'text', isValid: v => v.length >= 5 },
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { label: 'Anular', color: 'red-9', unelevated: true },
    dark: true,
    class: 'premium-glass-card',
  }).onOk(async motivo => {
    try {
      await api.post(`/certificados/${cert.id}/anular`, { motivo_anulacion: motivo })
      $q.notify({ type: 'positive', message: 'Certificado anulado.' })
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al anular el certificado.' })
    }
  })
}

onMounted(cargar)
</script>
