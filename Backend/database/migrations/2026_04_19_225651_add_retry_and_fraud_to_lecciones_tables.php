<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lecciones_materia', function (Blueprint $table) {
            $table->integer('max_intentos')->default(3)->after('activo');
        });

        Schema::table('notas_lecciones', function (Blueprint $table) {
            $table->integer('intentos')->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('lecciones_materia', function (Blueprint $table) {
            $table->dropColumn('max_intentos');
        });

        Schema::table('notas_lecciones', function (Blueprint $table) {
            $table->dropColumn('intentos');
        });
    }
};
