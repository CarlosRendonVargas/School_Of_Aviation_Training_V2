<template>
  <q-page padding style="padding-bottom:80px" class="animate-fade">

    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono text-primary q-mb-xs uppercase tracking-widest" style="font-size:10px">
          RAC 141 · Sistema de alertas
        </div>
        <h1 class="font-head text-white text-h5 text-weight-bold q-my-none">Alertas de Vencimiento</h1>
      </div>
      <q-btn v-if="authStore.esDirOps || authStore.esAdmin" outline no-caps color="primary" icon="sync"
        label="Sincronizar" @click="sincronizar" :loading="sincronizando" class="premium-btn" />
    </div>

    <!-- ═══════ SUMMARY CARDS ═══════ -->
    <div class="row q-col-gutter-lg q-mb-xl">
      <div class="col-6 col-md-3">
        <q-card class="card-rac text-center q-pa-md border-rose">
          <div class="text-h3 font-head text-weight-bolder text-rose">{{ vencidos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase">VENCIDOS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="card-rac text-center q-pa-md border-amber">
          <div class="text-h3 font-head text-weight-bolder text-amber">{{ criticos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase">CRÍTICOS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="card-rac text-center q-pa-md">
          <div class="text-h3 font-head text-weight-bolder text-blue">{{ advertencias.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase">ADVERTENCIAS</div>
        </q-card>
      </div>
      <div class="col-6 col-md-3">
        <q-card class="card-rac text-center q-pa-md">
          <div class="text-h3 font-head text-weight-bolder text-emerald">{{ infos.length }}</div>
          <div class="text-caption text-grey-6 font-mono uppercase">INFORMATIVOS</div>
        </q-card>
      </div>
    </div>

    <!-- ═══════ FILTERS ═══════ -->
    <div class="row q-gutter-sm q-mb-lg">
      <q-btn v-for="tipo in tiposEntidad" :key="tipo.value"
        unelevated no-caps
        :color="filtroTipo === tipo.value ? 'primary' : 'transparent'"
        :class="filtroTipo === tipo.value ? '' : 'glass-btn'"
        :label="tipo.label"
        @click="filtroTipo = filtroTipo === tipo.value ? null : tipo.value"
        class="border-radius-12 q-px-md"
      />
    </div>

    <!-- ═══════ VENCIDOS SECTION ═══════ -->
    <div v-if="vencidosFiltrados.length" class="q-mb-xl">
      <div class="font-mono text-rose q-mb-md uppercase tracking-widest flex items-center" style="font-size:11px">
        <q-icon name="error" class="q-mr-sm" /> ✘ VENCIDOS — Requieren acción inmediata
      </div>
      <div v-for="v in vencidosFiltrados" :key="`v-${v.id}`"
        class="q-pa-md q-mb-sm card-rac border-rose animate-slide-right">
        <div class="row items-center q-gutter-md">
          <div class="kpi-icon-box bg-rose-transparent">
            <q-icon name="cancel" color="rose" size="24px" />
          </div>
          <div class="col">
            <div class="text-subtitle1 text-white text-weight-bold">{{ v.descripcion }}</div>
            <div class="font-mono text-grey-6 uppercase" style="font-size:10px">
              {{ v.tipo_entidad }} · <span class="text-rose">Venció: {{ v.fecha_vencimiento }}</span>
            </div>
          </div>
          <q-btn flat round icon="chevron_right" color="grey-7" />
        </div>
      </div>
    </div>

    <!-- ═══════ UPCOMING SECTION ═══════ -->
    <div class="font-mono text-grey-7 q-mb-md uppercase tracking-widest" style="font-size:11px">
      PRÓXIMOS A VENCER (AUDITORÍA RAC 141)
    </div>

    <q-list class="card-rac overflow-hidden separator-white">
      <!-- FIXED SKELETON: Using rect/text instead of invalid QItem -->
      <template v-if="cargando">
        <div v-for="n in 6" :key="n" class="q-pa-md border-bottom-border">
          <q-item>
             <q-item-section avatar><q-skeleton type="rect" width="44px" height="44px" dark class="border-radius-10" /></q-item-section>
             <q-item-section>
               <q-skeleton type="text" width="60%" dark />
               <q-skeleton type="text" width="40%" dark />
             </q-item-section>
          </q-item>
        </div>
      </template>

      <template v-else>
        <q-item v-for="v in proximosFiltrados" :key="v.id" class="q-py-md hover-bg-glass">
          <q-item-section avatar>
            <div class="day-badge" :style="`background: ${bgNivel(v.nivel_calculado)}`">
              <span class="font-mono text-white text-weight-bold">{{ v.dias_restantes }}</span>
              <span class="text-white opacity-50" style="font-size:8px">DÍAS</span>
            </div>
          </q-item-section>

          <q-item-section>
            <q-item-label class="text-white text-weight-bold">{{ v.descripcion }}</q-item-label>
            <q-item-label caption class="text-grey-6 font-mono text-uppercase" style="font-size:10px">
              {{ v.tipo_entidad }} · <q-icon name="event" size="12px" class="q-mr-xs" />{{ v.fecha_vencimiento }}
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <q-chip dense :color="colorNivel(v.nivel_calculado)" text-color="white"
              class="font-mono text-weight-bold" style="font-size:10px">
              {{ v.nivel_calculado.toUpperCase() }}
            </q-chip>
          </q-item-section>
        </q-item>

        <div v-if="!proximosFiltrados.length" class="q-pa-xl text-center">
          <q-icon name="verified_user" color="emerald" size="64px" class="opacity-20 q-mb-md" />
          <div class="text-h6 text-grey-7">Sin alertas pendientes</div>
        </div>
      </template>
    </q-list>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'
import { api } from 'boot/axios'

const $q        = useQuasar()
const authStore = useAuthStore()
const store     = useVencimientosStore()

const sincronizando = ref(false)
const filtroTipo    = ref(null)

const cargando   = computed(() => store.cargando)
const vencidos   = computed(() => store.vencidos)
const criticos   = computed(() => store.criticos)
const advertencias = computed(() => store.advertencias)
const infos      = computed(() => (store.items || []).filter(v => v.nivel_calculado === 'info'))

const tiposEntidad = [
  { label: 'Aeronaves',    value: 'aeronave'   },
  { label: 'Estudiantes',  value: 'estudiante' },
  { label: 'Instructores', value: 'instructor' },
  { label: 'Documentos',   value: 'documento'  },
]

const vencidosFiltrados = computed(() =>
  filtroTipo.value ? (vencidos.value || []).filter(v => v.tipo_entidad === filtroTipo.value) : (vencidos.value || [])
)

const proximosFiltrados = computed(() =>
  filtroTipo.value ? (store.items || []).filter(v => v.tipo_entidad === filtroTipo.value) : (store.items || [])
)

const bgNivel = (n) => ({
  vencido:      '#ef4444',
  critico:      '#f43f5e',
  advertencia:  '#f59e0b',
  info:         '#3b82f6',
}[n] || '#64748b')

const colorNivel = (n) => ({
  vencido: 'negative', critico: 'negative', advertencia: 'warning', info: 'info',
}[n] || 'grey')

async function sincronizar() {
  sincronizando.value = true
  try {
    const { data } = await api.post('/vencimientos/sincronizar')
    $q.notify({ 
      type: 'positive', 
      message: `Sincronizado: ${data.data.creados} nuevos, ${data.data.actualizados} actualizados.`,
      class: 'premium-glass-card'
    })
    store.cargar()
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error en sincronización' })
  } finally { sincronizando.value = false }
}

onMounted(() => store.cargar())
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.6s ease-out; }
.animate-slide-right { animation: slideRight 0.4s ease-out both; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideRight { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

.day-badge {
  width: 50px; height: 50px; border-radius: 12px;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.kpi-icon-box {
  width: 48px; height: 48px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
}
.bg-rose-transparent { background: rgba(244, 63, 94, 0.15); }

.border-bottom-border { border-bottom: 1px solid rgba(255,255,255,0.05); }
.hover-bg-glass:hover { background: rgba(255,255,255,0.02); }

.opacity-20 { opacity: 0.2; }
.tracking-widest { letter-spacing: 2px; }
</style>
