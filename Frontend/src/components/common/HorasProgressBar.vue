<template>
  <div class="horas-bar">
    <div class="horas-bar__header">
      <div class="horas-bar__label">
        <q-icon :name="icono" size="14px" color="grey-6" class="q-mr-xs" />
        {{ label }}
        <q-badge v-if="cumplido" color="positive" label="✔" class="q-ml-xs" style="font-size:9px" />
      </div>
      <div class="horas-bar__nums">
        <span class="acum">{{ acumulado.toFixed(1) }}h</span>
        <span class="sep">/</span>
        <span class="req">{{ requerido.toFixed(1) }}h</span>
        <span class="pct" :style="`color:${colorPct}`">{{ porcentaje }}%</span>
      </div>
    </div>

    <div class="horas-bar__track">
      <div
        class="horas-bar__fill"
        :style="`width:${Math.min(porcentaje,100)}%; background:${colorFill}`"
      ></div>
      <!-- Marca de límite si hay horas_sim_max -->
      <div v-if="limiteMax" class="horas-bar__limit"
        :style="`left:${Math.min((acumulado / limiteMax) * 100, 100)}%`">
      </div>
    </div>

    <div v-if="!cumplido && faltante > 0" class="horas-bar__faltante">
      Faltan {{ faltante.toFixed(1) }}h
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  label:     { type: String,  required: true },
  acumulado: { type: Number,  default: 0 },
  requerido: { type: Number,  default: 0 },
  limiteMax: { type: Number,  default: null }, // para simulador
  icono:     { type: String,  default: 'flight' },
})

const porcentaje = computed(() =>
  props.requerido > 0 ? Math.round((props.acumulado / props.requerido) * 100) : 100
)

const cumplido = computed(() => props.acumulado >= props.requerido)
const faltante = computed(() => Math.max(0, props.requerido - props.acumulado))

const colorFill = computed(() => {
  const p = porcentaje.value
  if (cumplido.value) return '#22c55e'
  if (p >= 80) return '#f59e0b'
  if (p >= 50) return '#3b82f6'
  return '#3b82f6'
})

const colorPct = computed(() => {
  if (cumplido.value) return '#22c55e'
  if (porcentaje.value >= 80) return '#f59e0b'
  return '#60a5fa'
})
</script>

<style scoped>
.horas-bar { margin-bottom: 14px; }

.horas-bar__header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 6px;
}
.horas-bar__label {
  display: flex; align-items: center;
  font-size: 13px; color: #cbd5e1; font-weight: 500;
}
.horas-bar__nums { display: flex; align-items: center; gap: 4px; }
.acum { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #60a5fa; }
.sep  { font-size: 11px; color: #334155; }
.req  { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #475569; }
.pct  { font-family: 'JetBrains Mono', monospace; font-size: 11px; margin-left: 6px; }

.horas-bar__track {
  position: relative;
  height: 8px;
  background: rgba(255,255,255,.06);
  border-radius: 4px;
  overflow: hidden;
}
.horas-bar__fill {
  height: 100%;
  border-radius: 4px;
  transition: width .5s ease, background .3s ease;
}
.horas-bar__limit {
  position: absolute; top: 0; bottom: 0;
  width: 2px; background: rgba(255,255,255,.25);
  transform: translateX(-1px);
}
.horas-bar__faltante {
  font-size: 11px; color: #ef4444; margin-top: 3px;
  font-family: 'JetBrains Mono', monospace;
}
</style>
