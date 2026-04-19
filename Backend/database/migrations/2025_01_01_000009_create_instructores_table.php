<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('personas')->cascadeOnDelete();
            $table->string('num_licencia', 30);
            $table->enum('tipo_licencia', ['CPL', 'ATPL', 'CFI']);
            $table->date('venc_licencia');
            // JSON: [{"tipo":"IFR","venc":"2026-05-01"},{"tipo":"multimotor","venc":"2026-08-15"}]
            $table->json('habilitaciones')->nullable();
            $table->decimal('horas_totales_pic', 8, 1)->default(0);
            $table->decimal('horas_instruccion', 8, 1)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('activo');
            $table->index('venc_licencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instructores');
    }
};
