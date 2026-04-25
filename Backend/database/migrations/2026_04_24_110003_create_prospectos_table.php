<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('email', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->enum('programa_interes', ['PPL', 'CPL', 'ATPL', 'HABILITACION', 'otro'])->default('PPL');
            $table->enum('estado', ['nuevo', 'contactado', 'interesado', 'matriculado', 'descartado'])->default('nuevo');
            $table->text('notas')->nullable();
            $table->string('fuente', 100)->nullable();
            $table->date('fecha_primer_contacto');
            $table->date('proxima_accion')->nullable();
            $table->foreignId('asignado_a')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospectos');
    }
};
