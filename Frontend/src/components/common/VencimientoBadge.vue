<template>
  <div
    class="venc-badge"
    :class="[`venc-${nivel}`, clickable ? 'cursor-pointer' : '']"
    @click="clickable && $emit('click')"
  >
    <q-icon :name="icono" :color="colorIcono" size="16px" class="q-mr-xs" />
    <div class="venc-info">
      <div class="venc-desc">{{ descripcion }}</div>
      <div class="venc-meta">
        <span class="venc-entidad">{{ tipoLabel }}</span>
        <span class="venc-dias" :style="`color:${colorDias}`">
          {{ diasLabel }}
        </span>
      </div>
    </div>
    <slot name="action" />
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  descripcion:   { type: String,  required: true },
  tipoEntidad:   { type: String,  default: '' },
  diasRestantes: { type: Number,  default: null },
  fechaVencimiento: { type: String, default: null },
  nivel:         { type: String,  default: 'info' }, // critico | advertencia | info | vencido
  clickable:     { type: Boolean, default: false },
})

defineEmits(['click'])

const nivel = computed(() => {
  if (props.nivel) return props.nivel
  const d = props.diasRestantes
  if (d === null) return 'info'
  if (d <= 0)  return 'vencido'
  if (d <= 15) return 'critico'
  if (d <= 30) return 'advertencia'
  return 'info'
})

const icono = computed(() => ({
  vencido:     'cancel',
  critico:     'error',
  advertencia: 'warning',
  info:        'schedule',
}[nivel.value] || 'info'))

const colorIcono = computed(() => ({
  vencido:     'negative',
  critico:     'negative',
  advertencia: 'warning',
  info:        'info',
}[nivel.value] || 'info'))

const colorDias = computed(() => ({
  vencido:     '#ef4444',
  critico:     '#ef4444',
  advertencia: '#f59e0b',
  info:        '#60a5fa',
}[nivel.value] || '#94a3b8'))

const tipoLabel = computed(() => ({
  aeronave:   '✈ Aeronave',
  instructor: '🧑‍✈️ Instructor',
  estudiante: '🎓 Estudiante',
  documento:  '📄 Documento',
  escuela:    '🏫 Escuela',
}[props.tipoEntidad] || props.tipoEntidad))

const diasLabel = computed(() => {
  const d = props.diasRestantes
  if (d === null) return props.fechaVencimiento || ''
  if (d <= 0) return 'VENCIDO'
  if (d === 1) return 'Vence mañana'
  return `Vence en ${d} días`
})
</script>

<style scoped>
.venc-badge {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 12px;
  border-radius: 8px;
  border-left: 3px solid;
  margin-bottom: 6px;
  transition: opacity .15s;
}
.venc-badge.cursor-pointer:hover { opacity: .85; }

.venc-vencido     { background: rgba(239,68,68,.07);   border-color: #ef4444; }
.venc-critico     { background: rgba(239,68,68,.05);   border-color: #ef4444; }
.venc-advertencia { background: rgba(245,158,11,.06);  border-color: #f59e0b; }
.venc-info        { background: rgba(96,165,250,.05);  border-color: #60a5fa; }

.venc-info   { flex: 1; min-width: 0; }
.venc-desc   { font-size: 13px; color: #e2e8f0; font-weight: 500;
               white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.venc-meta   { display: flex; align-items: center; gap: 10px; margin-top: 3px; }
.venc-entidad{ font-size: 10px; color: #64748b; }
.venc-dias   { font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 600; }
</style>
