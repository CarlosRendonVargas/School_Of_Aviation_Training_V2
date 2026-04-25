<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->text('objetivos')->nullable()->after('tipo');
            $table->enum('confirmacion_estudiante', ['pendiente', 'aceptada', 'rechazada'])
                  ->default('pendiente')->after('objetivos');
            $table->timestamp('confirmacion_expira')->nullable()->after('confirmacion_estudiante');
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['objetivos', 'confirmacion_estudiante', 'confirmacion_expira']);
        });
    }
};
