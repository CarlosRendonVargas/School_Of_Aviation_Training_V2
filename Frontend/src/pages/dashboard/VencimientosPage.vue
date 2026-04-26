<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado del Radar de Alertas ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="radar" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">SISTEMA INTEGRADO DE SEGURIDAD · RAC 141</div>
          <h1 class="rac-page-title">Radar de Vencimientos</h1>
        </div>
      </div>
      <q-btn 
        v-if="authStore.esDirOps || authStore.esAdmin" 
        color="red-9" icon="sync"
        label="Sincronizar Alertas" 
        class="premium-btn shadow-10" 
        @click="sincronizar" 
        :loading="sincronizando" 
      />
    </div>

    <!-- ════ SUMMARY CARDS: STATUS DE FLOTA Y PERSONAL ════ -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-6 col-md-3">
        <q-card class="premium-glass-card text-center q-pa-lg glow-red-border">
          <div class="text-h2 font-mono text-weight-bolder text-red-9 line-height-1">{{ vencidos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-sm">VENCIDOS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="premium-glass-card text-center q-pa-lg">
          <div class="text-h2 font-mono text-weight-bolder text-orange-9 line-height-1">{{ criticos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-sm">CRÍTICOS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="premium-glass-card text-center q-pa-lg">
          <div class="text-h2 font-mono text-weight-bolder text-amber-9 line-height-1">{{ advertencias.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-sm">ADVERTENCIAS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="premium-glass-card text-center q-pa-lg">
          <div class="text-h2 font-mono text-weight-bolder text-emerald line-height-1">{{ infos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mt-sm">OPERATIVOS</div>
        </q-card>
      </div>
    </div>

    <!-- ════ FILTROS TÁCTICOS ════ -->
    <div class="row q-gutter-sm q-mb-xl flex-wrap">
      <q-btn v-for="tipo in tiposEntidad" :key="tipo.value"
        unelevated no-caps
        :color="filtroTipo === tipo.value ? 'red-9' : 'transparent'"
        class="premium-glass-card q-px-lg font-mono text-weight-bold filter-btn"
        style="font-size: 11px"
        :label="tipo.label.toUpperCase()"
        @click="filtroTipo = filtroTipo === tipo.value ? null : tipo.value"
      />
    </div>

    <!-- ════ SECCIÓN: INOPERATIVOS (VENCIDOS) ════ -->
    <div v-if="vencidosFiltrados.length" class="q-mb-xl animate-slide-up">
      <div class="row items-center q-mb-md">
        <q-icon name="report_problem" color="red-9" size="20px" class="q-mr-sm" />
        <div class="text-subtitle1 font-head text-red-9 tracking-tighter uppercase text-weight-bolder">Atención Inmediata · Operación Suspendida</div>
      </div>
      
      <div v-for="v in vencidosFiltrados" :key="`v-${v.id}`"
        class="premium-glass-card q-pa-lg q-mb-sm border-red-left alert-vencido-item">
        <div class="row items-start q-gutter-md">
          <div class="kpi-icon-round bg-red-transparent flex-shrink-0">
            <q-icon name="block" color="red-9" size="22px" />
          </div>
          <div class="col">
            <div class="text-subtitle1 text-white font-head text-weight-bold">{{ v.descripcion }}</div>
            <div class="font-mono text-grey-6 uppercase flex items-center flex-wrap q-gutter-xs" style="font-size:10px">
              <span>{{ v.tipo_entidad }}</span>
              <q-icon name="fiber_manual_record" size="6px" />
              <span class="text-red-9 text-weight-bold">INOPERATIVO DESDE {{ formatFechaCO(v.fecha_vencimiento) }}</span>
            </div>
          </div>
          <q-btn flat round icon="more_vert" color="grey-7" class="flex-shrink-0" />
        </div>
      </div>
    </div>

    <!-- ════ SECCIÓN: PRÓXIMAS AUDITORÍAS (SCHEDULE) ════ -->
    <div class="row items-center q-mb-md">
      <q-icon name="history" color="grey-6" size="20px" class="q-mr-sm" />
      <div class="text-subtitle1 font-head text-grey-6 tracking-tighter uppercase">Cronograma de Cumplimiento RAC</div>
    </div>

    <q-card class="premium-glass-card overflow-hidden">
      <q-list separator dark>
        <template v-if="cargando">
          <div v-for="n in 5" :key="n" class="q-pa-lg">
            <q-skeleton type="text" width="60%" dark />
            <q-skeleton type="text" width="30%" dark />
          </div>
        </template>

        <template v-else>
          <q-item v-for="v in proximosFiltrados" :key="v.id" class="q-pa-md hover-row">
            <q-item-section avatar>
              <div class="days-indicator" :style="`border-top-color: ${bgNivel(v.nivel_calculado)}`">
                <div class="font-mono text-h6 text-weight-bolder text-white">{{ v.dias_restantes }}</div>
                <div class="text-grey-6 font-mono" style="font-size:8px">DÍAS</div>
              </div>
            </q-item-section>

            <q-item-section>
              <q-item-label class="text-subtitle1 text-grey-2 font-head text-weight-bold">{{ v.descripcion }}</q-item-label>
              <q-item-label caption class="text-grey-6 font-mono uppercase" style="font-size:10px">
                {{ v.tipo_entidad }} · {{ formatFechaCO(v.fecha_vencimiento) }}
              </q-item-label>
            </q-item-section>

            <q-item-section side class="flex-shrink-0">
              <q-badge outline :color="colorNivel(v.nivel_calculado)" class="font-mono text-weight-bold q-pa-xs">
                {{ v.nivel_calculado.toUpperCase() }}
              </q-badge>
            </q-item-section>
          </q-item>

          <div v-if="!proximosFiltrados.length" class="q-pa-xl text-center">
            <q-icon name="verified" color="emerald" size="64px" class="opacity-20 q-mb-md" />
            <div class="text-h6 text-grey-7 font-mono">STATUS OPERATIVO: CLEAR</div>
          </div>
        </template>
      </q-list>
    </q-card>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'
import { api } from 'boot/axios'
import { formatFechaCO } from 'src/utils/formatters'

const $q        = useQuasar()
const authStore = useAuthStore()
const store     = useVencimientosStore()

const sincronizando = ref(false)
const filtroTipo    = ref(null)

const cargando   = computed(() => store.cargando)
const vencidos   = computed(() => store.vencidos)
const items      = computed(() => store.items || [])
const criticos   = computed(() => items.value.filter(v => v.nivel_calculado === 'critico'))
const advertencias = computed(() => items.value.filter(v => v.nivel_calculado === 'advertencia'))
const infos      = computed(() => items.value.filter(v => v.nivel_calculado === 'info'))

const tiposEntidad = [
  { label: 'Aeronaves',    value: 'aeronave'   },
  { label: 'Estudiantes',  value: 'estudiante' },
  { label: 'Instructores', value: 'instructor' },
  { label: 'Documentos',   value: 'documento'  },
]

const vencidosFiltrados = computed(() => filtroTipo.value ? vencidos.value.filter(v => v.tipo_entidad === filtroTipo.value) : vencidos.value)
const proximosFiltrados = computed(() => filtroTipo.value ? items.value.filter(v => v.tipo_entidad === filtroTipo.value) : items.value)

const bgNivel = (n) => ({ vencido: '#A10B13', critico: '#ef4444', advertencia: '#f59e0b', info: '#10b981' }[n] || '#64748b')
const colorNivel = (n) => ({ vencido: 'red-9', critico: 'red-7', advertencia: 'orange-9', info: 'emerald' }[n] || 'grey')

async function sincronizar() {
  sincronizando.value = true
  try {
    const { data } = await api.post('/vencimientos/sincronizar')
    $q.notify({ color: 'red-9', icon: 'sync', message: 'Radar sincronizado correctamente.', class: 'premium-glass-card' })
    store.cargar()
  } catch (e) {
    $q.notify({ color: 'negative', message: 'Error en sincronización satelital.' })
  } finally { sincronizando.value = false }
}

onMounted(() => store.cargar())
</script>

<style lang="scss" scoped>

.animate-slide-up { animation: slideUp 0.6s ease-out; }

@keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

.glow-red-border { border: 1px solid rgba(161, 11, 19, 0.4) !important; box-shadow: 0 0 20px rgba(161, 11, 19, 0.1); }

.kpi-icon-round {
  width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center;
  justify-content: center; flex-shrink: 0;
  @media (max-width: 599px) { width: 36px; height: 36px; }
}
.bg-red-transparent { background: rgba(161, 11, 19, 0.1); }
.flex-shrink-0 { flex-shrink: 0; }

.days-indicator {
  width: 50px; height: 50px; border-radius: 10px; background: rgba(0,0,0,0.3);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  border-top: 3px solid transparent; border-right: 1px solid rgba(255,255,255,0.05);
  @media (max-width: 599px) { width: 40px; height: 40px; }
}

// Filter buttons wrap on mobile
.filter-btn {
  @media (max-width: 599px) { flex: 1 1 calc(50% - 8px); min-width: 0; }
}

.hover-row { transition: all 0.2s; &:hover { background: rgba(255,255,255,0.03); } }

.alert-vencido-item { transition: transform 0.2s; &:hover { transform: scale(1.005); } }
</style>
