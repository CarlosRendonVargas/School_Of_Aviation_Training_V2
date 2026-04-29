<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Raíz de Laravel — un nivel arriba de public_html
define('LARAVEL_ROOT', __DIR__ . '/../aviation_app');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = LARAVEL_ROOT . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require LARAVEL_ROOT . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once LARAVEL_ROOT . '/bootstrap/app.php';

// IMPORTANTE: Decirle a Laravel que esta carpeta es su carpeta pública
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
