<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Mi Formación ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="trending_up" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">RAC 61 · MI EXPEDIENTE DE VUELO</div>
          <h1 class="rac-page-title">Mi Progreso Académico</h1>
        </div>
      </div>
      <q-btn flat color="red-9" icon="verified" label="Autoverificar para Examen" class="premium-btn text-weight-bolder" @click="verificarExamen" :loading="verificando" />
    </div>

    <q-skeleton v-if="cargando" type="rect" height="400px" dark class="premium-glass-card" />

    <div v-else-if="!progreso" class="text-center q-pa-xl">
      <q-icon name="flight_takeoff" size="72px" color="grey-8" class="q-mb-lg" />
      <div class="text-h6 text-grey-6 font-head q-mb-sm">Sin expediente de vuelo activo</div>
      <div class="text-caption text-grey-7 font-mono">El progreso académico está disponible una vez que el estudiante tenga matrícula activa en un programa RAC 141.</div>
    </div>

    <template v-else-if="progreso">

      <!-- ══ Bloque de Identificación de Expediente ══ -->
      <q-card class="premium-glass-card q-pa-lg q-mb-xl border-red-low shadow-inner overflow-hidden">
        <div class="row items-center q-col-gutter-lg">
          <div class="col-12 col-md-4">
             <div class="text-caption text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">Nº EXPEDIENTE</div>
             <div class="text-h5 text-red-9 font-mono text-weight-bolder line-height-1">{{ progreso.num_expediente }}</div>
          </div>
          <div class="col-12 col-md-4">
             <div class="text-caption text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">PROGRAMA DE FORMACIÓN</div>
             <div class="text-h6 text-white font-head text-weight-bold truncate">{{ progreso.programa }}</div>
          </div>
          <div class="col-12 col-md-4 flex justify-end">
             <q-badge v-if="progreso.listo_para_examen" color="emerald" label="APTO PARA EXAMEN UAEAC" class="font-mono text-weight-bold q-px-xl q-py-sm shadow-24 pulsate" />
             <q-badge v-else color="red-9" label="FASE DE ENTRENAMIENTO" class="font-mono text-weight-bold q-px-xl q-py-sm shadow-24" />
          </div>
        </div>
      </q-card>

      <!-- ══ Sensores de Horas de Cristal ══ -->
      <div class="row q-col-gutter-lg q-mb-xl">
        <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
          <q-card class="premium-glass-card q-pa-xl text-center border-red-low shadow-inner">
             <div class="text-h5 text-weight-bolder font-mono q-mb-xs" :style="`color:${h.color}`">{{ h.valor }}</div>
             <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ h.label }}</div>
          </q-card>
        </div>
      </div>

      <!-- ══ Monitor de Cumplimiento Regulatorio ══ -->
      <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-top shadow-24">
        <div class="text-subtitle1 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Control de Horas Mínimas RAC 61</div>
        
        <div v-for="cat in categorias" :key="cat.key" class="q-mb-xl last-no-margin">
           <div class="row items-center justify-between q-mb-md">
              <div>
                 <div class="text-h6 text-white font-head text-weight-bold">{{ cat.label }}</div>
                 <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:10px">{{ cat.rac }}</div>
              </div>
              <div class="text-right">
                 <div class="text-h5 text-red-9 font-mono text-weight-bolder">
                    {{ cat.acumulado?.toFixed(1) }}<span class="text-grey-7 uppercase" style="font-size:11px">h voladas</span>
                 </div>
                 <div class="text-caption text-grey-7 font-mono">META REGLAMENTARIA: {{ cat.requerido?.toFixed(1) }}H</div>
              </div>
           </div>
           
           <q-linear-progress :value="Math.min((cat.porcentaje||0)/100,1)" :color="cat.cumplido?'emerald':'red-9'" size="10px" rounded track-color="white-1" class="shadow-24" />
           
           <div class="row justify-between q-mt-sm">
              <span v-if="cat.cumplido" class="text-caption text-emerald font-mono text-weight-bold">✔ REQUISITO RAC CUMPLIDO</span>
              <span v-else class="text-caption text-red-9 font-mono text-weight-bold">✘ FALTAN {{ cat.faltante?.toFixed(1) }}H PARA CERTIFICACIÓN</span>
              <span class="font-mono text-grey-6" style="font-size:11px">{{ (cat.porcentaje||0).toFixed(0) }}% COMPLETADO</span>
           </div>
        </div>
      </q-card>

      <!-- ══ Bóveda de Salud RAC 67 ══ -->
      <q-card class="premium-glass-card q-pa-lg shadow-24 border-red-left" :class="!progreso.tiene_medico ? 'border-red-glow pulse-red' : ''">
        <div class="row items-center q-col-gutter-lg">
           <div class="col-auto">
             <q-icon :name="progreso.tiene_medico ? 'verified_user' : 'report_problem'" :color="progreso.tiene_medico ? 'emerald' : 'red-9'" size="42px" />
           </div>
           <div class="col">
              <div class="text-h6 text-white font-head text-weight-bold">Certificado Médico Aeronáutico</div>
              <div class="text-caption text-grey-6 font-mono">
                 ESTADO: <span :class="progreso.tiene_medico ? 'text-emerald' : 'text-red-9'">{{ progreso.tiene_medico ? 'VIGENTE Y CERTIFICADO' : 'VENCIDO O NO REPORTADO' }}</span> · CONDICIÓN OBLIGATORIA PARA VUELO
              </div>
           </div>
        </div>
      </q-card>

    </template>

    <!-- ══ DIÁLOGO: VERIFICADOR UAEAC ══ -->
    <q-dialog v-model="dialogVerificacion" backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top" style="width:min(500px, 95vw);">
        <div class="row items-center justify-between q-mb-xl">
           <div class="row items-center">
              <q-icon name="fact_check" color="red-9" size="32px" class="q-mr-md" />
              <div class="text-h5 text-white font-head text-weight-bolder">Verificador de Competencia</div>
           </div>
           <q-btn flat round dense icon="close" v-close-popup color="grey-6" />
        </div>

        <div v-if="resultadoVerificacion">
           <div class="text-center q-mb-xl">
              <q-icon :name="resultadoVerificacion.aprobado?'check_circle':'pending_actions'" :color="resultadoVerificacion.aprobado?'emerald':'orange-9'" size="56px" class="pulsate" />
              <div class="text-h5 text-white font-head text-weight-bold q-mt-md">
                 {{ resultadoVerificacion.aprobado ? '¡REQUISITOS COMPLETADOS!' : 'REQUISITOS PENDIENTES' }}
              </div>
           </div>
           
           <div class="q-gutter-y-md">
              <div v-for="v in resultadoVerificacion.validaciones" :key="v.criterio" class="premium-glass-card q-pa-md border-red-low shadow-inner">
                 <div class="row items-center q-gutter-x-md">
                    <q-icon :name="v.cumplido?'verified':'sync_problem'" :color="v.cumplido?'emerald':'red-9'" size="20px" />
                    <div class="col" style="font-size:13px; color:#cbd5e1">{{ v.mensaje }}</div>
                 </div>
              </div>
           </div>
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useAuthStore } from 'store/auth'

const authStore = useAuthStore()
const progreso  = ref(null)
const cargando  = ref(false)
const verificando = ref(false)
const dialogVerificacion = ref(false)
const resultadoVerificacion = ref(null)

const categorias = computed(() => {
  const cats = progreso.value?.categorias || {}
  return Object.entries(cats).filter(([,v]) => (v.requerido||0) > 0).map(([k, v]) => ({ key: k, ...v }))
})

const resumenHoras = computed(() => {
  const cats = progreso.value?.categorias || {}
  return [
    { label: 'TOTAL ARRT', valor: (cats.vuelo_total?.acumulado||0).toFixed(1), color: '#A10B13' },
    { label: 'DUAL DIES', valor: (cats.dual?.acumulado||0).toFixed(1), color: 'white' },
    { label: 'SOLO PIC', valor: (cats.solo?.acumulado||0).toFixed(1), color: '#8b5cf6' },
    { label: 'TIME NVG', valor: (cats.noche?.acumulado||0).toFixed(1), color: '#f97316' },
    { label: 'INST IFR', valor: (cats.ifr?.acumulado||0).toFixed(1), color: '#A10B13' },
    { label: 'MISSIONS', valor: progreso.value?.total_vuelos || 0, color: '#10b981' },
  ]
})

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/dashboard')
    progreso.value = data.data?.progreso_horas
  } finally { cargando.value = false }
}

async function verificarExamen() {
  verificando.value = true
  try {
    const estudianteId = authStore.usuario?.id
    const { data } = await api.get(`/estudiantes/${estudianteId}/validar-examen`)
    resultadoVerificacion.value = data.data; dialogVerificacion.value = true
  } finally { verificando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.shadow-inner { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }

.pulse-red { animation: pulseRed 2s infinite; }
@keyframes pulseRed { 0%, 100% { border-color: rgba(161, 11, 19, 0.4); } 50% { border-color: rgba(161, 11, 19, 0.8); } }

.last-no-margin:last-child { margin-bottom: 0 !important; }
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
