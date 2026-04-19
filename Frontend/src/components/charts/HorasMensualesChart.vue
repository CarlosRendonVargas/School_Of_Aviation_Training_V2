<template>
  <div style="position:relative; width:100%; height:180px">
    <canvas ref="canvasRef"></canvas>
    <div v-if="!datos || !datos.length"
      class="absolute-full flex flex-center column"
      style="color:#475569; font-size:13px; gap:8px">
      <q-icon name="bar_chart" size="32px" color="grey-8" />
      Sin datos de vuelo registrados
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
  Chart, BarController, CategoryScale, LinearScale,
  BarElement, Tooltip, Legend,
} from 'chart.js'

Chart.register(BarController, CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps({
  // Array de { mes: '2025-03', horas: 12.5, dual: 8, solo: 4.5 }
  datos: { type: Array, default: () => [] },
  // Qué campo mostrar: 'horas' (total), 'dual', 'solo'
  campo: { type: String, default: 'horas' },
})

const canvasRef = ref(null)
let chart = null

const COLORES = {
  horas: { border: '#3b82f6', bg: 'rgba(59,130,246,0.5)',  hover: 'rgba(59,130,246,0.8)' },
  dual:  { border: '#14b8a6', bg: 'rgba(20,184,166,0.5)',  hover: 'rgba(20,184,166,0.8)' },
  solo:  { border: '#f59e0b', bg: 'rgba(245,158,11,0.5)',  hover: 'rgba(245,158,11,0.8)' },
  noche: { border: '#8b5cf6', bg: 'rgba(139,92,246,0.5)',  hover: 'rgba(139,92,246,0.8)' },
}

function labels() {
  return (props.datos || []).map(d => {
    const [, m] = d.mes.split('-')
    const meses = ['', 'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']
    return meses[parseInt(m)]
  })
}

function valores() {
  return (props.datos || []).map(d => parseFloat(d[props.campo] || 0))
}

function crear() {
  if (!canvasRef.value || !props.datos?.length) return
  const ctx = canvasRef.value.getContext('2d')
  const col = COLORES[props.campo] || COLORES.horas

  chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels(),
      datasets: [{
        label: `Horas ${props.campo}`,
        data: valores(),
        backgroundColor:      col.bg,
        borderColor:          col.border,
        hoverBackgroundColor: col.hover,
        borderWidth: 1.5,
        borderRadius: 5,
        borderSkipped: 'bottom',
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      animation: { duration: 500 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f1218',
          borderColor: 'rgba(255,255,255,.1)',
          borderWidth: 1,
          titleColor: '#e2e8f0',
          bodyColor: '#94a3b8',
          callbacks: {
            label: (ctx) => ` ${ctx.parsed.y.toFixed(1)}h`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#475569', font: { family: 'JetBrains Mono', size: 10 } },
        },
        y: {
          beginAtZero: true,
          grid: { color: 'rgba(255,255,255,.05)' },
          ticks: {
            color: '#475569',
            font: { family: 'JetBrains Mono', size: 10 },
            callback: (v) => v + 'h',
          },
        },
      },
    },
  })
}

function actualizar() {
  if (!chart) { crear(); return }
  chart.data.labels           = labels()
  chart.data.datasets[0].data = valores()
  chart.update('active')
}

watch(() => [props.datos, props.campo], actualizar, { deep: true })
onMounted(crear)
onUnmounted(() => chart?.destroy())
</script>
