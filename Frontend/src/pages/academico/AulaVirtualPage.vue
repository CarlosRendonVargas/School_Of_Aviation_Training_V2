<template>
  <q-page padding class="rac-page">
    <div class="row items-center q-mb-lg">
      <q-icon name="school" size="36px" color="primary" class="q-mr-md icon-glow" />
      <div>
        <div class="text-h5 text-white font-head">Aula Virtual RAC 141</div>
        <div class="text-caption text-grey-6 font-mono">EDUCACIÓN AERONÁUTICA COMPUTARIZADA (LMS)</div>
      </div>
    </div>

    <!-- Lista de Materias -->
    <div class="row q-col-gutter-lg" v-if="!materiaActiva">
      <div v-for="m in materias" :key="m.id" class="col-12 col-md-4">
        <q-card class="premium-glass-card premium-hover-card full-height flex column">
          <q-card-section>
            <div class="row items-center justify-between q-mb-sm">
                <q-badge color="grey-10" text-color="grey-4" class="font-mono">{{ m.codigo }}</q-badge>
                <q-chip v-if="m.aprobado" color="positive" text-color="white" icon="verified" size="xs">APROBADO</q-chip>
                <q-chip v-else color="indigo-9" text-color="white" icon="history_edu" size="xs">CURSANDO</q-chip>
            </div>
            <div class="text-h6 text-white text-weight-bold">{{ m.nombre }}</div>
            <div class="text-caption text-grey-5">{{ m.etapa }}</div>
          </q-card-section>

          <q-card-section class="col">
             <div class="row q-col-gutter-sm text-center">
                 <div class="col-6">
                    <div class="text-h6 text-primary">{{ m.horas }}</div>
                    <div class="text-caption text-grey-7" style="font-size: 10px">HRS TIERRA</div>
                 </div>
                 <div class="col-6">
                    <div class="text-h6" :class="m.nota_max >= 75 ? 'text-positive' : 'text-orange'">{{ m.nota_max || '-' }}</div>
                    <div class="text-caption text-grey-7" style="font-size: 10px">MEJOR NOTA</div>
                 </div>
             </div>
          </q-card-section>

          <q-card-actions vertical class="q-px-md q-pb-md">
            <q-btn unelevated color="primary" label="Ingresar Aula" class="full-width" @click="entrarAula(m)" />
          </q-card-actions>
        </q-card>
      </div>
    </div>

    <!-- Detalle de Materia / Aula -->
    <div v-else class="row q-col-gutter-lg">
        <div class="col-12 q-mb-md">
            <q-btn flat icon="arrow_back" color="grey-5" label="Volver al Listado" @click="materiaActiva = null" />
        </div>

        <!-- Columna Izquierda: Contenido -->
        <div class="col-12 col-md-8">
            <q-card class="premium-glass-card" style="min-height: 400px">
                <q-card-section class="bg-primary text-white">
                    <div class="text-h6">{{ materiaActiva.nombre }}</div>
                    <div class="text-caption">Contenido Curricular y Recursos</div>
                </q-card-section>
                
                <q-tabs v-model="tabAula" dense class="text-grey" active-color="white" indicator-color="white" align="justify">
                    <q-tab name="temario" label="Temario" icon="list" />
                    <q-tab name="clase" label="Clase en Vivo" icon="video_camera_front" />
                    <q-tab name="recursos" label="Documentos" icon="attachment" />
                </q-tabs>

                <q-tab-panels v-model="tabAula" animated class="bg-transparent text-white">
                    <q-tab-panel name="temario">
                        <div v-if="materiaActiva.temario" class="font-mono text-grey-3" style="white-space: pre-wrap">
                            {{ materiaActiva.temario }}
                        </div>
                        <div v-else class="text-center q-pa-xl text-grey-7">
                            <q-icon name="menu_book" size="48px" />
                            <p>No hay temario detallado cargado para esta materia.</p>
                        </div>
                    </q-tab-panel>

                    <q-tab-panel name="clase">
                        <div class="text-center q-pa-xl">
                            <q-icon name="videocam" size="64px" color="blue-4" />
                            <div class="text-h6 q-mt-md">Sincronización Google Meet</div>
                            <p class="text-grey-5">Haz clic en el botón para unirte a la sesión programada con el instructor.</p>
                            <q-btn v-if="materiaActiva.link_meet" label="Unirse a Meet" color="blue-9" icon="launch" size="lg" rounded @click="abrirEnlace(materiaActiva.link_meet)" />
                            <div v-else class="text-red-4 font-mono">No hay enlace programado actualmente.</div>
                        </div>
                    </q-tab-panel>

                    <q-tab-panel name="recursos">
                        <div v-if="materiaActiva.documento_url" class="row items-center q-gutter-md q-pa-md bg-dark-light rounded-borders">
                            <q-icon name="picture_as_pdf" color="red-5" size="32px" />
                            <div class="col">
                                <div class="text-weight-bold">Guía de Estudio / Material de Apoyo</div>
                                <div class="text-caption text-grey-6">Documento PDF Oficial</div>
                            </div>
                            <q-btn flat color="teal-4" label="Descargar" icon="download" @click="abrirEnlace(materiaActiva.documento_url)" />
                        </div>
                        <div v-else class="text-center q-pa-xl text-grey-7">
                             <q-icon name="folder_off" size="48px" />
                             <p>Aún no se han cargado documentos para esta materia.</p>
                        </div>
                    </q-tab-panel>
                </q-tab-panels>
            </q-card>
        </div>

        <!-- Columna Derecha: Examen -->
        <div class="col-12 col-md-4">
            <q-card class="bg-dark-light border-teal-left">
                <q-card-section>
                    <div class="text-subtitle1 text-teal-4 text-weight-bold q-mb-md">Evaluación Teórica</div>
                    <p class="text-caption text-grey-4">Al completar el estudio de los recursos, debes presentar el examen para validar tu progreso en el RAC 141.</p>
                    
                    <div class="q-pa-md bg-dark rounded-borders q-mb-md">
                        <div class="row justify-between text-caption">
                            <span>Estado:</span>
                            <span :class="materiaActiva.aprobado ? 'text-positive' : 'text-orange'">{{ materiaActiva.aprobado ? 'Completado' : 'Pendiente' }}</span>
                        </div>
                        <div class="row justify-between text-caption q-mt-xs">
                            <span>Mínimo requerido:</span>
                            <span class="text-white">75%</span>
                        </div>
                    </div>

                    <q-btn label="Presentar Examen" color="teal-8" class="full-width" icon="assignment_turned_in" size="lg" :loading="cargandoExamen" @click="comenzarExamen" />
                </q-card-section>
            </q-card>
        </div>
    </div>

    <!-- Dialogo de Examen -->
    <q-dialog v-model="dialogExamen" persistent maximized transition-show="slide-up" transition-hide="slide-down">
        <q-card class="bg-dark text-white">
            <q-toolbar class="bg-primary text-white">
                <q-toolbar-title>Evaluación: {{ materiaActiva?.nombre }}</q-toolbar-title>
                <q-space />
                <div class="row items-center q-gutter-md q-mr-md">
                    <div class="row items-center q-gutter-xs text-orange-4 font-mono text-subtitle2" v-if="timeLeft > 0">
                        <q-icon name="timer" size="20px" class="q-mr-xs" :class="timeLeft < 60 ? 'text-red animate-blink' : ''" />
                        {{ formattedTime }}
                    </div>
                    <div class="font-mono text-subtitle2">Pregunta {{ currentQuestionIndex + 1 }} de {{ preguntas.length }}</div>
                </div>
            </q-toolbar>

            <q-card-section v-if="preguntaActual" class="q-pa-xl flex flex-center">
                <div style="max-width: 800px; width: 100%">
                    <div class="text-h5 q-mb-xl text-center">{{ preguntaActual.pregunta }}</div>
                    
                    <q-list class="q-gutter-md">
                        <q-item v-for="(opc, idx) in preguntaActual.opciones" :key="idx" 
                            clickable v-ripple 
                            @click="seleccionarRespuesta(opc)"
                            class="rounded-borders q-pa-md select-item"
                            :class="respuestasTemporales[preguntaActual.id] === opc ? 'bg-indigo-9 text-white' : 'bg-grey-10 text-grey-4'"
                            style="border: 1px solid rgba(255,255,255,0.05)"
                        >
                            <q-item-section avatar>
                                <div class="text-weight-bold">{{ String.fromCharCode(65 + idx) }}</div>
                            </q-item-section>
                            <q-item-section>{{ opc }}</q-item-section>
                            <q-item-section side v-if="respuestasTemporales[preguntaActual.id] === opc">
                                <q-icon name="check_circle" color="white" />
                            </q-item-section>
                        </q-item>
                    </q-list>

                    <div class="row justify-between q-mt-xl">
                        <q-btn flat label="Anterior" @click="currentQuestionIndex--" :disable="currentQuestionIndex === 0" />
                        
                        <q-btn v-if="currentQuestionIndex < preguntas.length - 1" 
                            color="indigo-7" label="Siguiente" @click="currentQuestionIndex++" :disable="!respuestasTemporales[preguntaActual.id]" />
                        
                        <q-btn v-else color="positive" label="Finalizar Examen" @click="finalizarExamen" :loading="enviandoExamen" :disable="!respuestasTemporales[preguntaActual.id]" />
                    </div>
                </div>
            </q-card-section>
        </q-card>
    </q-dialog>

    <!-- Dialogo Resultado -->
    <q-dialog v-model="dialogResultado">
        <q-card class="bg-dark text-white text-center q-pa-lg" style="min-width: 300px">
            <q-card-section>
                <q-icon :name="resultadoFinal?.aprobado ? 'verified' : 'error'" :color="resultadoFinal?.aprobado ? 'positive' : 'negative'" size="80px" />
                <div class="text-h4 q-mt-md">{{ resultadoFinal?.nota.toFixed(1) }}</div>
                <div class="text-subtitle1 text-grey-5">{{ resultadoFinal?.aprobado ? '¡Materia Aprobada!' : 'No aprobaste la evaluación' }}</div>
                <div class="text-caption q-mt-sm">{{ resultadoFinal?.aciertos }} aciertos de {{ resultadoFinal?.total }} preguntas</div>
            </q-card-section>
            <q-card-actions align="center">
                <q-btn label="Cerrar" color="primary" @click="cerrarTodo" />
            </q-card-actions>
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
const tabAula = ref('temario')

// Examen State
const dialogExamen = ref(false)
const preguntas = ref([])
const currentQuestionIndex = ref(0)
const respuestasTemporales = ref({})
const cargandoExamen = ref(false)
const enviandoExamen = ref(false)

// Result State
const dialogResultado = ref(false)
const resultadoFinal = ref(null)

// Timer State
const timeLeft = ref(0)
const timer = ref(null)

const formattedTime = computed(() => {
    const min = Math.floor(timeLeft.value / 60)
    const sec = timeLeft.value % 60
    return `${min}:${sec.toString().padStart(2, '0')}`
})

const startTimer = (minutes) => {
    timeLeft.value = minutes * 60
    if (timer.value) clearInterval(timer.value)
    timer.value = setInterval(() => {
        timeLeft.value--
        if (timeLeft.value <= 0) {
            clearInterval(timer.value)
            forceFinalizarExamen()
        }
    }, 1000)
}

const forceFinalizarExamen = () => {
    $q.notify({
        color: 'warning',
        message: 'TIEMPO AGOTADO. El examen se guardará automáticamente con lo que hayas respondido.',
        icon: 'timer_off'
    })
    finalizarExamen()
}

const preguntaActual = computed(() => preguntas.value[currentQuestionIndex.value])

const cargarMaterias = async () => {
    try {
        const { data } = await api.get('/aula-virtual/mis-materias')
        materias.value = data.data
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error cargando aula virtual' })
    }
}

const entrarAula = async (m) => {
    try {
        const { data } = await api.get(`/aula-virtual/materia/${m.id}`)
        materiaActiva.value = data.data
        // Merge progress data
        materiaActiva.value.aprobado = m.aprobado
        materiaActiva.value.nota_max = m.nota_max
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al ingresar al aula' })
    }
}

const finalizarExamen = async () => {
    enviandoExamen.value = true
    // Limpiar timer y listener de fraude
    if (timer.value) clearInterval(timer.value)
    document.removeEventListener('visibilitychange', handleFraud)
    try {
        const { data } = await api.post(`/aula-virtual/materia/${materiaActiva.value.id}/examen`, {
            respuestas: respuestasTemporales.value
        })
        resultadoFinal.value = data.resultado
        dialogExamen.value = false
        dialogResultado.value = true
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al enviar examen' })
    } finally {
        enviandoExamen.value = false
    }
}

const seleccionarRespuesta = (val) => {
    respuestasTemporales.value[preguntaActual.value.id] = val
}

const handleFraud = () => {
    if (document.visibilityState === 'hidden' && dialogExamen.value) {
        $q.notify({
            color: 'negative',
            message: 'ALERTA ANTIFRAUDE: Se ha detectado cambio de pestaña. Este evento será reportado a la Dirección de Operaciones.',
            icon: 'warning',
            position: 'top',
            timeout: 10000
        })
        console.warn('Student switched tabs during exam')
    }
}

const comenzarExamen = async () => {
    cargandoExamen.value = true
    try {
        const { data } = await api.get(`/aula-virtual/materia/${materiaActiva.value.id}/examen`)
        preguntas.value = data.data
        respuestasTemporales.value = {}
        currentQuestionIndex.value = 0
        dialogExamen.value = true
        // Iniciar timer
        startTimer(materiaActiva.value.duracion_minutos || 15)
        // Iniciar monitoreo antifraude
        document.addEventListener('visibilitychange', handleFraud)
    } catch (e) {
        if (e.response?.status === 402) {
             $q.dialog({
                 title: 'Pago Requerido',
                 message: e.response.data.mensaje,
                 ok: 'Entendido',
                 color: 'orange'
             })
        } else {
            $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Error al cargar examen' })
        }
    } finally {
        cargandoExamen.value = false
    }
}

const cerrarTodo = () => {
    dialogResultado.value = false
    materiaActiva.value = null
    cargarMaterias()
}

const abrirEnlace = (url) => {
    window.open(url, '_blank')
}

onMounted(cargarMaterias)
</script>

<style scoped>
.select-item {
    transition: all 0.2s ease;
    cursor: pointer;
}
.select-item:hover {
    background: rgba(255,255,255,0.08) !important;
}
</style>
