<template>
  <div>
    <div v-if="cargando" class="flex flex-center q-pa-xl">
      <q-spinner-tail color="blue-4" size="40px" />
    </div>

    <template v-else-if="data">

      <!-- ── FILA 1: Stats rápidos ─────────────────────────────────── -->
      <div class="stats-row q-mb-lg">

        <div class="stat-card accent-blue">
          <div class="stat-icon">✈</div>
          <div class="stat-value text-blue-4">{{ data.progreso_horas?.categorias?.vuelo_total?.acumulado ?? 0 }}h</div>
          <div class="stat-label">Horas totales de vuelo</div>
          <div class="stat-meta">de {{ data.progreso_horas?.categorias?.vuelo_total?.requerido ?? 0 }}h requeridas</div>
        </div>

        <div class="stat-card accent-green">
          <div class="stat-icon">🩺</div>
          <div class="stat-value" :class="data.tiene_medico ? 'text-positive' : 'text-negative'">
            {{ data.tiene_medico ? 'Vigente' : 'Vencido' }}
          </div>
          <div class="stat-label">Certificado médico</div>
          <div class="stat-meta">RAC 67</div>
        </div>

        <div class="stat-card accent-amber">
          <div class="stat-icon">📋</div>
          <div class="stat-value text-amber">{{ data.progreso_horas?.total_vuelos ?? 0 }}</div>
          <div class="stat-label">Vuelos realizados</div>
          <div class="stat-meta">en el programa</div>
        </div>

        <div class="stat-card" :class="data.listo_para_examen ? 'accent-green' : 'accent-red'">
          <div class="stat-icon">🏆</div>
          <div class="stat-value" :class="data.listo_para_examen ? 'text-positive' : 'text-negative'">
            {{ data.listo_para_examen ? 'Listo' : 'Pendiente' }}
          </div>
          <div class="stat-label">Examen UAEAC</div>
          <div class="stat-meta">{{ data.progreso_horas?.programa }}</div>
        </div>
      </div>

      <!-- ── FILA 2: Progreso RAC 61 ──────────────────────────────── -->
      <div class="rac-card q-mb-lg q-pa-lg">
        <div class="card-title q-mb-md">
          <q-icon name="trending_up" color="blue-4" size="18px" class="q-mr-xs" />
          Progreso de horas — RAC 61 ({{ data.progreso_horas?.programa }})
        </div>

        <div class="horas-grid">
          <div
            v-for="(cat, key) in categorias"
            :key="key"
            class="hora-item"
          >
            <div class="hora-header">
              <span class="hora-label">{{ cat.label }}</span>
              <span class="hora-nums">
                <strong>{{ cat.acumulado }}h</strong>
                <span class="q-ml-xs" style="color:rgba(255,255,255,.4)">/ {{ cat.requerido || cat.limite_max }}h</span>
              </span>
            </div>
            <div class="progress-rac q-mt-xs">
              <div
                class="progress-fill"
                :class="cat.cumplido ? 'ok' : (cat.porcentaje > 0 ? 'partial' : 'empty')"
                :style="{ width: `${Math.min(cat.porcentaje, 100)}%` }"
              />
            </div>
            <div class="hora-footer">
              <span v-if="cat.cumplido" class="text-positive" style="font-size:11px">✔ Completado</span>
              <span v-else style="font-size:11px;color:rgba(255,255,255,.4)">Faltan {{ cat.faltante }}h</span>
              <span class="hora-pct">{{ cat.porcentaje }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ── FILA 3: Próxima reserva + Alerta médico ───────────────── -->
      <div class="bottom-row">

        <!-- Próxima reserva -->
        <div class="rac-card q-pa-lg">
          <div class="card-title q-mb-md">
            <q-icon name="flight_takeoff" color="amber-5" size="18px" class="q-mr-xs" />
            Próxima sesión de vuelo
          </div>

          <template v-if="data.proxima_reserva">
            <div class="reserva-card">
              <div class="reserva-fecha">
                {{ formatFecha(data.proxima_reserva.fecha) }}
                · {{ data.proxima_reserva.hora_inicio }} – {{ data.proxima_reserva.hora_fin }}
              </div>
              <div class="reserva-aeronave q-mt-xs">
                ✈ {{ data.proxima_reserva.aeronave?.matricula }}
                · {{ data.proxima_reserva.aeronave?.modelo }}
              </div>
              <div class="reserva-instructor q-mt-xs" v-if="data.proxima_reserva.instructor">
                👨‍✈️ {{ data.proxima_reserva.instructor.persona?.nombres }}
                {{ data.proxima_reserva.instructor.persona?.apellidos }}
              </div>
              <div class="q-mt-sm">
                <span class="badge-base" :class="`badge-${data.proxima_reserva.estado}`">
                  {{ data.proxima_reserva.estado }}
                </span>
              </div>
            </div>
          </template>
          <template v-else>
            <div class="no-data">Sin vuelos programados próximamente</div>
            <q-btn
              label="Solicitar reserva"
              icon="add"
              color="primary"
              flat
              dense
              class="q-mt-sm"
              to="/reservas"
            />
          </template>
        </div>

        <!-- Estado del médico + alertas -->
        <div class="rac-card q-pa-lg">
          <div class="card-title q-mb-md">
            <q-icon name="alarm" color="red-4" size="18px" class="q-mr-xs" />
            Alertas y vencimientos
          </div>

          <div
            v-for="alerta in data.vencimientos"
            :key="alerta.id"
            class="alert-vencimiento q-mb-sm"
            :class="alerta.nivel_calculado"
          >
            <q-icon :name="iconAlerta(alerta.nivel_calculado)" size="16px" />
            <div>
              <div style="font-size:12px;font-weight:600">{{ alerta.descripcion }}</div>
              <div style="font-size:11px;opacity:.7">
                Vence: {{ alerta.fecha_vencimiento }}
                · {{ alerta.dias_restantes }} días
              </div>
            </div>
          </div>

          <div v-if="!data.vencimientos?.length" class="no-data text-positive">
            <q-icon name="check_circle" class="q-mr-xs" />
            Sin alertas pendientes
          </div>
        </div>

      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

const cargando = ref(true)
const data     = ref(null)

const categorias = computed(() => {
  const cats = data.value?.progreso_horas?.categorias || {}
  const labels = {
    vuelo_total: 'Vuelo total',
    dual:        'Instrucción dual',
    solo:        'Vuelo solo',
    noche:       'Vuelo nocturno',
    ifr:         'Instrumentos IFR',
    simulador:   'Simulador FTD/FFS',
  }
  return Object.entries(cats)
    .filter(([, v]) => (v.requerido || v.limite_max) > 0)
    .reduce((acc, [k, v]) => {
      acc[k] = { ...v, label: labels[k] || k }
      return acc
    }, {})
})

function formatFecha(fecha) {
  return dayjs(fecha).format('ddd D MMM')
}

function iconAlerta(nivel) {
  return { critico: 'error', advertencia: 'warning', info: 'info' }[nivel] || 'info'
}

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
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 12px;

  .stat-card {
    .stat-icon  { font-size: 22px; margin-bottom: 8px; }
    .stat-value { font-family: 'Syne', sans-serif; font-size: 28px; font-weight: 800; }
    .stat-label { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 2px; }
    .stat-meta  { font-size: 10px; color: rgba(255,255,255,.3); margin-top: 4px; font-family: 'JetBrains Mono', monospace; }
  }
}

.card-title {
  font-family: 'Syne', sans-serif;
  font-size: 15px; font-weight: 700;
  color: #fff;
  display: flex; align-items: center;
}

.horas-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 16px;
}

.hora-item {
  .hora-header {
    display: flex; justify-content: space-between;
    font-size: 12.5px;
    .hora-label { color: rgba(255,255,255,.7); font-weight: 600; }
    .hora-nums  { font-family: 'JetBrains Mono', monospace; font-size: 12px; strong { color: #fff; } }
  }
  .hora-footer {
    display: flex; justify-content: space-between;
    margin-top: 4px;
    .hora-pct { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: rgba(255,255,255,.3); }
  }
}

.bottom-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  @media (max-width: 700px) { grid-template-columns: 1fr; }
}

.reserva-card {
  background: rgba(255,255,255,.03);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 8px;
  padding: 14px;

  .reserva-fecha { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; }
  .reserva-aeronave,
  .reserva-instructor { font-size: 13px; color: rgba(255,255,255,.6); }
}

.no-data {
  font-size: 13px;
  color: rgba(255,255,255,.4);
  padding: 8px 0;
}
</style>
