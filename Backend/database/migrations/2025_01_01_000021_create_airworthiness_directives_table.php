<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('airworthiness_directives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->cascadeOnDelete();
            $table->string('numero_ad', 30);            // Ej: 2024-18-02
            $table->string('titulo', 200);
            $table->date('fecha_emision');
            $table->date('fecha_limite')->nullable();
            $table->enum('estado', ['pendiente', 'cumplido', 'no_aplica'])->default('pendiente');
            $table->text('metodo_cumplimiento')->nullable();
            $table->string('archivo_url', 300)->nullable();
            $table->timestamps();

            $table->index(['aeronave_id', 'estado']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airworthiness_directives');
    }
};
