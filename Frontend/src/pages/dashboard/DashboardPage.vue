<template>
  <q-page class="rac-page">

    <!-- Header de página -->
    <div class="page-header">
      <div>
        <div class="page-header-label">{{ saludoHora }},</div>
        <h1 class="page-header-title">{{ auth.nombre }}</h1>
        <div class="page-header-sub">
          <span class="badge-base badge-info-level">{{ rolLabel }}</span>
          <span class="q-ml-sm" style="font-size:12px;color:rgba(255,255,255,.4)">{{ fechaHoy }}</span>
        </div>
      </div>
      <q-btn
        v-if="auth.esDirOps || auth.esAdmin"
        label="Sincronizar alertas"
        icon="sync"
        flat
        dense
        color="blue-4"
        size="sm"
        @click="sincronizar"
        :loading="sincronizando"
      />
    </div>

    <!-- ── DASHBOARD ESTUDIANTE ─────────────────────────────────── -->
    <template v-if="auth.esEstudiante">
      <DashboardEstudiante />
    </template>

    <!-- ── DASHBOARD INSTRUCTOR ────────────────────────────────── -->
    <template v-else-if="auth.esInstructor">
      <DashboardInstructor />
    </template>

    <!-- ── DASHBOARD DIR. OPS ──────────────────────────────────── -->
    <template v-else-if="auth.esDirOps">
      <DashboardDirOps />
    </template>

    <!-- ── DASHBOARD MANTENIMIENTO ─────────────────────────────── -->
    <template v-else-if="auth.esMantenimiento">
      <DashboardMantenimiento />
    </template>

    <!-- ── DASHBOARD ADMIN ─────────────────────────────────────── -->
    <template v-else-if="auth.esAdmin">
      <DashboardAdmin />
    </template>

    <!-- ── DASHBOARD AUDITOR UAEAC ─────────────────────────────── -->
    <template v-else-if="auth.esAuditor">
      <DashboardAuditor />
    </template>

  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'
import { api } from 'boot/axios'
import dayjs from 'dayjs'
import 'dayjs/locale/es'
dayjs.locale('es')

import DashboardEstudiante   from 'components/dashboard/DashboardEstudiante.vue'
import DashboardInstructor   from 'components/dashboard/DashboardInstructor.vue'
import DashboardDirOps       from 'components/dashboard/DashboardDirOps.vue'
import DashboardMantenimiento from 'components/dashboard/DashboardMantenimiento.vue'
import DashboardAdmin        from 'components/dashboard/DashboardAdmin.vue'
import DashboardAuditor      from 'components/dashboard/DashboardAuditor.vue'

const $q     = useQuasar()
const auth   = useAuthStore()
const vStore = useVencimientosStore()

const sincronizando = ref(false)

const fechaHoy = computed(() => dayjs().format('dddd, D [de] MMMM YYYY'))

const saludoHora = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Buenos días'
  if (h < 18) return 'Buenas tardes'
  return 'Buenas noches'
})

const rolLabel = computed(() => ({
  estudiante:    'Estudiante',
  instructor:    'Instructor de Vuelo',
  admin:         'Administrativo',
  dir_ops:       'Director de Operaciones',
  mantenimiento: 'Mantenimiento',
  auditor_uaeac: 'Auditor UAEAC',
}[auth.rol] || auth.rol))

async function sincronizar() {
  sincronizando.value = true
  try {
    await api.post('/vencimientos/sincronizar')
    await vStore.cargar()
    $q.notify({ type: 'positive', message: 'Alertas sincronizadas correctamente.' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al sincronizar.' })
  } finally {
    sincronizando.value = false
  }
}
</script>

<style lang="scss" scoped>
.rac-page {
  padding: 24px;
  background: #0a0c10;
  min-height: 100vh;

  @media (max-width: 600px) { padding: 16px; }
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;

  .page-header-label {
    font-size: 13px;
    color: rgba(255,255,255,.4);
    margin-bottom: 2px;
  }
  .page-header-title {
    font-family: 'Syne', sans-serif;
    font-size: clamp(20px, 4vw, 28px);
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px 0;
  }
  .page-header-sub {
    display: flex;
    align-items: center;
    gap: 6px;
  }
}
</style>
