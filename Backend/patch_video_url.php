<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('materias', 'video_url')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->string('video_url')->nullable();
    });
    echo "Columna video_url agregada a materias.\n";
} else {
    echo "Columna video_url ya existe en materias.\n";
}
