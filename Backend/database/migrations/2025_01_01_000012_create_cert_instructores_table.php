<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cert_instructores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructores')->cascadeOnDelete();
            $table->enum('tipo', ['licencia', 'habilitacion', 'cheque_competencia', 'medico']);
            $table->string('descripcion', 120);
            $table->string('numero', 40)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');       // alerta 90 días antes
            $table->string('archivo_url', 300)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['instructor_id', 'fecha_vencimiento']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cert_instructores');
    }
};
