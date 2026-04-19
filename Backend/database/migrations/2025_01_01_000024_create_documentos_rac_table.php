<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_rac', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['MOE', 'PIA', 'enmienda', 'circular', 'certificado']);
            $table->string('numero', 30);
            $table->string('version', 10);
            $table->string('titulo', 200);
            $table->date('fecha_documento');
            $table->boolean('aprobado_uaeac')->default(false);
            $table->date('fecha_aprobacion')->nullable();
            $table->string('archivo_url', 300);
            $table->boolean('vigente')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['tipo', 'vigente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_rac');
    }
};
