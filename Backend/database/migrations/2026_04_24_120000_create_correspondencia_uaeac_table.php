<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correspondencia_uaeac', function (Blueprint $table) {
            $table->id();
            $table->string('numero_radicado', 30)->nullable();
            $table->enum('tipo', ['entrada', 'salida']);
            $table->enum('categoria', ['certificacion', 'inspeccion', 'enmienda', 'solicitud', 'respuesta', 'circular', 'resolucion', 'otro']);
            $table->string('asunto', 300);
            $table->text('contenido')->nullable();
            $table->date('fecha_documento');
            $table->date('fecha_vencimiento_respuesta')->nullable();
            $table->enum('estado', ['recibido', 'en_revision', 'respondido', 'archivado'])->default('recibido');
            $table->string('archivo_url', 500)->nullable();
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->text('notas_internas')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'fecha_documento']);
            $table->index(['estado', 'fecha_vencimiento_respuesta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correspondencia_uaeac');
    }
};
