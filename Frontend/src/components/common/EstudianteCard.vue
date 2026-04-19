<template>
  <div class="est-card" :class="{ 'est-card--clickable': clickable }" @click="clickable && $emit('click')">

    <!-- Avatar + datos básicos -->
    <div class="est-card__header">
      <q-avatar :size="avatarSize" color="primary" text-color="white"
        style="font-family:Syne,sans-serif; font-weight:700; flex-shrink:0">
        {{ iniciales }}
      </q-avatar>

      <div class="est-card__info">
        <div class="est-card__nombre">{{ nombre }}</div>
        <div class="est-card__meta">
          <span class="font-mono" style="color:#64748b; font-size:10px">{{ estudiante.num_expediente }}</span>
          <span v-if="estudiante.programa?.codigo" class="est-card__chip">{{ estudiante.programa.codigo }}</span>
          <span class="est-card__estado" :class="`est-estado--${estudiante.estado}`">{{ estudiante.estado }}</span>
        </div>
      </div>

      <q-btn v-if="clickable" flat round dense icon="chevron_right" color="grey-7" size="sm" />
    </div>

    <!-- Barra de progreso global (si se proveen datos de progreso) -->
    <div v-if="progreso" class="est-card__progreso q-mt-sm">
      <div class="row items-center justify-between q-mb-xs">
        <span style="font-size:11px; color:#94a3b8">Programa completado</span>
        <span class="font-mono" :style="`font-size:11px; color:${colorPct}`">{{ pct }}%</span>
      </div>
      <div style="height:5px; background:rgba(255,255,255,.06); border-radius:3px; overflow:hidden">
        <div :style="`width:${pct}%; height:100%; background:${colorPct}; transition:width .4s ease; border-radius:3px`"></div>
      </div>
      <div class="row q-gutter-md q-mt-xs">
        <div v-for="h in miniStats" :key="h.label" class="text-center">
          <div class="font-mono" style="font-size:11px; font-weight:600" :style="`color:${h.color}`">{{ h.valor.toFixed(1) }}h</div>
          <div style="font-size:9px; color:#475569">{{ h.label }}</div>
        </div>
      </div>
    </div>

    <!-- Alerta médico -->
    <div v-if="sinMedico" class="est-card__alerta">
      <q-icon name="warning" color="negative" size="14px" />
      <span>Sin certificado médico vigente (RAC 67)</span>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  estudiante: { type: Object,  required: true },
  progreso:   { type: Object,  default: null  }, // datos de progreso RAC 61
  clickable:  { type: Boolean, default: false },
  avatarSize: { type: String,  default: '40px' },
  sinMedico:  { type: Boolean, default: false },
})
defineEmits(['click'])

const nombre   = computed(() =>
  `${props.estudiante.persona?.nombres || ''} ${props.estudiante.persona?.apellidos || ''}`.trim()
)
const iniciales = computed(() =>
  nombre.value.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
)

const cats = computed(() => {
  const c = props.progreso?.categorias || {}
  return Object.values(c).filter(v => (v.requerido || 0) > 0)
})

const pct = computed(() => {
  if (!cats.value.length) return 0
  const total = cats.value.reduce((a, c) => a + Math.min(c.porcentaje || 0, 100), 0)
  return Math.round(total / cats.value.length)
})

const colorPct = computed(() => pct.value >= 100 ? '#22c55e' : pct.value >= 60 ? '#f59e0b' : '#3b82f6')

const miniStats = computed(() => {
  const c = props.progreso?.categorias || {}
  return [
    { label: 'Total', valor: c.vuelo_total?.acumulado || 0, color: '#3b82f6' },
    { label: 'Dual',  valor: c.dual?.acumulado || 0,        color: '#14b8a6' },
    { label: 'Solo',  valor: c.solo?.acumulado || 0,        color: '#f59e0b' },
  ]
})
</script>

<style scoped>
.est-card {
  background: var(--bg2, #0f1218);
  border: 1px solid rgba(255,255,255,.07);
  border-radius: 10px;
  padding: 14px 16px;
  transition: border-color .2s, transform .15s;
}
.est-card--clickable { cursor: pointer; }
.est-card--clickable:hover { border-color: rgba(255,255,255,.16); transform: translateY(-1px); }

.est-card__header { display:flex; align-items:center; gap:12px; }
.est-card__info   { flex:1; min-width:0; }
.est-card__nombre { font-size:14px; color:#e2e8f0; font-weight:600;
                    white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.est-card__meta   { display:flex; align-items:center; gap:6px; margin-top:3px; flex-wrap:wrap; }

.est-card__chip {
  font-family: 'JetBrains Mono', monospace;
  font-size:9px; color:#60a5fa;
  background:rgba(59,130,246,.1); border:1px solid rgba(59,130,246,.2);
  padding:1px 6px; border-radius:3px;
}
.est-card__estado {
  font-family: 'JetBrains Mono', monospace;
  font-size:9px; padding:1px 6px; border-radius:3px;
}
.est-estado--activo     { color:#22c55e; background:rgba(34,197,94,.1); }
.est-estado--suspendido { color:#f59e0b; background:rgba(245,158,11,.1); }
.est-estado--graduado   { color:#3b82f6; background:rgba(59,130,246,.1); }
.est-estado--retirado   { color:#64748b; background:rgba(100,116,139,.1); }

.est-card__alerta {
  display:flex; align-items:center; gap:6px; margin-top:10px;
  font-size:11px; color:#fca5a5;
  background:rgba(239,68,68,.07); border:1px solid rgba(239,68,68,.2);
  border-radius:6px; padding:6px 10px;
}
</style>
