<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado del Aula Virtual ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header" v-if="!materiaActiva">
      <div class="row items-center">
        <q-icon name="laptop_chromebook" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">EDUCACIÓN AERONÁUTICA COMPUTARIZADA · LMS · RAC 141</div>
          <h1 class="rac-page-title">Aula Virtual</h1>
        </div>
      </div>
    </div>

    <!-- ════ LISTA DE MATERIAS (HANGAR DE ESTUDIO) ════ -->
    <div class="row q-col-gutter-xl" v-if="!materiaActiva">
      <div v-for="m in materias" :key="m.id" class="col-12 col-md-4 col-lg-3 animate-slide-up">
        <q-card class="premium-glass-card hover-row full-height flex column overflow-hidden shadow-24 border-red-low">
          <div class="status-gradient-line" :class="m.aprobado ? 'bg-emerald' : 'bg-red-9'"></div>
          
          <q-card-section class="q-pa-xl">
            <div class="row items-center justify-between q-mb-lg">
                <q-badge outline color="red-9" class="font-mono text-weight-bold" :label="m.codigo" />
                <q-chip v-if="m.aprobado" color="emerald" text-color="white" icon="verified" size="xs" dense class="shadow-24">APROBADO</q-chip>
                <q-chip v-else color="red-9" text-color="white" icon="pending_actions" size="xs" dense class="shadow-24">EN CURSO</q-chip>
            </div>
            <div class="text-h6 text-white text-weight-bolder font-head q-mb-sm line-height-1">{{ m.nombre }}</div>
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ m.etapa || 'TEÓRICA' }}</div>
          </q-card-section>

          <q-card-section class="col q-px-xl">
             <div class="row q-col-gutter-lg text-center shadow-inner q-pa-md rounded-12 bg-black-20">
                 <div class="col-6">
                    <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">PLAN (H)</div>
                    <div class="text-h5 text-grey-3 font-mono text-weight-bolder">{{ m.horas }}</div>
                 </div>
                 <div class="col-6">
                    <div class="text-grey-7 font-mono uppercase q-mb-xs" style="font-size:9px">SCORE</div>
                    <div class="text-h5 font-mono text-weight-bolder" :class="m.nota_max >= 75 ? 'text-emerald' : 'text-red-9'">{{ m.nota_max || '0' }}%</div>
                 </div>
             </div>
          </q-card-section>

          <q-card-actions class="q-pa-xl">
            <q-btn unelevated color="red-9" label="Entrar a Módulo" class="full-width premium-btn" icon-right="chevron_right" @click="entrarAula(m)" />
          </q-card-actions>
        </q-card>
      </div>
    </div>

    <!-- ════ DETALLE DE MATERIA (CABINA DE INSTRUCCIÓN) ════ -->
    <div v-else class="animate-fade">
        <div class="row items-center justify-between q-mb-xl welcome-hero q-pa-xl premium-glass-card border-red-low shadow-24 overflow-hidden">
           <div class="hero-glow"></div>
           <div class="row items-center relative-position">
              <q-btn flat round icon="arrow_back" color="red-9" @click="materiaActiva = null" class="q-mr-xl shadow-24" />
              <div>
                 <div class="text-h3 text-white font-head text-weight-bolder uppercase tracking-tighter line-height-1">{{ materiaActiva.nombre }}</div>
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-sm">RECURSOS DE ENTRENAMIENTO Y EVALUACIÓN DE COMPETENCIA</div>
              </div>
           </div>
           <q-badge outline color="red-9" class="q-pa-md font-mono text-weight-bold shadow-24 relative-position">MODULO ID: {{ materiaActiva.codigo }}</q-badge>
        </div>

        <div class="row q-col-gutter-xl">
            <!-- Columna Izquierda: Recursos de Misión -->
            <div class="col-12 col-lg-8">
                <q-card class="premium-glass-card shadow-24 overflow-hidden border-red-low">
                    <q-tabs v-model="tabAula" dense active-color="red-9" indicator-color="red-9" align="justify" class="bg-black-20 text-grey-5 border-bottom">
                        <q-tab name="lecciones" label="Lecciones del Módulo" icon="video_settings" />
                        <q-tab name="temario" label="Syllabus Técnico" icon="description" />
                        <q-tab name="clase" label="Briefing en Vivo" icon="podcasts" />
                    </q-tabs>

                    <q-tab-panels v-model="tabAula" animated class="bg-transparent text-white">
                        
                        <!-- LECCIONES INTERACTIVAS -->
                        <q-tab-panel name="lecciones" class="q-pa-none">
                            <q-list dark separator class="shadow-inner">
                                <q-expansion-item
                                    v-for="lec in materiaActiva.lecciones" :key="lec.id"
                                    header-class="q-py-xl q-px-xl"
                                    group="lecciones"
                                    class="hover-row"
                                >
                                    <template v-slot:header>
                                       <q-item-section avatar>
                                          <q-icon :name="lec.nota_estudiante?.aprobado ? 'verified' : 'play_circle'" :color="lec.nota_estudiante?.aprobado ? 'emerald' : 'red-9'" size="38px" />
                                       </q-item-section>
                                       <q-item-section>
                                          <q-item-label class="text-h6 font-head text-weight-bolder">{{ lec.titulo }}</q-item-label>
                                          <q-item-label caption class="text-grey-7 font-mono uppercase" style="font-size:10px">{{ lec.descripcion || 'Sin descripción de módulo registrada' }}</q-item-label>
                                       </q-item-section>
                                       <q-item-section side v-if="lec.nota_estudiante">
                                          <div class="text-right">
                                             <div class="text-h6 font-mono text-weight-bolder" :class="lec.nota_estudiante.aprobado ? 'text-emerald' : 'text-red-9'">{{ Number(lec.nota_estudiante.nota) }}%</div>
                                             <div class="text-grey-7 font-mono uppercase" style="font-size:9px">QUIZ OK</div>
                                          </div>
                                       </q-item-section>
                                    </template>

                                    <q-card class="bg-black-30 border-red-low">
                                        <q-card-section class="q-pa-xl">
                                            <div class="q-gutter-y-xl">
                                                <!-- Video Player de Cristal -->
                                                <div v-if="lec.video_url" class="video-container-premium shadow-24 border-red-low">
                                                    <q-video :src="getEmbedUrl(lec.video_url)" class="full-width aspect-16-9" />
                                                </div>
                                                
                                                <!-- Document Viewer -->
                                                <div v-if="lec.documento_url" class="document-vault q-pa-xl border-red-low shadow-inner">
                                                    <div class="row justify-between items-center q-mb-lg">
                                                       <div class="text-subtitle1 font-head text-grey-4 text-weight-bold">MATERIAL DE REFERENCIA RAC</div>
                                                       <q-btn color="red-9" icon="open_in_new" label="Ver Expediente PDF" size="sm" class="premium-btn shadow-24" @click="abrirEnlace(lec.documento_url)" />
                                                    </div>
                                                    <iframe :src="getEmbedUrl(lec.documento_url)" class="pdf-frame shadow-24 rounded-12" />
                                                </div>

                                                <div class="quiz-mision-card q-pa-xl flex items-center justify-between border-red-low shadow-inner rounded-12 welcome-hero overflow-hidden">
                                                    <div class="hero-glow"></div>
                                                    <div class="relative-position">
                                                       <div class="text-h5 font-head text-white text-weight-bold">Validación de Conocimiento</div>
                                                       <div class="text-grey-6 font-mono uppercase q-mt-xs" style="font-size:10px">REQUISITO PARA DESBLOQUEAR EXAMEN FINAL</div>
                                                    </div>
                                                    <q-btn 
                                                       v-if="lec.preguntas_count > 0"
                                                       color="red-9" icon="task_alt" 
                                                       :label="lec.nota_estudiante ? 'Reintentar Evaluación' : 'Comenzar Quiz Ahora'"
                                                       class="premium-btn shadow-24 q-px-xl relative-position"
                                                       @click="abrirQuizLeccion(lec)"
                                                    />
                                                </div>
                                            </div>
                                        </q-card-section>
                                    </q-card>
                                </q-expansion-item>
                            </q-list>
                        </q-tab-panel>

                        <!-- TEMARIO TÉCNICO -->
                        <q-tab-panel name="temario" class="q-pa-xl text-grey-3 font-mono line-height-1 shadow-inner" style="white-space: pre-wrap; font-size: 15px">
                            {{ materiaActiva.temario || 'Expediente de temario no disponible para este módulo.' }}
                        </q-tab-panel>

                        <!-- BRIEFING EN VIVO -->
                        <q-tab-panel name="clase" class="q-pa-xl text-center shadow-inner">
                            <q-icon name="podcasts" size="120px" color="red-9" class="q-mb-xl glow-primary pulsate" />
                            <div class="text-h3 font-head text-white text-weight-bolder tracking-tighter">Sesión de Instrucción Virtual</div>
                            <p class="text-grey-6 max-width-700 q-mx-auto font-mono text-h6 line-height-1">Accede a la sala de conferencias para interactuar en tiempo real con el Capitán Instructor y recibir formación teórica avanzada.</p>
                            <q-btn 
                              v-if="materiaActiva.link_meet" 
                              label="Ingresar al Briefing" color="red-10" 
                              icon="video_chat" class="premium-btn q-mt-xl shadow-24 q-px-xl q-py-lg" size="lg"
                              @click="abrirEnlace(materiaActiva.link_meet)" 
                            />
                            <div v-else class="text-red-9 q-mt-xl font-mono text-weight-bolder shadow-inner q-pa-lg rounded-12 border-red-low">SALA VIRTUAL TEMPORALMENTE NO DISPONIBLE</div>
                        </q-tab-panel>
                    </q-tab-panels>
                </q-card>
            </div>

            <!-- Columna Derecha: Score & Estatus -->
            <div class="col-12 col-lg-4">
                <q-card class="premium-glass-card border-red-left sticky-score shadow-24">
                    <q-card-section class="q-pa-xl">
                        <div class="text-subtitle1 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Estatus de Certificación</div>
                        
                        <div class="score-master-dial q-mb-xl">
                             <div class="text-center">
                                <div class="text-h1 font-mono text-weight-bolder line-height-1" :class="materiaActiva.nota_max >= 75 ? 'text-emerald' : 'text-red-9'">
                                  {{ Number(materiaActiva.nota_max || 0).toFixed(0) }}%
                                </div>
                                <div class="text-caption text-grey-7 font-mono uppercase tracking-widest q-mt-md">PROMEDIO FINAL OBTENIDO</div>
                             </div>
                        </div>

                        <div class="q-gutter-y-lg q-mb-xl">
                            <div class="flex justify-between font-mono text-caption">
                               <span class="text-grey-7">LOGROS DE LECCIONES (40%):</span>
                               <span class="text-white text-weight-bold">{{ Number(materiaActiva.promedio_lecciones || 0).toFixed(0) }}%</span>
                            </div>
                            <div class="flex justify-between font-mono text-caption">
                               <span class="text-grey-7">RETENCION EXAMEN (60%):</span>
                               <span class="text-white text-weight-bold">{{ Number(materiaActiva.nota_max || 0).toFixed(0) }}%</span>
                            </div>
                            <q-separator dark class="opacity-10" />
                        </div>

                        <q-btn 
                          label="Presentar Examen Final" 
                          color="red-9" class="full-width premium-btn shadow-24 py-xl" 
                          icon="history_edu" size="lg"
                          :loading="cargandoExamen"
                          @click="comenzarExamen" 
                        />
                        
                        <div class="q-mt-xl text-center text-grey-7 font-mono uppercase" style="font-size:9px">
                           Nota Mínima de Competencia: 75% • RAC 141
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </div>

    <!-- ════ SISTEMA DE EXAMEN INMERSIVO ════ -->
    <q-dialog v-model="dialogExamen" persistent maximized transition-show="slide-up" transition-hide="slide-down">
        <q-card class="bg-dark-page text-white overflow-hidden">
            <q-toolbar class="q-py-xl q-px-xl shadow-24 border-bottom-border bg-black-20">
                <q-icon name="psychology" color="red-9" size="42px" class="q-mr-md glow-primary" />
                <q-toolbar-title class="font-head text-weight-bolder uppercase tracking-tighter" style="font-size:24px">
                   {{ examMode === 'final' ? 'AUDITORÍA FINAL:' : 'VALIDACIÓN MÓDULO:' }} {{ materiaActiva?.nombre }}
                </q-toolbar-title>
                <div class="row items-center q-gutter-xl">
                    <div class="timer-vault shadow-inner" v-if="timeLeft > 0">
                        <q-icon name="schedule" size="28px" class="q-mr-md" :class="timeLeft < 60 ? 'text-red pulse-red' : 'text-red-9'" />
                        <span class="font-mono text-h5 text-weight-bolder">{{ formattedTime }}</span>
                    </div>
                    <div class="font-mono text-grey-6 text-weight-bold uppercase" style="font-size:12px">PREGUNTA <span class="text-white">{{ currentQuestionIndex + 1 }}</span> DE {{ preguntas.length }}</div>
                </div>
            </q-toolbar>

            <q-card-section v-if="preguntaActual" class="flex flex-center full-height q-pa-none">
                <div style="width:min(1100px, 95vw)" class="animate-fade q-pa-xl">
                    <div class="text-h3 q-mb-xl text-center font-head text-weight-bolder line-height-1 text-white">{{ preguntaActual.pregunta }}</div>
                    
                    <div class="row q-col-gutter-xl">
                        <div v-for="(opc, idx) in preguntaActual.opciones" :key="idx" class="col-12 col-md-6">
                           <q-card 
                              clickable v-ripple 
                              @click="seleccionarRespuesta(opc)"
                              class="exam-option-card q-pa-xl flex items-center shadow-24 pointer"
                              :class="respuestasTemporales[preguntaActual.id] === opc ? 'is-selected' : 'is-unselected'"
                           >
                              <div class="option-index font-mono shadow-inner">{{ String.fromCharCode(65 + idx) }}</div>
                              <div class="text-h6 q-ml-xl text-weight-bold" style="flex:1; font-size:17px">{{ opc }}</div>
                              <q-icon name="center_focus_strong" v-if="respuestasTemporales[preguntaActual.id] === opc" color="red-9" size="28px" />
                           </q-card>
                        </div>
                    </div>

                    <div class="row justify-between items-center q-mt-xl">
                        <q-btn flat label="Pregunta Anterior" icon="west" color="grey-7" @click="currentQuestionIndex--" :disable="currentQuestionIndex === 0" class="font-mono" />
                        
                        <q-btn v-if="currentQuestionIndex < preguntas.length - 1" 
                            color="red-9" label="Siguiente Parámetro" icon-right="east" class="premium-btn shadow-24 q-px-xl q-py-lg"
                            @click="currentQuestionIndex++" :disable="!respuestasTemporales[preguntaActual.id]" />
                        
                        <q-btn v-else color="emerald" label="Finalizar Evaluación" icon="send" class="premium-btn shadow-24 q-px-xl q-py-lg text-weight-bolder"
                            @click="finalizarExamen" :loading="enviandoExamen" :disable="!respuestasTemporales[preguntaActual.id]" />
                    </div>
                </div>
            </q-card-section>
        </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const materias = ref([])
const materiaActiva = ref(null)
const tabAula = ref('lecciones')

// Examen State
const dialogExamen = ref(false)
const examMode = ref('final')
const leccionActivaQuiz = ref(null)
const preguntas = ref([])
const currentQuestionIndex = ref(0)
const respuestasTemporales = ref({})
const cargandoExamen = ref(false)
const enviandoExamen = ref(false)
const timeLeft = ref(0)
const timer = ref(null)

const formattedTime = computed(() => {
    const min = Math.floor(timeLeft.value / 60); const sec = timeLeft.value % 60
    return `${min}:${sec.toString().padStart(2, '0')}`
})

const startTimer = (minutes) => {
    timeLeft.value = minutes * 60
    if (timer.value) clearInterval(timer.value)
    timer.value = setInterval(() => {
        timeLeft.value--
        if (timeLeft.value <= 0) { clearInterval(timer.value); finalizarExamen() }
    }, 1000)
}

const preguntaActual = computed(() => preguntas.value[currentQuestionIndex.value])

const cargarMaterias = async () => {
    try { const { data } = await api.get('/aula-virtual/mis-materias'); materias.value = data.data } catch (e) {}
}

const entrarAula = async (m) => {
    try {
        const { data } = await api.get(`/aula-virtual/materia/${m.id}`)
        materiaActiva.value = data.data; materiaActiva.value.aprobado = m.aprobado; materiaActiva.value.nota_max = m.nota_max
    } catch (e) {}
}

const finalizarExamen = async () => {
    enviandoExamen.value = true; if (timer.value) clearInterval(timer.value)
    try {
        const url = examMode.value === 'final' ? `/aula-virtual/materia/${materiaActiva.value.id}/examen` : `/aula-virtual/leccion/${leccionActivaQuiz.value.id}/quiz`
        const { data } = await api.post(url, { respuestas: respuestasTemporales.value })
        const res = data.resultado; dialogExamen.value = false
        $q.dialog({ title: res.aprobado ? '¡ÉXITO EN CERTIFICACIÓN!' : 'RESULTADO INSUFICIENTE', message: `Calificación final: ${res.nota.toFixed(0)}%. ${res.aprobado ? 'Capacidad teórica demostrada, excelente trabajo.' : 'Revisar material de estudio y reintentar.'}`, color: res.aprobado ? 'emerald' : 'red-9', ok: 'Confirmar' }).onOk(cerrarTodo)
    } finally { enviandoExamen.value = false }
}

const seleccionarRespuesta = (val) => { respuestasTemporales.value = { ...respuestasTemporales.value, [preguntaActual.value.id]: val } }

const comenzarExamen = () => {
    $q.dialog({ title: 'CONTROL DE EVALUACIÓN', message: '¿Confirmas el inicio del examen final UAEAC? El cronómetro no podrá detenerse.', color: 'red-9', cancel: 'Abortar', persistent: true }).onOk(async () => {
        cargandoExamen.value = true; examMode.value = 'final'
        try {
            const { data } = await api.get(`/aula-virtual/materia/${materiaActiva.value.id}/examen`)
            preguntas.value = data.data; respuestasTemporales.value = {}; currentQuestionIndex.value = 0; dialogExamen.value = true; startTimer(materiaActiva.value.duracion_minutos || 15)
        } finally { cargandoExamen.value = false }
    })
}

const abrirQuizLeccion = async (lec) => {
    try {
        const { data } = await api.get(`/aula-virtual/leccion/${lec.id}/quiz`)
        preguntas.value = data.data; leccionActivaQuiz.value = lec; examMode.value = 'quiz'; respuestasTemporales.value = {}; currentQuestionIndex.value = 0; dialogExamen.value = true; startTimer(5)
    } catch (e) {}
}

const cerrarTodo = () => {
    const mid = materiaActiva.value.id; materiaActiva.value = null; cargarMaterias().then(() => { const mat = materias.value.find(x => x.id === mid); if (mat) entrarAula(mat) })
}

const abrirEnlace = (url) => { window.open(url, '_blank') }
const getEmbedUrl = (url) => { if (!url) return ''; if (url.includes('youtube.com/watch?v=')) return `https://www.youtube.com/embed/${url.split('v=')[1].split('&')[0]}`; if (url.includes('youtu.be/')) return `https://www.youtube.com/embed/${url.split('youtu.be/')[1].split('?')[0]}`; if (url.includes('drive.google.com/file/d/')) return url.replace(/\/view.*?$/, '/preview'); return url; }

onMounted(cargarMaterias)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-left { border-left: 4px solid #A10B13 !important; }
.status-gradient-line { position: absolute; top:0; left:0; width:100%; height:4px; opacity:0.8; }
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.shadow-inner { box-shadow: inset 0 2px 20px rgba(0,0,0,0.5); }
.bg-black-30 { background: rgba(0,0,0,0.3); }
.bg-black-20 { background: rgba(0,0,0,0.2); }
.line-height-1 { line-height: 1.1; }
.text-emerald { color: #10b981; }
.bg-emerald { background: #10b981 !important; }

.video-container-premium { width: 100%; border-radius: 20px; overflow: hidden; background: #000; .aspect-16-9 { aspect-ratio: 16/9; } }
.document-vault { background: rgba(255,255,255,0.02); border-radius: 16px; margin-top: 40px; }
.pdf-frame { width: 100%; height: 650px; border: none; background: white; }

.score-master-dial { padding: 50px; border-radius: 50%; background: radial-gradient(circle, rgba(161, 11, 19, 0.1) 0%, transparent 70%); }
.sticky-score { position: sticky; top: 120px; }

.exam-option-card { 
  border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  &.is-selected { background: rgba(161, 11, 19, 0.15) !important; border-color: #A10B13 !important; transform: scale(1.02); }
  &.is-unselected { background: rgba(0,0,0,0.3); &:hover { background: rgba(255,255,255,0.03); transform: scale(1.01); } }
}
.option-index { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); font-weight: 900; font-size: 20px; }
.is-selected .option-index { background: #A10B13; color: white; }

.timer-vault { background: rgba(0,0,0,0.4); padding: 12px 25px; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; }
.bg-dark-page { background: #05070a; }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
.transition-2 { transition: all 0.2s; }
.rounded-20 { border-radius: 20px !important; }
.hover-row { transition: background 0.2s; &:hover { background: rgba(255,255,255,0.03); } }
</style>
