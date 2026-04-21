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
        Schema::table('notas_academicas', function (Blueprint $table) {
            $table->integer('fraude_intentos')->default(0)->after('observaciones');
        });

        Schema::table('notas_lecciones', function (Blueprint $table) {
            $table->integer('fraude_intentos')->default(0)->after('intentos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_academicas', function (Blueprint $table) {
            $table->dropColumn('fraude_intentos');
        });

        Schema::table('notas_lecciones', function (Blueprint $table) {
            $table->dropColumn('fraude_intentos');
        });
    }
};
