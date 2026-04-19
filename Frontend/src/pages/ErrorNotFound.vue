<template>
  <q-layout>
    <q-page-container>
      <q-page class="window-height flex flex-center" style="background:#0a0c10">
        <div class="text-center">
          <div class="font-head text-primary" style="font-size:80px;font-weight:800;line-height:1;opacity:.3">404</div>
          <div class="font-head text-white q-mt-md" style="font-size:22px;font-weight:700">Página no encontrada</div>
          <div class="q-mt-sm" style="font-size:14px;color:#64748b">La ruta que busca no existe en el sistema</div>
          <q-btn unelevated color="primary" no-caps label="Volver al inicio" icon="home"
            class="q-mt-xl" to="/dashboard" style="border-radius:8px" />
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>
<script setup></script>
