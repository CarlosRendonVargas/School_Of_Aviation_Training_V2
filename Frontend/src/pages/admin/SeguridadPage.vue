<template>
  <q-page class="q-pa-md rac-page">
    <div class="row items-center q-mb-md">
      <q-icon name="admin_panel_settings" size="32px" color="teal-4" class="q-mr-sm icon-teal-glow" />
      <div>
        <h1 class="text-h5 text-white font-head q-my-none">Módulo 01: Seguridad y Usuarios</h1>
        <div class="text-grey-5 font-mono text-caption">Auditoría, Control de Accesos y Gestión de Roles</div>
      </div>
    </div>
    
    <div class="q-mt-md">
      <q-card class="premium-glass-card shadow-12 custom-tabs-card">
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="usuarios" icon="people" label="Gestión de Usuarios" />
          <q-tab name="audit" icon="policy" label="Auditoría RAC 141" />
        </q-tabs>

        <q-separator color="grey-8" />

        <q-tab-panels v-model="tab" animated class="bg-transparent text-white">
          <!-- USUARIOS PANEL -->
          <q-tab-panel name="usuarios">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Usuarios del Sistema</div>
                <q-btn color="teal-4" icon="person_add" label="Nuevo Usuario" outline rounded class="glass-btn" @click="dialogUsuario = true" />
            </div>

            <q-table
              :rows="usuarios"
              :columns="columnsUsuarios"
              row-key="id"
              class="rac-table bg-transparent text-white border-teal-left"
              flat
              bordered
              table-header-class="text-grey-4"
              :loading="loadingUsuarios"
            >
              <template v-slot:body-cell-activo="props">
                <q-td :props="props">
                  <q-toggle
                    v-model="props.row.activo"
                    color="teal-4"
                    checked-icon="check"
                    unchecked-icon="clear"
                    @update:model-value="toggleStatus(props.row)"
                  />
                  <span :class="props.row.activo ? 'text-teal-4' : 'text-red-4'" class="q-ml-sm" style="font-size:11px">
                    {{ props.row.activo ? 'ACTIVO' : 'SUSPENDIDO' }}
                  </span>
                </q-td>
              </template>
              <template v-slot:body-cell-rol="props">
                <q-td :props="props">
                  <q-chip :style="`background: ${getColorPorRol(props.row.rol?.nombre)}`" text-color="white" size="sm" class="font-mono shadow-3">
                    {{ props.row.rol?.nombre.toUpperCase() }}
                  </q-chip>
                </q-td>
              </template>
              <template v-slot:body-cell-nombre="props">
                <q-td :props="props" class="text-weight-bold">
                  {{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}
                  <div class="text-caption text-grey-6">{{ props.row.email }}</div>
                </q-td>
              </template>
              <template v-slot:body-cell-acciones="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="blue-4" icon="edit" size="sm" @click="abrirEditar(props.row)" tooltip="Editar Datos" />
                  <q-btn flat round color="purple-4" icon="vpn_key" size="sm" @click="abrirClave(props.row)" tooltip="Restablecer Clave" />
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>

          <!-- AUDIT PANEL -->
          <q-tab-panel name="audit">
            <div class="row justify-between items-center q-mb-md">
                <div class="text-h6 font-head">Registro de Auditoría RAC 141.77</div>
            </div>
            <q-table
              :rows="auditorias"
              :columns="columnsAudit"
              row-key="id"
              class="rac-table bg-transparent text-white"
              flat
              bordered
              table-header-class="text-grey-4"
              :loading="loadingAudit"
            >
              <template v-slot:body-cell-fecha="props">
                <q-td :props="props">{{ new Date(props.row.created_at).toLocaleString() }}</q-td>
              </template>
              <template v-slot:body-cell-usuario="props">
                <q-td :props="props" class="text-weight-bold text-teal-3">
                  {{ props.row.usuario?.persona?.nombres || props.row.usuario?.email || 'Sistema' }}
                </q-td>
              </template>
              <template v-slot:body-cell-accion="props">
                <q-td :props="props">
                  <q-chip :color="props.row.accion === 'CREATE' ? 'green-8' : (props.row.accion === 'UPDATE' ? 'blue-8' : 'red-8')" text-color="white" size="sm">
                    {{ props.row.accion }}
                  </q-chip>
                </q-td>
              </template>
            </q-table>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>

    <!-- Modal Crear Usuario -->
    <q-dialog v-model="dialogUsuario" persistent>
      <q-card style="width: 500px; max-width: 90vw;" class="bg-dark text-white border-teal-left">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Registrar Nuevo Usuario</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="guardarUsuario" class="q-gutter-md">
            <div class="row" style="gap: 16px">
                <q-input v-model="formUsu.nombres" label="Nombres" outlined dark color="teal" class="col" />
                <q-input v-model="formUsu.apellidos" label="Apellidos" outlined dark color="teal" class="col" />
            </div>
            
            <div class="row" style="gap: 16px">
                <q-input v-model="formUsu.num_documento" label="Cédula" outlined dark color="teal" class="col" />
                <q-input v-model="formUsu.telefono" label="Teléfono / Celular" type="tel" outlined dark color="teal" class="col" />
            </div>
            
            <q-input v-model="formUsu.email" label="Correo Electrónico" type="email" outlined dark color="teal" />
            <q-input v-model="formUsu.password" label="Contraseña" type="password" outlined dark color="teal" />
            
            <q-select 
              v-model="formUsu.rol_id" 
              :options="roles" 
              option-value="id" 
              option-label="nombre" 
              emit-value 
              map-options
              label="Perfil RAC" 
              outlined 
              dark 
              color="teal" 
            />

            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Crear Cuenta" color="teal-4" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
    <!-- Modal Editar Usuario -->
    <q-dialog v-model="dialogEditar" persistent>
      <q-card style="width: 500px; max-width: 90vw;" class="bg-dark text-white border-blue-left">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Modificar Usuario</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <q-form @submit="actualizarUsuario" class="q-gutter-md">
            <q-input v-model="formUsuEdit.nombres" label="Nombres" outlined dark color="blue" />
            <q-input v-model="formUsuEdit.apellidos" label="Apellidos" outlined dark color="blue" />
            <q-input v-model="formUsuEdit.num_documento" label="Cédula" outlined dark color="blue" />
            <q-input v-model="formUsuEdit.telefono" label="Teléfono / Celular" type="tel" outlined dark color="blue" />
            <q-select v-model="formUsuEdit.rol_id" :options="roles" option-value="id" option-label="nombre" emit-value map-options label="Perfil RAC" outlined dark color="blue" />
            <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Actualizar" color="blue-4" class="q-ml-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- Modal Restablecer Clave -->
    <q-dialog v-model="dialogClave" persistent>
      <q-card style="width: 400px; max-width: 90vw;" class="bg-dark text-white border-purple-left">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 font-head">Restablecer Contraseña</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section>
          <p class="text-grey-4">Confirma la nueva contraseña para <strong>{{ usuClaveTemp?.persona?.nombres }}</strong>.</p>
          <q-form @submit="restablecerClave" class="q-gutter-md">
             <q-input v-model="formClave" label="Nueva Contraseña" type="password" outlined dark color="purple" autofocus />
             <div class="row justify-end q-mt-md">
              <q-btn flat label="Cancelar" color="grey-5" v-close-popup />
              <q-btn type="submit" label="Guardar Clave" color="purple-4" class="q-ml-sm" />
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
  { name: 'id', label: 'ID', align: 'left', field: 'id', sortable: true },
  { name: 'nombre', label: 'Nombre / Correo', align: 'left' },
  { name: 'rol', label: 'Perfil de Acceso', align: 'left' },
  { name: 'ultimo_acceso', label: 'Últ. Acceso', align: 'left', field: 'ultimo_acceso' },
  { name: 'activo', label: 'Estado', align: 'left' },
  { name: 'acciones', label: 'Acciones', align: 'right' }
]

const columnsAudit = [
  { name: 'id', label: 'Log ID', align: 'left', field: 'id' },
  { name: 'fecha', label: 'Fecha de Registro', align: 'left' },
  { name: 'usuario', label: 'Responsable', align: 'left' },
  { name: 'accion', label: 'Operación', align: 'left' },
  { name: 'tabla', label: 'Módulo / Tabla', align: 'left', field: 'tabla' },
  { name: 'registro', label: 'ID Afectado', align: 'left', field: 'registro_id' }
]

const getColorPorRol = (rolName) => {
    switch (rolName) {
        case 'admin': return '#8b5cf6'
        case 'dir_ops': return '#fbbf24'
        case 'instructor': return '#3b82f6'
        case 'estudiante': return '#10b981'
        case 'mantenimiento': return '#ec4899'
        case 'auditor_uaeac': return '#ef4444'
        default: return '#6b7280'
    }
}

const cargarUsuarios = async () => {
    loadingUsuarios.value = true
    try {
        const { data } = await api.get('/usuarios')
        usuarios.value = data.data || []
    } catch (e) {
        $q.notify({ color: 'negative', message: 'Error cargando usuarios' })
    } finally { loadingUsuarios.value = false }
}

const cargarAuditorias = async () => {
    loadingAudit.value = true
    try {
        const { data } = await api.get('/auditoria')
        auditorias.value = data.data || []
    } catch (e) {
        console.error(e)
    } finally { loadingAudit.value = false }
}

const cargarRoles = async () => {
    try {
        const { data } = await api.get('/usuarios/roles')
        roles.value = data.data || []
    } catch (e) {}
}

const toggleStatus = async (usuario) => {
    try {
        await api.put(`/usuarios/${usuario.id}`, { activo: usuario.activo })
        $q.notify({ color: 'positive', message: 'Estado actualizado correctamente' })
    } catch(e) {
        usuario.activo = !usuario.activo // revert
        $q.notify({ color: 'negative', message: 'Error al actualizar.' })
    }
}

const guardarUsuario = async () => {
    try {
        const { data } = await api.post('/usuarios', formUsu.value)
        usuarios.value.push(data.data)
        $q.notify({ color: 'positive', message: 'Usuario creado exitosamente' })
        dialogUsuario.value = false
        formUsu.value = { nombres: '', apellidos: '', num_documento: '', email: '', password: '', rol_id: null, telefono: '' }
    } catch(e) {
        $q.notify({ color: 'negative', message: 'Error al crear usuario. Verifica los datos.' })
    }
}

const abrirEditar = (row) => {
    formUsuEdit.value = { 
        id: row.id,
        nombres: row.persona?.nombres,
        apellidos: row.persona?.apellidos,
        num_documento: row.persona?.num_documento,
        telefono: row.persona?.telefono,
        rol_id: row.rol_id
    }
    dialogEditar.value = true
}

const actualizarUsuario = async () => {
    try {
        const { data } = await api.put(`/usuarios/${formUsuEdit.value.id}`, formUsuEdit.value)
        const idx = usuarios.value.findIndex(u => u.id === data.data.id)
        if(idx !== -1) usuarios.value[idx] = data.data
        $q.notify({ color: 'positive', message: 'Usuario actualizado correctamente' })
        dialogEditar.value = false
    } catch(e) {
        $q.notify({ color: 'negative', message: 'Error al actualizar usuario.' })
    }
}

const abrirClave = (row) => {
    usuClaveTemp.value = row
    formClave.value = 'Rac141*2025' // Sugerir la estándar
    dialogClave.value = true
}

const restablecerClave = async () => {
    try {
        await api.put(`/usuarios/${usuClaveTemp.value.id}/password`, { password: formClave.value })
        $q.notify({ color: 'positive', message: 'Contraseña restablecida con éxito' })
        dialogClave.value = false
    } catch(e) {
         $q.notify({ color: 'negative', message: 'Error al restablecer la contraseña.' })
    }
}

onMounted(() => {
    cargarUsuarios()
    cargarAuditorias()
    cargarRoles()
})

</script>

<style lang="scss" scoped>
.rac-page { background: #0a0c10; min-height: 100vh; }
.font-head { font-family: 'Syne', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }
.icon-teal-glow { filter: drop-shadow(0 0 10px rgba(45, 212, 191, 0.7)); }

.premium-glass-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 12px;
}

.custom-tabs-card {
  overflow: hidden;
}

.border-teal-left { border-left: 2px solid #2dd4bf; }
.border-blue-left { border-left: 2px solid #60a5fa; }
.border-purple-left { border-left: 2px solid #a855f7; }

::v-deep(.q-table__card) {
  background-color: transparent !important;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
::v-deep(.q-table th) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-family: 'JetBrains Mono', monospace;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.05em;
}
::v-deep(.q-table td) {
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.glass-btn {
  background: rgba(255,255,255, 0.05);
  backdrop-filter: blur(4px);
  transition: all 0.3s ease;
  &:hover {
    background: rgba(45, 212, 191, 0.2);
    transform: translateY(-2px);
  }
}
</style>
