<template>
  <div>
    <div v-if="cargando" class="flex flex-center q-pa-xl"><q-spinner-tail color="blue-4" size="40px" /></div>
    <div v-else class="rac-card q-pa-lg">
      <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:700;color:#fff;margin-bottom:8px">Panel cargado</div>
      <pre style="font-size:11px;color:rgba(255,255,255,.4);overflow:auto">{{ JSON.stringify(data, null, 2) }}</pre>
    </div>
  </div>
</template>
<script setup>
const props = defineProps({
  data:     { type: Object,  default: null },
  cargando: { type: Boolean, default: false },
})
</script>
