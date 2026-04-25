<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensajes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('remitente_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('destinatario_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('asunto', 200);
            $table->text('cuerpo');
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_en')->nullable();
            $table->foreignId('respuesta_a')->nullable()->constrained('mensajes')->nullOnDelete();
            $table->timestamps();

            $table->index(['destinatario_id', 'leido']);
            $table->index(['remitente_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
