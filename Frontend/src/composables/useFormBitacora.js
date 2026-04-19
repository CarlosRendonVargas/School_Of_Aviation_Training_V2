// src/composables/useFormBitacora.js
/**
 * useFormBitacora
 * ───────────────
 * Encapsula la lógica de validación RAC 61 del formulario de bitácora.
 * Reutilizable en BitacoraNuevaPage y eventuales formularios de edición.
 */
import { ref, computed } from 'vue'
import dayjs from 'dayjs'

export function useFormBitacora() {
  const form = ref({
    estudiante_id:   null,
    instructor_id:   null,
    aeronave_id:     null,
    reserva_id:      null,
    fecha:           dayjs().format('YYYY-MM-DD'),
    hora_salida:     '',
    hora_llegada:    '',
    origen_icao:     '',
    destino_icao:    '',
    horas_totales:   0,
    horas_dual:      0,
    horas_solo:      0,
    horas_noche:     0,
    horas_ifr:       0,
    horas_simulador: 0,
    tipo_vuelo:      'local',
    condiciones_vmc: true,
    aterrizajes:     1,
    observaciones:   '',
  })

  const errores = ref([])

  // ── Computed helpers ────────────────────────────────────────────────────────

  /** Horas disponibles después de dual + solo */
  const horasRestantes = computed(() =>
    Math.max(0, form.value.horas_totales - form.value.horas_dual - form.value.horas_solo)
  )

  /** Porcentaje de distribución para visualización */
  const distribucionHoras = computed(() => {
    const total = form.value.horas_totales || 1
    return {
      dual:      Math.min(100, (form.value.horas_dual / total) * 100),
      solo:      Math.min(100, (form.value.horas_solo / total) * 100),
      noche:     Math.min(100, (form.value.horas_noche / total) * 100),
      ifr:       Math.min(100, (form.value.horas_ifr / total) * 100),
      simulador: Math.min(100, (form.value.horas_simulador / total) * 100),
    }
  })

  // ── Validación RAC 61 ───────────────────────────────────────────────────────

  function validarRAC(opciones = {}) {
    const { estudianteOk = true, aeronaveOk = true, airworthinessOk = true } = opciones
    errores.value = []

    // Certificado médico RAC 67
    if (!estudianteOk) {
      errores.value.push('El estudiante no tiene certificado médico aeronáutico vigente (RAC 67).')
    }

    // Aeronavegabilidad RAC 141.51
    if (!airworthinessOk) {
      errores.value.push('La aeronave tiene el certificado de aeronavegabilidad vencido (RAC 141.51).')
    }

    // Aeronave disponible
    if (!aeronaveOk) {
      errores.value.push('La aeronave no está disponible para vuelo (en mantenimiento o baja).')
    }

    // Suma de horas
    const sumaParcial = form.value.horas_dual + form.value.horas_solo
    if (sumaParcial > form.value.horas_totales + 0.05) {
      errores.value.push(`Horas dual (${form.value.horas_dual}h) + solo (${form.value.horas_solo}h) supera el total (${form.value.horas_totales}h).`)
    }

    // Horas nocturnas y IFR no pueden superar total
    if (form.value.horas_noche > form.value.horas_totales) {
      errores.value.push('Las horas nocturnas no pueden superar las horas totales del vuelo.')
    }
    if (form.value.horas_ifr > form.value.horas_totales) {
      errores.value.push('Las horas IFR no pueden superar las horas totales del vuelo.')
    }

    // Consistencia tipo vuelo / condiciones
    if (form.value.tipo_vuelo === 'noche' && form.value.horas_noche === 0) {
      errores.value.push('El tipo "Vuelo nocturno" requiere al menos 0.1h de horas nocturnas.')
    }
    if (form.value.tipo_vuelo === 'ifr' && !form.value.horas_ifr) {
      errores.value.push('El tipo "IFR" requiere registrar horas de instrumentos.')
    }
    if (form.value.tipo_vuelo === 'ifr' && form.value.condiciones_vmc) {
      errores.value.push('El vuelo IFR debe realizarse en condiciones IMC.')
    }

    // ICAO codes
    if (form.value.origen_icao.length !== 4) {
      errores.value.push('El indicador ICAO de origen debe tener exactamente 4 caracteres.')
    }
    if (form.value.destino_icao.length !== 4) {
      errores.value.push('El indicador ICAO de destino debe tener exactamente 4 caracteres.')
    }

    // Instructor requerido para vuelo dual
    if (form.value.horas_dual > 0 && !form.value.instructor_id) {
      errores.value.push('Se registraron horas duales pero no se seleccionó instructor.')
    }

    // Fecha no puede ser futura
    if (dayjs(form.value.fecha).isAfter(dayjs())) {
      errores.value.push('La fecha del vuelo no puede ser en el futuro.')
    }

    return errores.value.length === 0
  }

  /** Prepara el payload limpio para enviar a la API */
  function payload() {
    return {
      ...form.value,
      horas_totales:   parseFloat(form.value.horas_totales   || 0),
      horas_dual:      parseFloat(form.value.horas_dual      || 0),
      horas_solo:      parseFloat(form.value.horas_solo      || 0),
      horas_noche:     parseFloat(form.value.horas_noche     || 0),
      horas_ifr:       parseFloat(form.value.horas_ifr       || 0),
      horas_simulador: parseFloat(form.value.horas_simulador || 0),
      aterrizajes:     parseInt(form.value.aterrizajes       || 1),
      origen_icao:     form.value.origen_icao.toUpperCase(),
      destino_icao:    form.value.destino_icao.toUpperCase(),
    }
  }

  /** Resetea el formulario a valores iniciales */
  function reset() {
    form.value = {
      estudiante_id: null, instructor_id: null, aeronave_id: null, reserva_id: null,
      fecha: dayjs().format('YYYY-MM-DD'), hora_salida: '', hora_llegada: '',
      origen_icao: '', destino_icao: '',
      horas_totales: 0, horas_dual: 0, horas_solo: 0,
      horas_noche: 0, horas_ifr: 0, horas_simulador: 0,
      tipo_vuelo: 'local', condiciones_vmc: true, aterrizajes: 1, observaciones: '',
    }
    errores.value = []
  }

  return {
    form,
    errores,
    horasRestantes,
    distribucionHoras,
    validarRAC,
    payload,
    reset,
  }
}
