<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <q-skeleton v-if="cargando" type="rect" height="200px" dark class="premium-glass-card q-mb-xl" />

    <template v-else-if="instructor">

      <!-- ══ Hero Header del Instructor ══ -->
      <q-card class="premium-glass-card q-pa-lg q-pa-md-xl q-mb-xl border-red-left shadow-24 welcome-hero overflow-hidden">
        <div class="hero-glow"></div>
        <div class="row items-center q-gutter-lg relative-position flex-wrap">
          <div class="instructor-avatar shadow-24 flex-shrink-0">
            <img v-if="instructor.persona?.foto_url" :src="instructor.persona.foto_url" style="width:100%;height:100%;object-fit:cover;border-radius:24px" />
            <div v-else class="font-head text-white text-weight-bolder" style="font-size:32px">{{ iniciales }}</div>
          </div>
          <div class="col min-w-0">
            <div class="font-head text-white text-weight-bolder uppercase tracking-tighter instructor-name">
              {{ instructor.persona?.nombres }} {{ instructor.persona?.apellidos }}
            </div>
            <div class="row items-center q-gutter-sm q-mt-sm flex-wrap">
              <div class="rac-page-subtitle">{{ instructor.num_licencia }}</div>
              <q-badge :color="instructor.activo ? 'emerald' : 'grey-8'" :label="instructor.activo ? 'ACTIVO' : 'INACTIVO'" class="font-mono text-weight-bold" />
              <q-badge outline color="red-9" :label="instructor.tipo_licencia" class="font-mono text-weight-bold" />
              <q-badge v-for="hab in (instructor.habilitaciones || [])" :key="hab.tipo"
                outline color="orange-9" :label="hab.tipo" class="font-mono" style="font-size:9px" />
            </div>
            <div class="row q-gutter-xl q-mt-lg">
              <div>
                <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">HORAS PIC</div>
                <div class="text-h5 text-red-9 font-mono text-weight-bolder">{{ Number(instructor.horas_totales_pic||0).toFixed(0) }}h</div>
              </div>
              <div>
                <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">HORAS INSTRUC.</div>
                <div class="text-h5 text-white font-mono text-weight-bolder">{{ Number(instructor.horas_instruccion||0).toFixed(0) }}h</div>
              </div>
              <div>
                <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">VENC. LICENCIA</div>
                <div class="text-h5 font-mono text-weight-bolder" :class="licenciaOk ? 'text-emerald' : 'text-red-9'">
                  {{ fmtFecha(instructor.venc_licencia) }}
                </div>
              </div>
            </div>
          </div>
          <q-btn flat round dense icon="arrow_back" color="grey-6" @click="$router.push('/instructores')" class="flex-shrink-0" />
        </div>
      </q-card>

      <!-- ══ Tabs de Detalle ══ -->
      <q-card class="premium-glass-card shadow-24 overflow-hidden">
        <q-tabs v-model="tab" dense class="text-grey-5"
          active-color="red-9" indicator-color="red-9" align="left" no-caps
          style="border-bottom:1px solid rgba(255,255,255,0.05); padding-left:10px">
          <q-tab name="perfil"       icon="person"           label="Perfil" />
          <q-tab name="certs"        icon="workspace_premium" label="Certificaciones"
            :alert="certsVencidas > 0" alert-color="red-9" />
          <q-tab name="planes"       icon="event_note"       label="Planes de Clase" />
          <q-tab name="bitacoras"    icon="auto_stories"     label="Bitácoras" />
        </q-tabs>

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">

          <!-- ════ PERFIL ════ -->
          <q-tab-panel name="perfil" class="q-pa-xl">
            <div class="row items-center justify-between q-mb-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest">Datos Personales y de Licencia</div>
              <q-btn v-if="puedeEditar" outline color="red-9" icon="edit" label="Editar" no-caps
                class="font-mono text-weight-bolder" @click="abrirEditar" />
            </div>
            <div class="row q-col-gutter-xl">
              <div class="col-12 col-md-6">
                <div class="q-gutter-y-sm">
                  <div v-for="c in datosPersonales" :key="c.label"
                    class="premium-glass-card q-pa-md flex items-center justify-between border-red-low">
                    <span class="text-grey-6 font-mono uppercase" style="font-size:10px">{{ c.label }}</span>
                    <span class="text-white text-weight-bold font-mono">{{ c.valor || '—' }}</span>
                  </div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="q-gutter-y-sm">
                  <div v-for="c in datosLicencia" :key="c.label"
                    class="premium-glass-card q-pa-md flex items-center justify-between border-red-low">
                    <span class="text-grey-6 font-mono uppercase" style="font-size:10px">{{ c.label }}</span>
                    <span class="text-weight-bold font-mono" :class="c.alerta ? 'text-red-9' : 'text-white'">{{ c.valor || '—' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </q-tab-panel>

          <!-- ════ CERTIFICACIONES ════ -->
          <q-tab-panel name="certs" class="q-pa-xl">
            <div class="row items-center justify-between q-mb-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest">Certificaciones RAC 65</div>
              <q-btn v-if="puedeEditar" color="red-9" icon="add" label="Agregar Certificación" no-caps
                class="premium-btn shadow-24 q-px-xl" @click="dialogCert = true" />
            </div>
            <div class="row q-col-gutter-lg">
              <div v-if="!certs.length" class="col-12 text-center q-pa-xl text-grey-7 font-mono uppercase">
                <q-icon name="workspace_premium" size="64px" class="opacity-10 q-mb-md" /><br>
                Sin certificaciones registradas
              </div>
              <div v-for="cert in certs" :key="cert.id" class="col-12 col-md-6">
                <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low"
                  :class="!certVigente(cert) ? 'border-red-glow pulse-red' : ''">
                  <div class="row items-start justify-between q-mb-md">
                    <div>
                      <q-badge color="red-9" :label="cert.tipo?.toUpperCase().replace('_',' ')" class="font-mono q-mb-sm" />
                      <div class="text-h6 text-white font-head text-weight-bolder">{{ cert.descripcion }}</div>
                      <div class="text-caption text-grey-6 font-mono q-mt-xs">Nº {{ cert.numero || 'S/N' }}</div>
                    </div>
                    <q-icon name="workspace_premium" :color="certVigente(cert) ? 'emerald' : 'red-9'" size="36px" />
                  </div>
                  <q-separator dark class="opacity-10 q-my-md" />
                  <div class="row q-col-gutter-md">
                    <div class="col-6">
                      <div class="text-caption text-grey-7 font-mono uppercase">EMISIÓN</div>
                      <div class="text-white font-mono text-weight-bold">{{ fmtFecha(cert.fecha_emision) }}</div>
                    </div>
                    <div class="col-6 text-right">
                      <div class="text-caption text-grey-7 font-mono uppercase">VENCIMIENTO</div>
                      <div class="font-mono text-weight-bold" :class="certVigente(cert) ? 'text-emerald' : 'text-red-9'">
                        {{ fmtFecha(cert.fecha_vencimiento) }}
                      </div>
                    </div>
                  </div>
                  <div v-if="cert.archivo_url" class="q-mt-md">
                    <q-btn flat dense no-caps icon="picture_as_pdf" color="red-9"
                      :href="cert.archivo_url" target="_blank"
                      label="Ver documento PDF" class="font-mono" />
                  </div>
                </q-card>
              </div>
            </div>
          </q-tab-panel>

          <!-- ════ PLANES DE CLASE ════ -->
          <q-tab-panel name="planes" class="q-pa-xl">
            <div class="row items-center justify-between q-mb-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest">Planes de Clase RAC 141</div>
              <q-btn v-if="puedeEditar" color="red-9" icon="add" label="Nuevo Plan" no-caps
                class="premium-btn shadow-24 q-px-xl" @click="dialogPlan = true" />
            </div>
            <q-table :rows="planesClase" :columns="colsPlanes" row-key="id"
              flat dark class="rac-table shadow-24" :grid="$q.screen.lt.md">
              <template #body-cell-estado="props">
                <q-td :props="props" class="text-center">
                  <q-badge :color="colorEstadoPlan(props.value)" :label="props.value?.toUpperCase()" class="font-mono text-weight-bold" />
                </q-td>
              </template>
              <template #body-cell-materia="props">
                <q-td :props="props" class="text-left font-mono">{{ props.value }}</q-td>
              </template>
              <template v-slot:item="props">
                <div class="col-12 q-pa-xs">
                  <q-card class="premium-glass-card border-red-low q-mb-sm shadow-24">
                    <q-card-section class="q-pa-md">
                      <div class="row items-center justify-between">
                        <q-badge :color="colorEstadoPlan(props.row.estado)" :label="props.row.estado?.toUpperCase()" class="font-mono" />
                        <span class="font-mono text-grey-5">{{ fmtFecha(props.row.fecha) }}</span>
                      </div>
                      <div class="text-white font-head text-weight-bold q-mt-sm">{{ props.row.materia?.nombre }}</div>
                      <div class="text-grey-6 font-mono q-mt-xs" style="font-size:10px">
                        {{ props.row.duracion_min }} min · {{ props.row.objetivos?.slice(0,80) }}...
                      </div>
                    </q-card-section>
                  </q-card>
                </div>
              </template>
            </q-table>
          </q-tab-panel>

          <!-- ════ BITÁCORAS ════ -->
          <q-tab-panel name="bitacoras" class="q-pa-xl">
            <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Historial de Misiones como Instructor</div>
            <q-table :rows="bitacoras" :columns="colsBitacoras" row-key="id"
              flat dark class="rac-table shadow-24" :grid="$q.screen.lt.md">
              <template #body-cell-horas="props">
                <q-td :props="props" class="font-mono text-red-9 text-weight-bolder text-right">
                  {{ Number(props.value||0).toFixed(1) }}H
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>

        </q-tab-panels>
      </q-card>
    </template>

    <!-- ════ DIALOG: AGREGAR CERTIFICACIÓN ════ -->
    <q-dialog v-model="dialogCert" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-low rounded-20" style="width:min(550px,95vw)">
        <div class="rac-dialog-header">
          <div class="row items-center justify-between">
            <div class="row items-center">
              <q-icon name="workspace_premium" color="red-9" size="28px" class="q-mr-md" />
              <div class="text-h6 text-white font-head text-weight-bolder uppercase">Nueva Certificación</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogCert=false" color="grey-6" />
          </div>
        </div>
        <div class="rac-dialog-body">
          <q-form @submit.prevent="guardarCert" class="q-gutter-y-lg">
            <q-select v-model="formCert.tipo"
              :options="['licencia','habilitacion','cheque_competencia','medico']"
              label="TIPO DE CERTIFICACIÓN *" filled dark class="premium-input-login" stack-label
              :rules="[v=>!!v||'Requerido']" />
            <q-input v-model="formCert.descripcion" label="DESCRIPCIÓN / DENOMINACIÓN *"
              filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']" />
            <q-input v-model="formCert.numero" label="NÚMERO DE DOCUMENTO" filled dark class="premium-input-login" stack-label />
            <div class="row q-col-gutter-lg">
              <div class="col-6">
                <q-input v-model="formCert.fecha_emision" type="date" label="FECHA EMISIÓN *"
                  filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']" />
              </div>
              <div class="col-6">
                <q-input v-model="formCert.fecha_vencimiento" type="date" label="VENCIMIENTO *"
                  filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']" />
              </div>
            </div>
            <q-input v-model="formCert.archivo_url" label="URL DOCUMENTO PDF" filled dark class="premium-input-login" stack-label placeholder="https://..." />
            <q-btn type="submit" color="red-10" icon="save" label="Guardar Certificación"
              class="full-width premium-btn q-py-lg shadow-24" :loading="guardando" />
          </q-form>
        </div>
      </q-card>
    </q-dialog>

    <!-- ════ DIALOG: NUEVO PLAN DE CLASE ════ -->
    <q-dialog v-model="dialogPlan" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-low rounded-20" style="width:min(650px,95vw)">
        <div class="rac-dialog-header">
          <div class="row items-center justify-between">
            <div class="row items-center">
              <q-icon name="event_note" color="red-9" size="28px" class="q-mr-md" />
              <div class="text-h6 text-white font-head text-weight-bolder uppercase">Nuevo Plan de Clase</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogPlan=false" color="grey-6" />
          </div>
        </div>
        <div class="rac-dialog-body">
          <q-form @submit.prevent="guardarPlan" class="q-gutter-y-lg">
            <q-select v-model="formPlan.materia_id"
              :options="materiasOpts" emit-value map-options
              label="MATERIA / ASIGNATURA *" filled dark class="premium-input-login" stack-label
              :rules="[v=>!!v||'Requerido']" />
            <div class="row q-col-gutter-lg">
              <div class="col-7">
                <q-input v-model="formPlan.fecha" type="date" label="FECHA DE CLASE *"
                  filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']" />
              </div>
              <div class="col-5">
                <q-input v-model.number="formPlan.duracion_min" type="number" min="30" step="30"
                  label="DURACIÓN (MIN)" filled dark class="premium-input-login" stack-label />
              </div>
            </div>
            <q-input v-model="formPlan.objetivos" type="textarea" rows="3"
              label="OBJETIVOS DE APRENDIZAJE *" filled dark class="premium-input-login" stack-label
              :rules="[v=>!!v||'Requerido']" />
            <q-input v-model="formPlan.contenido" type="textarea" rows="4"
              label="CONTENIDO / TEMARIO" filled dark class="premium-input-login" stack-label />
            <q-input v-model="formPlan.recursos" type="textarea" rows="2"
              label="MATERIALES Y RECURSOS" filled dark class="premium-input-login" stack-label />
            <q-select v-model="formPlan.estado"
              :options="['planificada','realizada','cancelada']"
              label="ESTADO" filled dark class="premium-input-login" stack-label />
            <q-btn type="submit" color="red-10" icon="save" label="Guardar Plan de Clase"
              class="full-width premium-btn q-py-lg shadow-24" :loading="guardando" />
          </q-form>
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from 'store/auth'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const route     = useRoute()
const authStore = useAuthStore()
const $q        = useQuasar()
const id        = route.params.id

const cargando   = ref(false)
const instructor = ref(null)
const certs      = ref([])
const planesClase = ref([])
const bitacoras  = ref([])
const materias   = ref([])
const tab        = ref('perfil')
const dialogCert = ref(false)
const dialogPlan = ref(false)
const guardando  = ref(false)

const formCert = ref({ tipo: null, descripcion: '', numero: '', fecha_emision: '', fecha_vencimiento: '', archivo_url: '' })
const formPlan = ref({ materia_id: null, fecha: '', duracion_min: 60, objetivos: '', contenido: '', recursos: '', estado: 'planificada' })

const puedeEditar  = ['admin', 'dir_ops'].includes(authStore.rol)

const iniciales = computed(() => {
  const p = instructor.value?.persona; if (!p) return '--'
  return ((p.nombres?.[0] || '') + (p.apellidos?.[0] || '')).toUpperCase()
})

const licenciaOk   = computed(() => instructor.value?.venc_licencia ? dayjs(instructor.value.venc_licencia).isAfter(dayjs().add(30, 'day')) : true)
const certsVencidas = computed(() => certs.value.filter(c => !certVigente(c)).length)

const certVigente = (c) => c.fecha_vencimiento ? dayjs(c.fecha_vencimiento).isAfter(dayjs()) : true
const fmtFecha    = (f) => f ? dayjs(f).format('DD/MM/YYYY') : '—'

const datosPersonales = computed(() => {
  const p = instructor.value?.persona; if (!p) return []
  return [
    { label: 'Identificación',   valor: `${p.tipo_documento} ${p.num_documento}` },
    { label: 'Teléfono',         valor: p.telefono },
    { label: 'Ciudad',           valor: p.ciudad },
    { label: 'Nacionalidad',     valor: p.nacionalidad },
    { label: 'Fecha Nacimiento', valor: fmtFecha(p.fecha_nacimiento) },
  ]
})

const datosLicencia = computed(() => {
  const i = instructor.value; if (!i) return []
  return [
    { label: 'Número Licencia',  valor: i.num_licencia },
    { label: 'Tipo Licencia',    valor: i.tipo_licencia },
    { label: 'Vencimiento',      valor: fmtFecha(i.venc_licencia), alerta: !licenciaOk.value },
    { label: 'Horas PIC',        valor: `${Number(i.horas_totales_pic||0).toFixed(1)}h` },
    { label: 'Horas Instrucción',valor: `${Number(i.horas_instruccion||0).toFixed(1)}h` },
  ]
})

const materiasOpts = computed(() => materias.value.map(m => ({ value: m.id, label: `${m.codigo} — ${m.nombre}` })))

const colsPlanes = [
  { name: 'fecha',   label: 'FECHA',   field: r => fmtFecha(r.fecha),   align: 'left' },
  { name: 'materia', label: 'MATERIA', field: r => r.materia?.nombre,   align: 'left' },
  { name: 'duracion', label: 'MIN',    field: 'duracion_min',            align: 'center' },
  { name: 'objetivos', label: 'OBJETIVOS', field: r => r.objetivos?.slice(0,60)+'...', align: 'left' },
  { name: 'estado',  label: 'ESTADO',  field: 'estado',                  align: 'center' },
]

const colsBitacoras = [
  { name: 'fecha',      label: 'FECHA',      field: r => fmtFecha(r.fecha),            align: 'left' },
  { name: 'aeronave',   label: 'HK',         field: r => r.aeronave?.matricula,        align: 'center' },
  { name: 'estudiante', label: 'ALUMNO',     field: r => r.estudiante?.persona?.apellidos, align: 'left' },
  { name: 'tipo_vuelo', label: 'MISIÓN',     field: 'tipo_vuelo',                      align: 'center' },
  { name: 'horas',      label: 'BLOCK TIME', field: 'horas_totales',                   align: 'right' },
]

const colorEstadoPlan = (e) => ({ planificada: 'blue-9', realizada: 'emerald', cancelada: 'grey-7' }[e] || 'grey-8')

function abrirEditar() {
  $q.notify({ color: 'info', message: 'Funcionalidad de edición disponible próximamente.' })
}

async function guardarCert() {
  guardando.value = true
  try {
    await api.post(`/instructores/${id}/certificaciones`, formCert.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Certificación registrada.' })
    dialogCert.value = false
    formCert.value = { tipo: null, descripcion: '', numero: '', fecha_emision: '', fecha_vencimiento: '', archivo_url: '' }
    cargarCerts()
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Error al guardar.' })
  } finally { guardando.value = false }
}

async function guardarPlan() {
  guardando.value = true
  try {
    await api.post(`/instructores/${id}/planes-clase`, { ...formPlan.value, instructor_id: id })
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Plan de clase creado.' })
    dialogPlan.value = false
    formPlan.value = { materia_id: null, fecha: '', duracion_min: 60, objetivos: '', contenido: '', recursos: '', estado: 'planificada' }
    cargarPlanes()
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Error al guardar.' })
  } finally { guardando.value = false }
}

async function cargarInstructor() {
  cargando.value = true
  try { const { data } = await api.get(`/instructores/${id}`); instructor.value = data.data }
  finally { cargando.value = false }
}
async function cargarCerts() {
  try { const { data } = await api.get(`/instructores/${id}/certificaciones`); certs.value = data.data || [] }
  catch { certs.value = [] }
}
async function cargarPlanes() {
  try { const { data } = await api.get(`/instructores/${id}/planes-clase`); planesClase.value = data.data || [] }
  catch { planesClase.value = [] }
}
async function cargarBitacoras() {
  try { const { data } = await api.get(`/bitacoras?instructor_id=${id}&per_page=50`); bitacoras.value = data.data?.data || data.data || [] }
  catch { bitacoras.value = [] }
}
async function cargarMaterias() {
  try { const { data } = await api.get('/materias'); materias.value = data.data || [] }
  catch { materias.value = [] }
}

onMounted(() => {
  cargarInstructor()
  cargarCerts()
  cargarPlanes()
  cargarBitacoras()
  cargarMaterias()
})
</script>

<style lang="scss" scoped>
.flex-shrink-0 { flex-shrink: 0; }
.min-w-0       { min-width: 0; }
.instructor-name {
  font-size: 2.2rem;
  @media (max-width:599px) { font-size:1.4rem!important; word-break:break-word; }
}
.instructor-avatar {
  width:90px; height:90px; background:rgba(161,11,19,0.1);
  border:1px solid rgba(161,11,19,0.25); border-radius:24px;
  display:flex; align-items:center; justify-content:center;
  @media (max-width:599px) { width:64px; height:64px; border-radius:16px; }
}
.welcome-hero { position:relative; }
.hero-glow    { position:absolute; top:0; right:0; bottom:0; left:0; background:radial-gradient(circle at 100% 0%, rgba(161,11,19,0.1) 0%, transparent 50%); pointer-events:none; }
.pulse-red    { animation:pulseRed 2s infinite; }
@keyframes pulseRed { 0%,100%{border-color:rgba(161,11,19,0.4);} 50%{border-color:rgba(161,11,19,0.8);} }
.premium-input-login {
  :deep(.q-field__control) {
    border-radius:12px!important; background:rgba(255,255,255,0.03)!important;
    border:1px solid rgba(255,255,255,0.05)!important;
    &::before,&::after{display:none;}
    &:hover{border-color:rgba(161,11,19,0.4)!important;}
  }
}
</style>
