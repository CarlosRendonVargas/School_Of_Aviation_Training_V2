<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acciones_correctivas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporte_sms_id')->constrained('reportes_sms')->cascadeOnDelete();
            $table->text('descripcion');
            $table->foreignId('responsable_id')->constrained('usuarios')->restrictOnDelete();
            $table->date('fecha_limite');
            $table->enum('estado', ['pendiente', 'en_proceso', 'cerrada', 'verificada'])->default('pendiente');
            $table->string('evidencia_url', 300)->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->text('observaciones_cierre')->nullable();
            $table->timestamps();

            $table->index(['reporte_sms_id', 'estado']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acciones_correctivas');
    }
};
