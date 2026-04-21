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
                <q-chip v-else-if="m.habilitado" color="blue-9" text-color="white" icon="check_circle" size="xs" dense class="shadow-24">HABILITADO</q-chip>
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
                             
                             <div v-if="materiaActiva.video_url" class="q-mb-xl video-container-premium shadow-24 border-red-low">
                                <q-video :src="getEmbedUrl(materiaActiva.video_url)" style="width: 100%; aspect-ratio: 16/9;" class="rounded-12" />
                                <div class="q-pa-md text-center text-grey-6 font-mono text-caption uppercase" style="font-size:9px">INTRODUCCIÓN TÉCNICA DEL MÓDULO</div>
                             </div>

                             {{ materiaActiva.temario || 'Expediente de temario no disponible para este módulo.' }}
                        </q-tab-panel>


                        <!-- BRIEFING EN VIVO (IFRAME EMBED) -->
                        <q-tab-panel name="clase" class="q-pa-none bg-black overflow-hidden relative-position" :style="$q.screen.lt.md ? 'height: 500px' : 'height: 750px'">
                            <div v-if="materiaActiva.link_meet" class="full-height column">
                                <div class="row items-center justify-between q-pa-md bg-black-20 border-bottom">
                                    <div class="row items-center">
                                        <q-icon name="podcasts" color="red-9" size="sm" class="q-mr-sm pulsate" />
                                        <div class="font-head text-weight-bold text-caption gt-xs">SALA DE INSTRUCCIÓN EN VIVO</div>
                                        <div class="font-head text-weight-bold text-caption lt-sm">SALA EN VIVO</div>
                                        <q-badge v-if="materiaActiva.sesion_viva_inicio" color="red-low" text-color="red-9" class="q-ml-md font-mono text-weight-bolder">
                                            {{ formatFecha(materiaActiva.sesion_viva_inicio) }}
                                        </q-badge>
                                    </div>
                                    <div class="q-gutter-x-sm">
                                        <q-btn flat dense icon="refresh" color="white" size="sm" @click="tabAula = 'lecciones'; setTimeout(() => tabAula = 'clase', 100)" />
                                        <q-btn flat dense icon="open_in_new" label="Ventana Externa" color="red-9" size="sm" class="text-weight-bold" @click="abrirEnlace(materiaActiva.link_meet)" />
                                    </div>
                                </div>
                                <div class="col relative-position bg-black-30 flex flex-center">
                                    <!-- Si es Google Meet, mostramos una interfaz de "Unirse" premium en lugar de un iframe roto -->
                                    <div v-if="materiaActiva.link_meet.includes('meet.google.com')" class="text-center q-pa-xl animate-fade">
                                        <div class="video-preview-placeholder shadow-24 q-mb-xl flex flex-center">
                                            <q-icon name="videocam" size="100px" color="red-9" class="pulsate opacity-20" />
                                            <div class="absolute-center full-width">
                                                <div class="text-h6 font-head text-weight-bold">GOOGLE MEET</div>
                                                <div class="text-caption font-mono text-grey-5">SESIÓN EXTERNA REQUERIDA</div>
                                            </div>
                                        </div>
                                        <div class="text-h4 font-head text-white text-weight-bolder q-mb-md">Sala de Instrucción Lista</div>
                                        <p class="text-grey-6 font-mono max-width-500 q-mx-auto q-mb-xl">Debido a políticas de seguridad de Google, las sesiones de Meet deben abrirse en una ventana independiente para habilitar cámara y micrófono.</p>
                                        <q-btn 
                                            label="Unirse a la Sesión Ahora" 
                                            color="red-10" 
                                            icon="open_in_new" 
                                            class="premium-btn q-px-xl q-py-lg shadow-24" 
                                            size="lg"
                                            @click="abrirEnlace(materiaActiva.link_meet)" 
                                        />
                                    </div>

                                    <!-- Para otros servicios que permitan iframe (Jitsi, Zoom Web, etc) -->
                                    <iframe 
                                        v-else
                                        :src="materiaActiva.link_meet" 
                                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                                        class="full-width full-height no-border"
                                        title="Sala Virtual"
                                    ></iframe>

                                    <!-- Overlay informativo discreto -->
                                    <div class="absolute-bottom q-pa-xs bg-black-50 text-center text-grey-8" style="font-size: 9px; letter-spacing: 1px;">
                                        RAC 141 · CONEXIÓN CIFRADA DE PUNTO A PUNTO
                                    </div>
                                </div>
                            </div>
                            <div v-else class="flex flex-center full-height column q-pa-xl text-center welcome-hero overflow-hidden">
                                <div class="hero-glow"></div>
                                <q-icon name="videocam_off" size="120px" color="red-9" class="q-mb-lg pulsate opacity-50" />
                                <div class="text-h4 font-head text-white text-weight-bolder tracking-tighter">SALA VIRTUAL EN HANGAR</div>
                                <p class="text-grey-6 font-mono q-mt-md max-width-500">El Capitán Instructor aún no ha habilitado la frecuencia para la sesión en vivo. Por favor, verifica el horario programado en tu syllabus.</p>
                                <q-btn flat label="Revisar Syllabus" color="red-9" icon="description" @click="tabAula = 'temario'" class="q-mt-xl" />
                            </div>
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

                        <!-- Lógica de Botón de Examen Inteligente -->
                        <div v-if="materiaActiva.aprobado" class="text-center q-pa-lg bg-emerald-transparent rounded-12 border-emerald shadow-inner">
                            <q-icon name="verified" color="emerald" size="48px" class="q-mb-sm" />
                            <div class="text-emerald font-head text-weight-bolder">CERTIFICACIÓN OBTENIDA</div>
                            <div class="text-grey-6 font-mono text-caption uppercase" style="font-size:9px">CONTENIDO COMPLETADO SEGÚN RAC 141</div>
                        </div>

                        <template v-else-if="materiaActiva.intentos > 0 && (materiaActiva.nota_max || 0) < 75 && !materiaActiva.habilitado">
                            <q-btn 
                              label="Solicitar Habilitación de Reintento" 
                              color="orange-10" class="full-width premium-btn shadow-24 py-xl" 
                              icon="payments" size="lg"
                              @click="solicitarReintento" 
                            />
                            <div class="q-mt-lg text-center text-orange-9 font-mono text-weight-bolder animate-pulse" style="font-size:11px">
                               <q-icon name="warning" class="q-mr-xs" /> EXAMEN BLOQUEADO: REQUIERE PAGO DE REINTENTO
                            </div>
                        </template>

                        <q-btn 
                          v-else
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
    <q-dialog v-model="dialogExamen" persistent maximized transition-show="fade" transition-hide="fade" @show="setupAntiFraude" @hide="cleanupAntiFraude">
        <q-card class="bg-dark-page text-white overflow-hidden relative-position no-select" @contextmenu.prevent>
            <!-- Botón Cerrar (Emergencia) -->
            <!-- Botón Abandonar (Con Advertencia) -->
            <q-btn flat round icon="close" color="grey-9" @click="abandonarExamen" class="absolute-top-right q-ma-md z-max" />

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
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import dayjs from 'dayjs'

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

// Anti-Fraude State
const fraudWarnings = ref(0)
const isExamActive = ref(false)

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
        materiaActiva.value = data.data; 
        materiaActiva.value.aprobado = m.aprobado; 
        materiaActiva.value.nota_max = m.nota_max;
        materiaActiva.value.intentos = m.intentos;
        materiaActiva.value.habilitado = m.habilitado;
    } catch (e) {}
}

const finalizarExamen = async () => {
    enviandoExamen.value = true; if (timer.value) clearInterval(timer.value)
    isExamActive.value = false // Detener monitoreo
    try {
        const url = examMode.value === 'final' ? `/aula-virtual/materia/${materiaActiva.value.id}/examen` : `/aula-virtual/leccion/${leccionActivaQuiz.value.id}/quiz`
        const { data } = await api.post(url, { 
            respuestas: respuestasTemporales.value,
            fraude_intentos: fraudWarnings.value
        })
        const res = data.resultado; dialogExamen.value = false
        $q.dialog({ title: res.aprobado ? '¡ÉXITO EN CERTIFICACIÓN!' : 'RESULTADO INSUFICIENTE', message: `Calificación final: ${res.nota.toFixed(0)}%. ${res.aprobado ? 'Capacidad teórica demostrada, excelente trabajo.' : 'Revisar material de estudio y reintentar.'}`, color: res.aprobado ? 'emerald' : 'red-9', ok: 'Confirmar' }).onOk(cerrarTodo)
    } finally { 
        enviandoExamen.value = false
        cleanupAntiFraude()
    }
}

const seleccionarRespuesta = (val) => { respuestasTemporales.value = { ...respuestasTemporales.value, [preguntaActual.value.id]: val } }

const comenzarExamen = () => {
    $q.dialog({ 
        title: '🔒 PROTOCOLO DE EVALUACIÓN SEGURA', 
        message: `Estás por iniciar el examen final. Se han activado los protocolos de seguridad RAC 141:
        \n• El cronómetro no se detendrá.
        \n• Salir de esta ventana o cambiar de pestaña generará una alerta de fraude.
        \n• Un segundo intento de salida resultará en la ANULACIÓN y ENVÍO automático del examen.
        \n• Acciones de copiar, pegar e inspección están bloqueadas.
        \n\n¿Confirmas el inicio del examen?`, 
        color: 'red-10', 
        cancel: { label: 'Abortar', flat: true, color: 'white' },
        ok: { label: 'Iniciar Examen', color: 'red-9', unelevated: true },
        persistent: true 
    }).onOk(async () => {
        cargandoExamen.value = true; examMode.value = 'final'
        try {
            const { data } = await api.get(`/aula-virtual/materia/${materiaActiva.value.id}/examen`)
            preguntas.value = data.data; respuestasTemporales.value = {}; currentQuestionIndex.value = 0; dialogExamen.value = true; startTimer(materiaActiva.value.duracion_minutos || 15)
            fraudWarnings.value = 0
            isExamActive.value = true
        } finally { cargandoExamen.value = false }
    })
}

const abrirQuizLeccion = async (lec) => {
    $q.dialog({
        title: '🔒 VALIDACIÓN DE CONOCIMIENTO',
        message: 'Esta evaluación cuenta con monitoreo anti-fraude. Si sales de la ventana, el quiz será enviado automáticamente tras la segunda advertencia. ¿Deseas comenzar?',
        color: 'red-10',
        cancel: { label: 'Cancelar', flat: true, color: 'white' },
        ok: { label: 'Comenzar Quiz', color: 'red-9', unelevated: true },
        persistent: true
    }).onOk(async () => {
        cargandoExamen.value = true
        try {
            const { data } = await api.get(`/aula-virtual/leccion/${lec.id}/quiz`)
            preguntas.value = data.data; leccionActivaQuiz.value = lec; examMode.value = 'quiz'; respuestasTemporales.value = {}; currentQuestionIndex.value = 0; dialogExamen.value = true; startTimer(5)
            fraudWarnings.value = 0
            isExamActive.value = true
        } catch (e) {
            $q.notify({ type: 'negative', message: 'Error cargando el quiz' })
        } finally {
            cargandoExamen.value = false
        }
    })
}

const cerrarTodo = () => {
    const mid = materiaActiva.value.id; materiaActiva.value = null; cargarMaterias().then(() => { const mat = materias.value.find(x => x.id === mid); if (mat) entrarAula(mat) })
}

const abandonarExamen = () => {
    $q.dialog({
        title: '⚠️ ABANDONAR EXAMEN',
        message: 'Si cierras el examen ahora, se enviará con las respuestas que hayas marcado hasta el momento y contará como un intento consumido. ¿Realmente deseas abandonar?',
        color: 'red-10',
        cancel: 'Permanecer en Examen',
        ok: { label: 'Abandonar y Enviar', color: 'red-9', unelevated: true },
        persistent: true
    }).onOk(() => {
        finalizarExamen()
    })
}

const solicitarReintento = () => {
    $q.dialog({
        title: '💳 HABILITACIÓN DE REINTENTO',
        message: `Se ha detectado un intento previo no satisfactorio en <b>${materiaActiva.value.nombre}</b>.
        <br><br>
        Para habilitar un nuevo examen final, debes:<br>
        1. Dirigirte a la <b>Tesorería de la Escuela</b>.<br>
        2. Realizar el pago correspondiente al <b>Derecho de Reintento</b>.<br>
        3. Una vez validado el pago, el sistema desbloqueará automáticamente el acceso.
        <br><br>
        <small class="text-grey-7">Nota: El valor es determinado por las tarifas vigentes de la Tesorería.</small>`,
        html: true,
        ok: { label: 'Entendido', color: 'red-9', unelevated: true },
        persistent: true
    })
}

// ════════════════════════════════════════════════════════════════════
// 🛡️ SISTEMA ANTI-FRAUDE (RAC 141 COMPLIANCE)
// ════════════════════════════════════════════════════════════════════

const setupAntiFraude = () => {
    window.addEventListener('blur', detectFraude)
    document.addEventListener('visibilitychange', detectFraude)
    window.addEventListener('keydown', preventKeyCombos)
    window.addEventListener('beforeunload', preventTabClose)
}

const cleanupAntiFraude = () => {
    window.removeEventListener('blur', detectFraude)
    document.removeEventListener('visibilitychange', detectFraude)
    window.removeEventListener('keydown', preventKeyCombos)
    window.removeEventListener('beforeunload', preventTabClose)
    isExamActive.value = false
}

const preventTabClose = (e) => {
    if (isExamActive.value) {
        e.preventDefault()
        e.returnValue = ''
    }
}

const detectFraude = () => {
    if (!isExamActive.value || !dialogExamen.value) return

    fraudWarnings.value++
    
    if (fraudWarnings.value >= 2) {
        $q.notify({
            type: 'negative',
            message: 'DETECCIÓN DE FRAUDE CRÍTICA: Se ha detectado salida de la ventana. El examen ha sido enviado automáticamente.',
            position: 'center',
            timeout: 8000,
            icon: 'gavel'
        })
        finalizarExamen()
    } else {
        $q.dialog({
            title: '⚠️ ALERTA DE INTEGRIDAD',
            message: 'Has salido de la ventana del examen. Según las regulaciones RAC, esta acción está prohibida. Si vuelves a salir de la ventana, tu examen será ENVIADO Y ANULADO automáticamente.',
            color: 'red-10',
            persistent: true,
            ok: { label: 'Entendido, volver al examen', color: 'red-9', unelevated: true }
        })
    }
}

const preventKeyCombos = (e) => {
    // Bloquear F12, Ctrl+Shift+I, Ctrl+U, Ctrl+C, Ctrl+V, PrintScreen
    if (
        e.key === 'F12' ||
        (e.ctrlKey && e.shiftKey && e.key === 'I') ||
        (e.ctrlKey && e.key === 'u') ||
        (e.ctrlKey && e.key === 'c') ||
        (e.ctrlKey && e.key === 'v') ||
        e.key === 'PrintScreen'
    ) {
        e.preventDefault()
        $q.notify({ type: 'warning', message: 'Acción bloqueada por seguridad del examen.', position: 'top' })
    }
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
onUnmounted(cleanupAntiFraude)
const formatFecha = (f) => {
    if (!f) return ''
    return dayjs(f).format('DD MMM, HH:mm')
}
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
    font-family: 'Cinzel', serif; font-size: 22px; font-weight: 900; color: #fff;
    text-shadow: 0 0 20px rgba(161, 11, 19, 0.2), 0 3px 6px #000;
    letter-spacing: -1px; display: block; line-height: 1.2;
}

.answer-card-premium {
    background: rgba(10, 12, 16, 0.8) !important;
    border: 1px solid rgba(161, 11, 19, 0.2) !important;
    border-radius: 8px !important;
    padding: 8px 16px !important;
    transition: all 0.4s cubic-bezier(0.19, 1, 0.22, 1);
    
    .answer-letter-box {
        width: 44px; height: 44px; border-radius: 4px; display: flex; align-items: center; justify-content: center;
        background: transparent; color: #64748b; font-size: 18px; font-weight: 900; border: 2px solid rgba(161, 11, 19, 0.1);
        flex-shrink: 0; transition: all 0.3s;
    }
    
    .answer-text-content { font-size: 16px; font-weight: 500; color: #94a3b8; line-height: 1.4; flex: 1; margin-left: 20px; }

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

.video-preview-placeholder {
    width: 300px; height: 180px; border-radius: 20px;
    background: linear-gradient(135deg, rgba(161, 11, 19, 0.1) 0%, rgba(0,0,0,0.8) 100%);
    border: 1px solid rgba(161, 11, 19, 0.3);
    margin: 0 auto; position: relative; overflow: hidden;
    &::after {
        content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
        background: repeating-linear-gradient(0deg, rgba(255,255,255,0.03) 0, rgba(255,255,255,0.03) 1px, transparent 1px, transparent 2px);
    }
}

.no-select {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
</style>
