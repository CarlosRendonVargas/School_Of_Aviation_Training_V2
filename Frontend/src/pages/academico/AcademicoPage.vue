<template>
  <q-page class="q-pa-md rac-page">
    <div class="row items-center q-mb-md">
      <q-icon name="school" size="32px" color="teal-4" class="q-mr-sm icon-teal-glow" />
      <div>
        <h1 class="text-h5 text-white font-head q-my-none">Módulo 02: Gestión Académica RAC 141</h1>
        <div class="text-grey-5 font-mono text-caption">Control de Programas (PIA), Expedientes y Progreso de Instrucción</div>
      </div>
    </div>

    <div class="q-mt-md">
      <q-card class="premium-glass-card shadow-12 custom-tabs-card">
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="teal-4"
          indicator-color="teal-4"
          align="justify"
          narrow-indicator
        >
          <q-tab name="programas" icon="menu_book" label="Programas (PIA)" />
          <q-tab name="estudiantes" icon="groups" label="Estudiantes / Expedientes" />
          <q-tab name="notas" icon="grade" label="Calificaciones" />
        </q-tabs>

        <q-separator color="grey-8" />

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
          <!-- PROGRAMAS PANEL -->
          <q-tab-panel name="programas">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Programas de Instrucción Autorizados</div>
                <q-btn color="teal-4" icon="add" label="Nuevo Programa" outline rounded class="glass-btn" @click="abrirNuevoPrograma" />
            </div>

            <div class="row q-col-gutter-md">
                <div v-for="prog in programas" :key="prog.id" class="col-12 col-sm-6 col-md-4">
                    <q-card class="bg-dark-light border-teal-left premium-hover-card">
                        <q-card-section>
                            <div class="row justify-between items-start">
                                <div class="text-overline text-teal-4">{{ prog.codigo }}</div>
                                <q-chip size="xs" color="grey-9" text-color="white">{{ prog.tipo }}</q-chip>
                            </div>
                            <div class="text-h6 q-mt-sm">{{ prog.nombre }}</div>
                            <div class="text-caption text-grey-5 q-mt-xs">{{ prog.descripcion || 'Sin descripción' }}</div>
                        </q-card-section>
                        <q-separator dark inset />
                        <q-card-actions align="right">
                            <q-btn flat color="teal-3" label="Ver Syllabus" size="sm" icon="visibility" @click="verSyllabus(prog)" />
                            <q-btn flat color="grey-5" label="Editar" size="sm" icon="edit" @click="editarPrograma(prog)" />
                        </q-card-actions>
                    </q-card>
                </div>
            </div>
          </q-tab-panel>

          <!-- ESTUDIANTES PANEL -->
          <q-tab-panel name="estudiantes">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Expedientes Estudiantiles (RAC 141.77)</div>
                <div class="row items-center q-gutter-sm">
                    <q-input v-model="filtroBusqueda" dense outlined dark color="teal" placeholder="Buscar por nombre o documento..." class="bg-dark-light" @keyup.enter="cargarEstudiantes">
                        <template v-slot:append>
                            <q-icon name="search" />
                        </template>
                    </q-input>
                </div>
            </div>

            <q-table
              :rows="estudiantes"
              :columns="columnsEstudiantes"
              row-key="id"
              class="rac-table bg-transparent text-white"
              flat
              bordered
              :loading="loadingEstudiantes"
              v-model:pagination="pagination"
              @request="cargarEstudiantes"
            >
              <template v-slot:body-cell-nombre="props">
                <q-td :props="props" class="text-weight-bold">
                  {{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}
                  <div class="text-caption text-grey-6">{{ props.row.num_expediente }}</div>
                </q-td>
              </template>
              <template v-slot:body-cell-estado="props">
                <q-td :props="props">
                  <q-chip :color="getEstadoColor(props.row.estado)" text-color="white" size="sm">
                    {{ props.row.estado.toUpperCase() }}
                  </q-chip>
                </q-td>
              </template>
              <template v-slot:body-cell-progreso="props">
                <q-td :props="props" class="text-center" style="width: 150px">
                  <q-linear-progress :value="0.35" color="teal-4" size="10px" rounded class="q-mt-sm" />
                  <div class="text-caption text-grey-5">35% del curso</div>
                </q-td>
              </template>
              <template v-slot:body-cell-acciones="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="teal-4" icon="account_circle" size="sm" @click="verDetalleEstudiante(props.row)">
                    <q-tooltip>Ver Expediente Completo</q-tooltip>
                  </q-btn>
                  <q-btn flat round color="blue-4" icon="medical_services" size="sm" @click="verCertMedicos(props.row)">
                    <q-tooltip>Certificados Médicos (RAC 61/67)</q-tooltip>
                  </q-btn>
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>

          <!-- NOTAS PANEL -->
          <q-tab-panel name="notas">
             <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Registro de Calificaciones Teóricas</div>
                <q-btn color="orange-8" icon="add_chart" label="Ingresar Nota" outline rounded class="glass-btn" @click="dialogNota = true" />
            </div>
            <p class="text-grey-5 font-mono text-caption">Las notas registradas aquí afectan directamente el cumplimiento del PIA (Programa de Instrucción Autorizado).</p>
            
            <!-- Aquí iría un listado de notas recientes o filtros por curso -->
            <div class="q-pa-xl text-center">
                <q-icon name="pending_actions" size="64px" color="grey-8" />
                <div class="text-grey-7 q-mt-md">Selecciona un programa o estudiante para gestionar calificaciones.</div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>

    <!-- Modal Detalle Expediente -->
    <q-dialog v-model="dialogExpediente" full-width full-height>
      <q-card class="bg-dark text-white">
        <q-toolbar class="bg-teal-9 text-white">
          <q-avatar icon="person" color="teal-7" />
          <q-toolbar-title>
            Expediente Digital: {{ estudianteTemp?.persona?.nombres }} {{ estudianteTemp?.persona?.apellidos }}
            <span class="text-caption q-ml-md font-mono">{{ estudianteTemp?.num_expediente }}</span>
          </q-toolbar-title>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-toolbar>

        <q-card-section class="row q-col-gutter-lg">
            <div class="col-12 col-md-4">
                <q-card class="bg-dark-light bordered">
                    <q-card-section>
                        <div class="text-h6 text-teal-4 q-mb-md">Información General</div>
                        <q-list dense dark padding>
                            <q-item>
                                <q-item-section avatar><q-icon name="fingerprint" color="teal-3" /></q-item-section>
                                <q-item-section>
                                    <q-item-label caption>Documento</q-item-label>
                                    <q-item-label>{{ estudianteTemp?.persona?.num_documento }}</q-item-label>
                                </q-item-section>
                            </q-item>
                            <q-item>
                                <q-item-section avatar><q-icon name="flight_takeoff" color="teal-3" /></q-item-section>
                                <q-item-section>
                                    <q-item-label caption>Programa</q-item-label>
                                    <q-item-label>{{ estudianteTemp?.programa?.nombre }}</q-item-label>
                                </q-item-section>
                            </q-item>
                            <q-item>
                                <q-item-section avatar><q-icon name="event" color="teal-3" /></q-item-section>
                                <q-item-section>
                                    <q-item-label caption>Fecha Ingreso</q-item-label>
                                    <q-item-label>{{ estudianteTemp?.fecha_ingreso }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </q-list>
                    </q-card-section>
                </q-card>
            </div>

            <div class="col-12 col-md-8">
                <q-card class="bg-dark-light bordered">
                    <q-card-section>
                        <div class="text-h6 text-teal-4 q-mb-md">Progreso de Instrucción RAC 61</div>
                        <div class="row q-col-gutter-sm text-center">
                            <div class="col-4">
                                <q-circular-progress
                                    show-value
                                    font-size="12px"
                                    :value="45"
                                    size="80px"
                                    :thickness="0.2"
                                    color="teal-4"
                                    track-color="grey-9"
                                    class="q-ma-md"
                                >
                                    45.5h
                                </q-circular-progress>
                                <div class="text-caption">Horas Voladas</div>
                            </div>
                            <!-- Más estadísticas aquí -->
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal Programa -->
    <q-dialog v-model="dialogPrograma" persistent>
        <q-card class="bg-dark text-white" style="width: 500px; max-width: 80vw;">
            <q-card-section class="bg-teal-9">
                <div class="text-h6">{{ formPrograma.id ? 'Editar Programa' : 'Nuevo Programa' }}</div>
            </q-card-section>
            <q-form @submit.prevent="guardarPrograma">
                <q-card-section class="q-gutter-md">
                    <q-input v-model="formPrograma.codigo" label="Código" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <q-input v-model="formPrograma.nombre" label="Nombre" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <q-select v-model="formPrograma.tipo" :options="['Avión', 'Helicóptero']" label="Tipo" outlined dense dark bg-color="grey-10" />
                    
                    <div class="row q-col-gutter-sm">
                      <div class="col-6"><q-input v-model="formPrograma.horas_tierra_min" type="number" step="0.1" label="Hrs Tierra" outlined dense dark bg-color="grey-10" /></div>
                      <div class="col-6"><q-input v-model="formPrograma.horas_vuelo_min" type="number" step="0.1" label="Hrs Vuelo Total" outlined dense dark bg-color="grey-10" /></div>
                      <div class="col-6"><q-input v-model="formPrograma.horas_dual_min" type="number" step="0.1" label="Hrs Dual" outlined dense dark bg-color="grey-10" /></div>
                      <div class="col-6"><q-input v-model="formPrograma.horas_solo_min" type="number" step="0.1" label="Hrs Solo" outlined dense dark bg-color="grey-10" /></div>
                    </div>
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" color="grey" v-close-popup />
                    <q-btn flat label="Guardar" color="teal-4" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

    <!-- Modal Syllabus -->
    <q-dialog v-model="dialogSyllabus">
        <q-card class="bg-dark text-white" style="width: 500px; max-width: 80vw;">
            <q-card-section class="bg-teal-9 text-white">
                <div class="text-h6">Syllabus de Vuelo Operativo</div>
                <div class="text-caption">{{ programaSeleccionado?.nombre }} ({{ programaSeleccionado?.codigo }})</div>
            </q-card-section>
            
            <q-card-section class="q-pt-md">
                <div class="text-subtitle2 text-teal-4 q-mb-md font-mono text-uppercase">Desglose de Materias y Contenido LMS</div>
                
                <div v-for="etapa in programaSeleccionado?.etapas" :key="etapa.id" class="q-mb-md">
                    <div class="text-caption text-grey-5 q-mb-sm">Etapa {{ etapa.numero }}: {{ etapa.nombre }}</div>
                    <q-list bordered separator class="rounded-borders bg-dark-light">
                        <q-item v-for="materia in etapa.materias" :key="materia.id">
                            <q-item-section>
                                <q-item-label class="text-weight-bold">{{ materia.nombre }}</q-item-label>
                                <q-item-label caption class="text-grey-6">{{ materia.codigo }} · {{ materia.horas }}h</q-item-label>
                            </q-item-section>
                            <q-item-section side>
                                <div class="row q-gutter-xs">
                                    <q-btn flat dense icon="school" color="primary" @click="abrirGestionLms(materia)">
                                        <q-tooltip>Gestionar Temario y Recursos</q-tooltip>
                                    </q-btn>
                                    <q-btn flat dense icon="quiz" color="orange" @click="abrirBancoPreguntas(materia)">
                                        <q-tooltip>Banco de Preguntas</q-tooltip>
                                    </q-btn>
                                </div>
                            </q-item-section>
                        </q-item>
                    </q-list>
                </div>

                <q-separator dark class="q-my-lg" />
                
                <div class="text-subtitle2 text-teal-4 q-mb-sm font-mono text-uppercase">Requisitos Mínimos Generales (RAC 141)</div>
                <q-list bordered separator class="rounded-borders bg-dark-light">
                    <!-- Fase Tierra -->
                    <q-item>
                        <q-item-section avatar>
                            <q-avatar color="teal-9" text-color="teal-3" icon="menu_book" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label class="text-weight-bold">Fase de Tierra (Teórica)</q-item-label>
                            <q-item-label caption class="text-grey-5">Horas presenciales requeridas</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                            <div class="text-h6 text-teal-3">{{ programaSeleccionado?.horas_tierra_min || 0 }}h</div>
                        </q-item-section>
                    </q-item>

                    <!-- Fase Vuelo Total -->
                    <q-item>
                        <q-item-section avatar>
                            <q-avatar color="blue-9" text-color="blue-3" icon="flight_takeoff" />
                        </q-item-section>
                        <q-item-section>
                            <q-item-label class="text-weight-bold">Fase de Vuelo (Práctica)</q-item-label>
                            <q-item-label caption class="text-grey-5">Total de horas de vuelo mínimas</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                            <div class="text-h6 text-blue-3">{{ programaSeleccionado?.horas_vuelo_min || 0 }}h</div>
                        </q-item-section>
                    </q-item>

                </q-list>

                <div class="bg-grey-10 q-pa-sm q-mt-md rounded-borders flex items-center text-grey-5 shadow-1" style="border-left: 3px solid #0d9488">
                    <q-icon name="info" size="20px" class="q-mr-sm text-teal-5" />
                    <span style="font-size: 11px;">Instructores: Use los íconos superiores en cada materia para configurar el aula virtual y el examen.</span>
                </div>
            </q-card-section>

    <!-- Dialogo Gestion LMS Materia -->
    <q-dialog v-model="dialogLmsMateria" persistent>
        <q-card class="bg-dark text-white" style="width: 600px; max-width: 90vw;">
            <q-card-section class="bg-primary">
                <div class="text-h6">Configuración de Aula Virtual</div>
                <div class="text-caption">{{ materiaSeleccionada?.nombre }}</div>
            </q-card-section>
            <q-form @submit.prevent="guardarLmsMateria">
                <q-card-section class="q-gutter-md">
                    <div class="row q-col-gutter-sm">
                        <div class="col-4"><q-input v-model.number="materiaSeleccionada.max_intentos" type="number" label="Máx. Intentos" outlined dark dense bg-color="grey-10" /></div>
                        <div class="col-4"><q-input v-model.number="materiaSeleccionada.costo_reintento" type="number" label="Costo Supl. ($)" outlined dark dense bg-color="grey-10" /></div>
                        <div class="col-4"><q-input v-model.number="materiaSeleccionada.duracion_minutos" type="number" label="Tiempo (Min)" outlined dark dense bg-color="grey-10" /></div>
                    </div>
                    <q-input v-model="materiaSeleccionada.link_meet" label="Enlace Google Meet" outlined dark dense bg-color="grey-10" placeholder="https://meet.google.com/..." />
                    <q-input v-model="materiaSeleccionada.documento_url" label="URL de Documento / PDF" outlined dark dense bg-color="grey-10" placeholder="https://..." />
                    <q-input v-model="materiaSeleccionada.temario" type="textarea" label="Temario de la Materia" outlined dark bg-color="grey-10" placeholder="Desglose de temas a ver..." />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cerrar" color="grey" v-close-popup />
                    <q-btn flat label="Guardar RAC 141" color="primary" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

    <!-- Dialogo Banco de Preguntas -->
    <q-dialog v-model="dialogBancoPreguntas" maximized transition-show="slide-up" transition-hide="slide-down">
        <q-card class="bg-dark text-white">
            <q-toolbar class="bg-orange-9">
                <q-btn flat round dense icon="arrow_back" v-close-popup />
                <q-toolbar-title>Banco de Preguntas: {{ materiaSeleccionada?.nombre }}</q-toolbar-title>
                <q-btn flat icon="add" label="Nueva Pregunta" @click="abrirNuevaPregunta" />
            </q-toolbar>
            
            <q-card-section>
                <q-table
                    :rows="preguntas"
                    :columns="[
                        { name: 'pregunta', label: 'Pregunta', align: 'left', field: 'pregunta' },
                        { name: 'resp', label: 'R. Correcta', align: 'left', field: 'respuesta_correcta' },
                        { name: 'acciones', label: 'Acciones', align: 'right' }
                    ]"
                    flat dark class="bg-transparent"
                    :loading="cargandoPreguntas"
                >
                    <template v-slot:body-cell-pregunta="props">
                        <q-td :props="props">
                            <div style="white-space: normal; max-width: 500px">
                                {{ props.row.pregunta }}
                            </div>
                        </q-td>
                    </template>
                    <template v-slot:body-cell-acciones="props">
                        <q-td :props="props">
                            <q-btn flat icon="edit" color="blue" size="sm" @click="editarPregunta(props.row)" />
                            <q-btn flat icon="delete" color="red" size="sm" @click="eliminarPregunta(props.row.id)" />
                        </q-td>
                    </template>
                </q-table>
            </q-card-section>
        </q-card>
    </q-dialog>

    <!-- Dialogo Form Pregunta -->
    <q-dialog v-model="dialogFormPregunta" persistent>
        <q-card class="bg-dark text-white" style="width: 600px">
            <q-card-section class="bg-orange-9 text-white">
                <div class="text-h6">{{ formPregunta.id ? 'Editar Pregunta' : 'Nueva Pregunta' }}</div>
            </q-card-section>
            <q-form @submit.prevent="guardarPregunta">
                <q-card-section class="q-gutter-sm">
                    <q-input v-model="formPregunta.pregunta" label="Enunciado de la Pregunta" outlined dark type="textarea" bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    
                    <div class="text-subtitle2 text-grey-5 q-mt-sm">Opciones de Respuesta (Mín. 2)</div>
                    <div v-for="(opc, idx) in formPregunta.opciones" :key="idx" class="row q-gutter-xs items-center q-mb-xs">
                        <q-input v-model="formPregunta.opciones[idx]" :label="'Opción ' + (idx + 1)" outlined dark dense class="col" bg-color="grey-10" :rules="[val => !!val || 'Campo no puede estar vacío']" />
                        <q-btn flat round icon="delete" color="red" size="sm" @click="formPregunta.opciones.splice(idx, 1)" v-if="formPregunta.opciones.length > 2" />
                    </div>
                    <q-btn flat icon="add" label="Agregar Alternativa" color="teal" size="sm" class="q-mb-md" @click="formPregunta.opciones.push('')" />

                    <q-select v-model="formPregunta.respuesta_correcta" :options="formPregunta.opciones" label="Selecciona la Respuesta Correcta" outlined dark dense bg-color="grey-10" class="q-mt-md" :rules="[val => !!val || 'Debes marcar cuál es la correcta']" />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" v-close-popup />
                    <q-btn flat label="Guardar Pregunta" color="orange" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

            <q-card-actions align="right" class="bg-dark">
                <q-btn flat label="Cerrar" color="teal-4" v-close-popup />
            </q-card-actions>
        </q-card>
    </q-dialog>

    <!-- Modal Cert Medico -->
    <q-dialog v-model="dialogCertMedico" persistent>
        <q-card class="bg-dark text-white" style="width: 500px; max-width: 80vw;">
            <q-card-section class="bg-blue-9">
                <div class="text-h6">Registrar Certificado Médico</div>
                <div class="text-caption">{{ estudianteTemp?.persona?.nombres }} {{ estudianteTemp?.persona?.apellidos }}</div>
            </q-card-section>
            <q-form @submit.prevent="guardarCertMedico">
                <q-card-section class="q-gutter-md">
                    <q-select v-model="formCert.tipo" :options="['clase_1', 'clase_2', 'clase_3']" label="Clase de Certificado" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <q-input v-model="formCert.numero_certificado" label="Número Certificado" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <div class="row q-col-gutter-sm">
                      <div class="col-6"><q-input v-model="formCert.fecha_emision" type="date" label="Emisión" stack-label outlined dense dark bg-color="grey-10" /></div>
                      <div class="col-6"><q-input v-model="formCert.fecha_vencimiento" type="date" label="Vencimiento" stack-label outlined dense dark bg-color="grey-10" /></div>
                    </div>
                    <q-input v-model="formCert.centro_aeromedico" label="Centro Aeromédico" outlined dense dark bg-color="grey-10" />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" color="grey" v-close-popup />
                    <q-btn flat label="Guardar RAC 67" color="blue-4" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

    <!-- Modal Nota -->
    <q-dialog v-model="dialogNota" persistent>
        <q-card class="bg-dark text-white" style="width: 500px; max-width: 80vw;">
            <q-card-section class="bg-orange-9">
                <div class="text-h6">Ingresar Calificación Teórica</div>
            </q-card-section>
            <q-form @submit.prevent="guardarNota">
                <q-card-section class="q-gutter-md">
                    <q-select v-model="formNota.estudiante_id" :options="estudiantes" option-label="num_expediente" option-value="id" map-options emit-value label="Estudiante" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']">
                         <template v-slot:option="scope">
                            <q-item v-bind="scope.itemProps" dark>
                                <q-item-section>
                                    <q-item-label>{{ scope.opt.persona?.nombres }} {{ scope.opt.persona?.apellidos }}</q-item-label>
                                    <q-item-label caption>{{ scope.opt.num_expediente }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </template>
                        <template v-slot:selected-item="scope">
                            {{ scope.opt.persona?.nombres }} - {{ scope.opt.num_expediente }}
                        </template>
                    </q-select>
                    <!-- Asumiendo materia_id temporal por no tener tabla unida a la vista de materias directamente -->
                    <q-input v-model="formNota.materia_id" type="number" label="ID Materia (Temporal)" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <q-input v-model="formNota.nota" type="number" step="0.1" label="Calificación (0-100)" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                    <q-input v-model="formNota.fecha_evaluacion" type="date" stack-label label="Fecha" outlined dense dark bg-color="grey-10" :rules="[val => !!val || 'Requerido']" />
                </q-card-section>
                <q-card-actions align="right">
                    <q-btn flat label="Cancelar" color="grey" v-close-popup />
                    <q-btn flat label="Registrar" color="orange-4" type="submit" />
                </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const tab = ref('programas')

const programas = ref([])
const estudiantes = ref([])
const loadingEstudiantes = ref(false)
const filtroBusqueda = ref('')
const dialogPrograma = ref(false)
const dialogExpediente = ref(false)
const dialogSyllabus = ref(false)
const dialogCertMedico = ref(false)
const dialogNota = ref(false)

const programaSeleccionado = ref(null)

// --- Gestión LMS / Banco Preguntas ---
const dialogLmsMateria = ref(false)
const dialogBancoPreguntas = ref(false)
const materiaSeleccionada = ref(null)
const preguntas = ref([])
const cargandoPreguntas = ref(false)
const formPregunta = ref({ id: null, pregunta: '', opciones: ['', ''], respuesta_correcta: '', nivel_dificultad: 1 })
const dialogFormPregunta = ref(false)

const abrirGestionLms = (materia) => {
    materiaSeleccionada.value = { 
        max_intentos: 1,
        costo_reintento: 0,
        duracion_minutos: 15,
        ...materia 
    }
    dialogLmsMateria.value = true
}

const guardarLmsMateria = async () => {
    try {
        await api.put(`/gestion-materias/${materiaSeleccionada.value.id}/lms`, materiaSeleccionada.value)
        $q.notify({ color: 'positive', message: 'Contenido LMS actualizado' })
        dialogLmsMateria.value = false
        cargarProgramas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al actualizar contenido' })
    }
}

const abrirBancoPreguntas = async (materia) => {
    materiaSeleccionada.value = materia
    dialogBancoPreguntas.value = true
    cargarPreguntas()
}

const cargarPreguntas = async () => {
    cargandoPreguntas.value = true
    try {
        const { data } = await api.get(`/gestion-materias/${materiaSeleccionada.value.id}/preguntas`)
        preguntas.value = data.data
    } finally {
        cargandoPreguntas.value = false
    }
}

const abrirNuevaPregunta = () => {
    formPregunta.value = { id: null, pregunta: '', opciones: ['', '', '', ''], respuesta_correcta: '', nivel_dificultad: 1 }
    dialogFormPregunta.value = true
}

const editarPregunta = (preg) => {
    formPregunta.value = { ...preg }
    dialogFormPregunta.value = true
}

const guardarPregunta = async () => {
    try {
        if (formPregunta.value.id) {
            await api.put(`/gestion-materias/${materiaSeleccionada.value.id}/preguntas/${formPregunta.value.id}`, formPregunta.value)
        } else {
            await api.post(`/gestion-materias/${materiaSeleccionada.value.id}/preguntas`, formPregunta.value)
        }
        $q.notify({ color: 'positive', message: 'Pregunta guardada' })
        dialogFormPregunta.value = false
        cargarPreguntas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al guardar pregunta' })
    }
}

const eliminarPregunta = async (id) => {
    $q.dialog({
        title: 'Confirmar',
        message: '¿Deseas eliminar esta pregunta?',
        cancel: true,
        persistent: true
    }).onOk(async () => {
        await api.delete(`/gestion-materias/${materiaSeleccionada.value.id}/preguntas/${id}`)
        cargarPreguntas()
    })
}

const formPrograma = ref({
    id: null, codigo: '', nombre: '', tipo: 'Avión',
    horas_tierra_min: 0, horas_vuelo_min: 0,
    horas_dual_min: 0, horas_solo_min: 0
})

const estudianteTemp = ref(null)

const formCert = ref({
    tipo: 'clase_1', numero_certificado: '', fecha_emision: '', fecha_vencimiento: '', centro_aeromedico: ''
})

const formNota = ref({
    estudiante_id: null, materia_id: null, nota: null, fecha_evaluacion: ''
})

const pagination = ref({
    sortBy: 'id',
    descending: true,
    page: 1,
    rowsPerPage: 10,
    rowsNumber: 0
})

const columnsEstudiantes = [
  { name: 'nombre', label: 'Estudiante / Expediente', align: 'left' },
  { name: 'programa', label: 'Programa Inscrito', align: 'left', field: row => row.programa?.nombre },
  { name: 'estado', label: 'Estado RAC', align: 'center', field: 'estado' },
  { name: 'progreso', label: 'Progreso PIA', align: 'center' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const cargarProgramas = async () => {
    try {
        const { data } = await api.get('/programas')
        programas.value = data.data || []
    } catch (e) {
        console.error('Error cargando programas', e)
    }
}

const cargarEstudiantes = async (props) => {
    const { page, rowsPerPage, sortBy, descending } = props?.pagination || pagination.value
    loadingEstudiantes.value = true
    try {
        const { data } = await api.get('/estudiantes', {
            params: {
                page,
                per_page: rowsPerPage,
                buscar: filtroBusqueda.value
            }
        })
        estudiantes.value = data.data?.data || data.data || []
        pagination.value.rowsNumber = data.data?.total || estudiantes.value.length
        pagination.value.page = page
        pagination.value.rowsPerPage = rowsPerPage
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error cargando listado de estudiantes' })
    } finally {
        loadingEstudiantes.value = false
    }
}

const getEstadoColor = (estado) => {
    switch(estado) {
        case 'activo': return 'green-8'
        case 'suspendido': return 'orange-9'
        case 'graduado': return 'blue-8'
        case 'retirado': return 'red-9'
        default: return 'grey-7'
    }
}

const verDetalleEstudiante = async (row) => {
    estudianteTemp.value = row
    dialogExpediente.value = true
    try {
        const { data } = await api.get(`/estudiantes/${row.id}/expediente`)
        estudianteTemp.value = data.data.estudiante
    } catch (e) {
        console.error('Cant load full dossier', e)
    }
}

const verCertMedicos = (row) => {
    estudianteTemp.value = row
    formCert.value = { tipo: 'clase_1', numero_certificado: '', fecha_emision: '', fecha_vencimiento: '', centro_aeromedico: '' }
    dialogCertMedico.value = true
}

const guardarCertMedico = async () => {
    try {
        await api.post(`/estudiantes/${estudianteTemp.value.id}/cert-medicos`, formCert.value)
        $q.notify({ color: 'positive', message: 'Certificado guardado con éxito' })
        dialogCertMedico.value = false
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al registrar certificato médico' })
    }
}

const abrirNuevoPrograma = () => {
    formPrograma.value = { id: null, codigo: '', nombre: '', tipo: 'Avión', horas_tierra_min: 0, horas_vuelo_min: 0, horas_dual_min: 0, horas_solo_min: 0 }
    dialogPrograma.value = true
}

const editarPrograma = (prog) => {
    formPrograma.value = { ...prog }
    dialogPrograma.value = true
}

const guardarPrograma = async () => {
    try {
        if (formPrograma.value.id) {
            await api.put(`/programas/${formPrograma.value.id}`, formPrograma.value)
        } else {
            await api.post('/programas', formPrograma.value)
        }
        $q.notify({ color: 'positive', message: 'Programa guardado' })
        dialogPrograma.value = false
        cargarProgramas()
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al guardar el programa' })
    }
}

const verSyllabus = (prog) => {
    programaSeleccionado.value = prog
    dialogSyllabus.value = true
}

const guardarNota = async () => {
    try {
        await api.post(`/estudiantes/${formNota.value.estudiante_id}/notas`, formNota.value)
        $q.notify({ color: 'positive', message: 'Calificación registrada' })
        dialogNota.value = false
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error al registrar calificación' })
    }
}

onMounted(() => {
    cargarProgramas()
    cargarEstudiantes()
})


</script>

<script>
// Estilos adicionales
</script>

<style lang="scss" scoped>
.rac-page { background: #0a0c10; min-height: 100vh; }
.font-head { font-family: 'Syne', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }
.icon-teal-glow { filter: drop-shadow(0 0 10px rgba(45, 212, 191, 0.7)); }

.premium-glass-card {
  background: rgba(255, 255, 255, 0.02);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 12px;
}

.bg-dark-light { background: rgba(255, 255, 255, 0.04); }
.border-teal-left { border-left: 3px solid #2dd4bf; }

.premium-hover-card {
    transition: all 0.3s ease;
    &:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.07);
        box-shadow: 0 10px 20px rgba(0,0,0,0.4);
    }
}

.glass-btn {
  background: rgba(255,255,255, 0.03);
  backdrop-filter: blur(4px);
  &:hover {
    background: rgba(255,255,255, 0.08);
  }
}
</style>
