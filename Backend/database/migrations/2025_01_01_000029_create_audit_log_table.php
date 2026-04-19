<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RAC 141.77 — Todos los registros deben conservarse mínimo 5 años
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('tabla', 60);
            $table->string('accion', 10);           // INSERT | UPDATE | DELETE
            $table->unsignedBigInteger('registro_id');
            $table->json('datos_antes')->nullable(); // estado previo (UPDATE/DELETE)
            $table->json('datos_despues')->nullable(); // estado nuevo (INSERT/UPDATE)
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tabla', 'created_at']);
            $table->index(['tabla', 'registro_id']);
            $table->index('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
