# ✈️ RAC 141 Aviación — Frontend Quasar

Sistema de gestión para escuelas de aviación RAC 141 Colombia.
Vue 3 + Quasar Framework — compatible con XAMPP y cPanel.

---

## 🖥️ Instalación en XAMPP (Windows)

### Requisitos previos
- Node.js 18+ — https://nodejs.org
- npm 9+ (viene con Node.js)
- Quasar CLI: `npm install -g @quasar/cli`
- XAMPP con PHP 8.2+ y MySQL 8.0

### Paso 1 — Instalar dependencias

```bash
cd C:\xampp\htdocs\SchoolOfAviationT\Frontend
npm install
```

### Paso 2 — Configurar la URL del backend

Crear el archivo `.env` en la raíz del proyecto Frontend:

```
VITE_API_URL=http://localhost:8000/api/v1
```

> Si usas `php artisan serve` en el Backend, el puerto es 8000.
> Si usas XAMPP directamente, ajusta la URL:
> `VITE_API_URL=http://localhost/SchoolOfAviationT/Backend/public/api/v1`

### Paso 3 — Levantar el servidor de desarrollo

```bash
# Terminal 1: Backend Laravel
cd C:\xampp\htdocs\SchoolOfAviationT\Backend
php artisan serve

# Terminal 2: Frontend Quasar
cd C:\xampp\htdocs\SchoolOfAviationT\Frontend
npm run dev
npm.cmd run dev

```

El frontend abrirá automáticamente en: **http://localhost:9000**

---

## 🚀 Build para producción (cPanel)

```bash
npm run build
```

Resultado en `dist/spa/` — subir ese contenido a `public_html/` del cPanel.

### .htaccess para cPanel (crear en public_html/)

```apache
Options -MultiViews -Indexes
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.html [L]
```

> **Nota:** El proyecto usa `createWebHashHistory()` — las URLs tienen `#`.
> No necesitas .htaccess para que funcione. Si quieres URLs limpias,
> cambia a `createWebHistory()` en `src/router/index.js` y agrega el .htaccess.

---

## ❌ Solución de errores comunes

### `The file /index.html is missing`
El proyecto no tiene la estructura completa de Quasar.
**Solución:** Asegúrate de haber descomprimido el ZIP completo,
incluyendo `index.html` en la raíz (junto a `package.json`).

### `Cannot find module 'quasar/wrappers'`
Quasar no está instalado.
**Solución:** `npm install`

### `Failed to load resource: net::ERR_CONNECTION_REFUSED`
El backend Laravel no está corriendo.
**Solución:** Ejecutar `php artisan serve` en el directorio del Backend.

### Error de CORS en el navegador
El backend no tiene CORS configurado para el frontend.
**Solución en Laravel** — `config/cors.php`:
```php
'allowed_origins' => ['http://localhost:9000', 'http://localhost:8080'],
```

### `Refused to apply style from Google Fonts`
Si trabajas sin internet, las fuentes de Google no cargarán.
La app funciona igual, solo con fuentes del sistema.

---

## 📱 Build Android (APK)

```bash
# Instalar Capacitor
npm install @capacitor/core @capacitor/cli @capacitor/android

# Build
npm run build:android

# Abrir en Android Studio
npx cap open android
```

---

## 🗂️ Estructura del proyecto

```
Frontend/
├── index.html          ← OBLIGATORIO — Quasar lo requiere en la raíz
├── quasar.config.js    ← Configuración de Quasar
├── package.json        ← Dependencias npm
├── .env                ← URL del backend (crear manualmente)
├── postcss.config.cjs
├── .eslintrc.cjs
└── src/
    ├── App.vue             ← Componente raíz
    ├── boot/
    │   ├── pinia.js        ← Store global
    │   ├── axios.js        ← HTTP client + interceptores
    │   └── i18n.js         ← Español
    ├── css/
    │   ├── app.scss        ← Estilos globales
    │   └── quasar.variables.scss
    ├── router/
    │   └── index.js        ← Rutas + guards de roles
    ├── stores/
    │   ├── auth.js         ← Autenticación Pinia
    │   ├── dashboard.js    ← Cache del dashboard
    │   └── vencimientos.js ← Alertas RAC
    ├── composables/
    │   ├── useApi.js
    │   ├── useBitacora.js
    │   ├── useReserva.js
    │   └── useNotificaciones.js
    ├── utils/
    │   └── formatters.js   ← Formato COP, fechas, ICAO
    ├── layouts/
    │   └── MainLayout.vue  ← Sidebar + bottom nav móvil
    ├── components/
    │   ├── charts/         ← Chart.js: radar, barras, dona
    │   ├── common/         ← VencimientoBadge, HorasProgressBar
    │   └── forms/          ← BitacoraForm, ReservaForm
    └── pages/              ← 30 páginas organizadas por módulo
```

---

## 🔧 Tecnologías

| Paquete | Versión | Uso |
|---|---|---|
| Quasar | ^2.17 | UI Framework + Build tool |
| Vue 3 | ^3.5 | Framework reactivo |
| Pinia | ^2.3 | Estado global |
| Axios | ^1.7 | HTTP client |
| Chart.js | ^4.4 | Gráficas de progreso |
| Day.js | ^1.11 | Fechas en español |
