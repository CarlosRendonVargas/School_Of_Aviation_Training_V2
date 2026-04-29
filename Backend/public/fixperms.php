<?php
/**
 * FIXPERMS — Corrector de permisos para cPanel
 * Sube a public_html/fixperms.php — ELIMINAR después de usar
 */

// Seguridad básica
define('FIX_KEY', 'Aviation2050Fix');
$key = $_GET['key'] ?? '';
if ($key !== FIX_KEY) {
    die('<h2 style="font-family:sans-serif;color:red;">Accede con ?key=Aviation2050Fix</h2>');
}

$rootPath = realpath(__DIR__ . '/../aviation_app');
$action   = $_GET['run'] ?? '';

$dirs  = 0;
$files = 0;
$errors = [];

if ($action === 'go' && $rootPath) {
    // Recorre todo aviation_app/ recursivamente
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        if ($item->isDir()) {
            if (!@chmod($item->getPathname(), 0755)) {
                $errors[] = 'DIR: ' . $item->getPathname();
            }
            $dirs++;
        } else {
            if (!@chmod($item->getPathname(), 0644)) {
                $errors[] = 'FILE: ' . $item->getPathname();
            }
            $files++;
        }
    }

    // Los directorios de escritura necesitan 775
    $writableDirs = [
        $rootPath . '/storage',
        $rootPath . '/storage/logs',
        $rootPath . '/storage/framework',
        $rootPath . '/storage/framework/cache',
        $rootPath . '/storage/framework/sessions',
        $rootPath . '/storage/framework/views',
        $rootPath . '/storage/app',
        $rootPath . '/storage/app/public',
        $rootPath . '/bootstrap/cache',
    ];
    foreach ($writableDirs as $d) {
        if (is_dir($d)) @chmod($d, 0755);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Fix Permisos</title>
<style>
  body { font-family: 'Segoe UI', sans-serif; background: #0f172a; color: #e2e8f0; padding: 40px; }
  .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 28px; max-width: 640px; margin: auto; }
  h1 { font-size: 20px; margin-bottom: 8px; }
  p  { color: #94a3b8; font-size: 13px; margin-bottom: 20px; }
  .btn { display:inline-block; padding: 12px 28px; background:#dc2626; color:#fff; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px; }
  .btn:hover { background:#b91c1c; }
  .ok   { color: #4ade80; font-weight:bold; }
  .fail { color: #f87171; font-weight:bold; }
  .box  { background:#0f172a; border-radius:8px; padding:16px; margin-top:16px; font-size:13px; }
  pre   { white-space:pre-wrap; word-break:break-all; font-size:11px; color:#f87171; max-height:200px; overflow-y:auto; }
</style>
</head>
<body>
<div class="card">
  <h1>🔧 Corrector de Permisos — aviation_app/</h1>
  <p>Aplica <strong>755</strong> a directorios y <strong>644</strong> a archivos en todo <code>aviation_app/</code></p>

  <?php if (!$rootPath): ?>
    <p class="fail">❌ No se encontró la carpeta aviation_app/</p>
  <?php elseif ($action !== 'go'): ?>
    <p>Ruta detectada: <code><?= htmlspecialchars($rootPath) ?></code></p>
    <a href="?key=<?= FIX_KEY ?>&run=go" class="btn">▶ Ejecutar corrección de permisos</a>
  <?php else: ?>
    <div class="box">
      <?php if (empty($errors)): ?>
        <p class="ok">✅ Permisos corregidos sin errores</p>
      <?php else: ?>
        <p class="fail">⚠️ Completado con <?= count($errors) ?> errores</p>
        <pre><?= htmlspecialchars(implode("\n", array_slice($errors, 0, 30))) ?><?= count($errors) > 30 ? "\n... y " . (count($errors)-30) . " más" : '' ?></pre>
      <?php endif; ?>
      <p style="margin-top:12px;color:#94a3b8;">
        📁 Directorios procesados: <strong><?= $dirs ?></strong><br>
        📄 Archivos procesados: <strong><?= $files ?></strong>
      </p>
    </div>

    <div style="margin-top:20px;padding:12px 16px;background:#172554;border:1px solid #1d4ed8;border-radius:8px;font-size:12px;color:#93c5fd;">
      ✅ <strong>Siguiente paso:</strong> Vuelve a abrir
      <a href="/install.php?key=Aviation2050Deploy" style="color:#60a5fa;">install.php</a>
      — ahora debería funcionar.<br><br>
      🔐 <strong>Elimina <code>fixperms.php</code></strong> desde File Manager cuando termines.
    </div>
  <?php endif; ?>
</div>
</body>
</html>
