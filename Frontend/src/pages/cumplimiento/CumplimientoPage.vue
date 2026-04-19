<template>
<!-- CumplimientoPage.vue -->
  <q-page padding style="padding-bottom:80px">
    <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">RAC 141.11 · 141.13 · 141.77</div>
    <div class="font-head text-white q-mb-lg" style="font-size:20px; font-weight:700">Cumplimiento Regulatorio</div>

    <q-tabs v-model="tab" dense align="left" class="q-mb-md"
      active-color="primary" indicator-color="primary" style="border-bottom:1px solid rgba(255,255,255,.08)">
      <q-tab name="checklist" icon="checklist"    label="Checklist RAC 141" no-caps />
      <q-tab name="documentos" icon="description" label="MOE / PIA"         no-caps />
      <q-tab name="audit"      icon="history"     label="Audit Log"         no-caps />
    </q-tabs>

    <q-tab-panels v-model="tab" animated dark style="background:transparent">
      <!-- Checklist -->
      <q-tab-panel name="checklist" class="q-pa-none">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">Estado general de cumplimiento</div>
            <div v-for="item in checklistItems" :key="item.rac"
              class="row items-center q-gutter-sm q-py-sm" style="border-bottom:1px solid rgba(255,255,255,.05)">
              <q-icon :name="item.ok ? 'check_circle' : 'cancel'" :color="item.ok ? 'positive' : 'negative'" size="20px" />
              <div class="col">
                <div class="row items-center q-gutter-sm">
                  <span class="font-mono" style="font-size:10px; color:#3b82f6; background:rgba(59,130,246,.1); padding:1px 7px; border-radius:3px">{{ item.rac }}</span>
                  <span style="font-size:13px; color:#e2e8f0">{{ item.desc }}</span>
                </div>
                <div v-if="item.nota" style="font-size:11px; color:#94a3b8; margin-top:2px">{{ item.nota }}</div>
              </div>
              <q-chip dense :color="item.ok ? 'positive' : 'negative'" :label="item.ok ? '✔ OK' : '⚠ Revisar'"
                text-color="white" style="font-size:10px" />
            </div>
          </q-card-section>
        </q-card>
      </q-tab-panel>

      <!-- Documentos MOE/PIA -->
      <q-tab-panel name="documentos" class="q-pa-none">
        <div class="row justify-end q-mb-md" v-if="puedeEditar">
          <q-btn unelevated color="primary" icon="upload_file" label="Subir documento" no-caps style="border-radius:8px" />
        </div>
        <q-list dark separator style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px">
          <q-item v-for="doc in documentos" :key="doc.id" style="padding:14px 16px">
            <q-item-section avatar>
              <q-chip dense :color="tipoDocColor(doc.tipo)" :label="doc.tipo" text-color="white" style="font-size:10px; max-width:80px" />
            </q-item-section>
            <q-item-section>
              <q-item-label style="font-size:13px; color:#e2e8f0">{{ doc.titulo }}</q-item-label>
              <q-item-label caption>v{{ doc.version }} · {{ doc.fecha_documento }}
                <span v-if="doc.aprobado_uaeac" style="color:#22c55e"> · Aprobado UAEAC</span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <div class="row q-gutter-xs">
                <q-chip dense :color="doc.vigente ? 'positive' : 'grey'" :label="doc.vigente ? 'VIGENTE' : 'HISTÓRICO'"
                  text-color="white" style="font-size:9px" />
                <q-btn flat round dense icon="download" color="grey-5" size="sm"
                  :href="doc.archivo_url" target="_blank" />
              </div>
            </q-item-section>
          </q-item>
        </q-list>
      </q-tab-panel>

      <!-- Audit Log -->
      <q-tab-panel name="audit" class="q-pa-none">
        <div class="font-mono q-mb-md" style="font-size:11px; color:#475569">
          Registro de cambios del sistema — RAC 141.77 (conservar 5 años)
        </div>
        <q-table :rows="auditLogs" :columns="colsAudit" flat dark class="tabla-rac"
          style="background:#0f1218; border:1px solid rgba(255,255,255,.07); border-radius:12px" row-key="id">
          <template #body-cell-accion="{ value }">
            <q-td>
              <q-chip dense :color="value === 'INSERT' ? 'positive' : value === 'DELETE' ? 'negative' : 'warning'"
                :label="value" text-color="white" style="font-size:10px" />
            </q-td>
          </template>
        </q-table>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const authStore   = useAuthStore()
const tab         = ref('checklist')
const documentos  = ref([])
const auditLogs   = ref([])
const puedeEditar = ['dir_ops', 'admin'].includes(authStore.rol)

const checklistItems = [
  { rac: 'RAC 141.11', desc: 'Certificado de aprobación de la escuela vigente', ok: true },
  { rac: 'RAC 141.13', desc: 'Manual de Operaciones de la Escuela (MOE) aprobado', ok: true },
  { rac: 'RAC 141.31', desc: 'Director de Operaciones designado con cert. vigente', ok: true },
  { rac: 'RAC 141.35', desc: 'Instructores con licencias y habilitaciones vigentes', ok: true },
  { rac: 'RAC 141.51', desc: 'Aeronaves con certificado de aeronavegabilidad vigente', ok: true },
  { rac: 'RAC 141.67', desc: 'Programa de Instrucción Aprobado (PIA) vigente', ok: true },
  { rac: 'RAC 141.77', desc: 'Registros conservados y disponibles para UAEAC', ok: true },
  { rac: 'RAC 67',     desc: 'Estudiantes con certificado médico aeronáutico vigente', ok: false, nota: 'Verificar estudiantes próximos a vencer' },
  { rac: 'Anexo 19',   desc: 'Sistema de Gestión de Seguridad (SMS) operativo', ok: true },
]

const tipoDocColor = (t) => ({ MOE: 'primary', PIA: 'teal', enmienda: 'amber-9', circular: 'purple', certificado: 'positive' }[t] || 'grey')

const colsAudit = [
  { name: 'created_at', label: 'Fecha',    field: row => row.created_at?.slice(0,19), align: 'left' },
  { name: 'tabla',      label: 'Tabla',    field: 'tabla',   align: 'left' },
  { name: 'accion',     label: 'Acción',   field: 'accion',  align: 'center' },
  { name: 'registro_id',label: 'ID',       field: 'registro_id', align: 'center' },
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
