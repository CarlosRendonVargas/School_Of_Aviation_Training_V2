<template>
  <q-page class="q-pa-md animate-fade" style="padding-bottom: 80px">

    <!-- ══ Header ══ -->
    <div class="row items-center q-mb-xl rac-page-header">
      <q-icon name="badge" size="48px" color="red-9" class="q-mr-md glow-primary pulsate" />
      <div>
        <div class="rac-page-subtitle">SISTEMA DE IDENTIFICACIÓN AERONÁUTICA · RAC</div>
        <h1 class="rac-page-title">Credencial Operativa</h1>
      </div>
    </div>

    <div class="row q-col-gutter-xl justify-center">

      <!-- ══════════════════════════════════════════
           COLUMNA IZQUIERDA — Carnet ID
      ══════════════════════════════════════════ -->
      <div class="col-12 col-lg-4">
        <q-card class="id-card overflow-hidden shadow-24" style="border-radius:20px; min-height: 480px">

          <!-- Franja superior -->
          <div class="id-card-header row items-center justify-between q-px-lg q-py-md">
            <div class="row items-center q-gutter-x-sm">
              <q-icon name="verified" color="white" size="20px" />
              <span class="font-mono text-white text-weight-bolder tracking-widest" style="font-size:11px; letter-spacing:2px">
                ID OFICIAL · {{ authStore.rol?.replace(/_/g,' ').toUpperCase() }}
              </span>
            </div>
            <q-icon name="wifi_tethering" color="white" size="22px" class="pulsate" />
          </div>

          <!-- Cuerpo del carnet -->
          <div class="id-card-body q-pa-xl text-center">

            <!-- Avatar con botón de foto -->
            <div class="avatar-wrapper q-mb-lg" @click="triggerFoto" title="Cambiar foto">
              <div class="hologram-ring"></div>

              <q-avatar size="130px" class="id-avatar shadow-24"
                :style="!fotoPreview && !authStore.foto ? `background: linear-gradient(135deg, ${rolStyle.bg}, ${rolStyle.accent})` : ''">
                <img v-if="fotoPreview || authStore.foto" :src="fotoPreview || authStore.foto"
                  style="width:100%; height:100%; object-fit:cover; border-radius:50%" />
                <span v-else class="text-h2 text-white font-head text-weight-bolder">{{ iniciales }}</span>
              </q-avatar>

              <!-- Overlay cámara -->
              <div class="avatar-camera-overlay">
                <q-icon name="photo_camera" color="white" size="28px" />
                <div class="font-mono text-white" style="font-size:9px; margin-top:4px">CAMBIAR</div>
              </div>

              <!-- Badge de rol -->
              <q-badge rounded :color="rolStyle.badgeColor" class="id-badge shadow-24">
                <q-icon :name="rolStyle.icon" size="14px" />
              </q-badge>

              <!-- Spinner de subida -->
              <q-inner-loading :showing="subiendoFoto" style="border-radius:50%">
                <q-spinner-orbit color="white" size="40px" />
              </q-inner-loading>
            </div>

            <!-- Nombre -->
            <div class="text-h6 text-white font-head text-weight-bolder q-mb-xs" style="line-height:1.2">
              {{ authStore.nombre || authStore.usuario?.email }}
            </div>
            <div class="font-mono q-mb-lg" :style="`color: ${rolStyle.accent}; font-size:11px; letter-spacing:1px`">
              {{ authStore.rol?.replace(/_/g,' ').toUpperCase() }}
            </div>

            <!-- Datos rápidos -->
            <div class="id-data-block text-left q-pa-md">
              <div class="id-data-row">
                <span class="id-data-label">ID SISTEMA</span>
                <span class="id-data-val font-mono">{{ authStore.usuario?.email?.split('@')[0] }}</span>
              </div>
              <q-separator dark class="opacity-10 q-my-sm" />
              <div class="id-data-row">
                <span class="id-data-label">FRECUENCIA</span>
                <span class="id-data-val font-mono" style="font-size:11px; word-break:break-all">{{ authStore.usuario?.email }}</span>
              </div>
              <q-separator dark class="opacity-10 q-my-sm" />
              <div class="id-data-row">
                <span class="id-data-label">ESTADO</span>
                <q-badge color="emerald-9" label="AUTORIZADO ACTIVO" class="font-mono text-weight-bolder" style="font-size:8px" />
              </div>
            </div>
          </div>

          <!-- Código de barras decorativo -->
          <div class="barcode-footer">||| |||| | ||||| ||| | || |||| |</div>
        </q-card>

        <!-- Input file oculto -->
        <input ref="inputFoto" type="file" accept="image/jpeg,image/png,image/webp"
          style="display:none" @change="onFotoSelected" />
      </div>

      <!-- ══════════════════════════════════════════
           COLUMNA DERECHA — Formularios
      ══════════════════════════════════════════ -->
      <div class="col-12 col-lg-8">

        <!-- ── DATOS PERSONALES ── -->
        <q-card class="premium-glass-card shadow-24 border-red-left rounded-20 q-mb-lg">
          <q-card-section class="q-pa-xl">
            <div class="row items-center q-mb-lg border-bottom-border pb-md">
              <q-icon name="person_pin" color="red-9" size="28px" class="q-mr-md" />
              <div>
                <div class="text-h6 font-head text-white text-weight-bolder">Datos del Titular</div>
                <div class="text-caption text-grey-6 font-mono" style="font-size:10px">IDENTIFICACIÓN PERSONAL AERONÁUTICA</div>
              </div>
              <q-space />
              <q-btn v-if="!editandoPerfil" flat no-caps icon="edit" label="Editar" color="red-9" size="sm"
                class="font-mono" @click="editandoPerfil = true" />
            </div>

            <!-- Modo vista -->
            <div v-if="!editandoPerfil" class="row q-col-gutter-md">
              <div v-for="campo in camposVista" :key="campo.label" class="col-12 col-sm-6">
                <div class="perfil-field">
                  <div class="perfil-field-label">{{ campo.label }}</div>
                  <div class="perfil-field-val">{{ campo.valor || '—' }}</div>
                </div>
              </div>
            </div>

            <!-- Modo edición -->
            <q-form v-else @submit.prevent="guardarPerfil" class="row q-col-gutter-md">
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.nombres" label="NOMBRES *" filled dark class="premium-input-login"
                  stack-label :rules="[v => !!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.apellidos" label="APELLIDOS *" filled dark class="premium-input-login"
                  stack-label :rules="[v => !!v || 'Requerido']" />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.num_documento" label="DOCUMENTO" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.telefono" label="TELÉFONO" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.ciudad" label="CIUDAD" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="perfilForm.direccion" label="DIRECCIÓN" filled dark class="premium-input-login" stack-label />
              </div>
              <div class="col-12 row justify-end q-gutter-sm q-mt-sm">
                <q-btn flat label="Cancelar" color="grey-6" @click="cancelarEdicion" />
                <q-btn type="submit" label="Guardar Cambios" color="red-9" icon="save"
                  class="premium-btn" :loading="guardandoPerfil" />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- ── CAMBIO DE CONTRASEÑA ── -->
        <q-card class="premium-glass-card shadow-24 border-red-left rounded-20">
          <q-card-section class="q-pa-xl">
            <div class="row items-center q-mb-lg border-bottom-border pb-md">
              <q-icon name="gpp_good" color="emerald-9" size="28px" class="q-mr-md" style="filter:drop-shadow(0 0 10px rgba(16,185,129,.5))"/>
              <div>
                <div class="text-h6 font-head text-white text-weight-bolder">Protocolo de Seguridad</div>
                <div class="text-caption text-grey-6 font-mono" style="font-size:10px">ACTUALIZACIÓN DE CIFRADO DE RED</div>
              </div>
            </div>

            <q-form @submit.prevent="cambiarPassword" class="row q-col-gutter-md">
              <div class="col-12">
                <q-input v-model="passForm.password_actual" :type="verP1 ? 'text' : 'password'"
                  filled dark label="CREDENCIAL ACTUAL" class="premium-input-login" stack-label
                  :rules="[v => !!v || 'Requerido']">
                  <template #prepend><q-icon name="vpn_key" color="red-9" /></template>
                  <template #append>
                    <q-icon :name="verP1 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP1 = !verP1" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="passForm.password_nuevo" :type="verP2 ? 'text' : 'password'"
                  filled dark label="NUEVO CIFRADO (MÍN. 8)" class="premium-input-login" stack-label
                  :rules="[v => v && v.length >= 8 || 'Mínimo 8 caracteres']">
                  <template #prepend><q-icon name="enhanced_encryption" color="emerald-9" /></template>
                  <template #append>
                    <q-icon :name="verP2 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP2 = !verP2" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-sm-6">
                <q-input v-model="passForm.password_nuevo_confirmation" :type="verP3 ? 'text' : 'password'"
                  filled dark label="RE-VERIFICAR CIFRADO" class="premium-input-login" stack-label
                  :rules="[v => v === passForm.password_nuevo || 'No coinciden']">
                  <template #prepend><q-icon name="fact_check" color="emerald-9" /></template>
                  <template #append>
                    <q-icon :name="verP3 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP3 = !verP3" />
                  </template>
                </q-input>
              </div>

              <div class="col-12">
                <div class="row items-center q-pa-md rounded-12 q-gutter-x-md"
                  style="background:rgba(161,11,19,.08); border:1px solid rgba(161,11,19,.2)">
                  <q-icon name="warning" color="red-9" size="20px" />
                  <div class="col text-caption text-grey-5 font-mono" style="font-size:10px">
                    Actualizar credenciales revocará inmediatamente las sesiones activas en todos los terminales (RAC Security Standard).
                  </div>
                </div>
              </div>

              <div class="col-12 row justify-end">
                <q-btn unelevated type="submit" color="red-10" icon="system_update_alt"
                  label="Sincronizar Bóveda de Seguridad"
                  class="premium-btn shadow-24 q-px-xl q-py-sm text-weight-bolder"
                  :loading="guardandoPass" />
              </div>
            </q-form>

          </q-card-section>
        </q-card>

      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q        = useQuasar()
const router    = useRouter()
const authStore = useAuthStore()

// ── Avatar / Foto ─────────────────────────────────────────────────────────────
const inputFoto    = ref(null)
const fotoPreview  = ref(null)
const subiendoFoto = ref(false)

const triggerFoto = () => inputFoto.value?.click()

const onFotoSelected = async (e) => {
  const file = e.target.files[0]
  if (!file) return

  fotoPreview.value = URL.createObjectURL(file)
  subiendoFoto.value = true

  const form = new FormData()
  form.append('foto', file)
  try {
    const { data } = await api.post('/auth/foto', form, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    authStore.usuario.foto_url = data.foto_url
    fotoPreview.value = data.foto_url
    $q.notify({ color: 'emerald', icon: 'photo_camera', message: 'Foto actualizada correctamente.', textColor: 'dark' })
  } catch {
    fotoPreview.value = null
    $q.notify({ type: 'negative', message: 'Error al subir la foto. Máximo 3MB (JPG/PNG/WebP).' })
  } finally {
    subiendoFoto.value = false
    e.target.value = ''
  }
}

// ── Datos personales ──────────────────────────────────────────────────────────
const editandoPerfil  = ref(false)
const guardandoPerfil = ref(false)
const perfilForm = ref({ nombres: '', apellidos: '', num_documento: '', telefono: '', ciudad: '', direccion: '' })

const camposVista = computed(() => {
  const p = authStore.usuario?.persona
  return [
    { label: 'NOMBRES',    valor: p?.nombres },
    { label: 'APELLIDOS',  valor: p?.apellidos },
    { label: 'DOCUMENTO',  valor: p?.num_documento },
    { label: 'TELÉFONO',   valor: p?.telefono },
    { label: 'CIUDAD',     valor: p?.ciudad },
    { label: 'DIRECCIÓN',  valor: p?.direccion },
  ]
})

const cancelarEdicion = () => {
  editandoPerfil.value = false
  const p = authStore.usuario?.persona
  perfilForm.value = {
    nombres:       p?.nombres || '',
    apellidos:     p?.apellidos || '',
    num_documento: p?.num_documento || '',
    telefono:      p?.telefono || '',
    ciudad:        p?.ciudad || '',
    direccion:     p?.direccion || '',
  }
}

const guardarPerfil = async () => {
  guardandoPerfil.value = true
  try {
    const { data } = await api.put('/auth/perfil', perfilForm.value)
    authStore.usuario = data.data
    localStorage.setItem('rac141_usuario', JSON.stringify(data.data))
    editandoPerfil.value = false
    $q.notify({ color: 'emerald', icon: 'verified', message: 'Datos actualizados correctamente.', textColor: 'dark' })
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.mensaje || 'Error al guardar.' })
  } finally {
    guardandoPerfil.value = false
  }
}

// ── Contraseña ────────────────────────────────────────────────────────────────
const guardandoPass = ref(false)
const passForm  = ref({ password_actual: '', password_nuevo: '', password_nuevo_confirmation: '' })
const verP1 = ref(false)
const verP2 = ref(false)
const verP3 = ref(false)

const cambiarPassword = async () => {
  guardandoPass.value = true
  try {
    await api.post('/auth/change-password', passForm.value)
    $q.notify({ color: 'emerald', icon: 'shield', message: 'Contraseña actualizada. Redirigiendo al login...', textColor: 'dark' })
    setTimeout(async () => { await authStore.logout(); router.push('/login') }, 1500)
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.mensaje || 'Error al actualizar contraseña.' })
  } finally {
    guardandoPass.value = false }
}

// ── Helpers ───────────────────────────────────────────────────────────────────
const iniciales = computed(() => {
  const n = authStore.nombre || authStore.usuario?.email || '?'
  return n.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
})

const rolStyle = computed(() => {
  const map = {
    estudiante:    { bg: '#1e40af', accent: '#60a5fa', badgeColor: 'blue-9',    icon: 'school' },
    instructor:    { bg: '#0f5440', accent: '#2dd4bf', badgeColor: 'teal-9',    icon: 'flight' },
    admin:         { bg: '#5b21b6', accent: '#a78bfa', badgeColor: 'deep-purple-9', icon: 'admin_panel_settings' },
    dir_ops:       { bg: '#7c2d12', accent: '#fb923c', badgeColor: 'deep-orange-9', icon: 'manage_accounts' },
    mantenimiento: { bg: '#1e3a5f', accent: '#3b82f6', badgeColor: 'blue-10',   icon: 'build' },
    tesoreria:     { bg: '#14532d', accent: '#22c55e', badgeColor: 'green-9',   icon: 'account_balance' },
    seguridad:     { bg: '#7f1d1d', accent: '#f87171', badgeColor: 'red-9',     icon: 'security' },
  }
  return map[authStore.rol] || { bg: '#A10B13', accent: '#ef4444', badgeColor: 'red-9', icon: 'badge' }
})

onMounted(async () => {
  await authStore.cargarPerfil()
  const p = authStore.usuario?.persona
  perfilForm.value = {
    nombres:       p?.nombres || '',
    apellidos:     p?.apellidos || '',
    num_documento: p?.num_documento || '',
    telefono:      p?.telefono || '',
    ciudad:        p?.ciudad || '',
    direccion:     p?.direccion || '',
  }
})
</script>

<style lang="scss" scoped>
/* ── Carnet ID ── */
.id-card {
  background: linear-gradient(160deg, #0d0f14 0%, #1a0a0c 100%);
  border: 1px solid rgba(161, 11, 19, 0.3);
  position: relative;
}

.id-card-header {
  background: linear-gradient(90deg, #A10B13, #7f0a10);
  border-bottom: 1px solid rgba(255,255,255,0.08);
}

.id-card-body { position: relative; }

/* ── Avatar ── */
.avatar-wrapper {
  position: relative; width: 130px; height: 130px;
  margin: 0 auto; cursor: pointer;

  .id-avatar {
    border: 4px solid rgba(255,255,255,0.08);
    box-shadow: 0 0 40px rgba(161,11,19,0.4);
    position: relative; z-index: 2;
  }

  .avatar-camera-overlay {
    position: absolute; inset: 0; border-radius: 50%; z-index: 5;
    background: rgba(0,0,0,0.6); display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    opacity: 0; transition: opacity 0.2s ease;
  }

  &:hover .avatar-camera-overlay { opacity: 1; }

  .id-badge {
    position: absolute; bottom: 4px; right: 4px;
    border: 2px solid rgba(255,255,255,0.15); z-index: 10;
    padding: 5px;
  }
}

.hologram-ring {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 150px; height: 150px; border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.05);
  border-top: 2px solid #A10B13;
  animation: spin 8s linear infinite; z-index: 1;
}
@keyframes spin { 100% { transform: translate(-50%, -50%) rotate(360deg); } }

/* ── Datos del carnet ── */
.id-data-block {
  background: rgba(0,0,0,0.35);
  border: 1px solid rgba(161,11,19,0.15);
  border-radius: 12px;
}
.id-data-row { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
.id-data-label { font-family: monospace; font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; white-space: nowrap; }
.id-data-val { font-family: monospace; font-size: 12px; color: #e2e8f0; font-weight: 600; text-align: right; }

/* ── Código de barras ── */
.barcode-footer {
  font-size: 22px; color: rgba(255,255,255,0.15); text-align: center;
  padding: 10px; background: rgba(0,0,0,0.3);
  letter-spacing: 3px; font-family: monospace;
}

/* ── Campos de perfil en modo vista ── */
.perfil-field {
  padding: 12px 0;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.perfil-field-label {
  font-family: monospace; font-size: 9px; color: #64748b;
  text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 4px;
}
.perfil-field-val {
  font-size: 14px; color: #e2e8f0; font-weight: 600;
}

/* ── Misc ── */
.pulsate { animation: pulsate 2s infinite; }
@keyframes pulsate { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }

.premium-input-login {
  :deep(.q-field__control) {
    border-radius: 12px !important;
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.06) !important;
    transition: all 0.3s ease;
    &::before, &::after { display: none; }
    &:hover { border-color: rgba(161,11,19,0.4) !important; }
  }
  :deep(.q-field--focused .q-field__control) {
    border-color: rgba(161,11,19,0.7) !important;
  }
}
</style>
