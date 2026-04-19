<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('materias', 'max_intentos')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->integer('max_intentos')->default(1);
        $table->decimal('costo_reintento', 10, 2)->default(0);
    });
}

if (!Schema::hasColumn('notas_academicas', 'pagado')) {
    Schema::table('notas_academicas', function (Blueprint $table) {
        $table->boolean('pagado')->default(true); // El primero suele ser gratis (incluido), el resto depende.
    });
}

echo "Columnas de intentos y costos agregadas.\n";
