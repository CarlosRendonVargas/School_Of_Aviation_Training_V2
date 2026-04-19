<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mel_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aeronave_id')->constrained('aeronaves')->cascadeOnDelete();
            $table->string('item_ata', 20);              // Ej: 34-10-01
            $table->text('descripcion');
            // A=3d | B=10d | C=30d | D=120d
            $table->char('categoria', 1);
            $table->date('fecha_apertura');
            $table->date('fecha_limite');               // calculado según categoría
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->text('procedimiento_o')->nullable(); // procedimiento operacional
            $table->string('cerrado_por', 100)->nullable();
            $table->date('fecha_cierre')->nullable();
            $table->timestamps();

            $table->index(['aeronave_id', 'estado']);
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mel_items');
    }
};
