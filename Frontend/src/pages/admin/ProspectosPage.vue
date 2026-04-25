<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">Gestión de Leads · CRM</div>
        <h1 class="rac-page-title">Prospectos <span class="text-red-9">y CRM</span></h1>
      </div>
      <q-btn unelevated color="red-9" icon="person_add" label="Nuevo Prospecto"
        class="premium-btn" @click="abrirForm()" />
    </div>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="k in kpis" :key="k.label" class="col-6 col-md-3">
        <q-card class="premium-glass-card q-pa-md text-center">
          <div class="text-h4 text-weight-bold" :style="`color: ${k.color}`">{{ k.valor }}</div>
          <div class="font-mono text-grey-6 q-mt-xs" style="font-size:10px">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Pipeline Kanban -->
    <div class="row q-col-gutter-md q-mb-md">
      <div v-for="col in kanban" :key="col.estado" class="col-12 col-sm-6 col-md-2">
        <div class="q-pa-sm" style="background: rgba(255,255,255,0.03); border-radius: 10px; border: 1px solid rgba(255,255,255,0.06)">
          <div class="row items-center q-mb-sm">
            <q-badge :color="col.color" class="font-mono q-mr-xs" style="font-size:9px">{{ col.label }}</q-badge>
            <q-badge color="grey-8" class="font-mono" style="font-size:9px">{{ col.items.length }}</q-badge>
          </div>
          <div v-for="p in col.items" :key="p.id"
            class="q-mb-sm q-pa-sm cursor-pointer" @click="abrirForm(p)"
            style="background:rgba(255,255,255,0.04); border-radius:8px; border:1px solid rgba(255,255,255,0.06); transition: all 0.2s"
            @mouseenter="e => e.currentTarget.style.borderColor='rgba(161,11,19,0.4)'"
            @mouseleave="e => e.currentTarget.style.borderColor='rgba(255,255,255,0.06)'"
          >
            <div class="text-white text-weight-medium" style="font-size:12px">{{ p.nombres }} {{ p.apellidos }}</div>
            <div class="font-mono text-grey-6" style="font-size:10px">{{ p.programa_interes }}</div>
            <div v-if="p.proxima_accion" class="font-mono text-amber-6" style="font-size:9px; margin-top:3px">
              ► {{ p.proxima_accion }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de prospectos -->
    <q-card class="premium-glass-card">
      <q-card-section class="row items-center q-gutter-md">
        <q-input v-model="busqueda" dark outlined dense placeholder="Buscar prospecto..." clearable style="max-width:280px">
          <template #prepend><q-icon name="search" color="grey-6" /></template>
        </q-input>
        <q-select v-model="filtroEstado" :options="estadosOpts" emit-value map-options
          dark outlined dense label="Estado" clearable style="min-width:160px" />
        <q-select v-model="filtroPrograma" :options="programasOpts" emit-value map-options
          dark outlined dense label="Programa" clearable style="min-width:140px" />
      </q-card-section>

      <q-separator dark />

      <div v-if="cargando" class="flex flex-center q-pa-xl"><q-spinner-cube color="red-9" size="40px" /></div>

      <q-table v-else :rows="prospectosFiltrados" :columns="columnas"
        row-key="id" dark flat :rows-per-page-options="[15, 30]"
        no-data-label="Sin prospectos" class="bg-transparent">

        <template #body-cell-nombre="{ row }">
          <q-td>
            <div class="text-white text-weight-medium">{{ row.nombres }} {{ row.apellidos }}</div>
            <div class="font-mono text-grey-6" style="font-size:10px">{{ row.email }}</div>
          </q-td>
        </template>

        <template #body-cell-estado="{ row }">
          <q-td>
            <q-badge :color="colorEstado(row.estado)" class="font-mono" style="font-size:9px">
              {{ row.estado.toUpperCase() }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-proxima_accion="{ row }">
          <q-td>
            <span v-if="row.proxima_accion"
              :class="row.proxima_accion < hoy ? 'text-red-5' : 'text-amber-5'"
              class="font-mono" style="font-size:11px">
              {{ row.proxima_accion }}
            </span>
            <span v-else class="text-grey-7">—</span>
          </q-td>
        </template>

        <template #body-cell-acciones="{ row }">
          <q-td class="row no-wrap q-gutter-xs">
            <q-btn flat round dense icon="edit" color="grey-5" size="sm" @click="abrirForm(row)" />
            <q-btn flat round dense icon="delete" color="red-9" size="sm" @click="eliminar(row)" />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo form -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(600px,95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase tracking-widest q-mb-xs" style="font-size:10px">CRM · Gestión de Leads</div>
          <div class="text-h5 text-white font-head text-weight-bolder">{{ editando ? 'Editar' : 'Nuevo' }} Prospecto</div>
        </div>

        <div class="rac-dialog-body q-gutter-y-md">
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Nombres</div>
              <q-input v-model="form.nombres" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label required">Apellidos</div>
              <q-input v-model="form.apellidos" dark outlined dense />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label">Email</div>
              <q-input v-model="form.email" type="email" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label">Teléfono</div>
              <q-input v-model="form.telefono" dark outlined dense />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label">Ciudad</div>
              <q-input v-model="form.ciudad" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label">Fuente</div>
              <q-input v-model="form.fuente" dark outlined dense placeholder="Redes, referido, web..." />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Programa de Interés</div>
              <q-select v-model="form.programa_interes" :options="programasOpts" emit-value map-options dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label required">Estado</div>
              <q-select v-model="form.estado" :options="estadosOpts" emit-value map-options dark outlined dense />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Primer Contacto</div>
              <q-input v-model="form.fecha_primer_contacto" type="date" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label">Próxima Acción</div>
              <q-input v-model="form.proxima_accion" type="date" dark outlined dense />
            </div>
          </div>
          <div>
            <div class="campo-label">Notas</div>
            <q-input v-model="form.notas" dark outlined dense type="textarea" rows="3" />
          </div>
        </div>

        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" :label="editando ? 'Guardar cambios' : 'Registrar Prospecto'"
            class="premium-btn" :loading="guardando" @click="guardar" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q      = useQuasar()
const hoy     = new Date().toISOString().slice(0, 10)
const cargando = ref(false)
const guardando = ref(false)
const prospectos = ref([])
const estadisticas = ref({})
const busqueda = ref('')
const filtroEstado = ref(null)
const filtroPrograma = ref(null)
const dialogForm = ref(false)
const editando = ref(null)

const estadosOpts = [
  { value: 'nuevo',       label: 'Nuevo' },
  { value: 'contactado',  label: 'Contactado' },
  { value: 'interesado',  label: 'Interesado' },
  { value: 'matriculado', label: 'Matriculado' },
  { value: 'descartado',  label: 'Descartado' },
]
const programasOpts = [
  { value: 'PPL',         label: 'PPL' },
  { value: 'CPL',         label: 'CPL' },
  { value: 'ATPL',        label: 'ATPL' },
  { value: 'HABILITACION',label: 'Habilitación' },
  { value: 'otro',        label: 'Otro' },
]

const colorEstado = e => ({ nuevo: 'blue-6', contactado: 'amber-7', interesado: 'teal-6', matriculado: 'green-7', descartado: 'grey-7' }[e] || 'grey-7')

const formVacio = () => ({
  nombres: '', apellidos: '', email: '', telefono: '', ciudad: '',
  programa_interes: 'PPL', estado: 'nuevo', notas: '', fuente: '',
  fecha_primer_contacto: hoy, proxima_accion: '',
})
const form = ref(formVacio())

const kpis = computed(() => [
  { label: 'TOTAL PROSPECTOS', valor: prospectos.value.length, color: '#3b82f6' },
  { label: 'INTERESADOS',      valor: prospectos.value.filter(p => p.estado === 'interesado').length, color: '#2dd4bf' },
  { label: 'MATRICULADOS',     valor: prospectos.value.filter(p => p.estado === 'matriculado').length, color: '#4ade80' },
  { label: 'PRÓX. 7 DÍAS',     valor: prospectos.value.filter(p => p.proxima_accion && p.proxima_accion <= new Date(Date.now()+7*864e5).toISOString().slice(0,10) && p.proxima_accion >= hoy).length, color: '#f5a623' },
])

const kanban = computed(() =>
  estadosOpts.map(e => ({
    ...e,
    color: colorEstado(e.value),
    items: prospectos.value.filter(p => p.estado === e.value),
  }))
)

const prospectosFiltrados = computed(() => {
  let r = prospectos.value
  if (filtroEstado.value)   r = r.filter(p => p.estado === filtroEstado.value)
  if (filtroPrograma.value) r = r.filter(p => p.programa_interes === filtroPrograma.value)
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    r = r.filter(p => `${p.nombres} ${p.apellidos} ${p.email}`.toLowerCase().includes(q))
  }
  return r
})

const columnas = [
  { name: 'nombre',         label: 'Prospecto',         field: row => `${row.nombres} ${row.apellidos}`, align: 'left' },
  { name: 'programa_interes', label: 'Programa',        field: 'programa_interes', align: 'left' },
  { name: 'telefono',       label: 'Teléfono',          field: 'telefono',         align: 'left' },
  { name: 'fuente',         label: 'Fuente',            field: 'fuente',           align: 'left' },
  { name: 'estado',         label: 'Estado',            field: 'estado',           align: 'left' },
  { name: 'proxima_accion', label: 'Próxima Acción',    field: 'proxima_accion',   align: 'left' },
  { name: 'acciones',       label: '',                  field: 'id',               align: 'center' },
]

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/prospectos')
    prospectos.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar prospectos.' })
  } finally {
    cargando.value = false
  }
}

function abrirForm(p = null) {
  editando.value = p
  form.value = p
    ? { nombres: p.nombres, apellidos: p.apellidos, email: p.email || '', telefono: p.telefono || '', ciudad: p.ciudad || '', programa_interes: p.programa_interes, estado: p.estado, notas: p.notas || '', fuente: p.fuente || '', fecha_primer_contacto: p.fecha_primer_contacto, proxima_accion: p.proxima_accion || '' }
    : formVacio()
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.nombres || !form.value.apellidos) {
    $q.notify({ type: 'warning', message: 'Nombres y apellidos son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    if (editando.value) {
      await api.put(`/prospectos/${editando.value.id}`, form.value)
      $q.notify({ type: 'positive', message: 'Prospecto actualizado.' })
    } else {
      await api.post('/prospectos', form.value)
      $q.notify({ type: 'positive', message: 'Prospecto registrado.' })
    }
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar.' })
  } finally {
    guardando.value = false
  }
}

function eliminar(p) {
  $q.dialog({
    title: 'Eliminar Prospecto',
    message: `¿Eliminar a ${p.nombres} ${p.apellidos}?`,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { label: 'Eliminar', color: 'red-9', unelevated: true },
    dark: true, class: 'premium-glass-card',
  }).onOk(async () => {
    try {
      await api.delete(`/prospectos/${p.id}`)
      $q.notify({ type: 'positive', message: 'Prospecto eliminado.' })
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar.' })
    }
  })
}

onMounted(cargar)
</script>
