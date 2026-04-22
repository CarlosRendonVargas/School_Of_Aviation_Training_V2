<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Reporte ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-btn flat round dense icon="arrow_back" color="red-9" @click="$router.push('/sms')" class="q-mr-md shadow-24" />
        <div>
          <div class="rac-page-subtitle">SISTEMA GESTIÓN SEGURIDAD OPERACIONAL · OACI ANEXO 19</div>
          <h1 class="rac-page-title">Nuevo Reporte de Seguridad</h1>
        </div>
      </div>
    </div>

    <!-- ══ Banner de Cultura de Reporte (Just Culture) ══ -->
    <q-card class="premium-glass-card q-mb-xl border-red-low shadow-24 overflow-hidden welcome-hero">
      <div class="hero-glow"></div>
      <q-card-section class="row items-center q-pa-lg relative-position">
        <q-icon name="privacy_tip" color="red-9" size="32px" class="q-mr-md glow-primary pulsate" />
        <div class="col">
           <div class="text-subtitle2 text-white font-head text-weight-bold">Política de No Represalias</div>
           <div class="text-caption text-grey-5 font-mono uppercase tracking-widest" style="font-size:10px">UAEAC RAC 141.205 · La identidad del informante se protege para fomentar la seguridad operacional.</div>
        </div>
      </q-card-section>
    </q-card>

    <q-form @submit.prevent="enviar">
      <div class="row q-col-gutter-xl">

        <!-- ─── Columna 1: Datos Técnicos del Hallazgo ─────────────────────────── -->
        <div class="col-12 col-lg-7">
          <q-card class="premium-glass-card shadow-24 border-red-low h-full flex column" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
            <div class="text-h6 text-white font-head text-weight-bolder flex items-center border-bottom-border pb-md uppercase tracking-tighter" :class="$q.screen.lt.md ? 'q-mb-lg' : 'q-mb-xl'">
               <q-icon name="assignment_late" color="red-9" :size="$q.screen.lt.md ? '20px' : '24px'" class="q-mr-md" />
               Detalles del Evento
            </div>

            <div class="q-gutter-y-lg col">
                <!-- Toggle Anónimo de Lujo -->
                <div class="flex items-center justify-between q-pa-md border-red-low shadow-inner rounded-12 bg-black-20 q-mb-md">
                  <div>
                    <div class="text-white font-mono text-weight-bolder uppercase" style="font-size:12px">Reporte Anónimo</div>
                    <div class="text-grey-6 font-mono uppercase" style="font-size:9px">Certificar sin registro de identidad</div>
                  </div>
                  <q-toggle v-model="form.anonimo" color="red-9" dark />
                </div>

                <!-- Toggle Notificado UAEAC -->
                <div class="flex items-center justify-between q-pa-md border-red-low shadow-inner rounded-12 bg-black-20">
                  <div>
                    <div class="text-white font-mono text-weight-bolder uppercase" style="font-size:12px">Notificado a la UAEAC</div>
                    <div class="text-grey-6 font-mono uppercase" style="font-size:9px">Reporte obligatorio a la autoridad (OACI)</div>
                  </div>
                  <q-toggle v-model="form.notificado_uaeac" color="red-9" dark />
                </div>

                <q-select v-model="form.tipo" filled dark class="premium-input-login"
                  :options="opcionesTipo" emit-value map-options
                  label="CATEGORÍA DEL HALLAZGO" stack-label :rules="[v=>!!v||'Requerido']">
                  <template #prepend><q-icon :name="iconTipo" :color="colorTipo" /></template>
                </q-select>

                <div class="row q-col-gutter-lg">
                  <div class="col-12 col-md-7">
                    <q-input v-model="form.fecha_evento" type="datetime-local" filled dark class="premium-input-login"
                      label="FECHA Y HORA DEL SUCESO" stack-label :rules="[v=>!!v||'Requerido']">
                      <template #prepend><q-icon name="event" color="red-9" /></template>
                    </q-input>
                  </div>
                  <div class="col-12 col-md-5">
                    <q-select v-model="form.aeronave_id" filled dark class="premium-input-login"
                      :options="opcionesAeronaves" option-value="id" option-label="label"
                      emit-value map-options label="HK ASOCIADA (OPCIONAL)" stack-label clearable>
                      <template #prepend><q-icon name="airplane_ticket" color="red-9" /></template>
                    </q-select>
                  </div>
                </div>

                <q-input v-model="form.lugar" filled dark class="premium-input-login"
                  label="UBICACIÓN EXACTA" stack-label placeholder="Ej: Pista 23, Briefing Room, Rampa..."
                  :rules="[v=>!!v||'Requerido']">
                  <template #prepend><q-icon name="place" color="red-9" /></template>
                </q-input>

                <q-input v-model="form.descripcion" type="textarea" filled dark class="premium-input-login"
                  label="DESCRIPCIÓN TÉCNICA DETALLADA" stack-label rows="6"
                  placeholder="Relate hechos, factores contribuyentes y posibles consecuencias..."
                  :rules="[v=>v&&v.length>=20||'Mínimo 20 caracteres']" counter maxlength="2000" />
            </div>
          </q-card>
        </div>

        <!-- ─── Columna 2: Evaluación de Riesgo UAEAC ────────────────────────── -->
        <div class="col-12 col-lg-5">
          <q-card class="premium-glass-card shadow-24 border-red-low" :class="[$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl', $q.screen.gt.md ? 'sticky-card' : '']">
            <div class="text-h6 text-white font-head text-weight-bolder flex items-center border-bottom-border pb-md uppercase tracking-tighter" :class="$q.screen.lt.md ? 'q-mb-lg' : 'q-mb-xl'">
               <q-icon name="query_stats" color="red-9" :size="$q.screen.lt.md ? '20px' : '24px'" class="q-mr-md" />
               Riesgo OACI
            </div>

            <!-- Severidad Selector de Cristal -->
            <div class="q-mb-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-md" style="font-size:10px">Severidad del Daño (S)</div>
              <div class="row q-col-gutter-sm">
                <div v-for="s in 5" :key="s" class="col">
                  <q-btn flat class="full-width font-mono text-weight-bolder"
                    :class="form.severidad===s ? 'btn-active-red' : 'btn-inactive'"
                    @click="form.severidad=s" :label="s" />
                </div>
              </div>
            </div>

            <!-- Probabilidad Selector de Cristal -->
            <div class="q-mb-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-md" style="font-size:10px">Probabilidad de Ocurrencia (P)</div>
              <div class="row q-col-gutter-sm">
                <div v-for="p in 5" :key="p" class="col">
                  <q-btn flat class="full-width font-mono text-weight-bolder"
                    :class="form.probabilidad===p ? 'btn-active-red' : 'btn-inactive'"
                    @click="form.probabilidad=p" :label="p" />
                </div>
              </div>
            </div>

            <!-- Dashboard de Riesgo Dinámico -->
             <div v-if="nivelRiesgo > 0" class="risk-result-vault shadow-24 welcome-hero overflow-hidden" :style="`border-color: ${borderResultado}`" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
                <div class="hero-glow"></div>
                <div class="text-center relative-position">
                   <div class="text-caption font-mono uppercase tracking-widest q-mb-sm" :style="`color: ${colorResultado}`">Riesgo Calculado</div>
                   <div class="font-mono text-weight-bolder line-height-1" :style="`color: ${colorResultado}; text-shadow: 0 0 20px ${borderResultado}; font-size: ${$q.screen.lt.md ? '60px' : '88px'}`">{{ nivelRiesgo }}</div>
                   <div class="text-weight-bolder uppercase tracking-tighter q-mt-md" :class="$q.screen.lt.md ? 'text-subtitle1' : 'text-h6'" :style="`color: ${colorResultado}`">{{ clasificacionRiesgo }}</div>
                  
                  <div v-if="nivelRiesgo >= 15" class="q-mt-lg q-pa-md border-red-low shadow-inner rounded-8 bg-red-10 animate-pulse">
                    <div class="text-white font-mono text-weight-bolder" style="font-size:11px">⚠️ REPORTE OBLIGATORIO UAEAC</div>
                  </div>
               </div>
            </div>

            <div v-else class="q-pa-xl text-center border-red-low shadow-inner rounded-12 bg-black-20">
               <q-icon name="ads_click" color="grey-7" size="48px" class="q-mb-md opacity-20" />
               <div class="text-grey-7 font-mono uppercase tracking-widest" style="font-size:10px">Defina Parámetros S y P para Evaluar</div>
            </div>

            <div class="q-mt-xl">
               <q-btn unelevated color="red-10" label="Certificar Reporte SMS" icon="verified"
                  type="submit" :loading="enviando" class="full-width premium-btn q-py-lg shadow-24"
                  :disable="!formValido" />
               <div class="text-center q-mt-md text-grey-7 font-mono uppercase" style="font-size:9px">La información será analizada por el Depto. de Seguridad</div>
            </div>
          </q-card>
        </div>

      </div>
    </q-form>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const router  = useRouter()
const $q      = useQuasar()
const enviando = ref(false)
const aeronaves = ref([])

const form = ref({ anonimo: false, notificado_uaeac: false, tipo: null, fecha_evento: '', lugar: '', aeronave_id: null, descripcion: '', severidad: 0, probabilidad: 0 })

const opcionesTipo = [
  { label: '⚠️ PELIGRO IDENTIFICADO', value: 'peligro' },
  { label: '📋 INCIDENTE OPERACIONAL', value: 'incidente' },
  { label: '🚨 ACCIDENTE AERONÁUTICO', value: 'accidente' },
  { label: '😰 NEAR MISS / PROXIMIDAD', value: 'near_miss' },
]

const opcionesAeronaves = computed(() => aeronaves.value.map(a => ({ id: a.id, label: `${a.matricula} · ${a.modelo}` })))
const nivelRiesgo = computed(() => form.value.severidad * form.value.probabilidad)

const clasificacionRiesgo = computed(() => {
  const n = nivelRiesgo.value; if (n >= 10) return 'RIESGO INACEPTABLE'; if (n >= 5) return 'RIESGO TOLERABLE'; if (n >= 1) return 'RIESGO ACEPTABLE'; return ''
})

const colorResultado = computed(() => {
  const n = nivelRiesgo.value; if (n >= 10) return '#ff4444'; if (n >= 5) return '#f59e0b'; return '#10b981'
})

const borderResultado = computed(() => {
  const n = nivelRiesgo.value; if (n >= 10) return 'rgba(255, 68, 68, 0.4)'; if (n >= 5) return 'rgba(245, 158, 11, 0.4)'; return 'rgba(16, 185, 129, 0.3)'
})

const iconTipo  = computed(() => ({ peligro:'warning', incidente:'report_problem', accidente:'emergency_share', near_miss:'visibility' }[form.value.tipo] || 'flag'))
const colorTipo = computed(() => ({ peligro:'orange-10', incidente:'red-9', accidente:'red-10', near_miss:'purple-10' }[form.value.tipo] || 'grey-6'))

const formValido = computed(() => form.value.tipo && form.value.fecha_evento && form.value.lugar && form.value.descripcion?.length >= 20 && form.value.severidad > 0 && form.value.probabilidad > 0)

async function enviar() {
  enviando.value = true
  try {
    const { data } = await api.post('/sms/reportes', form.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: `Reporte certificado. Categoría: ${data.nivel?.clasificacion || 'Registrada'}`, timeout: 5000 })
    router.push('/sms')
  } catch (e) { $q.notify({ color: 'negative', icon: 'error', message: 'Error al certificar el reporte SMS.' }) }
  finally { enviando.value = false }
}

onMounted(async () => {
  try { const { data } = await api.get('/aeronaves'); aeronaves.value = data.data || [] } catch (e) {}
})
</script>

<style lang="scss" scoped>

.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.bg-black-20 { background: rgba(0,0,0,0.2); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}

.btn-active-red { background: rgba(161, 11, 19, 0.2) !important; border: 1px solid #A10B13 !important; color: #A10B13 !important; }
.btn-inactive { background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.05) !important; color: #64748b !important; }

.risk-result-vault { border-radius: 20px; border: 2px solid; background: rgba(0,0,0,0.3); }

.sticky-card { position: sticky; top: 20px; }
.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.animate-pulse { animation: pulseAlert 1.5s infinite; }
@keyframes pulseAlert { 0%, 100% { transform: scale(1); } 50% { transform: scale(0.98); } }
</style>
