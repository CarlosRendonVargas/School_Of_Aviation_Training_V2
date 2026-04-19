<template>
  <q-page class="rac-page flex flex-center">
    <div style="text-align:center">
      <div style="font-size:60px;margin-bottom:16px">🚫</div>
      <h2 style="font-family:'Syne',sans-serif;color:#fff">Sin permiso</h2>
      <p style="color:rgba(255,255,255,.5)">No tienes acceso a esta sección.</p>
      <q-btn label="Ir al dashboard" color="primary" unelevated to="/dashboard" class="q-mt-md" />
    </div>
  </q-page>
</template>
<script setup></script>
<style scoped>
.rac-page { background: #0a0c10; min-height: 100vh; }
</style>
