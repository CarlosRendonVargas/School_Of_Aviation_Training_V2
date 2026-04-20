<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado de Gestión de Capitanes ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="assignment_ind" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">PERSONAL DE VUELO · RAC 65 / 141</div>
          <h1 class="text-h4 text-weight-bolder text-white font-head q-my-none">Instructores de Vuelo</h1>
        </div>
      </div>
      <q-btn 
        v-if="puedeCrear" 
        unelevated color="red-9" icon="person_add" label="Registrar Instructor" 
        @click="dialogNuevo = true" 
        class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" 
      />
    </div>

    <!-- ══ Filtros de Operación (Cristal) ══ -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-12 col-md-5">
        <q-input 
          v-model="buscar" 
          outlined dense dark 
          class="premium-input-login"
          placeholder="Buscar por HK / Licencia / Nombre..."
        >
          <template #prepend><q-icon name="search" color="red-9" size="18px" /></template>
        </q-input>
      </div>
      <div class="col-6 col-md-3">
        <q-select 
          v-model="filtroLicencia" 
          outlined dense dark 
          :options="tiposLicencia" 
          emit-value map-options clearable 
          label="Categoría RAC" 
          stack-label 
          class="premium-input-login" 
        />
      </div>
      <div class="col-6 col-md-4 flex items-center">
        <q-toggle v-model="soloActivos" color="red-9" label="Solo tripulación en servicio" dark class="font-mono text-grey-4 uppercase" style="font-size: 10px" />
      </div>
    </div>

    <!-- ══ Grid de Credenciales Premium ══ -->
    <div class="row q-col-gutter-lg">
      <div v-if="cargando" v-for="n in 6" :key="n" class="col-12 col-sm-6 col-md-4">
        <q-skeleton type="rect" height="200px" dark class="border-radius-16" />
      </div>

      <div v-for="inst in instructores" :key="inst.id" class="col-12 col-sm-6 col-md-4 animate-slide-up">
        <q-card class="premium-glass-card shadow-24 premium-hover-card" :class="{'border-red-glow': !licenciaOk(inst)}">
          <q-card-section class="q-pa-xl">
            <div class="row items-start no-wrap q-mb-xl">
              <div class="relative-position q-mr-lg">
                <q-avatar size="64px" class="glow-avatar shadow-10">
                  <img :src="`https://ui-avatars.com/api/?name=${inst.persona?.nombres}+${inst.persona?.apellidos}&background=A10B13&color=fff&bold=true`" />
                </q-avatar>
                <div class="status-dot" :class="inst.activo ? 'bg-emerald' : 'bg-grey-7'"></div>
              </div>
              <div class="col overflow-hidden">
                <div class="text-h6 text-white text-weight-bolder font-head truncate line-height-1" style="font-size:17px">
                  {{ inst.persona?.nombres }}<br>{{ inst.persona?.apellidos }}
                </div>
                <div class="font-mono text-red-9 text-weight-bold q-mt-xs tracking-widest uppercase" style="font-size:10px">
                    {{ inst.num_licencia || 'SIN LICENCIA' }}
                </div>
              </div>
            </div>

            <div class="row q-col-gutter-lg q-mb-xl">
              <div class="col-6">
                <div class="text-caption text-grey-7 uppercase font-mono tracking-widest" style="font-size:9px">EXPERIENCIA PIC</div>
                <div class="text-h5 text-white text-weight-bolder font-mono">{{ Number(inst.horas_totales_pic || 0).toFixed(0) }}<span class="text-caption text-grey-6 q-ml-xs">h</span></div>
              </div>
              <div class="col-6 text-right">
                <div class="text-caption text-grey-7 uppercase font-mono tracking-widest" style="font-size:9px">CATEGORÍA</div>
                <q-badge color="red-10" label="RAC-141" class="font-mono q-mt-xs" style="font-size:10px" />
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
    <!-- ════ DIÁLOGO: REGISTRAR NUEVO CAPITÁN ════ -->
    <q-dialog v-model="dialogNuevo" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-low rounded-20" style="width:min(700px, 95vw);">
        <div class="rac-dialog-header">
          <div class="row items-center justify-between q-col-gutter-md">
            <div class="row items-center col">
              <q-icon name="person_add" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter line-height-1">Tripulación de Vuelo</div>
            </div>
            <q-btn flat round dense icon="close" @click="dialogNuevo = false" color="grey-6" class="shadow-inner" />
          </div>
        </div>

        <div class="rac-dialog-body">
          <q-form @submit.prevent="guardarNuevo" class="q-gutter-y-lg">
            <div class="row q-col-gutter-lg">
              <div class="col-12 col-md-6">
                <q-input v-model="form.nombres" label="NOMBRES" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.apellidos" label="APELLIDOS" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-3">
                <q-select v-model="form.tipo_documento" :options="['CC','CE','PA']" label="TIPO DOC" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.num_documento" label="NÚMERO DOC" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-5">
                <q-input v-model="form.fecha_nacimiento" type="date" label="FECHA NACIMIENTO" filled dark class="premium-input-login" stack-label />
              </div>
  
              <q-separator dark class="col-12 q-my-md opacity-10" />
  
              <div class="col-12 col-md-4">
                <q-input v-model="form.num_licencia" label="NÚMERO LICENCIA" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-4">
                <q-select v-model="form.tipo_licencia" :options="['CPL', 'ATPL', 'CFI']" label="CATEGORÍA RAC" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.venc_licencia" type="date" label="VENCIMIENTO" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12">
                <q-input v-model.number="form.horas_totales_pic" type="number" step="0.1" label="EXPERIENCIA PIC ACUMULADA (HORAS)" filled dark class="premium-input-login" stack-label />
              </div>
            </div>
  
            <q-btn type="submit" color="red-10" icon="save" label="Registrar en Tripulación Activa" class="full-width premium-btn q-py-lg shadow-24" :loading="guardando" />
          </q-form>
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const authStore = useAuthStore()
const instructores   = ref([])
const cargando       = ref(false)
const dialogNuevo    = ref(false)
const guardando      = ref(false)
const buscar         = ref('')
const filtroLicencia = ref(null)
const soloActivos    = ref(true)

const form = ref({
  nombres: '',
  apellidos: '',
  tipo_documento: 'CC',
  num_documento: '',
  fecha_nacimiento: '',
  num_licencia: '',
  tipo_licencia: 'CPL',
  venc_licencia: '',
  horas_totales_pic: 0,
})

async function guardarNuevo() {
  guardando.value = true
  try {
    await api.post('/instructores', form.value)
    $q.notify({ color: 'emerald', message: 'Instructor registrado en la base de datos.' })
    dialogNuevo.value = false
    cargar()
  } catch (e) {
    const msg = e.response?.data?.mensaje || 'Error al registrar tripulante.'
    $q.notify({ color: 'negative', message: msg })
  } finally { guardando.value = false }
}

const puedeCrear = ['admin', 'dir_ops'].includes(authStore.rol)
const tiposLicencia = [ { label: 'CPL (Comercial)', value: 'CPL' }, { label: 'ATPL (Transporte)', value: 'ATPL' }, { label: 'CFI (Instructor)', value: 'CFI' } ]

const licenciaOk   = (i) => i.venc_licencia ? dayjs(i.venc_licencia).isAfter(dayjs().add(30, 'day')) : true
const formatearFecha = (f) => f ? dayjs(f).format('DD/MM/YYYY') : 'SIN FECHA'

async function cargar() {
  cargando.value = true
  try {
    const params = {}
    if (buscar.value)        params.buscar        = buscar.value
    if (filtroLicencia.value) params.tipo_licencia = filtroLicencia.value
    if (soloActivos.value)   params.activo        = 1
    const { data } = await api.get('/instructores', { params })
    instructores.value = data.data?.data || data.data || []
  } finally { cargando.value = false }
}

watch([buscar, filtroLicencia, soloActivos], () => cargar())
onMounted(cargar)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.glow-avatar { border: 3px solid rgba(161, 11, 19, 0.3); box-shadow: 0 0 20px rgba(161,11,19,0.2); }
.status-dot { position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; border-radius: 50%; border: 3px solid #0a0c11; }
.bg-emerald { background: #10b981; }
.text-emerald { color: #10b981; }
.rounded-20 { border-radius: 20px; }

.premium-hover-card {
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
  @media (hover: hover) {
    &:hover {
      transform: translateY(-6px);
      border-color: rgba(161,11,19,0.5) !important;
      box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 15px rgba(161,11,19,0.12) !important;
    }
  }
}
.border-red-glow { border-color: rgba(161, 11, 19, 0.4) !important; box-shadow: 0 0 20px rgba(161, 11, 19, 0.12); }
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.line-height-1 { line-height: 1.1; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important;
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { background: rgba(255,255,255,0.05) !important; border-color: rgba(161,11,19,0.4) !important; }
  }
  &.q-field--focused :deep(.q-field__control) { background: rgba(161, 11, 19, 0.05) !important; border-color: #A10B13 !important; }
}
</style>
