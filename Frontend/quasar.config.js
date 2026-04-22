import { configure } from 'quasar/wrappers'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

export default configure(function () {
  return {
    // Boot files — orden importa: axios primero
    boot: ['axios', 'i18n', 'dayjs'],

    css: ['app.scss'],

    extras: ['material-icons', 'mdi-v7'],

    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node20',
      },
      // hash → no necesita .htaccess en cPanel ni XAMPP
      vueRouterMode: 'hash',

      env: {
        API_URL: 'https://schoolaviationtraining.com/api/v1',
      },

      extendViteConf (viteConf) {
        viteConf.resolve.alias = {
          ...viteConf.resolve.alias,
          'store': path.join(__dirname, './src/store')
        }
      }
    },

    devServer: { open: true },

    framework: {
      config: {
        brand: {
          primary:   '#A10B13',
          secondary: '#1a1a1a',
          accent:    '#da291c',
          dark:      '#121212',
          positive:  '#10b981',
          negative:  '#ef4444',
          info:      '#60a5fa',
          warning:   '#f59e0b',
        },
        notify:     { position: 'top-right', timeout: 3000 },
        loading:    { message: 'Cargando...' },
        loadingBar: { color: 'primary', size: '3px' },
        dark: true,
      },
      plugins: ['Notify', 'Loading', 'LocalStorage', 'SessionStorage', 'Dialog', 'LoadingBar'],
    },

    animations: [],
    capacitor: { hideSplashscreen: true },
  }
})
