<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado de Seguridad Premium ══ -->
    <div class="row items-center justify-between q-mb-xl rac-page-header">
      <div class="row items-center">
        <q-icon name="admin_panel_settings" size="48px" color="red-9" class="q-mr-md glow-primary" />
        <div>
          <div class="rac-page-subtitle">MÓDULO 01 · GESTIÓN DE ACCESOS & LOGS</div>
          <h1 class="rac-page-title">Centro de Seguridad</h1>
        </div>
      </div>
      <q-btn color="red-9" icon="person_add" label="Crear Nuevo Acceso" class="premium-btn shadow-10" @click="dialogUsuario = true" />
    </div>

    <!-- ══ Contenedor de Gestión ══ -->
    <q-card class="premium-glass-card">
      <q-tabs
        v-model="tab"
        dense
        class="text-grey-5"
        active-color="red-9"
        indicator-color="red-9"
        align="left"
        no-caps
        style="padding-left: 10px"
      >
        <q-tab name="usuarios" icon="people" label="Directorio de Usuarios" />
        <q-tab name="audit" icon="history" label="Registros de Auditoría (Logs)" />
      </q-tabs>

      <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
        
        <!-- ════ DIRECTORIO DE USUARIOS ════ -->
        <q-tab-panel name="usuarios" class="q-pa-lg">
          <q-table
            :rows="usuarios"
            :columns="columnsUsuarios"
            row-key="id"
            class="rac-table"
            flat
            :loading="loadingUsuarios"
          >
            <template v-slot:body-cell-activo="props">
              <q-td :props="props">
                <q-toggle
                  v-model="props.row.activo"
                  color="red-9"
                  keep-color
                  @update:model-value="toggleStatus(props.row)"
                />
                <q-badge :color="props.row.activo ? 'emerald' : 'red-10'" class="text-weight-bold" style="font-size:9px">
                  {{ props.row.activo ? 'ACTIVE' : 'LOCKED' }}
                </q-badge>
              </q-td>
            </template>
            
            <template v-slot:body-cell-rol="props">
              <q-td :props="props">
                <q-chip outline :style="`border-color: ${getColorPorRol(props.row.rol?.nombre)}; color: ${getColorPorRol(props.row.rol?.nombre)}`" size="sm" class="font-mono text-weight-bolder">
                  {{ props.row.rol?.nombre.toUpperCase() }}
                </q-chip>
              </q-td>
            </template>

            <template v-slot:body-cell-nombre="props">
              <q-td :props="props">
                <div class="text-weight-bold text-white">{{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}</div>
                <div class="text-caption text-grey-6 font-mono">{{ props.row.email }}</div>
              </q-td>
            </template>

            <template v-slot:body-cell-acciones="props">
              <q-td :props="props" class="text-right q-gutter-x-sm">
                <q-btn flat round color="red-9" icon="edit_note" size="sm" @click="abrirEditar(props.row)">
                  <q-tooltip>Editar Perfil</q-tooltip>
                </q-btn>
                <q-btn flat round color="white" icon="key" size="sm" @click="abrirClave(props.row)">
                  <q-tooltip>Modificar Credenciales</q-tooltip>
                </q-btn>
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ AUDITORÍA (LOGS TERMINAL) ════ -->
        <q-tab-panel name="audit" class="q-pa-lg">
          <div class="row items-center q-mb-md">
            <q-icon name="terminal" color="red-9" size="20px" class="q-mr-sm" />
            <span class="font-mono text-grey-5" style="font-size:12px">REGISTROS CRÍTICOS RAC 141.77</span>
          </div>
          
          <q-table
            :rows="auditorias"
            :columns="columnsAudit"
            row-key="id"
            class="rac-table terminal-style"
            flat
            :loading="loadingAudit"
          >
            <template v-slot:body-cell-usuario="props">
              <q-td :props="props" class="text-weight-bold text-red-7 font-mono">
                {{ props.row.usuario?.email?.split('@')[0] || 'SYSTEM' }}
              </q-td>
            </template>
            <template v-slot:body-cell-accion="props">
              <q-td :props="props">
                <span :class="props.row.accion === 'CREATE' ? 'text-emerald' : (props.row.accion === 'UPDATE' ? 'text-orange-9' : 'text-red-9')" class="text-weight-bolder font-mono">
                  [{{ props.row.accion }}]
                </span>
              </q-td>
            </template>
          </q-table>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- ══ DIALOGS ══ -->
    <q-dialog v-model="dialogUsuario" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24" style="min-width: 500px">
        <q-card-section class="q-pa-lg">
          <div class="text-h6 font-head text-white q-mb-lg">Apertura de Cuenta Nueva</div>
          <q-form @submit="guardarUsuario" class="row q-col-gutter-md">
            <div class="col-6"><q-input v-model="formUsu.nombres" label="Nombres *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.apellidos" label="Apellidos *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.num_documento" label="Cédula/ID *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.telefono" label="Contacto *" outlined /></div>
            <div class="col-12"><q-input v-model="formUsu.email" label="Correo Corporativo *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.password" label="Contraseña Temporal *" type="password" outlined /></div>
            <div class="col-6"><q-select v-model="formUsu.rol_id" :options="roles" option-value="id" option-label="nombre" emit-value map-options label="Perfil RAC *" outlined /></div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Activar Usuario" color="red-9" class="premium-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogClave" persistent backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card shadow-24 border-red-left" style="min-width: 400px">
        <q-card-section class="q-pa-lg text-white text-center">
          <q-icon name="lock_reset" size="64px" color="red-9" class="q-mb-md" />
          <div class="text-h6 font-head">Seguridad de Acceso</div>
          <div class="text-caption text-grey-5 q-mb-xl">Restablecer clave para {{ usuClaveTemp?.persona?.nombres }}</div>
          <q-form @submit="restablecerClave" class="q-gutter-md text-left">
             <q-input v-model="formClave" label="Nueva Contraseña Maestra *" type="password" outlined autofocus />
             <div class="row justify-center q-mt-lg">
              <q-btn type="submit" label="Actualizar Credenciales" color="red-9" class="premium-btn full-width" />
              <q-btn flat label="Volver" color="grey-6" v-close-popup class="q-mt-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const tab = ref('usuarios')

const usuarios = ref([])
const auditorias = ref([])
const roles = ref([])
const loadingUsuarios = ref(false)
const loadingAudit = ref(false)
const dialogUsuario = ref(false)
const dialogEditar = ref(false)
const dialogClave = ref(false)

const formUsu = ref({ nombres: '', apellidos: '', num_documento: '', email: '', password: '', rol_id: null, telefono: '' })
const formUsuEdit = ref({ id: null, nombres: '', apellidos: '', num_documento: '', telefono: '', rol_id: null })
const formClave = ref('')
const usuClaveTemp = ref(null)

const columnsUsuarios = [
  { name: 'nombre', label: 'Funcionario / ID', align: 'left' },
  { name: 'rol', label: 'Permisos RAC', align: 'left' },
  { name: 'ultimo_acceso', label: 'Sincronización', align: 'left', field: 'ultimo_acceso' },
  { name: 'activo', label: 'Estado Seguridad', align: 'left' },
  { name: 'acciones', label: 'Auditoría Persona', align: 'right' }
]

const columnsAudit = [
  { name: 'fecha', label: 'Timestamp', align: 'left' },
  { name: 'usuario', label: 'Operador', align: 'left' },
  { name: 'accion', label: 'Sufijo', align: 'left' },
  { name: 'tabla', label: 'Área Afectada', align: 'left', field: 'tabla' },
]

const getColorPorRol = (rolName) => {
    switch (rolName) {
        case 'admin': return '#f43f5e'
        case 'instructor': return '#3b82f6'
        case 'estudiante': return '#10b981'
        default: return '#94a3b8'
    }
}

const cargarTodo = async () => {
    loadingUsuarios.value = true
    loadingAudit.value = true
    try {
        const [u, a, r] = await Promise.all([
            api.get('/usuarios'),
            api.get('/auditoria'),
            api.get('/usuarios/roles')
        ])
        usuarios.value = u.data.data || []
        auditorias.value = (a.data.data || []).slice(0, 50)
        roles.value = r.data.data || []
    } finally {
        loadingUsuarios.value = false
        loadingAudit.value = false
    }
}

const toggleStatus = async (usuario) => {
    try {
        await api.put(`/usuarios/${usuario.id}`, { activo: usuario.activo })
        $q.notify({ color: 'emerald', message: 'Configuración de acceso actualizada.', icon: 'verified', textColor: 'dark' })
    } catch(e) {
        usuario.activo = !usuario.activo
        $q.notify({ type: 'negative', message: 'Error de servidor.' })
    }
}

const guardarUsuario = async () => {
    try {
        await api.post('/usuarios', formUsu.value)
        $q.notify({ type: 'positive', message: 'Cuenta activada correctamente.' })
        dialogUsuario.value = false
        cargarTodo()
    } catch { $q.notify({ type: 'negative', message: 'Error al procesar solicitud.' }) }
}

const abrirClave = (row) => {
    usuClaveTemp.value = row
    formClave.value = ''
    dialogClave.value = true
}

const restablecerClave = async () => {
    try {
        await api.put(`/usuarios/${usuClaveTemp.value.id}/password`, { password: formClave.value })
        $q.notify({ color: 'emerald', message: 'Clave actualizada exitosamente.', textColor: 'dark' })
        dialogClave.value = false
    } catch { $q.notify({ type: 'negative', message: 'Error en el cambio de credenciales.' }) }
}

onMounted(cargarTodo)
</script>

<style lang="scss" scoped>
.animate-fade { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.text-emerald { color: #10b981; }
.terminal-style {
  font-family: 'JetBrains Mono', monospace;
  background: rgba(0, 0, 0, 0.2) !important;
  border-radius: 8px;
}
</style>
