<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<body style='background:#000;color:#0f0;font-family:monospace;padding:20px;'>";
echo "<h1>Ultimos Errores de Laravel</h1>";

$path = realpath(__DIR__ . '/../aviation_app/storage/logs/laravel.log');

if ($path && file_exists($path)) {
    $lines = file($path);
    // Buscamos las líneas que empiezan con "[" (fecha del log)
    $errors = array_filter($lines, function($line) {
        return str_contains($line, 'local.ERROR') || str_contains($line, 'production.ERROR');
    });
    
    $lastErrors = array_slice($errors, -5); // Ultimos 5 errores reales
    
    foreach(array_reverse($lastErrors) as $error) {
        echo "<div style='border:1px solid #333;padding:15px;margin-bottom:20px;background:#111;'>";
        echo "<strong style='color:#f00;'>ERROR DETECTADO:</strong><br>";
        echo htmlspecialchars($error);
        echo "</div>";
    }
} else {
    echo "No se encontró el log.";
}
?>
