<template>
  <q-page padding style="padding-bottom:80px">

    <q-skeleton v-if="cargando" type="rect" height="200px" dark class="q-mb-md" />

    <template v-else-if="expediente">

      <!-- Header del expediente -->
      <div class="row items-start q-gutter-md q-mb-lg">
        <q-avatar size="64px" color="primary" text-color="white" style="font-size:22px;font-weight:700">
          {{ iniciales }}
        </q-avatar>
        <div class="col">
          <div class="font-head text-white" style="font-size:22px;font-weight:800">
            {{ expediente.estudiante.persona?.nombres }} {{ expediente.estudiante.persona?.apellidos }}
          </div>
          <div class="row items-center q-gutter-sm q-mt-xs">
            <div class="font-mono" style="font-size:11px;color:#475569">
              {{ expediente.estudiante.num_expediente }}
            </div>
            <q-chip dense :color="colorEstado" text-color="white"
              :label="expediente.estudiante.estado" style="font-size:10px" />
            <q-chip dense color="blue-9" text-color="white"
              :label="expediente.estudiante.programa?.nombre" style="font-size:10px" />
          </div>
        </div>
        <q-btn flat round dense icon="arrow_back" color="grey-5"
          @click="$router.push('/estudiantes')" />
      </div>

      <!-- Tabs de secciones -->
      <q-tabs v-model="tab" dense align="left" active-color="primary" indicator-color="primary"
        style="border-bottom:1px solid rgba(255,255,255,.08)" class="q-mb-md">
        <q-tab name="resumen"   label="Resumen"    icon="dashboard"  no-caps />
        <q-tab name="horas"     label="Horas RAC"  icon="flight"     no-caps />
        <q-tab name="notas"     label="Académico"  icon="school"     no-caps />
        <q-tab name="medico"    label="Médico"     icon="medical_services" no-caps />
        <q-tab name="bitacoras" label="Bitácoras"  icon="book"       no-caps />
      </q-tabs>

      <q-tab-panels v-model="tab" animated dark style="background:transparent">

        <!-- ─── Resumen ─────────────────────────────────────────────── -->
        <q-tab-panel name="resumen" class="q-pa-none">
          <div class="row q-col-gutter-md">

            <!-- Info personal -->
            <div class="col-12 col-md-4">
              <q-card flat class="card-rac" style="background:#0f1218">
                <q-card-section>
                  <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">
                    DATOS PERSONALES
                  </div>
                  <div v-for="campo in datosPersonales" :key="campo.label" class="q-mb-sm">
                    <div style="font-size:11px;color:#64748b">{{ campo.label }}</div>
                    <div class="text-white" style="font-size:13px;margin-top:1px">{{ campo.valor || '—' }}</div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Progreso general -->
            <div class="col-12 col-md-8">
              <q-card flat class="card-rac" style="background:#0f1218">
                <q-card-section>
                  <div class="row items-center justify-between q-mb-md">
                    <div class="font-mono" style="font-size:10px;color:#475569;letter-spacing:2px">
                      PROGRESO GENERAL — {{ expediente.estudiante.programa?.nombre }}
                    </div>
                    <q-badge v-if="expediente.validacion_examen?.aprobado" color="positive" label="✔ Listo para examen" />
                    <q-badge v-else color="warning" label="En formación" />
                  </div>

                  <div class="row q-col-gutter-sm q-mb-md">
                    <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
                      <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:10px;text-align:center">
                        <div class="font-head text-primary" style="font-size:18px;font-weight:700">{{ h.valor }}</div>
                        <div class="font-mono" style="font-size:9px;color:#475569;letter-spacing:.5px">{{ h.label }}</div>
                      </div>
                    </div>
                  </div>

                  <!-- Barra progreso general -->
                  <div class="q-mb-xs row items-center justify-between">
                    <span style="font-size:12px;color:#94a3b8">Progreso del programa</span>
                    <span class="font-mono text-primary" style="font-size:12px">{{ porcentajeGeneral }}%</span>
                  </div>
                  <q-linear-progress :value="porcentajeGeneral/100"
                    :color="porcentajeGeneral>=100?'positive':porcentajeGeneral>60?'warning':'primary'"
                    rounded size="10px" />
                </q-card-section>
              </q-card>
            </div>

          </div>
        </q-tab-panel>

        <!-- ─── Horas RAC 61 ─────────────────────────────────────────── -->
        <q-tab-panel name="horas" class="q-pa-none">
          <q-card flat class="card-rac" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-lg" style="font-size:10px;color:#475569;letter-spacing:2px">
                CUMPLIMIENTO RAC 61 — HORAS MÍNIMAS REQUERIDAS
              </div>

              <div v-for="cat in categoriasHoras" :key="cat.key" class="q-mb-lg">
                <div class="row items-center justify-between q-mb-sm">
                  <div>
                    <div style="font-size:14px;color:#e2e8f0;font-weight:500">{{ cat.label }}</div>
                    <div class="font-mono" style="font-size:10px;color:#475569">{{ cat.rac }}</div>
                  </div>
                  <div class="text-right">
                    <div class="font-mono text-primary" style="font-size:16px;font-weight:700">
                      {{ Number(cat.acumulado || 0).toFixed(1) }}<span style="font-size:11px">h</span>
                    </div>
                    <div class="font-mono" style="font-size:11px;color:#475569">
                      de {{ Number(cat.requerido || 0).toFixed(1) }}h
                    </div>
                  </div>
                </div>
                <q-linear-progress
                  :value="Math.min((cat.porcentaje||0)/100, 1)"
                  :color="cat.cumplido?'positive':cat.porcentaje>60?'warning':'primary'"
                  rounded size="10px" class="q-mb-xs" />
                <div class="row items-center justify-between">
                  <span v-if="!cat.cumplido" style="font-size:11px;color:#ef4444">
                    Faltan {{ cat.faltante?.toFixed(1) }}h
                  </span>
                  <span v-else style="font-size:11px;color:#22c55e">✔ Requisito cumplido</span>
                  <span class="font-mono" style="font-size:11px;color:#64748b">{{ cat.porcentaje?.toFixed(0) }}%</span>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </q-tab-panel>

        <!-- ─── Notas académicas ─────────────────────────────────────── -->
        <q-tab-panel name="notas" class="q-pa-none">
          <q-table :rows="expediente.estudiante.notas || []"
            :columns="columnasNotas" flat dark class="tabla-rac"
            style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
            row-key="id">
            <template #body-cell-nota="{ row }">
              <q-td>
                <span class="font-mono" :style="`font-size:14px;font-weight:700;color:${row.aprobado?'#22c55e':'#ef4444'}`">
                  {{ row.nota?.toFixed(1) }}
                </span>
              </q-td>
            </template>
            <template #body-cell-aprobado="{ row }">
              <q-td class="text-center">
                <q-icon :name="row.aprobado?'check_circle':'cancel'"
                  :color="row.aprobado?'positive':'negative'" size="18px" />
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ─── Certificado médico RAC 67 ────────────────────────────── -->
        <q-tab-panel name="medico" class="q-pa-none">
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-6" v-for="cm in expediente.estudiante.cert_medicos || []" :key="cm.id">
              <q-card flat class="card-rac" style="background:#0f1218">
                <q-card-section>
                  <div class="row items-start justify-between">
                    <div>
                      <div class="font-mono text-primary" style="font-size:11px;letter-spacing:1px">
                        {{ cm.tipo.toUpperCase().replace('_', ' ') }}
                      </div>
                      <div class="text-white q-mt-xs" style="font-size:16px;font-weight:700">
                        N° {{ cm.numero_certificado }}
                      </div>
                    </div>
                    <q-icon :name="esMedicoVigente(cm) ? 'verified' : 'cancel'"
                      :color="esMedicoVigente(cm) ? 'positive' : 'negative'" size="28px" />
                  </div>
                  <q-separator dark class="q-my-sm" />
                  <div class="row q-col-gutter-sm">
                    <div class="col-6">
                      <div style="font-size:11px;color:#64748b">Emisión</div>
                      <div class="font-mono text-white" style="font-size:12px">{{ cm.fecha_emision }}</div>
                    </div>
                    <div class="col-6">
                      <div style="font-size:11px;color:#64748b">Vencimiento</div>
                      <div class="font-mono" :style="`font-size:12px;color:${esMedicoVigente(cm)?'#22c55e':'#ef4444'}`">
                        {{ cm.fecha_vencimiento }}
                      </div>
                    </div>
                    <div class="col-12">
                      <div style="font-size:11px;color:#64748b">Centro aeromédico</div>
                      <div style="font-size:12px;color:#cbd5e1">{{ cm.centro_aeromedico }}</div>
                    </div>
                    <div v-if="cm.restricciones" class="col-12">
                      <div style="font-size:11px;color:#f59e0b">Restricciones</div>
                      <div style="font-size:12px;color:#fde68a">{{ cm.restricciones }}</div>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
            <div v-if="!expediente.estudiante.cert_medicos?.length" class="col-12">
              <div class="text-center q-py-xl" style="color:#475569">
                <q-icon name="medical_services" size="48px" class="q-mb-sm" color="negative" /><br>
                Sin certificados médicos registrados (RAC 67)
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- ─── Bitácoras recientes ───────────────────────────────────── -->
        <q-tab-panel name="bitacoras" class="q-pa-none">
          <div class="row justify-end q-mb-md">
            <q-btn flat no-caps :to="`/bitacoras?estudiante=${$route.params.id}`"
              color="primary" label="Ver todas las bitácoras" icon-right="arrow_forward" />
          </div>
          <q-list dark separator style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px">
            <q-item v-for="b in bitacorasRecientes" :key="b.id" style="padding:12px 16px">
              <q-item-section avatar>
                <div class="font-mono text-center" style="width:50px">
                  <div style="font-size:16px;font-weight:700;color:#3b82f6">{{ Number(b.horas_totales || 0).toFixed(1) }}</div>
                  <div style="font-size:9px;color:#475569;letter-spacing:.5px">horas</div>
                </div>
              </q-item-section>
              <q-item-section>
                <q-item-label style="font-size:13px;color:#e2e8f0">
                  {{ b.origen_icao }} → {{ b.destino_icao }}
                  <span class="q-ml-sm"><q-chip dense :color="colorTipoVuelo(b.tipo_vuelo)" text-color="white"
                    :label="b.tipo_vuelo" style="font-size:9px" /></span>
                </q-item-label>
                <q-item-label caption style="font-size:11px">
                  {{ b.fecha }} · {{ b.aeronave?.matricula }}
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="row q-gutter-xs">
                  <q-icon :name="b.firma_instructor?'verified':'pending'"
                    :color="b.firma_instructor?'positive':'grey-6'" size="16px">
                    <q-tooltip>Instructor</q-tooltip>
                  </q-icon>
                  <q-icon :name="b.firma_estudiante?'verified':'pending'"
                    :color="b.firma_estudiante?'positive':'grey-6'" size="16px">
                    <q-tooltip>Estudiante</q-tooltip>
                  </q-icon>
                </div>
              </q-item-section>
            </q-item>
            <q-item v-if="!bitacorasRecientes.length">
              <q-item-section class="text-center q-py-lg" style="color:#475569">
                Sin bitácoras registradas
              </q-item-section>
            </q-item>
          </q-list>
        </q-tab-panel>

      </q-tab-panels>
    </template>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const route   = useRoute()
const cargando  = ref(false)
const expediente = ref(null)
const tab       = ref('resumen')

const iniciales = computed(() => {
  const e = expediente.value?.estudiante
  return ((e?.persona?.nombres?.[0] || '') + (e?.persona?.apellidos?.[0] || '')).toUpperCase()
})

const colorEstado = computed(() => ({
  activo:'primary', suspendido:'warning', graduado:'positive', retirado:'grey-7',
}[expediente.value?.estudiante?.estado] || 'grey'))

const datosPersonales = computed(() => {
  const p = expediente.value?.estudiante?.persona
  if (!p) return []
  return [
    { label: 'Documento',    valor: `${p.tipo_documento}: ${p.num_documento}` },
    { label: 'Nacimiento',   valor: p.fecha_nacimiento },
    { label: 'Nacionalidad', valor: p.nacionalidad },
    { label: 'Teléfono',     valor: p.telefono },
    { label: 'Ciudad',       valor: p.ciudad },
    { label: 'Ingreso',      valor: expediente.value?.estudiante?.fecha_ingreso },
  ]
})

const categoriasHoras = computed(() => {
  const cats = expediente.value?.progreso?.categorias || {}
  return Object.entries(cats).filter(([,v]) => (v.requerido||0) > 0).map(([k, v]) => ({ key: k, ...v }))
})

const resumenHoras = computed(() => {
  const cats = categoriasHoras.value
  const hTotal = cats.find(c => c.key === 'vuelo_total')?.acumulado || 0
  const hDual  = cats.find(c => c.key === 'dual')?.acumulado || 0
  const hSolo  = cats.find(c => c.key === 'solo')?.acumulado || 0
  const hNoche = cats.find(c => c.key === 'noche')?.acumulado || 0
  const hIfr   = cats.find(c => c.key === 'ifr')?.acumulado || 0
  return [
    { label: 'TOTAL',  valor: hTotal.toFixed(1) },
    { label: 'DUAL',   valor: hDual.toFixed(1)  },
    { label: 'SOLO',   valor: hSolo.toFixed(1)  },
    { label: 'NOCHE',  valor: hNoche.toFixed(1) },
    { label: 'IFR',    valor: hIfr.toFixed(1)   },
    { label: 'VUELOS', valor: expediente.value?.progreso?.total_vuelos || 0 },
  ]
})

const porcentajeGeneral = computed(() => {
  const cats = categoriasHoras.value.filter(c => c.requerido > 0)
  if (!cats.length) return 0
  return Math.round(cats.reduce((a, c) => a + Math.min(c.porcentaje || 0, 100), 0) / cats.length)
})

const bitacorasRecientes = computed(() => expediente.value?.estudiante?.bitacoras?.slice(0, 10) || [])

const columnasNotas = [
  { name: 'materia',  label: 'Materia',    field: row => row.materia?.nombre, align: 'left' },
  { name: 'nota',     label: 'Nota',       field: 'nota',       align: 'center' },
  { name: 'aprobado', label: 'Aprobado',   field: 'aprobado',   align: 'center' },
  { name: 'intento',  label: 'Intento',    field: 'intento_num',align: 'center' },
  { name: 'fecha',    label: 'Fecha',      field: 'fecha_evaluacion', align: 'left' },
]

const esMedicoVigente = (cm) => dayjs(cm.fecha_vencimiento).isAfter(dayjs())
const colorTipoVuelo  = (t) => ({ local:'primary', navegacion:'teal', noche:'purple', ifr:'amber-9', sim:'grey-7' }[t] || 'grey')

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get(`/estudiantes/${route.params.id}/expediente`)
    expediente.value = data.data
  } finally { cargando.value = false }
}

onMounted(cargar)
</script>
