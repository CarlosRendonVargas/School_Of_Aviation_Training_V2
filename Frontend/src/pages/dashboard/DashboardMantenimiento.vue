<template>
  <div>
    <!-- Estado de la flota -->
    <div class="row q-col-gutter-md q-mb-md">
      <div class="col-12">
        <div class="font-mono q-mb-sm" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">
          Estado de la flota
        </div>
        <div class="row q-col-gutter-sm">
          <div v-for="av in (data || [])" :key="av.id" class="col-12 col-sm-6 col-md-4">
            <q-card flat class="card-rac" style="background:#0f1218; cursor:pointer"
              @click="$router.push(`/aeronaves/${av.id}`)">
              <q-card-section style="padding:14px 16px">
                <div class="row items-center justify-between q-mb-xs">
                  <div class="font-mono text-white" style="font-size:15px; font-weight:600">{{ av.matricula }}</div>
                  <q-chip dense
                    :color="av.estado === 'disponible' ? 'positive' : av.estado === 'mantenimiento' ? 'warning' : 'grey'"
                    :label="av.estado.toUpperCase()" text-color="white" style="font-size:9px" />
                </div>
                <div style="font-size:12px; color:#64748b; margin-bottom:10px">{{ av.modelo }}</div>
                <div class="row q-gutter-sm">
                  <div>
                    <div class="font-mono text-primary" style="font-size:13px">{{ av.horas_celula_total?.toFixed(0) }}h</div>
                    <div style="font-size:10px; color:#475569">TTAE</div>
                  </div>
                  <div v-if="av.mel_abiertos?.length">
                    <div class="font-mono text-negative" style="font-size:13px">{{ av.mel_abiertos.length }}</div>
                    <div style="font-size:10px; color:#475569">MEL</div>
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </div>

    <!-- Mantenimientos próximos -->
    <q-card flat class="card-rac" style="background:#0f1218">
      <q-card-section>
        <div class="font-head text-white q-mb-md" style="font-size:15px; font-weight:700">
          🔧 Mantenimientos próximos (30 días)
        </div>
        <div class="text-center q-py-md" style="color:#475569; font-size:13px" v-if="!proximosMx?.length">
          Sin mantenimientos próximos programados
        </div>
        <div v-for="mx in (proximosMx || [])" :key="mx.id"
          class="q-pa-sm q-mb-xs rounded-borders"
          style="background:rgba(249,115,22,.06); border:1px solid rgba(249,115,22,.15)">
          <div class="row items-center justify-between">
            <div>
              <div style="font-size:13px; color:#e2e8f0">{{ mx.aeronave?.matricula }} — {{ mx.tipo.replace('_', ' ').toUpperCase() }}</div>
              <div class="font-mono" style="font-size:11px; color:#94a3b8">{{ mx.proxima_fecha }}</div>
            </div>
            <q-btn flat dense no-caps :to="`/aeronaves/${mx.aeronave_id}`"
              color="grey-5" size="sm" icon="arrow_forward" />
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ data: { type: Object, default: null }, cargando: Boolean })
const proximosMx = computed(() => props.data?.mantenimientos_proximos || [])
</script>
