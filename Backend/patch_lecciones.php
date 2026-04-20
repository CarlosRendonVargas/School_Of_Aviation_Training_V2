<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('lecciones_materia')) {
    Schema::create('lecciones_materia', function (Blueprint $table) {
        $table->id();
        $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
        $table->string('titulo');
        $table->text('descripcion')->nullable();
        $table->string('video_url')->nullable();
        $table->string('documento_url')->nullable();
        $table->integer('orden')->default(0);
        $table->boolean('activo')->default(true);
        $table->timestamps();
    });
    echo "Table 'lecciones_materia' created successfully.\n";
} else {
    echo "Table 'lecciones_materia' already exists.\n";
}
