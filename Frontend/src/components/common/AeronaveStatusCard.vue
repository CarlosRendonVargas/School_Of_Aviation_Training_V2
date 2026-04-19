<template>
  <div class="av-card" :class="{ 'av-card--clickable': clickable, [`av-estado--${aeronave.estado}`]: true }"
    @click="clickable && $emit('click')">

    <!-- Indicador de estado -->
    <div class="av-card__status-bar"></div>

    <div class="av-card__body">
      <!-- Matrícula y modelo -->
      <div class="row items-center justify-between q-mb-xs">
        <div>
          <div class="av-card__matricula font-mono">{{ aeronave.matricula }}</div>
          <div class="av-card__modelo">{{ aeronave.modelo }}</div>
        </div>
        <q-chip dense :color="colorEstado" text-color="white" :label="aeronave.estado?.toUpperCase()"
          style="font-size:9px; font-family:monospace" />
      </div>

      <!-- Stats técnicos -->
      <div class="row q-gutter-md q-mt-sm">
        <div>
          <div style="font-size:9px; color:#64748b; font-family:monospace; letter-spacing:.5px">TTAE</div>
          <div class="font-mono" style="font-size:12px; font-weight:600; color:#60a5fa">
            {{ aeronave.horas_celula_total?.toFixed(0) || '—' }}h
          </div>
        </div>
        <div>
          <div style="font-size:9px; color:#64748b; font-family:monospace; letter-spacing:.5px">SMOH</div>
          <div class="font-mono" style="font-size:12px; font-weight:600; color:#94a3b8">
            {{ aeronave.horas_desde_oh?.toFixed(0) || '—' }}h
          </div>
        </div>
        <div>
          <div style="font-size:9px; color:#64748b; font-family:monospace; letter-spacing:.5px">AW</div>
          <div class="font-mono" style="font-size:12px; font-weight:600" :style="`color:${colorAw}`">
            {{ diasAw >= 0 ? diasAw + 'd' : 'VENC.' }}
          </div>
        </div>
      </div>

      <!-- Alertas MEL / ADs -->
      <div v-if="tieneMel || tieneAds" class="row q-gutter-xs q-mt-sm">
        <q-chip v-if="tieneMel" dense color="warning" text-color="white"
          :label="`MEL: ${melCount}`" icon="warning" style="font-size:9px; font-family:monospace" />
        <q-chip v-if="tieneAds" dense color="negative" text-color="white"
          :label="`AD: ${adsCount}`" icon="gpp_maybe" style="font-size:9px; font-family:monospace" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import dayjs from 'dayjs'

const props = defineProps({
  aeronave:  { type: Object,  required: true },
  clickable: { type: Boolean, default: false },
})
defineEmits(['click'])

const diasAw   = computed(() => dayjs(props.aeronave.venc_airworthiness).diff(dayjs(), 'day'))
const tieneMel = computed(() => (props.aeronave.mel_abiertos?.length || 0) > 0)
const tieneAds = computed(() => (props.aeronave.ads_pendientes?.length || 0) > 0)
const melCount = computed(() => props.aeronave.mel_abiertos?.length || 0)
const adsCount = computed(() => props.aeronave.ads_pendientes?.length || 0)

const colorEstado = computed(() => ({
  disponible:    'positive',
  mantenimiento: 'warning',
  baja:          'negative',
}[props.aeronave.estado] || 'grey'))

const colorAw = computed(() => {
  const d = diasAw.value
  if (d <= 0)  return '#ef4444'
  if (d <= 30) return '#f59e0b'
  return '#22c55e'
})
</script>

<style scoped>
.av-card {
  background: #0f1218;
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 10px;
  overflow: hidden;
  transition: border-color .2s, box-shadow .2s;
  position: relative;
}
.av-card--clickable { cursor: pointer; }
.av-card--clickable:hover { border-color: rgba(255,255,255,.16); box-shadow: 0 4px 20px rgba(0,0,0,.3); }

.av-card__status-bar { height: 3px; width: 100%; }
.av-estado--disponible    .av-card__status-bar { background: #22c55e; }
.av-estado--mantenimiento .av-card__status-bar { background: #f59e0b; }
.av-estado--baja          .av-card__status-bar { background: #ef4444; }

.av-card__body      { padding: 12px 14px; }
.av-card__matricula { font-size: 16px; font-weight: 700; color: #fff; letter-spacing: .5px; }
.av-card__modelo    { font-size: 11px; color: #64748b; margin-top: 1px; }
</style>
