<?php
/**
 * ============================================================
 *  INSTALADOR WEB — Escuela Aviación RAC141
 *  Solo para uso en despliegue inicial. ELIMINAR después de usar.
 * ============================================================
 */

// ─── Mostrar errores para diagnóstico ────────────────────────
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutos para las migraciones

// ─── Seguridad mínima: clave de acceso ───────────────────────
define('INSTALL_KEY', 'Aviation2050Deploy');

$key = $_GET['key'] ?? $_POST['key'] ?? '';
if ($key !== INSTALL_KEY) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red;">403 — Acceso denegado.<br><small>Agrega ?key=Aviation2050Deploy a la URL</small></h2>');
}

// ─── Bootstrap Laravel ───────────────────────────────────────
define('LARAVEL_START', microtime(true));
define('LARAVEL_ROOT', __DIR__ . '/../aviation_app');

$bootError = null;
$kernel    = null;

try {
    require LARAVEL_ROOT . '/vendor/autoload.php';
    $app    = require_once LARAVEL_ROOT . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
} catch (\Throwable $e) {
    $bootError = $e;
}

// ─── Helpers ─────────────────────────────────────────────────
function runArtisan($kernel, string $cmd, array $args = []): array
{
    $output = new Symfony\Component\Console\Output\BufferedOutput();
    $code   = $kernel->call($cmd, $args, $output);
    return ['cmd' => $cmd, 'code' => $code, 'output' => $output->fetch()];
}

function badge(int $code): string
{
    return $code === 0
        ? '<span style="background:#16a34a;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px">OK</span>'
        : '<span style="background:#dc2626;color:#fff;padding:2px 8px;border-radius:4px;font-size:12px">ERROR '.$code.'</span>';
}

// ─── Determinar acción ───────────────────────────────────────
$action = $_POST['action'] ?? '';
$results = [];

if ($action === 'run') {
    $steps = $_POST['steps'] ?? [];

    if (in_array('fix_sessions', $steps)) {
        try {
            // Leer .env manualmente para obtener credenciales
            $env = file_get_contents(LARAVEL_ROOT . '/.env');
            preg_match('/DB_HOST=(.*)/', $env, $host);
            preg_match('/DB_DATABASE=(.*)/', $env, $db);
            preg_match('/DB_USERNAME=(.*)/', $env, $user);
            preg_match('/DB_PASSWORD=(.*)/', $env, $pass);
            
            $dsn = "mysql:host=" . trim($host[1]) . ";dbname=" . trim($db[1]) . ";charset=utf8mb4";
            $pdo = new PDO($dsn, trim($user[1]), trim($pass[1]), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            $sql = "CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(191) NOT NULL PRIMARY KEY,
                user_id BIGINT UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                payload LONGTEXT NOT NULL,
                last_activity INT NOT NULL,
                INDEX sessions_user_id_index(user_id),
                INDEX sessions_last_activity_index(last_activity)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            
            $pdo->exec($sql);
            $results[] = ['cmd' => 'SQL CREATE TABLE sessions', 'code' => 0, 'output' => 'Tabla sessions creada con éxito vía PDO.'];
        } catch (\Exception $e) {
            $results[] = ['cmd' => 'SQL CREATE TABLE sessions', 'code' => 1, 'output' => 'Error PDO: ' . $e->getMessage()];
        }
    }
    if (in_array('migrate_fresh', $steps)) {
        $results[] = runArtisan($kernel, 'migrate:fresh', ['--force' => true]);
    }
    if (in_array('migrate', $steps)) {
        $results[] = runArtisan($kernel, 'migrate', ['--force' => true]);
    }
    if (in_array('seed_roles', $steps)) {
        $results[] = runArtisan($kernel, 'db:seed', ['--class' => 'RolesSeeder', '--force' => true]);
    }
    if (in_array('seed_usuarios', $steps)) {
        $results[] = runArtisan($kernel, 'db:seed', ['--class' => 'UsuariosSeeder', '--force' => true]);
    }
    if (in_array('seed_programas', $steps)) {
        $results[] = runArtisan($kernel, 'db:seed', ['--class' => 'ProgramasEscuelaSeeder', '--force' => true]);
    }
    if (in_array('config_cache', $steps)) {
        $results[] = runArtisan($kernel, 'config:cache');
    }
    if (in_array('route_cache', $steps)) {
        $results[] = runArtisan($kernel, 'route:cache');
    }
    if (in_array('storage_link', $steps)) {
        $results[] = runArtisan($kernel, 'storage:link');
    }
    if (in_array('optimize', $steps)) {
        $results[] = runArtisan($kernel, 'optimize');
    }
}

// ─── UI ──────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Instalador — Escuela Aviación RAC141</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Segoe UI', sans-serif;
    background: #0f172a;
    color: #e2e8f0;
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 40px 16px;
  }
  .card {
    background: #1e293b;
    border: 1px solid #334155;
    border-radius: 12px;
    padding: 32px;
    width: 100%;
    max-width: 720px;
  }
  h1 { font-size: 22px; color: #f8fafc; margin-bottom: 4px; }
  .subtitle { font-size: 13px; color: #94a3b8; margin-bottom: 28px; }
  .warning {
    background: #7f1d1d;
    border: 1px solid #dc2626;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 13px;
    color: #fca5a5;
    margin-bottom: 24px;
  }
  .steps-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 24px;
  }
  .step-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    padding: 12px 14px;
    cursor: pointer;
  }
  .step-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: #dc2626; cursor: pointer; }
  .step-label { font-size: 14px; color: #e2e8f0; }
  .step-desc { font-size: 11px; color: #64748b; margin-top: 2px; }
  .btn {
    display: block;
    width: 100%;
    padding: 14px;
    background: #dc2626;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
  }
  .btn:hover { background: #b91c1c; }
  .result-block {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  .result-item {
    background: #0f172a;
    border: 1px solid #334155;
    border-radius: 8px;
    overflow: hidden;
  }
  .result-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #1e293b;
    border-bottom: 1px solid #334155;
    font-size: 13px;
    font-weight: 600;
    color: #cbd5e1;
  }
  .result-output {
    padding: 12px 14px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: #94a3b8;
    white-space: pre-wrap;
    max-height: 200px;
    overflow-y: auto;
  }
  .delete-note {
    margin-top: 24px;
    padding: 12px 16px;
    background: #172554;
    border: 1px solid #1d4ed8;
    border-radius: 8px;
    font-size: 12px;
    color: #93c5fd;
  }
</style>
</head>
<body>
<div class="card">
  <h1>🛩️ Instalador — Escuela Aviación RAC141</h1>
  <p class="subtitle">Ejecuta los pasos de instalación sin necesidad de terminal SSH</p>

  <?php if ($bootError): ?>
  <div style="background:#450a0a;border:1px solid #dc2626;border-radius:8px;padding:16px;margin-bottom:20px;">
    <p style="color:#fca5a5;font-weight:bold;margin-bottom:8px;">❌ Error al iniciar Laravel:</p>
    <pre style="color:#fca5a5;font-size:12px;white-space:pre-wrap;word-break:break-all;margin:0;"><?= htmlspecialchars($bootError->getMessage() . "\n\nArchivo: " . $bootError->getFile() . " línea " . $bootError->getLine()) ?></pre>
  </div>
  <?php endif; ?>

  <div class="warning">
    ⚠️ <strong>IMPORTANTE:</strong> Elimina este archivo <code>install.php</code> de tu servidor inmediatamente después de usarlo.
  </div>

  <form method="POST" action="?key=<?= htmlspecialchars($key) ?>">
    <input type="hidden" name="action" value="run">

    <div class="steps-grid">
      <label class="step-item" style="border-color: #3b82f6;">
        <input type="checkbox" name="steps[]" value="fix_sessions" checked>
        <div>
          <div class="step-label" style="color:#3b82f6;">🛠️ Fix Sessions Table</div>
          <div class="step-desc">Crea la tabla de sesiones faltante</div>
        </div>
      </label>
      <label class="step-item" style="border-color: #f59e0b;">
        <input type="checkbox" name="steps[]" value="migrate_fresh">
        <div>
          <div class="step-label" style="color:#f59e0b;">🔥 Migrate Fresh</div>
          <div class="step-desc">BORRA TODO y crea tablas de cero</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="migrate" checked>
        <div>
          <div class="step-label">🗃️ Migrate</div>
          <div class="step-desc">Crea tablas (si no existen)</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="seed_roles" checked>
        <div>
          <div class="step-label">👥 Seed Roles</div>
          <div class="step-desc">Roles: Admin, Instructor, Estudiante</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="seed_usuarios" checked>
        <div>
          <div class="step-label">👤 Seed Usuarios</div>
          <div class="step-desc">Usuario administrador inicial</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="seed_programas">
        <div>
          <div class="step-label">📚 Seed Programas</div>
          <div class="step-desc">Programas académicos de aviación</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="storage_link" checked>
        <div>
          <div class="step-label">🔗 Storage Link</div>
          <div class="step-desc">Enlace simbólico para archivos públicos</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="config_cache" checked>
        <div>
          <div class="step-label">⚡ Config Cache</div>
          <div class="step-desc">Optimiza la configuración</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="route_cache" checked>
        <div>
          <div class="step-label">🗺️ Route Cache</div>
          <div class="step-desc">Optimiza el enrutamiento</div>
        </div>
      </label>
      <label class="step-item">
        <input type="checkbox" name="steps[]" value="optimize">
        <div>
          <div class="step-label">🚀 Optimize</div>
          <div class="step-desc">Optimización general de Laravel</div>
        </div>
      </label>
    </div>

    <button type="submit" class="btn">▶ Ejecutar pasos seleccionados</button>
  </form>

  <?php if (!empty($results)): ?>
  <div class="result-block">
    <?php foreach ($results as $r): ?>
    <div class="result-item">
      <div class="result-header">
        <span>php artisan <?= htmlspecialchars($r['cmd']) ?></span>
        <?= badge($r['code']) ?>
      </div>
      <div class="result-output"><?= htmlspecialchars(trim($r['output'])) ?: '(sin salida)' ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="delete-note">
    🔐 <strong>Seguridad:</strong> Una vez completada la instalación, elimina <code>public/install.php</code> desde el File Manager de cPanel para evitar acceso no autorizado.
  </div>
</div>
</body>
</html>
