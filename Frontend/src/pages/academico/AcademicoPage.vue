<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Instrucción Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="school" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">CENTRO DE INSTRUCCIÓN AERONÁUTICA · RAC 141</div>
          <h1 class="rac-page-title">Gestión Académica</h1>
        </div>
      </div>
    </div>

    <!-- ══ Contenedor Principal (Tabs de Cristal) ══ -->
    <q-card class="premium-glass-card overflow-hidden shadow-24 rounded-20 bonus-grid">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-5 border-bottom-border"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        style="padding-left: 10px"
      >
        <q-tab name="programas"   icon="menu_book"      :label="$q.screen.gt.xs ? 'Programas (PIA)' : ''" />
        <q-tab name="estudiantes" icon="groups"         :label="$q.screen.gt.xs ? 'Archivo Digital' : ''" />
        <q-tab name="vuelo"       icon="flight_takeoff" :label="$q.screen.gt.xs ? 'Fase de Vuelo' : ''" />
        <q-tab name="tesoreria"   icon="payments"       :label="$q.screen.gt.xs ? 'Habilitaciones' : ''" />
        <q-tab name="planes"        icon="fact_check"      :label="$q.screen.gt.xs ? 'Planes de Clase' : ''" />
        <q-tab name="endorsements"  icon="flight_takeoff"  :label="$q.screen.gt.xs ? 'Endorsements' : ''" />
        <q-tab name="certificados"  icon="workspace_premium" :label="$q.screen.gt.xs ? 'Certificados' : ''" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white" style="min-height:600px">
        
        <!-- ════ SECCIÓN: PROGRAMAS (PIA) ════ -->
        <q-tab-panel name="programas" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
            <div>
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Programas Autorizados UAEAC</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">PPA / PCA / IFR / ME · Acreditados por la UAEAC · RAC 141</div>
            </div>
            <q-btn color="red-9" icon="add" label="Nuevo Registro PIA"
              class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder"
              @click="abrirNuevoPrograma" />
          </div>

          <div v-if="loadingProgramas" class="row justify-center q-pa-xl">
            <q-spinner-dots color="red-9" size="48px" />
          </div>

          <div v-else-if="!programas.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
            <q-icon name="menu_book" size="80px" class="opacity-10 q-mb-md" />
            <div>Sin programas PIA registrados.</div>
          </div>

          <div v-else class="row q-col-gutter-lg">
            <div v-for="(prog, idx) in programas" :key="prog.id"
              class="col-12 col-sm-6 col-lg-4 animate-slide-up"
              :style="`animation-delay: ${idx * 0.07}s`">

              <q-card class="premium-glass-card border-red-low hover-card h-full flex column no-wrap shadow-24 rounded-20">
                <q-card-section class="q-pa-lg col">
                  <div class="row justify-between items-start q-mb-md">
                    <q-badge color="red-10" class="font-mono text-weight-bolder q-px-md q-py-xs shadow-24" :label="prog.codigo" />
                    <div class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">{{ prog.tipo || 'ENTRENAMIENTO' }}</div>
                  </div>
                  <div class="text-h6 text-white font-head text-weight-bolder q-mb-sm line-height-1" style="font-size: 18px">{{ prog.nombre }}</div>
                  <div class="text-caption text-grey-6 line-height-1 q-mb-lg" style="min-height: 40px">
                    {{ prog.descripcion || 'Manual de instrucción de vuelo y tierra aprobado según RAC 141.' }}
                  </div>
                  
                  <div class="row q-col-gutter-sm bg-black-20 rounded-12 q-pa-sm shadow-inner border-red-low">
                    <div class="col-6 text-left">
                      <div class="text-caption text-grey-7 font-mono uppercase tracking-widest" style="font-size:8px">VUELO</div>
                      <div class="text-subtitle1 text-red-9 text-weight-bolder font-mono">{{ prog.horas_vuelo_min }}<span class="text-caption text-grey-6">H</span></div>
                    </div>
                    <div class="col-6 text-right">
                      <div class="text-caption text-grey-7 font-mono uppercase tracking-widest" style="font-size:8px">TIERRA</div>
                      <div class="text-subtitle1 text-white text-weight-bolder font-mono">{{ prog.horas_tierra_min }}<span class="text-caption text-grey-6">H</span></div>
                    </div>
                  </div>
                </q-card-section>
                
                <q-separator dark class="opacity-5" />
                
                <q-card-actions align="between" class="q-px-lg q-py-md">
                  <div class="row q-gutter-xs">
                    <q-btn flat round color="red-9" icon="delete" size="sm" @click.stop="eliminarPrograma(prog)" />
                    <q-btn flat round color="grey-6" icon="edit" size="sm" @click.stop="editarPrograma(prog)" />
                  </div>
                  <q-btn flat color="red-9" label="Syllabus RAC 141" icon="auto_stories" size="sm"
                    @click.stop="verSyllabus(prog)" no-caps
                    class="font-mono text-weight-bolder" />
                </q-card-actions>
              </q-card>
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ SECCIÓN: EXPEDIENTES DIGITALES ════ -->
        <q-tab-panel name="estudiantes" class="q-pa-xl">
          <div class="row items-center justify-between q-mb-xl flex-wrap q-gutter-y-md">
            <div class="col-12 col-sm-auto">
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Archivo Académico (RAC 141.77)</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">Control Central de Alumnos y Trazabilidad Académica</div>
            </div>
            <div class="col-12 col-sm-auto row q-gutter-sm">
              <q-input v-model="filtroBusqueda" dense filled dark class="premium-input-login"
                style="width:100%; min-width:280px" placeholder="Búsqueda de expediente..."
                @keyup.enter="cargarDatos">
                <template #prepend><q-icon name="manage_search" color="red-9" /></template>
              </q-input>
              <q-btn color="red-10" icon="person_add" label="Matricular Cadete" @click="abrirNuevoEstudiante" class="premium-btn q-px-md" />
            </div>
          </div>

          <q-table :rows="estudiantes" :columns="columnsEstudiantes" row-key="id"
            dark class="rac-table shadow-24 rounded-20" flat
            :loading="loadingEstudiantes" :rows-per-page-options="[10,20,50]"
            :grid="$q.screen.lt.md">
            
            <!-- Plantilla de Grid para Móvil -->
            <template v-slot:item="props">
              <div class="col-12 q-pa-sm">
                <q-card class="premium-glass-card shadow-24 border-red-low q-pa-md hover-card" style="border-radius:15px">
                  <q-card-section class="q-pa-none">
                    <div class="row items-center justify-between q-mb-md">
                      <div class="row items-center q-gutter-md">
                        <q-avatar size="42px" class="shadow-24 border-red-low">
                          <img :src="`https://ui-avatars.com/api/?name=${props.row.persona?.nombres}&background=A10B13&color=fff`" />
                        </q-avatar>
                        <div>
                          <div class="text-weight-bolder text-white font-head" style="font-size:16px">{{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}</div>
                          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">EXP: {{ props.row.num_expediente }}</div>
                        </div>
                      </div>
                      <q-badge outline :color="getEstadoColor(props.row.estado)"
                        :label="props.row.estado?.toUpperCase()"
                        class="text-weight-bolder font-mono q-px-md q-py-xs shadow-24" />
                    </div>
                    
                    <div class="row q-col-gutter-sm bg-black-20 rounded-12 q-pa-sm q-mb-md border-red-low">
                      <div class="col-12">
                        <div class="text-caption text-grey-7 font-mono uppercase tracking-widest" style="font-size:8px">PROGRAMA ASIGNADO</div>
                        <div class="text-subtitle2 text-grey-3 font-head ellipsis">{{ props.row.programa?.nombre || 'PROGRAMA NO ASIGNADO' }}</div>
                      </div>
                    </div>

                    <div class="row justify-between items-center">
                      <div class="text-caption text-grey-6 font-mono" style="font-size:10px">ING: {{ formatFechaCO(props.row.fecha_ingreso) }}</div>
                      <div class="row q-gutter-x-xs">
                        <q-btn flat round color="emerald" icon="history_edu" size="sm" @click="abrirNuevaNota(props.row)">
                           <q-tooltip>Nota</q-tooltip>
                        </q-btn>
                        <q-btn flat round color="blue-9" icon="medical_services" size="sm" @click="abrirNuevoCertificado(props.row)">
                           <q-tooltip>Salud</q-tooltip>
                        </q-btn>
                        <q-btn flat round color="red-9" icon="visibility" size="md"
                          @click="$router.push({ name: 'estudiante-detalle', params: { id: props.row.id } })"
                          class="shadow-inner">
                           <q-tooltip>Ver Expediente</q-tooltip>
                        </q-btn>
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </template>
            <template #body-cell-nombre="props">
              <q-td :props="props">
                <div class="row items-center q-gutter-md cursor-pointer" @click="$router.push(`/estudiantes/${props.row.id}`)">
                  <q-avatar size="36px" class="shadow-24 border-red-low">
                    <img :src="`https://ui-avatars.com/api/?name=${props.row.persona?.nombres}&background=A10B13&color=fff`" />
                  </q-avatar>
                  <div>
                    <div class="text-weight-bolder text-white font-head hover-red" style="font-size:15px">{{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}</div>
                    <div class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:9px">EXP: {{ props.row.num_expediente }}</div>
                  </div>
                </div>
              </q-td>
            </template>
            <template #body-cell-estado="props">
              <q-td :props="props" class="text-center">
                <q-badge outline :color="getEstadoColor(props.row.estado)"
                  :label="$q.screen.gt.xs ? props.row.estado?.toUpperCase() : props.row.estado?.slice(0,3).toUpperCase()"
                  class="text-weight-bolder font-mono q-px-md q-py-xs shadow-24" />
              </q-td>
            </template>
            <template #body-cell-acciones="props">
              <q-td :props="props" class="text-right">
                <div class="row items-center justify-end q-gutter-sm">
                  <q-btn flat round color="emerald" icon="history_edu" size="sm" @click="abrirNuevaNota(props.row)" class="shadow-inner">
                    <q-tooltip class="bg-emerald font-mono uppercase">Registrar Calificación</q-tooltip>
                  </q-btn>
                  <q-btn flat round color="blue-9" icon="medical_services" size="sm" @click="abrirNuevoCertificado(props.row)" class="shadow-inner">
                    <q-tooltip class="bg-blue-9 font-mono uppercase">Registrar Cert. Médico</q-tooltip>
                  </q-btn>
                    <q-btn flat round color="red-9" icon="visibility" size="md"
                      @click="$router.push({ name: 'estudiante-detalle', params: { id: props.row.id } })"
                      class="shadow-inner premium-hover-card">
                    <q-tooltip class="bg-dark font-mono uppercase">Ver Detalle Académico</q-tooltip>
                  </q-btn>
                </div>
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ SECCIÓN: FASE DE VUELO ════ -->
        <q-tab-panel name="vuelo" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
            <div>
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Registro de Misiones de Instrucción</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">Control de Instrucción Práctica y Calificaciones RAC 141</div>
            </div>
            <q-btn color="red-9" icon="flight_takeoff" label="Autorizar Nueva Misión"
              class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder"
              @click="abrirRegistroVuelo" />
          </div>

          <q-table :rows="misionesVuelo" :columns="columnsVuelos" row-key="id"
            dark class="rac-table shadow-24 rounded-20" flat
            :loading="loadingVuelo" :rows-per-page-options="[10,20,50]"
            :grid="$q.screen.lt.md">

            <!-- Plantilla de Grid para Misiones en Móvil -->
            <template v-slot:item="props">
              <div class="col-12 q-pa-sm">
                <q-card class="premium-glass-card shadow-24 border-red-low q-pa-md hover-card" style="border-radius:15px">
                  <div class="row items-center justify-between q-mb-md">
                    <div class="column">
                      <div class="text-caption text-grey-6 font-mono" style="font-size:9px">{{ formatFechaCO(props.row.fecha) }}</div>
                      <div class="text-weight-bolder text-white font-head" style="font-size:16px">{{ props.row.estudiante?.persona?.nombres }}</div>
                    </div>
                    <q-badge outline color="red-9" :label="props.row.matricula" class="font-mono text-weight-bolder" />
                  </div>

                  <div class="row q-col-gutter-sm bg-black-20 rounded-12 q-pa-sm q-mb-md border-red-low items-center">
                    <div class="col-4 text-center">
                      <div class="text-caption text-grey-7 font-mono" style="font-size:7px">DURACIÓN</div>
                      <div class="text-h6 text-red-9 font-mono text-weight-bolder">{{ Number(props.row.horas || 0).toFixed(1) }}H</div>
                    </div>
                    <div class="col-4 text-center border-left-border">
                      <div class="text-caption text-grey-7 font-mono" style="font-size:7px">INSTRUCTOR</div>
                      <div class="text-caption text-white font-head ellipsis">{{ props.row.instructor?.persona?.apellidos || 'N/A' }}</div>
                    </div>
                    <div class="col-4 text-center border-left-border">
                      <div class="text-caption text-grey-7 font-mono" style="font-size:7px">RAC</div>
                      <q-badge :color="props.row.calificacion === 'S' ? 'emerald' : 'red-10'"
                        :label="props.row.calificacion || 'N/A'"
                        class="font-mono text-weight-bolder q-px-sm" />
                    </div>
                  </div>

                  <div class="row justify-between items-center no-wrap">
                    <div class="text-caption text-grey-6 font-mono ellipsis" style="font-size:9px">OB: {{ props.row.observaciones || 'S/O' }}</div>
                    <q-btn flat round color="red-9" icon="visibility" size="sm" class="shrink-0" 
                      @click="$router.push({ name: 'estudiante-detalle', params: { id: props.row.estudiante_id } })">
                       <q-tooltip>Ver Académico</q-tooltip>
                    </q-btn>
                  </div>
                </q-card>
              </div>
            </template>
            <template #body-cell-fecha="props">
              <q-td :props="props" class="font-mono text-grey-3">{{ formatFechaCO(props.row.fecha) }}</q-td>
            </template>
            <template #body-cell-matricula="props">
              <q-td :props="props" class="text-center">
                <q-badge outline color="red-9" :label="props.row.matricula"
                  class="font-mono text-weight-bolder q-px-lg" />
              </q-td>
            </template>
            <template #body-cell-duracion="props">
              <q-td :props="props" class="text-right font-mono text-red-9 text-weight-bolder text-h6">
                {{ Number(props.row.horas || 0).toFixed(1) }}H
              </q-td>
            </template>
            <template #body-cell-calificacion="props">
              <q-td :props="props" class="text-center">
                <q-badge :color="props.row.calificacion === 'S' ? 'emerald' : (props.row.calificacion === 'NA' ? 'grey-8' : 'red-10')"
                  :label="props.row.calificacion || 'N/A'"
                  class="font-mono text-weight-bolder q-px-lg shadow-24" />
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ SECCIÓN: TESORERÍA / HABILITACIONES ════ -->
        <q-tab-panel name="tesoreria" class="q-pa-none">
          <div class="row q-col-gutter-lg q-pa-lg">
            <div class="col-12 col-md-4">
              <q-card class="premium-glass-card border-red-low shadow-24">
                <q-card-section class="q-pa-xl">
                  <div class="text-h6 font-head text-weight-bolder text-white q-mb-lg">Buscador de Alumnos</div>
                  <q-input 
                    v-model="filtroTesore" 
                    dark filled 
                    label="Nombre o Documento" 
                    class="premium-input-login q-mb-md"
                    @keyup.enter="buscarAlumnoTesore"
                  >
                    <template v-slot:append>
                      <q-btn flat round icon="search" @click="buscarAlumnoTesore" color="red-9" />
                    </template>
                  </q-input>

                  <q-list dark separator class="bg-black-20 rounded-12 shadow-inner" v-if="alumnosTesore.length">
                    <q-item v-for="est in alumnosTesore" :key="est.id" clickable @click="seleccionarEstTesore(est)" :active="estSelTesore?.id === est.id" active-class="bg-red-10-opacity">
                      <q-item-section>
                        <q-item-label class="text-weight-bold font-head">{{ est.persona.nombres }} {{ est.persona.apellidos }}</q-item-label>
                        <q-item-label caption class="text-grey-6 font-mono">EXP: {{ est.num_expediente }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-12 col-md-8">
              <q-card v-if="estSelTesore" class="premium-glass-card border-red-low shadow-24 animate-fade">
                <q-card-section class="q-pa-xl">
                  <div class="row items-center justify-between q-mb-xl">
                    <div>
                      <div class="text-h5 font-head text-weight-bolder text-white">{{ estSelTesore.persona.nombres }} {{ estSelTesore.persona.apellidos }}</div>
                      <div class="text-grey-6 font-mono uppercase" style="font-size:10px">GESTIÓN DE REINTENTOS PARA: {{ estSelTesore.programa?.nombre }}</div>
                    </div>
                    <q-badge color="red-10" class="q-pa-sm font-mono text-weight-bolder">{{ estSelTesore.estado }}</q-badge>
                  </div>

                  <div class="row q-col-gutter-md">
                    <div v-for="mat in materiasEstTesore" :key="mat.id" class="col-12 col-sm-6">
                      <div class="premium-glass-card q-pa-lg border-red-low bg-black-10 hover-row flex items-center justify-between rounded-12 h-full">
                        <div>
                          <div class="text-weight-bold text-white font-head" style="font-size:15px; line-height: 1.2;">{{ mat.nombre }}</div>
                          <div class="text-caption text-grey-6 font-mono q-mt-sm">Nota Max: <span class="text-red-9 font-weight-bolder">{{ mat.nota_max || '0' }}%</span> • Intentos: {{ mat.intentos }}</div>
                        </div>
                        <div class="q-ml-sm">
                           <q-btn 
                             v-if="mat.intentos > 0 && mat.nota_max < 75 && !mat.habilitado_manual"
                             color="orange-10" 
                             icon="payments" 
                             label="Habilitar" 
                             size="sm" 
                             class="premium-btn shadow-24"
                             @click="abrirDialogHabilitar(mat)"
                           />
                           <q-btn 
                             v-else-if="mat.intentos > 0 && mat.nota_max < 75 && mat.habilitado_manual"
                             color="blue-9" 
                             icon="check_circle" 
                             label="Habilitado" 
                             size="sm" 
                             class="premium-btn shadow-24"
                             disable
                           />
                           <q-icon v-else-if="mat.nota_max >= 75" name="verified" color="emerald" size="32px" class="glow-primary" />
                           <span v-else class="text-grey-8 font-mono" style="font-size:9px">SIN INTENTOS</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
              <div v-else class="flex flex-center full-height opacity-30">
                <div class="text-center">
                  <q-icon name="payments" size="100px" color="grey-8" />
                  <div class="text-h6 font-head">SELECCIONE UN ALUMNO</div>
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ SECCIÓN: PLANES DE CLASE ════ -->
        <q-tab-panel name="planes" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
            <div>
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Syllabus & Planes de Clase</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">Planificación Diaria de Instructores</div>
            </div>
            <q-btn color="red-9" icon="add" label="Crear Plan de Clase"
              class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder"
              @click="abrirNuevoPlanClase" />
          </div>

          <div v-if="loadingPlanes" class="row justify-center q-pa-xl">
             <q-spinner-dots color="red-9" size="48px" />
          </div>

          <div v-else-if="!planesClase.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
             <q-icon name="fact_check" size="80px" class="opacity-10 q-mb-md" />
             <div>No hay planes de clase registrados.</div>
          </div>

          <div v-else class="row q-col-gutter-lg">
             <div v-for="plan in planesClase" :key="plan.id" class="col-12 col-md-6 col-lg-4">
                <q-card class="premium-glass-card hover-card border-red-low shadow-24 h-full flex column rounded-20">
                   <q-card-section class="q-pa-lg col">
                      <div class="row items-center justify-between q-mb-md">
                         <div class="text-caption text-grey-6 font-mono">{{ formatFechaCO(plan.fecha) }}</div>
                         <q-badge outline :color="plan.estado === 'realizada' ? 'emerald' : (plan.estado === 'cancelada' ? 'red-9' : 'blue-9')" :label="plan.estado?.toUpperCase() || 'PLANIFICADA'" class="font-mono" />
                      </div>
                      <div class="text-h6 text-white font-head text-weight-bolder ellipsis line-height-1">{{ plan.materia?.nombre || 'Clase sin materia' }}</div>
                      <div class="text-subtitle2 text-red-9 font-mono q-mt-xs q-mb-lg ellipsis">Instructor: {{ plan.instructor?.persona?.apellidos || 'No Asignado' }}</div>

                      <div class="text-caption text-grey-5 ellipsis-2-lines q-mb-md" style="min-height: 35px">{{ plan.objetivos || plan.contenido || 'Sin descripción técnica.' }}</div>

                      <div class="row q-col-gutter-sm bg-black-20 rounded-12 q-pa-sm border-red-low">
                         <div class="col-6 text-center">
                            <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:8px">DURACIÓN</div>
                            <div class="text-subtitle1 text-white text-weight-bolder font-mono">{{ plan.duracion_min }}<span class="text-caption text-grey-6">M</span></div>
                         </div>
                         <div class="col-6 text-center border-left-border">
                            <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:8px">RECURSOS</div>
                            <q-icon name="devices" color="grey-5" size="18px" class="q-mt-xs">
                               <q-tooltip>{{ plan.recursos || 'Ninguno' }}</q-tooltip>
                            </q-icon>
                         </div>
                      </div>
                   </q-card-section>
                   <q-separator dark class="opacity-5" />
                   <q-card-actions align="between" class="q-px-lg q-py-md">
                      <q-btn flat round color="red-9" icon="delete" size="sm" @click="eliminarPlanClase(plan.id)" />
                      <q-btn flat round color="white" icon="edit" size="sm" @click="editarPlanClase(plan)" />
                   </q-card-actions>
                </q-card>
             </div>
          </div>
        </q-tab-panel>

        <!-- ════ ENDORSEMENTS ════ -->
        <q-tab-panel name="endorsements" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
            <div>
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Endorsements de Vuelo Solo</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">RAC 141 — Autorización de primer vuelo solo</div>
            </div>
            <q-btn color="red-9" icon="open_in_new" label="Ver Módulo Completo"
              class="premium-btn shadow-24 q-px-xl" no-caps
              @click="$router.push('/endorsements')" />
          </div>
          <div v-if="loadingEndorsements" class="text-center q-pa-xl">
            <q-spinner-dots color="red-9" size="48px" />
          </div>
          <div v-else-if="!endorsements.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
            <q-icon name="flight_takeoff" size="80px" class="opacity-10 q-mb-md" /><br>
            No hay endorsements registrados.
          </div>
          <q-table v-else
            :rows="endorsements" :columns="colsEndorsements" row-key="id"
            flat dark class="rac-table shadow-24"
            :pagination="{ rowsPerPage: 10 }"
          >
            <template #body-cell-tipo="{ row }">
              <q-td><q-badge color="primary" :label="endTipoLabel(row.tipo)" /></q-td>
            </template>
            <template #body-cell-firma="{ row }">
              <q-td class="text-center">
                <q-icon :name="row.firma_instructor ? 'verified' : 'pending'" :color="row.firma_instructor ? 'positive' : 'warning'" />
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ CERTIFICADOS ════ -->
        <q-tab-panel name="certificados" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
            <div>
              <div class="text-h5 font-head text-weight-bolder text-white uppercase tracking-tighter">Certificados y Constancias</div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-xs" style="font-size:10px">RAC 141.77 — Documentos emitidos</div>
            </div>
            <q-btn color="red-9" icon="open_in_new" label="Ver Módulo Completo"
              class="premium-btn shadow-24 q-px-xl" no-caps
              @click="$router.push('/certificados')" />
          </div>
          <div v-if="loadingCerts" class="text-center q-pa-xl">
            <q-spinner-dots color="red-9" size="48px" />
          </div>
          <div v-else-if="!certRecientes.length" class="text-center q-pa-xl text-grey-7 font-mono uppercase tracking-widest">
            <q-icon name="workspace_premium" size="80px" class="opacity-10 q-mb-md" /><br>
            No hay certificados emitidos.
          </div>
          <q-table v-else
            :rows="certRecientes" :columns="colsCerts" row-key="id"
            flat dark class="rac-table shadow-24"
            :pagination="{ rowsPerPage: 10 }"
          />
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

    <q-dialog v-model="dialogPrograma" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'" style="width:min(900px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
          <div class="row items-center">
            <q-icon name="edit_note" color="red-9" size="32px" class="q-mr-md glow-primary" />
            <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">
              {{ modoPrograma === 'crear' ? 'Nuevo Programa UAEAC' : 'Actualizar PIA' }}
            </div>
          </div>
          <q-btn flat round dense icon="close" @click="dialogPrograma = false" color="grey-6" class="bg-black-20 hover-red" />
        </div>

        <q-form @submit.prevent="guardarPrograma" class="q-gutter-y-lg">
          <div class="row q-col-gutter-lg">
            <div class="col-12 col-md-4">
              <q-input v-model="progForm.codigo" label="CÓDIGO UAEAC" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-select v-model="progForm.tipo" :options="['PPL', 'CPL', 'ATPL', 'HABILITACION']" label="CATEGORÍA RAC" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="progForm.rac_referencia" label="RAC REFERENCIA" placeholder="61.109" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-12">
              <q-input v-model="progForm.nombre" label="NOMBRE DEL PROGRAMA ACADÉMICO" filled dark class="premium-input-login" stack-label />
            </div>

            <div class="col-12 text-grey-6 font-mono text-uppercase q-mt-md" style="font-size:10px; letter-spacing: 2px;">REQUISITOS MÍNIMOS DE EXPERIENCIA (HORAS)</div>
            
            <div class="col-6 col-md-3">
              <q-input v-model.number="progForm.horas_vuelo_min" type="number" label="VUELO TOTAL" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-3">
              <q-input v-model.number="progForm.horas_tierra_min" type="number" label="Tierra/Teoría" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-3">
              <q-input v-model.number="progForm.horas_dual_min" type="number" label="INSTR. DUAL" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-3">
              <q-input v-model.number="progForm.horas_solo_min" type="number" label="VUELO SOLO" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-4">
              <q-input v-model.number="progForm.horas_ifr_min" type="number" label="HORAS IFR/SIM" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-4">
              <q-input v-model.number="progForm.horas_noche_min" type="number" label="HORAS NOCHE" filled dark class="premium-input-login" stack-label />
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model.number="progForm.horas_sim_max" type="number" label="MÁX SIMULADOR" filled dark class="premium-input-login" stack-label />
            </div>
          </div>
          <q-btn type="submit" color="red-10" class="full-width premium-btn q-py-lg shadow-24"
            label="Guardar Configuración de Programa" icon="save"
            :loading="guardandoPrograma" />
        </q-form>
      </q-card>
    </q-dialog>

    <!-- ════ DIÁLOGO: SYLLABUS DEL PROGRAMA ════ -->
    <q-dialog v-model="dialogSyllabus" persistent :maximized="$q.screen.lt.md" transition-show="slide-up" transition-hide="slide-down" backdrop-filter="blur(25px)">
      <q-card class="bg-dark-page text-white overflow-hidden flex column" 
        :class="$q.screen.gt.sm ? 'premium-glass-card shadow-24 border-red-top rounded-20' : ''" 
        :style="$q.screen.gt.sm ? 'width:min(1200px, 95vw); height: 90vh;' : 'height: 100vh;'">

        
        <!-- Navbar Superior (Syllabus) -->
        <q-toolbar class="border-bottom-border bg-black-20 q-py-md no-wrap" :class="$q.screen.lt.md ? 'q-px-md' : 'q-px-xl'">
            <q-btn flat round dense icon="arrow_back" @click="dialogSyllabus = false" v-if="$q.screen.lt.md" class="q-mr-sm" />
            <div class="column col overflow-hidden">
               <div class="text-caption text-grey-6 font-mono uppercase tracking-widest hidden-xs" style="font-size:7px; letter-spacing: 1px;">Estructura Curricular Autorizada · PIA</div>
               <div class="text-white font-head text-weight-bolder uppercase ellipsis" :style="$q.screen.lt.md ? 'font-size:16px' : 'font-size:24px'">{{ progTemp?.nombre }}</div>
               <div class="row items-center q-gutter-x-sm">
                  <q-badge outline color="red-9" :label="progTemp?.codigo" class="font-mono text-weight-bolder" style="font-size:9px" />
               </div>
            </div>
            <div class="row items-center q-gutter-x-sm shrink-0">
               <q-btn color="red-9" icon="add" :label="$q.screen.gt.sm ? 'AGREGAR FASE' : ''" @click="abrirNuevaEtapa" class="premium-btn shadow-24 q-px-md">
                   <q-tooltip v-if="$q.screen.lt.md">Agregar Fase al Syllabus</q-tooltip>
               </q-btn>
               <q-btn flat round dense icon="close" v-close-popup color="grey-5" class="bg-black-20 hover-red" v-if="$q.screen.gt.sm" />
            </div>
        </q-toolbar>

        <q-card-section class="q-pa-none col overflow-hidden">
          <q-scroll-area class="full-height">
            <div class="q-pa-md q-pa-md-xl q-gutter-y-xl">

          <!-- KPIs de horas (Responsive & Consistent) -->
          <div class="row q-col-gutter-sm q-col-gutter-md-lg">
            <div class="col-12 col-sm-4">
              <q-card class="premium-glass-card q-pa-md text-center border-red-low shadow-24 rounded-16 hover-card bg-kpi-vuelo flex items-center justify-between no-wrap" v-if="$q.screen.lt.sm">
                <div class="text-left">
                  <div class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px">VUELO (MIN)</div>
                  <div class="text-h5 text-weight-bolder text-red-9">{{ Number(progTemp?.horas_vuelo_min || 0).toFixed(1) }}</div>
                </div>
                <q-icon name="flight_takeoff" color="red-9" size="24px" class="opacity-30" />
              </q-card>
              <q-card class="premium-glass-card q-pa-lg text-center border-red-low shadow-24 rounded-20 hover-card bg-kpi-vuelo" v-else>
                <q-icon name="flight_takeoff" color="red-9" size="20px" class="q-mb-xs opacity-50" />
                <div class="text-h4 text-weight-bolder text-red-9 line-height-1">{{ Number(progTemp?.horas_vuelo_min || 0).toFixed(1) }}</div>
                <q-separator dark class="q-my-sm opacity-10" />
                <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px; letter-spacing: 1px;">VUELO (MIN)</div>
              </q-card>
            </div>
            <div class="col-6 col-sm-4">
              <q-card class="premium-glass-card q-pa-md text-center border-red-low shadow-24 rounded-16 hover-card bg-kpi-tierra flex items-center justify-between no-wrap" v-if="$q.screen.lt.sm">
                <div class="text-left">
                  <div class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px">TIERRA (MIN)</div>
                  <div class="text-h5 text-weight-bolder text-white">{{ Number(progTemp?.horas_tierra_min || 0).toFixed(1) }}</div>
                </div>
                <q-icon name="school" color="white" size="24px" class="opacity-30" />
              </q-card>
              <q-card class="premium-glass-card q-pa-lg text-center border-red-low shadow-24 rounded-20 hover-card bg-kpi-tierra" v-else>
                <q-icon name="school" color="white" size="20px" class="q-mb-xs opacity-50" />
                <div class="text-h4 text-weight-bolder text-white line-height-1">{{ Number(progTemp?.horas_tierra_min || 0).toFixed(1) }}</div>
                <q-separator dark class="q-my-sm opacity-10" />
                <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px; letter-spacing: 1px;">TIERRA (MIN)</div>
              </q-card>
            </div>
            <div class="col-6 col-sm-4">
              <q-card class="premium-glass-card q-pa-md text-center border-red-low shadow-24 rounded-16 hover-card bg-kpi-sim flex items-center justify-between no-wrap" v-if="$q.screen.lt.sm">
                <div class="text-left">
                  <div class="text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px">SIM (MAX)</div>
                  <div class="text-h5 text-weight-bolder text-grey-4">{{ Number(progTemp?.horas_sim_max || 0).toFixed(1) }}</div>
                </div>
                <q-icon name="videogame_asset" color="grey-4" size="24px" class="opacity-30" />
              </q-card>
              <q-card class="premium-glass-card q-pa-lg text-center border-red-low shadow-24 rounded-20 hover-card bg-kpi-sim" v-else>
                <q-icon name="videogame_asset" color="grey-4" size="20px" class="q-mb-xs opacity-50" />
                <div class="text-h4 text-weight-bolder text-grey-4 line-height-1">{{ Number(progTemp?.horas_sim_max || 0).toFixed(1) }}</div>
                <q-separator dark class="q-my-sm opacity-10" />
                <div class="text-caption text-grey-6 font-mono uppercase tracking-widest" style="font-size:8px; letter-spacing: 1px;">SIM (MAX)</div>
              </q-card>
            </div>
          </div>


          <!-- Módulos de instrucción -->
          <div class="q-mt-xl">
            <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-lg border-bottom-border pb-md" :style="$q.screen.lt.md ? 'font-size:8px; letter-spacing: 1px;' : 'font-size:10px; letter-spacing: 2px;'">
              Módulos de Instrucción Registrados
            </div>
            
            <q-list dark class="bg-transparent no-border q-gutter-y-lg">
              <template v-for="(et, n) in progTemp?.etapas" :key="et.id">
                <div class="q-mb-xl last-no-margin">

                  <!-- Cabecera de Etapa (Ultra-Responsive) -->
                  <div class="bg-black-20 rounded-20 border-red-low shadow-10 q-pa-lg shadow-inner overflow-hidden" 
                       :class="$q.screen.lt.sm ? 'column items-center text-center' : 'row items-center justify-between'">


                    
                    <!-- Lado Izquierdo / Superior -->
                    <div class="row items-center col-grow" :class="$q.screen.lt.sm ? 'column' : 'q-gutter-x-lg'">
                       <q-avatar color="red-low" text-color="red-9" size="48px" class="shadow-24 border-red-low font-head text-weight-bolder gt-xs">{{ et.orden }}</q-avatar>
                       <div class="column" :class="$q.screen.lt.sm ? 'q-mb-md' : ''">
                          <div class="text-white font-head text-weight-bolder uppercase tracking-widest" :class="$q.screen.lt.sm ? 'text-h6' : 'text-h5'">{{ et.nombre }}</div>
                          <div class="text-grey-6 font-mono line-height-tight q-mt-xs" style="font-size:10px" v-if="et.descripcion">{{ et.descripcion }}</div>
                       </div>
                    </div>



                    <!-- Lado Derecho / Inferior (Métricas y Acciones) -->
                    <div :class="$q.screen.lt.sm ? 'full-width column items-center q-mt-md' : 'row items-center q-gutter-x-xl'">
                       <!-- Métricas de Horas -->
                       <div class="row q-gutter-x-lg" :class="$q.screen.lt.sm ? 'q-mb-lg' : ''">
                          <div class="text-center">
                             <div class="text-red-9 font-mono text-weight-bolder text-h6 line-height-1">{{ Number(et.horas_vuelo).toFixed(1) || '0.0' }}H</div>
                             <div class="text-grey-6 font-mono uppercase tracking-tighter" style="font-size:8px">VUELO</div>
                          </div>
                          <div class="text-center border-column-left pl-md">
                             <div class="text-white font-mono text-weight-bolder text-h6 line-height-1">{{ Number(et.horas_tierra).toFixed(1) || '0.0' }}H</div>
                             <div class="text-grey-6 font-mono uppercase tracking-tighter" style="font-size:8px">TIERRA</div>
                          </div>
                       </div>

                       
                       <!-- Acciones -->
                       <div class="row no-wrap q-gutter-x-sm">
                                         <q-btn unelevated round icon="delete" color="red-10" size="md" @click="eliminarEtapa(et.id)" />
                        </div>
                     </div>
                  </div>

                  <!-- Grilla de Materias (High Accessibility & Readability) -->
                   <div v-if="et.materias?.length" :class="$q.screen.lt.md ? 'q-px-md q-mt-md' : 'q-px-xl q-mt-lg'">
                       <div class="row q-col-gutter-y-md">
                         <div v-for="mat in et.materias" :key="mat.id" class="col-12">
                           <q-card class="bg-black-30 border-red-low hover-card shadow-12 overflow-hidden" style="border-radius:14px; min-height: 80px;">
                               <q-item class="q-pa-md items-center">
                                 <!-- Info de Materia -->
                                 <q-item-section>
                                    <div class="text-white font-head text-weight-bolder uppercase tracking-tight" 
                                         :class="$q.screen.lt.md ? 'text-subtitle2' : 'text-h6'" 
                                         style="line-height:1.2;">
                                         {{ mat.nombre }}
                                    </div>
                                    <div class="text-caption text-grey-6 font-mono tracking-tighter q-mt-xs" style="font-size:10px">
                                       <span class="text-red-9 font-weight-bolder">{{ mat.codigo }}</span> · {{ mat.horas }} HORAS CARGA ACADÉMICA
                                    </div>
                                 </q-item-section>

                                 <!-- Acciones -->
                                 <q-item-section side>
                                   <div class="row no-wrap q-gutter-x-sm bg-black-20 rounded-12 q-pa-xs border-red-low shadow-inner">
                                      <q-btn flat round icon="video_settings" color="red-9" size="md" @click="gestionarLms(mat)">
                                         <q-tooltip class="bg-dark shadow-24">AULA VIRTUAL (LMS)</q-tooltip>
                                      </q-btn>
                                      <q-btn flat round icon="edit" color="white" size="md" @click="editarMateria(mat, et.id)" />
                                      <q-btn flat round icon="delete" color="red-10" size="md" @click="eliminarMateria(mat.id)" />
                                   </div>
                                 </q-item-section>
                               </q-item>
                           </q-card>
                       </div>
                    </div>
                  </div>


                  <!-- Botón Añadir Materia (Refined Solid Style) -->
                  <div class="q-mt-xl row justify-center">
                     <q-btn color="grey-10" icon="add_circle" label="Añadir Asignatura" size="md" @click="abrirNuevaMateria(et.id)" no-caps 
                        class="font-mono text-weight-bolder rounded-12 q-px-xl shadow-24 border-red-low" />
                  </div>




                </div>
              </template>
              
              <div v-if="!progTemp?.etapas?.length" class="q-pa-xl text-center text-grey-7 font-mono uppercase border-red-low rounded-20 bg-black-20">
                   Sin fases registradas en el syllabus.
              </div>
            </q-list>
            </div>
          </div>
        </q-scroll-area>


        </q-card-section>
      </q-card>

    </q-dialog>

    <q-dialog v-model="dialogEtapa" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'" style="width:min(650px, 95vw);">
         <div class="row items-center justify-between q-mb-lg border-bottom-border pb-md">
            <div class="text-h6 text-white font-head text-weight-bolder uppercase">{{ modoEtapa === 'crear' ? 'Nueva Fase' : 'Editar Fase' }}</div>
            <q-btn flat round dense icon="close" @click="dialogEtapa = false" color="grey-6" class="bg-black-20 hover-red" />
         </div>
         <q-form @submit.prevent="guardarEtapa" class="q-gutter-y-md">
            <q-input v-model="etapaForm.nombre" filled dark label="TÍTULO DE LA FASE/MÓDULO" class="premium-input-login" :rules="[v => !!v || 'Campo obligatorio']" />
            <q-input v-model="etapaForm.descripcion" filled dark type="textarea" label="CONTENIDO TÉCNICO / DESCRIPCIÓN" class="premium-input-login" rows="2" />
            <div class="row q-col-gutter-md">
               <div class="col-6"><q-input v-model.number="etapaForm.horas_vuelo" type="number" step="0.1" filled dark label="H. VUELO" class="premium-input-login" /></div>
               <div class="col-6"><q-input v-model.number="etapaForm.horas_tierra" type="number" step="0.1" filled dark label="H. TIERRA" class="premium-input-login" /></div>
            </div>
            <q-btn type="submit" color="red-10" label="Guardar Fase" class="full-width premium-btn q-py-md" :loading="guardandoEtapa" />
         </q-form>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogMateria" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(650px, 95vw);">
        <div class="border-bottom-border pb-md" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
          <div class="row items-center justify-between">
            <div class="row items-center">
              <q-icon name="menu_book" color="red-9" size="32px" class="q-mr-md glow-primary" />
              <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Detalle de Asignatura</div>
            </div>
            <q-btn flat round dense icon="close" v-close-popup color="grey-6" class="bg-black-20 hover-red" />
          </div>
        </div>
        <div :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
           <q-form @submit.prevent="guardarMateria" class="q-gutter-y-lg">
            <q-input v-model="materiaForm.nombre" filled dark label="NOMBRE DE LA MATERIA" class="premium-input-login" :rules="[v => !!v || 'Obligatorio']" />
            <div class="row q-col-gutter-md">
               <div class="col-12 col-md-6">
                 <q-select v-model="materiaForm.tipo" :options="['teorica', 'practica', 'simulador']" label="MODALIDAD" filled dark class="premium-input-login" />
               </div>
               <div class="col-12 col-md-6">
                 <q-input v-model.number="materiaForm.nota_minima" type="number" label="NOTA MÍNIMA" filled dark class="premium-input-login" />
               </div>
               <div class="col-6"><q-input v-model="materiaForm.codigo" filled dark label="CÓDIGO (PPA-01)" class="premium-input-login" /></div>
               <div class="col-6"><q-input v-model.number="materiaForm.horas" type="number" filled dark label="HORAS CARGA" class="premium-input-login" /></div>
               <div class="col-6"><q-input v-model="materiaForm.rac_referencia" filled dark label="RAC REFERENCIA" class="premium-input-login" /></div>
               <div class="col-6"><q-input v-model.number="materiaForm.duracion_minutos" type="number" filled dark label="DURACIÓN EXAMEN (MIN)" class="premium-input-login" /></div>
            </div>
            <q-btn type="submit" color="red-10" label="Sincronizar Materia al Syllabus" class="full-width premium-btn q-py-md shadow-24" :loading="guardandoMat" />
         </q-form>
        </div>
      </q-card>
    </q-dialog>

    <!-- ════ DIÁLOGO: GESTIÓN LMS (AULA VIRTUAL ADMIN) ════ -->
    <q-dialog v-model="dialogLms" persistent maximized transition-show="slide-up" transition-hide="slide-down">
      <q-card class="bg-dark-page text-white overflow-hidden">
         <!-- Navbar del LMS -->
         <!-- Navbar del LMS Responsivo -->
         <q-toolbar class="border-bottom-border bg-black-20 no-wrap" :class="$q.screen.lt.md ? 'q-pa-sm' : 'q-pa-lg'">
            <q-btn flat round dense icon="arrow_back" @click="dialogLms = false" class="q-mr-sm" />
            <q-toolbar-title class="font-head text-weight-bolder uppercase tracking-widest overflow-hidden" :style="$q.screen.lt.md ? 'font-size:11px' : ''">
               <span v-if="$q.screen.gt.sm">Gestión de Aula Virtual: </span>
               <span class="text-red-9">{{ matActiva?.nombre }}</span>
            </q-toolbar-title>
            <q-btn color="red-9" icon="save" :label="$q.screen.gt.sm ? 'Sincronizar LMS' : ''" @click="dialogLms = false" class="premium-btn q-px-md" />
         </q-toolbar>

         <!-- Tabs en segunda fila para Móvil -->
         <div class="bg-black-20 border-bottom-border">
            <q-tabs v-model="tabLms" dense active-color="red-9" indicator-color="red-9" 
               no-caps outside-arrows mobile-arrows
               class="text-grey-5">
               <q-tab name="lecciones" :label="$q.screen.gt.sm ? 'Lecciones y Contenido' : 'Lecciones'" icon="smart_display" />
               <q-tab name="preguntas" :label="$q.screen.gt.sm ? 'Banco de Preguntas' : 'Banco'" icon="quiz" />
               <q-tab name="config"   :label="$q.screen.gt.sm ? 'Configuración' : 'Config'" icon="settings" />
            </q-tabs>
         </div>

          <q-scroll-area :style="`height: calc(100vh - ${$q.screen.lt.md ? '100px' : '80px'})`">
            <q-tab-panels v-model="tabLms" animated class="bg-transparent">
               
               <!-- PANEL: LECCIONES -->
               <q-tab-panel name="lecciones" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
                  <div class="row justify-between items-center q-mb-xl flex-wrap q-gutter-y-md">
                     <div>
                        <div class="text-white font-head text-weight-bolder uppercase tracking-tighter" :class="$q.screen.lt.md ? 'text-h6' : 'text-h5'">Contenidos de Video</div>
                        <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:9px">Estructura teórica del módulo</div>
                     </div>
                     <q-btn color="red-9" icon="add" :label="$q.screen.gt.xs ? 'Añadir Nueva Lección' : 'Añadir'" @click="abrirNuevaLeccion" class="premium-btn q-px-md" />
                  </div>

                  <div v-if="!lecciones?.length" class="text-center q-pa-xl border-red-low rounded-20 bg-black-20">
                     <q-icon name="video_library" size="64px" color="grey-8" class="q-mb-md" />
                     <div class="text-h6 text-grey-6 font-mono uppercase">Sin lecciones cargadas</div>
                  </div>

                  <div class="row q-col-gutter-md">
                    <div v-for="lec in lecciones" :key="lec.id" class="col-12 col-md-6">
                      <q-card class="bg-black-20 border-red-low shadow-10 overflow-hidden hover-card" style="border-radius:16px">
                        <q-item class="q-pa-md items-center">
                          <q-item-section avatar v-if="$q.screen.gt.xs">
                             <div class="kpi-icon-premium shadow-24 bg-red-low flex flex-center" style="width: 48px; height: 48px; border-radius: 12px;">
                                <q-icon name="play_circle" color="red-9" size="24px" class="glow-primary" />
                             </div>
                          </q-item-section>
                          <q-item-section>
                             <div class="text-white font-head text-weight-bolder uppercase tracking-tighter" :class="$q.screen.lt.md ? 'text-subtitle2' : 'text-h6'" style="line-height: 1.2;">{{ lec.titulo }}</div>
                             <div class="text-caption text-grey-6 font-mono ellipsis q-mt-xs" style="font-size:9px">{{ lec.video_url || lec.contenido_url || 'Sin enlace' }}</div>
                          </q-item-section>
                          <q-item-section side>
                             <div class="row no-wrap q-gutter-x-xs bg-black-30 rounded-12 q-pa-xs border-red-low">
                                <q-btn flat round icon="edit" color="white" size="sm" @click="editarLeccion(lec)" />
                                <q-btn flat round icon="delete" color="red-10" size="sm" @click="eliminarLeccion(lec.id)" />
                             </div>
                          </q-item-section>
                        </q-item>
                      </q-card>
                    </div>
                  </div>
               </q-tab-panel>

               <!-- PANEL: PREGUNTAS -->
               <q-tab-panel name="preguntas" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
                  <div class="row justify-between items-center q-mb-xl flex-wrap q-gutter-y-md">
                     <div>
                        <div class="text-white font-head text-weight-bolder uppercase tracking-tighter" :class="$q.screen.lt.md ? 'text-h6' : 'text-h5'">Banco de Reactivos</div>
                        <div class="text-caption text-grey-6 font-mono uppercase" style="font-size:9px">Evaluación de competencia técnica UAEAC</div>
                     </div>
                     <q-btn color="red-9" icon="add" :label="$q.screen.gt.xs ? 'Añadir Nueva Pregunta' : 'Añadir'" @click="abrirNuevaPregunta" class="premium-btn q-px-md" />
                  </div>



                  <div v-if="!bancoPreguntas?.length" class="text-center q-pa-xl border-red-low rounded-20 bg-black-20">
                     <q-icon name="quiz" size="64px" color="grey-8" class="q-mb-md" />
                     <div class="text-h6 text-grey-6 font-mono uppercase">Sin reactivos registrados</div>
                  </div>

                  <div class="row q-col-gutter-md">
                     <div v-for="preg in bancoPreguntas" :key="preg.id" class="col-12 col-md-6">
                        <q-card class="bg-black-20 border-red-low shadow-10 overflow-hidden hover-card" style="border-radius:16px">
                           <q-item class="q-pa-md items-center">
                              <q-item-section avatar v-if="$q.screen.gt.xs">
                                 <div class="kpi-icon-premium shadow-24 bg-red-low flex flex-center" style="width: 48px; height: 48px; border-radius: 12px;">
                                    <q-icon name="help_center" color="red-9" size="24px" class="glow-primary" />
                                 </div>
                              </q-item-section>
                              <q-item-section>
                                 <div class="text-white font-head text-weight-bolder uppercase tracking-tighter" :class="$q.screen.lt.md ? 'text-subtitle2' : 'text-h6'" style="line-height: 1.2;">{{ preg.pregunta }}</div>
                                 <div class="row items-center q-gutter-x-sm q-mt-xs">
                                    <q-badge outline color="grey-7" :label="`${preg.opciones?.length || 0} OPCIONES`" class="font-mono" style="font-size:8px" />
                                    <q-badge color="emerald" label="RESPUESTA CARGADA" class="font-mono" style="font-size:8px" v-if="preg.respuesta_correcta" />
                                 </div>
                              </q-item-section>
                              <q-item-section side>
                                 <div class="row no-wrap q-gutter-x-xs bg-black-30 rounded-12 q-pa-xs border-red-low">
                                    <q-btn flat round icon="edit" color="white" size="sm" @click="editarPregunta(preg)" />
                                    <q-btn flat round icon="delete" color="red-10" size="sm" @click="eliminarPregunta(preg.id)" />
                                 </div>
                              </q-item-section>
                           </q-item>
                        </q-card>
                     </div>
                  </div>

               </q-tab-panel>

               <!-- PANEL: CONFIG -->
               <q-tab-panel name="config" class="q-pa-xl">
                  <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low" style="max-width: 600px">
                     <div class="text-h5 font-head text-weight-bolder q-mb-xl uppercase text-red-9">Parámetros de Evaluación</div>
                     <q-form class="q-gutter-y-lg">
                        <q-input v-model.number="matActiva.nota_minima" type="number" filled dark label="NOTA MÍNIMA PARA APROBAR (%)" class="premium-input-login" prefix="%" />
                        <q-input v-model.number="matActiva.duracion_minutos" type="number" filled dark label="DURACIÓN EXAMEN (MINUTOS)" class="premium-input-login" />
                        <q-input v-model.number="matActiva.max_intentos" type="number" filled dark label="NÚMERO MÁXIMO DE INTENTOS" class="premium-input-login" />
                        <div class="row q-col-gutter-md">
                           <div class="col-12">
                              <q-input v-model="matActiva.link_meet" filled dark label="ENLACE DE REUNIÓN (GOOGLE MEET / EXTERNO)" class="premium-input-login">
                                 <template #append>
                                    <q-btn flat dense icon="calendar_month" color="red-9" @click="programarEnGoogle" label="GENERAR EVENTO">
                                       <q-tooltip>Abrir Google Calendar con estos datos</q-tooltip>
                                    </q-btn>
                                 </template>
                              </q-input>
                           </div>
                        </div>

                        <div class="row q-col-gutter-lg q-mt-sm">
                           <div class="col-12 col-md-6">
                              <div class="text-caption text-grey-7 font-mono q-mb-xs uppercase" style="font-size: 10px">Inicia:</div>
                              <q-input filled v-model="matActiva.sesion_viva_inicio" dark placeholder="Seleccione fecha y hora" class="premium-input-login">
                                 <template v-slot:prepend>
                                 <q-icon name="event" class="cursor-pointer text-red-9">
                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                       <q-date v-model="matActiva.sesion_viva_inicio" mask="YYYY-MM-DD HH:mm" dark color="red-9" />
                                    </q-popup-proxy>
                                 </q-icon>
                                 </template>
                                 <template v-slot:append>
                                 <q-icon name="schedule" class="cursor-pointer text-red-9">
                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                       <q-time v-model="matActiva.sesion_viva_inicio" mask="YYYY-MM-DD HH:mm" format24h dark color="red-9" />
                                    </q-popup-proxy>
                                 </q-icon>
                                 </template>
                              </q-input>
                           </div>
                           <div class="col-12 col-md-6">
                              <div class="text-caption text-grey-7 font-mono q-mb-xs uppercase" style="font-size: 10px">Finaliza:</div>
                              <q-input filled v-model="matActiva.sesion_viva_fin" dark placeholder="Seleccione fecha y hora" class="premium-input-login">
                                 <template v-slot:prepend>
                                 <q-icon name="event" class="cursor-pointer text-red-9">
                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                       <q-date v-model="matActiva.sesion_viva_fin" mask="YYYY-MM-DD HH:mm" dark color="red-9" />
                                    </q-popup-proxy>
                                 </q-icon>
                                 </template>
                                 <template v-slot:append>
                                 <q-icon name="schedule" class="cursor-pointer text-red-9">
                                    <q-popup-proxy cover transition-show="scale" transition-hide="scale">
                                       <q-time v-model="matActiva.sesion_viva_fin" mask="YYYY-MM-DD HH:mm" format24h dark color="red-9" />
                                    </q-popup-proxy>
                                 </q-icon>
                                 </template>
                              </q-input>
                           </div>
                        </div>

                        <q-input v-model="matActiva.video_url" filled dark label="VIDEO INTRODUCTORIO (YOUTUBE/DRIVE)" class="premium-input-login" />
                        <q-btn color="red-10" label="Guardar Configuración General" @click="guardarConfigGeneral" class="full-width premium-btn q-py-lg shadow-24" :loading="guardandoLms" />
                     </q-form>
                  </q-card>
               </q-tab-panel>

            </q-tab-panels>
         </q-scroll-area>
      </q-card>
    </q-dialog>

    <!-- ════ DIÁLOGOS: CREACIÓN LECCIÓN Y PREGUNTA ════ -->
    <q-dialog v-model="dialogNuevaLeccion" backdrop-filter="blur(15px)">
       <q-card class="premium-glass-card q-pa-xl border-red-low shadow-24 rounded-20" style="width:500px">
          <div class="text-h6 text-white font-head q-mb-lg uppercase text-red-9">Nueva Lección de Video</div>
          <q-form @submit.prevent="guardarLeccion" class="q-gutter-y-md">
             <q-input v-model="leccionForm.titulo" filled dark label="TÍTULO DE LA LECCIÓN" class="premium-input-login" :rules="[v=>!!v||'Requerido']" />
             <q-input v-model="leccionForm.video_url" filled dark label="URL VIDEO (YOUTUBE/DRIVE)" class="premium-input-login" />
             <q-input v-model="leccionForm.documento_url" filled dark label="URL DOCUMENTO (PDF/DRIVE/RAC)" class="premium-input-login" />
             <q-input v-model="leccionForm.descripcion" type="textarea" filled dark label="DESCRIPCIÓN" class="premium-input-login" rows="2" />
             <q-btn type="submit" color="red-10" label="Guardar Contenidos de Lección" class="full-width premium-btn q-py-md" />
          </q-form>
       </q-card>
    </q-dialog>

    <q-dialog v-model="dialogNuevaPregunta" backdrop-filter="blur(15px)">
       <q-card class="premium-glass-card q-pa-xl border-red-low shadow-24 rounded-20" style="width:600px">
          <div class="text-h6 text-white font-head q-mb-lg uppercase text-red-9">Añadir Reactivo al Banco</div>
          <q-form @submit.prevent="guardarPregunta" class="q-gutter-y-md">
             <q-input v-model="preguntaForm.pregunta" filled dark type="textarea" label="PREGUNTA / ENUNCIADO" class="premium-input-login" rows="2" />
             <div class="text-caption text-grey-6 font-mono uppercase q-mb-sm">Opciones de Respuesta (Marca la correcta)</div>
             <div v-for="i in [0,1,2,3]" :key="i" class="row items-center q-mb-xs">
                <q-radio v-model="preguntaForm.respuesta_correcta" :val="preguntaForm.opciones[i]" color="red-9" class="q-mr-sm" />
                <q-input v-model="preguntaForm.opciones[i]" filled dark dense :label="`Opción ${i+1}`" class="col premium-input-login" />
             </div>
             <q-btn type="submit" color="red-10" label="Guardar Pregunta" class="full-width premium-btn q-py-md q-mt-md" />
          </q-form>
       </q-card>
    </q-dialog>

    <q-dialog v-model="dialogVuelo" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'" style="width:min(900px, 95vw);">
        <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
          <div class="row items-center">
            <q-icon name="flight_takeoff" color="red-9" size="32px" class="q-mr-md glow-primary" />
            <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Certificar Misión de Entrenamiento</div>
          </div>
          <q-btn flat round dense icon="close" @click="dialogVuelo = false" color="grey-6" class="bg-black-20 hover-red" />
        </div>

        <q-form @submit.prevent="guardarVuelo" class="q-gutter-y-lg">
          <q-select v-model="vueloForm.estudiante_id" :options="estudiantesOptions"
            filled dark label="SELECCIONAR ALUMNO CERTIFICADO" class="premium-input-login"
            map-options emit-value stack-label>
            <template #prepend><q-icon name="person" color="red-9" /></template>
          </q-select>

          <div class="row q-col-gutter-lg">
            <div class="col-12 col-md-6">
              <q-input v-model="vueloForm.matricula" filled dark label="MATRÍCULA (HK)" class="premium-input-login" placeholder="Ej: HK-5000" stack-label>
                <template #prepend><q-icon name="flight" color="red-9" /></template>
              </q-input>
            </div>
            <div class="col-12 col-md-6">
              <q-select v-model="vueloForm.tipo_vuelo" :options="['dual', 'solo', 'ftd', 'chequeo']"
                filled dark label="TIPO DE MISIÓN" class="premium-input-login"
                stack-label uppercase>
                <template #prepend><q-icon name="category" color="red-9" /></template>
              </q-select>
            </div>
            <div class="col-12 col-md-6">
              <q-select v-model="vueloForm.instructor_id" :options="instructoresOptions"
                filled dark label="CAPITÁN INSTRUCTOR" class="premium-input-login"
                map-options emit-value stack-label>
                <template #prepend><q-icon name="badge" color="red-9" /></template>
              </q-select>
            </div>
            <div class="col-12 col-md-4">
              <q-input v-model="vueloForm.fecha" type="date" filled dark label="FECHA MISIÓN" class="premium-input-login" stack-label>
                <template #prepend><q-icon name="event" color="red-9" /></template>
              </q-input>
            </div>
            <div class="col-6 col-md-4">
              <q-input v-model="vueloForm.horas" type="number" step="0.1" filled dark label="DURACIÓN (HRS)" class="premium-input-login" stack-label>
                <template #prepend><q-icon name="schedule" color="red-9" /></template>
              </q-input>
            </div>
            <div class="col-6 col-md-4">
              <q-select v-model="vueloForm.calificacion" :options="['S', 'NI', 'D', 'NA']"
                filled dark label="CALIFICACIÓN (RAC)" class="premium-input-login"
                stack-label>
                <template #prepend><q-icon name="star" color="red-9" /></template>
                <q-tooltip class="bg-dark">S: Satisfactorio, NI: Necesita Instrucción, D: Deficiente, NA: No Aplica</q-tooltip>
              </q-select>
            </div>
            <div class="col-6 col-md-4">
              <q-input v-model="vueloForm.despegues" type="number" filled dark label="DESPEGUES" class="premium-input-login" stack-label />
            </div>
            <div class="col-6 col-md-4">
              <q-input v-model="vueloForm.aterrizajes" type="number" filled dark label="ATERRIZAJES" class="premium-input-login" stack-label />
            </div>
            <div class="col-12">
              <q-input v-model="vueloForm.observaciones" type="textarea" label="OBSERVACIONES TÉCNICAS / MANIOBRAS" class="premium-input-login" rows="2" stack-label />
            </div>
          </div>

          <q-btn type="submit" color="red-10" class="full-width premium-btn q-py-lg shadow-24"
            label="Certificar y Registrar Misión de Vuelo" icon="verified"
            :loading="guardandoVuelo" />
        </q-form>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogNuevoEstudiante" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'" style="width:min(900px, 95vw);">
         <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md">
            <div class="text-h5 text-white font-head text-weight-bolder uppercase">Inscripción RAC 141 (Nuevo Cadete)</div>
            <q-btn flat round dense icon="close" @click="dialogNuevoEstudiante = false" color="grey-6" class="bg-black-20 hover-red" />
         </div>
         
         <q-form @submit.prevent="guardarNuevoEstudiante" class="q-gutter-y-lg">
            <div class="row q-col-gutter-md">
               <div class="col-12 col-md-6">
                  <q-input v-model="estForm.nombres" label="NOMBRES" filled dark class="premium-input-login" :rules="[v => !!v || 'Requerido']" />
               </div>
               <div class="col-12 col-md-6">
                  <q-input v-model="estForm.apellidos" label="APELLIDOS" filled dark class="premium-input-login" :rules="[v => !!v || 'Requerido']" />
               </div>
               <div class="col-12 col-md-4">
                  <q-select v-model="estForm.tipo_documento" :options="['CC', 'CE', 'PPT', 'TI']" label="TIPO DOC" filled dark class="premium-input-login" />
               </div>
               <div class="col-12 col-md-8">
                  <q-input v-model="estForm.num_documento" label="NÚMERO DE DOCUMENTO" filled dark class="premium-input-login" :rules="[v => !!v || 'Requerido']" />
               </div>
               <div class="col-12 col-md-6">
                  <q-input v-model="estForm.fecha_nacimiento" type="date" label="F. NACIMIENTO" filled dark class="premium-input-login" stack-label />
               </div>
               <div class="col-12 col-md-6">
                  <q-input v-model="estForm.fecha_ingreso" type="date" label="FECHA DE INGRESO" filled dark class="premium-input-login" stack-label />
               </div>
               <div class="col-12">
                   <q-select v-model="estForm.programa_id" :options="programasFilteredOptions" 
                      label="PROGRAMA ACADÉMICO ASIGNADO (PIA)" filled dark class="premium-input-login" 
                      map-options emit-value stack-label use-input fill-input hide-selected
                      @filter="filterProgramas">
                      <template v-slot:no-option>
                        <q-item><q-item-section class="text-grey">No se encontraron programas</q-item-section></q-item>
                      </template>
                   </q-select>
                </div>
               <div class="col-12">
                  <q-input v-model="estForm.observaciones" type="textarea" label="OBSERVACIONES DE MATRÍCULA" filled dark class="premium-input-login" rows="2" />
               </div>
            </div>
            <q-btn type="submit" color="red-10" class="full-width premium-btn q-py-lg shadow-24" label="Registrar y Generar Expediente UAEAC" :loading="guardandoNuevoEst" />
         </q-form>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogNota" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(550px, 95vw);">
         <div class="row items-center justify-between q-mb-lg border-bottom-border pb-md">
            <div class="text-h6 text-white font-head text-weight-bolder uppercase">Registrar Calificación Teórica</div>
            <q-btn flat round dense icon="close" @click="dialogNota = false" color="grey-6" class="bg-black-20 hover-red" />
         </div>
         <div class="text-subtitle2 text-red-9 font-mono q-mb-xl">{{ estActivo?.persona?.nombres }} {{ estActivo?.persona?.apellidos }}</div>
         
         <q-form @submit.prevent="guardarNota" class="q-gutter-y-md">
            <q-select v-model="notaForm.materia_id" :options="materiasOptions"
              filled dark label="ASIGNATURA/MATERIA" class="premium-input-login"
              map-options emit-value stack-label>
              <template #prepend><q-icon name="menu_book" color="red-9" /></template>
            </q-select>
            <div class="row q-col-gutter-md">
               <div class="col-6">
                  <q-input v-model="notaForm.nota" type="number" step="0.1" filled dark label="CALIFICACIÓN (0-100)" class="premium-input-login" />
               </div>
               <div class="col-6">
                  <q-input v-model="notaForm.fecha_evaluacion" type="date" filled dark label="FECHA EXAMEN" class="premium-input-login" stack-label />
               </div>
            </div>
            <q-input v-model="notaForm.observaciones" type="textarea" filled dark label="OBSERVACIONES" class="premium-input-login" rows="2" />
            <q-btn type="submit" color="red-10" class="full-width premium-btn q-py-lg" label="Guardar Nota en Expediente" :loading="guardandoNota" />
         </q-form>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogCertMedico" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top rounded-20" style="width:min(550px, 95vw);">
         <div class="row items-center justify-between q-mb-lg border-bottom-border pb-md">
            <div class="text-h6 text-white font-head text-weight-bolder uppercase">Certificado Médico RAC 67</div>
            <q-btn flat round dense icon="close" @click="dialogCertMedico = false" color="grey-6" class="bg-black-20 hover-red" />
         </div>
         <div class="text-subtitle2 text-blue-9 font-mono q-mb-xl">{{ estActivo?.persona?.nombres }} {{ estActivo?.persona?.apellidos }}</div>

         <q-form @submit.prevent="guardarCertMedico" class="q-gutter-y-md">
            <q-select v-model="certForm.tipo" :options="[{label:'Clase 1', value:'clase_1'},{label:'Clase 2', value:'clase_2'},{label:'Clase 3', value:'clase_3'}]"
              filled dark label="CLASE DE CERTIFICADO" class="premium-input-login"
              map-options emit-value stack-label />
            <q-input v-model="certForm.numero_certificado" filled dark label="NÚMERO DE CERTIFICADO" class="premium-input-login" />
            <div class="row q-col-gutter-md">
               <div class="col-6">
                  <q-input v-model="certForm.fecha_emision" type="date" filled dark label="FECHA EMISIÓN" class="premium-input-login" stack-label />
               </div>
               <div class="col-6">
                  <q-input v-model="certForm.fecha_vencimiento" type="date" filled dark label="VENCIMIENTO" class="premium-input-login" stack-label />
               </div>
            </div>
            <q-input v-model="certForm.centro_aeromedico" filled dark label="CENTRO AEROMÉDICO / MÉDICO DELEGADO" class="premium-input-login" />
            <q-input v-model="certForm.restricciones" type="textarea" filled dark label="RESTRICCIONES (SI APLICA)" class="premium-input-login" rows="2" />
            <q-btn type="submit" color="blue-10" class="full-width premium-btn q-py-lg" label="Sincronizar Salud UAEAC" :loading="guardandoCert" />
         </q-form>
      </q-card>
    </q-dialog>

    <!-- DIÁLOGO: PLAN DE CLASE -->
    <q-dialog v-model="dialogPlan" persistent backdrop-filter="blur(15px)">
       <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(700px, 95vw)">
          <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
             <div class="row items-center">
                <q-icon name="fact_check" color="red-9" size="32px" class="q-mr-md glow-primary" />
                <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Plan de Clase</div>
             </div>
             <q-btn flat round dense icon="close" @click="dialogPlan = false" color="grey-6" class="bg-black-20 hover-red" />
          </div>
          <q-form @submit.prevent="guardarPlanClase" class="q-gutter-y-md" :class="$q.screen.lt.md ? 'q-px-lg q-pb-lg' : 'q-px-xl q-pb-xl'">
             <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.instructor_id" :options="instructoresOptions" label="INSTRUCTOR" filled dark class="premium-input-login" map-options emit-value stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.materia_id" :options="materiasTodasOptions" label="MATERIA / MÓDULO" filled dark class="premium-input-login" map-options emit-value stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-input v-model="planForm.fecha" type="date" label="FECHA PLANIFICADA" filled dark class="premium-input-login" stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-input v-model.number="planForm.duracion_min" type="number" label="DURACIÓN (MINUTOS)" filled dark class="premium-input-login" stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.estado" :options="['planificada', 'realizada', 'cancelada']" label="ESTADO" filled dark class="premium-input-login" stack-label uppercase />
                </div>
             </div>
             <q-input v-model="planForm.objetivos" type="textarea" label="OBJETIVOS DE LA CLASE" filled dark class="premium-input-login" rows="2" stack-label />
             <q-input v-model="planForm.contenido" type="textarea" label="CONTENIDO TÉCNICO" filled dark class="premium-input-login" rows="2" stack-label />
             <q-input v-model="planForm.recursos" type="textarea" label="RECURSOS (MATERIALES / EQUIPO)" filled dark class="premium-input-login" rows="1" stack-label />

             <q-btn type="submit" color="red-10" label="Guardar Plan de Clase" class="full-width premium-btn q-py-lg q-mt-md shadow-24 text-weight-bolder" :loading="guardandoPlan" />
          </q-form>
       </q-card>
    </q-dialog>

    <!-- DIÁLOGO: PLAN DE CLASE -->
    <q-dialog v-model="dialogPlan" persistent backdrop-filter="blur(15px)">
       <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(700px, 95vw)">
          <div class="row items-center justify-between q-mb-xl border-bottom-border pb-md" :class="$q.screen.lt.md ? 'q-pa-lg' : 'q-pa-xl'">
             <div class="row items-center">
                <q-icon name="fact_check" color="red-9" size="32px" class="q-mr-md glow-primary" />
                <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">Plan de Clase</div>
             </div>
             <q-btn flat round dense icon="close" @click="dialogPlan = false" color="grey-6" class="bg-black-20 hover-red" />
          </div>
          <q-form @submit.prevent="guardarPlanClase" class="q-gutter-y-md" :class="$q.screen.lt.md ? 'q-px-lg q-pb-lg' : 'q-px-xl q-pb-xl'">
             <div class="row q-col-gutter-md">
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.instructor_id" :options="instructoresOptions" label="INSTRUCTOR" filled dark class="premium-input-login" map-options emit-value stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.materia_id" :options="materiasTodasOptions" label="MATERIA / MÓDULO" filled dark class="premium-input-login" map-options emit-value stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-input v-model="planForm.fecha" type="date" label="FECHA PLANIFICADA" filled dark class="premium-input-login" stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-input v-model.number="planForm.duracion_min" type="number" label="DURACIÓN (MINUTOS)" filled dark class="premium-input-login" stack-label />
                </div>
                <div class="col-12 col-md-6">
                   <q-select v-model="planForm.estado" :options="['planificada', 'realizada', 'cancelada']" label="ESTADO" filled dark class="premium-input-login" stack-label uppercase />
                </div>
             </div>
             <q-input v-model="planForm.objetivos" type="textarea" label="OBJETIVOS DE LA CLASE" filled dark class="premium-input-login" rows="2" stack-label />
             <q-input v-model="planForm.contenido" type="textarea" label="CONTENIDO TÉCNICO" filled dark class="premium-input-login" rows="2" stack-label />
             <q-input v-model="planForm.recursos" type="textarea" label="RECURSOS (MATERIALES / EQUIPO)" filled dark class="premium-input-login" rows="1" stack-label />

             <q-btn type="submit" color="red-10" label="Guardar Plan de Clase" class="full-width premium-btn q-py-lg q-mt-md shadow-24 text-weight-bolder" :loading="guardandoPlan" />
          </q-form>
       </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import dayjs from 'dayjs'
import { formatFechaCO } from 'src/utils/formatters'

const route = useRoute()
const $q = useQuasar()
const tab = ref(route.query.tab || 'programas')
const filtroBusqueda = ref('')

const programas          = ref([])
const estudiantes        = ref([])
const misionesVuelo      = ref([])
const loadingProgramas   = ref(false)
const loadingEstudiantes = ref(false)
const loadingVuelo       = ref(false)
const guardandoVuelo     = ref(false)
const guardandoPrograma  = ref(false)

const dialogPrograma = ref(false)
const dialogSyllabus = ref(false)
const dialogVuelo    = ref(false)
const modoPrograma   = ref('crear')
const dialogEtapa    = ref(false)
const dialogMateria  = ref(false)
const dialogLms      = ref(false)
const modoEtapa      = ref('crear')
const modoMateria    = ref('crear')
const guardandoEtapa = ref(false)
const guardandoMat   = ref(false)

const progTemp    = ref(null)
const matActiva   = ref(null)
const progForm    = ref({
  id: null,
  nombre: '',
  codigo: '',
  tipo: 'PPL',
  horas_tierra_min: 80,
  horas_vuelo_min: 40,
  horas_dual_min: 20,
  horas_solo_min: 10,
  horas_ifr_min: 0,
  horas_noche_min: 0,
  horas_sim_max: 5,
  rac_referencia: '61.109',
  activo: true
})
const etapaForm   = ref({ nombre: '', descripcion: '', horas_vuelo: 0, horas_tierra: 0, simulador: 0, numero: 1 })
const materiaForm = ref({ nombre: '', codigo: '', horas: 0, etapa_id: null, tipo: 'teorica', nota_minima: 75, rac_referencia: '' })

const bancoPreguntas = ref([])
const lecciones      = ref([])
const tabLms         = ref('lecciones')
const loadingLms     = ref(false)
const guardandoLms   = ref(false)
const tabEtapa       = ref(null)

const dialogNuevaLeccion  = ref(false)
const dialogNuevaPregunta = ref(false)
const leccionForm = ref({ titulo: '', video_url: '', descripcion: '', orden: 1 })
const preguntaForm = ref({ pregunta: '', opciones: ['', '', '', ''], respuesta_correcta: '', nivel_dificultad: 1 })

const dialogNuevoEstudiante = ref(false)
const guardandoNuevoEst     = ref(false)
const programasOptions      = ref([])
const programasFilteredOptions = ref([])
const estForm = ref({ nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '', fecha_nacimiento: '', fecha_ingreso: dayjs().format('YYYY-MM-DD'), programa_id: null, observaciones: '' })

const dialogNota       = ref(false)
const dialogCertMedico = ref(false)
const guardandoNota    = ref(false)
const guardandoCert    = ref(false)
const estActivo        = ref(null)
const materiasOptions  = ref([])

const notaForm = ref({ estudiante_id: null, materia_id: null, nota: 80, fecha_evaluacion: dayjs().format('YYYY-MM-DD'), observaciones: '' })
const certForm = ref({ tipo: 'clase_1', numero_certificado: '', fecha_emision: '', fecha_vencimiento: '', centro_aeromedico: '', restricciones: '' })

const vueloForm = ref({
  estudiante_id: null,
  instructor_id: null,
  fecha: dayjs().format('YYYY-MM-DD'),
  matricula: '',
  tipo_vuelo: 'dual',
  horas: 1.0,
  despegues: 1,
  aterrizajes: 1,
  calificacion: 'S',
  observaciones: ''
})

const estudiantesOptions  = ref([])
const aeronavesOptions    = ref([])
const instructoresOptions = ref([])

const planesClase = ref([])
const loadingPlanes = ref(false)

// Endorsements + Certificados tabs
const endorsements        = ref([])
const loadingEndorsements = ref(false)
const certRecientes       = ref([])
const loadingCerts        = ref(false)

const colsEndorsements = [
  { name: 'estudiante', label: 'Estudiante', field: r => (r.estudiante?.nombres ?? '') + ' ' + (r.estudiante?.apellidos ?? ''), align: 'left' },
  { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'center' },
  { name: 'fecha', label: 'Fecha', field: r => r.fecha?.slice(0,10) ?? '—', align: 'center' },
  { name: 'aeronave_matricula', label: 'Aeronave', field: 'aeronave_matricula', align: 'center' },
  { name: 'instructor', label: 'Instructor', field: r => (r.instructor?.nombres ?? '') + ' ' + (r.instructor?.apellidos ?? ''), align: 'left' },
  { name: 'firma', label: 'Firmado', field: 'firma_instructor', align: 'center' },
]
const colsCerts = [
  { name: 'numero_certificado', label: 'N° Certificado', field: 'numero_certificado', align: 'left' },
  { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'center' },
  { name: 'estudiante', label: 'Estudiante', field: r => (r.estudiante?.nombres ?? '') + ' ' + (r.estudiante?.apellidos ?? ''), align: 'left' },
  { name: 'fecha_emision', label: 'Fecha', field: r => r.fecha_emision?.slice(0,10) ?? '—', align: 'center' },
  { name: 'anulado', label: 'Estado', field: r => r.anulado ? 'Anulado' : 'Vigente', align: 'center' },
]
const endTipoLabel = (t) => ({
  primer_vuelo_solo: 'Primer Vuelo Solo', vuelo_solo_area: 'Vuelo Solo Área',
  vuelo_solo_xc_corto: 'XC Corto', vuelo_solo_xc_largo: 'XC Largo',
  vuelo_nocturno: 'Vuelo Nocturno', examen_vuelo: 'Examen Vuelo',
})[t] ?? t
const dialogPlan = ref(false)
const guardandoPlan = ref(false)
const planForm = ref({ id: null, instructor_id: null, materia_id: null, fecha: dayjs().format('YYYY-MM-DD'), duracion_min: 60, estado: 'planificada', objetivos: '', contenido: '', recursos: '' })
const materiasTodasOptions = ref([])

const columnsEstudiantes = [
  { name: 'nombre',   label: 'NOMBRE DEL CADETE', align: 'left' },
  { name: 'estado',   label: 'STATUS UAEAC',       align: 'center' },
  { name: 'acciones', label: 'EXPEDIENTE',          align: 'right' }
]

const columnsVuelos = [
  { name: 'fecha',         label: 'FECHA OPERACIÓN',  field: row => row.fecha ? dayjs(row.fecha).format('DD/MM/YYYY') : 'N/A', align: 'left' },
  { name: 'matricula',     label: 'HK ASIGNADA',       field: 'matricula',     align: 'center' },
  { name: 'tipo',          label: 'MISIÓN ACADÉMICA',  field: row => row.tipo_vuelo || 'Instrucción', align: 'left' },
  { name: 'duracion',      label: 'DURACIÓN',          field: 'horas', align: 'right' },
  { name: 'calificacion',  label: 'CALIFICACIÓN',      field: 'calificacion',  align: 'center' },
]

const getEstadoColor = (e) => ({ 'activo': 'emerald', 'volando': 'blue-9', 'graduado': 'purple-9', 'suspendido': 'red-9' }[e] || 'grey-8')

// ── Carga de datos ──
async function cargarDatos() {
  loadingProgramas.value = true; loadingEstudiantes.value = true; loadingVuelo.value = true; loadingPlanes.value = true
  try {
    const [ps, es, vs, as, ins, pl] = await Promise.all([
      api.get('/programas'),
      api.get('/estudiantes', { params: { q: filtroBusqueda.value } }),
      api.get('/vuelos'),
      api.get('/aeronaves'),
      api.get('/instructores'),
      api.get('/planes-clase')
    ])

    programas.value     = ps.data.data || ps.data || []
    programasOptions.value = programas.value.map(p => ({ label: p.nombre, value: p.id }))
    
    materiasTodasOptions.value = []
    programas.value.forEach(p => {
       p.etapas?.forEach(et => {
          et.materias?.forEach(m => {
             materiasTodasOptions.value.push({ label: `${m.codigo} - ${m.nombre} (${p.codigo})`, value: m.id })
          })
       })
    })

    estudiantes.value   = es.data.data?.data || es.data.data || es.data || []
    misionesVuelo.value = vs.data.data || vs.data || []
    planesClase.value   = pl.data.data || pl.data || []

    estudiantesOptions.value  = estudiantes.value.map(e => ({ label: `${e.persona?.nombres} ${e.persona?.apellidos}`, value: e.id }))
    
    const rawAeronaves    = as.data.data || as.data || []
    aeronavesOptions.value = rawAeronaves.map(a => ({ label: `${a.matricula} · ${a.modelo}`, value: a.id }))
    
    const rawInstructores = ins.data.data || ins.data || []
    instructoresOptions.value = rawInstructores.map(i => ({ label: `Cpt. ${i.persona?.nombres} ${i.persona?.apellidos}`, value: i.id }))

  } catch (e) {
    console.error("Error al cargar datos:", e)
    $q.notify({ color: 'negative', message: 'Fallo al sincronizar con el centro de datos.' })
  } finally {
    loadingProgramas.value = false; loadingEstudiantes.value = false; loadingVuelo.value = false; loadingPlanes.value = false
  }
}

async function eliminarPrograma(prog) {
  $q.dialog({
    title: `<span class="text-red-9 font-head uppercase">Eliminar PIA: ${prog.nombre}</span>`,
    message: 'Esta acción eliminará permanentemente el programa académico y todas sus fases. Los cadetes inscritos quedarán sin programa asociado.',
    html: true,
    dark: true,
    ok: { color: 'red-10', label: 'ELIMINAR PERMANENTEMENTE', unelevated: true },
    cancel: { label: 'Cancelar', flat: true, color: 'grey-6' }
  }).onOk(async () => {
    try {
      await api.delete(`/programas/${prog.id}`)
      $q.notify({ color: 'emerald', message: 'Programa eliminado del sistema.' })
      cargarDatos()
    } catch {
      $q.notify({ color: 'negative', message: 'No se pudo eliminar el programa (posiblemente tiene dependencias activas).' })
    }
  })
}

// ── Acciones de Programas ──
function abrirNuevoPrograma() {
  modoPrograma.value = 'crear'
  progForm.value = { nombre: '', codigo: '', horas_tierra_min: 0, horas_vuelo_min: 0, descripcion: '' }
  dialogPrograma.value = true
}

function editarPrograma(prog) {
  modoPrograma.value = 'editar'
  progForm.value = { ...prog }
  dialogPrograma.value = true
}

function verSyllabus(prog) {
  progTemp.value = prog
  dialogSyllabus.value = true
}

function abrirRegistroVuelo() {
  vueloForm.value = { estudiante_id: null, matricula: '', instructor_id: null, fecha: dayjs().format('YYYY-MM-DD'), horas: 1.0, calificacion: 'S', tipo_vuelo: 'dual', despegues: 1, aterrizajes: 1 }
  dialogVuelo.value = true
}

// ── Guardar Programa ──
async function guardarPrograma() {
  guardandoPrograma.value = true
  try {
    if (modoPrograma.value === 'crear') await api.post('/programas', progForm.value)
    else await api.put(`/programas/${progForm.value.id}`, progForm.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Configuración PIA actualizada correctamente.' })
    dialogPrograma.value = false
    await cargarDatos()
    if (progTemp.value) {
      progTemp.value = programas.value.find(p => p.id === progTemp.value.id)
    }
  } catch {
    $q.notify({ color: 'negative', message: 'Error al actualizar el programa PIA.' })
  } finally { guardandoPrograma.value = false }
}

function abrirNuevaEtapa() {
  modoEtapa.value = 'crear'
  etapaForm.value = { nombre: '', descripcion: '', horas_vuelo: 0, horas_tierra: 0, simulador: 0, numero: (progTemp.value?.etapas?.length || 0) + 1 }
  dialogEtapa.value = true
}

function editarEtapa(et) {
  modoEtapa.value = 'editar'
  etapaForm.value = { ...et }
  dialogEtapa.value = true
}

async function guardarEtapa() {
  guardandoEtapa.value = true
  try {
    if (modoEtapa.value === 'crear') await api.post(`/programas/${progTemp.value.id}/etapas`, etapaForm.value)
    else await api.put(`/programas/etapas/${etapaForm.value.id}`, etapaForm.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Estructura curricular actualizada.' })
    dialogEtapa.value = false
    const { data } = await api.get('/programas')
    programas.value = data.data; progTemp.value = programas.value.find(p => p.id === progTemp.value.id)
  } catch {
    $q.notify({ color: 'negative', message: 'Error al actualizar la etapa.' })
  } finally { guardandoEtapa.value = false }
}

async function eliminarEtapa(id) {
  $q.dialog({ title: 'Confirmar', message: '¿Eliminar esta fase?', dark: true, ok: { color: 'red-9', label: 'Eliminar' }, cancel: true }).onOk(async () => {
    try {
      await api.delete(`/programas/etapas/${id}`)
      const { data } = await api.get('/programas')
      programas.value = data.data; progTemp.value = programas.value.find(p => p.id === progTemp.value.id)
      $q.notify({ color: 'emerald', message: 'Fase eliminada.' })
    } catch { $q.notify({ color: 'negative', message: 'Fallo al eliminar.' }) }
  })
}

// ── Gestión de Materias ──
function abrirNuevaMateria(etapaId) {
  modoMateria.value = 'crear'
  materiaForm.value = { nombre: '', codigo: '', horas: 0, etapa_id: etapaId }
  dialogMateria.value = true
}

function editarMateria(mat, etapaId) {
  modoMateria.value = 'editar'
  materiaForm.value = { ...mat, etapa_id: etapaId }
  dialogMateria.value = true
}

async function guardarMateria() {
  guardandoMat.value = true
  try {
    if (modoMateria.value === 'crear') await api.post('/gestion-materias', materiaForm.value)
    else await api.put(`/gestion-materias/${materiaForm.value.id}`, materiaForm.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Materia actualizada.' })
    dialogMateria.value = false
    const { data } = await api.get('/programas')
    programas.value = data.data; progTemp.value = programas.value.find(p => p.id === progTemp.value.id)
  } catch {
    $q.notify({ color: 'negative', message: 'Error al actualizar materia.' })
  } finally { guardandoMat.value = false }
}

function eliminarMateria(id) {
  $q.dialog({ title: 'Confirmar', message: '¿Eliminar esta materia y todo su contenido?', dark: true, ok: { color: 'red-9', label: 'Eliminar' }, cancel: true }).onOk(async () => {
    try {
      await api.delete(`/gestion-materias/${id}`)
      const { data } = await api.get('/programas')
      programas.value = data.data; progTemp.value = programas.value.find(p => p.id === progTemp.value.id)
      $q.notify({ color: 'emerald', message: 'Materia eliminada.' })
    } catch { $q.notify({ color: 'negative', message: 'Fallo al eliminar.' }) }
  })
}

// ── Gestión LMS (Lecciones y Preguntas) ──
async function gestionarLms(mat) {
  matActiva.value = { ...mat }
  // Formatear fechas para los pickers de Quasar
  if (matActiva.value.sesion_viva_inicio) matActiva.value.sesion_viva_inicio = dayjs(matActiva.value.sesion_viva_inicio).format('YYYY-MM-DD HH:mm')
  if (matActiva.value.sesion_viva_fin)    matActiva.value.sesion_viva_fin    = dayjs(matActiva.value.sesion_viva_fin).format('YYYY-MM-DD HH:mm')
  
  tabLms.value = 'lecciones'
  loadingLms.value = true
  try {
    await cargarContenidoLms()
    dialogLms.value = true
  } catch {
    $q.notify({ color: 'negative', message: 'Error al cargar contenido pedagógico.' })
  } finally {
    loadingLms.value = false
  }
}

async function cargarContenidoLms() {
  const [lecs, pregs] = await Promise.all([
    api.get(`/gestion-materias/${matActiva.value.id}/lecciones`),
    api.get(`/gestion-materias/${matActiva.value.id}/preguntas`)
  ])
  lecciones.value = lecs.data.data
  bancoPreguntas.value = pregs.data.data
}

async function guardarConfigGeneral() {
  guardandoLms.value = true
  try {
    await api.put(`/gestion-materias/${matActiva.value.id}/lms`, matActiva.value)
    $q.notify({ color: 'emerald', message: 'Configuración general guardada.' })
  } catch {
    $q.notify({ color: 'negative', message: 'Error al guardar configuración.' })
  } finally { guardandoLms.value = false }
}

// ── CRUD Lecciones ──
async function eliminarLeccion(id) {
  try {
    await api.delete(`/gestion-materias/${matActiva.value.id}/lecciones/${id}`)
    $q.notify({ color: 'emerald', message: 'Lección eliminada.' })
    cargarContenidoLms()
  } catch { $q.notify({ color: 'negative', message: 'Error al eliminar.' }) }
}

// ── CRUD Preguntas ──
async function eliminarPregunta(id) {
  try {
    await api.delete(`/gestion-materias/${matActiva.value.id}/preguntas/${id}`)
    $q.notify({ color: 'emerald', message: 'Pregunta eliminada.' })
    cargarContenidoLms()
  } catch { $q.notify({ color: 'negative', message: 'Error al eliminar.' }) }
}

function abrirNuevaLeccion() {
  leccionForm.value = { titulo: '', video_url: '', documento_url: '', descripcion: '', orden: lecciones.value.length + 1 }
  dialogNuevaLeccion.value = true
}

function editarLeccion(lec) {
  leccionForm.value = { ...lec }
  dialogNuevaLeccion.value = true
}

async function guardarLeccion() {
  try {
    if (leccionForm.value.id) {
      await api.put(`/gestion-materias/${matActiva.value.id}/lecciones/${leccionForm.value.id}`, leccionForm.value)
    } else {
      await api.post(`/gestion-materias/${matActiva.value.id}/lecciones`, leccionForm.value)
    }
    $q.notify({ color: 'emerald', message: 'Contenido actualizado correctamente.' })
    dialogNuevaLeccion.value = false
    cargarContenidoLms()
  } catch { $q.notify({ color: 'negative', message: 'Error al procesar la lección.' }) }
}

function abrirNuevaPregunta() {
  preguntaForm.value = { pregunta: '', opciones: ['', '', '', ''], respuesta_correcta: '', nivel_dificultad: 1 }
  dialogNuevaPregunta.value = true
}

function editarPregunta(preg) {
  preguntaForm.value = { ...preg }
  dialogNuevaPregunta.value = true
}

async function guardarPregunta() {
  if (!preguntaForm.value.respuesta_correcta) return $q.notify({ color: 'warning', message: 'Selecciona la respuesta correcta.' })
  try {
    if (preguntaForm.value.id) {
      await api.put(`/gestion-materias/${matActiva.value.id}/preguntas/${preguntaForm.value.id}`, preguntaForm.value)
    } else {
      await api.post(`/gestion-materias/${matActiva.value.id}/preguntas`, preguntaForm.value)
    }
    $q.notify({ color: 'emerald', message: 'Pregunta actualizada en el banco.' })
    dialogNuevaPregunta.value = false
    cargarContenidoLms()
  } catch { $q.notify({ color: 'negative', message: 'Error al guardar reactivo.' }) }
}

// ── Guardar Vuelo ──
async function guardarVuelo() {
  guardandoVuelo.value = true
  try {
    await api.post('/vuelos', vueloForm.value)
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Misión de vuelo certificada exitosamente.' })
    dialogVuelo.value = false
    cargarDatos()
  } catch {
    $q.notify({ color: 'negative', message: 'Error al certificar la misión de vuelo.' })
  } finally { guardandoVuelo.value = false }
}

// ── Gestión Planes de Clase ──
function abrirNuevoPlanClase() {
  planForm.value = { id: null, instructor_id: null, materia_id: null, fecha: dayjs().format('YYYY-MM-DD'), duracion_min: 60, estado: 'planificada', objetivos: '', contenido: '', recursos: '' }
  dialogPlan.value = true
}

function editarPlanClase(plan) {
  planForm.value = { ...plan }
  dialogPlan.value = true
}

async function guardarPlanClase() {
  guardandoPlan.value = true
  try {
    if (planForm.value.id) await api.put(`/planes-clase/${planForm.value.id}`, planForm.value)
    else await api.post('/planes-clase', planForm.value)
    $q.notify({ color: 'emerald', message: 'Plan de clase guardado.' })
    dialogPlan.value = false
    cargarDatos()
  } catch {
    $q.notify({ color: 'negative', message: 'Error al guardar el plan de clase.' })
  } finally { guardandoPlan.value = false }
}

function eliminarPlanClase(id) {
  $q.dialog({ title: 'Confirmar', message: '¿Eliminar este plan de clase?', dark: true, ok: { color: 'red-9', label: 'Eliminar' }, cancel: true }).onOk(async () => {
    try {
      await api.delete(`/planes-clase/${id}`)
      $q.notify({ color: 'emerald', message: 'Plan de clase eliminado.' })
      cargarDatos()
    } catch { $q.notify({ color: 'negative', message: 'Fallo al eliminar.' }) }
  })
}



async function cargarEndorsements() {
  loadingEndorsements.value = true
  try {
    const { data } = await api.get('/endorsements')
    endorsements.value = data || []
  } catch { endorsements.value = [] }
  finally { loadingEndorsements.value = false }
}

async function cargarCertRecientes() {
  loadingCerts.value = true
  try {
    const { data } = await api.get('/certificados?per_page=20')
    certRecientes.value = data.data ?? data ?? []
  } catch { certRecientes.value = [] }
  finally { loadingCerts.value = false }
}

onMounted(() => {
  cargarDatos()
  cargarEndorsements()
  cargarCertRecientes()
})
// ── Acciones Estudiante ──
function filterProgramas(val, update) {
  if (val === '') {
    update(() => { programasFilteredOptions.value = programasOptions.value })
    return
  }
  update(() => {
    const needle = val.toLowerCase()
    programasFilteredOptions.value = programasOptions.value.filter(v => v.label.toLowerCase().indexOf(needle) > -1)
  })
}

function abrirNuevoEstudiante() {
  estForm.value = { nombres: '', apellidos: '', tipo_documento: 'CC', num_documento: '', fecha_nacimiento: '2000-01-01', fecha_ingreso: dayjs().format('YYYY-MM-DD'), programa_id: null, observaciones: '' }
  dialogNuevoEstudiante.value = true
}

async function guardarNuevoEstudiante() {
  guardandoNuevoEst.value = true
  try {
    const { data } = await api.post('/estudiantes', estForm.value)
    $q.notify({ color: 'emerald', message: data.mensaje || 'Estudiante matriculado con éxito.' })
    dialogNuevoEstudiante.value = false
    cargarDatos()
  } catch (e) {
    console.error('Error en POST /estudiantes:', e)
    const msg = e.response?.data?.mensaje || 'Error al matricular estudiante.'
    $q.notify({ color: 'negative', message: msg })
  } finally { guardandoNuevoEst.value = false }
}

function abrirNuevaNota(est) {
  estActivo.value = est
  notaForm.value = { estudiante_id: est.id, materia_id: null, nota: null, fecha_evaluacion: dayjs().format('YYYY-MM-DD'), observaciones: '' }
  // Cargar materias del programa del estudiante
  materiasOptions.value = []
  if (est.programa?.etapas) {
    est.programa.etapas.forEach(et => {
      et.materias?.forEach(m => {
        materiasOptions.value.push({ label: `${m.codigo} - ${m.nombre}`, value: m.id })
      })
    })
  }
  dialogNota.value = true
}

async function guardarNota() {
  guardandoNota.value = true
  try {
    await api.post(`/estudiantes/${notaForm.value.estudiante_id}/notas`, notaForm.value)
    $q.notify({ color: 'emerald', message: 'Calificación registrada en el expediente.' })
    dialogNota.value = false
    cargarDatos()
  } catch { $q.notify({ color: 'negative', message: 'Error al registrar nota.' }) }
  finally { guardandoNota.value = false }
}

function abrirNuevoCertificado(est) {
  estActivo.value = est
  certForm.value = { tipo: 'clase_1', numero_certificado: '', fecha_emision: '', fecha_vencimiento: '', centro_aeromedico: '', restricciones: '' }
  dialogCertMedico.value = true
}

async function guardarCertMedico() {
  guardandoCert.value = true
  try {
    await api.post(`/estudiantes/${estActivo.value.id}/cert-medicos`, certForm.value)
    $q.notify({ color: 'emerald', message: 'Certificado médico actualizado y sincronizado.' })
    dialogCertMedico.value = false
    cargarDatos()
  } catch { $q.notify({ color: 'negative', message: 'Error al registrar certificado.' }) }
  finally { guardandoCert.value = false }
}
const programarEnGoogle = () => {
    if (!matActiva.value.sesion_viva_inicio) {
        $q.notify({ type: 'warning', message: 'Seleccione fecha y hora de inicio primero.' });
        return;
    }
    // Formatear fechas para Google Calendar (Formato: YYYYMMDDTHHmmSS)
    // Asumimos hora local, para UTC se añadiría 'Z' al final
    const fmt = (str) => {
        if (!str) return '';
        const clean = str.replace(/[- :]/g, ''); // 202604211200
        return clean.substring(0, 8) + 'T' + clean.substring(8, 12) + '00';
    }

    const start = fmt(matActiva.value.sesion_viva_inicio);
    const end = fmt(matActiva.value.sesion_viva_fin || matActiva.value.sesion_viva_inicio);
    
    const title = encodeURIComponent('CLASE VIRTUAL RAC 141: ' + matActiva.value.nombre);
    
    // Simplificamos el URL para evitar errores de creación en Google
    const url = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${title}&dates=${start}/${end}`;
    
    window.open(url, '_blank');
    
    $q.notify({ 
        color: 'blue-9', 
        icon: 'info', 
        message: 'Se ha abierto Google Calendar. Haz clic en "GUARDAR" en la página de Google y luego COPIA el link de Meet.',
        timeout: 10000
    });
}

// --- Tesorería Logic ---
const filtroTesore = ref('')
const alumnosTesore = ref([])
const estSelTesore = ref(null)
const materiasEstTesore = ref([])
const dialogHabilitar = ref(false)
const matAHabilitar = ref(null)
const numReciboHabil = ref('')

const buscarAlumnoTesore = async () => {
    if (!filtroTesore.value) return
    const { data } = await api.get('/estudiantes', { params: { buscar: filtroTesore.value } })
    alumnosTesore.value = data.data.data
}

const seleccionarEstTesore = async (est) => {
    estSelTesore.value = est
    const res = await api.get(`/estudiantes/${est.id}/expediente`)
    const reintentos = res.data.data.estudiante.reintentos_autorizados || []
    
    materiasEstTesore.value = res.data.data.estudiante.notas.reduce((acc, n) => {
        const exist = acc.find(x => x.id === n.materia_id)
        if (!exist) acc.push({ ...n.materia, nota_max: n.nota, intentos: 1 })
        else {
            exist.intentos++
            if (n.nota > exist.nota_max) exist.nota_max = n.nota
        }
        return acc
    }, [])
    
    // Si la materia no tiene notas aún, pero está en el programa, la agregamos
    est.programa.etapas.forEach(et => {
        et.materias.forEach(m => {
            let target = materiasEstTesore.value.find(x => x.id === m.id)
            if (!target) {
                target = { ...m, nota_max: 0, intentos: 0 }
                materiasEstTesore.value.push(target)
            }
        })
    })

    materiasEstTesore.value.forEach(m => {
        m.habilitado_manual = reintentos.some(r => r.materia_id === m.id && r.usado === 0)
    })
}

const abrirDialogHabilitar = (mat) => {
    matAHabilitar.value = mat
    $q.dialog({
        title: '🔑 HABILITAR REINTENTO DE EXAMEN',
        message: `Estás autorizando un nuevo intento para <b>${estSelTesore.value.persona.nombres}</b> en la materia <b>${mat.nombre}</b>. <br><br>Ingrese el número de recibo de caja de Tesorería:`,
        html: true,
        prompt: { model: numReciboHabil.value, type: 'text' },
        cancel: true,
        persistent: true,
        ok: { label: 'Autorizar Ahora', color: 'emerald', unelevated: true }
    }).onOk(async (val) => {
        try {
            await api.post('/aula-virtual/autorizar-reintento', {
                estudiante_id: estSelTesore.value.id,
                materia_id: mat.id,
                num_recibo: val
            })
            $q.notify({ type: 'positive', message: 'Examen habilitado correctamente.' })
            seleccionarEstTesore(estSelTesore.value) // Refresh
        } catch (e) {
            $q.notify({ type: 'negative', message: 'Error habilitando examen.' })
        }
    })
}

</script>

<style lang="scss" scoped>

.animate-slide-up { animation: slideUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; }

@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.shadow-inner      { box-shadow: inset 0 2px 15px rgba(0,0,0,0.4); }

.bg-black-20       { background: rgba(0,0,0,0.2); }
.opacity-10        { opacity: 0.1; }

.pulsate { animation: pulsate 2.5s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.65; } }

.bonus-grid { background-image: radial-gradient(rgba(161, 11, 19, 0.04) 1px, transparent 1px); background-size: 30px 30px; }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 80% 0%, rgba(161, 11, 19, 0.15) 0%, transparent 55%); pointer-events: none; }

.hover-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  &:hover { transform: translateY(-8px); border-color: rgba(161, 11, 19, 0.4) !important; box-shadow: 0 25px 50px rgba(0,0,0,0.4), 0 0 20px rgba(161,11,19,0.15) !important; }
}

.hover-row { transition: all 0.2s; cursor: pointer; &:hover { background: rgba(255,255,255,0.03); } }

.syllabus-icon-panel {
  width: 56px; height: 56px; border-radius: 16px;
  background: rgba(161, 11, 19, 0.1); border: 1px solid rgba(161, 11, 19, 0.25);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
  &.q-field--focused :deep(.q-field__control) { border-color: #A10B13 !important; background: rgba(161,11,19,0.04) !important; }
}

.h-full { height: 100%; }
.pb-md { padding-bottom: 16px; }
.hover-red { transition: color 0.2s; &:hover { color: #A10B13 !important; } }

.bg-kpi-vuelo  { background: radial-gradient(circle at top left, rgba(161, 11, 19, 0.08), transparent 70%) !important; }
.bg-kpi-tierra { background: radial-gradient(circle at top left, rgba(255, 255, 255, 0.03), transparent 70%) !important; }
.bg-kpi-sim    { background: radial-gradient(circle at top left, rgba(148, 163, 184, 0.05), transparent 70%) !important; }

.opacity-50 { opacity: 0.5; }
.glass-btn-hover { transition: all 0.3s; &:hover { background: rgba(255,255,255,0.05); color: white !important; border-color: rgba(161, 11, 19, 0.5) !important; } }
.hover-red-bg { transition: all 0.3s; &:hover { background: #A10B13 !important; color: white !important; } }


</style>
