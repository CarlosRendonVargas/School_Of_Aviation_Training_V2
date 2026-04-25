<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_erg', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 200);
            $table->enum('tipo_emergencia', [
                'accidente_aereo',
                'incendio',
                'evacuacion',
                'derrame_combustible',
                'lesion_personal',
                'amenaza_bomba',
                'falla_estructural',
                'otro',
            ]);
            $table->text('procedimiento');
            $table->text('personal_responsable');
            $table->text('contactos_emergencia');
            $table->string('version', 10)->default('1.0');
            $table->date('fecha_revision');
            $table->date('proxima_revision');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_erg');
    }
};
