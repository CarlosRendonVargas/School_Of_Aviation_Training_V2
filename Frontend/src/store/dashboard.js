// src/store/dashboard.js
import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    datos:          null,
    cargando:       false,
    ultimaCarga:    null,
    // Cache de horas por estudiante
    progresoHoras:  {},
    historialHoras: {},
  }),

  getters: {
    esFresh: (state) => {
      if (!state.ultimaCarga) return false
      // Considerar fresco si tiene menos de 5 minutos
      return (Date.now() - state.ultimaCarga) < 5 * 60 * 1000
    },
  },

  actions: {
    async cargar(forzar = false) {
      if (this.esFresh && !forzar) return

      this.cargando = true
      try {
        const { data } = await api.get('/dashboard')
        this.datos       = data.data
        this.ultimaCarga = Date.now()
      } catch (e) {
        console.error('Error cargando dashboard:', e)
      } finally {
        this.cargando = false
      }
    },

    async cargarProgresoEstudiante(estudianteId) {
      if (this.progresoHoras[estudianteId]) return this.progresoHoras[estudianteId]
      try {
        const { data } = await api.get(`/estudiantes/${estudianteId}/progreso-horas`)
        this.progresoHoras[estudianteId] = data.data
        return data.data
      } catch { return null }
    },

    async cargarHistorial(estudianteId) {
      if (this.historialHoras[estudianteId]) return this.historialHoras[estudianteId]
      try {
        const { data } = await api.get(`/estudiantes/${estudianteId}/historial-horas`)
        this.historialHoras[estudianteId] = data.data || []
        return data.data || []
      } catch { return [] }
    },

    limpiar() {
      this.datos       = null
      this.ultimaCarga = null
      this.progresoHoras  = {}
      this.historialHoras = {}
    },
  },
})
