<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_id')->constrained('etapas')->cascadeOnDelete();
            $table->string('codigo', 15);              // METEO-01, NAV-02, FH-01
            $table->string('nombre', 120);
            $table->decimal('horas', 5, 1);
            $table->enum('tipo', ['teorica', 'practica', 'simulador']);
            $table->decimal('nota_minima', 5, 2)->default(70.00);
            $table->string('rac_referencia', 40)->nullable();
            $table->timestamps();

            $table->unique(['etapa_id', 'codigo']);
            $table->index('etapa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
