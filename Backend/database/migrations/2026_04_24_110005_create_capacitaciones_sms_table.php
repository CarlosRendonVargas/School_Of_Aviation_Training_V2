<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('capacitaciones_sms', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['inicial', 'recurrente', 'especializada', 'simulacro_emergencia']);
            $table->date('fecha');
            $table->unsignedSmallInteger('duracion_horas')->default(1);
            $table->string('instructor_nombre', 150)->nullable();
            $table->boolean('obligatoria')->default(true);
            $table->enum('estado', ['programada', 'realizada', 'cancelada'])->default('programada');
            $table->text('material_url')->nullable();
            $table->timestamps();
        });

        Schema::create('registros_capacitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capacitacion_id')->constrained('capacitaciones_sms')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->boolean('asistio')->default(false);
            $table->decimal('nota', 5, 2)->nullable();
            $table->boolean('aprobado')->default(false);
            $table->timestamps();

            $table->unique(['capacitacion_id', 'usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_capacitacion');
        Schema::dropIfExists('capacitaciones_sms');
    }
};
