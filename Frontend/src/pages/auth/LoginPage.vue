<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="window-height row items-center justify-center"
        style="background: radial-gradient(ellipse at top, #0d1520 0%, #0a0c10 70%)">

    <!-- Fondo decorativo -->
    <div class="absolute-full overflow-hidden" style="pointer-events:none">
      <div style="position:absolute; top:-100px; right:-100px; width:500px; height:500px;
        background:radial-gradient(circle, rgba(59,130,246,.06) 0%, transparent 70%); border-radius:50%"></div>
      <div style="position:absolute; bottom:-80px; left:-80px; width:400px; height:400px;
        background:radial-gradient(circle, rgba(20,184,166,.04) 0%, transparent 70%); border-radius:50%"></div>
    </div>

    <div class="col-xs-11 col-sm-8 col-md-4 col-lg-3" style="max-width:420px; z-index:1">

      <!-- Logo y título -->
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
          Acceso Corporativo
        </div>
      </div>

      <!-- Card de login -->
      <q-card flat style="background:#0f1218; border:1px solid rgba(255,255,255,.09); border-radius:14px">
        <q-card-section class="q-pa-xl">

          <q-form @submit.prevent="iniciarSesion" class="q-gutter-md">

            <!-- Email -->
            <div>
              <div class="font-mono q-mb-xs" style="font-size:10px; color:#64748b; letter-spacing:1px; text-transform:uppercase">
                Correo electrónico
              </div>
              <q-input
                v-model="form.email"
                type="email"
                outlined
                dense
                dark
                :disable="cargando"
                placeholder="usuario@escuela.com"
                :rules="[v => !!v || 'Requerido', v => /.+@.+\..+/.test(v) || 'Email inválido']"
                lazy-rules
                bg-color="grey-10"
                style="border-radius:8px"
              >
                <template #prepend>
                  <q-icon name="email" color="grey-6" size="18px" />
                </template>
              </q-input>
            </div>

            <!-- Contraseña -->
            <div>
              <div class="font-mono q-mb-xs" style="font-size:10px; color:#64748b; letter-spacing:1px; text-transform:uppercase">
                Contraseña
              </div>
              <q-input
                v-model="form.password"
                :type="mostrarPassword ? 'text' : 'password'"
                outlined
                dense
                dark
                :disable="cargando"
                placeholder="••••••••"
                :rules="[v => !!v || 'Requerido', v => v.length >= 6 || 'Mínimo 6 caracteres']"
                lazy-rules
                bg-color="grey-10"
              >
                <template #prepend>
                  <q-icon name="lock" color="grey-6" size="18px" />
                </template>
                <template #append>
                  <q-icon
                    :name="mostrarPassword ? 'visibility_off' : 'visibility'"
                    color="grey-6" size="18px" class="cursor-pointer"
                    @click="mostrarPassword = !mostrarPassword"
                  />
                </template>
              </q-input>
            </div>

            <!-- Error de login -->
            <q-banner v-if="error" dense rounded
              style="background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.2); color:#fca5a5; border-radius:8px; font-size:13px">
              <template #avatar><q-icon name="error_outline" color="negative" /></template>
              {{ error }}
            </q-banner>

            <!-- Botón login -->
            <q-btn
              type="submit"
              unelevated
              color="primary"
              class="full-width q-mt-sm"
              style="background: #A10B13; color: white; height:44px; border-radius:8px; font-weight:600; font-size:14px"
              :loading="cargando"
              :disable="cargando"
              label="Iniciar Sesión"
            >
              <template #loading>
                <q-spinner-tail color="white" size="1.2em" />
              </template>
            </q-btn>

            <!-- Recuperar contraseña -->
            <div class="text-center q-mt-sm">
              <router-link to="/recuperar-password"
                style="font-size:12px; color:#60a5fa; text-decoration:none">
                ¿Olvidó su contraseña?
              </router-link>
            </div>

          </q-form>
        </q-card-section>
      </q-card>

      <!-- Footer -->
      <div class="text-center q-mt-lg font-mono" style="font-size:10px; color:#334155; letter-spacing:1px">
        UAEAC · RAC 141 · Colombia · v1.0
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
