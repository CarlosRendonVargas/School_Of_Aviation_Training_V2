<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Control de Sistema ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="settings_suggest" size="48px" color="red-9" class="q-mr-md glow-primary spin-slow" />
        <div>
          <div class="rac-page-subtitle">ADMINISTRACIÓN DE PARÁMETROS · PLATFORM CONTROL</div>
          <h1 class="rac-page-title">Centro de Configuración</h1>
        </div>
      </div>
      <q-btn unelevated color="red-10" label="Guardar Parámetros del Sistema" icon="save_as"
        class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder"
        @click="guardarConfig" :loading="guardando" />
    </div>

    <!-- ══ Banner de Contexto del Sistema ══ -->
    <q-card class="premium-glass-card q-pa-xl q-mb-xl border-red-left shadow-24 welcome-hero overflow-hidden rounded-20">
      <div class="hero-glow"></div>
      <div class="row items-center relative-position">
        <q-icon name="memory" color="red-9" size="48px" class="q-mr-xl glow-primary" />
        <div class="col">
          <div class="font-mono text-grey-5 uppercase tracking-widest q-mb-xs" style="font-size:9px">SISTEMA ACTIVO DESDE: 2024 · VERSIÓN PLATAFORMA: v2.5.1</div>
          <div class="text-h5 text-white font-head text-weight-bolder uppercase tracking-tighter">School of Aviation Training · Sistema de Gestión Aeronáutica</div>
          <div class="text-caption text-grey-6 font-mono q-mt-xs">Módulo: RAC 141 · Proveedor de Entrenamiento Certificado UAEAC · ISO 9001</div>
        </div>
        <div class="q-gutter-x-lg flex items-center">
          <div v-for="s in systemStats" :key="s.label" class="text-center">
            <div class="text-h5 font-mono text-weight-bolder" :style="`color:${s.color}`">{{ s.val }}</div>
            <div class="text-grey-7 font-mono uppercase" style="font-size:9px">{{ s.label }}</div>
          </div>
        </div>
      </div>
    </q-card>

    <!-- ══ Paneles de Configuración (Grid 2x2) ══ -->
    <div class="row q-col-gutter-xl">

      <!-- Panel 1: Experiencia Visual de la Plataforma -->
      <div class="col-12 col-md-6">
        <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-full">
          <div class="row items-center q-mb-xl border-bottom-border pb-md">
            <div class="config-icon-panel q-mr-lg">
              <q-icon name="palette" color="red-9" size="28px" />
            </div>
            <div>
              <div class="text-h6 font-head text-white text-weight-bolder uppercase tracking-tighter">Experiencia Visual</div>
              <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Rendering & interfaz de usuario</div>
            </div>
          </div>
          <div class="q-gutter-y-xl">
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Modo Alta Visibilidad</div>
                <div class="text-caption text-grey-7 font-mono">Aumenta el contraste de los instrumentos para condiciones de baja luminosidad.</div>
              </div>
              <q-toggle v-model="config.contraste_alto" color="red-9" dark />
            </div>
            <q-separator dark class="opacity-5" />
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Animaciones Avanzadas</div>
                <div class="text-caption text-grey-7 font-mono">Habilita transiciones cinemáticas y efectos de profundidad.</div>
              </div>
              <q-toggle v-model="config.animaciones" color="red-9" dark />
            </div>
            <q-separator dark class="opacity-5" />
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Glassmorphism Intensidad</div>
                <div class="text-caption text-grey-7 font-mono">Profundidad del efecto cristal en tarjetas y paneles.</div>
              </div>
              <q-btn-toggle v-model="config.glass_level" no-caps dense toggle-color="red-9" color="transparent" text-color="grey-5"
                :options="[ { label: 'LOW', value: 'low' }, { label: 'MED', value: 'med' }, { label: 'MAX', value: 'max' } ]"
                class="rounded-8 border-red-low font-mono text-weight-bolder"
              />
            </div>
          </div>
        </q-card>
      </div>

      <!-- Panel 2: Centro de Alertas y Notificaciones RAC -->
      <div class="col-12 col-md-6">
        <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-full">
          <div class="row items-center q-mb-xl border-bottom-border pb-md">
            <div class="config-icon-panel q-mr-lg">
              <q-icon name="notifications_active" color="red-9" size="28px" />
            </div>
            <div>
              <div class="text-h6 font-head text-white text-weight-bolder uppercase tracking-tighter">Alertas del Sistema</div>
              <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Radar de vencimientos y notificaciones RAC</div>
            </div>
          </div>
          <div class="q-gutter-y-xl">
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Alerta Temprana RAC (30 días)</div>
                <div class="text-caption text-grey-7 font-mono">Notificar vencimientos de licencias e instrumentaciones con 30 días de anticipación.</div>
              </div>
              <q-toggle v-model="config.alertas_vencimiento" color="red-9" dark />
            </div>
            <q-separator dark class="opacity-5" />
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Push SMS Operacional</div>
                <div class="text-caption text-grey-7 font-mono">Enviar alertas críticas de seguridad al teléfono del capitán de vuelo.</div>
              </div>
              <q-toggle v-model="config.alertas_sms" color="red-9" dark />
            </div>
            <q-separator dark class="opacity-5" />
            <div class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-grey-2 font-head text-weight-bold">Briefing Matutino (Email)</div>
                <div class="text-caption text-grey-7 font-mono">Enviar el resumen operacional diario de la flota a las 06:00 UTC.</div>
              </div>
              <q-toggle v-model="config.email_notify" color="red-9" dark />
            </div>
          </div>
        </q-card>
      </div>

      <!-- Panel 3: Parámetros Académicos y RAC -->
      <div class="col-12 col-md-6">
        <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-full">
          <div class="row items-center q-mb-xl border-bottom-border pb-md">
            <div class="config-icon-panel q-mr-lg">
              <q-icon name="rule" color="red-9" size="28px" />
            </div>
            <div>
              <div class="text-h6 font-head text-white text-weight-bolder uppercase tracking-tighter">Parámetros RAC</div>
              <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Límites operacionales y académicos</div>
            </div>
          </div>
          <div class="q-gutter-y-lg">
            <div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm" style="font-size:9px">Umbral de Alerta de Vencimiento (días)</div>
              <q-slider v-model="config.umbral_alerta" :min="15" :max="90" :step="15" color="red-9" dark label
                :label-value="`${config.umbral_alerta} días`" class="q-px-sm" />
              <div class="row justify-between text-grey-7 font-mono q-mt-xs" style="font-size:9px">
                <span>15 días</span><span>45 días</span><span>90 días</span>
              </div>
            </div>
            <q-separator dark class="opacity-5" />
            <div>
              <div class="text-caption text-grey-6 font-mono uppercase tracking-widest q-mb-sm" style="font-size:9px">Máx. Horas de Vuelo Diarias (RAC 61)</div>
              <q-input v-model.number="config.max_horas_diarias" type="number" filled dark class="premium-input-login" dense>
                <template #append><span class="font-mono text-grey-6">hrs/día</span></template>
              </q-input>
            </div>
          </div>
        </q-card>
      </div>

      <!-- Panel 4: Acciones de Mantenimiento del Sistema -->
      <div class="col-12 col-md-6">
        <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-low rounded-20 h-full">
          <div class="row items-center q-mb-xl border-bottom-border pb-md">
            <div class="config-icon-panel q-mr-lg">
              <q-icon name="build_circle" color="red-9" size="28px" />
            </div>
            <div>
              <div class="text-h6 font-head text-white text-weight-bolder uppercase tracking-tighter">Mantenimiento</div>
              <div class="text-caption text-grey-7 font-mono uppercase" style="font-size:9px">Utilidades del sistema y diagnóstico</div>
            </div>
          </div>
          <div class="q-gutter-y-md">
            <q-btn flat icon="sync" label="Sincronizar Radar de Vencimientos" color="grey-4" class="full-width font-mono text-weight-bold justify-start q-pa-md rounded-12 border-red-low hover-btn" @click="syncVencimientos" />
            <q-btn flat icon="storage" label="Exportar Base de Datos RAC" color="grey-4" class="full-width font-mono text-weight-bold justify-start q-pa-md rounded-12 border-red-low hover-btn" @click="exportDB" />
            <q-btn flat icon="policy" label="Generar Reporte de Auditoría SMS" color="grey-4" class="full-width font-mono text-weight-bold justify-start q-pa-md rounded-12 border-red-low hover-btn" @click="auditSms" />
            <q-btn flat icon="bug_report" label="Diagnóstico de Integración API" color="grey-4" class="full-width font-mono text-weight-bold justify-start q-pa-md rounded-12 border-red-low hover-btn" @click="diagnose" />
          </div>
        </q-card>
      </div>
    </div>

  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const guardando = ref(false)

const config = ref({
  contraste_alto: false,
  animaciones: true,
  glass_level: 'max',
  alertas_vencimiento: true,
  alertas_sms: true,
  email_notify: false,
  umbral_alerta: 30,
  max_horas_diarias: 8,
})

const systemStats = [
  { label: 'ESTADO', val: 'ONLINE', color: '#10b981' },
  { label: 'UPTIME', val: '99.8%', color: '#fff' },
  { label: 'API', val: 'SYNC', color: '#A10B13' },
]

const guardarConfig = () => {
  guardando.value = true
  setTimeout(() => {
    guardando.value = false
    $q.notify({ color: 'red-9', icon: 'verified', message: 'Parámetros del sistema guardados y sincronizados.', classes: 'font-mono' })
  }, 1200)
}

const syncVencimientos = () => $q.notify({ color: 'red-9', icon: 'sync', message: 'Sincronizando radar de vencimientos...' })
const exportDB = () => $q.notify({ color: 'blue-9', icon: 'storage', message: 'Exportando registros RAC...' })
const auditSms = () => $q.notify({ color: 'orange-9', icon: 'policy', message: 'Generando reporte de auditoría SMS...' })
const diagnose = () => $q.notify({ color: 'grey-6', icon: 'bug_report', message: 'Ejecutando diagnóstico de API...' })
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.premium-glass-card { background: rgba(10, 12, 17, 0.7); backdrop-filter: blur(25px); border: 1px solid rgba(255,255,255,0.05); }
.border-red-low { border: 1px solid rgba(161, 11, 19, 0.2) !important; }
.border-red-left { border-left: 5px solid #A10B13 !important; }
.border-bottom-border { border-bottom: 1px solid rgba(255,255,255,0.05); }
.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }
.rounded-20 { border-radius: 20px; }
.rounded-12 { border-radius: 12px; }
.rounded-8 { border-radius: 8px; }
.h-full { height: 100%; }

.welcome-hero { position: relative; }
.hero-glow { position: absolute; top:0; right:0; bottom:0; left:0; background: radial-gradient(circle at 100% 0%, rgba(161, 11, 19, 0.15) 0%, transparent 50%); pointer-events: none; }
.glow-primary { filter: drop-shadow(0 0 15px rgba(161, 11, 19, 0.4)); }
.spin-slow { animation: spinSlow 12s linear infinite; }
@keyframes spinSlow { 100% { transform: rotate(360deg); } }

.config-icon-panel {
  width: 56px; height: 56px; border-radius: 16px;
  background: rgba(161, 11, 19, 0.1); border: 1px solid rgba(161, 11, 19, 0.25);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}

.hover-btn { transition: all 0.25s ease;
  &:hover { background: rgba(161, 11, 19, 0.08) !important; border-color: rgba(161, 11, 19, 0.4) !important; transform: translateX(4px); }
}

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.5) !important; }
  }
}
</style>
