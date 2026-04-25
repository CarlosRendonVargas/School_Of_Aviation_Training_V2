<template>
  <q-page class="q-pa-md animate-fade">

    <div class="row items-center justify-between q-mb-lg flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">Comunicaciones Internas</div>
        <h1 class="rac-page-title">Centro de <span class="text-red-9">Mensajes</span></h1>
      </div>
      <q-btn unelevated color="red-9" icon="edit" label="Nuevo Mensaje"
        class="premium-btn" @click="abrirCompose()" />
    </div>

    <div class="row q-col-gutter-md">

      <!-- ═══ Panel izquierdo (lista) ═══ -->
      <div class="col-12 col-md-4">
        <q-card class="premium-glass-card">
          <q-tabs v-model="tab" dense active-color="red-9" indicator-color="red-9"
            class="text-grey-5 font-mono" style="font-size:11px">
            <q-tab name="recibidos" label="Recibidos">
              <q-badge v-if="noLeidos > 0" color="red-9" floating rounded :label="noLeidos" />
            </q-tab>
            <q-tab name="enviados" label="Enviados" />
          </q-tabs>

          <q-separator dark />

          <div v-if="cargando" class="flex flex-center q-pa-lg">
            <q-spinner-cube color="red-9" size="32px" />
          </div>

          <q-scroll-area v-else style="height: calc(100vh - 280px)">
            <q-list separator>
              <q-item v-for="msg in listaActual" :key="msg.id"
                clickable v-ripple
                :active="seleccionado?.id === msg.id"
                active-class="bg-red-9"
                @click="seleccionar(msg)"
                class="q-py-md msg-item"
              >
                <q-item-section avatar>
                  <q-avatar size="36px" color="grey-8" text-color="white" style="font-size:14px">
                    {{ inicialDe(msg) }}
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-white text-weight-medium" style="font-size:13px">
                    {{ nombreDe(msg) }}
                  </q-item-label>
                  <q-item-label caption class="text-grey-5" style="font-size:11px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis">
                    {{ msg.asunto }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <div class="column items-end q-gutter-y-xs">
                    <div class="font-mono text-grey-7" style="font-size:9px">{{ fechaCorta(msg.created_at) }}</div>
                    <q-badge v-if="tab === 'recibidos' && !msg.leido" color="red-9" rounded />
                    <q-btn flat round dense icon="delete_outline" size="xs" color="grey-6"
                      class="msg-delete-btn"
                      @click.stop="confirmarEliminar(msg)" />
                  </div>
                </q-item-section>
              </q-item>

              <q-item v-if="!listaActual.length" class="text-center q-pa-xl">
                <q-item-section class="text-grey-7 font-mono" style="font-size:11px">
                  Sin mensajes
                </q-item-section>
              </q-item>
            </q-list>
          </q-scroll-area>
        </q-card>
      </div>

      <!-- ═══ Panel derecho (hilo) ═══ -->
      <div class="col-12 col-md-8">
        <q-card class="premium-glass-card" style="min-height: 500px">
          <div v-if="!seleccionado" class="flex flex-center column q-pa-xl text-center" style="height:500px">
            <q-icon name="mark_email_read" size="56px" color="grey-8" />
            <div class="text-grey-7 font-mono q-mt-md" style="font-size:12px">Selecciona un mensaje para leerlo</div>
          </div>

          <template v-else>
            <!-- Cabecera del mensaje -->
            <div class="q-pa-lg row items-start justify-between" style="border-bottom: 1px solid rgba(255,255,255,0.07)">
              <div class="col">
                <div class="text-h6 text-white text-weight-bold">{{ hilo?.mensaje?.asunto }}</div>
                <div class="row items-center q-mt-sm q-gutter-md">
                  <div class="font-mono text-grey-5" style="font-size:11px">
                    De: <span class="text-grey-3">{{ nombreCompleto(hilo?.mensaje?.remitente) }}</span>
                  </div>
                  <div class="font-mono text-grey-5" style="font-size:11px">
                    Para: <span class="text-grey-3">{{ nombreCompleto(hilo?.mensaje?.destinatario) }}</span>
                  </div>
                  <div class="font-mono text-grey-6" style="font-size:10px">
                    {{ fechaLarga(hilo?.mensaje?.created_at) }}
                  </div>
                </div>
              </div>
              <q-btn flat round icon="delete" color="grey-5" size="sm"
                title="Eliminar conversación"
                @click="confirmarEliminar(seleccionado)" />
            </div>

            <!-- Cuerpo del mensaje raíz -->
            <q-scroll-area style="height: calc(100vh - 420px); min-height: 200px">
              <div class="q-pa-lg">
                <div class="text-grey-3 q-mb-lg" style="font-size:14px; line-height:1.7; white-space:pre-wrap">{{ hilo?.mensaje?.cuerpo }}</div>

                <!-- Respuestas -->
                <div v-for="r in hilo?.respuestas" :key="r.id"
                  class="q-mb-md q-pa-md" style="background:rgba(255,255,255,0.03); border-radius:8px; border-left:3px solid rgba(161,11,19,0.5)">
                  <div class="row items-center q-mb-sm">
                    <q-avatar size="24px" color="red-9" text-color="white" style="font-size:10px; margin-right:8px">
                      {{ (r.remitente?.persona?.nombres || '?')[0] }}
                    </q-avatar>
                    <span class="text-white text-weight-medium" style="font-size:12px">{{ nombreCompleto(r.remitente) }}</span>
                    <span class="text-grey-6 font-mono q-ml-sm" style="font-size:10px">· {{ fechaLarga(r.created_at) }}</span>
                  </div>
                  <div class="text-grey-4" style="font-size:13px; white-space:pre-wrap">{{ r.cuerpo }}</div>
                </div>
              </div>
            </q-scroll-area>

            <!-- Responder -->
            <div class="q-pa-md" style="border-top: 1px solid rgba(255,255,255,0.07)">
              <q-input v-model="respuesta" dark outlined dense type="textarea" rows="2"
                placeholder="Escribir respuesta..." class="q-mb-sm" />
              <div class="row justify-end">
                <q-btn unelevated color="red-9" icon="send" label="Responder"
                  size="sm" class="premium-btn" :loading="enviando" @click="enviarRespuesta" />
              </div>
            </div>
          </template>
        </q-card>
      </div>
    </div>

    <!-- ══ Diálogo nuevo mensaje ══ -->
    <q-dialog v-model="dialogCompose" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width:min(560px,95vw)">
        <div class="rac-dialog-header">
          <div class="text-h5 text-white font-head text-weight-bolder">Nuevo Mensaje</div>
        </div>
        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Destinatario</div>
            <q-select v-model="nuevoMsg.destinatario_id" :options="usuariosOps" emit-value map-options
              dark outlined dense use-input input-debounce="0" @filter="filtrarUsuarios" />
          </div>
          <div>
            <div class="campo-label required">Asunto</div>
            <q-input v-model="nuevoMsg.asunto" dark outlined dense placeholder="Asunto del mensaje..." />
          </div>
          <div>
            <div class="campo-label required">Mensaje</div>
            <q-input v-model="nuevoMsg.cuerpo" dark outlined dense type="textarea" rows="5" />
          </div>
        </div>
        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" icon="send" label="Enviar" class="premium-btn"
            :loading="enviando" @click="enviarNuevo" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<style scoped>
.msg-item .msg-delete-btn { opacity: 0; transition: opacity 0.15s; }
.msg-item:hover .msg-delete-btn { opacity: 1; }
</style>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'

const $q = useQuasar()

const tab         = ref('recibidos')
const cargando    = ref(false)
const enviando    = ref(false)
const recibidos   = ref([])
const enviados    = ref([])
const seleccionado= ref(null)
const hilo        = ref(null)
const respuesta   = ref('')
const noLeidos    = ref(0)
const dialogCompose = ref(false)
const usuarios    = ref([])
const usuariosOps = ref([])

const nuevoMsg = ref({ destinatario_id: null, asunto: '', cuerpo: '' })

const listaActual = computed(() => tab.value === 'recibidos' ? recibidos.value : enviados.value)

function inicialDe(msg) {
  const p = tab.value === 'recibidos' ? msg.remitente?.persona : msg.destinatario?.persona
  return (p?.nombres || '?')[0].toUpperCase()
}
function nombreDe(msg) {
  const p = tab.value === 'recibidos' ? msg.remitente?.persona : msg.destinatario?.persona
  return `${p?.nombres || ''} ${p?.apellidos || ''}`.trim()
}
function nombreCompleto(u) {
  return `${u?.persona?.nombres || ''} ${u?.persona?.apellidos || ''}`.trim() || u?.email || '—'
}
function fechaCorta(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-CO', { day: '2-digit', month: 'short' })
}
function fechaLarga(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('es-CO', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

async function cargar() {
  cargando.value = true
  try {
    const [recRes, envRes, nlRes] = await Promise.all([
      api.get('/mensajes/recibidos'),
      api.get('/mensajes/enviados'),
      api.get('/mensajes/no-leidos'),
    ])
    recibidos.value = recRes.data.data
    enviados.value  = envRes.data.data
    noLeidos.value  = nlRes.data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar mensajes.' })
  } finally {
    cargando.value = false
  }
}

async function seleccionar(msg) {
  seleccionado.value = msg
  respuesta.value    = ''
  try {
    const { data } = await api.get(`/mensajes/${msg.id}/hilo`)
    hilo.value = data.data
    if (tab.value === 'recibidos' && !msg.leido) {
      await api.post(`/mensajes/${msg.id}/marcar-leido`)
      msg.leido = true
      noLeidos.value = Math.max(0, noLeidos.value - 1)
    }
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar el hilo.' })
  }
}

async function enviarRespuesta() {
  if (!respuesta.value.trim()) return
  enviando.value = true
  try {
    const dest = tab.value === 'recibidos'
      ? hilo.value.mensaje.remitente_id
      : hilo.value.mensaje.destinatario_id
    await api.post('/mensajes', {
      destinatario_id: dest,
      asunto: hilo.value.mensaje.asunto,
      cuerpo: respuesta.value,
      respuesta_a: hilo.value.mensaje.id,
    })
    respuesta.value = ''
    await seleccionar(seleccionado.value)
    $q.notify({ type: 'positive', message: 'Respuesta enviada.' })
  } catch {
    $q.notify({ type: 'negative', message: 'Error al enviar respuesta.' })
  } finally {
    enviando.value = false
  }
}

async function abrirCompose() {
  nuevoMsg.value = { destinatario_id: null, asunto: '', cuerpo: '' }
  if (!usuarios.value.length) {
    const { data } = await api.get('/mensajes/usuarios')
    usuarios.value   = data.data
    usuariosOps.value = usuarios.value.map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  }
  dialogCompose.value = true
}

function filtrarUsuarios(val, update) {
  update(() => {
    const q = val.toLowerCase()
    usuariosOps.value = usuarios.value
      .filter(u => u.nombre.toLowerCase().includes(q))
      .map(u => ({ value: u.id, label: `${u.nombre} (${u.rol})` }))
  })
}

async function enviarNuevo() {
  if (!nuevoMsg.value.destinatario_id || !nuevoMsg.value.asunto || !nuevoMsg.value.cuerpo) {
    $q.notify({ type: 'warning', message: 'Destinatario, asunto y mensaje son obligatorios.' })
    return
  }
  enviando.value = true
  try {
    await api.post('/mensajes', nuevoMsg.value)
    $q.notify({ type: 'positive', message: 'Mensaje enviado correctamente.' })
    dialogCompose.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al enviar el mensaje.' })
  } finally {
    enviando.value = false
  }
}

function confirmarEliminar(msg) {
  $q.dialog({
    title: 'Eliminar mensaje',
    message: `¿Eliminar la conversación "<strong>${msg.asunto}</strong>"? Esta acción no se puede deshacer.`,
    html: true,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok: { unelevated: true, label: 'Eliminar', color: 'negative', icon: 'delete' },
    persistent: true,
  }).onOk(async () => {
    try {
      await api.delete(`/mensajes/${msg.id}`)
      $q.notify({ type: 'positive', message: 'Mensaje eliminado.' })
      if (seleccionado.value?.id === msg.id) {
        seleccionado.value = null
        hilo.value = null
      }
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar el mensaje.' })
    }
  })
}

watch(tab, () => { seleccionado.value = null; hilo.value = null })

onMounted(cargar)
</script>
