<template>
  <q-page class="q-pa-md" style="padding-bottom:100px">

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
    <div v-else>
        <div class="row items-center justify-between q-mb-lg welcome-hero premium-glass-card border-red-low shadow-24 overflow-hidden" 
             :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
           <div class="hero-glow"></div>
           <div class="row items-center relative-position col-12 col-md-auto">
              <q-btn flat round icon="arrow_back" color="red-9" @click="materiaActiva = null" :class="$q.screen.lt.md ? 'q-mr-md' : 'q-mr-xl'" class="shadow-24" />
              <div class="col">
                 <div class="text-white font-head text-weight-bolder uppercase tracking-tighter line-height-1" 
                      :style="$q.screen.lt.md ? 'font-size: 24px' : 'font-size: 48px'">
                      {{ materiaActiva.nombre }}
                 </div>
                 <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:9px">CONTENIDO TÉCNICO Y EVALUACIÓN</div>
              </div>
           </div>
           <q-badge outline color="red-9" class="q-pa-sm font-mono text-weight-bold shadow-24 relative-position gt-xs">ID: {{ materiaActiva.codigo }}</q-badge>
        </div>


        <div class="row q-col-gutter-xl">
            <!-- Columna Izquierda: Recursos de Misión -->
            <div class="col-12 col-lg-8">
                <q-card class="premium-glass-card shadow-24 overflow-hidden border-red-low">
                    <q-tabs v-model="tabAula" dense active-color="red-9" indicator-color="red-9" align="justify" class="bg-black-20 text-grey-5 border-bottom">
                        <q-tab name="lecciones" :label="$q.screen.lt.md ? 'Lecciones' : 'Lecciones del Módulo'" icon="video_settings" />
                        <q-tab name="temario" :label="$q.screen.lt.md ? 'Syllabus' : 'Syllabus Técnico'" icon="description" />
                        <q-tab name="clase" :label="$q.screen.lt.md ? 'En Vivo' : 'Briefing en Vivo'" icon="podcasts" />
                    </q-tabs>


                    <q-tab-panels v-model="tabAula" animated class="bg-transparent text-white">
                        
                        <!-- LECCIONES INTERACTIVAS -->
                        <q-tab-panel name="lecciones" class="q-pa-none">
                            <q-list dark separator class="shadow-inner">
                                <q-expansion-item
                                    v-for="lec in materiaActiva.lecciones" :key="lec.id"
                                    :header-class="$q.screen.lt.md ? 'q-py-lg q-px-md' : 'q-py-xl q-px-xl'"
                                    group="lecciones"
                                    class="hover-row"
                                >

                                    <template v-slot:header>
                                       <q-item-section avatar>
                                          <q-icon :name="lec.nota_estudiante?.aprobado ? 'verified' : 'play_circle'" :color="lec.nota_estudiante?.aprobado ? 'emerald' : 'red-9'" size="38px" />
                                       </q-item-section>
                                       <q-item-section>
                                          <q-item-label class="font-head text-weight-bolder uppercase" :class="$q.screen.lt.md ? 'text-subtitle1' : 'text-h6'">{{ lec.titulo }}</q-item-label>
                                          <q-item-label caption class="text-grey-7 font-mono uppercase wrap" style="font-size:9px">{{ lec.descripcion || 'Sin descripción de módulo registrada' }}</q-item-label>
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
                                                <!-- Video Player Multi-Formato -->
                                                <div v-if="lec.video_url" class="video-container-premium shadow-24 overflow-hidden border-red-low">
                                                    <div class="row justify-end q-pa-sm" style="background: rgba(0,0,0,0.5)">
                                                        <q-btn flat dense icon="open_in_new" label="Abrir en ventana externa" color="white" size="xs" @click="abrirEnlace(lec.video_url)" />
                                                    </div>
                                                    <video v-if="lec.video_url.endsWith('.mp4') || lec.video_url.includes('mp4')" 
                                                           controls class="full-width rounded-12" style="aspect-ratio: 16/9; background: #000;">
                                                        <source :src="lec.video_url" type="video/mp4">
                                                    </video>
                                                    <q-video v-else :src="getEmbedUrl(lec.video_url)" 
                                                             style="width: 100%; aspect-ratio: 16/9;" 
                                                             :style="$q.screen.lt.md ? 'min-height: 250px;' : 'min-height: 450px;'"
                                                             class="rounded-12" />
                                                </div>

                                                
                                                <!-- Document Viewer -->
                                                <div v-if="lec.documento_url" class="document-vault border-red-low shadow-inner" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
                                                    <div class="row justify-between items-center q-mb-lg" :class="$q.screen.lt.md ? 'column text-center' : ''">
                                                       <div class="font-head text-grey-4 text-weight-bold" :class="$q.screen.lt.md ? 'text-subtitle2 q-mb-md' : 'text-subtitle1'">MATERIAL DE REFERENCIA RAC</div>
                                                       <q-btn color="red-9" icon="open_in_new" label="Ver Expediente PDF" size="sm" class="premium-btn shadow-24" :class="$q.screen.lt.md ? 'full-width' : ''" @click="abrirEnlace(lec.documento_url)" />
                                                    </div>
                                                    <iframe :src="getEmbedUrl(lec.documento_url)" class="pdf-frame shadow-24 rounded-12" :style="$q.screen.lt.md ? 'height: 400px' : ''" />
                                                </div>


                                                <div class="quiz-mision-card flex items-center justify-between border-red-low shadow-inner rounded-12 welcome-hero overflow-hidden"
                                                     :class="$q.screen.lt.md ? 'q-pa-lg column items-center text-center' : 'q-pa-xl'">
                                                    <div class="hero-glow"></div>
                                                    <div class="relative-position" :class="$q.screen.lt.md ? 'q-mb-md' : ''">
                                                       <div class="font-head text-white text-weight-bold" :class="$q.screen.lt.md ? 'text-h6' : 'text-h5'">Validación de Conocimiento</div>
                                                       <div class="text-grey-6 font-mono uppercase q-mt-xs" style="font-size:9px">REQUISITO PARA DESBLOQUEAR EXAMEN FINAL</div>
                                                    </div>
                                                    <q-btn 
                                                       v-if="lec.preguntas_count > 0"
                                                       color="red-9" icon="task_alt" 
                                                       :label="lec.nota_estudiante ? 'Reintentar Evaluación' : 'Comenzar Quiz Ahora'"
                                                       class="premium-btn shadow-24 relative-position"
                                                       :class="$q.screen.lt.md ? 'full-width' : 'q-px-xl'"
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
                        <q-tab-panel name="temario" class="text-grey-3 line-height-2 shadow-inner" 
                             :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'"
                             style="white-space: pre-wrap; font-size: 14px; letter-spacing: 0.5px;">
                            <div class="font-head text-white text-subtitle1 q-mb-lg uppercase text-weight-bold border-bottom-border pb-sm">Contenido Programático Autorizado</div>
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

    <!-- ════ SISTEMA DE EXAMEN INMERSIVO (PREMIUM DESIGN) ════ -->
    <q-dialog v-model="dialogExamen" persistent maximized transition-show="fade" transition-hide="fade">
        <q-card class="bg-dark-page text-white overflow-hidden relative-position">
            <!-- Botón Cerrar (Emergencia) -->
            <q-btn flat round icon="close" color="grey-9" v-close-popup class="absolute-top-right q-ma-md z-max" />

            <!-- Capas de Decoración / Ambient Glow -->
            <div class="exam-ambient-glow top-left"></div>
            <div class="exam-ambient-glow bottom-right"></div>
            <div class="question-backdrop-glow" v-if="preguntaActual"></div>

            <!-- Header Flotante: HUD de Examen -->
            <div class="exam-floating-header flex justify-center">
                <div class="column items-center">
                    <div class="timer-pill shadow-24 flex items-center no-wrap" v-if="timeLeft > 0">
                        <q-icon name="precision_manufacturing" color="red-9" size="24px" class="q-mr-md pulsate" />
                        <span class="timer-text">{{ formattedTime }}</span>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <q-card-section class="col q-pa-none relative-position scroll">
                    <div v-if="cargandoExamen" class="flex flex-center full-height">
                        <q-spinner-orbit color="red-9" size="80px" />
                    </div>
                    <div v-else class="column items-center no-wrap full-width relative-position" :style="$q.screen.gt.sm ? 'padding-bottom: 300px;' : 'padding-bottom: 180px;'">
                        <!-- Pilar Central Decorativo (Desktop) -->
                        <div class="exam-core-pillar gt-sm shadow-24"></div>

                        <!-- Espaciador para no chocar con el HUD -->
                        <div style="height: 120px;" class="gt-sm"></div>
                        <div style="height: 80px;" class="lt-md"></div>

                        <div v-if="preguntaActual" style="width:min(1100px, 95vw)" class="q-pa-md z-top">
                            <!-- Indicador de Progreso (HUD) -->
                            <div class="text-center q-mb-xl">
                                <div class="hud-label">
                                    MISSION STATUS: QUESTION {{ currentQuestionIndex + 1 }} OF {{ preguntas.length }}
                                </div>
                            </div>

                            <!-- Pregunta con Tipografía Premium -->
                            <div class="exam-question-container text-center q-mb-xl">
                                <h2 class="exam-question-text line-height-1" style="max-width: 950px; margin: 0 auto;">{{ preguntaActual.pregunta }}</h2>
                            </div>
                            
                            <!-- Opciones de Respuesta -->
                            <div class="row q-col-gutter-lg justify-center">
                                <div v-for="(opc, idx) in preguntaActual.opciones" :key="idx" class="col-12 col-md-10">
                                   <q-card 
                                       clickable v-ripple 
                                       @click="seleccionarRespuesta(opc)"
                                       class="answer-card-premium flex items-center shadow-24 pointer"
                                       :class="respuestasTemporales[preguntaActual.id] === opc ? 'is-selected' : 'is-unselected'"
                                   >
                                      <div class="answer-letter-box font-head">
                                          {{ String.fromCharCode(65 + idx) }}
                                      </div>
                                       <div class="answer-text-content q-ml-xl">
                                           {{ opc }}
                                       </div>
                                       <div v-if="respuestasTemporales[preguntaActual.id] === opc" class="q-ml-auto q-mr-lg">
                                           <q-icon name="check_circle" color="red-9" size="32px" class="shadow-24" />
                                       </div>
                                    </q-card>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!cargandoExamen" class="flex flex-center q-pa-xl z-top">
                            <div class="text-center">
                                <q-icon name="error_outline" color="grey-8" size="80px" />
                                <div class="text-h5 text-grey-8 font-head q-mt-md">NO HAY PREGUNTAS DISPONIBLES</div>
                                <div class="text-grey-9 font-mono">Contacta al instructor para cargar el banco de preguntas.</div>
                                <q-btn label="Cerrar Examen" flat color="white" class="q-mt-xl premium-btn" @click="dialogExamen = false" />
                            </div>
                        </div>
                    </div>
            </q-card-section>

            <!-- Footer Fijo de Navegación -->
            <div class="fixed-bottom flex justify-center q-pa-md z-max no-pointer-events" style="background: linear-gradient(to top, rgba(0,0,0,0.95), transparent); padding-bottom: 30px;">
                 <div class="row items-center justify-between no-wrap q-gutter-x-xl shadow-24 container-nav-premium all-pointer-events" v-if="preguntaActual">
                    <q-btn flat round icon="west" color="grey-7" @click="currentQuestionIndex--" :disable="currentQuestionIndex === 0" class="btn-nav-flat">
                        <span class="gt-xs q-ml-sm">Anterior</span>
                    </q-btn>
                    
                    <q-btn 
                        :loading="enviandoExamen"
                        @click="currentQuestionIndex < preguntas.length - 1 ? currentQuestionIndex++ : finalizarExamen()"
                        class="premium-btn-red"
                        :disable="!respuestasTemporales[preguntaActual.id]"
                    >
                        <div class="row items-center no-wrap">
                            <span class="q-mr-md">{{ currentQuestionIndex < preguntas.length - 1 ? 'SIGUIENTE' : 'FINALIZAR' }}</span>
                            <q-icon :name="currentQuestionIndex < preguntas.length - 1 ? 'east' : 'check_circle'" />
                        </div>
                    </q-btn>
                </div>
            </div>


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
    cargandoExamen.value = true
    try {
        const { data } = await api.get(`/aula-virtual/leccion/${lec.id}/quiz`)
        preguntas.value = data.data; leccionActivaQuiz.value = lec; examMode.value = 'quiz'; respuestasTemporales.value = {}; currentQuestionIndex.value = 0; dialogExamen.value = true; startTimer(5)
    } catch (e) {
        $q.notify({ type: 'negative', message: 'Error cargando el quiz' })
    } finally {
        cargandoExamen.value = false
    }
}

const cerrarTodo = () => {
    const mid = materiaActiva.value.id; materiaActiva.value = null; cargarMaterias().then(() => { const mat = materias.value.find(x => x.id === mid); if (mat) entrarAula(mat) })
}

const abrirEnlace = (url) => { window.open(url, '_blank') }
const getEmbedUrl = (url) => {
    if (!url) return ''
    let videoId = ''
    if (url.includes('youtube.com/watch?v=')) {
        videoId = url.split('v=')[1]?.split('&')[0]
    } else if (url.includes('youtu.be/')) {
        videoId = url.split('youtu.be/')[1]?.split('?')[0]
    } else if (url.includes('youtube.com/shorts/')) {
        videoId = url.split('shorts/')[1]?.split('?')[0]
    } else if (url.includes('youtube.com/live/')) {
        videoId = url.split('live/')[1]?.split('?')[0]
    }
    
    if (videoId) return `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1`
    if (url.includes('drive.google.com/file/d/')) return url.replace(/\/view.*?$/, '/preview')
    return url
}

onMounted(cargarMaterias)
</script>

<style lang="scss" scoped>

// ═════════ ANIMACIONES ═════════
.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }
.animate-fade { animation: fadeIn 0.8s ease-in-out both; }
@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

// ═════════ COMPONENTES AULA ═════════
.status-gradient-line { position: absolute; top:0; left:0; width:100%; height:4px; opacity:0.8; }
.shadow-inner { box-shadow: inset 0 2px 20px rgba(0,0,0,0.5); }
.bg-black-30 { background: rgba(0,0,0,0.3); }
.bg-black-20 { background: rgba(0,0,0,0.2); }

.video-container-premium { border-radius: 20px; overflow: hidden; background: #000; .aspect-16-9 { aspect-ratio: 16/9; } }
.document-vault { background: rgba(255,255,255,0.02); border-radius: 16px; margin-top: 40px; }
.pdf-frame { width: 100%; height: 650px; border: none; background: white; }

.score-master-dial { padding: 50px; border-radius: 50%; background: radial-gradient(circle, rgba(161, 11, 19, 0.1) 0%, transparent 70%); }
.sticky-score { position: sticky; top: 120px; }

// ═════════ EXAM SYSTEM PREMIUM ═════════
.bg-dark-page { background: #000000; } // Black absolute base

.exam-ambient-glow {
    position: absolute; width: 600px; height: 600px; border-radius: 50%; pointer-events: none; opacity: 0.15;
    &.top-left { top: -200px; left: -200px; background: radial-gradient(circle, #A10B13 0%, transparent 70%); }
    &.bottom-right { bottom: -200px; right: -200px; background: radial-gradient(circle, #A10B13 0%, transparent 70%); }
}

.question-backdrop-glow {
    position: absolute; top: 30%; left: 50%; transform: translate(-50%, -50%);
    width: 800px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(255,255,255,0.03) 0%, transparent 80%);
    pointer-events: none;
}

.exam-holographic-overlay {
    position: absolute; top:0; left:0; width:100%; height:100%;
    background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%),
                linear-gradient(90deg, rgba(255, 0, 0, 0.02), rgba(0, 255, 0, 0.005), rgba(0, 0, 255, 0.02));
    background-size: 100% 4px, 2px 100%;
    pointer-events: none; z-index: 5; opacity: 0.2;
}

.exam-floating-header {
    position: absolute; top: 30px; left: 0; width: 100%; z-index: 1000;
    pointer-events: none;
}

.exam-core-pillar {
    position: absolute; left: 50%; top: 0; bottom: 0; width: 2px;
    background: repeating-linear-gradient(to bottom, 
        rgba(161, 11, 19, 0.4) 0, rgba(161, 11, 19, 0.4) 10px, 
        transparent 10px, transparent 20px
    );
    transform: translateX(-50%); z-index: 1; opacity: 0.3;
}

.timer-pill {
    background: rgba(18, 22, 30, 0.9);
    border: 1px solid rgba(161, 11, 19, 0.4);
    border-radius: 50px; padding: 8px 32px;
    backdrop-filter: blur(10px);
    box-shadow: 0 0 40px rgba(0,0,0,0.8), 0 0 15px rgba(161, 11, 19, 0.2);
    pointer-events: all;
    .timer-text { font-family: 'Courier Prime', monospace; font-size: 28px; font-weight: 900; color: #fff; letter-spacing: 2px; }
}

.progress-label { font-family: 'Courier Prime', monospace; font-size: 10px; letter-spacing: 5px; color: rgba(161, 11, 19, 0.8); font-weight: 900; text-transform: uppercase; }

.exam-question-text {
    font-family: 'Cinzel', serif; font-size: 25px; font-weight: 900; color: #fff;
    text-shadow: 0 0 30px rgba(161, 11, 19, 0.3), 0 5px 10px #000;
    letter-spacing: -2px; display: block; line-height: 1;
}

.answer-card-premium {
    background: rgba(10, 12, 16, 0.8) !important;
    border: 1px solid rgba(161, 11, 19, 0.2) !important;
    border-radius: 8px !important;
    padding: 10px 20px !important;
    transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
    
    .answer-letter-box {
        width: 60px; height: 60px; border-radius: 4px; display: flex; align-items: center; justify-content: center;
        background: transparent; color: #64748b; font-size: 26px; font-weight: 900; border: 2px solid rgba(161, 11, 19, 0.1);
        flex-shrink: 0; transition: all 0.3s;
    }
    
    .answer-text-content { font-size: 22px; font-weight: 500; color: #94a3b8; line-height: 1.4; flex: 1; margin-left: 30px; }

    &.is-selected {
        background: #000 !important;
        border-color: #A10B13 !important;
        box-shadow: 0 0 40px rgba(161, 11, 19, 0.3), inset 0 0 20px rgba(161, 11, 19, 0.1) !important;
        transform: scale(1.02);
        .answer-letter-box { background: #A10B13; color: white; border-color: transparent; box-shadow: 0 0 15px rgba(161, 11, 19, 0.4); }
        .answer-text-content { color: #fff; }
    }

    &.is-unselected:hover {
        background: rgba(18, 22, 30, 1) !important;
        border-color: rgba(161, 11, 19, 0.5) !important;
        transform: translateX(10px);
        .answer-letter-box { border-color: #fff; color: #fff; }
    }
}

.premium-btn-red {
    background: #A10B13 !important; color: white !important;
    border-radius: 50px !important; padding: 12px 40px !important;
    font-weight: 900 !important; letter-spacing: 1px !important;
    box-shadow: 0 10px 25px rgba(161, 11, 19, 0.4) !important;
}

.btn-nav-flat { font-family: 'Courier Prime', monospace; font-size: 12px; letter-spacing: 1px; text-transform: uppercase; }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.container-nav-premium {
    width: min(750px, 95vw); margin: 0 auto;
    background: rgba(18, 22, 30, 0.95); border: 1px solid rgba(161, 11, 19, 0.4);
    border-radius: 80px; padding: 10px 40px; backdrop-filter: blur(25px);
    box-shadow: 0 10px 50px rgba(0,0,0,0.8);
}

@media (max-width: 600px) {
    .exam-floating-header { top: 20px; padding: 0 20px; }
    .timer-pill { padding: 8px 20px; .timer-text { font-size: 18px; } }
    .answer-card-premium { padding: 16px 20px !important; .answer-letter-box { width: 44px; height: 44px; font-size: 16px; } .answer-text-content { font-size: 15px; margin-left: 15px; } }
    .progress-label { margin-top: 60px; }
}

.hover-row { transition: background 0.2s; &:hover { background: rgba(255,255,255,0.03); } }
</style>
