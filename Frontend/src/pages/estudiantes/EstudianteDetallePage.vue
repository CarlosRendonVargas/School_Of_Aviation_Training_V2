<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <q-skeleton v-if="cargando" type="rect" height="400px" dark class="premium-glass-card" />

    <template v-else-if="expediente">

      <!-- ══ Cabecera de Expediente Premium ══ -->
      <q-card class="premium-glass-card q-pa-lg q-pa-md-xl q-mb-xl border-red-left shadow-24 welcome-hero overflow-hidden">
        <div class="hero-glow"></div>
        <div class="row items-center q-gutter-lg relative-position flex-wrap">
          <div class="cadet-avatar-container shadow-24 flex-shrink-0">
             <img v-if="expediente.estudiante.persona?.foto_url" :src="expediente.estudiante.persona.foto_url" style="width:100%;height:100%;object-fit:cover;border-radius:24px" />
             <div v-else class="font-head text-white text-weight-bolder cadet-initials">{{ iniciales }}</div>
          </div>
          
          <div class="col min-w-0">
            <div class="font-head text-white text-weight-bolder uppercase tracking-tighter line-height-1 cadet-name">
              {{ expediente.estudiante.persona?.nombres }} {{ expediente.estudiante.persona?.apellidos }}
            </div>
            <div class="row items-center q-gutter-sm q-mt-sm flex-wrap">
              <div class="rac-page-subtitle">EXP Nº {{ expediente.estudiante.num_expediente }}</div>
              <q-badge :color="colorEstado" :label="expediente.estudiante.estado?.toUpperCase()" class="font-mono text-weight-bold" />
              <q-badge outline color="red-9" :label="expediente.estudiante.programa?.nombre" class="font-mono text-weight-bold" />
            </div>
          </div>

          <div class="row q-gutter-sm flex-shrink-0">
            <q-btn v-if="puedeEditar" unelevated color="red-9" icon="edit" label="Editar" no-caps
              class="premium-btn font-mono shadow-24" @click="abrirEditar" />
            <q-btn flat round dense icon="arrow_back" color="grey-6"
              @click="$router.push('/academico?tab=estudiantes')" class="shadow-24" />
          </div>
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
                <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-lg">Identificación de Estudiante</div>
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
                    <div class="row q-gutter-sm">
                      <q-badge v-if="expediente.estudiante.etapa_actual" outline color="blue-9" :label="expediente.estudiante.etapa_actual.nombre" class="font-mono text-weight-bold q-px-md shadow-24" />
                      <q-badge v-if="expediente.validacion_examen?.aprobado" color="emerald" label="LISTO PARA EXAMEN" class="font-mono text-weight-bold q-px-lg shadow-24" />
                      <q-badge v-else color="red-9" label="EN ENTRENAMIENTO" class="font-mono text-weight-bold q-px-lg shadow-24" />
                    </div>
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
                 <q-linear-progress :value="porcentajeGeneral/100" color="red-9" size="10px" rounded track-color="white-1" class="shadow-24 q-mb-xl" />

                 <div v-if="expediente.estudiante.observaciones" class="premium-glass-card q-pa-md border-red-low shadow-inner">
                    <div class="text-caption text-grey-6 font-mono uppercase q-mb-xs" style="font-size:9px">Observaciones y Anotaciones</div>
                    <div class="text-white font-mono" style="font-size:12px">{{ expediente.estudiante.observaciones }}</div>
                 </div>
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
             <q-table 
                :rows="expediente.estudiante.notas || []" 
                :columns="columnasNotas" 
                flat dark 
                class="rac-table shadow-24"
                :grid="$q.screen.lt.md"
             >
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

                <!-- Modo Grid Móvil: Notas -->
                <template v-slot:item="props">
                  <div class="col-12 q-pa-xs">
                    <q-card class="bg-black-20 shadow-24 q-mb-sm p-0 border-red-low">
                      <q-card-section class="q-pa-md">
                        <div class="row items-center justify-between">
                          <span class="text-caption text-grey-5 font-mono uppercase">{{ props.row.materia?.codigo }}</span>
                          <q-icon :name="props.row.aprobado?'check_circle':'cancel'" :color="props.row.aprobado?'emerald':'red-9'" size="20px" />
                        </div>
                        <div class="text-white text-weight-bold font-head q-mt-sm" style="font-size:15px">{{ props.row.materia?.nombre }}</div>
                        <div class="row items-center justify-between q-mt-md border-top-border pt-sm">
                           <div>
                              <div class="text-grey-6 font-mono uppercase" style="font-size:8px">FECHA EVAL.</div>
                              <div class="text-white font-mono" style="font-size:11px">{{ formatFechaCO(props.row.fecha_evaluacion) }}</div>
                           </div>
                           <div class="text-right">
                              <div class="text-grey-6 font-mono uppercase" style="font-size:8px">RESULTADO</div>
                              <div class="text-h6 font-mono text-weight-bolder" :class="props.row.aprobado?'text-emerald':'text-red-9'">{{ Number(props.row.nota || 0).toFixed(1) }}</div>
                           </div>
                        </div>
                      </q-card-section>
                    </q-card>
                  </div>
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
                            <div class="text-white font-mono text-weight-bold">{{ formatFechaCO(cm.fecha_emision) }}</div>
                         </div>
                         <div class="col-6">
                            <div class="text-caption text-grey-7 font-mono uppercase text-right">VENCIMIENTO</div>
                            <div class="text-right font-mono text-weight-bold" :class="esMedicoVigente(cm)?'text-emerald':'text-red-9'">{{ dayjs(cm.fecha_vencimiento).format('DD/MM/YYYY') }}</div>
                         </div>
                      </div>
                      <q-separator dark class="q-my-md opacity-5" />
                      <div class="row q-col-gutter-md q-mb-sm">
                         <div class="col-12">
                            <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">CENTRO AEROMÉDICO</div>
                            <div class="text-white font-mono" style="font-size:12px">{{ cm.centro_aereomedico || 'No registrado' }}</div>
                         </div>
                         <div class="col-12">
                            <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">RESTRICCIONES</div>
                            <div class="text-white font-mono" style="font-size:12px">{{ cm.restricciones || 'Ninguna' }}</div>
                         </div>
                      </div>
                      <div class="q-mt-md" v-if="cm.archivo_url">
                         <q-btn flat no-caps dense icon="picture_as_pdf" color="red-9"
                            :href="cm.archivo_url" target="_blank"
                            label="Ver Certificado Digital" class="font-mono" />
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
                  :grid="$q.screen.lt.md"
               >
                  <template #body-cell-horas="props">
                     <q-td :props="props" class="text-right font-mono text-red-9 text-weight-bolder">
                        {{ Number(props.value || 0).toFixed(1) }}H
                     </q-td>
                  </template>
                  <template #body-cell-fecha="props">
                     <q-td :props="props" class="font-mono text-grey-5">{{ props.value }}</q-td>
                  </template>

                  <!-- Modo Grid Móvil: Bitácora -->
                  <template v-slot:item="props">
                    <div class="col-12 q-pa-xs">
                      <q-card class="bg-black-20 shadow-24 q-mb-sm p-0 border-red-low">
                        <q-card-section class="q-pa-md">
                           <div class="row items-center justify-between">
                              <span class="font-mono text-grey-5">{{ props.row.fecha ? dayjs(props.row.fecha).format('DD/MM/YYYY') : '---' }}</span>
                              <q-badge outline color="red-9" :label="props.row.aeronave?.matricula" class="font-mono text-weight-bolder" />
                           </div>
                           <div class="text-white font-head q-mt-md" style="font-size:15px">{{ props.row.tipo_vuelo || 'Instrucción' }}</div>
                           <div class="row items-center justify-between q-mt-md border-top-border pt-sm">
                              <div>
                                 <div class="text-grey-6 font-mono uppercase" style="font-size:8px">INSTRUCTOR</div>
                                 <div class="text-white font-head" style="font-size:12px">{{ props.row.instructor?.persona?.apellidos || 'N/A' }}</div>
                              </div>
                              <div class="text-right">
                                 <div class="text-grey-6 font-mono uppercase" style="font-size:8px">DURACIÓN</div>
                                 <div class="text-h6 text-red-9 font-mono text-weight-bolder">{{ Number(props.row.horas_totales || 0).toFixed(1) }}H</div>
                              </div>
                           </div>
                        </q-card-section>
                      </q-card>
                    </div>
                  </template>
               </q-table>

           </q-tab-panel>

        </q-tab-panels>
      </q-card>
    </template>

    <!-- ════ DIÁLOGO: EDITAR ESTUDIANTE ════ -->
    <q-dialog v-model="dlgEditar" persistent>
      <q-card style="width:min(700px,96vw)" class="premium-glass-card q-pa-xl border-red-low shadow-24 rounded-20">
        <q-card-section class="row items-center bg-red-10 text-white q-pa-lg q-mb-lg" style="border-radius:12px 12px 0 0; margin:-32px -32px 0 -32px">
          <q-icon name="edit" size="28px" class="q-mr-md" />
          <span class="text-h6 font-head text-weight-bolder uppercase">Editar Estudiante</span>
          <q-space />
          <q-btn flat round icon="close" v-close-popup color="white" />
        </q-card-section>

        <q-form @submit.prevent="guardarEditar" class="q-gutter-y-md q-mt-md">

          <div class="text-caption text-grey-5 font-mono uppercase q-mb-xs">Datos Personales</div>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.nombres" label="Nombres *" outlined dense dark />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.apellidos" label="Apellidos *" outlined dense dark />
            </div>
            <div class="col-6 col-sm-3">
              <q-select v-model="formEditar.tipo_documento"
                :options="['CC','CE','PA','PEP','TI']"
                label="Tipo Doc." outlined dense dark />
            </div>
            <div class="col-6 col-sm-4">
              <q-input v-model="formEditar.num_documento" label="Nº Documento" outlined dense dark />
            </div>
            <div class="col-12 col-sm-5">
              <q-input v-model="formEditar.fecha_nacimiento" type="date" label="F. Nacimiento" outlined dense dark />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.telefono" label="Teléfono" outlined dense dark />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.ciudad" label="Ciudad" outlined dense dark />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.nacionalidad" label="Nacionalidad" outlined dense dark />
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="formEditar.direccion" label="Dirección" outlined dense dark />
            </div>
          </div>

          <q-separator dark class="q-my-sm opacity-20" />
          <div class="text-caption text-grey-5 font-mono uppercase q-mb-xs">Datos de Matrícula</div>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-sm-4">
              <q-select v-model="formEditar.estado"
                :options="[{label:'Activo',value:'activo'},{label:'Suspendido',value:'suspendido'},{label:'Graduado',value:'graduado'},{label:'Retirado',value:'retirado'}]"
                label="Estado" outlined dense dark emit-value map-options />
            </div>
            <div class="col-12 col-sm-4">
              <q-input v-model="formEditar.fecha_ingreso" type="date" label="F. Ingreso" outlined dense dark />
            </div>
            <div class="col-12 col-sm-4">
              <q-select v-model="formEditar.programa_id"
                :options="programas" option-value="id" option-label="nombre"
                label="Programa" outlined dense dark emit-value map-options clearable />
            </div>
            <div class="col-12">
              <q-input v-model="formEditar.observaciones" label="Observaciones" type="textarea" rows="2" outlined dense dark />
            </div>
          </div>

          <div class="row justify-end q-gutter-sm q-mt-md">
            <q-btn flat label="Cancelar" v-close-popup />
            <q-btn unelevated color="red-10" icon="save" label="Guardar Cambios"
              type="submit" :loading="guardandoEditar" />
          </div>
        </q-form>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import { formatFechaCO } from 'src/utils/formatters'

const $q = useQuasar()
const route = useRoute()
const authStore = useAuthStore()
const cargando  = ref(false)
const expediente = ref(null)
const tab        = ref('resumen')
const dlgEditar  = ref(false)
const guardandoEditar = ref(false)
const formEditar = ref({})
const programas  = ref([])

const puedeEditar = computed(() => ['admin', 'dir_ops'].includes(authStore.rol))

const iniciales = computed(() => {
  const e = expediente.value?.estudiante; if (!e) return '--'
  return ((e.persona?.nombres?.[0] || '') + (e.persona?.apellidos?.[0] || '')).toUpperCase()
})

const colorEstado = computed(() => ({ activo:'emerald', suspendido:'orange-9', graduado:'blue-9', retirado:'grey-8' }[expediente.value?.estudiante?.estado] || 'grey-8'))

const datosPersonales = computed(() => {
  const p = expediente.value?.estudiante?.persona; if (!p) return []
  return [ 
    { label: 'Identificación', valor: `${p.tipo_documento} ${p.num_documento}` }, 
    { label: 'F. Nacimiento', valor: formatFechaCO(p.fecha_nacimiento) },
    { label: 'Nacionalidad', valor: p.nacionalidad || 'No reg.' },
    { label: 'Dirección', valor: p.direccion || 'No reg.' },
    { label: 'Teléfono Fijo/Cel', valor: p.telefono || 'No reg.' }, 
    { label: 'Ciudad Base', valor: p.ciudad }, 
    { label: 'Fecha Admisión', valor: formatFechaCO(expediente.value?.estudiante?.fecha_ingreso) }
  ]
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
  { name: 'fecha', label: 'FECHA EXAMEN', field: row => formatFechaCO(row.fecha_evaluacion), align: 'right' },
]

const columnasBitacora = [
  { name: 'fecha', label: 'FECHA', field: row => row.fecha ? dayjs(row.fecha).format('DD/MM/YYYY') : 'N/A', align: 'left' },
  { name: 'aeronave', label: 'HK', field: row => row.aeronave?.matricula, align: 'center' },
  { name: 'mision', label: 'MISIÓN', field: row => row.tipo_vuelo || 'Instrucción', align: 'left' },
  { name: 'horas', label: 'HORAS', field: 'horas_totales', align: 'right' },
  { name: 'instructor', label: 'INSTRUCTOR', field: row => row.instructor?.persona?.apellidos, align: 'right' },
]

const esMedicoVigente = (cm) => dayjs(cm.fecha_vencimiento).isAfter(dayjs())

function abrirEditar() {
  const e = expediente.value?.estudiante
  const p = e?.persona ?? {}
  formEditar.value = {
    nombres: p.nombres ?? '',
    apellidos: p.apellidos ?? '',
    tipo_documento: p.tipo_documento ?? 'CC',
    num_documento: p.num_documento ?? '',
    fecha_nacimiento: p.fecha_nacimiento ? String(p.fecha_nacimiento).substring(0, 10) : '',
    telefono: p.telefono ?? '',
    ciudad: p.ciudad ?? '',
    nacionalidad: p.nacionalidad ?? '',
    direccion: p.direccion ?? '',
    estado: e?.estado ?? 'activo',
    fecha_ingreso: e?.fecha_ingreso ? String(e.fecha_ingreso).substring(0, 10) : '',
    programa_id: e?.programa_id ?? null,
    observaciones: e?.observaciones ?? '',
  }
  dlgEditar.value = true
}

async function guardarEditar() {
  guardandoEditar.value = true
  try {
    await api.put(`/estudiantes/${route.params.id}`, formEditar.value)
    $q.notify({ type: 'positive', message: 'Datos del estudiante actualizados.' })
    dlgEditar.value = false
    cargar()
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message ?? 'Error al guardar.' })
  } finally { guardandoEditar.value = false }
}

async function cargar() {
  cargando.value = true
  try {
    const [expRes, progRes] = await Promise.allSettled([
      api.get(`/estudiantes/${route.params.id}/expediente`),
      api.get('/programas'),
    ])
    if (expRes.status === 'fulfilled') expediente.value = expRes.value.data.data
    if (progRes.status === 'fulfilled') {
      const d = progRes.value.data
      programas.value = d.data?.data ?? d.data ?? d ?? []
    }
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>

.shadow-inner    { box-shadow: inset 0 2px 10px rgba(0,0,0,0.5); }

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
