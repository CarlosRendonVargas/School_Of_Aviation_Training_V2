// src/boot/i18n.js
import { boot } from 'quasar/wrappers'

// Mensajes en español para la app
const messages = {
  es: {
    comun: {
      guardar:     'Guardar',
      cancelar:    'Cancelar',
      editar:      'Editar',
      eliminar:    'Eliminar',
      buscar:      'Buscar',
      filtrar:     'Filtrar',
      exportar:    'Exportar',
      cargando:    'Cargando…',
      sinResultados:'Sin resultados',
      confirmar:   'Confirmar',
      volver:      'Volver',
      ver:         'Ver',
      cerrar:      'Cerrar',
    },
    rac: {
      rac61:  'RAC 61',
      rac65:  'RAC 65',
      rac67:  'RAC 67',
      rac141: 'RAC 141',
      uaeac:  'UAEAC',
      oaci:   'OACI',
    },
    estados: {
      activo:     'Activo',
      inactivo:   'Inactivo',
      suspendido: 'Suspendido',
      graduado:   'Graduado',
      retirado:   'Retirado',
      disponible: 'Disponible',
      mantenimiento: 'En mantenimiento',
      baja:       'Baja',
    },
    vuelo: {
      dual:         'Instrucción dual',
      solo:         'Vuelo solo',
      noche:        'Vuelo nocturno',
      ifr:          'Vuelo por instrumentos',
      simulador:    'Simulador',
      local:        'Vuelo local',
      navegacion:   'Navegación',
    },
  },
}

export default boot(({ app }) => {
  // Disponible como this.$t en options API o inject en composition API
  app.config.globalProperties.$t = (key) => {
    const parts = key.split('.')
    let val = messages.es
    for (const p of parts) {
      if (!val) return key
      val = val[p]
    }
    return val || key
  }
})
