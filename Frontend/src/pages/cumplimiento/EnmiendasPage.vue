<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">RAC 141.13 · Gestión Documental UAEAC</div>
        <h1 class="rac-page-title">Enmiendas <span class="text-red-9">MOE / PIA</span></h1>
      </div>
      <q-btn v-if="auth.esAdmin || auth.esDirOps"
        unelevated color="red-9" icon="edit_document" label="Nueva Enmienda"
        class="premium-btn" @click="abrirForm()" />
    </div>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="k in kpis" :key="k.label" class="col-6 col-md-3">
        <q-card class="premium-glass-card q-pa-md text-center">
          <div class="text-h5 text-weight-bold" :style="`color:${k.color}`">{{ k.valor }}</div>
          <div class="font-mono text-grey-6 q-mt-xs" style="font-size:10px">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Filtros -->
    <q-card class="premium-glass-card q-mb-md">
      <q-card-section class="row q-gutter-md items-center">
        <q-select v-model="filtroEstado" :options="estadosOpts" emit-value map-options
          dark outlined dense label="Estado" clearable style="min-width:160px" />
        <q-input v-model="busqueda" dark outlined dense placeholder="Buscar enmienda..."
          clearable style="min-width:220px">
          <template #prepend><q-icon name="search" color="grey-6" /></template>
        </q-input>
      </q-card-section>
    </q-card>

    <!-- Tabla -->
    <q-card class="premium-glass-card">
      <div v-if="cargando" class="flex flex-center q-pa-xl">
        <q-spinner-cube color="red-9" size="40px" />
      </div>

      <q-table v-else :rows="enmiendsFiltradas" :columns="columnas" row-key="id" dark flat
        class="bg-transparent" :rows-per-page-options="[15, 30]"
        no-data-label="Sin enmiendas registradas">

        <template #body-cell-numero_enmienda="{ row }">
          <q-td>
            <span class="font-mono text-amber-5" style="font-size:12px">{{ row.numero_enmienda }}</span>
          </q-td>
        </template>

        <template #body-cell-documento="{ row }">
          <q-td>
            <div class="text-white text-weight-medium">{{ row.documento?.titulo }}</div>
            <div class="font-mono text-grey-6" style="font-size:10px">{{ row.documento?.tipo }}</div>
          </q-td>
        </template>

        <template #body-cell-estado="{ row }">
          <q-td>
            <q-badge :color="colorEstado(row.estado)" class="font-mono cursor-pointer" style="font-size:9px"
              @click="auth.esAdmin || auth.esDirOps ? cambiarEstado(row) : null">
              {{ labelEstado(row.estado) }}
            </q-badge>
          </q-td>
        </template>

        <template #body-cell-acciones="{ row }">
          <q-td class="row no-wrap q-gutter-xs">
            <q-btn flat round dense icon="visibility" color="teal-5" size="sm" @click="verDetalle(row)">
              <q-tooltip>Ver detalle</q-tooltip>
            </q-btn>
            <q-btn v-if="auth.esAdmin || auth.esDirOps" flat round dense icon="edit" color="grey-5" size="sm" @click="abrirForm(row)" />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Diálogo detalle -->
    <q-dialog v-model="dialogDetalle" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(680px,95vw); max-height:90vh; overflow-y:auto">
        <div class="rac-dialog-header">
          <div class="row items-center q-gutter-sm q-mb-xs">
            <span class="font-mono text-amber-5" style="font-size:13px">{{ enmiendaActiva?.numero_enmienda }}</span>
            <q-badge :color="colorEstado(enmiendaActiva?.estado)" class="font-mono" style="font-size:9px">{{ labelEstado(enmiendaActiva?.estado) }}</q-badge>
          </div>
          <div class="text-h6 text-white font-head text-weight-bolder">{{ enmiendaActiva?.descripcion }}</div>
          <div class="font-mono text-grey-6 q-mt-xs" style="font-size:11px">
            Doc: {{ enmiendaActiva?.documento?.titulo }} · Elaborado por: {{ nombreElaborador(enmiendaActiva) }}
          </div>
        </div>

        <div class="q-pa-lg q-gutter-y-md">
          <div>
            <div class="font-mono text-red-9 text-uppercase q-mb-sm" style="font-size:10px; letter-spacing:1px">CONTENIDO DEL CAMBIO</div>
            <div class="text-grey-3" style="font-size:13px; line-height:1.7; white-space:pre-wrap">{{ enmiendaActiva?.contenido_cambio }}</div>
          </div>

          <div v-if="enmiendaActiva?.respuesta_uaeac">
            <div class="font-mono text-green-6 text-uppercase q-mb-sm" style="font-size:10px; letter-spacing:1px">RESPUESTA UAEAC</div>
            <div class="text-grey-3 q-pa-md" style="font-size:13px; background:rgba(255,255,255,0.03); border-radius:8px; border-left:3px solid rgba(74,222,128,0.4); white-space:pre-wrap">{{ enmiendaActiva?.respuesta_uaeac }}</div>
          </div>

          <div class="row q-gutter-md">
            <div v-if="enmiendaActiva?.fecha_envio" class="col-auto">
              <div class="font-mono text-grey-6" style="font-size:9px">ENVIADA UAEAC</div>
              <div class="text-amber-5 font-mono" style="font-size:12px">{{ enmiendaActiva?.fecha_envio }}</div>
            </div>
            <div v-if="enmiendaActiva?.fecha_respuesta" class="col-auto">
              <div class="font-mono text-grey-6" style="font-size:9px">RESPUESTA RECIBIDA</div>
              <div class="text-green-5 font-mono" style="font-size:12px">{{ enmiendaActiva?.fecha_respuesta }}</div>
            </div>
          </div>
        </div>

        <!-- Cambiar estado (solo admin/dir_ops) -->
        <div v-if="auth.esAdmin || auth.esDirOps" class="q-px-lg q-pb-lg">
          <q-separator dark class="q-mb-md" />
          <div class="font-mono text-grey-5 q-mb-sm" style="font-size:10px; letter-spacing:1px">ACTUALIZAR ESTADO</div>
          <div class="row q-gutter-sm">
            <q-btn v-for="est in estadosOpts" :key="est.value"
              unelevated size="sm" :color="colorEstado(est.value)"
              :outline="enmiendaActiva?.estado !== est.value"
              :label="est.label" class="font-mono"
              @click="aplicarEstado(enmiendaActiva, est.value)" />
          </div>
          <div v-if="['aprobada', 'rechazada'].includes(enmiendaActiva?.estado)" class="q-mt-md">
            <div class="campo-label">Respuesta UAEAC</div>
            <q-input v-model="respuestaUaeac" dark outlined dense type="textarea" rows="2" />
            <div class="row justify-end q-mt-sm">
              <q-btn unelevated color="teal-7" size="sm" label="Guardar respuesta" @click="guardarRespuesta(enmiendaActiva)" />
            </div>
          </div>
        </div>
      </q-card>
    </q-dialog>

    <!-- Diálogo form nueva enmienda -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(600px,95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase q-mb-xs" style="font-size:10px">RAC 141.13 · MOE/PIA</div>
          <div class="text-h5 text-white font-head text-weight-bolder">{{ editando ? 'Editar' : 'Nueva' }} Enmienda</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Documento (MOE / PIA)</div>
            <q-select v-model="form.documento_id" :options="documentosOps" emit-value map-options dark outlined dense />
          </div>
          <div>
            <div class="campo-label required">Descripción del Cambio</div>
            <q-input v-model="form.descripcion" dark outlined dense placeholder="Resumen ejecutivo de la enmienda..." />
          </div>
          <div>
            <div class="campo-label required">Contenido del Cambio</div>
            <q-input v-model="form.contenido_cambio" dark outlined dense type="textarea" rows="5"
              placeholder="Detalle completo de los cambios propuestos al documento..." />
          </div>
          <div>
            <div class="campo-label required">Estado</div>
            <q-select v-model="form.estado" :options="estadosOpts" emit-value map-options dark outlined dense />
          </div>
          <div v-if="['enviada_uaeac', 'aprobada', 'rechazada'].includes(form.estado)">
            <div class="campo-label">Fecha de Envío a UAEAC</div>
            <q-input v-model="form.fecha_envio" type="date" dark outlined dense />
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" :label="editando ? 'Guardar cambios' : 'Crear Enmienda'"
            class="premium-btn" :loading="guardando" @click="guardar" />
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

const cargando      = ref(false)
const guardando     = ref(false)
const enmiendas     = ref([])
const documentos    = ref([])
const editando      = ref(null)
const enmiendaActiva= ref(null)
const respuestaUaeac= ref('')
const busqueda      = ref('')
const filtroEstado  = ref(null)
const dialogForm    = ref(false)
const dialogDetalle = ref(false)

const estadosOpts = [
  { value: 'borrador',      label: 'Borrador' },
  { value: 'enviada_uaeac', label: 'Enviada UAEAC' },
  { value: 'aprobada',      label: 'Aprobada' },
  { value: 'rechazada',     label: 'Rechazada' },
  { value: 'retirada',      label: 'Retirada' },
]

const colorEstado = e => ({ borrador: 'grey-7', enviada_uaeac: 'blue-6', aprobada: 'green-7', rechazada: 'red-9', retirada: 'orange-7' }[e] || 'grey-7')
const labelEstado = e => estadosOpts.find(o => o.value === e)?.label || e
const nombreElaborador = e => e ? `${e.elaborador?.persona?.nombres || ''} ${e.elaborador?.persona?.apellidos || ''}`.trim() : ''

const formVacio = () => ({ documento_id: null, descripcion: '', contenido_cambio: '', estado: 'borrador', fecha_envio: '' })
const form = ref(formVacio())

const documentosOps = computed(() =>
  documentos.value.map(d => ({ value: d.id, label: `${d.titulo} (${d.tipo})` }))
)

const kpis = computed(() => [
  { label: 'TOTAL',          valor: enmiendas.value.length,                            color: '#3b82f6' },
  { label: 'EN PROCESO',     valor: enmiendas.value.filter(e => e.estado === 'enviada_uaeac').length, color: '#f5a623' },
  { label: 'APROBADAS',      valor: enmiendas.value.filter(e => e.estado === 'aprobada').length,      color: '#4ade80' },
  { label: 'RECHAZADAS',     valor: enmiendas.value.filter(e => e.estado === 'rechazada').length,     color: '#f87171' },
])

const enmiendsFiltradas = computed(() => {
  let r = enmiendas.value
  if (filtroEstado.value) r = r.filter(e => e.estado === filtroEstado.value)
  if (busqueda.value) {
    const q = busqueda.value.toLowerCase()
    r = r.filter(e => e.numero_enmienda.toLowerCase().includes(q) || e.descripcion.toLowerCase().includes(q))
  }
  return r
})

const columnas = [
  { name: 'numero_enmienda', label: 'N.º Enmienda', field: 'numero_enmienda', align: 'left' },
  { name: 'documento',       label: 'Documento',    field: row => row.documento?.titulo, align: 'left' },
  { name: 'descripcion',     label: 'Descripción',  field: 'descripcion', align: 'left' },
  { name: 'estado',          label: 'Estado',       field: 'estado',      align: 'center' },
  { name: 'fecha_envio',     label: 'Enviada',      field: 'fecha_envio', align: 'center' },
  { name: 'acciones',        label: '',             field: 'id',          align: 'center' },
]

async function cargar() {
  cargando.value = true
  try {
    const [emRes, docRes] = await Promise.all([
      api.get('/enmiendas'),
      api.get('/cumplimiento/documentos'),
    ])
    enmiendas.value  = emRes.data.data
    documentos.value = docRes.data.data || []
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar enmiendas.' })
  } finally {
    cargando.value = false
  }
}

function verDetalle(e) {
  enmiendaActiva.value = e
  respuestaUaeac.value = e.respuesta_uaeac || ''
  dialogDetalle.value  = true
}

function cambiarEstado(e) { verDetalle(e) }

async function aplicarEstado(e, estado) {
  try {
    const payload = { estado }
    if (estado === 'enviada_uaeac') payload.fecha_envio = new Date().toISOString().slice(0, 10)
    await api.put(`/enmiendas/${e.id}`, payload)
    e.estado = estado
    enmiendaActiva.value = { ...e, estado }
    $q.notify({ type: 'positive', message: 'Estado actualizado.' })
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cambiar estado.' })
  }
}

async function guardarRespuesta(e) {
  try {
    const payload = { respuesta_uaeac: respuestaUaeac.value, fecha_respuesta: new Date().toISOString().slice(0, 10) }
    await api.put(`/enmiendas/${e.id}`, payload)
    $q.notify({ type: 'positive', message: 'Respuesta UAEAC guardada.' })
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar respuesta.' })
  }
}

function abrirForm(e = null) {
  editando.value = e
  form.value = e
    ? { documento_id: e.documento_id, descripcion: e.descripcion, contenido_cambio: e.contenido_cambio, estado: e.estado, fecha_envio: e.fecha_envio || '' }
    : formVacio()
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.documento_id || !form.value.descripcion || !form.value.contenido_cambio) {
    $q.notify({ type: 'warning', message: 'Documento, descripción y contenido son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    if (editando.value) {
      await api.put(`/enmiendas/${editando.value.id}`, form.value)
      $q.notify({ type: 'positive', message: 'Enmienda actualizada.' })
    } else {
      await api.post('/enmiendas', form.value)
      $q.notify({ type: 'positive', message: 'Enmienda creada.' })
    }
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la enmienda.' })
  } finally {
    guardando.value = false
  }
}

onMounted(cargar)
</script>
