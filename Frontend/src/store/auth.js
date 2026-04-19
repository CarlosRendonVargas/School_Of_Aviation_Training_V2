// src/store/auth.js
import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useAuthStore = defineStore('auth', {
  state: () => {
    let usuario = null
    try {
      usuario = JSON.parse(localStorage.getItem('rac141_usuario') || 'null')
    } catch (e) {
      console.error('Error al parsear usuario de localStorage', e)
    }
    return {
      token:   localStorage.getItem('rac141_token') || null,
      usuario,
      cargando: false,
    }
  },

  getters: {
    autenticado:    (state) => !!state.token,
    rol:            (state) => state.usuario?.rol || null,
    nombre:         (state) => state.usuario?.nombre_completo || '',
    foto:           (state) => state.usuario?.foto_url || null,
    permisos:       (state) => state.usuario?.permisos || [],

    esEstudiante:   (state) => state.usuario?.rol === 'estudiante',
    esInstructor:   (state) => state.usuario?.rol === 'instructor',
    esAdmin:        (state) => state.usuario?.rol === 'admin',
    esDirOps:       (state) => state.usuario?.rol === 'dir_ops',
    esMantenimiento:(state) => state.usuario?.rol === 'mantenimiento',
    esAuditor:      (state) => state.usuario?.rol === 'auditor_uaeac',

    // Puede ver cualquier cosa de operaciones
    puedeOperaciones: (state) => ['dir_ops', 'instructor', 'auditor_uaeac'].includes(state.usuario?.rol),
  },

  actions: {
    async login(email, password) {
      this.cargando = true
      try {
        const { data } = await api.post('/auth/login', { email, password })

        this.token   = data.token
        this.usuario = data.usuario

        localStorage.setItem('rac141_token', data.token)
        localStorage.setItem('rac141_usuario', JSON.stringify(data.usuario))

        // Cargar permisos completos
        await this.cargarPerfil()

        return { ok: true }
      } catch (error) {
        const mensaje = error.response?.data?.mensaje || 'Error al iniciar sesión'
        return { ok: false, mensaje }
      } finally {
        this.cargando = false
      }
    },

    async cargarPerfil() {
      try {
        const { data } = await api.get('/auth/me')
        this.usuario = data.data
        localStorage.setItem('rac141_usuario', JSON.stringify(data.data))
      } catch {
        // Si falla no interrumpir
      }
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } finally {
        this.token   = null
        this.usuario = null
        localStorage.removeItem('rac141_token')
        localStorage.removeItem('rac141_usuario')
      }
    },

    puede(modulo, accion) {
      if (this.esDirOps || this.esAuditor) return true
      return this.permisos?.some(
        (p) => p.modulo === modulo && p.accion === accion
      ) ?? false
    },
  },
})
