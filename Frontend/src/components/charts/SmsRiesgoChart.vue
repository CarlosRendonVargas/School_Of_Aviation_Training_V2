<template>
  <div style="position:relative; width:100%; max-width:200px; margin:0 auto">
    <canvas ref="canvasRef" :height="200"></canvas>
    <!-- Número central -->
    <div class="absolute-full flex flex-center column" style="pointer-events:none">
      <div class="font-head text-white" style="font-size:26px; font-weight:800; line-height:1">
        {{ total }}
      </div>
      <div class="font-mono" style="font-size:9px; color:#475569; letter-spacing:1px">REPORTES</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { Chart, DoughnutController, ArcElement, Tooltip } from 'chart.js'
Chart.register(DoughnutController, ArcElement, Tooltip)

const props = defineProps({
  // { aceptable: 5, tolerable: 3, inaceptable: 1 }
  distribucion: { type: Object, default: () => ({ aceptable: 0, tolerable: 0, inaceptable: 0 }) },
})

const canvasRef = ref(null)
let chart = null

const total = computed(() =>
  (props.distribucion.aceptable  || 0) +
  (props.distribucion.tolerable  || 0) +
  (props.distribucion.inaceptable|| 0)
)

function crear() {
  if (!canvasRef.value) return
  const ctx = canvasRef.value.getContext('2d')

  chart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Aceptable', 'Tolerable', 'Inaceptable'],
      datasets: [{
        data: [
          props.distribucion.aceptable  || 0,
          props.distribucion.tolerable  || 0,
          props.distribucion.inaceptable|| 0,
        ],
        backgroundColor: [
          'rgba(34,197,94,0.7)',
          'rgba(245,158,11,0.7)',
          'rgba(239,68,68,0.7)',
        ],
        borderColor: [
          'rgba(34,197,94,1)',
          'rgba(245,158,11,1)',
          'rgba(239,68,68,1)',
        ],
        borderWidth: 2,
        hoverOffset: 4,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '72%',
      animation: { duration: 600 },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0f1218',
          borderColor: 'rgba(255,255,255,.1)',
          borderWidth: 1,
          titleColor: '#e2e8f0',
          bodyColor: '#94a3b8',
          callbacks: {
            label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`,
          },
        },
      },
    },
  })
}

function actualizar() {
  if (!chart) { crear(); return }
  chart.data.datasets[0].data = [
    props.distribucion.aceptable  || 0,
    props.distribucion.tolerable  || 0,
    props.distribucion.inaceptable|| 0,
  ]
  chart.update('active')
}

watch(() => props.distribucion, actualizar, { deep: true })
onMounted(crear)
onUnmounted(() => chart?.destroy())
</script>
