<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('materias', 'link_meet')) {
    Schema::table('materias', function (Blueprint $table) {
        $table->string('link_meet')->nullable();
        $table->string('documento_url')->nullable();
        $table->text('temario')->nullable();
    });
    echo "Columnas agregadas a materias.\n";
} else {
    echo "Columnas ya existen en materias.\n";
}
