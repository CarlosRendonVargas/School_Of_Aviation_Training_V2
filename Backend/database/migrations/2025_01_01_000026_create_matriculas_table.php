<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('programa_id')->constrained('programas')->restrictOnDelete();
            $table->date('fecha_matricula');
            $table->decimal('valor_total', 14, 2);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->enum('forma_pago', ['contado', 'cuotas', 'financiado'])->default('contado');
            $table->unsignedTinyInteger('num_cuotas')->nullable();
            $table->enum('estado', ['activa', 'suspendida', 'finalizada', 'cancelada'])->default('activa');
            $table->string('contrato_url', 300)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
