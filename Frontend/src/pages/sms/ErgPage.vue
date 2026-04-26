<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">OACI Anexo 19 · RAC 141 · SMS</div>
        <h1 class="rac-page-title">Plan de <span class="text-red-9">Respuesta a Emergencias</span></h1>
      </div>
      <q-btn v-if="auth.esAdmin || auth.esDirOps"
        unelevated color="red-9" icon="add_alert" label="Nuevo Plan ERG"
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

    <!-- Grid de planes ERG -->
    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-cube color="red-9" size="40px" />
    </div>

    <div v-else-if="!planes.length" class="text-center q-pa-xl">
      <q-icon name="warning_amber" size="56px" color="grey-8" />
      <div class="text-grey-6 font-mono q-mt-md" style="font-size:12px">Sin planes ERG registrados</div>
      <div class="text-grey-7 q-mt-xs" style="font-size:11px">Define los procedimientos de emergencia para cumplir el Anexo 19 de la OACI</div>
    </div>

    <div v-else class="row q-col-gutter-md">
      <div v-for="plan in planes" :key="plan.id" class="col-12 col-md-6">
        <q-card class="premium-glass-card erg-card" :class="{ 'erg-vence': diasParaRevision(plan) <= 30 && diasParaRevision(plan) >= 0 }">
          <q-card-section class="q-pb-sm">
            <div class="row items-start no-wrap justify-between">
              <div class="col">
                <div class="row items-center q-gutter-sm q-mb-xs">
                  <q-icon :name="iconoEmergencia(plan.tipo_emergencia)" :color="colorEmergencia(plan.tipo_emergencia)" size="22px" />
                  <q-badge :color="colorEmergencia(plan.tipo_emergencia)" class="font-mono" style="font-size:9px">
                    {{ labelEmergencia(plan.tipo_emergencia) }}
                  </q-badge>
                  <q-badge v-if="!plan.activo" color="grey-7" class="font-mono" style="font-size:9px">INACTIVO</q-badge>
                </div>
                <div class="text-white text-weight-bold q-mb-xs" style="font-size:15px">{{ plan.titulo }}</div>
                <div class="font-mono text-grey-6" style="font-size:10px">
                  Versión {{ plan.version }} · Rev. {{ formatFechaCO(plan.fecha_revision) }}
                </div>
              </div>
              <div v-if="auth.esAdmin || auth.esDirOps" class="row no-wrap">
                <q-btn flat round dense icon="edit" color="grey-5" size="xs" @click="abrirForm(plan)" />
                <q-btn flat round dense icon="delete" color="red-9" size="xs" @click="eliminar(plan)" />
              </div>
            </div>
          </q-card-section>

          <q-separator dark />

          <q-card-section class="q-py-sm">
            <div class="font-mono text-grey-5 q-mb-xs" style="font-size:10px; letter-spacing:1px">PROCEDIMIENTO</div>
            <div class="text-grey-3" style="font-size:12px; line-height:1.6; max-height:80px; overflow:hidden">
              {{ plan.procedimiento }}
            </div>
          </q-card-section>

          <q-separator dark />

          <q-card-section class="row q-col-gutter-md q-py-sm">
            <div class="col-6">
              <div class="font-mono text-grey-6" style="font-size:9px">PERSONAL RESPONSABLE</div>
              <div class="text-grey-4" style="font-size:11px; margin-top:2px">{{ plan.personal_responsable?.slice(0,80) }}</div>
            </div>
            <div class="col-6">
              <div class="font-mono text-grey-6" style="font-size:9px">PRÓXIMA REVISIÓN</div>
              <div :class="diasParaRevision(plan) <= 30 ? 'text-red-5' : 'text-amber-5'"
                class="font-mono text-weight-bold" style="font-size:12px; margin-top:2px">
                {{ plan.proxima_revision }}
                <span v-if="diasParaRevision(plan) >= 0" style="font-size:10px; opacity:.8">
                  ({{ diasParaRevision(plan) }}d)
                </span>
              </div>
            </div>
          </q-card-section>

          <q-btn flat dense class="full-width text-grey-5 font-mono q-py-xs" style="font-size:10px; border-top:1px solid rgba(255,255,255,0.05)"
            @click="verDetalle(plan)">
            VER PLAN COMPLETO ▼
          </q-btn>
        </q-card>
      </div>
    </div>

    <!-- Diálogo detalle plan -->
    <q-dialog v-model="dialogDetalle" maximized backdrop-filter="blur(10px)">
      <div class="column full-height" style="background:#05070a">
        <div class="row items-center no-wrap q-px-lg q-py-md"
          style="border-bottom:1px solid rgba(255,255,255,0.07); background:rgba(10,13,20,0.95); flex-shrink:0">
          <q-icon :name="iconoEmergencia(planActivo?.tipo_emergencia)" color="red-9" size="22px" class="q-mr-md" />
          <div class="col">
            <div class="text-white text-weight-bold" style="font-size:15px">{{ planActivo?.titulo }}</div>
            <div class="font-mono text-grey-6" style="font-size:10px">{{ labelEmergencia(planActivo?.tipo_emergencia) }} · v{{ planActivo?.version }}</div>
          </div>
          <q-btn flat round dense icon="close" color="grey-4" v-close-popup />
        </div>
        <q-scroll-area class="col">
          <div class="q-pa-xl" style="max-width:800px; margin:0 auto">
            <div class="q-mb-lg">
              <div class="font-mono text-red-9 text-uppercase q-mb-sm" style="font-size:10px; letter-spacing:1.5px">PROCEDIMIENTO DE EMERGENCIA</div>
              <div class="text-grey-3" style="font-size:14px; line-height:1.8; white-space:pre-wrap">{{ planActivo?.procedimiento }}</div>
            </div>
            <div class="q-mb-lg">
              <div class="font-mono text-red-9 text-uppercase q-mb-sm" style="font-size:10px; letter-spacing:1.5px">PERSONAL RESPONSABLE</div>
              <div class="text-grey-3" style="font-size:13px; white-space:pre-wrap">{{ planActivo?.personal_responsable }}</div>
            </div>
            <div class="q-mb-lg">
              <div class="font-mono text-red-9 text-uppercase q-mb-sm" style="font-size:10px; letter-spacing:1.5px">CONTACTOS DE EMERGENCIA</div>
              <div class="text-grey-3" style="font-size:13px; white-space:pre-wrap">{{ planActivo?.contactos_emergencia }}</div>
            </div>
            <div class="row q-gutter-md">
              <q-chip color="grey-9" text-color="grey-4" icon="event" class="font-mono">Rev. {{ planActivo?.fecha_revision }}</q-chip>
              <q-chip color="grey-9" text-color="amber-5" icon="schedule" class="font-mono">Próx. {{ planActivo?.proxima_revision }}</q-chip>
              <q-chip color="grey-9" text-color="grey-4" icon="tag" class="font-mono">v{{ planActivo?.version }}</q-chip>
            </div>
          </div>
        </q-scroll-area>
      </div>
    </q-dialog>

    <!-- Diálogo form -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(640px,95vw); max-height:90vh; overflow-y:auto">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase tracking-widest q-mb-xs" style="font-size:10px">Anexo 19 OACI · SMS · RAC 141</div>
          <div class="text-h5 text-white font-head text-weight-bolder">{{ editando ? 'Editar' : 'Nuevo' }} Plan ERG</div>
        </div>

        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Título del Plan</div>
            <q-input v-model="form.titulo" dark outlined dense placeholder="Ej: Protocolo de Accidente de Aeronave" />
          </div>
          <div>
            <div class="campo-label required">Tipo de Emergencia</div>
            <q-select v-model="form.tipo_emergencia" :options="tiposEmerg" emit-value map-options dark outlined dense />
          </div>
          <div>
            <div class="campo-label required">Procedimiento de Respuesta</div>
            <q-input v-model="form.procedimiento" dark outlined dense type="textarea" rows="5"
              placeholder="Describir paso a paso el procedimiento de respuesta a la emergencia..." />
          </div>
          <div>
            <div class="campo-label required">Personal Responsable</div>
            <q-input v-model="form.personal_responsable" dark outlined dense type="textarea" rows="3"
              placeholder="Director de Ops, Instructores, etc..." />
          </div>
          <div>
            <div class="campo-label required">Contactos de Emergencia</div>
            <q-input v-model="form.contactos_emergencia" dark outlined dense type="textarea" rows="3"
              placeholder="Aeronáutica Civil: 3XXXXXXX | Bomberos: 119 | Ambulancia: 125..." />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-4">
              <div class="campo-label required">Versión</div>
              <q-input v-model="form.version" dark outlined dense placeholder="1.0" />
            </div>
            <div class="col-4">
              <div class="campo-label required">Fecha Revisión</div>
              <q-input v-model="form.fecha_revision" type="date" dark outlined dense />
            </div>
            <div class="col-4">
              <div class="campo-label required">Próxima Revisión</div>
              <q-input v-model="form.proxima_revision" type="date" dark outlined dense />
            </div>
          </div>
          <q-toggle v-model="form.activo" color="red-9" label="Plan activo" dark />
        </div>

        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" :label="editando ? 'Guardar cambios' : 'Crear Plan ERG'"
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

const cargando    = ref(false)
const guardando   = ref(false)
const planes      = ref([])
const planActivo  = ref(null)
const editando    = ref(null)
const dialogForm  = ref(false)
const dialogDetalle = ref(false)

const tiposEmerg = [
  { value: 'accidente_aereo',     label: 'Accidente de Aeronave' },
  { value: 'incendio',            label: 'Incendio' },
  { value: 'evacuacion',          label: 'Evacuación General' },
  { value: 'derrame_combustible', label: 'Derrame de Combustible' },
  { value: 'lesion_personal',     label: 'Lesión de Personal' },
  { value: 'amenaza_bomba',       label: 'Amenaza de Bomba' },
  { value: 'falla_estructural',   label: 'Falla Estructural' },
  { value: 'otro',                label: 'Otro' },
]

const iconoEmergencia = t => ({ accidente_aereo: 'flight_land', incendio: 'local_fire_department', evacuacion: 'exit_to_app', derrame_combustible: 'local_gas_station', lesion_personal: 'emergency', amenaza_bomba: 'warning', falla_estructural: 'domain_disabled', otro: 'report_problem' }[t] || 'report_problem')
const colorEmergencia = t => ({ accidente_aereo: 'red-9', incendio: 'deep-orange-7', evacuacion: 'orange-7', derrame_combustible: 'amber-7', lesion_personal: 'red-5', amenaza_bomba: 'red-9', falla_estructural: 'purple-7', otro: 'grey-7' }[t] || 'grey-7')
const labelEmergencia = t => tiposEmerg.find(e => e.value === t)?.label || t

const kpis = computed(() => [
  { label: 'PLANES ACTIVOS',    valor: planes.value.filter(p => p.activo).length, icono: 'shield', color: 'green-5' },
  { label: 'TOTAL PLANES',      valor: planes.value.length, icono: 'folder_open', color: 'blue-5' },
  { label: 'VENCEN EN 30D',     valor: planes.value.filter(p => diasParaRevision(p) >= 0 && diasParaRevision(p) <= 30).length, icono: 'schedule', color: 'amber-5' },
  { label: 'VENCIDOS',          valor: planes.value.filter(p => diasParaRevision(p) < 0).length, icono: 'error', color: 'red-5' },
])

function diasParaRevision(plan) {
  if (!plan.proxima_revision) return 999
  return Math.floor((new Date(plan.proxima_revision) - new Date()) / 86400000)
}

function verDetalle(plan) {
  planActivo.value = plan
  dialogDetalle.value = true
}

const formVacio = () => ({
  titulo: '', tipo_emergencia: 'accidente_aereo', procedimiento: '',
  personal_responsable: '', contactos_emergencia: '',
  version: '1.0', fecha_revision: new Date().toISOString().slice(0,10),
  proxima_revision: new Date(Date.now() + 365*864e5).toISOString().slice(0,10),
  activo: true,
})
const form = ref(formVacio())

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/erg')
    planes.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar planes ERG.' })
  } finally {
    cargando.value = false
  }
}

function abrirForm(plan = null) {
  editando.value = plan
  form.value = plan
    ? { titulo: plan.titulo, tipo_emergencia: plan.tipo_emergencia, procedimiento: plan.procedimiento, personal_responsable: plan.personal_responsable, contactos_emergencia: plan.contactos_emergencia, version: plan.version, fecha_revision: plan.fecha_revision, proxima_revision: plan.proxima_revision, activo: plan.activo }
    : formVacio()
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.titulo || !form.value.procedimiento || !form.value.personal_responsable) {
    $q.notify({ type: 'warning', message: 'Título, procedimiento y personal son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    if (editando.value) {
      await api.put(`/erg/${editando.value.id}`, form.value)
      $q.notify({ type: 'positive', message: 'Plan ERG actualizado.' })
    } else {
      await api.post('/erg', form.value)
      $q.notify({ type: 'positive', message: 'Plan ERG creado.' })
    }
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar el plan ERG.' })
  } finally {
    guardando.value = false
  }
}

function eliminar(plan) {
  $q.dialog({
    title: 'Eliminar Plan ERG',
    message: `¿Eliminar "${plan.titulo}"?`,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { label: 'Eliminar', color: 'red-9', unelevated: true },
    dark: true, class: 'premium-glass-card',
  }).onOk(async () => {
    try {
      await api.delete(`/erg/${plan.id}`)
      $q.notify({ type: 'positive', message: 'Plan ERG eliminado.' })
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar.' })
    }
  })
}

onMounted(cargar)
</script>

<style scoped>
.erg-card { border: 1px solid rgba(255,255,255,0.06) !important; transition: all 0.2s; }
.erg-card:hover { border-color: rgba(161,11,19,0.3) !important; }
.erg-vence { border-color: rgba(245,166,35,0.35) !important; }
</style>
