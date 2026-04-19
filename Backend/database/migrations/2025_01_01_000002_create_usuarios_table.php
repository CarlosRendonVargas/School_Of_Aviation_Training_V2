<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->string('token_reset', 100)->nullable();
            $table->timestamp('token_reset_expira')->nullable();
            $table->timestamps();

            $table->index('activo');
            $table->index('rol_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
