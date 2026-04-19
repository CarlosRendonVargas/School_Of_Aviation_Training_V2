<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aeronaves', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 10)->unique();   // HK-XXXX
            $table->string('modelo', 60);               // Cessna 172S
            $table->string('fabricante', 60);
            $table->string('serie', 40);
            $table->unsignedSmallInteger('anio');
            $table->enum('categoria', ['normal', 'utilidad', 'acrobatico'])->default('normal');
            $table->enum('clase', ['monomotor', 'multimotor'])->default('monomotor');
            $table->decimal('horas_celula_total', 8, 1)->default(0);   // TTAE
            $table->decimal('horas_motor_total', 8, 1)->default(0);
            $table->decimal('horas_desde_oh', 8, 1)->default(0);       // SMOH
            $table->string('cert_airworthiness', 30);
            $table->date('venc_airworthiness');
            $table->date('venc_seguro');
            $table->enum('estado', ['disponible', 'mantenimiento', 'baja'])->default('disponible');
            $table->timestamps();

            $table->index('estado');
            $table->index('venc_airworthiness');
            $table->index('venc_seguro');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aeronaves');
    }
};
