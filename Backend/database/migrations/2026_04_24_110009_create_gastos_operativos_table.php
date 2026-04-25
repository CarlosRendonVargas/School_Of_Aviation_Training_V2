<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_operativos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['caja_menor', 'proveedor', 'servicio', 'mantenimiento', 'administrativo', 'otro']);
            $table->string('concepto', 200);
            $table->decimal('valor', 12, 2);
            $table->date('fecha');
            $table->string('responsable', 150);
            $table->string('comprobante_url', 500)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tipo', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_operativos');
    }
};
