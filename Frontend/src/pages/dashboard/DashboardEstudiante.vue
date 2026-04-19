<template>
  <div>

    <!-- Tarjetas rápidas -->
    <div class="row q-col-gutter-md q-mb-lg">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="stat-num text-primary">{{ horasTotal.toFixed(1) }}<span style="font-size:14px">h</span></div>
            <div class="stat-label">Horas de vuelo total</div>
          </template>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="stat-num" :style="`color:${colorPorcentaje}`">{{ porcentajeGeneral }}%</div>
            <div class="stat-label">Programa completado</div>
          </template>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="stat-num" :class="tieneMedico ? 'text-positive' : 'text-negative'">
              <q-icon :name="tieneMedico ? 'verified_user' : 'gpp_bad'" />
            </div>
            <div class="stat-label">Cert. Médico RAC 67</div>
          </template>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card" :class="listoParaExamen ? '' : 'cursor-pointer'"
          @click="!listoParaExamen && $router.push('/mi-progreso')">
          <q-skeleton v-if="cargando" type="rect" height="60px" dark />
          <template v-else>
            <div class="stat-num" :class="listoParaExamen ? 'text-positive' : 'text-warning'">
              <q-icon :name="listoParaExamen ? 'verified' : 'pending'" />
            </div>
            <div class="stat-label">{{ listoParaExamen ? 'Listo para examen' : 'En progreso' }}</div>
          </template>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-md q-mb-md">

      <!-- Gráfica radar — progreso RAC 61 -->
      <div class="col-12 col-md-5">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-sm">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                Progreso RAC 61
              </div>
              <q-btn flat dense no-caps to="/mi-progreso" color="primary" size="sm"
                label="Ver detalle" icon-right="arrow_forward" />
            </div>
            <div class="font-mono q-mb-md" style="font-size:10px; color:#475569">
              {{ data?.programa || '—' }}
            </div>
            <q-skeleton v-if="cargando || !categorias" type="rect" height="280px" dark />
            <ProgresoHorasChart v-else :datos="categorias" />
          </q-card-section>
        </q-card>
      </div>

      <!-- Barras de progreso detallado -->
      <div class="col-12 col-md-7">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
              Horas por categoría
            </div>
            <q-skeleton v-if="cargando" v-for="n in 5" :key="n" type="rect" height="40px" dark class="q-mb-sm" />
            <template v-else>
              <HorasProgressBar
                v-for="cat in categoriasList" :key="cat.key"
                :label="cat.label"
                :acumulado="cat.acumulado"
                :requerido="cat.requerido"
                :icono="cat.icono"
              />
            </template>

            <!-- Listo para examen badge -->
            <div v-if="listoParaExamen" class="q-mt-md text-center">
              <q-chip color="positive" text-color="white" icon="verified"
                label="✔ Cumple todos los requisitos RAC 61 para el examen UAEAC"
                style="font-size:12px; padding:8px 14px; border-radius:8px; white-space:normal; height:auto" />
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>

    <!-- Historial de horas por mes -->
    <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
      <q-card-section>
        <div class="row items-center justify-between q-mb-md">
          <div class="font-head text-white" style="font-size:15px; font-weight:700">
            Horas voladas por mes (últimos 12 meses)
          </div>
          <div class="row q-gutter-xs">
            <q-btn v-for="campo in camposMes" :key="campo.value"
              :unelevated="campoSeleccionado === campo.value"
              :outline="campoSeleccionado !== campo.value"
              dense no-caps size="sm" :color="campo.color"
              :label="campo.label"
              style="border-radius:5px; min-width:52px"
              @click="campoSeleccionado = campo.value"
            />
          </div>
        </div>
        <q-skeleton v-if="cargando" type="rect" height="180px" dark />
        <HorasMensualesChart v-else :datos="historialMensual" :campo="campoSeleccionado" />
      </q-card-section>
    </q-card>

    <div class="row q-col-gutter-md">

      <!-- Próxima reserva -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                ✈️ Próximo vuelo
              </div>
              <q-btn flat dense no-caps to="/reservas" color="primary" size="sm"
                label="Ver reservas" icon-right="arrow_forward" />
            </div>
            <q-skeleton v-if="cargando" type="QItem" dark />

            <div v-if="!cargando && !proximaReserva"
              class="text-center q-py-md" style="color:#475569; font-size:13px">
              <q-icon name="event_available" color="grey-7" size="32px" class="q-mb-xs" /><br>
              Sin vuelos programados
              <div class="q-mt-sm">
                <q-btn unelevated dense no-caps to="/reservas" color="primary"
                  label="Solicitar reserva" style="border-radius:6px; font-size:12px" />
              </div>
            </div>

            <div v-else-if="proximaReserva"
              style="background:rgba(59,130,246,.06); border:1px solid rgba(59,130,246,.15); border-radius:10px; padding:14px">
              <div class="row q-col-gutter-md">
                <div class="col-6">
                  <div style="font-size:10px; color:#64748b; margin-bottom:2px; font-family:monospace">FECHA</div>
                  <div class="text-white" style="font-size:15px; font-weight:600">{{ proximaReserva.fecha }}</div>
                </div>
                <div class="col-6">
                  <div style="font-size:10px; color:#64748b; margin-bottom:2px; font-family:monospace">HORA</div>
                  <div class="text-white" style="font-size:15px; font-weight:600">
                    {{ proximaReserva.hora_inicio?.slice(0,5) }}
                  </div>
                </div>
                <div class="col-6">
                  <div style="font-size:10px; color:#64748b; margin-bottom:2px; font-family:monospace">AERONAVE</div>
                  <div class="text-primary" style="font-size:14px; font-weight:700; font-family:monospace">
                    {{ proximaReserva.aeronave?.matricula }}
                  </div>
                  <div style="font-size:11px; color:#64748b">{{ proximaReserva.aeronave?.modelo }}</div>
                </div>
                <div class="col-6" v-if="proximaReserva.instructor">
                  <div style="font-size:10px; color:#64748b; margin-bottom:2px; font-family:monospace">INSTRUCTOR</div>
                  <div class="text-white" style="font-size:13px">
                    {{ proximaReserva.instructor?.persona?.nombres }}
                  </div>
                </div>
              </div>
              <div class="q-mt-sm">
                <q-chip dense :color="colorTipoRes(proximaReserva.tipo)" text-color="white"
                  :label="proximaReserva.tipo?.toUpperCase()"
                  style="font-family:monospace; font-size:10px" />
                <q-chip dense :color="colorEstadoRes(proximaReserva.estado)" text-color="white"
                  :label="proximaReserva.estado?.toUpperCase()"
                  style="font-family:monospace; font-size:10px; margin-left:6px" />
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Mis alertas de vencimiento -->
      <div class="col-12 col-md-6">
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="row items-center justify-between q-mb-md">
              <div class="font-head text-white" style="font-size:15px; font-weight:700">
                ⚠️ Mis alertas
              </div>
              <q-btn flat dense no-caps to="/vencimientos" color="warning" size="sm"
                label="Ver todas" icon-right="arrow_forward" />
            </div>
            <q-skeleton v-if="cargando" v-for="n in 2" :key="n" type="rect" height="52px" dark class="q-mb-xs" />

            <VencimientoBadge
              v-for="v in vencimientosEstudiante" :key="v.id"
              :descripcion="v.descripcion"
              :tipo-entidad="v.tipo_entidad"
              :dias-restantes="v.dias_restantes"
              :nivel="v.nivel_calculado"
            />

            <div v-if="!cargando && !vencimientosEstudiante.length"
              class="text-center q-py-md" style="color:#475569; font-size:13px">
              <q-icon name="check_circle" color="positive" size="28px" class="q-mb-xs" /><br>
              Sin alertas de vencimiento
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import ProgresoHorasChart from 'components/charts/ProgresoHorasChart.vue'
import HorasMensualesChart from 'components/charts/HorasMensualesChart.vue'
import HorasProgressBar from 'components/common/HorasProgressBar.vue'
import VencimientoBadge from 'components/common/VencimientoBadge.vue'

const props = defineProps({
  data:     { type: Object, default: null },
  cargando: { type: Boolean, default: false },
})

const campoSeleccionado = ref('horas')
const camposMes = [
  { label: 'Total',  value: 'horas', color: 'primary' },
  { label: 'Dual',   value: 'dual',  color: 'teal'    },
  { label: 'Solo',   value: 'solo',  color: 'amber-9' },
]

const categorias        = computed(() => props.data?.progreso_horas?.categorias || null)
const tieneMedico       = computed(() => props.data?.tiene_medico || false)
const listoParaExamen   = computed(() => props.data?.progreso_horas?.listo_para_examen || false)
const proximaReserva    = computed(() => props.data?.proxima_reserva || null)
const historialMensual  = computed(() => props.data?.historial_mensual || [])
const vencimientosEstudiante = computed(() => (props.data?.vencimientos || []).slice(0, 4))

const ICONOS = {
  vuelo_total: 'flight',  dual: 'supervisor_account',
  solo: 'person',         noche: 'nights_stay',
  ifr: 'radar',           simulador: 'computer',
}
const LABELS = {
  vuelo_total: 'Vuelo total', dual: 'Instrucción dual',
  solo: 'Vuelo solo',         noche: 'Vuelo nocturno',
  ifr: 'Instrumentos (IFR)',  simulador: 'Simulador',
}

const categoriasList = computed(() => {
  const cats = categorias.value || {}
  return Object.entries(cats)
    .filter(([, v]) => (v.requerido || 0) > 0)
    .map(([key, v]) => ({
      key,
      label:     LABELS[key] || key,
      icono:     ICONOS[key] || 'flight',
      acumulado: v.acumulado || 0,
      requerido: v.requerido || 0,
    }))
})

const horasTotal = computed(() => categorias.value?.vuelo_total?.acumulado || 0)

const porcentajeGeneral = computed(() => {
  const cats = categoriasList.value.filter(c => c.requerido > 0)
  if (!cats.length) return 0
  const cat = cats[0] // RAC: primero el total
  return Math.min(100, Math.round((cat.acumulado / cat.requerido) * 100))
})

const colorPorcentaje = computed(() => {
  const p = porcentajeGeneral.value
  return p >= 100 ? '#22c55e' : p >= 60 ? '#f59e0b' : '#3b82f6'
})

const colorTipoRes  = (t) => ({ instruccion:'primary', solo:'teal', simulador:'purple' }[t] || 'grey')
const colorEstadoRes = (e) => ({ pendiente:'warning', confirmada:'primary', completada:'positive' }[e] || 'grey')
</script>
