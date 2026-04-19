<template>
  <q-page padding style="padding-bottom:80px">
    <div class="font-head text-white q-mb-lg" style="font-size:20px;font-weight:700">Mi Perfil</div>

    <div class="row q-col-gutter-md">
      <div class="col-12 col-md-4">
        <q-card flat class="card-rac text-center q-pa-xl" style="background:#0f1218">
          <q-avatar size="80px" :color="rolColor" text-color="white" style="font-size:28px;font-weight:700;margin-bottom:16px">
            {{ iniciales }}
          </q-avatar>
          <div class="font-head text-white" style="font-size:18px;font-weight:700">{{ authStore.nombre }}</div>
          <div class="font-mono q-mt-xs" style="font-size:11px;color:#475569;letter-spacing:1px;text-transform:uppercase">
            {{ authStore.rol?.replace('_',' ') }}
          </div>
          <div class="q-mt-sm" style="font-size:12px;color:#64748b">{{ authStore.usuario?.email }}</div>
        </q-card>
      </div>

      <div class="col-12 col-md-8">
        <!-- Cambiar contraseña -->
        <q-card flat class="card-rac" style="background:#0f1218">
          <q-card-section>
            <div class="font-mono q-mb-md" style="font-size:10px;color:#475569;letter-spacing:2px">CAMBIAR CONTRASEÑA</div>
            <q-form @submit.prevent="cambiarPassword" class="q-gutter-md">
              <q-input v-model="passForm.password_actual" type="password" outlined dark bg-color="grey-10"
                label="Contraseña actual" stack-label :rules="[v=>!!v||'Requerido']" />
              <q-input v-model="passForm.password_nuevo" type="password" outlined dark bg-color="grey-10"
                label="Nueva contraseña" stack-label :rules="[v=>v&&v.length>=8||'Mínimo 8 caracteres']" />
              <q-input v-model="passForm.password_nuevo_confirmation" type="password" outlined dark bg-color="grey-10"
                label="Confirmar nueva contraseña" stack-label
                :rules="[v=>v===passForm.password_nuevo||'Las contraseñas no coinciden']" />
              <q-btn unelevated color="primary" no-caps type="submit" label="Actualizar contraseña"
                :loading="guardando" style="border-radius:8px" />
            </q-form>
          </q-card-section>
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

const iniciales = computed(() => {
  const n = authStore.nombre
  return n ? n.split(' ').map(w=>w[0]).slice(0,2).join('').toUpperCase() : '?'
})
const rolColor = computed(() => ({
  estudiante:'primary', instructor:'teal', admin:'purple',
  dir_ops:'amber-9', mantenimiento:'orange', auditor_uaeac:'green',
}[authStore.rol] || 'grey'))

async function cambiarPassword() {
  guardando.value = true
  try {
    await api.put('/auth/password', passForm.value)
    $q.notify({ type: 'positive', message: 'Contraseña actualizada. Vuelva a iniciar sesión.' })
    await authStore.logout()
    router.push('/login')
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.mensaje || 'Error al actualizar contraseña.' })
  } finally { guardando.value = false }
}
</script>
