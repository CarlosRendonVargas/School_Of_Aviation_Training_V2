<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado de Auditoría UAEAC ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="fact_check" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="rac-page-subtitle">CERTIFICACIÓN VIGENTE · RAC 141.11 / 141.77</div>
          <h1 class="rac-page-title">Cumplimiento Regulatorio</h1>
        </div>
      </div>
      <q-btn 
        v-if="puedeEditar" 
        unelevated color="red-9" icon="file_download" label="Generar Reporte UAEAC" 
        class="premium-btn shadow-24" 
        @click="exportarRegistroRAC"
      />
    </div>

    <!-- ══ Acceso Rápido Sub-Módulos ══ -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-sm-3" v-for="link in subModulos" :key="link.to">
        <q-card flat bordered class="text-center q-pa-md cursor-pointer hover-card" @click="$router.push(link.to)">
          <q-icon :name="link.icon" :color="link.color" size="32px" class="q-mb-sm" />
          <div class="text-weight-bold text-white" style="font-size:13px">{{ link.label }}</div>
          <div class="text-caption text-grey-6">{{ link.sub }}</div>
        </q-card>
      </div>
    </div>

    <!-- ══ Navegación de Control de Calidad ══ -->
    <q-card class="premium-glass-card shadow-24 overflow-hidden q-mb-xl rounded-20">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-5"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        style="padding-left: 10px; border-bottom: 1px solid rgba(255,255,255,0.05)"
      >
        <q-tab name="checklist" icon="verified"    :label="$q.screen.gt.xs ? 'Checklist RAC' : ''" />
        <q-tab name="documentos" icon="inventory_2" :label="$q.screen.gt.xs ? 'Manuales' : ''" />
        <q-tab name="audit"      icon="history_edu" :label="$q.screen.gt.xs ? 'Auditoría' : ''" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
        
        <!-- ════ CHECKLIST DE CUMPLIMIENTO ════ -->
        <q-tab-panel name="checklist" class="q-pa-xl">
          <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Estatus General de la Estación</div>
          
          <div class="q-gutter-y-sm">
            <div v-for="item in checklistItems" :key="item.rac"
              class="premium-glass-card q-pa-md q-pa-sm-lg row items-center no-wrap hover-row border-red-low q-mb-sm relative-position overflow-hidden">
              <q-icon 
                :name="item.ok ? 'verified' : 'report_problem'" 
                :color="item.ok ? 'emerald' : 'red-9'" 
                size="24px" class="q-mr-md q-mr-sm-lg pulsate flex-shrink-0" 
                :style="item.ok ? '' : 'filter: drop-shadow(0 0 10px #A10B13)'"
              />
              <div class="col min-w-0">
                <div class="column">
                  <div class="font-mono text-red-9 text-weight-bold" style="font-size:10px">{{ item.rac }}</div>
                  <div class="text-subtitle2 text-grey-2 font-head text-weight-bold truncate-2-lines">{{ item.desc }}</div>
                  <div v-if="item.nota" class="text-caption text-grey-6 font-mono q-mt-xs italic truncate" style="font-size:10px">ℹ {{ item.nota }}</div>
                </div>
              </div>
              <div class="side-info text-right q-ml-sm">
                 <q-badge :color="item.ok ? 'emerald' : 'red-9'" :label="item.ok ? 'OK' : '!!!'" class="font-mono text-weight-bold shadow-24" />
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ════ BÓVEDA DE DOCUMENTOS ════ -->
        <q-tab-panel name="documentos" class="q-pa-xl">
          <div class="row justify-between items-center q-mb-xl">
             <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest">Documentación Técnica Certificada</div>
             <q-btn v-if="puedeEditar" outline color="red-9" icon="upload_file" label="Subir Enmienda" class="font-mono text-weight-bold" @click="notificarProximamente" />
          </div>

          <div class="row q-col-gutter-lg">
             <div v-for="doc in documentos" :key="doc.id" class="col-12 col-sm-6">
                <q-card class="premium-glass-card q-pa-lg border-red-left premium-hover-card">
                   <div class="row items-center no-wrap">
                      <div class="q-mr-md text-center bg-black-20 q-pa-md rounded-12" style="width: 70px">
                         <div class="text-h6 text-red-9 font-mono text-weight-bolder">{{ doc.tipo }}</div>
                         <div class="text-grey-6 font-mono" style="font-size:8px">TYPE</div>
                      </div>
                      <div class="col">
                         <div class="text-subtitle1 text-white font-head text-weight-bold">{{ doc.titulo }}</div>
                         <div class="text-caption text-grey-6 font-mono">Res. UAEAC: {{ formatFechaCO(doc.fecha_documento) }} · Rev. {{ doc.version }}</div>
                      </div>
                      <q-btn flat round icon="download" color="grey-7" :href="doc.archivo_url" target="_blank" />
                   </div>
                   <div class="row items-center q-mt-md justify-between">
                      <q-badge :color="doc.aprobado_uaeac ? 'emerald' : 'orange-9'" :label="doc.aprobado_uaeac ? 'APROBADO UAEAC' : 'EN TRÁMITE'" class="font-mono" />
                      <div class="text-caption font-mono" :class="doc.vigente ? 'text-emerald' : 'text-grey-7'">{{ doc.vigente ? '● VIGENTE' : 'HISTÓRICO' }}</div>
                   </div>
                </q-card>
             </div>
          </div>
        </q-tab-panel>

        <!-- ════ REGISTRO DE AUDITORÍA (Logs) ════ -->
        <q-tab-panel name="audit" class="q-pa-xl">
          <div class="text-subtitle2 text-grey-6 font-mono uppercase tracking-widest q-mb-xl">Trazabilidad de Datos (RAC 141.77)</div>
          
          <q-table 
             :rows="auditLogs" 
             :columns="colsAudit" 
             flat dark 
             class="rac-table shadow-24"
             row-key="id"
          >
            <template #body-cell-accion="props">
              <q-td :props="props">
                <q-badge 
                  :color="props.value === 'INSERT' ? 'emerald' : props.value === 'DELETE' ? 'red-9' : 'orange-9'" 
                  :label="props.value" 
                  class="font-mono text-weight-bold" 
                />
              </q-td>
            </template>
            <template #body-cell-created_at="props">
              <q-td :props="props" class="font-mono text-grey-5">{{ props.value }}</q-td>
            </template>
          </q-table>
        </q-tab-panel>

      </q-tab-panels>
    </q-card>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import { formatFechaCO } from 'src/utils/formatters'

const authStore   = useAuthStore()
const tab         = ref('checklist')
const documentos  = ref([])
const auditLogs   = ref([])
const puedeEditar = ['dir_ops', 'admin'].includes(authStore.rol)

const subModulos = [
  { to: '/cumplimiento/enmiendas',      label: 'Enmiendas MOE/PIA', icon: 'edit_document',    color: 'orange',   sub: 'Control de cambios' },
  { to: '/cumplimiento/correspondencia',label: 'Correspondencia',   icon: 'mark_email_read',  color: 'blue',     sub: 'Radicados UAEAC' },
  { to: '/cumplimiento/reportes',       label: 'Reportes UAEAC',    icon: 'bar_chart',         color: 'positive', sub: 'Estadísticas RAC' },
  { to: '/vencimientos',                label: 'Alertas Vencimiento',icon: 'timer',            color: 'red-9',    sub: 'Radar de vencimientos' },
]

import { useQuasar, exportFile } from 'quasar'
const $q = useQuasar()

function exportarRegistroRAC() {
  if (auditLogs.value.length === 0) {
    $q.notify({ color: 'orange-10', icon: 'warning', message: 'No hay registros de trazabilidad para exportar.' })
    return
  }

  let content = 'FECHA_UTC,MODUL_AFECTADO,ACCION_TECNICA,REFERENCIA_ID\r\n'
  
  auditLogs.value.forEach(row => {
    const fecha = row.created_at ? row.created_at.slice(0,19).replace('T', ' ') : 'N/A'
    const tabla = row.tabla || 'N/A'
    const accion = row.accion || 'N/A'
    const ref_id = row.registro_id || 'N/A'
    content += `"${fecha}","${tabla}","${accion}","${ref_id}"\r\n`
  })

  // BOM para reconocimiento UTF-8 nativo en Excel
  const blobData = "\ufeff" + content
  
  const status = exportFile('Registro_Auditoria_UAEAC_RAC141_77.csv', blobData, 'text/csv;charset=utf-8;')

  if (status !== true) {
    $q.notify({ color: 'red-9', icon: 'report_problem', message: 'Fallo al exportar o bloqueado por el navegador.' })
  } else {
    $q.notify({ color: 'emerald-9', icon: 'verified', message: 'Reporte Oficial UAEAC RAC 141.77 descargado.' })
  }
}

const checklistItems = [
  { rac: 'RAC 141.11', desc: 'Certificado de aprobación de la escuela vigente', ok: true },
  { rac: 'RAC 141.13', desc: 'Manual de Operaciones de la Escuela (MOE) aprobado', ok: true },
  { rac: 'RAC 141.31', desc: 'Director de Operaciones designado con cert. vigente', ok: true },
  { rac: 'RAC 141.35', desc: 'Instructores con licencias y habilitaciones vigentes', ok: true },
  { rac: 'RAC 141.51', desc: 'Aeronaves con certificado de aeronavegabilidad vigente', ok: true },
  { rac: 'RAC 141.67', desc: 'Programa de Instrucción Aprobado (PIA) vigente', ok: true },
  { rac: 'RAC 141.77', desc: 'Registros conservados y disponibles para UAEAC', ok: true },
  { rac: 'RAC 67',     desc: 'Estudiantes con certificado médico aeronáutico vigente', ok: false, nota: 'Verificar estudiantes próximos a vencer en RADAR DE VENCIMIENTOS' },
  { rac: 'Anexo 19',   desc: 'Sistema de Gestión de Seguridad (SMS) operativo', ok: true },
]

const colsAudit = [
  { name: 'created_at', label: 'FECHA/HORA (UTC)', field: row => row.created_at?.slice(0,19), align: 'left' },
  { name: 'tabla',      label: 'MÓDULO AFECTADO',    field: 'tabla',   align: 'left' },
  { name: 'accion',     label: 'ACCIÓN TÉCNICA',   field: 'accion',  align: 'center' },
  { name: 'registro_id',label: 'REFE. ID',       field: 'registro_id', align: 'center' },
]

async function cargar() {
  try {
    const [docsRes, auditRes] = await Promise.all([
      api.get('/cumplimiento/documentos'),
      api.get('/cumplimiento/audit-log'),
    ])
    documentos.value = docsRes.data.data || []
    auditLogs.value  = auditRes.data.data?.data || []
  } catch { /* silencioso */ }
}

onMounted(cargar)
</script>

<style lang="scss" scoped>

.hover-card { transition: all 0.2s; &:hover { transform: translateY(-4px); border-color: rgba(255,255,255,0.2) !important; } }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }

.hover-row { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); cursor:pointer; &:hover { background: rgba(255,255,255,0.03); transform: scale(1.005); } }
.premium-hover-card { transition: all 0.3s; &:hover { transform: translateY(-8px); border-color: #A10B13 !important; box-shadow: 0 20px 40px rgba(0,0,0,0.4); } }

.bg-black-20 { background: rgba(0,0,0,0.2); }

.flex-shrink-0 { flex-shrink: 0; }
.min-w-0 { min-width: 0; }
.truncate-2-lines {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

@media (max-width: 599px) {
  .q-tab-panel { padding: 16px !important; }
}
</style>
