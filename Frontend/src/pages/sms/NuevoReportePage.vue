<template>
  <q-page padding style="padding-bottom:80px">

    <div class="row items-center q-gutter-md q-mb-lg">
      <q-btn flat round dense icon="arrow_back" color="grey-5" @click="$router.push('/sms')" />
      <div>
        <div class="font-mono q-mb-xs" style="font-size:10px;color:#475569;letter-spacing:2px;text-transform:uppercase">
          OACI Anexo 19 · SMS
        </div>
        <div class="font-head text-white" style="font-size:20px;font-weight:700">Nuevo Reporte de Seguridad</div>
      </div>
    </div>

    <!-- Aviso de anonimato -->
    <q-banner dense rounded class="q-mb-md"
      style="background:rgba(96,165,250,.07);border:1px solid rgba(96,165,250,.2);border-radius:10px">
      <template #avatar><q-icon name="info" color="info" /></template>
      <span style="font-size:13px;color:#93c5fd">
        Puede reportar de forma anónima. La cultura de reporte sin represalias es fundamental para la seguridad operacional (OACI Anexo 19).
      </span>
    </q-banner>

    <q-form @submit.prevent="enviar">
      <div class="row q-col-gutter-md">

        <!-- Formulario principal -->
        <div class="col-12 col-md-7">
          <q-card flat class="card-rac q-mb-md" style="background:#0f1218">
            <q-card-section>
              <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">DETALLES DEL EVENTO</div>
              <div class="q-gutter-md">

                <!-- Anonimato -->
                <div class="row items-center justify-between"
                  style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:8px;padding:12px 16px">
                  <div>
                    <div style="font-size:13px;color:#e2e8f0;font-weight:500">Reporte anónimo</div>
                    <div style="font-size:11px;color:#64748b">Su identidad no quedará registrada</div>
                  </div>
                  <q-toggle v-model="form.anonimo" color="primary" dark />
                </div>

                <q-select v-model="form.tipo" outlined dark bg-color="grey-10"
                  :options="opcionesTipo" emit-value map-options
                  label="Tipo de evento" stack-label :rules="[v=>!!v||'Requerido']">
                  <template #prepend>
                    <q-icon :name="iconTipo" :color="colorTipo" size="18px" />
                  </template>
                </q-select>

                <div class="row q-col-gutter-sm">
                  <div class="col-7">
                    <q-input v-model="form.fecha_evento" type="datetime-local" outlined dark bg-color="grey-10"
                      label="Fecha y hora del evento" stack-label :rules="[v=>!!v||'Requerido']" />
                  </div>
                  <div class="col-5">
                    <q-select v-model="form.aeronave_id" outlined dark bg-color="grey-10"
                      :options="opcionesAeronaves" option-value="id" option-label="label"
                      emit-value map-options label="Aeronave" stack-label clearable />
                  </div>
                </div>

                <q-input v-model="form.lugar" outlined dark bg-color="grey-10"
                  label="Lugar del evento" stack-label placeholder="Pista 23, Torre de control, Sala de clases…"
                  :rules="[v=>!!v||'Requerido']" />

                <q-input v-model="form.descripcion" type="textarea" outlined dark bg-color="grey-10"
                  label="Descripción detallada" stack-label rows="5"
                  placeholder="Describa qué ocurrió, las condiciones, factores contribuyentes y consecuencias…"
                  :rules="[v=>v&&v.length>=20||'Mínimo 20 caracteres']" counter maxlength="2000" />

              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Matriz de riesgo -->
        <div class="col-12 col-md-5">
          <q-card flat class="card-rac" style="background:#0f1218;position:sticky;top:16px">
            <q-card-section>
              <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">
                EVALUACIÓN DE RIESGO OACI
              </div>

              <!-- Severidad -->
              <div class="q-mb-lg">
                <div style="font-size:13px;color:#e2e8f0;font-weight:500;margin-bottom:8px">
                  Severidad del daño potencial
                </div>
                <div class="row q-col-gutter-xs">
                  <div v-for="s in 5" :key="s" class="col">
                    <q-btn flat dense class="full-width"
                      :style="`border-radius:6px;border:1px solid ${form.severidad===s?'rgba(59,130,246,.6)':'rgba(255,255,255,.07)'};
                        background:${form.severidad===s?'rgba(59,130,246,.15)':'transparent'};
                        color:${form.severidad===s?'#60a5fa':'#64748b'};
                        padding:8px 4px;font-family:monospace;font-size:13px;font-weight:700`"
                      @click="form.severidad=s" :label="s.toString()" />
                  </div>
                </div>
                <div class="row justify-between q-mt-xs font-mono" style="font-size:9px;color:#475569">
                  <span>Insignificante</span><span>Catastrófico</span>
                </div>
              </div>

              <!-- Probabilidad -->
              <div class="q-mb-lg">
                <div style="font-size:13px;color:#e2e8f0;font-weight:500;margin-bottom:8px">
                  Probabilidad de ocurrencia
                </div>
                <div class="row q-col-gutter-xs">
                  <div v-for="p in 5" :key="p" class="col">
                    <q-btn flat dense class="full-width"
                      :style="`border-radius:6px;border:1px solid ${form.probabilidad===p?'rgba(20,184,166,.6)':'rgba(255,255,255,.07)'};
                        background:${form.probabilidad===p?'rgba(20,184,166,.12)':'transparent'};
                        color:${form.probabilidad===p?'#2dd4bf':'#64748b'};
                        padding:8px 4px;font-family:monospace;font-size:13px;font-weight:700`"
                      @click="form.probabilidad=p" :label="p.toString()" />
                  </div>
                </div>
                <div class="row justify-between q-mt-xs font-mono" style="font-size:9px;color:#475569">
                  <span>Improbable</span><span>Frecuente</span>
                </div>
              </div>

              <!-- Resultado de riesgo -->
              <div v-if="nivelRiesgo > 0"
                style="border-radius:10px;padding:16px;text-align:center;border:1px solid"
                :style="`background:${bgResultado};border-color:${borderResultado}`">
                <div class="font-mono" style="font-size:11px;letter-spacing:1px;margin-bottom:4px"
                  :style="`color:${colorResultado}`">NIVEL DE RIESGO</div>
                <div class="font-head" style="font-size:36px;font-weight:800"
                  :style="`color:${colorResultado}`">{{ nivelRiesgo }}</div>
                <div class="font-mono" style="font-size:12px;font-weight:600;letter-spacing:1px"
                  :style="`color:${colorResultado}`">{{ clasificacionRiesgo.toUpperCase() }}</div>
                <div v-if="nivelRiesgo >= 15" style="font-size:11px;color:#fca5a5;margin-top:6px">
                  ⚠️ Requiere notificación a UAEAC
                </div>
              </div>

              <div v-else style="border-radius:10px;padding:16px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07)">
                <div style="font-size:12px;color:#475569">Seleccione severidad y probabilidad</div>
              </div>

            </q-card-section>

            <q-card-section style="border-top:1px solid rgba(255,255,255,.07)">
              <q-btn unelevated color="positive" no-caps label="Enviar reporte" icon="send"
                type="submit" :loading="enviando" class="full-width"
                :disable="!formValido" style="border-radius:8px;height:44px" />
            </q-card-section>
          </q-card>
        </div>

      </div>
    </q-form>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const router  = useRouter()
const $q      = useQuasar()
const enviando = ref(false)
const aeronaves = ref([])

const form = ref({
  anonimo: false, tipo: null, fecha_evento: '', lugar: '',
  aeronave_id: null, descripcion: '', severidad: 0, probabilidad: 0,
})

const opcionesTipo = [
  { label: '⚠️ Peligro identificado', value: 'peligro' },
  { label: '📋 Incidente',           value: 'incidente' },
  { label: '🚨 Accidente',           value: 'accidente' },
  { label: '😰 Near Miss',           value: 'near_miss' },
]

const opcionesAeronaves = computed(() =>
  aeronaves.value.map(a => ({ id: a.id, label: `${a.matricula} · ${a.modelo}` }))
)

const nivelRiesgo = computed(() =>
  form.value.severidad * form.value.probabilidad
)

const clasificacionRiesgo = computed(() => {
  const n = nivelRiesgo.value
  if (n >= 10) return 'Inaceptable'
  if (n >= 5)  return 'Tolerable'
  if (n >= 1)  return 'Aceptable'
  return ''
})

const colorResultado = computed(() => {
  const n = nivelRiesgo.value
  if (n >= 10) return '#ef4444'
  if (n >= 5)  return '#f59e0b'
  return '#22c55e'
})

const bgResultado = computed(() => {
  const n = nivelRiesgo.value
  if (n >= 10) return 'rgba(239,68,68,.08)'
  if (n >= 5)  return 'rgba(245,158,11,.07)'
  return 'rgba(34,197,94,.07)'
})

const borderResultado = computed(() => {
  const n = nivelRiesgo.value
  if (n >= 10) return 'rgba(239,68,68,.3)'
  if (n >= 5)  return 'rgba(245,158,11,.25)'
  return 'rgba(34,197,94,.2)'
})

const iconTipo  = computed(() => ({ peligro:'warning', incidente:'report', accidente:'emergency', near_miss:'visibility' }[form.value.tipo] || 'flag'))
const colorTipo = computed(() => ({ peligro:'warning', incidente:'orange', accidente:'negative', near_miss:'purple' }[form.value.tipo] || 'grey-5'))

const formValido = computed(() =>
  form.value.tipo &&
  form.value.fecha_evento &&
  form.value.lugar &&
  form.value.descripcion?.length >= 20 &&
  form.value.severidad > 0 &&
  form.value.probabilidad > 0
)

async function enviar() {
  enviando.value = true
  try {
    const { data } = await api.post('/sms/reportes', form.value)
    $q.notify({
      type: 'positive',
      message: `Reporte enviado. Nivel de riesgo: ${data.nivel?.clasificacion || ''}`,
      timeout: 5000,
    })
    router.push('/sms')
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Error al enviar el reporte.' })
  } finally { enviando.value = false }
}

onMounted(async () => {
  const { data } = await api.get('/aeronaves')
  aeronaves.value = data.data || []
})
</script>
