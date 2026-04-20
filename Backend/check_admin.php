<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

$email = 'admin@escuelaviacion.co';
$pass = 'Rac141*2025';

$u = Usuario::where('email', $email)->first();
if ($u) {
    echo "Usuario: " . $u->email . "\n";
    echo "Pass OK: " . (Hash::check($pass, $u->password_hash) ? "SI" : "NO") . "\n";
    echo "Activo: " . ($u->activo ? "SI" : "NO") . "\n";
} else {
    echo "Usuario $email no encontrado.\n";
}
