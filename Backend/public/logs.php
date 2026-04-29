<?php
/**
 * LOG VIEWER — Escuela Aviación RAC141
 * Sube a public_html/logs.php — ELIMINAR después de usar
 */

define('LOG_KEY', 'Aviation2050Logs');
$key = $_GET['key'] ?? '';
if ($key !== LOG_KEY) die('Acceso denegado');

$logFile = __DIR__ . '/../aviation_app/storage/logs/laravel.log';

if (!file_exists($logFile)) {
    die('El archivo de log no existe todavía. Laravel no ha generado errores registrados.');
}

$content = shell_exec('tail -n 50 ' . escapeshellarg($logFile));
if (!$content) {
    // Si tail no funciona, leemos con PHP
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    $content = implode("", $lastLines);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Log Viewer</title>
    <style>
        body { background: #1a1a1a; color: #00ff00; font-family: monospace; padding: 20px; }
        pre { white-space: pre-wrap; word-break: break-all; }
        h1 { color: #fff; font-size: 18px; }
    </style>
</head>
<body>
    <h1>Últimas 50 líneas de laravel.log</h1>
    <pre><?php echo htmlspecialchars($content); ?></pre>
</body>
</html>
