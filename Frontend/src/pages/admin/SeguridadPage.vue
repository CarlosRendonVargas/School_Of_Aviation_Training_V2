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
        <q-tab name="usuarios"  icon="people"               label="Directorio de Usuarios" />
        <q-tab name="perfiles"  icon="badge"                label="Perfiles de Acceso" />
        <q-tab name="audit"     icon="history"              label="Registros de Auditoría (Logs)" />
        <q-tab name="modulos"   icon="tune"                 label="Control de Módulos" />
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
            :grid="$q.screen.lt.md"
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

            <template v-slot:body-cell-ultimo_acceso="props">
              <q-td :props="props" class="font-mono" style="font-size:12px">
                <template v-if="props.row.ultimo_acceso">
                  <span class="text-grey-4">{{ formatTimestampCO(props.row.ultimo_acceso) }}</span>
                </template>
                <template v-else>
                  <span class="text-grey-6">Alta: {{ formatTimestampCO(props.row.created_at, 'DD/MM/YYYY') }}</span>
                  <q-badge color="grey-8" label="Sin acceso" class="q-ml-xs font-mono" style="font-size:8px" />
                </template>
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
                <q-btn flat round color="red-10" icon="delete_forever" size="sm" @click="eliminarUsuario(props.row)">
                  <q-tooltip>Eliminar Usuario</q-tooltip>
                </q-btn>
              </q-td>
            </template>

            <!-- Modo Grid (Cards) para móviles -->
            <template v-slot:item="props">
              <div class="q-pa-xs col-12 grid-style-transition">
                <q-card class="premium-glass-card border-red-low shadow-24 q-pa-sm q-mb-sm">
                  <q-card-section>
                    <div class="row justify-between items-center q-mb-sm">
                      <q-chip outline :style="`border-color: ${getColorPorRol(props.row.rol?.nombre)}; color: ${getColorPorRol(props.row.rol?.nombre)}`" size="sm" class="font-mono text-weight-bolder">
                        {{ props.row.rol?.nombre.toUpperCase() }}
                      </q-chip>
                      <q-toggle v-model="props.row.activo" color="red-9" keep-color size="sm" @update:model-value="toggleStatus(props.row)" />
                    </div>
                    <div class="text-weight-bold text-white text-h6" style="line-height:1.2">{{ props.row.persona?.nombres }} {{ props.row.persona?.apellidos }}</div>
                    <div class="text-caption text-grey-5 font-mono q-mb-md">{{ props.row.email }}</div>

                    <q-separator dark class="opacity-5" />

                    <div class="row justify-between items-center q-mt-md">
                       <q-badge :color="props.row.activo ? 'emerald' : 'red-10'" class="text-weight-bold" style="font-size:9px">{{ props.row.activo ? 'ACTIVE' : 'LOCKED' }}</q-badge>
                       <div class="q-gutter-x-xs">
                          <q-btn flat round outline color="red-9" icon="edit_note" size="sm" @click="abrirEditar(props.row)" />
                          <q-btn flat round outline color="white" icon="key" size="sm" @click="abrirClave(props.row)" />
                          <q-btn flat round outline color="red-10" icon="delete_forever" size="sm" @click="eliminarUsuario(props.row)" />
                       </div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ PERFILES DE ACCESO ════ -->
        <q-tab-panel name="perfiles" class="q-pa-lg">
          <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center">
              <q-icon name="badge" color="red-9" size="20px" class="q-mr-sm" />
              <span class="font-mono text-grey-5" style="font-size:12px">PERFILES DE ACCESO Y AUTORIZACIÓN RAC</span>
            </div>
            <q-btn color="red-9" icon="add" label="Nuevo Perfil" class="premium-btn" size="sm" @click="abrirNuevoRol" />
          </div>

          <q-table
            :rows="rolesTabla"
            :columns="columnsRoles"
            row-key="id"
            class="rac-table"
            flat
            :loading="loadingRoles"
          >
            <template v-slot:body-cell-nombre="props">
              <q-td :props="props">
                <q-chip outline :style="`border-color: ${getColorPorRol(props.row.nombre)}; color: ${getColorPorRol(props.row.nombre)}`"
                  size="sm" class="font-mono text-weight-bolder">
                  {{ props.row.nombre.toUpperCase() }}
                </q-chip>
              </q-td>
            </template>
            <template v-slot:body-cell-descripcion="props">
              <q-td :props="props" class="text-grey-4" style="font-size:12px">
                {{ props.row.descripcion || '—' }}
              </q-td>
            </template>
            <template v-slot:body-cell-usuarios_count="props">
              <q-td :props="props" class="text-center">
                <q-badge :color="props.row.usuarios_count > 0 ? 'blue-9' : 'grey-8'" class="font-mono">
                  {{ props.row.usuarios_count }}
                </q-badge>
              </q-td>
            </template>
            <template v-slot:body-cell-acciones_rol="props">
              <q-td :props="props" class="text-right q-gutter-x-xs">
                <q-btn flat round icon="edit_note" color="red-9" size="sm" @click="abrirEditarRol(props.row)">
                  <q-tooltip>Editar descripción</q-tooltip>
                </q-btn>
                <q-btn flat round icon="delete_forever" color="red-10" size="sm" @click="eliminarRol(props.row)"
                  :disable="props.row.usuarios_count > 0">
                  <q-tooltip>{{ props.row.usuarios_count > 0 ? 'Tiene usuarios asignados' : 'Eliminar perfil' }}</q-tooltip>
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
            :grid="$q.screen.lt.md"
          >
            <template v-slot:body-cell-fecha="props">
              <q-td :props="props" class="font-mono text-grey-5" style="font-size:11px">
                {{ formatTimestampCO(props.row.created_at) }}
              </q-td>
            </template>

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

            <!-- Modo Grid Log Audit -->
            <template v-slot:item="props">
              <div class="q-pa-xs col-12 grid-style-transition">
                <q-card class="bg-black-20 shadow-24 q-pa-sm q-mb-sm" style="border: 1px solid rgba(255,255,255,0.05)">
                  <q-card-section class="q-pa-xs">
                    <div class="row items-center justify-between">
                       <span :class="props.row.accion === 'CREATE' ? 'text-emerald' : (props.row.accion === 'UPDATE' ? 'text-orange-9' : 'text-red-9')" class="text-weight-bolder font-mono">[{{ props.row.accion }}]</span>
                       <span class="font-mono text-grey-5" style="font-size:10px">{{ formatTimestampCO(props.row.created_at) }}</span>
                    </div>
                    <div class="text-weight-bold text-red-7 font-mono q-mt-sm">{{ props.row.usuario?.email?.split('@')[0] || 'SYSTEM' }}</div>
                    <div class="text-caption text-grey-4 q-mt-xs">{{ props.row.detalles || 'Trazabilidad RAC' }}</div>
                    <div class="font-mono text-grey-6 q-mt-xs" style="font-size:9px">[{{ props.row.tabla }}]</div>
                  </q-card-section>
                </q-card>
              </div>
            </template>
          </q-table>
        </q-tab-panel>

        <!-- ════ CONTROL DE MÓDULOS ════ -->
        <q-tab-panel name="modulos" class="q-pa-lg">
          <div class="row items-center justify-between q-mb-lg">
            <div class="row items-center">
              <q-icon name="tune" color="red-9" size="20px" class="q-mr-sm" />
              <span class="font-mono text-grey-5" style="font-size:12px">MATRIZ DE ACCESO A MÓDULOS POR ROL</span>
            </div>
            <div class="row q-gutter-sm">
              <q-btn flat label="Restablecer Defaults" icon="restore" color="grey-5" size="sm" @click="restablecerDefaults" />
              <q-btn label="Guardar Cambios" icon="save" color="red-9" class="premium-btn" :loading="guardandoMatriz" @click="guardarMatriz" />
            </div>
          </div>

          <q-inner-loading :showing="loadingMatriz">
            <q-spinner-orbit color="red-9" size="48px" />
          </q-inner-loading>

          <div v-if="!loadingMatriz" class="matriz-wrapper">
            <!-- Leyenda de roles -->
            <div class="row items-center q-mb-md q-gutter-sm">
              <q-chip v-for="r in rolesOrden" :key="r" size="sm" class="font-mono text-weight-bold"
                :style="`background: ${getColorPorRol(r)}22; border: 1px solid ${getColorPorRol(r)}55; color: ${getColorPorRol(r)}`">
                {{ ROLE_LABELS[r] || r }}
              </q-chip>
              <q-space />
              <div class="row items-center text-grey-6 text-caption font-mono">
                <q-icon name="lock" size="14px" class="q-mr-xs text-amber-6" />
                Celda bloqueada (no modificable)
              </div>
            </div>

            <!-- Tabla de la matriz -->
            <div class="overflow-auto">
              <table class="matriz-tabla full-width">
                <thead>
                  <tr>
                    <th class="text-left text-grey-6 font-mono" style="font-size:11px; padding: 8px 12px; min-width: 180px">MÓDULO</th>
                    <th v-for="r in rolesOrden" :key="r" class="text-center font-mono" style="font-size:11px; padding: 8px; min-width: 80px"
                      :style="`color: ${getColorPorRol(r)}`">
                      {{ ROLE_LABELS[r] || r }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="grupo in GRUPOS" :key="grupo.label">
                    <!-- Separador de sección -->
                    <tr class="grupo-separador">
                      <td :colspan="rolesOrden.length + 1" class="font-mono text-grey-7 text-uppercase q-px-md q-py-xs" style="font-size: 10px; letter-spacing: 1px; background: rgba(255,255,255,0.03)">
                        {{ grupo.label }}
                      </td>
                    </tr>
                    <!-- Filas de módulos -->
                    <tr v-for="mod in grupo.modulos" :key="mod.key" class="modulo-fila">
                      <td class="q-px-md q-py-sm">
                        <div class="text-white text-weight-medium" style="font-size:13px">{{ mod.label }}</div>
                        <div class="font-mono text-grey-7" style="font-size:9px">{{ mod.key }}</div>
                      </td>
                      <td v-for="r in rolesOrden" :key="r" class="text-center q-py-sm">
                        <template v-if="esBloqueado(mod.key, r)">
                          <q-icon name="lock" color="amber-6" size="18px">
                            <q-tooltip>Acceso permanente — no se puede deshabilitar</q-tooltip>
                          </q-icon>
                        </template>
                        <template v-else>
                          <q-toggle
                            v-model="matrizLocal[mod.key][r]"
                            color="red-9"
                            keep-color
                            dense
                            size="sm"
                          />
                        </template>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <div class="q-mt-lg row justify-end">
              <q-btn label="Guardar Cambios" icon="save" color="red-9" class="premium-btn" :loading="guardandoMatriz" @click="guardarMatriz" />
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- ══ DIALOGS ══ -->
    <q-dialog v-model="dialogUsuario" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24" style="width: 500px; max-width: 90vw;">
        <q-card-section class="q-pa-lg">
          <div class="text-h6 font-head text-white q-mb-lg">Apertura de Cuenta Nueva</div>
          <q-form @submit="guardarUsuario" class="row q-col-gutter-md">
            <div class="col-6"><q-input v-model="formUsu.nombres" label="Nombres *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.apellidos" label="Apellidos *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.num_documento" label="Cédula/ID *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsu.telefono" label="Contacto *" outlined /></div>
            <div class="col-12"><q-input v-model="formUsu.email" label="Correo Corporativo *" outlined /></div>
            <div class="col-6">
              <q-input v-model="formUsu.password" label="Contraseña Temporal *" :type="verP1 ? 'text' : 'password'" outlined>
                <template #append>
                  <q-icon :name="verP1 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP1 = !verP1" />
                </template>
              </q-input>
            </div>

            <div class="col-6">
              <q-select v-model="formUsu.rol_id" :options="roles" option-value="id" emit-value map-options label="Perfil RAC *" outlined
                :option-label="r => r.nombre?.toUpperCase()">
                <template #selected-item="s">
                  <span class="text-white font-mono text-weight-bold">{{ s.opt.nombre?.toUpperCase() }}</span>
                </template>
              </q-select>
            </div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Activar Usuario" color="red-9" class="premium-btn" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogClave" persistent backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card shadow-24 border-red-left" style="width: 400px; max-width: 90vw;">
        <q-card-section class="q-pa-lg text-white text-center">
          <q-icon name="lock_reset" size="64px" color="red-9" class="q-mb-md" />
          <div class="text-h6 font-head">Seguridad de Acceso</div>
          <div class="text-caption text-grey-5 q-mb-xl">Restablecer clave para {{ usuClaveTemp?.persona?.nombres }}</div>
          <q-form @submit="restablecerClave" class="q-gutter-md text-left">
             <q-input v-model="formClave" label="Nueva Contraseña Maestra *" :type="verP2 ? 'text' : 'password'" outlined autofocus>
                <template #append>
                  <q-icon :name="verP2 ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="verP2 = !verP2" />
                </template>
             </q-input>

             <div class="row justify-center q-mt-lg">
              <q-btn type="submit" label="Actualizar Credenciales" color="red-9" class="premium-btn full-width" />
              <q-btn flat label="Volver" color="grey-6" v-close-popup class="q-mt-sm" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogEditar" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-left" style="width: 500px; max-width: 90vw;">
        <q-card-section class="q-pa-lg">
          <div class="text-h6 font-head text-white q-mb-lg">Modificar Perfil Operacional</div>
          <q-form @submit="guardarEdicion" class="row q-col-gutter-md">
            <div class="col-6"><q-input v-model="formUsuEdit.nombres" label="Nombres *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsuEdit.apellidos" label="Apellidos *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsuEdit.num_documento" label="Cédula/ID *" outlined /></div>
            <div class="col-6"><q-input v-model="formUsuEdit.telefono" label="Contacto *" outlined /></div>
            <div class="col-12">
              <q-select v-model="formUsuEdit.rol_id" :options="roles" option-value="id" emit-value map-options label="Perfil RAC *" outlined
                :option-label="r => r.nombre?.toUpperCase()">
                <template #selected-item="s">
                  <span class="text-white font-mono text-weight-bold">{{ s.opt.nombre?.toUpperCase() }}</span>
                </template>
              </q-select>
            </div>
            <div class="col-12 row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" label="Aplicar Cambios" color="red-9" class="premium-btn text-weight-bolder" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <!-- ═══ DIALOG ROL ═══ -->
    <q-dialog v-model="dialogRol" persistent backdrop-filter="blur(15px)">
      <q-card class="premium-glass-card shadow-24 border-red-top rounded-20" style="width:min(480px,95vw)">
        <q-card-section class="q-pa-lg">
          <div class="text-h6 font-head text-white q-mb-lg">
            {{ modoRol === 'crear' ? 'Nuevo Perfil de Acceso' : 'Editar Perfil' }}
          </div>
          <q-form @submit="guardarRol" class="q-gutter-y-md">
            <q-input v-if="modoRol === 'crear'" v-model="formRol.nombre" label="NOMBRE DEL PERFIL *"
              outlined dark filled class="premium-input-login"
              hint="Solo letras y guiones bajos. Ej: contador, dir_tecnico"
              :rules="[v => !!v || 'Requerido']" />
            <div v-else class="font-mono text-grey-5 q-mb-sm" style="font-size:11px">
              PERFIL: <span class="text-white text-weight-bold">{{ formRol.nombre?.toUpperCase() }}</span>
              <span class="text-grey-7 q-ml-sm">(el nombre no se puede cambiar)</span>
            </div>
            <q-input v-model="formRol.descripcion" label="DESCRIPCIÓN / CARGO" outlined dark filled
              class="premium-input-login" type="textarea" rows="2"
              hint="Descripción opcional del perfil (ej: Director de Operaciones de Vuelo)" />
            <div class="row justify-end q-gutter-sm q-mt-md">
              <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
              <q-btn type="submit" :label="modoRol === 'crear' ? 'Crear Perfil' : 'Guardar'" color="red-9" class="premium-btn" :loading="guardandoRol" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { formatTimestampCO } from 'src/utils/formatters'
import { useAuthStore } from 'store/auth'

const authStore = useAuthStore()

const $q = useQuasar()
const tab = ref('usuarios')

// ── Usuarios ──────────────────────────────────────────────────────────────
const usuarios = ref([])
const auditorias = ref([])
const roles = ref([])
const loadingUsuarios = ref(false)
const loadingAudit = ref(false)
const dialogUsuario = ref(false)
const dialogEditar = ref(false)
const dialogClave = ref(false)

// ── Roles ─────────────────────────────────────────────────────────────────────
const rolesTabla    = ref([])
const loadingRoles  = ref(false)
const dialogRol     = ref(false)
const modoRol       = ref('crear')
const guardandoRol  = ref(false)
const formRol       = ref({ id: null, nombre: '', descripcion: '' })

const columnsRoles = [
  { name: 'nombre',         label: 'PERFIL RAC',       align: 'left' },
  { name: 'descripcion',    label: 'DESCRIPCIÓN',      align: 'left' },
  { name: 'usuarios_count', label: 'USUARIOS',         align: 'center' },
  { name: 'acciones_rol',   label: 'ACCIONES',         align: 'right' },
]

const formUsu = ref({ nombres: '', apellidos: '', num_documento: '', email: '', password: '', rol_id: null, telefono: '' })
const formUsuEdit = ref({ id: null, nombres: '', apellidos: '', num_documento: '', telefono: '', rol_id: null })
const formClave = ref('')
const usuClaveTemp = ref(null)
const verP1 = ref(false)
const verP2 = ref(false)

// ── Módulos ───────────────────────────────────────────────────────────────
const loadingMatriz  = ref(false)
const guardandoMatriz = ref(false)
const matrizLocal    = reactive({})
const rolesOrden     = ref([])
const bloqueados     = ref({})

const ROLE_LABELS = {
  admin:         'Admin',
  dir_ops:       'Dir. Ops',
  instructor:    'Instructor',
  estudiante:    'Estudiante',
  mantenimiento: 'Mant.',
  auditor_uaeac: 'Auditor',
}

const GRUPOS = [
  {
    label: 'General',
    modulos: [
      { key: 'dashboard',    label: 'Dashboard' },
      { key: 'vencimientos', label: 'Alertas RAC' },
      { key: 'mensajes',     label: 'Mensajes' },
      { key: 'normatividad', label: 'Normatividad' },
    ],
  },
  {
    label: 'Operaciones de Vuelo',
    modulos: [
      { key: 'cronograma', label: 'Mi Cronograma' },
      { key: 'reservas',   label: 'Programar Vuelo' },
      { key: 'calendario', label: 'Calendario' },
      { key: 'vuelo',      label: 'Bitácoras' },
    ],
  },
  {
    label: 'Formación Académica',
    modulos: [
      { key: 'academico',               label: 'Gestión Académica' },
      { key: 'aula-virtual',            label: 'Aula Virtual' },
      { key: 'mi-progreso',             label: 'Mi Progreso' },
      { key: 'estudiantes',             label: 'Estudiantes' },
      { key: 'certificados',            label: 'Certificados' },
      { key: 'endorsements',            label: 'Endorsements' },
      { key: 'evaluaciones-instructor', label: 'Eval. Instructores' },
    ],
  },
  {
    label: 'Seguridad Operacional',
    modulos: [
      { key: 'sms',                label: 'Reportes SMS' },
      { key: 'sms-erg',            label: 'Plan ERG' },
      { key: 'sms-capacitaciones', label: 'Capacitaciones SMS' },
    ],
  },
  {
    label: 'Flota y Mantenimiento',
    modulos: [
      { key: 'aeronaves',     label: 'Aeronaves' },
      { key: 'mantenimiento', label: 'Control MX' },
    ],
  },
  {
    label: 'Cumplimiento UAEAC',
    modulos: [
      { key: 'cumplimiento',                 label: 'Cumplimiento RAC' },
      { key: 'cumplimiento-enmiendas',       label: 'Enmiendas MOE/PIA' },
      { key: 'cumplimiento-correspondencia', label: 'Correspondencia' },
      { key: 'cumplimiento-reportes',        label: 'Reportes UAEAC' },
    ],
  },
  {
    label: 'Administración',
    modulos: [
      { key: 'instructores', label: 'Instructores' },
      { key: 'financiero',   label: 'Financiero' },
      { key: 'matriculas',   label: 'Matrículas' },
      { key: 'facturacion',  label: 'Facturación' },
      { key: 'prospectos',   label: 'CRM Prospectos' },
      { key: 'nomina',       label: 'Nómina' },
      { key: 'gastos',       label: 'Gastos / Caja' },
    ],
  },
  {
    label: 'Configuración',
    modulos: [
      { key: 'seguridad', label: 'Acceso y Logs' },
    ],
  },
]

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
  const map = {
    admin:         '#f43f5e',
    instructor:    '#3b82f6',
    estudiante:    '#10b981',
    dir_ops:       '#fb923c',
    mantenimiento: '#60a5fa',
    auditor_uaeac: '#4ade80',
  }
  return map[rolName] || '#94a3b8'
}

function esBloqueado(modulo, rol) {
  return Array.isArray(bloqueados.value[modulo]) && bloqueados.value[modulo].includes(rol)
}

// ── Carga inicial ─────────────────────────────────────────────────────────
const cargarTodo = async () => {
  loadingUsuarios.value = true
  loadingAudit.value = true
  try {
    const [u, a, r] = await Promise.all([
      api.get('/usuarios'),
      api.get('/auditoria'),
      api.get('/roles')
    ])
    usuarios.value  = u.data.data || []
    auditorias.value = (a.data.data || []).slice(0, 50)
    roles.value     = r.data.data || []
    rolesTabla.value = r.data.data || []
  } finally {
    loadingUsuarios.value = false
    loadingAudit.value    = false
  }
}

const cargarMatriz = async () => {
  loadingMatriz.value = true
  try {
    const { data } = await api.get('/permisos/matrix')
    rolesOrden.value = data.roles || []
    bloqueados.value = data.bloqueados || {}

    // Poblar matrizLocal
    for (const modulo of (data.modulos || [])) {
      if (!matrizLocal[modulo]) matrizLocal[modulo] = {}
      for (const rol of data.roles) {
        matrizLocal[modulo][rol] = data.matrix?.[modulo]?.[rol] ?? false
      }
    }
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar la matriz de permisos.' })
  } finally {
    loadingMatriz.value = false
  }
}

// Cargar la matriz solo cuando el usuario abre la pestaña por primera vez
const matrizCargada = ref(false)
watch(tab, (val) => {
  if (val === 'modulos' && !matrizCargada.value) {
    matrizCargada.value = true
    cargarMatriz()
  }
})

// ── Acciones Usuarios ─────────────────────────────────────────────────────
const toggleStatus = async (usuario) => {
  try {
    await api.put(`/usuarios/${usuario.id}`, { activo: usuario.activo })
    $q.notify({ color: 'emerald', message: 'Configuración de acceso actualizada.', icon: 'verified', textColor: 'dark' })
  } catch {
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

const eliminarUsuario = (row) => {
  const nombre = row.persona ? `${row.persona.nombres} ${row.persona.apellidos}` : row.email
  $q.dialog({
    title: `<span class="text-red-9 font-head">Eliminar: ${nombre}</span>`,
    message: `Esta acción eliminará permanentemente la cuenta <b>${row.email}</b> y todos sus datos personales. No se puede deshacer.`,
    html: true,
    dark: true,
    ok: { color: 'red-10', label: 'ELIMINAR PERMANENTEMENTE', unelevated: true, icon: 'delete_forever' },
    cancel: { label: 'Cancelar', flat: true, color: 'grey-6' }
  }).onOk(async () => {
    try {
      await api.delete(`/usuarios/${row.id}`)
      $q.notify({ color: 'positive', message: 'Usuario eliminado correctamente.', icon: 'check_circle' })
      cargarTodo()
    } catch (e) {
      const msg = e.response?.data?.mensaje || 'Error al eliminar el usuario.'
      $q.notify({ type: 'negative', message: msg })
    }
  })
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

const abrirEditar = (row) => {
  formUsuEdit.value = {
    id: row.id,
    nombres: row.persona?.nombres || '',
    apellidos: row.persona?.apellidos || '',
    num_documento: row.persona?.num_documento || '',
    telefono: row.persona?.telefono || '',
    rol_id: row.rol_id
  }
  dialogEditar.value = true
}

const guardarEdicion = async () => {
  try {
    await api.put(`/usuarios/${formUsuEdit.value.id}`, formUsuEdit.value)
    $q.notify({ color: 'emerald', message: 'Datos operacionales actualizados correctamente.', textColor: 'dark', icon: 'verified' })
    dialogEditar.value = false
    cargarTodo()
    if (formUsuEdit.value.id === authStore.usuario?.id) {
      await authStore.cargarPerfil()
    }
  } catch {
    $q.notify({ type: 'negative', message: 'Error al actualizar cuenta en el servidor.' })
  }
}

// ── Acciones Módulos ──────────────────────────────────────────────────────
const guardarMatriz = async () => {
  guardandoMatriz.value = true
  try {
    await api.put('/permisos/matrix', { matrix: matrizLocal })
    $q.notify({ color: 'emerald', icon: 'verified', textColor: 'dark', message: 'Permisos de módulos guardados. Los cambios aplican en el próximo inicio de sesión.' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar la matriz de permisos.' })
  } finally {
    guardandoMatriz.value = false
  }
}

const restablecerDefaults = () => {
  $q.dialog({
    title: 'Restablecer Defaults',
    message: 'Esto sobreescribirá TODOS los permisos con los valores predeterminados del sistema. ¿Confirmas?',
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { label: 'Sí, restablecer', color: 'red-9' },
    dark: true,
    class: 'premium-glass-card',
  }).onOk(async () => {
    try {
      await api.post('/permisos/reset')
      Object.keys(matrizLocal).forEach(k => delete matrizLocal[k])
      await cargarMatriz()
      $q.notify({ color: 'emerald', icon: 'restore', message: 'Permisos restablecidos a valores predeterminados.', textColor: 'dark' })
    } catch {
      $q.notify({ type: 'negative', message: 'Error al restablecer los permisos.' })
    }
  })
}

// ── CRUD Roles ────────────────────────────────────────────────────────────────
const cargarRoles = async () => {
  loadingRoles.value = true
  try {
    const { data } = await api.get('/roles')
    rolesTabla.value = data.data || []
    roles.value = data.data || []
  } finally {
    loadingRoles.value = false
  }
}

const abrirNuevoRol = () => {
  modoRol.value  = 'crear'
  formRol.value  = { id: null, nombre: '', descripcion: '' }
  dialogRol.value = true
}

const abrirEditarRol = (row) => {
  modoRol.value  = 'editar'
  formRol.value  = { id: row.id, nombre: row.nombre, descripcion: row.descripcion || '' }
  dialogRol.value = true
}

const guardarRol = async () => {
  guardandoRol.value = true
  try {
    if (modoRol.value === 'crear') {
      await api.post('/roles', formRol.value)
      $q.notify({ color: 'positive', message: 'Perfil creado. Configura sus módulos en la pestaña "Control de Módulos".', icon: 'badge' })
    } else {
      await api.put(`/roles/${formRol.value.id}`, { descripcion: formRol.value.descripcion })
      $q.notify({ color: 'emerald', message: 'Perfil actualizado.', textColor: 'dark', icon: 'check_circle' })
    }
    dialogRol.value = false
    await cargarRoles()
    matrizCargada.value = false
  } catch (e) {
    $q.notify({ type: 'negative', message: e.response?.data?.mensaje || 'Error al guardar el perfil.' })
  } finally {
    guardandoRol.value = false
  }
}

const eliminarRol = (row) => {
  $q.dialog({
    title: `<span class="text-red-9">Eliminar perfil: ${row.nombre.toUpperCase()}</span>`,
    message: 'Se eliminarán también todos los permisos de módulos asociados a este perfil. Esta acción no se puede deshacer.',
    html: true, dark: true,
    ok: { color: 'red-10', label: 'ELIMINAR', unelevated: true, icon: 'delete_forever' },
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' }
  }).onOk(async () => {
    try {
      await api.delete(`/roles/${row.id}`)
      $q.notify({ color: 'positive', message: 'Perfil eliminado correctamente.', icon: 'check_circle' })
      await cargarRoles()
      matrizCargada.value = false
    } catch (e) {
      $q.notify({ type: 'negative', message: e.response?.data?.mensaje || 'Error al eliminar.' })
    }
  })
}

// Cargar roles cuando se abre esa pestaña
watch(tab, (val) => {
  if (val === 'perfiles') cargarRoles()
})

onMounted(cargarTodo)
</script>

<style lang="scss" scoped>
.terminal-style {
  font-family: 'JetBrains Mono', monospace;
  background: rgba(0, 0, 0, 0.2) !important;
  border-radius: 8px;
}

.matriz-wrapper {
  overflow-x: auto;
}

.matriz-tabla {
  border-collapse: collapse;
  width: 100%;

  th, td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }

  thead th {
    background: rgba(255, 255, 255, 0.03);
    position: sticky;
    top: 0;
    z-index: 1;
  }

  .grupo-separador td {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }

  .modulo-fila {
    transition: background 0.15s;
    &:hover {
      background: rgba(255, 255, 255, 0.03);
    }
  }
}
</style>
