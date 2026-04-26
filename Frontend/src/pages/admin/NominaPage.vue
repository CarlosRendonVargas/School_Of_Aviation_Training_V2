<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">Gestión de Personal · RRHH</div>
        <h1 class="rac-page-title">Nómina <span class="text-red-9">del Personal</span></h1>
      </div>
      <q-btn unelevated color="red-9" icon="add_circle" label="Nuevo Período"
        class="premium-btn" @click="abrirFormPeriodo()" />
    </div>

    <!-- Períodos -->
    <div class="row q-col-gutter-md">
      <!-- Lista de períodos -->
      <div class="col-12 col-md-4">
        <q-card class="premium-glass-card">
          <q-card-section>
            <div class="font-mono text-grey-5 text-uppercase q-mb-md" style="font-size:10px;letter-spacing:1px">Períodos de Nómina</div>
            <div v-if="cargando" class="flex flex-center q-pa-lg"><q-spinner-cube color="red-9" size="30px" /></div>
            <q-list v-else separator>
              <q-item v-for="p in periodos" :key="p.id"
                clickable v-ripple
                :active="periodoSel?.id === p.id" active-class="bg-red-9"
                @click="seleccionarPeriodo(p)" class="q-py-sm border-radius-8 q-mb-xs">
                <q-item-section>
                  <q-item-label class="text-white text-weight-bold font-mono">{{ p.periodo }}</q-item-label>
                  <q-item-label caption class="font-mono text-grey-6" style="font-size:10px">
                    {{ formatFechaCO(p.fecha_inicio) }} → {{ formatFechaCO(p.fecha_fin) }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge :color="estadoColor(p.estado)" class="font-mono" style="font-size:9px">
                    {{ p.estado.toUpperCase() }}
                  </q-badge>
                  <div class="text-green-5 font-mono q-mt-xs" style="font-size:11px">
                    {{ formatCOP(p.total_pagado) }}
                  </div>
                </q-item-section>
              </q-item>
              <q-item v-if="!periodos.length" class="text-center q-pa-lg">
                <q-item-section class="text-grey-7 font-mono" style="font-size:11px">Sin períodos creados</q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Detalle del período -->
      <div class="col-12 col-md-8">
        <q-card class="premium-glass-card">
          <div v-if="!periodoSel" class="flex flex-center column q-pa-xl" style="min-height:400px">
            <q-icon name="account_balance_wallet" size="52px" color="grey-8" />
            <div class="text-grey-7 font-mono q-mt-md" style="font-size:12px">Selecciona un período de nómina</div>
          </div>

          <template v-else>
            <q-card-section class="row items-center justify-between">
              <div>
                <div class="text-h6 text-white text-weight-bold font-head">Período {{ periodoSel.periodo }}</div>
                <div class="font-mono text-grey-6" style="font-size:11px">{{ periodoSel.items_count }} empleados</div>
              </div>
              <div class="row q-gutter-sm">
                <q-btn v-if="periodoSel.estado === 'abierto'"
                  unelevated color="teal-7" icon="person_add" label="Agregar Empleado"
                  size="sm" class="premium-btn" @click="abrirFormItem()" />
                <q-btn v-if="periodoSel.estado === 'abierto'"
                  unelevated color="orange-7" icon="lock" label="Cerrar Período"
                  size="sm" @click="cerrarPeriodo()" />
              </div>
            </q-card-section>

            <q-separator dark />

            <!-- KPIs del período -->
            <div class="row q-pa-md q-col-gutter-md">
              <div class="col-4">
                <div class="text-center">
                  <div class="text-h5 text-green-5 text-weight-bold">{{ formatCOP(totalNeto) }}</div>
                  <div class="font-mono text-grey-6" style="font-size:10px">NETO A PAGAR</div>
                </div>
              </div>
              <div class="col-4">
                <div class="text-center">
                  <div class="text-h5 text-amber-5 text-weight-bold">{{ formatCOP(totalSalarios) }}</div>
                  <div class="font-mono text-grey-6" style="font-size:10px">SALARIOS BASE</div>
                </div>
              </div>
              <div class="col-4">
                <div class="text-center">
                  <div class="text-h5 text-red-5 text-weight-bold">{{ formatCOP(totalDeducciones) }}</div>
                  <div class="font-mono text-grey-6" style="font-size:10px">DEDUCCIONES</div>
                </div>
              </div>
            </div>

            <q-separator dark />

            <div v-if="cargandoItems" class="flex flex-center q-pa-lg"><q-spinner-cube color="red-9" size="30px" /></div>
            <q-table v-else :rows="items" :columns="colsItems" row-key="id" dark flat
              class="bg-transparent" :rows-per-page-options="[15]" no-data-label="Sin empleados en este período">
              <template #body-cell-nombre="{ row }">
                <q-td>
                  <div class="text-white text-weight-medium">{{ row.usuario?.persona?.nombres }} {{ row.usuario?.persona?.apellidos }}</div>
                  <div class="font-mono text-grey-6" style="font-size:10px">{{ row.cargo }}</div>
                </q-td>
              </template>
              <template #body-cell-neto_pagar="{ row }">
                <q-td class="text-green-5 font-mono text-weight-bold">{{ formatCOP(row.neto_pagar) }}</q-td>
              </template>
              <template #body-cell-estado="{ row }">
                <q-td><q-badge :color="row.estado === 'pagado' ? 'green-7' : 'amber-7'" class="font-mono" style="font-size:9px">{{ row.estado.toUpperCase() }}</q-badge></q-td>
              </template>
            </q-table>
          </template>
        </q-card>
      </div>
    </div>

    <!-- Diálogo nuevo período -->
    <q-dialog v-model="dialogPeriodo" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(440px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h5 text-white font-head text-weight-bolder">Nuevo Período de Nómina</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Período (AAAA-MM)</div>
            <q-input v-model="formPeriodo.periodo" dark outlined dense placeholder="2026-04" mask="####-##" />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Fecha Inicio</div>
              <q-input v-model="formPeriodo.fecha_inicio" type="date" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label required">Fecha Fin</div>
              <q-input v-model="formPeriodo.fecha_fin" type="date" dark outlined dense />
            </div>
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" label="Crear Período" class="premium-btn" :loading="guardando" @click="guardarPeriodo" />
        </div>
      </q-card>
    </q-dialog>

    <!-- Diálogo nuevo empleado en nómina -->
    <q-dialog v-model="dialogItem" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(520px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h5 text-white font-head text-weight-bolder">Agregar a Nómina</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Empleado</div>
            <q-select v-model="formItem.usuario_id" :options="usuariosOps" emit-value map-options dark outlined dense use-input input-debounce="0" @filter="filtrarUsuarios" />
          </div>
          <div>
            <div class="campo-label required">Cargo</div>
            <q-input v-model="formItem.cargo" dark outlined dense placeholder="Instructor, Administrativo..." />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label required">Salario Base</div>
              <q-input v-model.number="formItem.salario_base" type="number" dark outlined dense prefix="$" />
            </div>
            <div class="col-6">
              <div class="campo-label">Bonificaciones</div>
              <q-input v-model.number="formItem.bonificaciones" type="number" dark outlined dense prefix="$" />
            </div>
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-6">
              <div class="campo-label">Horas Extra</div>
              <q-input v-model.number="formItem.horas_extra" type="number" dark outlined dense />
            </div>
            <div class="col-6">
              <div class="campo-label">Valor Hora Extra</div>
              <q-input v-model.number="formItem.valor_hora_extra" type="number" dark outlined dense prefix="$" />
            </div>
          </div>
          <div>
            <div class="campo-label">Deducciones adicionales</div>
            <q-input v-model.number="formItem.deducciones" type="number" dark outlined dense prefix="$" />
          </div>
          <div class="q-pa-sm" style="background:rgba(255,255,255,0.04); border-radius:8px">
            <div class="font-mono text-grey-5" style="font-size:10px">NETO A PAGAR (calculado)</div>
            <div class="text-h6 text-green-5 text-weight-bold">{{ formatCOP(netoCalculado) }}</div>
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" label="Agregar a Nómina" class="premium-btn" :loading="guardando" @click="guardarItem" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { formatFechaCO } from 'src/utils/formatters'

const $q = useQuasar()
const cargando = ref(false)
const cargandoItems = ref(false)
const guardando = ref(false)
const periodos = ref([])
const periodoSel = ref(null)
const items = ref([])
const usuarios = ref([])
const usuariosOps = ref([])
const dialogPeriodo = ref(false)
const dialogItem = ref(false)

const formPeriodo = ref({ periodo: '', fecha_inicio: '', fecha_fin: '' })
const formItem = ref({ usuario_id: null, cargo: '', salario_base: 0, bonificaciones: 0, horas_extra: 0, valor_hora_extra: 0, deducciones: 0 })

const estadoColor = e => ({ abierto: 'blue-6', procesado: 'amber-7', pagado: 'green-7' }[e] || 'grey-6')
const formatCOP = v => new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(v || 0)

const totalNeto       = computed(() => items.value.reduce((s, i) => s + +i.neto_pagar, 0))
const totalSalarios   = computed(() => items.value.reduce((s, i) => s + +i.salario_base, 0))
const totalDeducciones= computed(() => items.value.reduce((s, i) => s + +i.salud + +i.pension + +i.deducciones, 0))

const netoCalculado = computed(() => {
  const f = formItem.value
  const salud   = f.salario_base * 0.04
  const pension = f.salario_base * 0.04
  const extra   = f.horas_extra * f.valor_hora_extra
  return f.salario_base + extra + (f.bonificaciones || 0) - (f.deducciones || 0) - salud - pension
})

const colsItems = [
  { name: 'nombre',     label: 'Empleado',      field: 'id',            align: 'left'   },
  { name: 'salario_base',label: 'Salario Base', field: row => formatCOP(row.salario_base), align: 'right' },
  { name: 'bonificaciones',label: 'Bonif.',     field: row => formatCOP(row.bonificaciones), align: 'right' },
  { name: 'salud',      label: 'Salud+Pens',    field: row => formatCOP(+row.salud + +row.pension), align: 'right' },
  { name: 'neto_pagar', label: 'Neto a Pagar',  field: 'neto_pagar',    align: 'right'  },
  { name: 'estado',     label: 'Estado',        field: 'estado',        align: 'center' },
]

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/nomina/periodos')
    periodos.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar períodos.' })
  } finally {
    cargando.value = false
  }
}

async function seleccionarPeriodo(p) {
  periodoSel.value = p
  cargandoItems.value = true
  try {
    const { data } = await api.get(`/nomina/periodos/${p.id}/items`)
    items.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar empleados.' })
  } finally {
    cargandoItems.value = false
  }
}

async function guardarPeriodo() {
  if (!formPeriodo.value.periodo || !formPeriodo.value.fecha_inicio || !formPeriodo.value.fecha_fin) {
    $q.notify({ type: 'warning', message: 'Todos los campos son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    await api.post('/nomina/periodos', formPeriodo.value)
    $q.notify({ type: 'positive', message: 'Período creado.' })
    dialogPeriodo.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al crear período.' })
  } finally {
    guardando.value = false
  }
}

async function abrirFormItem() {
  formItem.value = { usuario_id: null, cargo: '', salario_base: 0, bonificaciones: 0, horas_extra: 0, valor_hora_extra: 0, deducciones: 0 }
  if (!usuarios.value.length) {
    const { data } = await api.get('/mensajes/usuarios')
    usuarios.value   = data.data
    usuariosOps.value = usuarios.value.map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  }
  dialogItem.value = true
}

function filtrarUsuarios(val, update) {
  update(() => {
    const q = val.toLowerCase()
    usuariosOps.value = usuarios.value.filter(u => u.nombre.toLowerCase().includes(q)).map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  })
}

async function guardarItem() {
  if (!formItem.value.usuario_id || !formItem.value.cargo || !formItem.value.salario_base) {
    $q.notify({ type: 'warning', message: 'Empleado, cargo y salario son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    await api.post(`/nomina/periodos/${periodoSel.value.id}/items`, formItem.value)
    $q.notify({ type: 'positive', message: 'Empleado agregado a nómina.' })
    dialogItem.value = false
    await seleccionarPeriodo(periodoSel.value)
  } catch {
    $q.notify({ type: 'negative', message: 'Error al agregar empleado.' })
  } finally {
    guardando.value = false
  }
}

async function cerrarPeriodo() {
  $q.dialog({
    title: 'Cerrar Período',
    message: `¿Cerrar el período ${periodoSel.value.periodo}? No podrá agregar más empleados.`,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { label: 'Cerrar Período', color: 'orange-7', unelevated: true },
    dark: true, class: 'premium-glass-card',
  }).onOk(async () => {
    try {
      await api.post(`/nomina/periodos/${periodoSel.value.id}/cerrar`)
      $q.notify({ type: 'positive', message: 'Período cerrado.' })
      await cargar()
      periodoSel.value = periodos.value.find(p => p.id === periodoSel.value.id) || null
    } catch {
      $q.notify({ type: 'negative', message: 'Error al cerrar período.' })
    }
  })
}

function abrirFormPeriodo() {
  formPeriodo.value = { periodo: '', fecha_inicio: '', fecha_fin: '' }
  dialogPeriodo.value = true
}

onMounted(cargar)
</script>
