import dayjs from 'dayjs'
import utc from 'dayjs/plugin/utc'
import timezone from 'dayjs/plugin/timezone'
import 'dayjs/locale/es'

dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.locale('es')

// Configuración global: Bogotá
dayjs.tz.setDefault('America/Bogota')

export default ({ app }) => {
  // Hacerlo disponible en componentes via this.$dayjs (opcional)
  app.config.globalProperties.$dayjs = dayjs
}

export { dayjs }
