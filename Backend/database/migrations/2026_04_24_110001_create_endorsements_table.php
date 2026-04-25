<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('endorsements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->restrictOnDelete();
            $table->foreignId('instructor_id')->constrained('instructores')->restrictOnDelete();
            $table->enum('tipo', [
                'primer_vuelo_solo',
                'vuelo_solo_area',
                'vuelo_cross_country_solo',
                'vuelo_nocturno_solo',
                'examen_practico_ppl',
                'examen_practico_cpl',
            ]);
            $table->date('fecha');
            $table->string('aeronave_matricula', 10);
            $table->string('aeropuerto_icao', 4)->nullable();
            $table->text('observaciones')->nullable();
            $table->string('firma_instructor', 500)->nullable();
            $table->timestamps();

            $table->index(['estudiante_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('endorsements');
    }
};
