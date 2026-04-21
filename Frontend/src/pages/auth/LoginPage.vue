<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="window-height row items-center justify-center login-page-bg overflow-hidden">
        
        <!-- ════ ELEMENTOS ATMOSFÉRICOS ════ -->
        <div class="absolute-full overflow-hidden no-pointer-events">
          <div class="glow-orb orb-1"></div>
          <div class="glow-orb orb-2"></div>
        </div>

        <div class="col-xs-11 col-sm-6 col-md-4 col-lg-3 animate-fade" style="max-width: 420px; z-index: 10;">
          
          <!-- BRANDING INICIAL -->
          <div class="text-center q-mb-xl">
             <div class="logo-container q-mb-lg">
                <img src="https://i.ibb.co/8DW6rNGm/LOGO.jpg" class="premium-logo shadow-24" />
             </div>
             <div class="font-head text-white text-h4 text-weight-bolder tracking-tighter line-height-1">
               SCHOOL <span class="text-red-9">OF</span> TRAINING
             </div>
             <div class="font-mono text-grey-5 q-mt-xs tracking-widest uppercase" style="font-size: 11px">
               Aviation Academy . <span class="text-white">v2.0</span>
             </div>
          </div>

          <!-- MÓDULO DE ACCESO (GLASS) -->
          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top">
            <q-card-section class="q-pa-none">
              
              <div class="font-mono text-grey-6 q-mb-xl text-center uppercase tracking-widest" style="font-size: 10px">
                Identificación de Tripulante
              </div>

              <q-form @submit.prevent="iniciarSesion" class="q-gutter-y-lg">
                
                <!-- Identificador / Email -->
                <div>
                   <div class="text-caption text-grey-6 font-mono q-mb-xs uppercase" style="font-size: 9px">Correo Corporativo</div>
                   <q-input 
                      v-model="form.email" 
                      type="email" 
                      filled dark dense 
                      class="premium-input-login"
                      placeholder="piloto@school.com"
                      :rules="[v => !!v || 'Requerido', v => /.+@.+\..+/.test(v) || 'Email inválido']"
                      hide-bottom-space
                   >
                     <template #prepend><q-icon name="alternate_email" color="red-9" size="20px" /></template>
                   </q-input>
                </div>

                <!-- Credencial / Password -->
                <div>
                   <div class="text-caption text-grey-6 font-mono q-mb-xs uppercase" style="font-size: 9px">Clave de Acceso</div>
                   <q-input 
                      v-model="form.password" 
                      :type="mostrarPassword ? 'text' : 'password'" 
                      filled dark dense 
                      class="premium-input-login"
                      placeholder="••••••••"
                      :rules="[v => !!v || 'Requerido']"
                      hide-bottom-space
                   >
                     <template #prepend><q-icon name="vpn_key" color="red-9" size="20px" /></template>
                     <template #append>
                        <q-icon 
                           :name="mostrarPassword ? 'visibility_off' : 'visibility'" 
                           color="grey-7" size="18px" class="cursor-pointer" 
                           @click="mostrarPassword = !mostrarPassword" 
                        />
                     </template>
                   </q-input>
                </div>

                <!-- Alerta de Error -->
                <transition name="q-transition--jump-left">
                   <div v-if="error" class="error-banner q-pa-md row items-center no-wrap">
                      <q-icon name="report_problem" color="red-9" size="20px" class="q-mr-sm" />
                      <div class="text-caption text-red-9 text-weight-bold font-mono">{{ error }}</div>
                   </div>
                </transition>

                <!-- Botón de Ignición -->
                <q-btn 
                   type="submit" 
                   unelevated color="red-9" 
                   class="full-width premium-btn q-py-md shadow-24" 
                   :loading="cargando"
                   label="Iniciar Operación"
                >
                   <template #loading>
                      <q-spinner-tail color="white" />
                   </template>
                </q-btn>

                <!-- Recovery -->
                <div class="text-center q-mt-md">
                   <q-btn flat no-caps dense color="grey-6" label="¿Olvidó su contraseña?" to="/recuperar-password" size="sm" class="font-mono" />
                </div>

              </q-form>
            </q-card-section>
          </q-card>

          <!-- Footer Regulador -->
          <div class="text-center q-mt-xl opacity-30 font-mono text-grey-5" style="font-size: 9px">
             CENTRO DE INSTRUCCIÓN AERONÁUTICA · UAEAC · RAC 141
          </div>
        </div>

      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from 'store/auth'

const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })
const cargando      = ref(false)
const error         = ref('')
const mostrarPassword = ref(false)

async function iniciarSesion() {
  error.value   = ''
  cargando.value = true
  const resultado = await authStore.login(form.value.email, form.value.password)
  cargando.value = false
  if (resultado.ok) {
    const redirect = route.query.redirect || '/dashboard'
    router.push(redirect)
  } else {
    error.value = resultado.mensaje
  }
}
</script>

<style lang="scss" scoped>
.login-page-bg {
  background: #05070a;
  position: relative;
}

.glow-orb {
  position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.15; pointer-events: none;
  &.orb-1 { width: 600px; height: 600px; top: -100px; right: -100px; background: radial-gradient(circle, #A10B13 0%, transparent 70%); }
  &.orb-2 { width: 500px; height: 500px; bottom: -100px; left: -100px; background: radial-gradient(circle, #05070a 0%, #A10B13 70%); }
}

.premium-logo {
  height: 90px; width: auto; border-radius: 12px; 
  border: 1px solid rgba(255,255,255,0.1);
  box-shadow: 0 20px 40px rgba(161, 11, 19, 0.2);
}

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important;
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    transition: all 0.3s ease;
    &::before { display: none; }
    &::after { display: none; }
    &:hover { background: rgba(255,255,255,0.05) !important; border-color: rgba(161,11,19,0.3) !important; }
  }
  &.q-field--focused :deep(.q-field__control) {
    background: rgba(161, 11, 19, 0.05) !important;
    border-color: #A10B13 !important;
    box-shadow: 0 0 15px rgba(161, 11, 19, 0.2);
  }
}

.error-banner {
  background: rgba(161, 11, 19, 0.05);
  border: 1px solid rgba(161, 11, 19, 0.2);
  border-radius: 12px;
}

@keyframes loginAppear {
  from { opacity: 0; transform: translateY(30px); }
  to { opacity: 1; transform: translateY(0); }
}

</style>
