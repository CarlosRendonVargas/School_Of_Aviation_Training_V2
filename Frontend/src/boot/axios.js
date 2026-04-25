// src/boot/axios.js
import { boot } from 'quasar/wrappers'
import axios from 'axios'
import { Notify } from 'quasar'

const api = axios.create({
  baseURL: process.env.API_URL,
  timeout: 30000,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

export default boot(({ app, router, store }) => {
  // ── Request interceptor: agregar token Bearer ──────────────────────────────
  api.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem('rac141_token')
      if (token) config.headers.Authorization = `Bearer ${token}`
      return config
    },
    (error) => Promise.reject(error)
  )

  // ── Response interceptor: manejar errores globalmente ─────────────────────
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      const { response } = error

      if (!response) {
        // Sin conexión al servidor
        Notify.create({
          type: 'negative',
          message: 'Sin conexión con el servidor. Verifique su internet.',
          icon: 'wifi_off',
        })
        return Promise.reject(error)
      }

      switch (response.status) {
        case 401:
          // Token expirado → redirigir al login
          localStorage.removeItem('rac141_token')
          localStorage.removeItem('rac141_usuario')
          router.push({ name: 'login' })
          break

        case 403:
          Notify.create({
            type: 'warning',
            message: 'No tiene permiso para realizar esta acción.',
            icon: 'lock',
          })
          break

        case 422:
          // Errores de validación — se manejan en cada componente
          break

        case 500:
          Notify.create({
            type: 'negative',
            message: 'Error interno del servidor. Contacte al administrador.',
            icon: 'error',
          })
          break
      }

      return Promise.reject(error)
    }
  )

  // Disponible globalmente como this.$api
  app.config.globalProperties.$api = api
})

export { api }
