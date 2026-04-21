<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="window-height row items-center justify-center login-page-bg overflow-hidden">
        
        <!-- ELEMENTOS ATMOSFÉRICOS -->
        <div class="absolute-full overflow-hidden no-pointer-events">
          <div class="glow-orb orb-1"></div>
          <div class="glow-orb orb-2"></div>
        </div>

        <div class="col-xs-11 col-sm-6 col-md-4 col-lg-3 animate-fade" style="max-width: 420px; z-index: 10;">
          
          <!-- BRANDING -->
          <div class="text-center q-mb-xl">
             <div class="logo-container q-mb-lg">
                <img src="https://i.ibb.co/8DW6rNGm/LOGO.jpg" class="premium-logo shadow-24" />
             </div>
             <div class="font-head text-white text-h4 text-weight-bolder tracking-tighter line-height-1">
               RECUPERACIÓN <span class="text-red-9">.</span>
             </div>
             <div class="font-mono text-grey-5 q-mt-xs tracking-widest uppercase" style="font-size: 11px">
               Soporte Técnico de Red
             </div>
          </div>

          <q-card class="premium-glass-card q-pa-xl shadow-24 border-red-top">
            <q-card-section class="q-pa-none">

              <!-- PASO 1: SOLICITUD -->
              <template v-if="paso === 1">
                <div class="text-caption text-grey-5 font-mono q-mb-xl text-center line-height-1" style="font-size: 11px">
                  INGRESE SU EMAIL REGISTRADO PARA RECIBIR INSTRUCCIONES DE RESTABLECIMIENTO.
                </div>
                <q-form @submit.prevent="enviarSolicitud" class="q-gutter-y-lg">
                   <div>
                      <div class="text-caption text-grey-6 font-mono q-mb-xs uppercase" style="font-size: 9px">Correo de Recuperación</div>
                      <q-input v-model="email" type="email" filled dark dense class="premium-input-login" placeholder="usuario@mail.com" :rules="[v => !!v || 'Requerido']" hide-bottom-space>
                         <template #prepend><q-icon name="send" color="red-9" size="20px" /></template>
                      </q-input>
                   </div>
                   <q-btn type="submit" unelevated color="red-9" class="full-width premium-btn q-py-md" :loading="cargando" label="Enviar Vínculo" />
                </q-form>
              </template>

              <!-- PASO 2: CONFIRMACIÓN -->
              <template v-else-if="paso === 2">
                <div class="text-center q-py-md">
                   <q-icon name="mark_email_read" color="emerald" size="80px" class="q-mb-lg shadow-glow-emerald" />
                   <div class="text-h6 text-white font-head q-mb-md">Enlace de Seguridad Enviado</div>
                   <div class="text-caption text-grey-5 font-mono line-height-1" style="font-size: 11px">
                      SI EL CORREO <span class="text-white text-weight-bold">{{ email }}</span> EXISTE, RECIBIRÁ INSTRUCCIONES EN UNOS MINUTOS. REVISTE SU BANDEJA DE SPAM.
                   </div>
                   <q-btn flat color="red-9" label="Volver al Login" to="/login" class="q-mt-xl font-mono text-weight-bold" />
                </div>
              </template>

              <!-- PASO 3: NUEVA CLAVE -->
              <template v-else-if="paso === 3">
                <div class="text-caption text-grey-5 font-mono q-mb-xl text-center" style="font-size: 11px">ESTABLECE TU NUEVA CREDENCIAL DE ACCESO.</div>
                <q-form @submit.prevent="resetearPassword" class="q-gutter-y-lg">
                   <q-input v-model="formReset.password" :type="showPwd1 ? 'text' : 'password'" filled dark dense label="NUEVA CONTRASEÑA" class="premium-input-login" stack-label :rules="[v => v.length >= 8 || 'Mínimo 8 caracteres']" hide-bottom-space>
                      <template #prepend><q-icon name="lock" color="emerald" /></template>
                      <template #append><q-icon :name="showPwd1 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="showPwd1 = !showPwd1" /></template>
                   </q-input>
                   <q-input v-model="formReset.password_confirmation" :type="showPwd2 ? 'text' : 'password'" filled dark dense label="CONFIRMAR" class="premium-input-login" stack-label :rules="[v => v === formReset.password || 'Error coincidencia']" hide-bottom-space>
                      <template #prepend><q-icon name="verified_user" color="emerald" /></template>
                      <template #append><q-icon :name="showPwd2 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="showPwd2 = !showPwd2" /></template>
                   </q-input>
                   <q-btn type="submit" unelevated color="red-9" class="full-width premium-btn q-py-md shadow-24" :loading="cargando" label="Actualizar Credencial" />
                </q-form>
              </template>

              <!-- PASO 4: ÉXITO -->
              <template v-else-if="paso === 4">
                <div class="text-center q-py-md">
                   <q-icon name="verified" color="emerald" size="80px" class="q-mb-lg shadow-glow-emerald" />
                   <div class="text-h6 text-white font-head q-mb-md">Acceso Restablecido</div>
                   <div class="text-caption text-grey-5 font-mono q-mb-xl" style="font-size: 11px">TU CUENTA HA SIDO ASEGURADA CON LA NUEVA CONTRASEÑA.</div>
                   <q-btn unelevated color="red-9" label="Iniciar Sesión" to="/login" class="full-width premium-btn q-py-md" />
                </div>
              </template>

              <!-- Volver lateral -->
              <div class="text-center q-mt-xl" v-if="paso !== 4 && paso !== 2">
                <q-btn flat no-caps dense color="grey-7" label="← Volver al inicio" to="/login" size="sm" class="font-mono" />
              </div>

            </q-card-section>
          </q-card>

          <div class="text-center q-mt-xl opacity-30 font-mono text-grey-5" style="font-size: 9px">
             OPERACIÓN SEGURA · 256-BIT ENCRYPTION
          </div>
        </div>

      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const route   = useRoute()
const router  = useRouter()
const $q      = useQuasar()

const paso     = ref(1)
const email    = ref('')
const cargando = ref(false)
const showPwd1 = ref(false)
const showPwd2 = ref(false)
const formReset = ref({ token: '', email: '', password: '', password_confirmation: '' })

async function enviarSolicitud() {
  cargando.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    paso.value = 2
  } catch {
    $q.notify({ color: 'negative', message: 'Error en servidor de correo.' })
  } finally { cargando.value = false }
}

async function resetearPassword() {
  cargando.value = true
  try {
    await api.post('/auth/reset-password', formReset.value)
    paso.value = 4
  } catch (e) {
    $q.notify({ color: 'negative', message: e.response?.data?.mensaje || 'Tóken expirado.' })
  } finally { cargando.value = false }
}

onMounted(() => {
  const token = route.query.token
  const emailParam = route.query.email
  if (token && emailParam) {
    formReset.value.token = token
    formReset.value.email = emailParam
    paso.value = 3
  }
})
</script>

<style lang="scss" scoped>
.login-page-bg { background: #05070a; position: relative; }
.glow-orb {
  position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.15; pointer-events: none;
  &.orb-1 { width: 600px; height: 600px; top: -100px; right: -100px; background: radial-gradient(circle, #A10B13 0%, transparent 70%); }
  &.orb-2 { width: 500px; height: 500px; bottom: -100px; left: -100px; background: radial-gradient(circle, #05070a 0%, #A10B13 70%); }
}
.premium-logo { height: 90px; width: auto; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 20px 40px rgba(161, 11, 19, 0.2); }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important;
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05) !important;
    transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { background: rgba(255,255,255,0.05) !important; border-color: rgba(161,11,19,0.3) !important; }
  }
  &.q-field--focused :deep(.q-field__control) { background: rgba(161, 11, 19, 0.05) !important; border-color: #A10B13 !important; box-shadow: 0 0 15px rgba(161, 11, 19, 0.2); }
}

@keyframes loginAppear { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

.shadow-glow-emerald { filter: drop-shadow(0 0 10px rgba(16, 185, 129, 0.4)); }

</style>
