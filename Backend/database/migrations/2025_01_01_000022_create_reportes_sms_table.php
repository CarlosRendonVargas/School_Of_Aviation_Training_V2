<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_sms', function (Blueprint $table) {
            $table->id();
            // null = reporte anónimo (cultura de seguridad OACI Anexo 19)
            $table->foreignId('reportante_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->boolean('anonimo')->default(false);
            $table->enum('tipo', ['peligro', 'incidente', 'accidente', 'near_miss']);
            $table->text('descripcion');
            $table->dateTime('fecha_evento');
            $table->string('lugar', 100);
            $table->foreignId('aeronave_id')->nullable()->constrained('aeronaves')->nullOnDelete();
            // Matriz de riesgo OACI: severidad × probabilidad
            $table->unsignedTinyInteger('severidad');     // 1-5
            $table->unsignedTinyInteger('probabilidad');  // 1-5
            $table->unsignedTinyInteger('nivel_riesgo');  // severidad * probabilidad (1-25)
            $table->enum('estado', ['nuevo', 'en_analisis', 'cerrado'])->default('nuevo');
            $table->boolean('notificado_uaeac')->default(false);
            $table->timestamps();

            $table->index(['estado', 'nivel_riesgo']);
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_sms');
    }
};
