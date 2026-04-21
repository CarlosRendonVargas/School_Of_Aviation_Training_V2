<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notas_academicas', function (Blueprint $table) {
            $table->foreignId('instructor_id')->nullable()->change();
            $table->date('fecha_evaluacion')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('notas_academicas', function (Blueprint $table) {
            $table->foreignId('instructor_id')->nullable(false)->change();
            $table->date('fecha_evaluacion')->nullable(false)->change();
        });
    }
};
