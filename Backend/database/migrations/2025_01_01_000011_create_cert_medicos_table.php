<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cert_medicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->enum('tipo', ['clase_1', 'clase_2', 'clase_3']);
            $table->string('numero_certificado', 30);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');       // alerta 60 días antes
            $table->string('centro_aeromedico', 120);
            $table->text('restricciones')->nullable();
            $table->string('archivo_url', 300)->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'fecha_vencimiento']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_medicos');
    }
};
