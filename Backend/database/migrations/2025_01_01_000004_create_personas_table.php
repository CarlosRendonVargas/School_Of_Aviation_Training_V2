<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('tipo_documento', 5);   // CC | CE | PA | TI
            $table->string('num_documento', 20)->unique();
            $table->date('fecha_nacimiento');
            $table->string('nacionalidad', 50)->default('Colombiana');
            $table->string('telefono', 20)->nullable();
            $table->string('foto_url', 300)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 80)->nullable();
            $table->timestamps();

            $table->index(['apellidos', 'nombres']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
