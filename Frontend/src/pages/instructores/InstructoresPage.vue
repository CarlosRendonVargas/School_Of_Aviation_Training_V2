<template>
  <q-page padding style="padding-bottom:80px">
    <div class="row items-center justify-between q-mb-lg">
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">RAC 141.35 · RAC 65</div>
        <div class="font-head text-white" style="font-size:20px; font-weight:700">Instructores</div>
      </div>
      <q-btn v-if="puedeCrear" unelevated color="teal" icon="person_add" label="Nuevo" no-caps
        @click="dialogNuevo = true" style="border-radius:8px" />
    </div>

    <!-- Filtro rápido -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-12 col-sm-5">
        <q-input v-model="buscar" outlined dense dark bg-color="grey-10" placeholder="Buscar por nombre o licencia…" clearable debounce="400">
          <template #prepend><q-icon name="search" color="grey-6" size="18px" /></template>
        </q-input>
      </div>
      <div class="col-6 col-sm-3">
        <q-select v-model="filtroLicencia" outlined dense dark bg-color="grey-10"
          :options="tiposLicencia" emit-value map-options clearable label="Tipo licencia" stack-label />
      </div>
      <div class="col-6 col-sm-2">
        <q-toggle v-model="soloActivos" color="teal" label="Solo activos" dark style="margin-top:8px" />
      </div>
    </div>

    <!-- Grid de instructores -->
    <div class="row q-col-gutter-md">
      <div v-if="cargando" v-for="n in 6" :key="n" class="col-12 col-sm-6 col-md-4">
        <q-skeleton type="rect" height="150px" dark style="border-radius:12px" />
      </div>

      <div v-for="inst in instructores" :key="inst.id" class="col-12 col-sm-6 col-md-4">
        <q-card flat class="card-rac" style="background:#0f1218"
          :style="!licenciaOk(inst) ? 'border-color:rgba(239,68,68,.35)' : ''">
          <q-card-section style="padding:16px">
            <div class="row items-center q-gutter-sm q-mb-sm">
              <q-avatar size="44px" color="teal" text-color="white" style="font-size:15px; font-weight:700">
                {{ iniciales(inst) }}
              </q-avatar>
              <div class="col">
                <div class="text-white" style="font-size:14px; font-weight:600; line-height:1.2">
                  {{ inst.persona?.nombres }} {{ inst.persona?.apellidos }}
                </div>
                <div class="font-mono" style="font-size:10px; color:#475569">{{ inst.num_licencia }}</div>
              </div>
            </div>

            <div class="row q-col-gutter-xs q-mb-sm">
              <div class="col-6">
                <div style="font-size:10px; color:#475569">Tipo licencia</div>
                <div class="font-mono text-teal" style="font-size:12px">{{ inst.tipo_licencia }}</div>
              </div>
              <div class="col-6">
                <div style="font-size:10px; color:#475569">Horas P.I.C.</div>
                <div class="font-mono text-primary" style="font-size:12px">{{ Number(inst.horas_totales_pic || 0).toFixed(0) }}h</div>
              </div>
            </div>

            <div class="row items-center justify-between">
              <div class="font-mono" style="font-size:11px"
                :style="`color:${licenciaOk(inst) ? '#22c55e' : '#ef4444'}`">
                <q-icon :name="licenciaOk(inst) ? 'check_circle' : 'warning'" size="14px" />
                Lic. {{ inst.venc_licencia }}
              </div>
              <q-chip dense :color="inst.activo ? 'positive' : 'grey'"
                :label="inst.activo ? 'ACTIVO' : 'INACTIVO'" text-color="white" style="font-size:9px" />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="!cargando && !instructores.length" class="col-12 text-center q-py-xl" style="color:#475569">
        <q-icon name="badge" size="48px" class="q-mb-sm" /><br>Sin instructores
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'
import dayjs from 'dayjs'

const authStore = useAuthStore()
const instructores   = ref([])
const cargando       = ref(false)
const dialogNuevo    = ref(false)
const buscar         = ref('')
const filtroLicencia = ref(null)
const soloActivos    = ref(true)

const puedeCrear = ['admin', 'dir_ops'].includes(authStore.rol)
const tiposLicencia = [
  { label: 'CPL', value: 'CPL' }, { label: 'ATPL', value: 'ATPL' }, { label: 'CFI', value: 'CFI' },
]

const iniciales    = (i) => ((i.persona?.nombres?.[0] || '') + (i.persona?.apellidos?.[0] || '')).toUpperCase()
const licenciaOk   = (i) => dayjs(i.venc_licencia).isAfter(dayjs().add(30, 'day'))

async function cargar() {
  cargando.value = true
  try {
    const params = {}
    if (buscar.value)        params.buscar        = buscar.value
    if (filtroLicencia.value) params.tipo_licencia = filtroLicencia.value
    if (soloActivos.value)   params.activo        = 1
    const { data } = await api.get('/instructores', { params })
    instructores.value = data.data?.data || data.data || []
  } finally { cargando.value = false }
}

watch([buscar, filtroLicencia, soloActivos], () => cargar())
onMounted(cargar)
</script>
