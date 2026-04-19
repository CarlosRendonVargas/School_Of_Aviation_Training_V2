<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('briefings_debriefings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('instructores')->restrictOnDelete();
            $table->enum('tipo', ['pre_vuelo', 'post_vuelo']);
            $table->dateTime('fecha_hora');
            $table->text('contenido');              // puntos discutidos, NOTAMs, wx…
            $table->text('areas_debiles')->nullable();
            $table->string('firma_instructor', 300);
            $table->timestamps();

            $table->index(['reserva_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('briefings_debriefings');
    }
};
