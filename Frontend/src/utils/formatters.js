// src/utils/formatters.js
/**
 * Colección de funciones de formateo reutilizables en toda la app.
 * Optimizadas para Colombia (COP, formato de fecha colombiano, ICAO).
 */
import dayjs from 'dayjs'
import 'dayjs/locale/es'
import relativeTime from 'dayjs/plugin/relativeTime'
import duration from 'dayjs/plugin/duration'

dayjs.extend(relativeTime)
dayjs.extend(duration)
dayjs.locale('es')

// ── Fechas ────────────────────────────────────────────────────────────────────

/** '2025-06-15' → 'sáb. 15 jun. 2025' */
export const formatFecha = (fecha) =>
  fecha ? dayjs(fecha).format('ddd. D MMM YYYY') : '—'

/** '2025-06-15' o '2025-06-15T00:00:00Z' → '15/06/2025' (zona Bogotá) */
export const formatFechaCO = (fecha) => {
  if (!fecha) return '—'
  // Tomar solo la parte de fecha (YYYY-MM-DD) para evitar desplazamiento de zona horaria
  const solo = typeof fecha === 'string' ? fecha.substring(0, 10) : fecha
  return dayjs(solo).format('DD/MM/YYYY')
}

/** '2025-06-15T14:30:00' → '14:30' */
export const formatHora = (dt) =>
  dt ? dayjs(dt).format('HH:mm') : '—'

/** '2025-06-15' → 'hace 3 días' o 'en 5 días' */
export const formatRelativo = (fecha) =>
  fecha ? dayjs(fecha).fromNow() : '—'

/** Días hasta una fecha */
export const diasHasta = (fecha) =>
  fecha ? dayjs(fecha).diff(dayjs(), 'day') : null

/** Label de días con color semántico */
export const diasLabel = (fecha) => {
  const d = diasHasta(fecha)
  if (d === null) return { texto: '—', color: '#64748b' }
  if (d < 0)  return { texto: 'VENCIDO',      color: '#ef4444' }
  if (d === 0)return { texto: 'Vence HOY',     color: '#ef4444' }
  if (d <= 15)return { texto: `${d}d`,          color: '#ef4444' }
  if (d <= 30)return { texto: `${d}d`,          color: '#f59e0b' }
  return       { texto: `${d}d`,                color: '#22c55e' }
}

// ── Dinero ────────────────────────────────────────────────────────────────────

/** 1500000 → '$1.500.000' */
export const formatCOP = (valor, decimales = 0) => {
  const n = parseFloat(valor || 0)
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: decimales,
    maximumFractionDigits: decimales,
  }).format(n)
}

/** 1500000 → '$1.5M' | 500000 → '$500K' */
export const formatCOPAbrev = (valor) => {
  const n = parseFloat(valor || 0)
  if (n >= 1_000_000) return `$${(n / 1_000_000).toFixed(1)}M`
  if (n >= 1_000)     return `$${(n / 1_000).toFixed(0)}K`
  return `$${n}`
}

// ── Horas de vuelo ────────────────────────────────────────────────────────────

/** 1.5 → '1h 30min' */
export const formatHorasVuelo = (horas) => {
  if (!horas && horas !== 0) return '—'
  const h = Math.floor(horas)
  const m = Math.round((horas - h) * 60)
  if (m === 0) return `${h}h`
  return `${h}h ${m}min`
}

/** Porcentaje de avance (0-100), devuelve color semántico */
export const colorProgreso = (pct) => {
  if (pct >= 100) return '#22c55e'
  if (pct >= 80)  return '#f59e0b'
  if (pct >= 50)  return '#3b82f6'
  return '#3b82f6'
}

// ── Aeronáutica ───────────────────────────────────────────────────────────────

/** 'SKBO' → 'SKBO (Bogotá El Dorado)' */
const AERODROMO_NOMBRES = {
  SKBO: 'Bogotá El Dorado',
  SKMD: 'Medellín Olaya Herrera',
  SKCL: 'Cali Alfonso Bonilla',
  SKBQ: 'Barranquilla E. Cortissoz',
  SKRG: 'Medellín J.M. Córdova',
  SKPE: 'Pereira Matecaña',
  SKUI: 'Quibdó El Caraño',
  SKLM: 'La Macarena',
  SKVP: 'Valledupar Alfonso López',
  SKUC: 'Arauca Santiago Pérez',
}

export const formatAerodromo = (icao) => {
  if (!icao) return '—'
  const nombre = AERODROMO_NOMBRES[icao?.toUpperCase()]
  return nombre ? `${icao} (${nombre})` : icao
}

/** Tipo de vuelo → etiqueta y color */
export const infoTipoVuelo = (tipo) => ({
  local:      { label: 'Local',       color: '#3b82f6', icono: 'flight' },
  navegacion: { label: 'Navegación',  color: '#14b8a6', icono: 'route' },
  noche:      { label: 'Noche',       color: '#8b5cf6', icono: 'nights_stay' },
  ifr:        { label: 'IFR',         color: '#f59e0b', icono: 'radar' },
  sim:        { label: 'Simulador',   color: '#6b7280', icono: 'computer' },
}[tipo] || { label: tipo, color: '#64748b', icono: 'flight' })

/** Estado de aeronave → color de chip */
export const colorEstadoAeronave = (estado) => ({
  disponible:   'positive',
  mantenimiento:'warning',
  baja:         'negative',
}[estado] || 'grey')

// ── SMS / Riesgo ──────────────────────────────────────────────────────────────

/** nivel (1-25) → { clasificacion, color, bg } */
export const infoNivelRiesgo = (nivel) => {
  if (nivel <= 4)  return { clasificacion: 'Aceptable',   color: '#22c55e', bg: 'rgba(34,197,94,.2)'  }
  if (nivel <= 9)  return { clasificacion: 'Tolerable',   color: '#f59e0b', bg: 'rgba(245,158,11,.2)' }
  return              { clasificacion: 'Inaceptable', color: '#ef4444', bg: 'rgba(239,68,68,.25)' }
}

// ── Personas ──────────────────────────────────────────────────────────────────

/** objeto persona → 'Juan Pérez' */
export const nombreCompleto = (persona) =>
  persona ? `${persona.nombres || ''} ${persona.apellidos || ''}`.trim() : '—'

/** nombre → 'JP' (iniciales) */
export const iniciales = (nombre) =>
  (nombre || '').split(' ').filter(Boolean).map(w => w[0]).slice(0, 2).join('').toUpperCase()

// ── Validaciones de formulario ────────────────────────────────────────────────

export const reglas = {
  requerido:  (v) => !!v || 'Campo requerido',
  email:      (v) => /.+@.+\..+/.test(v)    || 'Email inválido',
  icao:       (v) => /^[A-Z]{4}$/.test(v?.toUpperCase()) || 'Código ICAO inválido (4 letras)',
  horasVuelo: (v) => (v > 0 && v <= 24)     || 'Horas inválidas (0.1 – 24)',
  fecha:      (v) => !!v                     || 'Fecha requerida',
  hora:       (v) => /^\d{2}:\d{2}$/.test(v)|| 'Formato HH:MM',
  matricula:  (v) => /^HK-[A-Z0-9]{4}$/.test(v?.toUpperCase()) || 'Formato HK-XXXX',
}
