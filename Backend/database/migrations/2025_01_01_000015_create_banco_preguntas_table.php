<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banco_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->text('pregunta');
            $table->enum('tipo', ['multiple', 'verdadero_falso', 'abierta']);
            // JSON: ["opción A","opción B","opción C","opción D"]
            $table->json('opciones')->nullable();
            $table->text('respuesta_correcta');      // almacenada encriptada
            $table->unsignedTinyInteger('nivel_dificultad')->default(1); // 1=básico 2=medio 3=avanzado
            $table->string('rac_referencia', 40)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['materia_id', 'nivel_dificultad', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banco_preguntas');
    }
};
