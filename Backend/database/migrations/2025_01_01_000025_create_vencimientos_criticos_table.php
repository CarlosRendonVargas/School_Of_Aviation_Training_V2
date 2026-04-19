<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vencimientos_criticos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_entidad', ['aeronave', 'instructor', 'estudiante', 'documento', 'escuela']);
            $table->unsignedBigInteger('entidad_id');
            $table->string('descripcion', 200);
            $table->date('fecha_vencimiento');
            $table->unsignedSmallInteger('dias_alerta')->default(30);
            $table->enum('nivel_critico', ['info', 'advertencia', 'critico'])->default('info');
            $table->date('ultima_notificacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['fecha_vencimiento', 'tipo_entidad']);
            $table->index(['nivel_critico', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vencimientos_criticos');
    }
};
