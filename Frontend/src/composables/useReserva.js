// src/composables/useReserva.js
/**
 * useReserva
 * ──────────
 * Composable para el flujo completo de una reserva de aeronave:
 * 1. Verificar disponibilidad (aeronave + instructor)
 * 2. Validar requisitos RAC (médico, licencia, aeronavegabilidad)
 * 3. Crear la reserva
 * 4. Notificar confirmación
 */
import { ref, computed } from 'vue'
import { api } from 'boot/axios'
import { useQuasar } from 'quasar'
import { useNotificaciones } from './useNotificaciones'

export function useReserva() {
  const $q = useQuasar()
  const { notificarVueloConfirmado } = useNotificaciones()

  const cargando        = ref(false)
  const verificando     = ref(false)
  const erroresValidacion = ref([])
  const aeronavesDispo  = ref([])

  // ── Verificar disponibilidad ──────────────────────────────────────────────
  async function verificarDisponibilidad(fecha, horaInicio, horaFin) {
    if (!fecha || !horaInicio || !horaFin) return
    verificando.value = true
    try {
      const { data } = await api.get('/reservas/disponibilidad', {
        params: { fecha, hora_inicio: horaInicio, hora_fin: horaFin },
      })
      aeronavesDispo.value = data.data.aeronaves || []
      return aeronavesDispo.value
    } catch {
      aeronavesDispo.value = []
      return []
    } finally {
      verificando.value = false
    }
  }

  // ── Crear reserva con validaciones RAC ────────────────────────────────────
  async function crearReserva(form) {
    cargando.value          = true
    erroresValidacion.value = []

    try {
      const { data } = await api.post('/reservas', form)

      if (data.ok) {
        $q.notify({
          type: 'positive',
          message: '✔ Reserva creada correctamente.',
          caption: `${data.data.aeronave?.matricula} · ${data.data.fecha} · ${data.data.hora_inicio?.slice(0,5)}`,
          icon: 'flight',
          timeout: 5000,
        })

        // Notificación push
        notificarVueloConfirmado(
          data.data.aeronave?.matricula,
          data.data.fecha,
          data.data.hora_inicio?.slice(0, 5)
        )

        return { ok: true, reserva: data.data }
      }
    } catch (e) {
      const errores = e.response?.data?.errores || []
      erroresValidacion.value = errores

      if (errores.length) {
        $q.notify({
          type: 'negative',
          message: `Reserva no creada: ${errores.length} error(es) RAC`,
          caption: errores[0],
          icon: 'error',
          timeout: 6000,
        })
      }

      return { ok: false, errores }
    } finally {
      cargando.value = false
    }
  }

  // ── Confirmar reserva ─────────────────────────────────────────────────────
  async function confirmarReserva(reservaId) {
    try {
      await api.post(`/reservas/${reservaId}/confirmar`)
      $q.notify({ type: 'positive', message: 'Reserva confirmada.', icon: 'check_circle' })
      return true
    } catch {
      $q.notify({ type: 'negative', message: 'No se pudo confirmar la reserva.' })
      return false
    }
  }

  // ── Cancelar reserva ──────────────────────────────────────────────────────
  async function cancelarReserva(reservaId, motivo) {
    if (!motivo?.trim()) {
      $q.notify({ type: 'warning', message: 'Debe indicar el motivo de cancelación.' })
      return false
    }
    try {
      await api.post(`/reservas/${reservaId}/cancelar`, { motivo })
      $q.notify({ type: 'info', message: 'Reserva cancelada.', icon: 'event_busy' })
      return true
    } catch {
      $q.notify({ type: 'negative', message: 'Error al cancelar la reserva.' })
      return false
    }
  }

  const hayErrores = computed(() => erroresValidacion.value.length > 0)

  return {
    cargando,
    verificando,
    erroresValidacion,
    aeronavesDispo,
    hayErrores,
    verificarDisponibilidad,
    crearReserva,
    confirmarReserva,
    cancelarReserva,
  }
}
