<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mision_vuelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('instructor_id')->nullable()->constrained('instructores')->onDelete('set null'); // Instructor profile
            $table->foreignId('programa_id')->nullable()->constrained('programas');
            $table->date('fecha');
            $table->string('matricula'); // Aeronave
            $table->enum('tipo_vuelo', ['dual', 'solo', 'ftd', 'chequeo'])->default('dual');
            $table->decimal('horas', 5, 2);
            $table->integer('despegues')->default(1);
            $table->integer('aterrizajes')->default(1);
            $table->enum('calificacion', ['S', 'NI', 'D', 'NA'])->default('S'); // S=Satisfactorio, NI=Needs Instruction
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mision_vuelos');
    }
};
