<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasTable('notas_lecciones')) {
    Schema::create('notas_lecciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
        $table->foreignId('leccion_id')->constrained('lecciones_materia')->onDelete('cascade');
        $table->decimal('nota', 5, 2);
        $table->integer('aciertos');
        $table->integer('total');
        $table->timestamps();
    });
    echo "Table 'notas_lecciones' created successfully.\n";
} else {
    echo "Table 'notas_lecciones' already exists.\n";
}
