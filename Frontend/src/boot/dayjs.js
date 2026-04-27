import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'
import localizedFormat from 'dayjs/plugin/localizedFormat'
import relativeTime from 'dayjs/plugin/relativeTime'
import 'dayjs/locale/es'

dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.extend(localizedFormat)
dayjs.extend(relativeTime)
dayjs.locale('es')
dayjs.tz.setDefault('America/Bogota')

/**
 * Convierte cualquier string/Date a hora Bogotá y formatea.
 * Uso en templates: {{ fBogota(row.created_at) }}
 * Formatos comunes: 'DD/MM/YYYY HH:mm' | 'DD MMM YYYY' | 'HH:mm'
 */
export const fBogota = (value, fmt = 'DD/MM/YYYY HH:mm') => {
  if (!value) return '—'
  return dayjs.utc(value).tz('America/Bogota').format(fmt)
}

export default ({ app }) => {
  app.config.globalProperties.$dayjs = dayjs
  app.config.globalProperties.$fBogota = fBogota
}

export { dayjs }
