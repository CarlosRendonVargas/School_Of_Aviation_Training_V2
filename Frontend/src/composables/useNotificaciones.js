// src/composables/useNotificaciones.js
/**
 * useNotificaciones
 * ─────────────────
 * Gestiona notificaciones push nativas (Capacitor) y notificaciones
 * en navegador web (Notification API), con fallback a Quasar Notify.
 *
 * - En web:    usa Notification API del navegador si el usuario acepta
 * - En móvil:  usa @capacitor/push-notifications para alertas nativas
 * - Fallback:  Quasar Notify (siempre funciona)
 *
 * Alertas RAC 141 que dispara este composable:
 *   - Certificado médico próximo a vencer (RAC 67)
 *   - Licencia instructor próxima a vencer (RAC 65)
 *   - Aeronavegabilidad próxima a vencer (RAC 141.51)
 *   - Ítems MEL con fecha límite próxima (RAC 91)
 *   - Reportes SMS de riesgo inaceptable (OACI Anexo 19)
 */
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

export function useNotificaciones() {
  const $q           = useQuasar()
  const router       = useRouter()
  const permiso      = ref('default') // 'granted' | 'denied' | 'default'
  const esCapacitor  = ref(typeof window !== 'undefined' && !!window.Capacitor)

  // ── Inicialización ──────────────────────────────────────────────────────────
  async function inicializar() {
    if (esCapacitor.value) {
      await inicializarCapacitor()
    } else {
      await inicializarWeb()
    }
  }

  // ── Web: Notification API ───────────────────────────────────────────────────
  async function inicializarWeb() {
    if (!('Notification' in window)) return

    permiso.value = Notification.permission

    if (permiso.value === 'default') {
      const resultado = await Notification.requestPermission()
      permiso.value = resultado
    }
  }

  // ── Capacitor: Push Notifications ──────────────────────────────────────────
  async function inicializarCapacitor() {
    try {
      const { PushNotifications } = await import('@capacitor/push-notifications')

      const result = await PushNotifications.requestPermissions()
      if (result.receive === 'granted') {
        permiso.value = 'granted'
        await PushNotifications.register()

        // Escuchar notificaciones en primer plano
        PushNotifications.addListener('pushNotificationReceived', (notification) => {
          notificarQuasar(notification.title, notification.body, notification.data?.ruta)
        })

        // Escuchar tap en notificación
        PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
          const ruta = action.notification.data?.ruta
          if (ruta) router.push(ruta)
        })
      }
    } catch (e) {
      // Capacitor no disponible, continuar sin push
      console.warn('Push notifications no disponibles:', e.message)
    }
  }

  // ── Enviar notificación ─────────────────────────────────────────────────────
  function notificar(titulo, cuerpo, opciones = {}) {
    const {
      tipo  = 'info',    // 'info' | 'warning' | 'negative' | 'positive'
      ruta  = null,      // ruta Vue Router al hacer clic
      icono = 'notifications',
    } = opciones

    // 1. Intentar notificación nativa del navegador
    if (!esCapacitor.value && permiso.value === 'granted' && document.hidden) {
      const n = new Notification(titulo, {
        body: cuerpo,
        icon: '/icons/icon-128x128.png',
        badge: '/icons/icon-128x128.png',
        tag: ruta || 'rac141',
      })
      if (ruta) n.onclick = () => { window.focus(); router.push(ruta) }
      return
    }

    // 2. Fallback: Quasar Notify (siempre visible en app)
    notificarQuasar(titulo, cuerpo, ruta, tipo, icono)
  }

  function notificarQuasar(titulo, cuerpo, ruta, tipo = 'info', icono = 'notifications') {
    $q.notify({
      type: tipo,
      icon: icono,
      message: titulo,
      caption: cuerpo,
      timeout: 6000,
      position: 'top-right',
      actions: ruta ? [{
        label: 'Ver',
        color: 'white',
        handler: () => router.push(ruta),
      }] : [],
    })
  }

  // ── Alertas específicas RAC 141 ─────────────────────────────────────────────

  /** Alerta de vencimiento próximo con nivel de urgencia */
  function alertarVencimiento(descripcion, diasRestantes, ruta = '/vencimientos') {
    const nivel = diasRestantes <= 0   ? { tipo: 'negative', texto: 'VENCIDO', icono: 'error' }
                : diasRestantes <= 15  ? { tipo: 'negative', texto: `${diasRestantes}d`, icono: 'warning' }
                : diasRestantes <= 30  ? { tipo: 'warning',  texto: `${diasRestantes}d`, icono: 'schedule' }
                :                        { tipo: 'info',     texto: `${diasRestantes}d`, icono: 'info' }

    notificar(
      `⚠️ Vencimiento: ${nivel.texto}`,
      descripcion,
      { tipo: nivel.tipo, ruta, icono: nivel.icono }
    )
  }

  /** Alerta de reporte SMS de riesgo inaceptable */
  function alertarSmsInaceptable(nivel) {
    notificar(
      '🚨 Riesgo inaceptable SMS',
      `Nuevo reporte con nivel de riesgo ${nivel} — Acción inmediata requerida`,
      { tipo: 'negative', ruta: '/sms', icono: 'security' }
    )
  }

  /** Notificación de vuelo confirmado */
  function notificarVueloConfirmado(matricula, fecha, hora) {
    notificar(
      '✈️ Vuelo confirmado',
      `${matricula} · ${fecha} a las ${hora}`,
      { tipo: 'positive', ruta: '/reservas', icono: 'flight' }
    )
  }

  /** Verificar y alertar vencimientos críticos (ejecutar al iniciar app) */
  async function verificarVencimientosCriticos(vencimientos = []) {
    const criticos = vencimientos.filter(v => v.dias_restantes <= 15)
    if (criticos.length === 0) return

    // Una sola notificación resumen para no saturar
    if (criticos.length === 1) {
      alertarVencimiento(criticos[0].descripcion, criticos[0].dias_restantes)
    } else {
      notificar(
        `⚠️ ${criticos.length} vencimientos críticos`,
        'Hay elementos RAC que requieren atención inmediata',
        { tipo: 'negative', ruta: '/vencimientos', icono: 'warning' }
      )
    }
  }

  onMounted(inicializar)

  return {
    permiso,
    esCapacitor,
    inicializar,
    notificar,
    alertarVencimiento,
    alertarSmsInaceptable,
    notificarVueloConfirmado,
    verificarVencimientosCriticos,
  }
}
