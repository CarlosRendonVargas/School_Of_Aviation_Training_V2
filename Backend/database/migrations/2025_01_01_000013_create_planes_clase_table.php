<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_clase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructores')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->restrictOnDelete();
            $table->date('fecha');
            $table->unsignedSmallInteger('duracion_min');
            $table->text('objetivos');
            $table->text('contenido');
            $table->text('recursos')->nullable();
            $table->enum('estado', ['planificada', 'realizada', 'cancelada'])->default('planificada');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'fecha']);
            $table->index(['materia_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_clase');
    }
};
