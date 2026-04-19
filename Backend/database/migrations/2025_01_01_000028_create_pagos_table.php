<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->restrictOnDelete();
            $table->date('fecha_pago');
            $table->decimal('valor', 14, 2);
            $table->enum('metodo', ['efectivo', 'transferencia', 'tarjeta', 'cheque']);
            $table->string('referencia', 60)->nullable();   // número de transacción
            $table->foreignId('registrado_por')->constrained('usuarios')->restrictOnDelete();
            $table->string('comprobante_url', 300)->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['factura_id', 'fecha_pago']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
