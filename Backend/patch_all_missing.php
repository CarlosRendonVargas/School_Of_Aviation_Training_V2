<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// patch_lms: link_meet, documento_url, temario
if (!Schema::hasColumn('materias', 'link_meet')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->string('link_meet')->nullable();
        $table->string('documento_url')->nullable();
        $table->text('temario')->nullable();
    });
    echo "LMS columns added.\n";
}

// patch_exam_limits: max_intentos, costo_reintento
if (!Schema::hasColumn('materias', 'max_intentos')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->integer('max_intentos')->default(1);
        $table->decimal('costo_reintento', 10, 2)->default(0);
    });
    echo "Exam limits added.\n";
}

// patch_exam_time: duracion_minutos
if (!Schema::hasColumn('materias', 'duracion_minutos')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->integer('duracion_minutos')->default(15);
    });
    echo "Exam time added.\n";
}

// And for the exam limit on Estudiante:
if (!Schema::hasColumn('estudiantes', 'examenes_disponibles')) {
    Schema::table('estudiantes', function (Blueprint $table) {
        $table->integer('examenes_disponibles')->default(1);
    });
    echo "Examenes_disponibles added to estudiantes.\n";
}

echo "All patches applied.\n";
