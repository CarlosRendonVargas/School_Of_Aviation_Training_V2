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
        Schema::create('reintentos_autorizados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->unsignedBigInteger('materia_id');
            $table->unsignedBigInteger('autorizado_por')->nullable();
            $table->string('num_recibo')->nullable();
            $table->boolean('usado')->default(false);
            $table->timestamps();

            // $table->foreign('estudiante_id')->references('id')->on('estudiantes')->onDelete('cascade');
            // $table->foreign('materia_id')->references('id')->on('materias')->onDelete('cascade');
            // $table->foreign('autorizado_por')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reintentos_autorizados');
    }
};
