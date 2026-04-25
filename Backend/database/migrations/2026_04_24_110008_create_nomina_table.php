<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomina_periodos', function (Blueprint $table) {
            $table->id();
            $table->string('periodo', 7)->unique(); // 2026-04
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['abierto', 'procesado', 'pagado'])->default('abierto');
            $table->decimal('total_pagado', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('nomina_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periodo_id')->constrained('nomina_periodos')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('cargo', 100);
            $table->decimal('salario_base', 12, 2);
            $table->decimal('horas_extra', 5, 1)->default(0);
            $table->decimal('valor_hora_extra', 10, 2)->default(0);
            $table->decimal('bonificaciones', 12, 2)->default(0);
            $table->decimal('deducciones', 12, 2)->default(0);
            $table->decimal('salud', 12, 2)->default(0);
            $table->decimal('pension', 12, 2)->default(0);
            $table->decimal('neto_pagar', 12, 2);
            $table->enum('estado', ['pendiente', 'pagado'])->default('pendiente');
            $table->timestamps();

            $table->unique(['periodo_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomina_items');
        Schema::dropIfExists('nomina_periodos');
    }
};
