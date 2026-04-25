<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enmiendas_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_id')->constrained('documentos_rac')->cascadeOnDelete();
            $table->string('numero_enmienda', 20);
            $table->string('descripcion', 300);
            $table->text('contenido_cambio');
            $table->enum('estado', ['borrador', 'enviada_uaeac', 'aprobada', 'rechazada', 'retirada'])->default('borrador');
            $table->date('fecha_envio')->nullable();
            $table->date('fecha_respuesta')->nullable();
            $table->text('respuesta_uaeac')->nullable();
            $table->foreignId('elaborado_por')->constrained('usuarios')->restrictOnDelete();
            $table->timestamps();

            $table->index(['documento_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enmiendas_documentos');
    }
};
