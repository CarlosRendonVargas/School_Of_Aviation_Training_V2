<template>
  <q-page padding>
    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12 col-sm-6 col-md-2" v-for="k in kpis" :key="k.label">
        <q-card flat bordered class="text-center q-pa-sm">
          <div class="text-h5 text-weight-bold" :class="k.color">{{ k.value }}</div>
          <div class="text-caption text-grey-6">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Toolbar -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section class="row q-col-gutter-sm items-end">
        <div class="col-12 col-sm-4">
          <q-input v-model="filtros.buscar" label="Buscar radicado / asunto" dense outlined clearable
            @update:model-value="cargar" debounce="400">
            <template #prepend><q-icon name="search" /></template>
          </q-input>
        </div>
        <div class="col-6 col-sm-2">
          <q-select v-model="filtros.tipo" :options="tipoOpts" label="Tipo" dense outlined clearable
            emit-value map-options @update:model-value="cargar" />
        </div>
        <div class="col-6 col-sm-2">
          <q-select v-model="filtros.categoria" :options="categoriaOpts" label="Categoría" dense outlined clearable
            emit-value map-options @update:model-value="cargar" />
        </div>
        <div class="col-6 col-sm-2">
          <q-select v-model="filtros.estado" :options="estadoOpts" label="Estado" dense outlined clearable
            emit-value map-options @update:model-value="cargar" />
        </div>
        <div class="col-6 col-sm-2 text-right">
          <q-btn icon="add" label="Nueva" color="primary" @click="abrirNuevo" unelevated />
        </div>
      </q-card-section>
    </q-card>

    <!-- Tabla -->
    <q-card flat bordered>
      <q-table
        :rows="rows"
        :columns="columnas"
        :loading="loading"
        row-key="id"
        flat
        :pagination="{ rowsPerPage: 15 }"
        @row-click="(_, row) => verDetalle(row)"
      >
        <template #body-cell-tipo="{ row }">
          <q-td>
            <q-badge :color="row.tipo === 'entrada' ? 'blue' : 'orange'" :label="row.tipo" class="text-uppercase" />
          </q-td>
        </template>
        <template #body-cell-estado="{ row }">
          <q-td>
            <q-badge :color="estadoColor(row.estado)" :label="row.estado.replace('_', ' ')" />
          </q-td>
        </template>
        <template #body-cell-vencimiento="{ row }">
          <q-td>
            <span v-if="row.fecha_vencimiento_respuesta" :class="vencimientoClass(row)">
              {{ fmtFecha(row.fecha_vencimiento_respuesta) }}
            </span>
            <span v-else class="text-grey-5">—</span>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Dialog Detalle / Editar -->
    <q-dialog v-model="dlgDetalle" maximized>
      <q-card>
        <q-card-section class="row items-center bg-primary text-white">
          <q-icon name="mail" size="sm" class="q-mr-sm" />
          <span class="text-h6">{{ form.id ? 'Correspondencia UAEAC' : 'Nueva Correspondencia' }}</span>
          <q-space />
          <q-btn flat round icon="close" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-4">
              <q-input v-model="form.numero_radicado" label="N° Radicado" outlined dense />
            </div>
            <div class="col-6 col-sm-2">
              <q-select v-model="form.tipo" :options="tipoOpts" label="Tipo *" outlined dense emit-value map-options />
            </div>
            <div class="col-6 col-sm-3">
              <q-select v-model="form.categoria" :options="categoriaOpts" label="Categoría *" outlined dense emit-value map-options />
            </div>
            <div class="col-6 col-sm-3">
              <q-select v-model="form.estado" :options="estadoOpts" label="Estado" outlined dense emit-value map-options />
            </div>
            <div class="col-12">
              <q-input v-model="form.asunto" label="Asunto *" outlined dense />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_documento" type="date" label="Fecha Documento *" outlined dense />
            </div>
            <div class="col-6 col-sm-3">
              <q-input v-model="form.fecha_vencimiento_respuesta" type="date" label="Vencimiento Respuesta" outlined dense clearable />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="form.archivo_url" label="URL Archivo (opcional)" outlined dense />
            </div>
            <div class="col-12">
              <q-input v-model="form.contenido" label="Contenido / Descripción" type="textarea" rows="4" outlined dense />
            </div>
            <div class="col-12">
              <q-input v-model="form.notas_internas" label="Notas Internas" type="textarea" rows="3" outlined dense />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Cancelar" v-close-popup />
          <q-btn unelevated color="primary" :label="form.id ? 'Actualizar' : 'Guardar'" :loading="saving" @click="guardar" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()
const rows = ref([])
const loading = ref(false)
const saving = ref(false)
const dlgDetalle = ref(false)
const stats = ref({})

const filtros = ref({ buscar: '', tipo: null, categoria: null, estado: null })

const tipoOpts = [
  { label: 'Entrada', value: 'entrada' },
  { label: 'Salida', value: 'salida' },
]
const categoriaOpts = [
  { label: 'Certificación', value: 'certificacion' },
  { label: 'Inspección', value: 'inspeccion' },
  { label: 'Enmienda', value: 'enmienda' },
  { label: 'Solicitud', value: 'solicitud' },
  { label: 'Respuesta', value: 'respuesta' },
  { label: 'Circular', value: 'circular' },
  { label: 'Resolución', value: 'resolucion' },
  { label: 'Otro', value: 'otro' },
]
const estadoOpts = [
  { label: 'Recibido', value: 'recibido' },
  { label: 'En Revisión', value: 'en_revision' },
  { label: 'Respondido', value: 'respondido' },
  { label: 'Archivado', value: 'archivado' },
]

const columnas = [
  { name: 'numero_radicado', label: 'Radicado', field: 'numero_radicado', align: 'left', sortable: true },
  { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'center', sortable: true },
  { name: 'categoria', label: 'Categoría', field: 'categoria', align: 'left', sortable: true },
  { name: 'asunto', label: 'Asunto', field: 'asunto', align: 'left', style: 'max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap' },
  { name: 'fecha_documento', label: 'Fecha Doc.', field: r => fmtFecha(r.fecha_documento), align: 'center', sortable: true },
  { name: 'vencimiento', label: 'Vence', field: 'fecha_vencimiento_respuesta', align: 'center' },
  { name: 'estado', label: 'Estado', field: 'estado', align: 'center' },
]

const kpis = computed(() => [
  { label: 'Total', value: stats.value.total ?? 0, color: 'text-primary' },
  { label: 'Entradas', value: stats.value.entrada ?? 0, color: 'text-blue' },
  { label: 'Salidas', value: stats.value.salida ?? 0, color: 'text-orange' },
  { label: 'Pendientes', value: stats.value.pendientes ?? 0, color: 'text-warning' },
  { label: 'Vencidos', value: stats.value.vencidos ?? 0, color: 'text-negative' },
])

const emptyForm = () => ({
  id: null, numero_radicado: '', tipo: 'entrada', categoria: 'solicitud',
  asunto: '', contenido: '', fecha_documento: new Date().toISOString().slice(0, 10),
  fecha_vencimiento_respuesta: null, estado: 'recibido', archivo_url: '', notas_internas: '',
})
const form = ref(emptyForm())

async function cargar() {
  loading.value = true
  try {
    const params = {}
    if (filtros.value.buscar) params.buscar = filtros.value.buscar
    if (filtros.value.tipo) params.tipo = filtros.value.tipo
    if (filtros.value.categoria) params.categoria = filtros.value.categoria
    if (filtros.value.estado) params.estado = filtros.value.estado
    const res = await api.get('/correspondencia', { params })
    rows.value = res.data.data ?? res.data
  } finally {
    loading.value = false
  }
}

async function cargarStats() {
  const res = await api.get('/correspondencia/estadisticas')
  stats.value = res.data
}

function abrirNuevo() {
  form.value = emptyForm()
  dlgDetalle.value = true
}

function verDetalle(row) {
  form.value = {
    id: row.id,
    numero_radicado: row.numero_radicado ?? '',
    tipo: row.tipo,
    categoria: row.categoria,
    asunto: row.asunto,
    contenido: row.contenido ?? '',
    fecha_documento: row.fecha_documento,
    fecha_vencimiento_respuesta: row.fecha_vencimiento_respuesta ?? null,
    estado: row.estado,
    archivo_url: row.archivo_url ?? '',
    notas_internas: row.notas_internas ?? '',
  }
  dlgDetalle.value = true
}

async function guardar() {
  saving.value = true
  try {
    if (form.value.id) {
      await api.put(`/correspondencia/${form.value.id}`, form.value)
    } else {
      await api.post('/correspondencia', form.value)
    }
    $q.notify({ type: 'positive', message: 'Guardado correctamente' })
    dlgDetalle.value = false
    await Promise.all([cargar(), cargarStats()])
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error al guardar' })
  } finally {
    saving.value = false
  }
}

function estadoColor(e) {
  return { recibido: 'blue', en_revision: 'orange', respondido: 'positive', archivado: 'grey' }[e] ?? 'grey'
}

function vencimientoClass(row) {
  if (!row.fecha_vencimiento_respuesta) return ''
  const hoy = new Date()
  const v = new Date(row.fecha_vencimiento_respuesta)
  const dias = Math.ceil((v - hoy) / 86400000)
  if (dias < 0) return 'text-negative text-weight-bold'
  if (dias <= 7) return 'text-warning text-weight-bold'
  return ''
}

function fmtFecha(d) {
  if (!d) return '—'
  return new Date(d + 'T00:00:00').toLocaleDateString('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

onMounted(() => {
  cargar()
  cargarStats()
})
</script>
