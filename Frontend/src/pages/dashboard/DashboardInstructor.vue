<template>
  <div>
    <!-- Estado instructor -->
    <q-card flat class="card-rac q-mb-md" style="background:#0f1218"
      :style="!licenciaOk ? 'border-color:rgba(239,68,68,.4)' : ''">
      <q-card-section>
        <div class="row items-center q-gutter-md">
          <q-avatar size="52px" color="teal" text-color="white" style="font-family:Syne; font-size:20px; font-weight:700">
            {{ iniciales }}
          </q-avatar>
          <div class="col">
            <div class="font-head text-white" style="font-size:17px; font-weight:700">{{ data?.instructor?.nombre }}</div>
            <div class="font-mono q-mt-xs" style="font-size:11px; color:#64748b">
              Lic. {{ data?.instructor?.licencia }} ·
              <span :style="`color:${licenciaOk ? '#22c55e' : '#ef4444'}`">
                Vence {{ data?.instructor?.venc_licencia }}
                ({{ data?.instructor?.dias_venc }}d)
              </span>
            </div>
          </div>
          <q-chip dense :color="licenciaOk ? 'positive' : 'negative'" text-color="white" icon="verified"
            :label="licenciaOk ? 'Licencia vigente' : '⚠ Licencia próxima a vencer'" />
        </div>
      </q-card-section>
    </q-card>

    <!-- Stats -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-num text-teal">{{ data?.horas_mes?.toFixed(1) || '0.0' }}<span style="font-size:14px">h</span></div>
          <div class="stat-label">Horas este mes</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-num text-primary">{{ vuelosHoy.length }}</div>
          <div class="stat-label">Vuelos hoy</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-num text-amber">{{ data?.estudiantes_asignados?.length || 0 }}</div>
          <div class="stat-label">Estudiantes activos</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div class="stat-num" :style="`color:${data?.mis_vencimientos?.length ? '#f59e0b' : '#22c55e'}`">
            {{ data?.mis_vencimientos?.length || 0 }}
          </div>
          <div class="stat-label">Alertas personales</div>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-md">
      <!-- Vuelos del día -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              ✈️ Mis vuelos de hoy
            </div>
            <div v-if="!vuelosHoy.length" class="text-center q-py-md" style="color:#475569; font-size:13px">
              Sin vuelos programados hoy
            </div>
            <div v-for="v in vuelosHoy" :key="v.id"
              class="q-pa-sm q-mb-xs rounded-borders"
              style="background:rgba(59,130,246,.06); border:1px solid rgba(59,130,246,.15)">
              <div class="row items-center justify-between">
                <div>
                  <div class="font-mono text-white" style="font-size:13px">
                    {{ v.hora_inicio }} — {{ v.aeronave?.matricula }}
                  </div>
                  <div style="font-size:12px; color:#94a3b8; margin-top:2px">
                    {{ v.estudiante?.persona?.nombres }} {{ v.estudiante?.persona?.apellidos }}
                  </div>
                </div>
                <q-chip dense :color="v.estado === 'confirmada' ? 'positive' : 'warning'"
                  :label="v.estado" text-color="white" style="font-size:10px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Mis estudiantes -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                🎓 Mis estudiantes
              </div>
              <q-btn flat dense no-caps to="/estudiantes" color="grey-5" size="sm" label="Ver todos" icon-right="arrow_forward" />
            </div>
            <div v-for="est in (data?.estudiantes_asignados || []).slice(0,5)" :key="est.id"
              class="row items-center justify-between q-py-xs"
              style="border-bottom:1px solid rgba(255,255,255,.05); cursor:pointer"
              @click="$router.push(`/estudiantes/${est.id}`)">
              <div class="row items-center q-gutter-sm">
                <q-avatar size="28px" color="primary" text-color="white" style="font-size:11px">
                  {{ (est.persona?.nombres?.[0] || '') + (est.persona?.apellidos?.[0] || '') }}
                </q-avatar>
                <div style="font-size:13px; color:#e2e8f0">
                  {{ est.persona?.nombres }} {{ est.persona?.apellidos }}
                </div>
              </div>
              <q-icon name="chevron_right" color="grey-6" size="16px" />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Mis vencimientos -->
      <div v-if="data?.mis_vencimientos?.length" class="col-12">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              ⚠️ Mis alertas regulatorias
            </div>
            <div v-for="v in data.mis_vencimientos" :key="v.id"
              class="q-pa-sm q-mb-xs rounded-borders"
              :class="v.nivel_calculado === 'critico' ? 'alert-critico' : 'alert-advertencia'">
              <div class="row items-center justify-between">
                <span style="font-size:13px; color:#e2e8f0">{{ v.descripcion }}</span>
                <span class="font-mono" style="font-size:11px; color:#f59e0b">{{ v.dias_restantes }}d restantes</span>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({ data: { type: Object, default: null }, cargando: Boolean })

const licenciaOk  = computed(() => (props.data?.instructor?.dias_venc || 0) > 30)
const vuelosHoy   = computed(() => props.data?.vuelos_hoy || [])
const iniciales   = computed(() => {
  const n = props.data?.instructor?.nombre || ''
  return n.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})
</script>
