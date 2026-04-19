<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_mantenimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->restrictOnDelete();
            $table->enum('tipo', [
                'inspeccion_50h', 'inspeccion_100h', 'anual',
                'AD', 'SB', 'correctivo', 'preventivo'
            ]);
            $table->text('descripcion');
            $table->date('fecha_realizado');
            $table->decimal('horas_aeronave', 8, 1);    // TTAE al momento del mx
            $table->string('realizado_por', 100);
            $table->string('licencia_tecnico', 30)->nullable();
            $table->date('proxima_fecha')->nullable();
            $table->decimal('proximas_horas', 8, 1)->nullable();
            $table->decimal('costo', 12, 2)->nullable();
            $table->string('archivo_url', 300)->nullable();
            $table->timestamps();

            $table->index(['aeronave_id', 'fecha_realizado']);
            $table->index(['aeronave_id', 'proxima_fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_mantenimiento');
    }
};
