<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->cascadeOnDelete();
            $table->string('num_expediente', 20)->unique();   // RAC 141.77
            $table->foreignId('programa_id')->constrained('programas')->restrictOnDelete();
            $table->date('fecha_ingreso');
            $table->enum('estado', ['activo', 'suspendido', 'graduado', 'retirado'])->default('activo');
            $table->foreignId('etapa_actual_id')->nullable()->constrained('etapas')->nullOnDelete();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index(['programa_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
