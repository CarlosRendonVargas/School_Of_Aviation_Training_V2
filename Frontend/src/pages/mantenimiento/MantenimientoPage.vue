<template>
  <q-page padding style="padding-bottom: 80px">

    <!-- ══ Encabezado ══ -->
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 43 · RAC 91 · Módulo 05
        </div>
        <div class="font-head text-white" style="font-size:22px;font-weight:700">Mantenimiento y Flota</div>
      </div>
      <div class="row q-gutter-sm">
        <q-btn-dropdown
          v-if="puedeRegistrar"
          unelevated no-caps icon="add" label="Registrar"
          style="background:#A10B13;color:white;border-radius:8px;padding:8px 16px;font-weight:600"
        >
          <q-list dark style="background:#161b26;min-width:200px">
            <q-item clickable v-close-popup @click="abrirDialogMantenimiento">
              <q-item-section avatar><q-icon name="build" color="amber" size="18px"/></q-item-section>
              <q-item-section><q-item-label style="font-size:13px">Registro Mantenimiento</q-item-label></q-item-section>
            </q-item>
            <q-item clickable v-close-popup @click="abrirDialogMel">
              <q-item-section avatar><q-icon name="warning" color="orange" size="18px"/></q-item-section>
              <q-item-section><q-item-label style="font-size:13px">Ítem MEL</q-item-label></q-item-section>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </div>
    </div>

    <!-- ══ KPIs ══ -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3" v-for="kpi in kpis" :key="kpi.label">
        <div class="kpi-card">
          <div class="kpi-icon" :style="`background:${kpi.bg}`">
            <q-icon :name="kpi.icon" :color="kpi.color" size="22px" />
          </div>
          <div>
            <div class="kpi-num" :style="`color:${kpi.textColor || '#e2e8f0'}`">
              <q-skeleton v-if="cargando" type="text" width="40px" dark />
              <span v-else>{{ kpi.valor }}</span>
            </div>
            <div class="kpi-lbl">{{ kpi.label }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ Pestañas ══ -->
    <q-tabs v-model="tab" dark dense align="left" no-caps
      active-color="primary" indicator-color="primary" class="q-mb-md"
      style="border-bottom:1px solid rgba(255,255,255,.08)"
    >
      <q-tab name="aeronaves"   icon="flight"   label="Flota" />
      <q-tab name="registros"   icon="build"    label="Registros RAC 43" />
      <q-tab name="mel"         icon="warning"  label="MEL" />
      <q-tab name="ads"         icon="policy"   label="Directivas (ADs)" />
    </q-tabs>

    <q-tab-panels v-model="tab" dark animated>

      <!-- ════ FLOTA ════ -->
      <q-tab-panel name="aeronaves" class="q-pa-none">
        <div class="row q-col-gutter-md q-mt-xs">
          <div class="col-12 col-sm-6 col-lg-4"
            v-for="av in aeronaves" :key="av.id"
          >
            <div class="av-card" :class="estadoClass(av.estado)">
              <div class="row items-center justify-between q-mb-sm">
                <div>
                  <div class="font-head text-white" style="font-size:18px;font-weight:700">{{ av.matricula }}</div>
                  <div style="font-size:12px;color:#64748b">{{ av.fabricante }} · {{ av.modelo }} · {{ av.anio }}</div>
                </div>
                <q-chip dense :color="estadoColor(av.estado)" text-color="white"
                  :label="av.estado?.toUpperCase()" style="font-size:10px;font-family:monospace" />
              </div>

              <div class="row q-col-gutter-sm q-mb-sm">
                <div class="col-4">
                  <div class="mini-stat">
                    <div class="mini-stat-lbl">CÉLULA</div>
                    <div class="mini-stat-val">{{ Number(av.horas_celula_total || 0).toFixed(0) }}h</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="mini-stat">
                    <div class="mini-stat-lbl">MOTOR</div>
                    <div class="mini-stat-val">{{ Number(av.horas_motor_total || 0).toFixed(0) }}h</div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="mini-stat">
                    <div class="mini-stat-lbl">DESDE O/H</div>
                    <div class="mini-stat-val" :style="av.horas_desde_oh > 800 ? 'color:#f97316' : 'color:#4ade80'">
                      {{ Number(av.horas_desde_oh || 0).toFixed(0) }}h
                    </div>
                  </div>
                </div>
              </div>

              <div class="row items-center q-gutter-xs">
                <q-chip v-if="av.mel_abiertos_count > 0" dense color="warning" text-color="dark"
                  :label="`${av.mel_abiertos_count} MEL abiertos`" style="font-size:10px" />
                <q-chip v-if="av.ads_pendientes_count > 0" dense color="negative" text-color="white"
                  :label="`${av.ads_pendientes_count} ADs pendientes`" style="font-size:10px" />
                <span v-if="!av.mel_abiertos_count && !av.ads_pendientes_count"
                  style="font-size:11px;color:#4ade80;font-family:monospace">✓ Todo en orden</span>
              </div>

              <!-- Airworthiness progress -->
              <div class="q-mt-sm">
                <div class="row justify-between q-mb-xs">
                  <span style="font-size:10px;color:#475569;font-family:monospace">AERONAVEGABILIDAD</span>
                  <span style="font-size:10px;color:#94a3b8;font-family:monospace">{{ av.venc_airworthiness }}</span>
                </div>
                <q-linear-progress
                  :value="airworthinessProgress(av)"
                  :color="airworthinessProgressColor(av)"
                  track-color="grey-9"
                  rounded size="4px"
                />
              </div>
            </div>
          </div>

          <div v-if="!aeronaves.length && !cargandoAeronaves" class="col-12 text-center q-py-xl text-grey-6">
            <q-icon name="flight_off" size="48px" /><br>Sin aeronaves registradas
          </div>
          <q-skeleton v-if="cargandoAeronaves" v-for="n in 3" :key="n"
            class="col-12 col-sm-6 col-lg-4" type="rect" height="180px" dark />
        </div>
      </q-tab-panel>

      <!-- ════ REGISTROS MANTENIMIENTO ════ -->
      <q-tab-panel name="registros" class="q-pa-none q-mt-xs">

        <!-- Filtro aeronave -->
        <div class="row items-center q-col-gutter-sm q-mb-md">
          <div class="col-auto">
            <q-select v-model="filtroAeronave" :options="opcionesAeronave"
              option-value="id" option-label="matricula" emit-value map-options
              label="Aeronave" dense dark outlined clearable
              style="min-width:160px" bg-color="grey-10">
            </q-select>
          </div>
          <div class="col-auto">
            <q-select v-model="filtroTipoMant" :options="tiposMant"
              label="Tipo" dense dark outlined clearable
              style="min-width:160px" bg-color="grey-10">
            </q-select>
          </div>
        </div>

        <q-table
          :rows="registrosFiltrados"
          :columns="colsRegistros"
          row-key="id"
          dark flat
          :loading="cargandoRegistros"
          :rows-per-page-options="[10,20]"
          style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
          table-header-style="color:#475569;font-size:10px;letter-spacing:1px;font-family:'JetBrains Mono',monospace"
        >
          <template #body-cell-tipo="props">
            <q-td :props="props">
              <q-chip dense :color="colorTipoMant(props.value)" text-color="white"
                :label="props.value?.replace('_',' ').toUpperCase()" style="font-size:9px;font-family:monospace" />
            </q-td>
          </template>
          <template #body-cell-costo="props">
            <q-td :props="props" class="font-mono" style="color:#94a3b8;font-size:12px">
              {{ props.value ? `$${Number(props.value).toLocaleString('es-CO')}` : '—' }}
            </q-td>
          </template>
          <template #no-data>
            <div class="full-width text-center text-grey-6 q-py-xl">
              <q-icon name="build_circle" size="36px" class="q-mb-sm" /><br>Sin registros
            </div>
          </template>
        </q-table>
      </q-tab-panel>

      <!-- ════ MEL ════ -->
      <q-tab-panel name="mel" class="q-pa-none q-mt-xs">
        <q-table
          :rows="melItems"
          :columns="colsMel"
          row-key="id"
          dark flat
          :loading="cargandoMel"
          :rows-per-page-options="[10,20]"
          style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
          table-header-style="color:#475569;font-size:10px;letter-spacing:1px;font-family:'JetBrains Mono',monospace"
        >
          <template #body-cell-categoria="props">
            <q-td :props="props">
              <q-chip dense :color="colorCategoriaMel(props.value)" text-color="white"
                :label="props.value" style="font-size:11px;font-family:monospace;font-weight:700" />
            </q-td>
          </template>
          <template #body-cell-estado="props">
            <q-td :props="props">
              <q-chip dense :color="props.value === 'abierto' ? 'negative' : 'positive'"
                text-color="white" :label="props.value?.toUpperCase()"
                style="font-size:10px;font-family:monospace" />
            </q-td>
          </template>
          <template #body-cell-fecha_limite="props">
            <q-td :props="props" class="font-mono"
              :style="`color:${diasRestantesMel(props.value) <= 3 ? '#ef4444' : '#94a3b8'};font-size:12px`">
              {{ props.value }}
              <span v-if="props.row.estado === 'abierto'" style="font-size:10px;margin-left:4px">
                ({{ diasRestantesMel(props.value) }}d)
              </span>
            </q-td>
          </template>
          <template #no-data>
            <div class="full-width text-center q-py-xl" style="color:#4ade80">
              <q-icon name="check_circle" size="36px" class="q-mb-sm" /><br>Sin ítems MEL activos
            </div>
          </template>
        </q-table>
      </q-tab-panel>

      <!-- ════ AIRWORTHINESS DIRECTIVES ════ -->
      <q-tab-panel name="ads" class="q-pa-none q-mt-xs">
        <q-table
          :rows="adItems"
          :columns="colsAds"
          row-key="id"
          dark flat
          :loading="cargandoAds"
          :rows-per-page-options="[10,20]"
          style="background:#0f1218;border:1px solid rgba(255,255,255,.07);border-radius:12px"
          table-header-style="color:#475569;font-size:10px;letter-spacing:1px;font-family:'JetBrains Mono',monospace"
        >
          <template #body-cell-cumplido="props">
            <q-td :props="props">
              <q-chip dense :color="props.value ? 'positive' : 'negative'" text-color="white"
                :label="props.value ? 'CUMPLIDO' : 'PENDIENTE'"
                style="font-size:10px;font-family:monospace" />
            </q-td>
          </template>
          <template #body-cell-autoridad_emisora="props">
            <q-td :props="props" class="font-mono" style="color:#94a3b8;font-size:12px">
              {{ props.value }}
            </q-td>
          </template>
          <template #no-data>
            <div class="full-width text-center text-grey-6 q-py-xl">
              <q-icon name="policy" size="36px" class="q-mb-sm" /><br>Sin directivas registradas
            </div>
          </template>
        </q-table>
      </q-tab-panel>

    </q-tab-panels>

    <!-- ══ Dialog Nuevo Mantenimiento ══ -->
    <q-dialog v-model="dialogMant" persistent>
      <q-card dark style="min-width:480px;background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px">
        <q-card-section class="q-pb-none">
          <div class="font-head text-white" style="font-size:16px;font-weight:700">Registro de Mantenimiento</div>
          <div class="font-mono q-mt-xs" style="font-size:10px;color:#475569">RAC 43</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="guardarMantenimiento" class="q-gutter-md">
            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <q-select v-model="formMant.aeronave_id" :options="opcionesAeronave"
                  option-value="id" option-label="matricula" emit-value map-options
                  label="Aeronave *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="formMant.tipo" :options="tiposMant"
                  label="Tipo *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMant.fecha_realizado" type="date"
                  label="Fecha realizado *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMant.horas_aeronave" type="number" step="0.1"
                  label="Horas de la aeronave *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMant.costo" type="number"
                  label="Costo (COP)" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12">
                <q-input v-model="formMant.realizado_por" label="Realizado por *"
                  dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12">
                <q-input v-model="formMant.licencia_tecnico" label="Licencia técnico AME"
                  dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12">
                <q-input v-model="formMant.descripcion" label="Descripción del trabajo *"
                  type="textarea" rows="3" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMant.proxima_fecha" type="date"
                  label="Próxima fecha" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMant.proximas_horas" type="number" step="0.1"
                  label="Próximas horas" dark outlined dense bg-color="grey-10" />
              </div>
            </div>
            <div class="row justify-end q-gutter-sm q-pt-sm">
              <q-btn flat no-caps label="Cancelar" @click="dialogMant = false" color="grey-5" />
              <q-btn type="submit" unelevated no-caps label="Guardar" :loading="guardando"
                style="background:#A10B13;color:white;border-radius:8px;padding:8px 20px" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- ══ Dialog Nuevo MEL ══ -->
    <q-dialog v-model="dialogMel" persistent>
      <q-card dark style="min-width:420px;background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px">
        <q-card-section class="q-pb-none">
          <div class="font-head text-white" style="font-size:16px;font-weight:700">Nuevo Ítem MEL</div>
          <div class="font-mono q-mt-xs" style="font-size:10px;color:#475569">Minimum Equipment List</div>
        </q-card-section>
        <q-card-section>
          <q-form @submit.prevent="guardarMel" class="q-gutter-md">
            <div class="row q-col-gutter-sm">
              <div class="col-12">
                <q-select v-model="formMel.aeronave_id" :options="opcionesAeronave"
                  option-value="id" option-label="matricula" emit-value map-options
                  label="Aeronave *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="formMel.item_ata" label="Código ATA *"
                  placeholder="21-10-01" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12 col-sm-6">
                <q-select v-model="formMel.categoria" :options="['A','B','C','D']"
                  label="Categoría *" dark outlined dense bg-color="grey-10">
                  <template #hint>
                    <span class="font-mono" style="font-size:10px">
                      A=3d · B=10d · C=30d · D=120d
                    </span>
                  </template>
                </q-select>
              </div>
              <div class="col-12">
                <q-input v-model="formMel.descripcion" label="Descripción *"
                  dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12">
                <q-input v-model="formMel.fecha_apertura" type="date"
                  label="Fecha de apertura *" dark outlined dense bg-color="grey-10" />
              </div>
              <div class="col-12">
                <q-input v-model="formMel.procedimiento_o" label="Procedimiento operacional"
                  type="textarea" rows="2" dark outlined dense bg-color="grey-10" />
              </div>
            </div>
            <div class="row justify-end q-gutter-sm q-pt-sm">
              <q-btn flat no-caps label="Cancelar" @click="dialogMel = false" color="grey-5" />
              <q-btn type="submit" unelevated no-caps label="Abrir MEL" :loading="guardando"
                style="background:#A10B13;color:white;border-radius:8px;padding:8px 20px" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q        = useQuasar()
const authStore = useAuthStore()

const tab       = ref('aeronaves')
const cargando  = ref(true)

const aeronaves        = ref([])
const registros        = ref([])
const melItems         = ref([])
const adItems          = ref([])

const cargandoAeronaves = ref(true)
const cargandoRegistros = ref(true)
const cargandoMel       = ref(true)
const cargandoAds       = ref(true)

const filtroAeronave = ref(null)
const filtroTipoMant = ref(null)

const dialogMant = ref(false)
const dialogMel  = ref(false)
const guardando  = ref(false)

const formMant = ref({ aeronave_id: null, tipo: null, descripcion: '', fecha_realizado: '', horas_aeronave: '', realizado_por: '', licencia_tecnico: '', proxima_fecha: '', proximas_horas: '', costo: '' })
const formMel  = ref({ aeronave_id: null, item_ata: '', descripcion: '', categoria: null, fecha_apertura: '', procedimiento_o: '' })

const puedeRegistrar = computed(() =>
  ['admin', 'dir_ops', 'mantenimiento'].includes(authStore.rol)
)

// ── KPIs ──────────────────────────────────────────────────────
const melAbiertos     = computed(() => melItems.value.filter(m => m.estado === 'abierto').length)
const adsPendientes   = computed(() => adItems.value.filter(a => !a.cumplido).length)
const totalRegistros  = computed(() => registros.value.length)
const totalAeronaves  = computed(() => aeronaves.value.filter(a => a.estado !== 'baja').length)

const kpis = computed(() => [
  { label: 'Aeronaves activas',  valor: totalAeronaves.value,  icon: 'flight',        color: 'blue-4',   bg: 'rgba(96,165,250,.12)',     textColor: '#93c5fd' },
  { label: 'Registros RAC 43',   valor: totalRegistros.value,  icon: 'build',         color: 'amber-4',  bg: 'rgba(251,191,36,.12)',     textColor: '#fbbf24' },
  { label: 'MEL abiertos',       valor: melAbiertos.value,     icon: 'warning',       color: 'orange-4', bg: 'rgba(251,146,60,.12)',     textColor: melAbiertos.value > 0 ? '#f97316' : '#4ade80' },
  { label: 'ADs pendientes',     valor: adsPendientes.value,   icon: 'policy',        color: 'red-4',    bg: 'rgba(248,113,113,.12)',    textColor: adsPendientes.value > 0 ? '#ef4444' : '#4ade80' },
])

// ── Opciones ──────────────────────────────────────────────────
const opcionesAeronave = computed(() =>
  aeronaves.value.map(a => ({ id: a.id, matricula: a.matricula, label: a.matricula }))
)

const tiposMant = [
  'inspeccion_50h','inspeccion_100h','anual','AD','SB','correctivo','preventivo'
]

// ── Filtros ───────────────────────────────────────────────────
const registrosFiltrados = computed(() => {
  let r = registros.value
  if (filtroAeronave.value) r = r.filter(x => x.aeronave_id === filtroAeronave.value)
  if (filtroTipoMant.value) r = r.filter(x => x.tipo === filtroTipoMant.value)
  return r
})

// ── Columnas tablas ────────────────────────────────────────────
const colsRegistros = [
  { name: 'tipo',            label: 'TIPO',           field: 'tipo',            align: 'left', sortable: true },
  { name: 'fecha_realizado', label: 'FECHA',          field: 'fecha_realizado', align: 'left', sortable: true },
  { name: 'realizado_por',   label: 'TÉCNICO',        field: 'realizado_por',   align: 'left' },
  { name: 'horas_aeronave',  label: 'HORAS',          field: 'horas_aeronave',  align: 'center', style: 'font-family:monospace' },
  { name: 'descripcion',     label: 'DESCRIPCIÓN',    field: 'descripcion',     align: 'left', style: 'max-width:250px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis' },
  { name: 'costo',           label: 'COSTO',          field: 'costo',           align: 'right' },
  { name: 'proxima_fecha',   label: 'PRÓXIMA FECHA',  field: 'proxima_fecha',   align: 'center', style: 'font-family:monospace;font-size:12px;color:#94a3b8' },
]

const colsMel = [
  { name: 'item_ata',      label: 'ATA',         field: 'item_ata',      align: 'left',   style: 'font-family:monospace' },
  { name: 'descripcion',   label: 'DESCRIPCIÓN', field: 'descripcion',   align: 'left' },
  { name: 'categoria',     label: 'CAT',         field: 'categoria',     align: 'center' },
  { name: 'fecha_apertura',label: 'APERTURA',    field: 'fecha_apertura',align: 'center', style: 'font-family:monospace;font-size:12px;color:#94a3b8' },
  { name: 'fecha_limite',  label: 'LÍMITE',      field: 'fecha_limite',  align: 'center' },
  { name: 'estado',        label: 'ESTADO',      field: 'estado',        align: 'center' },
]

const colsAds = [
  { name: 'numero_ad',         label: 'No. AD',     field: 'numero_ad',         align: 'left',   style: 'font-family:monospace' },
  { name: 'titulo',            label: 'TÍTULO',     field: 'titulo',            align: 'left' },
  { name: 'autoridad_emisora', label: 'AUTORIDAD',  field: 'autoridad_emisora', align: 'center' },
  { name: 'fecha_emision',     label: 'EMITIDA',    field: 'fecha_emision',     align: 'center', style: 'font-family:monospace;font-size:12px;color:#94a3b8' },
  { name: 'fecha_limite',      label: 'LÍMITE',     field: 'fecha_limite',      align: 'center', style: 'font-family:monospace;font-size:12px;color:#94a3b8' },
  { name: 'cumplido',          label: 'ESTADO',     field: 'cumplido',          align: 'center' },
]

// ── Helpers ────────────────────────────────────────────────────
const estadoClass = (e) => ({
  disponible:    'estado-disponible',
  mantenimiento: 'estado-mantenimiento',
  baja:          'estado-baja',
}[e] || '')

const estadoColor = (e) => ({
  disponible:    'positive',
  mantenimiento: 'warning',
  baja:          'negative',
}[e] || 'grey')

const colorTipoMant = (t) => ({
  anual:           'deep-purple-8',
  inspeccion_50h:  'blue-8',
  inspeccion_100h: 'cyan-8',
  correctivo:      'red-9',
  preventivo:      'green-8',
  AD:              'orange-8',
  SB:              'amber-8',
}[t] || 'grey-7')

const colorCategoriaMel = (cat) => ({
  A: 'red-9', B: 'orange-8', C: 'amber-8', D: 'blue-8',
}[cat] || 'grey-7')

const diasRestantesMel = (fecha) => {
  if (!fecha) return 0
  const diff = new Date(fecha) - new Date()
  return Math.ceil(diff / 86400000)
}

const airworthinessProgress = (av) => {
  if (!av.venc_airworthiness) return 0
  const diff = new Date(av.venc_airworthiness) - new Date()
  const days = diff / 86400000
  return Math.min(Math.max(days / 365, 0), 1)
}

const airworthinessProgressColor = (av) => {
  const p = airworthinessProgress(av)
  if (p < 0.1) return 'negative'
  if (p < 0.25) return 'warning'
  return 'positive'
}

// ── Acciones dialogs ───────────────────────────────────────────
function abrirDialogMantenimiento() { dialogMant.value = true }
function abrirDialogMel()           { dialogMel.value  = true }

async function guardarMantenimiento() {
  if (!formMant.value.aeronave_id) return
  guardando.value = true
  try {
    await api.post(`/aeronaves/${formMant.value.aeronave_id}/mantenimientos`, formMant.value)
    $q.notify({ type: 'positive', message: 'Registro de mantenimiento guardado (RAC 43).' })
    dialogMant.value = false
    cargarRegistros()
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al guardar.' })
  } finally { guardando.value = false }
}

async function guardarMel() {
  if (!formMel.value.aeronave_id) return
  guardando.value = true
  try {
    await api.post(`/aeronaves/${formMel.value.aeronave_id}/mel`, formMel.value)
    $q.notify({ type: 'positive', message: 'Ítem MEL abierto correctamente.' })
    dialogMel.value = false
    cargarMel()
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.message || 'Error al guardar.' })
  } finally { guardando.value = false }
}

// ── Carga de datos ─────────────────────────────────────────────
async function cargarAeronaves() {
  cargandoAeronaves.value = true
  try {
    const { data } = await api.get('/aeronaves')
    aeronaves.value = data.data || []
  } catch { aeronaves.value = [] }
  finally { cargandoAeronaves.value = false }
}

async function cargarRegistros() {
  cargandoRegistros.value = true
  try {
    // Recolectar registros de todas las aeronaves
    const all = []
    for (const av of aeronaves.value.slice(0, 10)) {
      try {
        const { data } = await api.get(`/aeronaves/${av.id}/mantenimientos`)
        const items = data.data?.data || data.data || []
        items.forEach(r => r._matricula = av.matricula)
        all.push(...items)
      } catch {}
    }
    registros.value = all.sort((a, b) => b.fecha_realizado?.localeCompare(a.fecha_realizado))
  } catch { registros.value = [] }
  finally { cargandoRegistros.value = false }
}

async function cargarMel() {
  cargandoMel.value = true
  try {
    const all = []
    for (const av of aeronaves.value.slice(0, 10)) {
      try {
        const { data } = await api.get(`/aeronaves/${av.id}/mel`)
        const items = data.data || []
        items.forEach(r => r._matricula = av.matricula)
        all.push(...items)
      } catch {}
    }
    melItems.value = all.filter(m => m.estado === 'abierto').sort((a, b) => a.fecha_limite?.localeCompare(b.fecha_limite))
  } catch { melItems.value = [] }
  finally { cargandoMel.value = false }
}

async function cargarAds() {
  cargandoAds.value = true
  try {
    const all = []
    for (const av of aeronaves.value.slice(0, 10)) {
      try {
        const { data } = await api.get(`/aeronaves/${av.id}/ads`)
        const items = data.data || []
        items.forEach(r => r._matricula = av.matricula)
        all.push(...items)
      } catch {}
    }
    adItems.value = all.sort((a, b) => a.fecha_limite?.localeCompare(b.fecha_limite))
  } catch { adItems.value = [] }
  finally { cargandoAds.value = false }
}

async function cargarTodo() {
  cargando.value = true
  await cargarAeronaves()
  await Promise.all([cargarRegistros(), cargarMel(), cargarAds()])
  cargando.value = false
}

onMounted(cargarTodo)
</script>

<style scoped>
.kpi-card {
  background: #0f1218;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 12px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.kpi-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.kpi-num {
  font-size: 22px;
  font-weight: 700;
  font-family: 'JetBrains Mono', monospace;
  line-height: 1;
}
.kpi-lbl {
  font-size: 10px;
  color: #475569;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-top: 4px;
}

/* Aeronave cards */
.av-card {
  background: #0f1218;
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 12px;
  padding: 18px;
  transition: border-color .2s;
}
.av-card:hover { border-color: rgba(255,255,255,.18); }
.estado-disponible    { border-left: 3px solid #4ade80; }
.estado-mantenimiento { border-left: 3px solid #fbbf24; }
.estado-baja          { border-left: 3px solid #ef4444; opacity: .7; }

.mini-stat { text-align: center; }
.mini-stat-lbl {
  font-family: 'JetBrains Mono', monospace;
  font-size: 9px; color: #475569;
  letter-spacing: 1px; text-transform: uppercase;
}
.mini-stat-val {
  font-family: 'JetBrains Mono', monospace;
  font-size: 14px; color: #e2e8f0;
  font-weight: 700;
}
</style>
