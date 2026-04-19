<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="window-height row items-center justify-center"
        style="background: radial-gradient(ellipse at top, #0d1520 0%, #0a0c10 70%)">

    <div class="col-xs-11 col-sm-8 col-md-4 col-lg-3" style="max-width:400px; z-index:1">

      <!-- Logo -->
      <div class="text-center q-mb-xl">
        <div class="row justify-center items-center q-mb-md">
          <img src="https://i.ibb.co/8DW6rNGm/LOGO.jpg"
            style="height:100px; width:auto; object-fit:contain; border-radius:8px;">
        </div>
        <div class="font-head text-white" style="font-size:26px; font-weight:800; letter-spacing:.5px; line-height: 1.1;">
          SCHOOL <span style="color: #A10B13;">OF</span> TRAINING<br>
          <span style="font-size: 14px; letter-spacing: 4px; font-family: 'JetBrains Mono', monospace; font-weight: normal; color: #94a3b8;">AVIATION</span>
        </div>
        <div class="font-mono q-mt-md" style="font-size:10px; color:#475569; letter-spacing:2px; text-transform:uppercase">
          Recuperar Contraseña
        </div>
      </div>

      <q-card flat style="background:#0f1218; border:1px solid rgba(255,255,255,.09); border-radius:14px">
        <q-card-section class="q-pa-xl">

          <!-- Paso 1: ingresar email -->
          <template v-if="paso === 1">
            <div style="font-size:13px; color:#94a3b8; margin-bottom:20px; line-height:1.6">
              Ingrese su correo electrónico. Le enviaremos un enlace para restablecer su contraseña.
            </div>

            <q-form @submit.prevent="enviarSolicitud" class="q-gutter-md">
              <div>
                <div class="font-mono q-mb-xs" style="font-size:10px; color:#64748b; letter-spacing:1px; text-transform:uppercase">
                  Correo electrónico
                </div>
                <q-input v-model="email" type="email" outlined dense dark bg-color="grey-10"
                  placeholder="usuario@escuela.com" :disable="cargando"
                  :rules="[v => !!v || 'Requerido', v => /.+@.+\..+/.test(v) || 'Email inválido']"
                  lazy-rules>
                  <template #prepend><q-icon name="email" color="grey-6" size="18px" /></template>
                </q-input>
              </div>

              <q-btn type="submit" unelevated color="primary" class="full-width"
                style="height:44px; border-radius:8px; font-weight:600"
                :loading="cargando" :disable="cargando"
                label="Enviar instrucciones" />
            </q-form>
          </template>

          <!-- Paso 2: confirmación enviada -->
          <template v-else-if="paso === 2">
            <div class="text-center q-py-md">
              <q-icon name="mark_email_read" color="positive" size="56px" class="q-mb-md" />
              <div class="font-head text-white" style="font-size:18px; font-weight:700; margin-bottom:8px">
                Correo enviado
              </div>
              <div style="font-size:13px; color:#94a3b8; line-height:1.6">
                Si el correo <strong class="text-primary">{{ email }}</strong> está registrado en el sistema,
                recibirá un enlace para restablecer su contraseña.
              </div>
              <div class="q-mt-md font-mono" style="font-size:11px; color:#475569">
                Revise también su carpeta de spam.
              </div>
            </div>
          </template>

          <!-- Paso 3: nueva contraseña (viene del link del correo con ?token=) -->
          <template v-else-if="paso === 3">
            <div style="font-size:13px; color:#94a3b8; margin-bottom:20px">
              Ingrese su nueva contraseña.
            </div>
            <q-form @submit.prevent="resetearPassword" class="q-gutter-md">
              <q-input v-model="formReset.password" :type="showPwd1 ? 'text' : 'password'"
                outlined dense dark bg-color="grey-10" label="Nueva contraseña"
                :rules="[v => !!v || 'Requerido', v => v.length >= 8 || 'Mínimo 8 caracteres']"
                lazy-rules>
                <template #append>
                  <q-icon :name="showPwd1 ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                    color="grey-6" @click="showPwd1 = !showPwd1" />
                </template>
              </q-input>

              <q-input v-model="formReset.password_confirmation" :type="showPwd2 ? 'text' : 'password'"
                outlined dense dark bg-color="grey-10" label="Confirmar contraseña"
                :rules="[v => v === formReset.password || 'Las contraseñas no coinciden']"
                lazy-rules>
                <template #append>
                  <q-icon :name="showPwd2 ? 'visibility_off' : 'visibility'" class="cursor-pointer"
                    color="grey-6" @click="showPwd2 = !showPwd2" />
                </template>
              </q-input>

              <q-banner v-if="errorMsg" dense rounded
                style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.2); color:#fca5a5; border-radius:8px; font-size:13px">
                <template #avatar><q-icon name="error_outline" color="negative" /></template>
                {{ errorMsg }}
              </q-banner>

              <q-btn type="submit" unelevated color="primary" class="full-width"
                style="height:44px; border-radius:8px; font-weight:600"
                :loading="cargando" label="Restablecer contraseña" />
            </q-form>
          </template>

          <!-- Paso 4: éxito reset -->
          <template v-else-if="paso === 4">
            <div class="text-center q-py-md">
              <q-icon name="lock_reset" color="positive" size="56px" class="q-mb-md" />
              <div class="font-head text-white" style="font-size:18px; font-weight:700; margin-bottom:8px">
                Contraseña restablecida
              </div>
              <div style="font-size:13px; color:#94a3b8; margin-bottom:24px">
                Su contraseña fue actualizada exitosamente.
              </div>
              <q-btn unelevated color="primary" label="Ir al login" to="/login" no-caps
                style="border-radius:8px; padding:10px 32px; font-weight:600" />
            </div>
          </template>

          <!-- Volver al login -->
          <div class="text-center q-mt-lg" v-if="paso !== 4">
            <router-link to="/login" style="font-size:12px; color:#60a5fa; text-decoration:none">
              ← Volver al inicio de sesión
            </router-link>
          </div>
        </q-card-section>
      </q-card>
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
const errorMsg = ref('')
const showPwd1 = ref(false)
const showPwd2 = ref(false)

const formReset = ref({ token: '', email: '', password: '', password_confirmation: '' })

async function enviarSolicitud() {
  cargando.value = true
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    paso.value = 2
  } catch {
    $q.notify({ type: 'negative', message: 'Error al enviar el correo. Intente nuevamente.' })
  } finally { cargando.value = false }
}

async function resetearPassword() {
  cargando.value = true
  errorMsg.value = ''
  try {
    await api.post('/auth/reset-password', formReset.value)
    paso.value = 4
  } catch (e) {
    errorMsg.value = e.response?.data?.mensaje || 'Token inválido o expirado. Solicite un nuevo enlace.'
  } finally { cargando.value = false }
}

onMounted(() => {
  // Si viene del link del correo con ?token=xxx&email=yyy
  const token = route.query.token
  const emailParam = route.query.email
  if (token && emailParam) {
    formReset.value.token = token
    formReset.value.email = emailParam
    paso.value = 3
  }
})
</script>
