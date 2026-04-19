<template>
  <div style="position:relative; width:100%; max-width:320px; margin:0 auto">
    <canvas ref="canvasRef" :height="320"></canvas>
    <div v-if="!datos" class="absolute-full flex flex-center">
      <q-spinner color="primary" size="32px" />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
  Chart,
  RadarController,
  RadialLinearScale,
  PointElement,
  LineElement,
  Filler,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(RadarController, RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend)

const props = defineProps({
  // Objeto con categorías de horas: { vuelo_total: { acumulado, requerido }, dual: {...}, ... }
  datos: { type: Object, default: null },
})

const canvasRef = ref(null)
let chartInstance = null

const CATEGORIAS = [
  { key: 'vuelo_total', label: 'Total'   },
  { key: 'dual',        label: 'Dual'    },
  { key: 'solo',        label: 'Solo'    },
  { key: 'noche',       label: 'Noche'   },
  { key: 'ifr',         label: 'IFR'     },
  { key: 'simulador',   label: 'Sim.'    },
]

function buildDatasets() {
  const filtradas = CATEGORIAS.filter(c => {
    const cat = props.datos?.[c.key]
    return cat && (cat.requerido || 0) > 0
  })

  const labels      = filtradas.map(c => c.label)
  const acumulado   = filtradas.map(c => Math.min(100, props.datos[c.key].porcentaje || 0))
  const requerido   = filtradas.map(() => 100) // 100% como referencia

  return { labels, acumulado, requerido }
}

function crearGrafica() {
  if (!canvasRef.value || !props.datos) return

  const ctx = canvasRef.value.getContext('2d')
  const { labels, acumulado, requerido } = buildDatasets()

  chartInstance = new Chart(ctx, {
    type: 'radar',
    data: {
      labels,
      datasets: [
        {
          label: 'Requerido RAC 61',
          data: requerido,
          borderColor: 'rgba(100,116,139,0.4)',
          backgroundColor: 'rgba(100,116,139,0.05)',
          borderWidth: 1,
          borderDash: [4, 4],
          pointRadius: 0,
        },
        {
          label: 'Acumulado',
          data: acumulado,
          borderColor: 'rgba(59,130,246,0.9)',
          backgroundColor: 'rgba(59,130,246,0.15)',
          borderWidth: 2,
          pointBackgroundColor: 'rgba(59,130,246,1)',
          pointRadius: 4,
          pointHoverRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      animation: { duration: 600, easing: 'easeInOutQuart' },
      plugins: {
        legend: {
          display: true,
          position: 'bottom',
          labels: {
            color: '#94a3b8',
            font: { family: 'JetBrains Mono', size: 10 },
            padding: 12,
            boxWidth: 12,
          },
        },
        tooltip: {
          backgroundColor: '#0f1218',
          borderColor: 'rgba(255,255,255,.1)',
          borderWidth: 1,
          titleColor: '#e2e8f0',
          bodyColor: '#94a3b8',
          callbacks: {
            label: (ctx) => ` ${ctx.dataset.label}: ${ctx.parsed.r.toFixed(0)}%`,
          },
        },
      },
      scales: {
        r: {
          min: 0,
          max: 100,
          ticks: {
            stepSize: 25,
            color: '#334155',
            font: { family: 'JetBrains Mono', size: 9 },
            backdropColor: 'transparent',
            callback: (v) => v + '%',
          },
          grid:       { color: 'rgba(255,255,255,.06)' },
          angleLines: { color: 'rgba(255,255,255,.06)' },
          pointLabels: {
            color: '#94a3b8',
            font: { family: 'IBM Plex Sans', size: 11, weight: '500' },
          },
        },
      },
    },
  })
}

function actualizarGrafica() {
  if (!chartInstance || !props.datos) return
  const { labels, acumulado, requerido } = buildDatasets()
  chartInstance.data.labels               = labels
  chartInstance.data.datasets[0].data     = requerido
  chartInstance.data.datasets[1].data     = acumulado
  chartInstance.update('active')
}

watch(() => props.datos, (nuevo) => {
  if (nuevo) {
    if (chartInstance) actualizarGrafica()
    else               crearGrafica()
  }
}, { deep: true })

onMounted(() => { if (props.datos) crearGrafica() })
onUnmounted(() => { chartInstance?.destroy() })
</script>
