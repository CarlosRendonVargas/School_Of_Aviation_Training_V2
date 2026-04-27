<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->unsignedTinyInteger('peso_quices')->default(40)->after('max_intentos');
            $table->unsignedTinyInteger('peso_examen')->default(60)->after('peso_quices');
        });
    }

    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn(['peso_quices', 'peso_examen']);
        });
    }
};
