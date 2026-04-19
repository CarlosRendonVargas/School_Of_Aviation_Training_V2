<template>
  <div>

    <!-- ── KPIs principales ─────────────────────────────────────────────────── -->
    <div class="row q-col-gutter-md q-mb-lg">

      <div class="col-6 col-md-3" v-for="kpi in kpis" :key="kpi.label">
        <div class="stat-card cursor-pointer" @click="$router.push(kpi.to)">
          <q-skeleton v-if="cargando" type="rect" height="70px" dark />
          <template v-else>
            <div class="row items-start justify-between">
              <div>
                <div class="stat-num" :style="`color:${kpi.color}`">{{ kpi.valor }}</div>
                <div class="stat-label">{{ kpi.label }}</div>
              </div>
              <q-icon :name="kpi.icono" :style="`color:${kpi.color}; opacity:.35`" size="28px" />
            </div>
          </template>
        </div>
      </div>

    </div>

    <div class="row q-col-gutter-md">

      <!-- Vencimientos críticos -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                ⚠️ Vencimientos críticos
              </div>
              <q-btn flat dense no-caps to="/vencimientos" color="warning" size="sm" label="Ver todos" icon-right="arrow_forward" />
            </div>

            <q-skeleton v-if="cargando" v-for="n in 4" :key="n" type="QItem" dark class="q-mb-xs" />

            <div v-if="!cargando && !vencimientosCriticos.length"
              class="text-center q-py-md" style="color:#475569; font-size:13px">
              <q-icon name="check_circle" color="positive" size="24px" class="q-mb-xs" /><br>
              Sin vencimientos críticos
            </div>

            <div v-for="v in vencimientosCriticos" :key="v.id"
              class="q-pa-sm q-mb-xs rounded-borders alert-critico">
              <div class="row items-center justify-between">
                <div>
                  <div style="font-size:12.5px; color:#e2e8f0">{{ v.descripcion }}</div>
                  <div class="font-mono" style="font-size:10px; color:#94a3b8; margin-top:2px; text-transform:uppercase">{{ v.tipo_entidad }}</div>
                </div>
                <q-chip dense :color="v.dias_restantes <= 0 ? 'negative' : 'warning'" text-color="white"
                  :label="v.dias_restantes <= 0 ? 'VENCIDO' : v.dias_restantes + 'd'"
                  style="font-family: monospace; font-size:10px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- SMS KPIs -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                🛡️ SMS — Seguridad Operacional
              </div>
              <q-btn flat dense no-caps to="/sms" color="positive" size="sm" label="Ver SMS" icon-right="arrow_forward" />
            </div>

            <q-skeleton v-if="cargando" type="rect" height="140px" dark />

            <template v-else-if="smsKpis">
              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:8px; padding:12px">
                    <div class="font-head text-white" style="font-size:22px; font-weight:700">{{ smsKpis.total_reportes || 0 }}</div>
                    <div class="font-mono" style="font-size:10px; color:#64748b; letter-spacing:.5px">Reportes / {{ smsKpis.periodo_meses }}m</div>
                  </div>
                </div>
                <div class="col-6">
                  <div style="background:rgba(239,68,68,.06); border:1px solid rgba(239,68,68,.15); border-radius:8px; padding:12px">
                    <div class="font-head text-negative" style="font-size:22px; font-weight:700">{{ smsKpis.inaceptables || 0 }}</div>
                    <div class="font-mono" style="font-size:10px; color:#64748b; letter-spacing:.5px">Riesgo inaceptable</div>
                  </div>
                </div>
                <div class="col-6">
                  <div style="background:rgba(245,158,11,.05); border:1px solid rgba(245,158,11,.15); border-radius:8px; padding:12px">
                    <div class="font-head text-warning" style="font-size:22px; font-weight:700">{{ smsKpis.acciones_pendientes || 0 }}</div>
                    <div class="font-mono" style="font-size:10px; color:#64748b; letter-spacing:.5px">Acciones pendientes</div>
                  </div>
                </div>
                <div class="col-6">
                  <div style="background:rgba(239,68,68,.04); border:1px solid rgba(239,68,68,.12); border-radius:8px; padding:12px">
                    <div class="font-head text-negative" style="font-size:22px; font-weight:700">{{ smsKpis.acciones_vencidas || 0 }}</div>
                    <div class="font-mono" style="font-size:10px; color:#64748b; letter-spacing:.5px">Acciones vencidas</div>
                  </div>
                </div>
              </div>
            </template>

          </q-card-section>
        </q-card>
      </div>

      <!-- Aeronaves en mantenimiento -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                🔧 Aeronaves en mantenimiento
              </div>
              <q-btn flat dense no-caps to="/aeronaves" color="grey-5" size="sm" label="Ver flota" icon-right="arrow_forward" />
            </div>

            <q-skeleton v-if="cargando" v-for="n in 3" :key="n" type="QItem" dark class="q-mb-xs" />

            <div v-if="!cargando && !aeronavesEnMx.length"
              class="text-center q-py-md" style="color:#475569; font-size:13px">
              <q-icon name="flight_takeoff" color="positive" size="24px" class="q-mb-xs" /><br>
              Todas las aeronaves disponibles
            </div>

            <div v-for="av in aeronavesEnMx" :key="av.id"
              class="q-pa-sm q-mb-xs rounded-borders" style="background:rgba(249,115,22,.05); border:1px solid rgba(249,115,22,.15)">
              <div class="row items-center justify-between">
                <div>
                  <div class="font-mono text-white" style="font-size:13px; font-weight:500">{{ av.matricula }}</div>
                  <div style="font-size:11px; color:#94a3b8">{{ av.modelo }}</div>
                </div>
                <q-chip dense color="orange" text-color="white" label="En MX" style="font-size:10px" />
              </div>
            </div>

          </q-card-section>
        </q-card>
      </div>

      <!-- Vuelos de hoy -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                ✈️ Operaciones de hoy
              </div>
              <q-btn flat dense no-caps to="/calendario" color="primary" size="sm" label="Calendario" icon-right="arrow_forward" />
            </div>

            <q-skeleton v-if="cargando" type="rect" height="100px" dark />

            <template v-else>
              <div class="text-center q-py-md" v-if="!resumen.vuelos_hoy">
                <div class="font-head text-white" style="font-size:36px; font-weight:800; color:#3b82f6">{{ resumen.vuelos_hoy || 0 }}</div>
                <div class="font-mono" style="font-size:11px; color:#475569; letter-spacing:1px">VUELOS PROGRAMADOS HOY</div>
              </div>
            </template>

          </q-card-section>
        </q-card>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  data:     { type: Object, default: null },
  cargando: { type: Boolean, default: false },
})

const resumen            = computed(() => props.data?.resumen_escuela || {})
const vencimientosCriticos = computed(() => props.data?.vencimientos_criticos?.slice(0, 6) || [])
const smsKpis            = computed(() => props.data?.sms_kpis || null)
const aeronavesEnMx      = computed(() => props.data?.aeronaves_mantenimiento || [])

const kpis = computed(() => [
  { label: 'Estudiantes activos',   valor: resumen.value.estudiantes_activos  || 0, icono: 'school',        color: '#3b82f6', to: '/estudiantes' },
  { label: 'Instructores activos',  valor: resumen.value.instructores_activos || 0, icono: 'badge',         color: '#14b8a6', to: '/instructores' },
  { label: 'Aeronaves disponibles', valor: resumen.value.aeronaves_disponibles|| 0, icono: 'flight_takeoff',color: '#22c55e', to: '/aeronaves' },
  { label: 'Vuelos hoy',            valor: resumen.value.vuelos_hoy           || 0, icono: 'flight',        color: '#f59e0b', to: '/calendario' },
])
</script>
