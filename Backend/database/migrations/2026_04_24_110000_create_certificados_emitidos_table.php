<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificados_emitidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_certificado', 20)->unique();
            $table->enum('tipo', ['finalizacion_etapa', 'finalizacion_programa', 'constancia_horas', 'constancia_matricula', 'aprobacion_examen', 'endorsement']);
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('emitido_por')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('etapa_id')->nullable()->constrained('etapas')->nullOnDelete();
            $table->foreignId('programa_id')->nullable()->constrained('programas')->nullOnDelete();
            $table->date('fecha_emision');
            $table->text('descripcion')->nullable();
            $table->string('archivo_url', 500)->nullable();
            $table->boolean('anulado')->default(false);
            $table->text('motivo_anulacion')->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificados_emitidos');
    }
};
