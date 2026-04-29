<?php
/**
 * DIAGNÓSTICO — Escuela Aviación RAC141
 * Subir a public_html/check.php — ELIMINAR después de usar
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diagnóstico del Servidor</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 30px; }
  h1 { color: #f8fafc; margin-bottom: 20px; }
  .section { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 20px; margin-bottom: 20px; }
  h2 { font-size: 15px; color: #94a3b8; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  td { padding: 8px 10px; border-bottom: 1px solid #1e293b; }
  td:first-child { color: #94a3b8; width: 45%; }
  td:last-child { font-family: monospace; color: #e2e8f0; }
  .ok   { color: #4ade80 !important; font-weight: bold; }
  .warn { color: #facc15 !important; font-weight: bold; }
  .fail { color: #f87171 !important; font-weight: bold; }
  .path-box { background: #0f172a; border-radius: 6px; padding: 10px 14px; font-family: monospace; font-size: 12px; color: #60a5fa; margin-bottom: 8px; word-break: break-all; }
</style>
</head>
<body>
<h1>🛩️ Diagnóstico del Servidor</h1>

<?php
// ─── 1. PHP INFO ─────────────────────────────────────────────
$phpVersion  = PHP_VERSION;
$phpOk       = version_compare($phpVersion, '8.2.0', '>=');
$maxExec     = ini_get('max_execution_time');
$memLimit    = ini_get('memory_limit');
$uploadMax   = ini_get('upload_max_filesize');
?>
<div class="section">
  <h2>🐘 PHP</h2>
  <table>
    <tr><td>Versión PHP</td><td class="<?= $phpOk ? 'ok' : 'fail' ?>"><?= $phpVersion ?> <?= $phpOk ? '✅ OK (>=8.2)' : '❌ Necesita PHP 8.2+' ?></td></tr>
    <tr><td>max_execution_time</td><td><?= $maxExec ?>s</td></tr>
    <tr><td>memory_limit</td><td><?= $memLimit ?></td></tr>
    <tr><td>upload_max_filesize</td><td><?= $uploadMax ?></td></tr>
    <tr><td>PDO MySQL</td><td class="<?= extension_loaded('pdo_mysql') ? 'ok' : 'fail' ?>"><?= extension_loaded('pdo_mysql') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
    <tr><td>OpenSSL</td><td class="<?= extension_loaded('openssl') ? 'ok' : 'fail' ?>"><?= extension_loaded('openssl') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
    <tr><td>mbstring</td><td class="<?= extension_loaded('mbstring') ? 'ok' : 'fail' ?>"><?= extension_loaded('mbstring') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
    <tr><td>tokenizer</td><td class="<?= extension_loaded('tokenizer') ? 'ok' : 'fail' ?>"><?= extension_loaded('tokenizer') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
    <tr><td>xml</td><td class="<?= extension_loaded('xml') ? 'ok' : 'fail' ?>"><?= extension_loaded('xml') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
    <tr><td>fileinfo</td><td class="<?= extension_loaded('fileinfo') ? 'ok' : 'fail' ?>"><?= extension_loaded('fileinfo') ? '✅ Activo' : '❌ No disponible' ?></td></tr>
  </table>
</div>

<?php
// ─── 2. RUTAS ────────────────────────────────────────────────
$publicDir   = __DIR__;                              // donde está check.php = public_html
$laravelRoot  = __DIR__ . '/../aviation_app';         // Laravel está en aviation_app
$vendorPath   = $laravelRoot . '/vendor/autoload.php';
$bootstrapPath = $laravelRoot . '/bootstrap/app.php';
$envPath      = $laravelRoot . '/.env';
$storagePath  = $laravelRoot . '/storage';
$cachePath    = $laravelRoot . '/bootstrap/cache';
?>
<div class="section">
  <h2>📁 Rutas Detectadas</h2>
  <p style="font-size:12px;color:#64748b;margin-bottom:10px;">El script asume que check.php está en public/ y Laravel está un nivel arriba</p>
  <div class="path-box">📂 public/: <?= $publicDir ?></div>
  <div class="path-box">📂 Laravel Root: <?= $laravelRoot ?></div>
  <table>
    <tr>
      <td>vendor/autoload.php</td>
      <td class="<?= file_exists($vendorPath) ? 'ok' : 'fail' ?>"><?= file_exists($vendorPath) ? '✅ Encontrado' : '❌ NO encontrado — falta vendor/' ?></td>
    </tr>
    <tr>
      <td>bootstrap/app.php</td>
      <td class="<?= file_exists($bootstrapPath) ? 'ok' : 'fail' ?>"><?= file_exists($bootstrapPath) ? '✅ Encontrado' : '❌ NO encontrado' ?></td>
    </tr>
    <tr>
      <td>.env</td>
      <td class="<?= file_exists($envPath) ? 'ok' : 'warn' ?>"><?= file_exists($envPath) ? '✅ Encontrado' : '⚠️ NO encontrado — renombra .env.production a .env' ?></td>
    </tr>
    <tr>
      <td>storage/ writable</td>
      <td class="<?= is_writable($storagePath) ? 'ok' : 'fail' ?>"><?= is_writable($storagePath) ? '✅ Con permisos de escritura' : '❌ Sin permisos de escritura (chmod 755)' ?></td>
    </tr>
    <tr>
      <td>bootstrap/cache/ writable</td>
      <td class="<?= is_writable($cachePath) ? 'ok' : 'fail' ?>"><?= is_writable($cachePath) ? '✅ Con permisos de escritura' : '❌ Sin permisos de escritura (chmod 755)' ?></td>
    </tr>
  </table>
</div>

<?php
// ─── 3. CONEXIÓN BD ──────────────────────────────────────────
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';
$dbHost = 'localhost'; $dbName = ''; $dbUser = ''; $dbPass = '';
if ($envContent) {
    preg_match('/^DB_HOST=(.+)$/m',     $envContent, $m); $dbHost = trim($m[1] ?? 'localhost');
    preg_match('/^DB_DATABASE=(.+)$/m', $envContent, $m); $dbName = trim($m[1] ?? '');
    preg_match('/^DB_USERNAME=(.+)$/m', $envContent, $m); $dbUser = trim($m[1] ?? '');
    preg_match('/^DB_PASSWORD=(.+)$/m', $envContent, $m); $dbPass = trim($m[1] ?? '');
}
$dbOk = false; $dbError = '';
if ($dbName && $dbUser) {
    try {
        $pdo = new PDO("mysql:host={$dbHost};dbname={$dbName};charset=utf8", $dbUser, $dbPass);
        $dbOk = true;
    } catch (Exception $e) {
        $dbError = $e->getMessage();
    }
}
?>
<div class="section">
  <h2>🗄️ Conexión a Base de Datos</h2>
  <table>
    <tr><td>Host</td><td><?= htmlspecialchars($dbHost) ?></td></tr>
    <tr><td>Base de datos</td><td><?= htmlspecialchars($dbName ?: '(no detectado — falta .env)') ?></td></tr>
    <tr><td>Usuario</td><td><?= htmlspecialchars($dbUser ?: '(no detectado — falta .env)') ?></td></tr>
    <tr>
      <td>Conexión</td>
      <td class="<?= $dbOk ? 'ok' : 'fail' ?>">
        <?= $dbOk ? '✅ Conectado correctamente' : '❌ Error: ' . htmlspecialchars($dbError) ?>
      </td>
    </tr>
  </table>
</div>

<?php
// ─── 4. RESUMEN ───────────────────────────────────────────────
$checks = [
    $phpOk,
    extension_loaded('pdo_mysql'),
    file_exists($vendorPath),
    file_exists($bootstrapPath),
    file_exists($envPath),
    is_writable($storagePath),
    $dbOk,
];
$passedAll = !in_array(false, $checks);
?>
<div class="section" style="border-color: <?= $passedAll ? '#16a34a' : '#dc2626' ?>;">
  <h2>📋 Resumen</h2>
  <?php if ($passedAll): ?>
    <p class="ok" style="font-size:15px;">✅ Todo OK — puedes ejecutar install.php</p>
  <?php else: ?>
    <p class="fail" style="font-size:15px;">❌ Hay problemas que resolver antes de ejecutar install.php</p>
    <p style="font-size:13px;color:#94a3b8;margin-top:8px;">Revisa los errores marcados en rojo arriba.</p>
  <?php endif; ?>
</div>

<p style="font-size:11px;color:#475569;text-align:center;">⚠️ Elimina check.php del servidor después de usarlo</p>
</body>
</html>
