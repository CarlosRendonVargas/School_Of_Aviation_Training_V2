<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">OACI Anexo 19 · SMS · Capacitación Obligatoria</div>
        <h1 class="rac-page-title">Capacitaciones <span class="text-red-9">de Seguridad</span></h1>
      </div>
      <q-btn v-if="auth.esAdmin || auth.esDirOps"
        unelevated color="red-9" icon="school" label="Nueva Capacitación"
        class="premium-btn" @click="abrirForm()" />
    </div>

    <!-- KPIs -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="k in kpis" :key="k.label" class="col-6 col-md-3">
        <q-card class="premium-glass-card q-pa-md text-center">
          <q-icon :name="k.icono" :color="k.color" size="26px" />
          <div class="text-h5 text-white text-weight-bold q-mt-xs">{{ k.valor }}</div>
          <div class="font-mono text-grey-6" style="font-size:10px">{{ k.label }}</div>
        </q-card>
      </div>
    </div>

    <!-- Tabs -->
    <q-card class="premium-glass-card">
      <q-tabs v-model="tab" dense active-color="red-9" indicator-color="red-9"
        class="text-grey-5 font-mono" style="font-size:11px">
        <q-tab name="programadas" label="Programadas" />
        <q-tab name="realizadas" label="Realizadas" />
        <q-tab name="canceladas" label="Canceladas" />
      </q-tabs>
      <q-separator dark />

      <div v-if="cargando" class="flex flex-center q-pa-xl">
        <q-spinner-cube color="red-9" size="40px" />
      </div>

      <div v-else class="row q-pa-md q-col-gutter-md">
        <div v-for="cap in capsFiltradas" :key="cap.id" class="col-12 col-md-6 col-lg-4">
          <q-card class="premium-glass-card" style="border:1px solid rgba(255,255,255,0.06)">
            <q-card-section class="q-pb-sm">
              <div class="row items-start justify-between no-wrap">
                <div class="col">
                  <q-badge :color="colorTipo(cap.tipo)" class="font-mono q-mb-xs" style="font-size:9px">
                    {{ labelTipo(cap.tipo) }}
                  </q-badge>
                  <q-badge v-if="cap.obligatoria" color="red-9" class="font-mono q-mb-xs q-ml-xs" style="font-size:9px">OBLIGATORIA</q-badge>
                  <div class="text-white text-weight-bold" style="font-size:14px">{{ cap.titulo }}</div>
                  <div class="font-mono text-grey-6 q-mt-xs" style="font-size:10px">
                    {{ formatFechaCO(cap.fecha) }} · {{ cap.duracion_horas }}h
                    <span v-if="cap.instructor_nombre"> · {{ cap.instructor_nombre }}</span>
                  </div>
                </div>
                <div v-if="auth.esAdmin || auth.esDirOps" class="row no-wrap q-ml-sm">
                  <q-btn flat round dense icon="group" color="teal-5" size="xs"
                    @click="verAsistencia(cap)" title="Gestionar asistencia" />
                  <q-btn flat round dense icon="edit" color="grey-5" size="xs" @click="abrirForm(cap)" />
                </div>
              </div>
            </q-card-section>

            <q-separator dark />

            <q-card-section class="q-py-sm">
              <div class="text-grey-5" style="font-size:11px; line-height:1.5">
                {{ cap.descripcion || 'Sin descripción adicional.' }}
              </div>
            </q-card-section>

            <q-card-section class="q-pt-none row items-center justify-between">
              <div class="font-mono text-grey-6" style="font-size:10px">
                {{ cap.registros_count || 0 }} participantes registrados
              </div>
              <a v-if="cap.material_url" :href="cap.material_url" target="_blank" rel="noopener"
                class="text-teal-5 font-mono" style="font-size:10px; text-decoration:none">
                ▶ Material
              </a>
            </q-card-section>
          </q-card>
        </div>

        <div v-if="!capsFiltradas.length" class="col-12 text-center q-pa-xl">
          <q-icon name="school" size="52px" color="grey-8" />
          <div class="text-grey-6 font-mono q-mt-md" style="font-size:12px">Sin capacitaciones en este estado</div>
        </div>
      </div>
    </q-card>

    <!-- Diálogo asistencia -->
    <q-dialog v-model="dialogAsistencia" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(640px,95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase q-mb-xs" style="font-size:10px">Registro de Asistencia</div>
          <div class="text-h6 text-white font-head text-weight-bolder">{{ capActiva?.titulo }}</div>
        </div>

        <div class="q-pa-lg">
          <div v-if="cargandoRegistros" class="flex flex-center q-py-lg">
            <q-spinner-cube color="red-9" size="30px" />
          </div>
          <q-table v-else :rows="registros" :columns="colsRegistros" row-key="id"
            dark flat class="bg-transparent" :rows-per-page-options="[20]">
            <template #top-right>
              <q-btn unelevated color="teal-7" icon="person_add" label="Agregar" size="sm"
                @click="dialogAgregarParticipante = true" />
            </template>
            <template #body-cell-asistio="{ row }">
              <q-td>
                <q-toggle :model-value="row.asistio" color="green-7" dense
                  @update:model-value="v => actualizarAsistencia(row, { asistio: v })" />
              </q-td>
            </template>
            <template #body-cell-aprobado="{ row }">
              <q-td>
                <q-badge :color="row.aprobado ? 'green-7' : 'grey-7'" class="font-mono" style="font-size:9px">
                  {{ row.aprobado ? 'APROBADO' : 'PENDIENTE' }}
                </q-badge>
              </q-td>
            </template>
          </q-table>
        </div>

        <q-separator dark />
        <div class="q-pa-md row justify-end">
          <q-btn flat label="Cerrar" color="grey-6" v-close-popup />
        </div>
      </q-card>
    </q-dialog>

    <!-- Diálogo agregar participante -->
    <q-dialog v-model="dialogAgregarParticipante" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20" style="width:min(440px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h6 text-white font-head text-weight-bolder">Agregar Participante</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Usuario</div>
            <q-select v-model="formParticipante.usuario_id" :options="usuariosOps" emit-value map-options
              dark outlined dense use-input input-debounce="0" @filter="filtrarUsuarios" />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <q-toggle v-model="formParticipante.asistio" color="green-7" label="Asistió" dark />
            </div>
            <div class="col-6">
              <q-toggle v-model="formParticipante.aprobado" color="teal-7" label="Aprobado" dark />
            </div>
          </div>
          <div>
            <div class="campo-label">Nota (0-100)</div>
            <q-input v-model.number="formParticipante.nota" type="number" min="0" max="100" dark outlined dense />
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-md row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" label="Registrar" class="premium-btn"
            :loading="guardando" @click="agregarParticipante" />
        </div>
      </q-card>
    </q-dialog>

    <!-- Diálogo form capacitación -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(580px,95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase q-mb-xs" style="font-size:10px">SMS · Seguridad Operacional</div>
          <div class="text-h5 text-white font-head text-weight-bolder">{{ editando ? 'Editar' : 'Nueva' }} Capacitación</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Título</div>
            <q-input v-model="form.titulo" dark outlined dense placeholder="Ej: Seguridad Operacional Básica" />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Tipo</div>
              <q-select v-model="form.tipo" :options="tiposOpts" emit-value map-options dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label required">Estado</div>
              <q-select v-model="form.estado" :options="estadosOpts" emit-value map-options dark outlined dense />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Fecha</div>
              <q-input v-model="form.fecha" type="date" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label required">Duración (horas)</div>
              <q-input v-model.number="form.duracion_horas" type="number" min="1" dark outlined dense />
            </div>
          </div>
          <div>
            <div class="campo-label">Instructor</div>
            <q-input v-model="form.instructor_nombre" dark outlined dense placeholder="Nombre del instructor..." />
          </div>
          <div>
            <div class="campo-label">Descripción</div>
            <q-input v-model="form.descripcion" dark outlined dense type="textarea" rows="3" />
          </div>
          <div>
            <div class="campo-label">URL Material de Apoyo</div>
            <q-input v-model="form.material_url" dark outlined dense placeholder="https://..." />
          </div>
          <q-toggle v-model="form.obligatoria" color="red-9" label="Capacitación obligatoria" dark />
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" :label="editando ? 'Guardar cambios' : 'Crear Capacitación'"
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
import { formatFechaCO } from 'src/utils/formatters'

const $q   = useQuasar()
const auth = useAuthStore()

const cargando            = ref(false)
const cargandoRegistros   = ref(false)
const guardando           = ref(false)
const caps                = ref([])
const registros           = ref([])
const capActiva           = ref(null)
const editando            = ref(null)
const tab                 = ref('programadas')
const dialogForm          = ref(false)
const dialogAsistencia    = ref(false)
const dialogAgregarParticipante = ref(false)
const usuarios            = ref([])
const usuariosOps         = ref([])
const formParticipante    = ref({ usuario_id: null, asistio: true, aprobado: false, nota: null })

const tiposOpts  = [
  { value: 'inicial',              label: 'Inicial' },
  { value: 'recurrente',           label: 'Recurrente' },
  { value: 'especializada',        label: 'Especializada' },
  { value: 'simulacro_emergencia', label: 'Simulacro de Emergencia' },
]
const estadosOpts = [
  { value: 'programada', label: 'Programada' },
  { value: 'realizada',  label: 'Realizada' },
  { value: 'cancelada',  label: 'Cancelada' },
]

const colorTipo = t => ({ inicial: 'blue-6', recurrente: 'teal-6', especializada: 'purple-6', simulacro_emergencia: 'red-9' }[t] || 'grey-6')
const labelTipo = t => tiposOpts.find(o => o.value === t)?.label || t

const formVacio = () => ({
  titulo: '', tipo: 'recurrente', estado: 'programada', fecha: new Date().toISOString().slice(0,10),
  duracion_horas: 4, instructor_nombre: '', descripcion: '', material_url: '', obligatoria: true,
})
const form = ref(formVacio())

const kpis = computed(() => [
  { label: 'TOTAL',       valor: caps.value.length, icono: 'school', color: 'blue-5' },
  { label: 'PROGRAMADAS', valor: caps.value.filter(c => c.estado === 'programada').length, icono: 'event', color: 'amber-5' },
  { label: 'REALIZADAS',  valor: caps.value.filter(c => c.estado === 'realizada').length, icono: 'done_all', color: 'green-5' },
  { label: 'OBLIGATORIAS',valor: caps.value.filter(c => c.obligatoria).length, icono: 'priority_high', color: 'red-5' },
])

const capsFiltradas = computed(() => caps.value.filter(c => c.estado === tab.value))

const colsRegistros = [
  { name: 'usuario',  label: 'Participante', field: row => `${row.usuario?.persona?.nombres} ${row.usuario?.persona?.apellidos}`, align: 'left' },
  { name: 'asistio',  label: 'Asistió',      field: 'asistio',  align: 'center' },
  { name: 'nota',     label: 'Nota',         field: 'nota',     align: 'center' },
  { name: 'aprobado', label: 'Estado',       field: 'aprobado', align: 'center' },
]

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/capacitaciones-sms')
    caps.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar capacitaciones.' })
  } finally {
    cargando.value = false
  }
}

async function verAsistencia(cap) {
  capActiva.value = cap
  dialogAsistencia.value = true
  cargandoRegistros.value = true
  try {
    const [regRes, usrRes] = await Promise.all([
      api.get(`/capacitaciones-sms/${cap.id}/registros`),
      api.get('/mensajes/usuarios'),
    ])
    registros.value  = regRes.data.data.registros
    usuarios.value   = usrRes.data.data
    usuariosOps.value = usuarios.value.map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar registros.' })
  } finally {
    cargandoRegistros.value = false
  }
}

async function actualizarAsistencia(reg, cambios) {
  try {
    await api.post(`/capacitaciones-sms/${capActiva.value.id}/asistencia`, {
      usuario_id: reg.usuario_id,
      ...cambios,
    })
    Object.assign(reg, cambios)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al actualizar asistencia.' })
  }
}

function filtrarUsuarios(val, update) {
  update(() => {
    const q = val.toLowerCase()
    usuariosOps.value = usuarios.value.filter(u => u.nombre.toLowerCase().includes(q)).map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  })
}

async function agregarParticipante() {
  if (!formParticipante.value.usuario_id) {
    $q.notify({ type: 'warning', message: 'Selecciona un usuario.' })
    return
  }
  guardando.value = true
  try {
    await api.post(`/capacitaciones-sms/${capActiva.value.id}/asistencia`, formParticipante.value)
    $q.notify({ type: 'positive', message: 'Participante registrado.' })
    dialogAgregarParticipante.value = false
    await verAsistencia(capActiva.value)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al registrar participante.' })
  } finally {
    guardando.value = false
  }
}

function abrirForm(cap = null) {
  editando.value = cap
  form.value = cap
    ? { titulo: cap.titulo, tipo: cap.tipo, estado: cap.estado, fecha: cap.fecha, duracion_horas: cap.duracion_horas, instructor_nombre: cap.instructor_nombre || '', descripcion: cap.descripcion || '', material_url: cap.material_url || '', obligatoria: cap.obligatoria }
    : formVacio()
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.titulo || !form.value.fecha) {
    $q.notify({ type: 'warning', message: 'Título y fecha son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    if (editando.value) {
      await api.put(`/capacitaciones-sms/${editando.value.id}`, form.value)
      $q.notify({ type: 'positive', message: 'Capacitación actualizada.' })
    } else {
      await api.post('/capacitaciones-sms', form.value)
      $q.notify({ type: 'positive', message: 'Capacitación creada.' })
    }
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar.' })
  } finally {
    guardando.value = false
  }
}

onMounted(cargar)
</script>
