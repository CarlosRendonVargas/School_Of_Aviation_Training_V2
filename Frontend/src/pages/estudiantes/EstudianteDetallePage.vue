<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <q-skeleton v-if="cargando" type="rect" height="400px" dark class="premium-glass-card" />

    <template v-else-if="expediente">

      <!-- ══ Cabecera de Expediente Premium ══ -->
      <q-card class="premium-glass-card q-pa-lg q-pa-md-xl q-mb-xl border-red-left shadow-24 welcome-hero overflow-hidden">
        <div class="hero-glow"></div>
        <div class="row items-center q-gutter-lg relative-position flex-wrap">
          <div class="cadet-avatar-container shadow-24 flex-shrink-0">
             <div class="font-head text-white text-weight-bolder cadet-initials">{{ iniciales }}</div>
          </div>
          
          <div class="col min-w-0">
            <div class="font-head text-white text-weight-bolder uppercase tracking-tighter line-height-1 cadet-name">
              {{ expediente.estudiante.persona?.nombres }} {{ expediente.estudiante.persona?.apellidos }}
            </div>
            <div class="row items-center q-gutter-sm q-mt-sm flex-wrap">
              <div class="font-mono text-grey-6 uppercase tracking-widest" style="font-size:10px">EXP Nº {{ expediente.estudiante.num_expediente }}</div>
              <q-badge :color="colorEstado" :label="expediente.estudiante.estado?.toUpperCase()" class="font-mono text-weight-bold" />
              <q-badge outline color="red-9" :label="expediente.estudiante.programa?.nombre" class="font-mono text-weight-bold" />
            </div>
          </div>

          <q-btn flat round dense icon="arrow_back" color="grey-6" @click="$router.push('/academico?tab=estudiantes')" class="shadow-24 flex-shrink-0" />
        </div>
      </q-card>

      <!-- ══ Navegación de Expediente de Cristal ══ -->
      <q-card class="premium-glass-card shadow-24 overflow-hidden q-mb-xl">
        <q-tabs
          v-model="tab"
          dense
          class="text-grey-5"
          active-color="red-9"
          indicator-color="red-9"
          align="left"
          no-caps
          style="border-bottom: 1px solid rgba(255,255,255,0.05)"
          :narrow-indicator="true"
        >
          <q-tab name="resumen" icon="dns"               :label="$q.screen.gt.xs ? 'Resumen' : ''" />
          <q-tab name="horas"   icon="query_stats"       :label="$q.screen.gt.xs ? 'Horas RAC' : ''" />
          <q-tab name="notas"   icon="history_edu"       :label="$q.screen.gt.xs ? 'Académico' : ''" />
          <q-tab name="medico"  icon="medical_information" :label="$q.screen.gt.xs ? 'Salud' : ''" />
          <q-tab name="bitacoras" icon="auto_stories"   :label="$q.screen.gt.xs ? 'Bitácoras' : ''" />
        </q-tabs>

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
          
          <!-- ════ SECCIÓN: RESUMEN OPERATIVO ════ -->
          <q-tab-panel name="resumen" class="q-pa-xl">
            <div class="row q-col-gutter-xl">
              <!-- Info Personal -->
              <div class="col-12 col-md-4">
                <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-lg">Identificación de Cadete</div>
                <div class="q-gutter-y-sm">
                   <div v-for="campo in datosPersonales" :key="campo.label" 
                      class="premium-glass-card q-pa-md flex items-center justify-between border-red-low">
                      <span class="text-grey-6 font-mono uppercase" style="font-size:10px">{{ campo.label }}</span>
                      <span class="text-white text-weight-bold font-mono">{{ campo.valor || '---' }}</span>
                   </div>
                </div>
              </div>

              <!-- Monitor de Progreso -->
              <div class="col-12 col-md-8">
                 <div class="row items-center justify-between q-mb-lg">
                    <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest">Estatus de Formación UAEAC</div>
                    <q-badge v-if="expediente.validacion_examen?.aprobado" color="emerald" label="LISTO PARA EXAMEN" class="font-mono text-weight-bold q-px-lg shadow-24" />
                    <q-badge v-else color="red-9" label="EN ENTRENAMIENTO" class="font-mono text-weight-bold q-px-lg shadow-24" />
                 </div>

                 <div class="row q-col-gutter-lg q-mb-xl">
                    <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
                       <div class="premium-glass-card q-pa-md text-center border-red-low shadow-inner">
                          <div class="text-h6 text-red-9 text-weight-bolder font-mono">{{ h.valor }}</div>
                          <div class="text-caption text-grey-7 font-mono uppercase q-mt-xs" style="font-size:9px">{{ h.label }}</div>
                       </div>
                    </div>
                 </div>

                 <div class="q-mb-md flex justify-between items-end">
                    <span class="text-grey-4 font-mono uppercase" style="font-size:11px">Cumplimiento Global del Syllabus</span>
                    <span class="font-mono text-h6 text-red-9 text-weight-bolder">{{ porcentajeGeneral }}%</span>
                 </div>
                 <q-linear-progress :value="porcentajeGeneral/100" color="red-9" size="10px" rounded track-color="white-1" class="shadow-24" />
              </div>
            </div>
          </q-tab-panel>

          <!-- ════ SECCIÓN: CONTABILIDAD RAC ════ -->
          <q-tab-panel name="horas" class="q-pa-xl">
             <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Requisitos de Experiencia Reciente (RAC 61)</div>
             <div class="row q-col-gutter-lg">
                <div v-for="cat in categoriasHoras" :key="cat.key" class="col-12 col-md-6 q-mb-md">
                   <div class="premium-glass-card q-pa-lg border-red-low shadow-inner">
                      <div class="row items-center justify-between q-mb-md">
                         <div>
                            <div class="text-subtitle1 text-white font-head text-weight-bold">{{ cat.label }}</div>
                            <div class="text-caption text-grey-6 font-mono">{{ cat.rac }}</div>
                         </div>
                         <div class="text-right">
                            <div class="text-h6 text-red-9 font-mono text-weight-bolder">{{ Number(cat.acumulado || 0).toFixed(1) }}H</div>
                            <div class="text-caption text-grey-6 font-mono">REQ: {{ Number(cat.requerido || 0).toFixed(1) }}H</div>
                         </div>
                      </div>
                      <q-linear-progress :value="Math.min((cat.porcentaje||0)/100, 1)" :color="cat.cumplido?'emerald':'red-9'" size="6px" rounded track-color="white-1" />
                      <div class="row justify-between q-mt-sm">
                         <span v-if="!cat.cumplido" class="text-caption text-red-10 font-mono text-weight-bold">FALTAN {{ cat.faltante?.toFixed(1) }}H</span>
                         <span v-else class="text-caption text-emerald font-mono text-weight-bold">CERTIFICADO REGULATORIAMENTE</span>
                         <span class="font-mono text-grey-6" style="font-size:10px">{{ cat.porcentaje?.toFixed(0) }}%</span>
                      </div>
                   </div>
                </div>
             </div>
          </q-tab-panel>

          <!-- ════ SECCIÓN: DOSSIER ACADÉMICO ════ -->
          <q-tab-panel name="notas" class="q-pa-xl">
             <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Historial de Calificaciones Teóricas</div>
             <q-table :rows="expediente.estudiante.notas || []" :columns="columnasNotas" flat dark class="rac-table shadow-24">
                <template #body-cell-nota="props">
                   <q-td :props="props" class="text-center">
                      <span class="font-mono text-weight-bolder" :class="props.row.aprobado?'text-emerald':'text-red-9'" style="font-size:16px">
                         {{ Number(props.row.nota || 0).toFixed(1) }}
                      </span>
                   </q-td>
                </template>
                <template #body-cell-aprobado="props">
                   <q-td :props="props" class="text-center">
                      <q-icon :name="props.row.aprobado?'check_circle':'cancel'" :color="props.row.aprobado?'emerald':'red-9'" size="22px" />
                   </q-td>
                </template>
             </q-table>
          </q-tab-panel>

          <!-- ════ SECCIÓN: SALUD RAC 67 ════ -->
          <q-tab-panel name="medico" class="q-pa-xl">
             <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Certificados Aeromédicos Vigentes</div>
             <div class="row q-col-gutter-xl">
                <div class="col-12 col-md-6" v-for="cm in expediente.estudiante.cert_medicos || []" :key="cm.id">
                   <q-card class="premium-glass-card q-pa-xl border-red-low shadow-24" :class="!esMedicoVigente(cm) ? 'border-red-glow pulse-red' : ''">
                      <div class="row items-start justify-between q-mb-lg">
                         <div>
                            <q-badge color="red-9" :label="cm.tipo.toUpperCase().replace('_', ' ')" class="font-mono q-mb-sm" />
                            <div class="text-h5 text-white font-head text-weight-bolder">CERT. Nº {{ cm.numero_certificado }}</div>
                         </div>
                         <q-icon name="health_and_safety" :color="esMedicoVigente(cm) ? 'emerald' : 'red-9'" size="42px" />
                      </div>
                      <q-separator dark class="q-my-lg opacity-10" />
                      <div class="row q-col-gutter-lg">
                         <div class="col-6">
                            <div class="text-caption text-grey-7 font-mono uppercase">EMISIÓN</div>
                            <div class="text-white font-mono text-weight-bold">{{ dayjs(cm.fecha_emision).format('DD/MM/YYYY') }}</div>
                         </div>
                         <div class="col-6">
                            <div class="text-caption text-grey-7 font-mono uppercase text-right">VENCIMIENTO</div>
                            <div class="text-right font-mono text-weight-bold" :class="esMedicoVigente(cm)?'text-emerald':'text-red-9'">{{ dayjs(cm.fecha_vencimiento).format('DD/MM/YYYY') }}</div>
                         </div>
                      </div>
                   </q-card>
                </div>
                <div v-if="!expediente.estudiante.cert_medicos?.length" class="col-12 text-center q-pa-xl premium-glass-card border-red-low">
                   <q-icon name="error_outline" size="64px" color="red-9" class="q-mb-md" />
                   <div class="text-h6 text-grey-6 font-head uppercase tracking-widest">ALERTA: SIN CERTIFICADO MÉDICO RAC 67 REGISTRADO</div>
                </div>
             </div>
          </q-tab-panel>

           <!-- ════ SECCIÓN: BITÁCORAS DE VUELO ════ -->
           <q-tab-panel name="bitacoras" class="q-pa-xl">
              <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Registro de Misiones (Bitácora Digital)</div>
              <q-table 
                 :rows="expediente.estudiante.vuelos || []" 
                 :columns="columnasBitacora" 
                 flat dark 
                 class="rac-table shadow-24 border-red-low"
                 row-key="id"
              >
                 <template #body-cell-horas="props">
                    <q-td :props="props" class="text-right font-mono text-red-9 text-weight-bolder">
                       {{ Number(props.value || 0).toFixed(1) }}H
                    </q-td>
                 </template>
                 <template #body-cell-fecha="props">
                    <q-td :props="props" class="font-mono text-grey-5">{{ props.value }}</q-td>
                 </template>
              </q-table>
           </q-tab-panel>

        </q-tab-panels>
      </q-card>
    </template>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const $q = useQuasar()
const route = useRoute()
const cargando  = ref(false)
const expediente = ref(null)
const tab       = ref('resumen')

const iniciales = computed(() => {
  const e = expediente.value?.estudiante; if (!e) return '--'
  return ((e.persona?.nombres?.[0] || '') + (e.persona?.apellidos?.[0] || '')).toUpperCase()
})

const colorEstado = computed(() => ({ activo:'emerald', suspendido:'orange-9', graduado:'blue-9', retirado:'grey-8' }[expediente.value?.estudiante?.estado] || 'grey-8'))

const datosPersonales = computed(() => {
  const p = expediente.value?.estudiante?.persona; if (!p) return []
  return [ { label: 'Identificación', valor: `${p.tipo_documento} ${p.num_documento}` }, { label: 'F. Nacimiento', valor: p.fecha_nacimiento }, { label: 'Teléfono Fijo/Cel', valor: p.telefono || 'No reg.' }, { label: 'Ciudad Base', valor: p.ciudad }, { label: 'Fecha Admisión', valor: expediente.value?.estudiante?.fecha_ingreso } ]
})

const categoriasHoras = computed(() => {
  const cats = expediente.value?.progreso?.categorias || {}; return Object.entries(cats).filter(([,v]) => (v.requerido||0) > 0).map(([k, v]) => ({ key: k, ...v }))
})

const resumenHoras = computed(() => {
  const cats = categoriasHoras.value
  const findH = (k) => cats.find(c => c.key === k)?.acumulado || 0
  return [ { label: 'TOTAL', valor: findH('vuelo_total').toFixed(1) }, { label: 'DUAL', valor: findH('dual').toFixed(1) }, { label: 'SOLO', valor: findH('solo').toFixed(1) }, { label: 'NOCHE', valor: findH('noche').toFixed(1) }, { label: 'IFR', valor: findH('ifr').toFixed(1) }, { label: 'FLTS', valor: expediente.value?.progreso?.total_vuelos || 0 } ]
})

const porcentajeGeneral = computed(() => {
  const cats = categoriasHoras.value.filter(c => c.requerido > 0)
  if (!cats.length) return 0
  return Math.round(cats.reduce((a, c) => a + Math.min(c.porcentaje || 0, 100), 0) / cats.length)
})

const columnasNotas = [
  { name: 'materia', label: 'ASIGNATURA TEÓRICA', field: row => row.materia?.nombre, align: 'left' },
  { name: 'nota', label: 'EVAL.', field: 'nota', align: 'center' },
  { name: 'aprobado', label: 'RESULTADO', field: 'aprobado', align: 'center' },
  { name: 'intento', label: 'INT.', field: 'intento_num', align: 'center' },
  { name: 'fecha', label: 'FECHA EXAMEN', field: row => row.fecha_evaluacion ? dayjs(row.fecha_evaluacion).format('DD/MM/YYYY') : 'N/A', align: 'right' },
]

const columnasBitacora = [
  { name: 'fecha', label: 'FECHA', field: row => row.fecha ? dayjs(row.fecha).format('DD/MM/YYYY') : 'N/A', align: 'left' },
  { name: 'aeronave', label: 'HK', field: row => row.aeronave?.matricula, align: 'center' },
  { name: 'mision', label: 'MISIÓN', field: row => row.tipo_vuelo || 'Instrucción', align: 'left' },
  { name: 'horas', label: 'HORAS', field: 'horas_totales', align: 'right' },
  { name: 'instructor', label: 'INSTRUCTOR', field: row => row.instructor?.persona?.apellidos, align: 'right' },
]

const esMedicoVigente = (cm) => dayjs(cm.fecha_vencimiento).isAfter(dayjs())

async function cargar() {
  cargando.value = true
  try { const { data } = await api.get(`/estudiantes/${route.params.id}/expediente`); expediente.value = data.data } 
  finally { cargando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low  { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-left { border-left: 4px solid #A10B13 !important; }
.border-red-top  { border-top: 4px solid #A10B13 !important; }
.border-red-glow { border: 1px solid rgba(161, 11, 19, 0.4) !important; box-shadow: 0 0 20px rgba(161, 11, 19, 0.1); }
.shadow-inner    { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }
.text-emerald    { color: #10b981; }
.bg-emerald      { background: #10b981 !important; }
.line-height-1   { line-height: 1.1; }
.flex-shrink-0   { flex-shrink: 0; }
.min-w-0         { min-width: 0; }

// Responsive cadet name
.cadet-name {
  font-size: 2.2rem;
  @media (max-width: 599px) { font-size: 1.4rem !important; word-break: break-word; }
}
.cadet-initials {
  font-size: 28px;
  @media (max-width: 599px) { font-size: 20px; }
}

// Avatar container
.cadet-avatar-container {
  width: 90px; height: 90px; background: rgba(161, 11, 19, 0.1);
  border: 1px solid rgba(161, 11, 19, 0.25); border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  @media (max-width: 599px) { width: 64px; height: 64px; border-radius: 16px; }
}

.pulse-red { animation: pulseRed 2s infinite; }
@keyframes pulseRed { 0%, 100% { border-color: rgba(161, 11, 19, 0.4); } 50% { border-color: rgba(161, 11, 19, 0.8); } }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.1) 0%, transparent 50%); pointer-events:none; }

.last-no-margin:last-child { margin-bottom: 0 !important; }
</style>
