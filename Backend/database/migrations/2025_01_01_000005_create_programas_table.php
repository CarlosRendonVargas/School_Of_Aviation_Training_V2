<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();     // PPL-AV | CPL-AV | ATPL-AV | HB-IFR
            $table->string('nombre', 120);
            $table->enum('tipo', ['PPL', 'CPL', 'ATPL', 'HABILITACION']);
            $table->decimal('horas_tierra_min', 6, 1);
            $table->decimal('horas_vuelo_min', 6, 1);
            $table->decimal('horas_dual_min', 6, 1);
            $table->decimal('horas_solo_min', 6, 1);
            $table->decimal('horas_ifr_min', 6, 1)->nullable();
            $table->decimal('horas_noche_min', 6, 1)->nullable();
            $table->decimal('horas_sim_max', 6, 1)->nullable();  // máx. acreditables en simulador
            $table->string('rac_referencia', 30);               // Ej: RAC 61.109
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas');
    }
};
