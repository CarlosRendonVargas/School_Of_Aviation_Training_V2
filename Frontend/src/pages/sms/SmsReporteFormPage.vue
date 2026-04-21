<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="privacy_tip" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">SISTEMA DE GESTIÓN · OACI ANEXO 19</div>
          <h1 class="rac-page-title">Nuevo Reporte SMS</h1>
        </div>
      </div>
      <q-btn flat round dense icon="arrow_back" color="white" to="/sms" />
    </div>

    <!-- Banner cultura seguridad -->
    <div class="cultura-banner q-mb-lg">
      <q-icon name="shield" size="22px" color="green-4" />
      <div>
        <div class="font-head text-weight-bold" style="font-size:14px">Reporte sin represalias</div>
        <div class="font-mono text-grey-5" style="font-size:11px; margin-top:2px">
          El SMS tiene como objetivo mejorar la seguridad, no sancionar. Puedes reportar de forma anónima.
        </div>
      </div>
    </div>

    <q-form @submit.prevent="enviar" greedy>
      <div class="row q-col-gutter-xl">

        <!-- Col 1 -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-100">
            <div class="form-section-title q-mb-lg">Información del evento</div>

          <!-- Anonimato -->
          <div class="anonimo-row q-mb-md">
            <q-toggle v-model="form.anonimo" color="green" />
            <div>
              <div style="font-size:13px;font-weight:600">
                {{ form.anonimo ? '🔒 Reporte anónimo' : '👤 Reporte identificado' }}
              </div>
              <div style="font-size:11px;color:rgba(255,255,255,.4)">
                {{ form.anonimo ? 'Tu nombre no quedará registrado en el sistema' : 'Tu nombre quedará visible para el SMS Officer' }}
              </div>
            </div>
          </div>

          <q-select
            v-model="form.tipo"
            :options="tiposEvento"
            option-value="value" option-label="label"
            emit-value map-options
            label="Tipo de evento *"
            outlined dense dark class="q-mb-sm"
            :rules="[v => !!v || 'Requerido']"
          >
            <template #option="{ itemProps, opt }">
              <q-item v-bind="itemProps">
                <q-item-section avatar>
                  <q-icon :name="opt.icon" :color="opt.color" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ opt.label }}</q-item-label>
                  <q-item-label caption>{{ opt.desc }}</q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-select>

          <q-input
            v-model="form.fecha_evento"
            label="Fecha y hora del evento *"
            type="datetime-local"
            outlined dense dark class="q-mb-sm"
            :max="ahoraStr"
            :rules="[v => !!v || 'Requerido']"
          />

          <q-input
            v-model="form.lugar"
            label="Lugar del evento *"
            placeholder="Ej: Pista 01L SKBO, Aula de clases, Hangar..."
            outlined dense dark class="q-mb-sm"
            :rules="[v => !!v || 'Requerido']"
          />

          <q-select
            v-model="form.aeronave_id"
            :options="aeronavesOpts"
            option-value="value" option-label="label"
            emit-value map-options
            label="Aeronave involucrada (opcional)"
            outlined dense dark class="q-mb-sm"
            clearable
          />

          <q-input
            v-model="form.descripcion"
            type="textarea"
            label="Descripción del evento *"
            placeholder="Describe detalladamente lo que ocurrió, condiciones, factores contribuyentes..."
            outlined dense dark
            rows="6"
            :rules="[v => v?.length >= 20 || 'Mínimo 20 caracteres']"
          />
          </q-card>
        </div>

        <!-- Col 2: Matriz de riesgo -->
        <div class="col-12 col-md-6">
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-100">
            <div class="form-section-title q-mb-lg">
              Evaluación de riesgo
              <span class="text-weight-regular" style="font-size:10px; margin-left:4px; letter-spacing:0">OACI Matriz 5×5</span>
            </div>

          <!-- Severidad -->
          <div class="riesgo-label q-mb-xs">Severidad del evento</div>
          <div class="riesgo-opciones q-mb-md">
            <div
              v-for="s in nivelesSeveridad"
              :key="s.val"
              class="riesgo-opt"
              :class="{ selected: form.severidad === s.val, [`color-${s.color}`]: true }"
              @click="form.severidad = s.val"
            >
              <div class="riesgo-num">{{ s.val }}</div>
              <div class="riesgo-txt">{{ s.label }}</div>
            </div>
          </div>

          <!-- Probabilidad -->
          <div class="riesgo-label q-mb-xs">Probabilidad de ocurrencia</div>
          <div class="riesgo-opciones q-mb-lg">
            <div
              v-for="p in nivelesProbabilidad"
              :key="p.val"
              class="riesgo-opt"
              :class="{ selected: form.probabilidad === p.val, [`color-${p.color}`]: true }"
              @click="form.probabilidad = p.val"
            >
              <div class="riesgo-num">{{ p.val }}</div>
              <div class="riesgo-txt">{{ p.label }}</div>
            </div>
          </div>

          <!-- Resultado de la matriz -->
          <div class="matriz-resultado" :class="clasificacionRiesgo.color">
            <div class="resultado-val">{{ nivelRiesgo }}</div>
            <div class="resultado-clasificacion">{{ clasificacionRiesgo.label }}</div>
            <div class="resultado-desc">{{ clasificacionRiesgo.desc }}</div>
          </div>

          <!-- Notificar UAEAC -->
          <div v-if="form.tipo === 'accidente' || nivelRiesgo >= 15" class="alerta-uaeac q-mt-md">
            <q-icon name="warning" color="amber-5" size="18px" />
            <div style="font-size:12px">
              <strong>Este evento debe notificarse a la UAEAC</strong>
              (nivel de riesgo {{ nivelRiesgo }}/25 o tipo accidente — OACI Anexo 13)
            </div>
          </div>

          </q-card>
        </div>
      </div>

      <!-- Acciones -->
      <div class="row justify-end q-mt-xl q-gutter-x-md">
        <q-btn flat label="Cancelar" to="/sms" color="grey-5" no-caps class="font-mono" />
        <q-btn
          type="submit"
          label="Enviar Reporte de Seguridad"
          icon="send"
          color="red-9"
          unelevated
          class="premium-btn shadow-24 q-px-xl q-py-md"
          :loading="enviando"
        />
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

const now = new Date()
const ahoraStr = now.toISOString().slice(0, 16)

const form = ref({
  anonimo:     false,
  tipo:        null,
  fecha_evento: ahoraStr,
  lugar:       '',
  aeronave_id: null,
  descripcion: '',
  severidad:   3,
  probabilidad:3,
})

const tiposEvento = [
  { value: 'peligro',   label: 'Peligro',          icon: 'warning',      color: 'amber-5',  desc: 'Condición que puede causar daño' },
  { value: 'incidente', label: 'Incidente',         icon: 'error_outline',color: 'orange-5', desc: 'Evento que afecta seguridad sin accidente' },
  { value: 'accidente', label: 'Accidente',         icon: 'dangerous',    color: 'red-5',    desc: 'Evento con daños / lesiones' },
  { value: 'near_miss', label: 'Near Miss / Cuasi-accidente', icon: 'report', color: 'yellow-7', desc: 'Casi accidente sin consecuencias' },
]

const nivelesSeveridad = [
  { val: 1, label: 'Insignificante', color: 'green' },
  { val: 2, label: 'Menor',         color: 'teal' },
  { val: 3, label: 'Moderado',      color: 'amber' },
  { val: 4, label: 'Mayor',         color: 'orange' },
  { val: 5, label: 'Catastrófico',  color: 'red' },
]
const nivelesProbabilidad = [
  { val: 1, label: 'Improbable',    color: 'green' },
  { val: 2, label: 'Poco probable', color: 'teal' },
  { val: 3, label: 'Posible',       color: 'amber' },
  { val: 4, label: 'Probable',      color: 'orange' },
  { val: 5, label: 'Frecuente',     color: 'red' },
]

const nivelRiesgo = computed(() => form.value.severidad * form.value.probabilidad)

const clasificacionRiesgo = computed(() => {
  const n = nivelRiesgo.value
  if (n <= 4)  return { label: 'ACEPTABLE',    color: 'verde',    desc: 'Monitorear — sin acción inmediata requerida' }
  if (n <= 9)  return { label: 'TOLERABLE',    color: 'amarillo', desc: 'Revisar y aplicar controles de mitigación' }
  return              { label: 'INACEPTABLE',  color: 'rojo',     desc: 'Acción correctiva inmediata requerida' }
})

const aeronavesOpts = ref([])

async function enviar() {
  enviando.value = true
  try {
    const payload = {
      ...form.value,
      fecha_evento: new Date(form.value.fecha_evento).toISOString().replace('T', ' ').slice(0, 19),
    }
    const { data } = await api.post('/sms/reportes', payload)
    $q.notify({ type: 'positive', message: `Reporte SMS registrado. Nivel de riesgo: ${data.nivel?.clasificacion}.` })
    router.push('/sms')
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.mensaje || 'Error al enviar el reporte.' })
  } finally {
    enviando.value = false
  }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/aeronaves')
    aeronavesOpts.value = data.data.map(a => ({ value: a.id, label: `${a.matricula} · ${a.modelo}` }))
  } catch {}
})
</script>

<style lang="scss" scoped>
.cultura-banner {
  background: rgba(34,197,94,.06); border: 1px solid rgba(34,197,94,.15);
  border-radius: 10px; padding: 14px 16px;
  display: flex; align-items: flex-start; gap: 12px;
}
.anonimo-row {
  display: flex; align-items: center; gap: 12px;
  background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07);
  border-radius: 10px; padding: 12px;
}

.pb-sm { padding-bottom: 8px; }
.h-100 { height: 100%; }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }

.riesgo-label { font-size: 12px; color: rgba(255,255,255,.6); font-weight: 600; font-family: 'Syne', sans-serif;}
.riesgo-opciones { display: flex; gap: 8px; flex-wrap: wrap; }
.riesgo-opt {
  flex: 1; min-width: 60px; border: 1px solid rgba(255,255,255,.1);
  border-radius: 8px; padding: 10px 6px; text-align: center; cursor: pointer;
  transition: all .15s; background: rgba(255,255,255,.03);
  &:hover { border-color: rgba(255,255,255,.25); transform: translateY(-2px); }
  &.selected { border-width: 2px; }
  &.selected.color-green  { border-color: #22c55e; background: rgba(34,197,94,.1); }
  &.selected.color-teal   { border-color: #14b8a6; background: rgba(20,184,166,.1); }
  &.selected.color-amber  { border-color: #f59e0b; background: rgba(245,158,11,.1); }
  &.selected.color-orange { border-color: #f97316; background: rgba(249,115,22,.1); }
  &.selected.color-red    { border-color: #ef4444; background: rgba(239,68,68,.1); }
  .riesgo-num { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; }
  .riesgo-txt { font-size: 9px; color: rgba(255,255,255,.5); margin-top: 2px; font-family: 'JetBrains Mono', monospace;}
}

.matriz-resultado {
  border-radius: 12px; padding: 20px; text-align: center; margin-top: 10px;
  &.verde   { background: rgba(34,197,94,.08);  border: 2px solid rgba(34,197,94,.25); }
  &.amarillo{ background: rgba(245,158,11,.08); border: 2px solid rgba(245,158,11,.25); }
  &.rojo    { background: rgba(239,68,68,.08);  border: 2px solid rgba(239,68,68,.25); }

  .resultado-val           { font-family: 'Syne', sans-serif; font-size: 48px; font-weight: 800; line-height: 1; }
  .resultado-clasificacion { font-family: 'JetBrains Mono', monospace; font-size: 13px; letter-spacing: 2px; margin-top: 4px; }
  .resultado-desc          { font-size: 11px; color: rgba(255,255,255,.5); margin-top: 6px; }
}
.alerta-uaeac {
  background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.2);
  border-radius: 8px; padding: 12px; display: flex; gap: 10px; align-items: flex-start;
}
</style>
