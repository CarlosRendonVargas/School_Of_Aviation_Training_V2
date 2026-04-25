<template>
  <q-page class="q-pa-md animate-fade">

    <!-- ══ Encabezado ══ -->
    <div class="row items-center justify-between q-mb-lg rac-page-header flex-wrap" style="gap:12px">
      <div>
        <div class="rac-page-subtitle q-mb-xs">UAEAC · AEROCIVIL · RAC</div>
        <h1 class="rac-page-title tracking-tighter">Normatividad <span class="text-red-9">Aérea</span></h1>
      </div>
      <div class="row q-gutter-sm">
        <q-btn
          unelevated color="red-9"
          icon="open_in_new"
          label="RAC Aerocivil"
          class="premium-btn"
          target="_blank"
          rel="noopener noreferrer"
          href="https://www.aerocivil.gov.co/autoridad_aeronautica/normatividad/13-reglamentos-aeronauticos-de-colombia-rac"
        />
        <q-btn
          v-if="auth.esAdmin"
          unelevated color="grey-9"
          icon="add"
          label="Agregar"
          class="premium-btn"
          @click="abrirFormulario()"
        />
      </div>
    </div>

    <!-- ══ Lista de documentos ══ -->
    <q-card class="premium-glass-card" style="overflow: hidden">
      <q-card-section class="q-pa-md">
        <q-input
          v-model="busqueda"
          dense dark outlined
          placeholder="Buscar documento..."
          clearable
          style="max-width: 400px"
        >
          <template #prepend><q-icon name="search" color="grey-6" size="18px" /></template>
        </q-input>
      </q-card-section>

      <q-separator dark />

      <div v-if="cargando" class="flex flex-center q-pa-xl">
        <q-spinner-cube color="red-9" size="40px" />
      </div>

      <div v-else-if="!categoriasFiltradas.length" class="text-center q-pa-xl text-grey-6 font-mono" style="font-size:12px">
        Sin documentos disponibles.
      </div>

      <template v-else v-for="grupo in categoriasFiltradas" :key="grupo.categoria">
        <div class="q-px-lg q-pt-md q-pb-xs font-mono text-red-9 text-uppercase" style="font-size:10px; letter-spacing:1.5px">
          {{ grupo.categoria }}
        </div>

        <div class="row q-px-md q-pb-md q-col-gutter-md">
          <div
            v-for="doc in grupo.documentos"
            :key="doc.id"
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <q-card
              class="premium-glass-card doc-card cursor-pointer column"
              style="height: 100%"
              @click="abrirVisor(doc)"
            >
              <q-card-section class="col q-pa-md">
                <div class="row items-start no-wrap q-gutter-sm">
                  <q-icon name="picture_as_pdf" color="red-9" size="28px" class="q-mt-xs" />
                  <div class="col">
                    <div class="text-white text-weight-bold" style="font-size:13px; line-height:1.3">{{ doc.titulo }}</div>
                    <div v-if="doc.descripcion" class="text-grey-6 font-mono q-mt-xs" style="font-size:10px">{{ doc.descripcion }}</div>
                  </div>
                </div>
              </q-card-section>

              <q-card-actions class="q-pa-md q-pt-none row items-center justify-between">
                <q-badge outline color="red-9" class="font-mono" style="font-size:9px">{{ doc.categoria }}</q-badge>
                <div v-if="auth.esAdmin" class="row no-wrap">
                  <q-btn flat round dense icon="edit" color="grey-5" size="xs" @click.stop="abrirFormulario(doc)" />
                  <q-btn flat round dense icon="delete" color="red-9" size="xs" @click.stop="confirmarEliminar(doc)" />
                </div>
              </q-card-actions>

              <div class="doc-card-bar"></div>
            </q-card>
          </div>
        </div>
      </template>
    </q-card>

    <!-- ══ Diálogo visor de documento (maximizado, sin descarga) ══ -->
    <q-dialog v-model="dialogVisor" maximized backdrop-filter="blur(10px)">
      <div class="column full-height" style="background: #05070a">

        <!-- Barra superior -->
        <div class="row items-center no-wrap q-px-lg q-py-md" style="border-bottom: 1px solid rgba(255,255,255,0.07); background: rgba(10,13,20,0.95); flex-shrink: 0">
          <q-icon name="picture_as_pdf" color="red-9" size="22px" class="q-mr-md" />
          <div class="col">
            <div class="text-white text-weight-bold" style="font-size:14px">{{ docActivo?.titulo }}</div>
            <div class="font-mono text-grey-6" style="font-size:10px">{{ docActivo?.categoria }}</div>
          </div>
          <q-btn flat round dense icon="close" color="grey-4" size="md" v-close-popup>
            <q-tooltip>Cerrar</q-tooltip>
          </q-btn>
        </div>

        <!-- iframe sin barra de descarga — clip por márgenes negativos -->
        <div class="col viewer-clip">
          <iframe
            v-if="docActivo"
            :key="docActivo.id"
            :src="urlVisor(docActivo.url_pdf)"
            class="viewer-frame"
            sandbox="allow-scripts allow-same-origin allow-forms"
            referrerpolicy="no-referrer"
          />
        </div>

      </div>
    </q-dialog>

    <!-- ══ Diálogo: Agregar / Editar documento ══ -->
    <q-dialog v-model="dialogForm" backdrop-filter="blur(20px)">
      <q-card class="premium-glass-card border-red-top rounded-20 shadow-24" style="width: min(560px, 95vw)">
        <div class="rac-dialog-header">
          <div class="font-mono text-grey-5 uppercase tracking-widest q-mb-xs" style="font-size:10px">
            {{ editando ? 'Editar' : 'Nuevo' }} documento
          </div>
          <div class="text-h5 text-white font-head text-weight-bolder">Normatividad RAC</div>
        </div>

        <div class="rac-dialog-body q-gutter-y-md">
          <div>
            <div class="campo-label required">Título</div>
            <q-input v-model="form.titulo" dark outlined dense placeholder="Ej: RAC 91 — Reglas de Aire" />
          </div>
          <div>
            <div class="campo-label">Categoría</div>
            <q-input
              v-model="form.categoria"
              dark outlined dense
              placeholder="Ej: RAC 91, OACI, UAEAC..."
              list="categorias-list"
            />
            <datalist id="categorias-list">
              <option v-for="cat in categoriasOpciones" :key="cat" :value="cat" />
            </datalist>
          </div>
          <div>
            <div class="campo-label required">URL del documento (PDF o enlace)</div>
            <q-input v-model="form.url_pdf" dark outlined dense placeholder="https://..." />
          </div>
          <div>
            <div class="campo-label">Descripción</div>
            <q-input v-model="form.descripcion" dark outlined dense type="textarea" rows="2" placeholder="Descripción breve..." />
          </div>
          <div class="row q-gutter-md items-center">
            <div>
              <div class="campo-label">Orden</div>
              <q-input v-model.number="form.orden" dark outlined dense type="number" style="width: 100px" />
            </div>
            <div>
              <div class="campo-label">Estado</div>
              <q-toggle v-model="form.activo" color="red-9" label="Activo" dark />
            </div>
          </div>
        </div>

        <q-separator dark />
        <div class="q-pa-lg row justify-end q-gutter-sm">
          <q-btn flat label="Cancelar" color="grey-6" v-close-popup />
          <q-btn unelevated color="red-9" :label="editando ? 'Guardar cambios' : 'Crear documento'" class="premium-btn" :loading="guardando" @click="guardar" />
        </div>
      </q-card>
    </q-dialog>

  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'store/auth'
import { api } from 'boot/axios'

const $q   = useQuasar()
const auth = useAuthStore()

const cargando   = ref(false)
const guardando  = ref(false)
const grupos     = ref([])
const docActivo  = ref(null)
const busqueda   = ref('')
const dialogVisor = ref(false)
const dialogForm  = ref(false)
const editando    = ref(null)

const formVacio = () => ({ titulo: '', descripcion: '', url_pdf: '', categoria: '', orden: 0, activo: true })
const form = ref(formVacio())

const categoriasFiltradas = computed(() => {
  const q = busqueda.value?.toLowerCase() || ''
  return grupos.value
    .map(g => ({
      ...g,
      documentos: g.documentos.filter(d =>
        d.titulo.toLowerCase().includes(q) ||
        d.descripcion?.toLowerCase().includes(q) ||
        d.categoria.toLowerCase().includes(q)
      ),
    }))
    .filter(g => g.documentos.length)
})

const categoriasOpciones = computed(() =>
  [...new Set(grupos.value.map(g => g.categoria))].sort()
)

async function cargar() {
  cargando.value = true
  try {
    const { data } = await api.get('/normatividad')
    grupos.value = data.data
  } catch {
    $q.notify({ type: 'negative', message: 'Error al cargar documentos.' })
  } finally {
    cargando.value = false
  }
}

function abrirVisor(doc) {
  docActivo.value = doc
  dialogVisor.value = true
}

function urlVisor(url) {
  if (!url) return ''
  // Google Drive → preview limpio sin toolbar
  const driveMatch = url.match(/\/d\/([a-zA-Z0-9_-]+)/)
  if (driveMatch) return `https://drive.google.com/file/d/${driveMatch[1]}/preview?rm=minimal`
  // Cualquier otro PDF/URL → Google Docs Viewer embebido
  return `https://docs.google.com/viewer?url=${encodeURIComponent(url)}&embedded=true`
}

function abrirFormulario(doc = null) {
  editando.value = doc
  form.value = doc
    ? { titulo: doc.titulo, descripcion: doc.descripcion || '', url_pdf: doc.url_pdf, categoria: doc.categoria, orden: doc.orden, activo: doc.activo }
    : formVacio()
  dialogForm.value = true
}

async function guardar() {
  if (!form.value.titulo || !form.value.url_pdf) {
    $q.notify({ type: 'warning', message: 'Título y URL son obligatorios.' })
    return
  }
  guardando.value = true
  try {
    if (editando.value) {
      await api.put(`/normatividad/${editando.value.id}`, form.value)
      $q.notify({ type: 'positive', message: 'Documento actualizado.' })
    } else {
      await api.post('/normatividad', form.value)
      $q.notify({ type: 'positive', message: 'Documento creado.' })
    }
    dialogForm.value = false
    await cargar()
  } catch {
    $q.notify({ type: 'negative', message: 'Error al guardar el documento.' })
  } finally {
    guardando.value = false
  }
}

function confirmarEliminar(doc) {
  $q.dialog({
    title: 'Eliminar documento',
    message: `¿Eliminar "${doc.titulo}"?`,
    cancel: { flat: true, label: 'Cancelar', color: 'grey-6' },
    ok:     { label: 'Eliminar', color: 'red-9', unelevated: true },
    dark: true,
    class: 'premium-glass-card',
  }).onOk(async () => {
    try {
      await api.delete(`/normatividad/${doc.id}`)
      $q.notify({ type: 'positive', message: 'Documento eliminado.' })
      await cargar()
    } catch {
      $q.notify({ type: 'negative', message: 'Error al eliminar.' })
    }
  })
}

onMounted(cargar)
</script>

<style lang="scss" scoped>
// Contenedor que recorta el iframe por overflow
.viewer-clip {
  position: relative;
  overflow: hidden;
}

// El iframe se extiende más allá del contenedor en las zonas
// donde Drive/Docs Viewer inyecta botones — el overflow:hidden los oculta
.viewer-frame {
  position: absolute;
  top: -46px;
  left: 0;
  width: calc(100% + 60px);
  height: calc(100% + 46px); // solo compensa el top: -46px
  border: none;
}
.doc-card {
  border: 1px solid rgba(255,255,255,0.05) !important;
  position: relative;
  overflow: hidden;
  transition: all 0.25s ease;

  &:hover {
    border-color: rgba(161, 11, 19, 0.4) !important;
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.5) !important;
  }
}

.doc-card-bar {
  height: 3px;
  background: linear-gradient(90deg, #A10B13, transparent);
  opacity: 0;
  transition: opacity 0.25s;
}

.doc-card:hover .doc-card-bar {
  opacity: 1;
}
</style>
