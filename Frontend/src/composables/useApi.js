// src/composables/useApi.js
import { ref } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'

/**
 * Composable reutilizable para llamadas a la API.
 * Gestiona loading, errores y notificaciones automáticamente.
 *
 * Uso:
 *   const { datos, cargando, error, ejecutar } = useApi()
 *   await ejecutar(() => api.get('/estudiantes'))
 */
export function useApi() {
  const cargando = ref(false)
  const error    = ref(null)
  const datos    = ref(null)

  let $q
  try { $q = useQuasar() } catch { $q = null }

  async function ejecutar(llamada, opciones = {}) {
    const {
      successMsg    = null,
      errorMsg      = null,
      notify        = true,
      transformData = null,
    } = opciones

    cargando.value = true
    error.value    = null

    try {
      const response = await llamada()
      const raw      = response.data?.data ?? response.data

      datos.value = transformData ? transformData(raw) : raw

      if (successMsg && notify && $q) {
        $q.notify({ type: 'positive', message: successMsg, icon: 'check_circle' })
      }

      return { ok: true, data: datos.value }
    } catch (e) {
      error.value = e.response?.data?.mensaje
        || e.response?.data?.message
        || errorMsg
        || 'Error al comunicarse con el servidor.'

      if (notify && $q && e.response?.status !== 401) {
        $q.notify({
          type: 'negative',
          message: error.value,
          icon: 'error',
          timeout: 4000,
        })
      }

      // Extraer errores de validación Laravel (422)
      const validationErrors = e.response?.data?.errors || null

      return { ok: false, error: error.value, validationErrors }
    } finally {
      cargando.value = false
    }
  }

  return { datos, cargando, error, ejecutar }
}

/**
 * Composable para listados paginados con filtros.
 *
 * Uso:
 *   const lista = useListaApi('/estudiantes')
 *   lista.filtros.value.estado = 'activo'
 *   await lista.cargar()
 */
export function useListaApi(endpoint) {
  const items      = ref([])
  const cargando   = ref(false)
  const paginacion = ref({ page: 1, rowsPerPage: 20, rowsNumber: 0 })
  const filtros    = ref({})

  async function cargar(pagina = 1) {
    cargando.value = true
    try {
      const params = {
        page:     pagina,
        per_page: paginacion.value.rowsPerPage,
        ...Object.fromEntries(
          Object.entries(filtros.value).filter(([, v]) => v !== null && v !== '')
        ),
      }

      const { data } = await api.get(endpoint, { params })

      // Soporta respuesta paginada de Laravel o array simple
      if (data.data?.data) {
        items.value                  = data.data.data
        paginacion.value.rowsNumber  = data.data.total
        paginacion.value.page        = data.data.current_page
      } else {
        items.value = data.data || []
        paginacion.value.rowsNumber = items.value.length
      }
    } finally {
      cargando.value = false
    }
  }

  function onRequest(props) {
    paginacion.value.page        = props.pagination.page
    paginacion.value.rowsPerPage = props.pagination.rowsPerPage
    cargar(props.pagination.page)
  }

  return { items, cargando, paginacion, filtros, cargar, onRequest }
}
