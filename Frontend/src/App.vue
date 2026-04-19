<template>
  <router-view />
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useAuthStore } from 'store/auth'
import { useVencimientosStore } from 'store/vencimientos'

const authStore         = useAuthStore()
const vencimientosStore = useVencimientosStore()

watch(
  () => authStore.autenticado,
  async (autenticado) => {
    if (autenticado) await vencimientosStore.cargar()
  },
  { immediate: true }
)

onMounted(() => {
  const timer = setInterval(() => {
    if (authStore.autenticado) vencimientosStore.cargar()
  }, 10 * 60 * 1000)
  return () => clearInterval(timer)
})
</script>
