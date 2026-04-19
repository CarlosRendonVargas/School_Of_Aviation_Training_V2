<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacoras_vuelo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('instructor_id')->nullable()->constrained('instructores')->nullOnDelete();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->restrictOnDelete();
            $table->foreignId('reserva_id')->nullable()->constrained('reservas')->nullOnDelete();
            $table->date('fecha');
            $table->time('hora_salida');             // UTC / Zulú
            $table->time('hora_llegada');
            $table->char('origen_icao', 4);          // SKBO, SKMD…
            $table->char('destino_icao', 4);
            $table->decimal('horas_totales', 5, 1);
            $table->decimal('horas_dual', 5, 1)->default(0);
            $table->decimal('horas_solo', 5, 1)->default(0);
            $table->decimal('horas_noche', 5, 1)->default(0);
            $table->decimal('horas_ifr', 5, 1)->default(0);
            $table->decimal('horas_simulador', 5, 1)->default(0);
            $table->enum('tipo_vuelo', ['local', 'navegacion', 'noche', 'ifr', 'sim']);
            $table->boolean('condiciones_vmc')->default(true);  // true=VMC false=IMC
            $table->unsignedSmallInteger('aterrizajes')->default(1);
            $table->string('firma_instructor', 300)->nullable();  // base64 o URL
            $table->string('firma_estudiante', 300)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'fecha']);
            $table->index(['aeronave_id', 'fecha']);
            $table->index(['instructor_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacoras_vuelo');
    }
};
