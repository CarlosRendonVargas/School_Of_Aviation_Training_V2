// src/store/vencimientos.js
import { defineStore } from 'pinia'
import { api } from 'boot/axios'

export const useVencimientosStore = defineStore('vencimientos', {
  state: () => ({
    items:   [],
    vencidos: [],
    cargando: false,
    ultimaActualizacion: null,
  }),

  getters: {
    criticos:     (state) => state.items.filter(v => v.nivel_critico === 'critico'),
    advertencias: (state) => state.items.filter(v => v.nivel_critico === 'advertencia'),
    totalAlertas: (state) => state.items.length + state.vencidos.length,
  },

  actions: {
    async cargar() {
      this.cargando = true
      try {
        const [proximos, vencidos] = await Promise.all([
          api.get('/vencimientos?dias=365'),
          api.get('/vencimientos/vencidos'),
        ])
        this.items    = proximos.data.data
        this.vencidos = vencidos.data.data
        this.ultimaActualizacion = new Date()
      } catch {
        // Silencioso
      } finally {
        this.cargando = false
      }
    },
  },
})
