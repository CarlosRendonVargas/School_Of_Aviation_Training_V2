<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_instructor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructores')->restrictOnDelete();
            $table->foreignId('evaluado_por')->constrained('usuarios')->restrictOnDelete();
            $table->enum('tipo', ['standardization_check', 'evaluacion_anual', 'proficiency_check', 'observacion_clase']);
            $table->date('fecha');
            $table->decimal('puntaje', 5, 2);
            $table->enum('resultado', ['satisfactorio', 'requiere_mejora', 'no_satisfactorio']);
            $table->json('areas_evaluadas')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('acciones_requeridas')->nullable();
            $table->date('proxima_evaluacion')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_instructor');
    }
};
