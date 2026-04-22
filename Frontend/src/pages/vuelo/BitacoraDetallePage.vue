<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <div class="row items-center justify-between q-mb-xl rac-page-header" v-if="bitacora">
      <div class="row items-center">
        <q-btn flat round dense icon="arrow_back" color="red-9" class="q-mr-md bg-black-20 hover-red" @click="$router.push('/vuelo')" />
        <q-icon name="flight_takeoff" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="rac-page-subtitle">MISIÓN DE VUELO RAC 61/141</div>
          <h1 class="rac-page-title">Bitácora #{{ bitacora.id.toString().padStart(4, '0') }}</h1>
        </div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn unelevated color="blue-9" icon="picture_as_pdf" label="Descargar PDF" @click="descargarPdf" class="premium-btn shadow-24 q-px-lg font-mono text-weight-bolder" />
        <q-btn v-if="!bitacora.firma_instructor" unelevated color="emerald" icon="draw" label="Firmar" @click="dialogFirma = true" class="premium-btn shadow-24 q-px-lg font-mono text-weight-bolder" />
      </div>
    </div>

    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-tail color="red-9" size="64px" />
    </div>

    <div v-else-if="bitacora" class="row q-col-gutter-lg">
      
      <!-- Panel de Detalles Generales -->
      <div class="col-12 col-md-8">
        <q-card class="premium-glass-card shadow-24 border-red-low rounded-20 h-full">
          <q-card-section class="q-pa-xl">
             <div class="row q-col-gutter-lg q-mb-xl">
               <!-- Estudiante e Instructor -->
               <div class="col-12 col-md-6">
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-xs">ALUMNO PIC</div>
                 <div class="text-h6 text-white font-head uppercase">{{ bitacora.estudiante?.persona?.nombres }} {{ bitacora.estudiante?.persona?.apellidos }}</div>
                 <div class="text-caption text-grey-5 font-mono">{{ bitacora.estudiante?.num_documento }}</div>
               </div>
               <div class="col-12 col-md-6">
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-xs">INSTRUCTOR</div>
                 <div class="text-h6 text-white font-head uppercase">{{ bitacora.instructor?.persona?.nombres }} {{ bitacora.instructor?.persona?.apellidos }}</div>
                 <div class="text-caption text-grey-5 font-mono">Licencia: {{ bitacora.instructor?.num_licencia }}</div>
               </div>

               <!-- Aeronave y Ruta -->
               <div class="col-12 col-md-6">
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-xs">AERONAVE / TIPO</div>
                 <div class="text-h6 text-red-9 font-head text-weight-bolder uppercase">{{ bitacora.aeronave?.matricula }} <span class="text-white text-subtitle1">({{ bitacora.tipo_vuelo }})</span></div>
               </div>
               <div class="col-12 col-md-6">
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-xs">RUTA / CONDICIONES</div>
                 <div class="text-h6 text-white font-head uppercase">{{ bitacora.origen_icao }} <q-icon name="arrow_forward" color="red-9" /> {{ bitacora.destino_icao }}</div>
                 <q-badge outline :color="bitacora.condiciones_vmc ? 'blue-9' : 'red-9'" class="q-mt-sm font-mono uppercase" :label="bitacora.condiciones_vmc ? 'VMC (Visual)' : 'IMC (Instrumentos)'" />
               </div>
             </div>

             <q-separator dark class="opacity-10 q-my-lg" />

             <!-- Tiempos -->
             <div class="text-subtitle1 text-white font-head uppercase q-mb-md text-red-9">Registro de Tiempos</div>
             <div class="row q-col-gutter-md">
               <div class="col-6 col-md-3">
                 <div class="bg-black-20 rounded-12 q-pa-md border-red-low text-center">
                   <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:10px">BLOQUE OUT</div>
                   <div class="text-h6 text-white font-mono">{{ bitacora.hora_salida_bloque || '--:--' }}</div>
                 </div>
               </div>
               <div class="col-6 col-md-3">
                 <div class="bg-black-20 rounded-12 q-pa-md border-red-low text-center">
                   <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:10px">DESPEGUE</div>
                   <div class="text-h6 text-white font-mono">{{ bitacora.hora_despegue || '--:--' }}</div>
                 </div>
               </div>
               <div class="col-6 col-md-3">
                 <div class="bg-black-20 rounded-12 q-pa-md border-red-low text-center">
                   <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:10px">ATERRIZAJE</div>
                   <div class="text-h6 text-white font-mono">{{ bitacora.hora_aterrizaje || '--:--' }}</div>
                 </div>
               </div>
               <div class="col-6 col-md-3">
                 <div class="bg-black-20 rounded-12 q-pa-md border-red-low text-center">
                   <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:10px">BLOQUE IN</div>
                   <div class="text-h6 text-white font-mono">{{ bitacora.hora_llegada_bloque || '--:--' }}</div>
                 </div>
               </div>
             </div>

             <!-- Observaciones -->
             <div class="q-mt-xl bg-black-20 rounded-12 q-pa-lg border-red-low">
                <div class="text-subtitle1 text-red-9 font-head uppercase q-mb-sm">Observaciones RAC 61</div>
                <div class="text-body2 text-grey-4 font-mono">{{ bitacora.observaciones || 'No hay observaciones técnicas.' }}</div>
             </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Panel Lateral (Tiempos de Vuelo y Firmas) -->
      <div class="col-12 col-md-4">
         <q-card class="premium-glass-card shadow-24 border-red-low rounded-20 q-mb-lg">
            <q-card-section class="q-pa-xl">
               <div class="text-h6 text-white font-head uppercase border-bottom-border pb-sm q-mb-md">Desglose de Horas</div>
               
               <div class="row justify-between items-center q-mb-sm">
                 <div class="text-caption text-grey-5 font-mono uppercase tracking-widest">DUAL</div>
                 <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(bitacora.horas_dual || 0).toFixed(1) }}H</div>
               </div>
               <div class="row justify-between items-center q-mb-sm">
                 <div class="text-caption text-grey-5 font-mono uppercase tracking-widest">SOLO</div>
                 <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(bitacora.horas_solo || 0).toFixed(1) }}H</div>
               </div>
               <div class="row justify-between items-center q-mb-sm">
                 <div class="text-caption text-grey-5 font-mono uppercase tracking-widest">NOCTURNO</div>
                 <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(bitacora.horas_noche || 0).toFixed(1) }}H</div>
               </div>
               <div class="row justify-between items-center q-mb-sm">
                 <div class="text-caption text-grey-5 font-mono uppercase tracking-widest">IFR (INSTR.)</div>
                 <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(bitacora.horas_ifr || 0).toFixed(1) }}H</div>
               </div>
               <div class="row justify-between items-center q-mb-md">
                 <div class="text-caption text-grey-5 font-mono uppercase tracking-widest">SIMULADOR</div>
                 <div class="text-subtitle1 text-white font-mono text-weight-bold">{{ Number(bitacora.horas_simulador || 0).toFixed(1) }}H</div>
               </div>
               
               <q-separator dark class="opacity-10 q-my-md" />

               <div class="row justify-between items-center">
                 <div class="text-subtitle2 text-red-9 font-head uppercase tracking-widest">TOTAL TIEMPO VUELO</div>
                 <div class="text-h5 text-white font-mono text-weight-bolder">{{ Number(bitacora.horas_totales || 0).toFixed(1) }}<span class="text-caption text-grey-6">H</span></div>
               </div>
               <div class="row justify-between items-center q-mt-sm">
                 <div class="text-subtitle2 text-grey-5 font-head uppercase tracking-widest">ATERRIZAJES</div>
                 <div class="text-h6 text-white font-mono text-weight-bold">{{ bitacora.aterrizajes || 0 }}</div>
               </div>
            </q-card-section>
         </q-card>

         <!-- Firmas -->
         <q-card class="premium-glass-card shadow-24 border-red-low rounded-20">
            <q-card-section class="q-pa-xl text-center">
               <div class="text-h6 text-white font-head uppercase border-bottom-border pb-sm q-mb-md text-left">Certificación</div>
               
               <div class="q-mb-md">
                 <q-icon :name="bitacora.firma_instructor ? 'verified' : 'pending'" :color="bitacora.firma_instructor ? 'emerald' : 'orange'" size="32px" class="q-mb-xs" />
                 <div class="text-caption text-grey-5 font-mono uppercase">Firma del Instructor</div>
                 <div class="text-subtitle2 text-white font-mono" v-if="bitacora.firma_instructor">{{ bitacora.instructor?.persona?.nombres }} {{ bitacora.instructor?.persona?.apellidos }}</div>
                 <div class="text-caption text-orange font-mono" v-else>PENDIENTE</div>
               </div>
               <q-separator dark class="opacity-10 q-my-md" />
               <div>
                 <q-icon :name="bitacora.firma_estudiante ? 'verified' : 'pending'" :color="bitacora.firma_estudiante ? 'emerald' : 'orange'" size="32px" class="q-mb-xs" />
                 <div class="text-caption text-grey-5 font-mono uppercase">Firma del Alumno</div>
                 <div class="text-subtitle2 text-white font-mono" v-if="bitacora.firma_estudiante">{{ bitacora.estudiante?.persona?.nombres }} {{ bitacora.estudiante?.persona?.apellidos }}</div>
                 <div class="text-caption text-orange font-mono" v-else>PENDIENTE</div>
               </div>
            </q-card-section>
         </q-card>
      </div>
    </div>

    <!-- DIÁLOGO PARA FIRMAR -->
    <q-dialog v-model="dialogFirma" persistent backdrop-filter="blur(15px)">
       <q-card class="premium-glass-card shadow-24 border-emerald-top rounded-20" style="width:min(400px, 95vw)">
         <div class="q-pa-xl text-center">
            <q-icon name="draw" color="emerald" size="64px" class="q-mb-md glow-emerald" />
            <div class="text-h5 text-white font-head uppercase text-weight-bolder">Firmar Bitácora</div>
            <div class="text-caption text-grey-5 font-mono q-mt-sm">Al firmar, certificas que los datos de vuelo registrados son verídicos bajo RAC 61.</div>
            
            <q-input v-model="pinFirma" type="password" label="PIN DE FIRMA ELECTRÓNICA" filled dark class="premium-input-login q-mt-xl text-center" />

            <div class="row q-col-gutter-md q-mt-lg">
              <div class="col-6"><q-btn flat class="full-width premium-btn" color="grey-6" label="Cancelar" @click="dialogFirma = false" /></div>
              <div class="col-6"><q-btn unelevated color="emerald" label="Firmar" class="full-width premium-btn text-weight-bolder" @click="firmarBitacora" :loading="firmando" /></div>
            </div>
         </div>
       </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const route = useRoute()
const $q = useQuasar()
const bitacora = ref(null)
const cargando = ref(true)
const dialogFirma = ref(false)
const pinFirma = ref('')
const firmando = ref(false)

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get(`/bitacoras/${route.params.id}`)
    bitacora.value = data.data || data
  } catch {
    $q.notify({ color: 'negative', message: 'Error al cargar detalle de bitácora.' })
  } finally {
    cargando.value = false
  }
}

async function firmarBitacora() {
  if (!pinFirma.value) return $q.notify({ color: 'warning', message: 'Ingresa tu PIN de firma.' })
  firmando.value = true
  try {
    await api.post(`/bitacoras/${bitacora.value.id}/firmar`, { pin: pinFirma.value })
    $q.notify({ color: 'emerald', message: 'Bitácora firmada digitalmente.' })
    dialogFirma.value = false
    cargar()
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'PIN inválido o error.' })
  } finally {
    firmando.value = false
  }
}

function descargarPdf() {
  const url = `${api.defaults.baseURL}/bitacoras/${bitacora.value.id}/pdf?token=${localStorage.getItem('token')}`
  window.open(url, '_blank')
}

onMounted(cargar)
</script>

<style lang="scss" scoped>
.premium-input-login :deep(input) { text-align: center; font-family: monospace; letter-spacing: 4px; font-size: 18px; }
.border-emerald-top { border-top: 4px solid #10b981; }
.glow-emerald { text-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
</style>
