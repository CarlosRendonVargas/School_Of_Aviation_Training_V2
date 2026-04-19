<!-- DashboardInstructor.vue -->
<template>
  <div>
    <div v-if="cargando" class="flex flex-center q-pa-xl"><q-spinner-tail color="red-5" size="40px" /></div>
    <template v-else-if="data">
      <div class="stats-row q-mb-lg">
        <div class="stat-card accent-red">
          <div class="stat-value text-red-5">{{ data.horas_mes?.toFixed(1) ?? '0.0' }}h</div>
          <div class="stat-label">Horas instrucción este mes</div>
        </div>
        <div class="stat-card accent-grey">
          <div class="stat-value text-grey-4">{{ data.estudiantes_asignados?.length ?? 0 }}</div>
          <div class="stat-label">Estudiantes activos</div>
        </div>
        <div class="stat-card" :class="data.instructor?.licencia_ok ? 'accent-green' : 'accent-red'">
          <div class="stat-value" :class="data.instructor?.licencia_ok ? 'text-positive' : 'text-negative'">
            {{ data.instructor?.dias_venc ?? '–' }}d
          </div>
          <div class="stat-label">Días para vencer licencia</div>
        </div>
      </div>
      <div class="rac-card q-pa-lg q-mb-lg">
        <div class="card-title q-mb-md"><q-icon name="flight_takeoff" color="amber-5" size="18px" class="q-mr-xs" />Vuelos de hoy</div>
        <div v-if="!data.vuelos_hoy?.length" class="no-data">Sin vuelos programados hoy</div>
        <div v-for="v in data.vuelos_hoy" :key="v.id" class="vuelo-hoy-item q-mb-sm">
          <div class="vuelo-hora">{{ v.hora_inicio }} – {{ v.hora_fin }}</div>
          <div class="vuelo-est">{{ v.estudiante?.persona?.nombres }} {{ v.estudiante?.persona?.apellidos }}</div>
          <div class="vuelo-av">✈ {{ v.aeronave?.matricula }} · {{ v.aeronave?.modelo }}</div>
        </div>
      </div>
    </template>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
const cargando = ref(true); const data = ref(null)
onMounted(async () => { try { const { data: r } = await api.get('/dashboard'); data.value = r.data } finally { cargando.value = false } })
</script>
<style lang="scss" scoped>
.stats-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; .stat-value { font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 800; } .stat-label { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 3px; } }
.card-title { font-family: 'Syne', sans-serif; font-size: 15px; font-weight: 700; color: #fff; display: flex; align-items: center; }
.vuelo-hoy-item { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); border-radius: 8px; padding: 12px; .vuelo-hora { font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 700; } .vuelo-est, .vuelo-av { font-size: 12px; color: rgba(255,255,255,.5); margin-top: 2px; } }
.no-data { font-size: 13px; color: rgba(255,255,255,.4); padding: 8px 0; }
</style>
