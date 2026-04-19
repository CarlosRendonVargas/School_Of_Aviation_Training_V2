<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            // academico | vuelo | instructores | operaciones | sms | admin | cumplimiento
            $table->string('modulo', 50);
            // ver | crear | editar | eliminar | exportar | firmar
            $table->string('accion', 20);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['rol_id', 'modulo', 'accion']);
            $table->index(['rol_id', 'modulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
