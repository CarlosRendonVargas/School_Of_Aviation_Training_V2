<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('programas')->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');       // orden de la etapa (1, 2, 3…)
            $table->string('nombre', 100);
            $table->decimal('horas_tierra', 5, 1)->default(0);
            $table->decimal('horas_vuelo', 5, 1)->default(0);
            $table->text('descripcion')->nullable();
            // etapa previa requerida (autoreferencia)
            $table->foreignId('prereq_etapa_id')->nullable()->constrained('etapas')->nullOnDelete();
            $table->timestamps();

            $table->unique(['programa_id', 'numero']);
            $table->index('programa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas');
    }
};
