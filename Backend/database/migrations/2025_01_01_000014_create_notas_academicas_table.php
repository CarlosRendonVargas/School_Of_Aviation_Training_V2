<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_academicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('instructores')->restrictOnDelete();
            $table->decimal('nota', 5, 2);           // 0.00 – 100.00
            $table->boolean('aprobado');             // nota >= materia.nota_minima
            $table->unsignedTinyInteger('intento_num')->default(1);
            $table->date('fecha_evaluacion');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'materia_id']);
            $table->index(['estudiante_id', 'aprobado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas_academicas');
    }
};
