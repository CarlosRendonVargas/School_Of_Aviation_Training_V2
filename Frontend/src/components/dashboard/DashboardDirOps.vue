<template>
  <div>
    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-tail color="blue-4" size="40px" />
    </div>

    <template v-else-if="data">
      <!-- ── KPIs principales ───────────────────────────────────────── -->
      <div class="kpis-row q-mb-lg">
        <div class="stat-card accent-red">
          <div class="stat-value text-red-5">{{ data.resumen_escuela?.estudiantes_activos }}</div>
          <div class="stat-label">Estudiantes activos</div>
        </div>
        <div class="stat-card accent-teal">
          <div class="stat-value" style="color:#2dd4bf">{{ data.resumen_escuela?.instructores_activos }}</div>
          <div class="stat-label">Instructores activos</div>
        </div>
        <div class="stat-card accent-green">
          <div class="stat-value text-positive">{{ data.resumen_escuela?.aeronaves_disponibles }}</div>
          <div class="stat-label">Aeronaves disponibles</div>
        </div>
        <div class="stat-card accent-amber">
          <div class="stat-value text-amber">{{ data.resumen_escuela?.vuelos_hoy }}</div>
          <div class="stat-label">Vuelos programados hoy</div>
        </div>
        <div class="stat-card" :class="data.reportes_sms_nuevos > 0 ? 'accent-red' : 'accent-green'">
          <div class="stat-value" :class="data.reportes_sms_nuevos > 0 ? 'text-negative' : 'text-positive'">
            {{ data.reportes_sms_nuevos }}
          </div>
          <div class="stat-label">Reportes SMS nuevos</div>
        </div>
        <div class="stat-card accent-purple">
          <div class="stat-value" style="color:#a78bfa">{{ data.vencimientos_criticos?.length ?? 0 }}</div>
          <div class="stat-label">Vencimientos próximos</div>
        </div>
      </div>

      <!-- ── Fila central ───────────────────────────────────────────── -->
      <div class="center-row q-mb-lg">

        <!-- Vencimientos críticos -->
        <div class="rac-card q-pa-lg">
          <div class="card-title q-mb-md">
            <q-icon name="alarm" color="red-4" size="18px" class="q-mr-xs" />
            Vencimientos críticos (próximos 30 días)
            <q-space />
            <q-btn flat dense size="xs" label="Ver todos" color="red-5" to="/vencimientos" />
          </div>

          <div v-if="!vencCriticos.length" class="no-data text-positive">
            <q-icon name="check_circle" class="q-mr-xs" />
            Sin vencimientos críticos
          </div>

          <div
            v-for="v in vencCriticos.slice(0, 6)"
            :key="v.id"
            class="alert-vencimiento q-mb-xs"
            :class="v.nivel_calculado"
          >
            <q-icon :name="v.dias_restantes <= 0 ? 'error' : 'warning'" size="15px" />
            <div class="q-pl-xs">
              <div style="font-size:12px;font-weight:600">{{ v.descripcion }}</div>
              <div style="font-size:10px;opacity:.7;font-family:'JetBrains Mono',monospace">
                {{ v.dias_restantes <= 0 ? 'VENCIDO' : `${v.dias_restantes} días` }}
                · {{ v.fecha_vencimiento }}
              </div>
            </div>
          </div>
        </div>

        <!-- SMS KPIs -->
        <div class="rac-card q-pa-lg">
          <div class="card-title q-mb-md">
            <q-icon name="security" color="green-4" size="18px" class="q-mr-xs" />
            SMS — Seguridad Operacional
            <q-space />
            <q-btn flat dense size="xs" label="Ver SMS" color="red-4" to="/sms" />
          </div>

          <div class="sms-grid">
            <div class="sms-kpi">
              <div class="sms-val text-white">{{ data.sms_kpis?.total_reportes ?? 0 }}</div>
              <div class="sms-lbl">Reportes (3 meses)</div>
            </div>
            <div class="sms-kpi">
              <div class="sms-val" :class="data.sms_kpis?.inaceptables > 0 ? 'text-negative' : 'text-positive'">
                {{ data.sms_kpis?.inaceptables ?? 0 }}
              </div>
              <div class="sms-lbl">Nivel inaceptable</div>
            </div>
            <div class="sms-kpi">
              <div class="sms-val text-amber">{{ data.sms_kpis?.acciones_pendientes ?? 0 }}</div>
              <div class="sms-lbl">Acciones pendientes</div>
            </div>
            <div class="sms-kpi">
              <div class="sms-val" :class="data.sms_kpis?.acciones_vencidas > 0 ? 'text-negative' : 'text-positive'">
                {{ data.sms_kpis?.acciones_vencidas ?? 0 }}
              </div>
              <div class="sms-lbl">Acciones vencidas</div>
            </div>
          </div>

          <q-separator class="q-my-md" style="opacity:.15" />

          <!-- Botones de acción rápida -->
          <div class="card-title q-mb-sm" style="font-size:12px">Acceso rápido</div>
          <div class="accesos-rapidos">
            <q-btn flat dense to="/vuelo/nueva-bitacora"  icon="flight_takeoff" label="Nueva bitácora"  size="sm" color="red-4" />
            <q-btn flat dense to="/sms/nuevo-reporte" icon="report"        label="Reporte SMS"    size="sm" color="emerald-4" />
            <q-btn flat dense to="/reservas"          icon="calendar_month" label="Calendario"     size="sm" color="amber-5" />
            <q-btn flat dense to="/vencimientos"      icon="alarm"         label="Alertas RAC"    size="sm" color="red-5" />
          </div>
        </div>

      </div>

      <!-- ── Aeronaves en mantenimiento ─────────────────────────────── -->
      <div v-if="data.aeronaves_mantenimiento?.length" class="rac-card q-pa-lg">
        <div class="card-title q-mb-md">
          <q-icon name="build" color="amber-5" size="18px" class="q-mr-xs" />
          Aeronaves en mantenimiento
        </div>
        <div class="aeronaves-row">
          <div
            v-for="a in data.aeronaves_mantenimiento"
            :key="a.id"
            class="aeronave-chip"
            @click="$router.push(`/aeronaves/${a.id}`)"
          >
            <span class="chip-matricula">{{ a.matricula }}</span>
            <span class="chip-modelo">{{ a.modelo }}</span>
            <span class="chip-mel" v-if="a.mel_abiertos?.length">
              MEL: {{ a.mel_abiertos.length }}
            </span>
          </div>
        </div>
      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from 'boot/axios'

const cargando = ref(true)
const data     = ref(null)

const vencCriticos = computed(() => {
  return (data.value?.vencimientos_criticos || []).filter(v => v.dias_restantes <= 30)
})

onMounted(async () => {
  try {
    const { data: res } = await api.get('/dashboard')
    data.value = res.data
  } finally {
    cargando.value = false
  }
})
</script>

<style lang="scss" scoped>
.kpis-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 12px;
  .stat-value { font-family: 'Syne', sans-serif; font-size: 32px; font-weight: 800; }
  .stat-label { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 3px; }
}
.center-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
  @media (max-width: 768px) { grid-template-columns: 1fr; }
}
.card-title {
  font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700;
  color: #fff; display: flex; align-items: center;
}
.sms-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
  .sms-kpi { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); border-radius: 8px; padding: 12px; }
  .sms-val { font-family: 'Syne', sans-serif; font-size: 26px; font-weight: 800; }
  .sms-lbl { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }
}
.accesos-rapidos { display: flex; gap: 8px; flex-wrap: wrap; }
.aeronaves-row   { display: flex; gap: 10px; flex-wrap: wrap; }
.aeronave-chip {
  background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2);
  border-radius: 8px; padding: 10px 14px; cursor: pointer; transition: background .15s;
  &:hover { background: rgba(245,158,11,.15); }
  .chip-matricula { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #f59e0b; display: block; }
  .chip-modelo    { font-size: 11px; color: rgba(255,255,255,.5); display: block; }
  .chip-mel       { font-size: 10px; color: #ef4444; font-family: 'JetBrains Mono', monospace; display: block; margin-top: 3px; }
}
.no-data { font-size: 13px; color: rgba(255,255,255,.4); padding: 4px 0; }
</style>
