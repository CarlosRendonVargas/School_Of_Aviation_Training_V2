<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->restrictOnDelete();
            $table->string('numero_factura', 20)->unique();  // número DIAN
            $table->date('fecha_factura');
            $table->date('fecha_vencimiento_pago')->nullable();
            $table->text('concepto');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'anulada', 'vencida'])->default('pendiente');
            $table->string('cufe', 100)->nullable();        // Código Único de Factura Electrónica DIAN
            $table->string('archivo_url', 300)->nullable();
            $table->timestamps();

            $table->index(['estado', 'fecha_factura']);
            $table->index('matricula_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
    }
};
