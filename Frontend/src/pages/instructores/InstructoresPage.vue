<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="assignment_ind" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="rac-page-subtitle">PERSONAL DE VUELO · RAC 65 / 141</div>
          <h1 class="rac-page-title">Instructores de Vuelo</h1>
        </div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn outline color="blue-4" icon="rate_review" label="Evaluaciones RAC 65"
          @click="$router.push('/evaluaciones-instructor')" class="font-mono" />
        <q-btn v-if="puedeCrear" unelevated color="red-9" icon="person_add"
          label="Registrar Instructor" @click="dialogNuevo = true"
          class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" />
      </div>
    </div>

    <!-- ══ Filtros ══ -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-12 col-md-5">
        <q-input v-model="buscar" outlined dense dark class="premium-input-login"
          placeholder="Buscar por Licencia / Nombre...">
          <template #prepend><q-icon name="search" color="red-9" size="18px" /></template>
        </q-input>
      </div>
      <div class="col-6 col-md-3">
        <q-select v-model="filtroLicencia" outlined dense dark :options="tiposLicencia"
          emit-value map-options clearable label="Categoría RAC" stack-label class="premium-input-login" />
      </div>
      <div class="col-6 col-md-4 flex items-center">
        <q-toggle v-model="soloActivos" color="red-9" label="Solo activos" dark
          class="font-mono text-grey-4 uppercase" style="font-size:10px" />
      </div>
    </div>

    <!-- ══ Grid de Instructores ══ -->
    <div class="row q-col-gutter-lg">
      <div v-if="cargando" v-for="n in 6" :key="n" class="col-12 col-sm-6 col-md-4">
        <q-skeleton type="rect" height="200px" dark class="border-radius-16" />
      </div>

      <div v-for="inst in instructores" :key="inst.id" class="col-12 col-sm-6 col-md-4 animate-slide-up">
        <q-card class="premium-glass-card shadow-24 premium-hover-card cursor-pointer"
          :class="{'border-red-glow': !licenciaOk(inst)}"
          @click="verDetalle(inst)">
          <q-card-section class="q-pa-xl">
            <div class="row items-start no-wrap q-mb-xl">
              <div class="relative-position q-mr-lg">
                <q-avatar size="64px" class="glow-avatar shadow-10">
                  <img v-if="inst.persona?.foto_url" :src="inst.persona.foto_url" />
                  <img v-else :src="`https://ui-avatars.com/api/?name=${inst.persona?.nombres}+${inst.persona?.apellidos}&background=A10B13&color=fff&bold=true`" />
                </q-avatar>
                <div class="status-dot" :class="inst.activo ? 'bg-emerald' : 'bg-grey-7'"></div>
              </div>
              <div class="col overflow-hidden">
                <div class="text-h6 text-white text-weight-bolder font-head truncate" style="font-size:17px">
                  {{ inst.persona?.nombres }}<br>{{ inst.persona?.apellidos }}
                </div>
                <div class="font-mono text-red-9 text-weight-bold q-mt-xs tracking-widest uppercase" style="font-size:10px">
                  {{ inst.num_licencia || 'SIN LICENCIA' }}
                </div>
                <!-- Habilitaciones -->
                <div class="row q-gutter-xs q-mt-sm">
                  <q-badge v-for="hab in (inst.habilitaciones || [])" :key="hab.tipo"
                    outline color="red-9" class="font-mono" style="font-size:8px">
                    {{ hab.tipo }}
                  </q-badge>
                </div>
              </div>
            </div>

            <div class="row q-col-gutter-lg q-mb-xl">
              <div class="col-6">
                <div class="text-caption text-grey-7 uppercase font-mono" style="font-size:9px">PIC H.</div>
                <div class="text-h5 text-white text-weight-bolder font-mono">
                  {{ Number(inst.horas_totales_pic || 0).toFixed(0) }}<span class="text-caption text-grey-6 q-ml-xs">h</span>
                </div>
              </div>
              <div class="col-6 text-right">
                <div class="text-caption text-grey-7 uppercase font-mono" style="font-size:9px">INSTRUC. H.</div>
                <div class="text-h5 text-white text-weight-bolder font-mono">
                  {{ Number(inst.horas_instruccion || 0).toFixed(0) }}<span class="text-caption text-grey-6 q-ml-xs">h</span>
                </div>
              </div>
            </div>

            <q-separator dark class="q-mb-md opacity-5" />

            <div class="row items-center justify-between">
              <div class="flex items-center" :class="licenciaOk(inst) ? 'text-emerald' : 'text-red-9'">
                <q-icon :name="licenciaOk(inst) ? 'verified' : 'warning'" size="16px" class="q-mr-xs" />
                <span class="font-mono text-weight-bold" style="font-size:9px">
                  {{ licenciaOk(inst) ? 'CERT. VIGENTE' : 'VENCE PRONTO' }}
                </span>
              </div>
              <div class="text-grey-6 font-mono" style="font-size:10px">{{ formatearFecha(inst.venc_licencia) }}</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="!cargando && !instructores.length" class="col-12 text-center q-pa-xl text-grey-8">
        <q-icon name="group_off" size="100px" class="opacity-10 q-mb-md" />
        <div class="text-h5 font-head opacity-30">SIN TRIPULACIÓN DETECTADA</div>
      </div>
    </div>

    <!-- ════ DIÁLOGO: NUEVO INSTRUCTOR ════ -->
    <q-dialog v-model="dialogNuevo" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-low rounded-20" style="width:min(750px,95vw)">
        <div class="rac-dialog-header">
          <div class="row items-center justify-between">
            <div class="row items-center">
              <q-icon name="person_add" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase">Registrar Instructor</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogNuevo=false" color="grey-6" />
          </div>
        </div>
        <div class="rac-dialog-body">
          <q-form @submit.prevent="guardarNuevo" class="q-gutter-y-lg">
            <!-- Datos personales -->
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm">Datos Personales</div>
            <div class="row q-col-gutter-lg">
              <div class="col-12 col-md-6">
                <q-input v-model="form.nombres" label="NOMBRES *" filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']"/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.apellidos" label="APELLIDOS *" filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']"/>
              </div>
              <div class="col-12 col-md-3">
                <q-select v-model="form.tipo_documento" :options="['CC','CE','PA','PT']" label="TIPO DOC" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.num_documento" label="NÚMERO DOCUMENTO *" filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']"/>
              </div>
              <div class="col-12 col-md-5">
                <q-input v-model="form.fecha_nacimiento" type="date" label="FECHA NACIMIENTO" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.telefono" label="TELÉFONO" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.ciudad" label="CIUDAD" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12">
                <q-input v-model="form.foto_url" label="URL FOTO DE PERFIL" filled dark class="premium-input-login" stack-label placeholder="https://..."/>
              </div>
            </div>

            <q-separator dark class="opacity-10 q-my-md" />

            <!-- Datos de licencia -->
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm">Licencia y Experiencia RAC 65</div>
            <div class="row q-col-gutter-lg">
              <div class="col-12 col-md-4">
                <q-input v-model="form.num_licencia" label="NÚMERO LICENCIA *" filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']"/>
              </div>
              <div class="col-12 col-md-4">
                <q-select v-model="form.tipo_licencia" :options="['CPL','ATPL','CFI']" label="CATEGORÍA" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.venc_licencia" type="date" label="VENCIMIENTO *" filled dark class="premium-input-login" stack-label :rules="[v=>!!v||'Requerido']"/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model.number="form.horas_totales_pic" type="number" step="0.1" label="HORAS PIC ACUMULADAS" filled dark class="premium-input-login" stack-label/>
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model.number="form.horas_instruccion" type="number" step="0.1" label="HORAS COMO INSTRUCTOR" filled dark class="premium-input-login" stack-label/>
              </div>
            </div>

            <!-- Habilitaciones -->
            <q-separator dark class="opacity-10 q-my-md" />
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm">Habilitaciones</div>
            <div class="row q-col-gutter-sm q-mb-md">
              <div v-for="hab in habilitacionesDisp" :key="hab" class="col-auto">
                <q-chip clickable outline :selected="form.habilitaciones.includes(hab)"
                  @click="toggleHab(hab)" color="red-9" text-color="white"
                  class="font-mono text-weight-bolder">{{ hab }}</q-chip>
              </div>
            </div>

            <q-btn type="submit" color="red-10" icon="save" label="Registrar Instructor"
              class="full-width premium-btn q-py-lg shadow-24" :loading="guardando" />
          </q-form>
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'store/auth'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const authStore = useAuthStore()
const router    = useRouter()
const $q        = useQuasar()

const instructores   = ref([])
const cargando       = ref(false)
const dialogNuevo    = ref(false)
const guardando      = ref(false)
const buscar         = ref('')
const filtroLicencia = ref(null)
const soloActivos    = ref(true)

const habilitacionesDisp = ['IFR', 'Multimotor', 'Hidroavión', 'Acrobático', 'Globo', 'Planeador', 'Helicóptero']

const form = ref({
  nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '',
  fecha_nacimiento: '', telefono: '', ciudad: '', foto_url: '',
  num_licencia: '', tipo_licencia: 'CPL', venc_licencia: '',
  horas_totales_pic: 0, horas_instruccion: 0,
  habilitaciones: [],
})

function toggleHab(hab) {
  const idx = form.value.habilitaciones.indexOf(hab)
  if (idx >= 0) form.value.habilitaciones.splice(idx, 1)
  else form.value.habilitaciones.push(hab)
}

function verDetalle(inst) {
  router.push(`/instructores/${inst.id}`)
}

async function guardarNuevo() {
  guardando.value = true
  try {
    // Convertir habilitaciones a formato JSONB esperado por el backend
    const payload = {
      ...form.value,
      habilitaciones: form.value.habilitaciones.map(h => ({ tipo: h, venc: null })),
    }
    await api.post('/instructores', payload)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Instructor registrado correctamente.' })
    dialogNuevo.value = false
    resetForm()
    cargar()
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Error al registrar.' })
  } finally { guardando.value = false }
}

function resetForm() {
  form.value = {
    nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '',
    fecha_nacimiento: '', telefono: '', ciudad: '', foto_url: '',
    num_licencia: '', tipo_licencia: 'CPL', venc_licencia: '',
    horas_totales_pic: 0, horas_instruccion: 0, habilitaciones: [],
  }
}

const puedeCrear = ['admin', 'dir_ops'].includes(authStore.rol)
const tiposLicencia = [
  { label: 'CPL (Comercial)',     value: 'CPL' },
  { label: 'ATPL (Transporte)',   value: 'ATPL' },
  { label: 'CFI (Instructor)',    value: 'CFI' },
]

const licenciaOk    = (i) => i.venc_licencia ? dayjs(i.venc_licencia).isAfter(dayjs().add(30, 'day')) : true
const formatearFecha = (f) => f ? dayjs(f).format('DD/MM/YYYY') : 'SIN FECHA'

async function cargar() {
  cargando.value = true
  try {
    const params = {}
    if (buscar.value)         params.buscar        = buscar.value
    if (filtroLicencia.value) params.tipo_licencia = filtroLicencia.value
    if (soloActivos.value)    params.activo        = 1
    const { data } = await api.get('/instructores', { params })
    instructores.value = data.data?.data || data.data || []
  } finally { cargando.value = false }
}

watch([buscar, filtroLicencia, soloActivos], () => cargar())
onMounted(cargar)
</script>

<style lang="scss" scoped>
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.glow-avatar { border: 3px solid rgba(161,11,19,0.3); box-shadow: 0 0 20px rgba(161,11,19,0.2); }
.status-dot { position:absolute; bottom:2px; right:2px; width:14px; height:14px; border-radius:50%; border:3px solid #0a0c11; }
.premium-hover-card {
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
  &:hover { transform:translateY(-6px); border-color:rgba(161,11,19,0.5)!important; box-shadow:0 20px 40px rgba(0,0,0,0.4),0 0 15px rgba(161,11,19,0.12)!important; }
}
.truncate { white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.premium-input-login {
  :deep(.q-field__control) {
    border-radius:12px!important; background:rgba(255,255,255,0.03)!important;
    border:1px solid rgba(255,255,255,0.05)!important; transition:all 0.3s ease;
    &::before,&::after{display:none;}
    &:hover{background:rgba(255,255,255,0.05)!important;border-color:rgba(161,11,19,0.4)!important;}
  }
  &.q-field--focused :deep(.q-field__control){background:rgba(161,11,19,0.05)!important;border-color:#A10B13!important;}
}
</style>
