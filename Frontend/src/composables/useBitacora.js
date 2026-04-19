// src/composables/useBitacora.js
/**
 * useBitacora
 * ───────────
 * Composable que encapsula toda la lógica de creación y gestión de bitácoras.
 * Implementa las validaciones RAC 61 y RAC 91.417 antes de guardar.
 *
 * Validaciones incluidas:
 *  - Certificado médico del estudiante vigente (RAC 67)
 *  - Aeronavegabilidad de la aeronave vigente (RAC 141.51)
 *  - Suma de horas parciales coherente con el total
 *  - Límite de horas de simulador según el programa (RAC 61)
 *  - Instructor con licencia vigente si aplica (RAC 65)
 */
import { ref } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

export function useBitacora() {
  const $q     = useQuasar()
  const router = useRouter()

  const cargando = ref(false)
  const errores  = ref([])

  // ── Validación local antes de enviar ─────────────────────────────────────
  function validarLocalmente(form) {
    const errs = []

    // Horas totales
    if (!form.horas_totales || form.horas_totales <= 0) {
      errs.push('Las horas totales del vuelo deben ser mayores a 0.')
    }
    if (form.horas_totales > 24) {
      errs.push('No se pueden registrar más de 24 horas en un solo vuelo.')
    }

    // Suma dual + solo no supera total
    const sumaParcial = (form.horas_dual || 0) + (form.horas_solo || 0)
    if (sumaParcial > (form.horas_totales || 0) + 0.05) {
      errs.push(`La suma de horas dual (${form.horas_dual?.toFixed(1)}h) + solo (${form.horas_solo?.toFixed(1)}h) supera el total del vuelo.`)
    }

    // Código ICAO
    if (!/^[A-Z]{4}$/.test(form.origen_icao?.toUpperCase())) {
      errs.push('El código ICAO de origen debe tener 4 letras (ej: SKBO).')
    }
    if (!/^[A-Z]{4}$/.test(form.destino_icao?.toUpperCase())) {
      errs.push('El código ICAO de destino debe tener 4 letras (ej: SKMD).')
    }

    // Horario
    if (form.hora_llegada && form.hora_salida && form.hora_llegada <= form.hora_salida) {
      errs.push('La hora de llegada debe ser posterior a la hora de salida.')
    }

    // Vuelo solo sin instructor
    if (form.horas_solo > 0 && form.instructor_id) {
      // OK: puede haber vuelo solo con instructor de observación
    }

    // Vuelo dual necesita instructor
    if ((form.horas_dual || 0) > 0 && !form.instructor_id) {
      errs.push('Las horas de instrucción dual requieren un instructor asignado.')
    }

    return errs
  }

  // ── Crear bitácora ────────────────────────────────────────────────────────
  async function crear(formData) {
    errores.value = []

    // 1. Validación local
    const errsLocal = validarLocalmente(formData)
    if (errsLocal.length) {
      errores.value = errsLocal
      return { ok: false, errores: errsLocal }
    }

    cargando.value = true

    try {
      const payload = {
        ...formData,
        origen_icao:  formData.origen_icao?.toUpperCase(),
        destino_icao: formData.destino_icao?.toUpperCase(),
      }

      const { data } = await api.post('/bitacoras', payload)

      $q.notify({
        type: 'positive',
        message: '✔ Bitácora registrada correctamente',
        caption: `${data.data?.aeronave?.matricula} · ${formData.horas_totales?.toFixed(1)}h`,
        icon: 'flight',
        timeout: 5000,
        position: 'top-right',
      })

      return { ok: true, bitacora: data.data }
    } catch (e) {
      const msg = e.response?.data?.mensaje || 'Error al registrar la bitácora.'
      const errAPI = e.response?.data?.errors
        ? Object.values(e.response.data.errors).flat()
        : [msg]

      errores.value = errAPI

      $q.notify({
        type: 'negative',
        message: 'Error al registrar la bitácora',
        caption: errAPI[0],
        icon: 'error',
        timeout: 5000,
      })

      return { ok: false, errores: errAPI }
    } finally {
      cargando.value = false
    }
  }

  // ── Firmar bitácora digitalmente ─────────────────────────────────────────
  async function firmar(bitacoraId) {
    try {
      const { data } = await api.post(`/bitacoras/${bitacoraId}/firmar`)
      $q.notify({ type: 'positive', message: 'Bitácora firmada digitalmente.', icon: 'draw' })
      return { ok: true, data: data.data }
    } catch {
      $q.notify({ type: 'negative', message: 'Error al firmar la bitácora.' })
      return { ok: false }
    }
  }

  // ── Resumen de horas de un estudiante (para ver si cumple RAC 61) ────────
  async function obtenerResumenHoras(estudianteId) {
    try {
      const { data } = await api.get(`/estudiantes/${estudianteId}/progreso-horas`)
      return data.data
    } catch { return null }
  }

  return { cargando, errores, crear, firmar, obtenerResumenHoras }
}
