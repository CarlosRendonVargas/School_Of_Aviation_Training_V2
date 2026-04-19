<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->restrictOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructores')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->enum('tipo', ['instruccion', 'solo', 'simulador']);
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'completada'])->default('pendiente');
            $table->text('motivo_cancelacion')->nullable();
            $table->timestamps();

            // Evitar doble reserva de aeronave en mismo slot
            $table->index(['aeronave_id', 'fecha', 'hora_inicio']);
            $table->index(['estudiante_id', 'fecha']);
            $table->index(['instructor_id', 'fecha']);
            $table->index(['fecha', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
