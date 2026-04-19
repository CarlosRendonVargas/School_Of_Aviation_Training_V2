<template>
  <q-page padding style="padding-bottom:80px">

    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          RAC 61 · Mi Formación
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Mi Progreso</div>
      </div>
      <q-btn flat no-caps color="primary" icon="verified" label="Verificar examen"
        @click="verificarExamen" :loading="verificando" />
    </div>

    <q-skeleton v-if="cargando" v-for="n in 5" :key="n" type="rect" height="80px" dark class="q-mb-md" />

    <template v-else-if="progreso">

      <!-- Programa y expediente -->
      <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
        <q-card-section>
          <div class="row items-center q-gutter-md">
            <div class="col">
              <div class="font-mono" style="font-size:10px;color:#475569;letter-spacing:1px">EXPEDIENTE</div>
              <div class="font-mono text-primary" style="font-size:14px;font-weight:600">{{ progreso.num_expediente }}</div>
            </div>
            <div class="col">
              <div class="font-mono" style="font-size:10px;color:#475569;letter-spacing:1px">PROGRAMA</div>
              <div class="text-white" style="font-size:13px;font-weight:500">{{ progreso.programa }}</div>
            </div>
            <div class="col-auto">
              <q-badge v-if="progreso.listo_para_examen" color="positive" label="✔ Listo para examen UAEAC" style="font-size:11px;padding:6px 10px" />
              <q-badge v-else color="warning" label="En formación" style="font-size:11px;padding:6px 10px" />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Tarjetas de horas -->
      <div class="row q-col-gutter-sm q-mb-lg">
        <div class="col-4 col-md-2" v-for="h in resumenHoras" :key="h.label">
          <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:12px;text-align:center">
            <div class="font-head" :style="`font-size:22px;font-weight:800;color:${h.color}`">{{ h.valor }}</div>
            <div class="font-mono" style="font-size:9px;color:#475569;letter-spacing:.5px;margin-top:3px">{{ h.label }}</div>
          </div>
        </div>
      </div>

      <!-- Barras de progreso por categoría -->
      <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
        <q-card-section>
          <div class="font-mono q-mb-lg" style="font-size:10px;color:#475569;letter-spacing:2px">
            CUMPLIMIENTO RAC 61
          </div>
          <div v-for="cat in categorias" :key="cat.key" class="q-mb-xl">
            <div class="row items-center justify-between q-mb-sm">
              <div>
                <div style="font-size:14px;color:#e2e8f0;font-weight:500">{{ cat.label }}</div>
                <div class="font-mono" style="font-size:10px;color:#475569">{{ cat.rac }}</div>
              </div>
              <div class="text-right">
                <div class="font-head" :style="`font-size:20px;font-weight:800;color:${cat.cumplido?'#22c55e':'#3b82f6'}`">
                  {{ cat.acumulado?.toFixed(1) }}<span style="font-size:13px">h</span>
                </div>
                <div class="font-mono" style="font-size:11px;color:#475569">de {{ cat.requerido?.toFixed(1) }}h</div>
              </div>
            </div>
            <q-linear-progress
              :value="Math.min((cat.porcentaje||0)/100,1)"
              :color="cat.cumplido?'positive':cat.porcentaje>60?'warning':'primary'"
              rounded size="12px" class="q-mb-xs" />
            <div class="row items-center justify-between">
              <span v-if="cat.cumplido" style="font-size:12px;color:#22c55e">✔ Requisito RAC cumplido</span>
              <span v-else style="font-size:12px;color:#ef4444">Faltan {{ cat.faltante?.toFixed(1) }}h</span>
              <span class="font-mono" style="font-size:12px;color:#64748b">{{ (cat.porcentaje||0).toFixed(0) }}%</span>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Médico -->
      <q-card flat class="card-rac" :style="`background:#0f1218;border-color:${progreso.tiene_medico?'rgba(34,197,94,.2)':'rgba(239,68,68,.2)'}`">
        <q-card-section>
          <div class="row items-center q-gutter-md">
            <q-icon :name="progreso.tiene_medico?'verified':'cancel'"
              :color="progreso.tiene_medico?'positive':'negative'" size="32px" />
            <div class="col">
              <div style="font-size:14px;font-weight:600" :style="`color:${progreso.tiene_medico?'#22c55e':'#ef4444'}`">
                Certificado Médico Aeronáutico (RAC 67)
              </div>
              <div style="font-size:12px;color:#64748b">
                {{ progreso.tiene_medico ? 'Vigente — requerido para vuelo y examen' : 'Vencido o no registrado — obligatorio para continuar' }}
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

    </template>

    <!-- Dialog: Resultado de verificación -->
    <q-dialog v-model="dialogVerificacion">
      <q-card dark style="background:#0f1218;border:1px solid rgba(255,255,255,.1);border-radius:14px;min-width:340px;max-width:500px;width:100%">
        <q-card-section style="border-bottom:1px solid rgba(255,255,255,.07)">
          <div class="row items-center justify-between">
            <div class="font-head text-white" style="font-size:17px;font-weight:700">Verificación Examen UAEAC</div>
            <q-btn flat round dense icon="close" color="grey-5" @click="dialogVerificacion=false" />
          </div>
        </q-card-section>
        <q-card-section class="q-pa-lg" v-if="resultadoVerificacion">
          <div class="text-center q-mb-lg">
            <q-icon :name="resultadoVerificacion.aprobado?'verified':'pending_actions'"
              :color="resultadoVerificacion.aprobado?'positive':'warning'" size="48px" />
            <div class="font-head q-mt-sm" :style="`font-size:16px;font-weight:700;color:${resultadoVerificacion.aprobado?'#22c55e':'#f59e0b'}`">
              {{ resultadoVerificacion.aprobado ? '¡Requisitos cumplidos!' : 'Requisitos pendientes' }}
            </div>
          </div>
          <div v-for="v in resultadoVerificacion.validaciones" :key="v.criterio" class="q-mb-sm">
            <div class="row items-start q-gutter-sm">
              <q-icon :name="v.cumplido?'check_circle':'cancel'"
                :color="v.cumplido?'positive':'negative'" size="18px" style="margin-top:2px" />
              <div class="col" style="font-size:12.5px" :style="`color:${v.cumplido?'#86efac':'#fca5a5'}`">
                {{ v.mensaje }}
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useAuthStore } from 'store/auth'

const authStore = useAuthStore()
const progreso  = ref(null)
const cargando  = ref(false)
const verificando = ref(false)
const dialogVerificacion = ref(false)
const resultadoVerificacion = ref(null)

const categorias = computed(() => {
  const cats = progreso.value?.categorias || {}
  return Object.entries(cats).filter(([,v]) => (v.requerido||0) > 0).map(([k, v]) => ({ key: k, ...v }))
})

const resumenHoras = computed(() => {
  const cats = progreso.value?.categorias || {}
  return [
    { label: 'TOTAL',  valor: (cats.vuelo_total?.acumulado||0).toFixed(1), color: '#3b82f6' },
    { label: 'DUAL',   valor: (cats.dual?.acumulado||0).toFixed(1),         color: '#14b8a6' },
    { label: 'SOLO',   valor: (cats.solo?.acumulado||0).toFixed(1),         color: '#8b5cf6' },
    { label: 'NOCHE',  valor: (cats.noche?.acumulado||0).toFixed(1),        color: '#f97316' },
    { label: 'IFR',    valor: (cats.ifr?.acumulado||0).toFixed(1),          color: '#f59e0b' },
    { label: 'VUELOS', valor: progreso.value?.total_vuelos || 0,            color: '#22c55e' },
  ]
})

async function cargar() {
  cargando.value = true
  const estudiante = authStore.usuario
  try {
    // Obtener ID del estudiante desde /auth/me que carga el expediente
    const { data } = await api.get('/dashboard')
    progreso.value = data.data?.progreso_horas
  } finally { cargando.value = false }
}

async function verificarExamen() {
  verificando.value = true
  try {
    // Necesita el ID del estudiante — lo obtenemos del store
    const estudianteId = authStore.usuario?.id
    const { data } = await api.get(`/estudiantes/${estudianteId}/validar-examen`)
    resultadoVerificacion.value = data.data
    dialogVerificacion.value = true
  } finally { verificando.value = false }
}

onMounted(cargar)
</script>
