<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('materias', 'duracion_minutos')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->integer('duracion_minutos')->default(15); // 15 min por defecto
    });
}

echo "Columna duracion_minutos agregada.\n";
