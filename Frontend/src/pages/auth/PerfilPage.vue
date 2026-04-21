<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom:100px">

    <!-- ══ Encabezado de Identidad Aeronáutica ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="badge" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
        <div>
          <div class="rac-page-subtitle">SISTEMA DE IDENTIFICACIÓN AERONÁUTICA · RAC</div>
          <h1 class="rac-page-title">Credencial Operativa</h1>
        </div>
      </div>
    </div>

    <div class="row q-col-gutter-xl justify-center">
      <!-- Columna Izquierda: Credencial (ID License) -->
      <div class="col-12 col-lg-5">
        <q-card class="premium-glass-card q-pa-none overflow-hidden welcome-hero shadow-24" style="border-radius: 20px;">
          <div class="bg-red-10 q-pa-md d-flex justify-between items-center shadow-inner" style="display: flex; justify-content: space-between;">
             <div class="d-flex items-center" style="display: flex; align-items: center; gap: 8px;">
                <q-icon name="verified" color="white" size="24px" />
                <div class="font-head text-white text-weight-bolder tracking-widest uppercase">ID OFICIAL · {{ authStore.rol?.replace('_',' ') }}</div>
             </div>
             <q-icon name="wifi_tethering" color="white" size="24px" class="pulsate" />
          </div>

          <div class="q-pa-xl text-center relative-position">
             <!-- Background glow -->
             <div class="absolute-full" style="background: radial-gradient(circle at 50% 20%, rgba(161,11,19,0.15) 0%, transparent 60%); z-index: 0"></div>
             
             <!-- Content layer -->
             <div class="relative-position" style="z-index: 10;">
                 <div class="flex flex-center relative-position q-mb-lg" style="margin: 0 auto; width: 140px;">
                    <div class="hologram-ring"></div>
                    <q-avatar size="140px" class="glow-avatar shadow-24" :style="`background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})`">
                       <span class="text-h1 text-white font-head text-weight-bolder">{{ iniciales }}</span>
                    </q-avatar>
                    <q-badge floating rounded color="emerald-9" class="q-pa-sm border-white shadow-24" style="border: 2px solid rgba(255,255,255,0.2); z-index: 20;">
                       <q-icon name="admin_panel_settings" size="18px" />
                    </q-badge>
                 </div>

                 <!-- Datos del Titular -->
                 <div class="text-h5 text-white font-head text-weight-bolder text-center q-mb-lg" style="line-height: 1.3;">{{ authStore.nombre }}</div>
                 
                 <!-- Bloque Negro de Datos -->
                 <div class="full-width q-pa-lg rounded-12 shadow-inner text-left" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(161,11,19,0.2);">
                    <div class="q-mb-md">
                       <div class="text-grey-6 font-mono uppercase tracking-widest q-mb-xs" style="font-size:9px">Usuario de Red</div>
                       <div class="text-white text-weight-bolder font-mono" style="font-size:14px; word-break: break-all;">{{ authStore.usuario?.email.split('@')[0] }}</div>
                    </div>
                    <q-separator dark class="opacity-10 q-my-md" />
                    <div class="q-mb-md">
                       <div class="text-grey-6 font-mono uppercase tracking-widest q-mb-xs" style="font-size:9px">Frecuencia / Email</div>
                       <div class="text-grey-3 text-weight-bold font-mono" style="font-size:13px; word-break: break-all;">{{ authStore.usuario?.email }}</div>
                    </div>
                    <q-separator dark class="opacity-10 q-my-md" />
                    <div>
                       <div class="text-grey-6 font-mono uppercase tracking-widest q-mb-xs" style="font-size:9px">Estado de Permisos</div>
                       <q-badge color="emerald-9" label="AUTORIZADO ACTIVO" class="font-mono text-weight-bolder" />
                    </div>
                 </div>
             </div>
          </div>
          <!-- Código de Barras Decorativo -->
          <div class="barcode-footer">||| |||| | ||||| ||| | || |||| |</div>
        </q-card>
      </div>

      <!-- Columna Derecha: Bóveda de Seguridad -->
      <div class="col-12 col-lg-7">
        <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-left rounded-20 h-full d-flex column justify-between">
          <div>
              <div class="row items-center justify-between q-mb-lg border-bottom-border pb-md">
                <div class="row items-center">
                  <q-icon name="gpp_good" color="emerald-9" size="32px" class="q-mr-md glow-emerald" />
                  <div class="text-h5 font-head text-white text-weight-bolder tracking-tighter uppercase">Protocolo de Seguridad</div>
                </div>
              </div>

              <div class="text-caption text-grey-5 font-mono uppercase tracking-widest q-mb-xl" style="font-size:10px">Módulo de actualización de cifrado de red (Cambio de Contraseña).</div>
              
              <q-form @submit.prevent="cambiarPassword" class="q-gutter-y-lg">
                <q-input v-model="passForm.password_actual" :type="verP1 ? 'text' : 'password'" 
                  filled dark label="CREDENCIAL ACTUAL" 
                  class="premium-input-login" 
                  stack-label hide-bottom-space
                  :rules="[v=>!!v||'Requerido']" 
                >
                  <template #prepend><q-icon name="vpn_key" color="red-9" /></template>
                  <template #append>
                    <q-icon :name="verP1 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP1 = !verP1" />
                  </template>
                </q-input>


                <div class="row q-col-gutter-lg">
                  <div class="col-12 col-md-6">
                    <q-input v-model="passForm.password_nuevo" :type="verP2 ? 'text' : 'password'" 
                      filled dark label="NUEVO CIFRADO (MIN. 8 CHARS)" 
                      class="premium-input-login" 
                      stack-label hide-bottom-space
                      :rules="[v=>v&&v.length>=8||'Mínimo 8 caracteres']"
                    >
                      <template #prepend><q-icon name="enhanced_encryption" color="emerald-9" /></template>
                      <template #append>
                        <q-icon :name="verP2 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP2 = !verP2" />
                      </template>
                    </q-input>

                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="passForm.password_nuevo_confirmation" :type="verP3 ? 'text' : 'password'" 
                      filled dark label="RE-VERIFICAR CIFRADO" 
                      class="premium-input-login" 
                      stack-label hide-bottom-space
                      :rules="[v=>v===passForm.password_nuevo||'Las contraseñas no coinciden']"
                    >
                      <template #prepend><q-icon name="fact_check" color="emerald-9" /></template>
                      <template #append>
                        <q-icon :name="verP3 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP3 = !verP3" />
                      </template>
                    </q-input>

                  </div>
                </div>

                <div class="q-pa-md bg-black-20 rounded-12 border-red-low shadow-inner row items-center q-mt-md">
                   <q-icon name="warning" color="red-9" size="24px" class="q-mr-md" />
                   <div class="col text-caption text-grey-4 font-mono" style="font-size:11px">
                     ATENCIÓN: Actualizar sus credenciales revocará inmediatamente las sesiones activas en todos los demás terminales (RAC Security Standard).
                   </div>
                </div>

                <div class="flex justify-end q-mt-xl">
                   <q-btn unelevated color="red-10" label="Sincronizar Bóveda de Seguridad" 
                      icon="system_update_alt" class="premium-btn shadow-24 q-px-xl q-py-md text-weight-bolder" 
                      :loading="guardando" type="submit" />
                </div>
              </q-form>
          </div>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q       = useQuasar()
const router   = useRouter()
const authStore = useAuthStore()
const guardando = ref(false)
const passForm  = ref({ password_actual: '', password_nuevo: '', password_nuevo_confirmation: '' })
const verP1 = ref(false)
const verP2 = ref(false)
const verP3 = ref(false)


const iniciales = computed(() => {
  const n = authStore.nombre || '?'
  return n.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase()
})

const rolStyle = computed(() => {
  const map = {
    estudiante:    { bg: '#3b82f6', accent: '#60a5fa' },
    instructor:    { bg: '#0d9488', accent: '#2dd4bf' },
    admin:         { bg: '#7c3aed', accent: '#a78bfa' },
    dir_ops:       { bg: '#ea580c', accent: '#fb923c' },
    mantenimiento: { bg: '#2563eb', accent: '#3b82f6' },
  }
  return map[authStore.rol] || { bg: '#A10B13', accent: '#ef4444' }
})

async function cambiarPassword() {
  guardando.value = true
  try {
    await api.put('/auth/password', passForm.value)
    $q.notify({ color: 'emerald-9', icon: 'shield', message: 'Protocolo de seguridad actualizado. Procediendo a reinicio de sistema.' })
    await authStore.logout()
    router.push('/login')
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Fallo en la actualización de seguridad.' })
  } finally { guardando.value = false }
}
</script>

<style lang="scss" scoped>

.shadow-inner { box-shadow: inset 0 2px 15px rgba(0,0,0,0.5); }

.h-full { height: 100%; }
.bg-black-20 { background: rgba(0,0,0,0.2); }

.glow-emerald { filter: drop-shadow(0 0 15px rgba(16, 185, 129, 0.4)); }
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

.welcome-hero { position: relative; }
.hero-glow { position: relative; z-index: 1; background: radial-gradient(circle at 50% 20%, rgba(161, 11, 19, 0.15) 0%, transparent 60%); }
.glow-avatar { border: 6px solid rgba(10, 12, 17, 0.9); box-shadow: 0 0 40px rgba(161,11,19,0.3); z-index: 10; }

.hologram-ring {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  width: 160px; height: 160px; border-radius: 50%;
  border: 1px bordered rgba(255,255,255,0.1); border-top: 2px solid #A10B13;
  animation: spin 8s linear infinite;
  z-index: 0;
}
@keyframes spin { 100% { transform: translate(-50%, -50%) rotate(360deg); } }

.barcode-footer {
  font-family: 'Libre Barcode 39', monospace; /* Si no tiene la fuente, se verá como lineas */
  font-size: 24px; color: rgba(255,255,255,0.2); text-align: center; padding: 10px;
  background: rgba(0,0,0,0.3); letter-spacing: 2px;
}

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important; background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(16, 185, 129, 0.5) !important; }
  }
}
</style>
