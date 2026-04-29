<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('/', function () {
    $path = public_path('app.php');
    if (File::exists($path)) {
        return file_get_contents($path);
    }
    return view('welcome');
});

// Fallback para que las rutas de Quasar (como /login, /dashboard) funcionen al refrescar
Route::fallback(function () {
    $path = public_path('app.php');
    if (File::exists($path)) {
        return file_get_contents($path);
    }
    return response('Frontend no encontrado (app.php missing)', 404);
});

