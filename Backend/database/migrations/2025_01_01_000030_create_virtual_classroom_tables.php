<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lecciones_materia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('video_url')->nullable();
            $table->string('documento_url')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('notas_lecciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('leccion_id')->constrained('lecciones_materia')->cascadeOnDelete();
            $table->decimal('nota', 5, 2);
            $table->integer('aciertos')->default(0);
            $table->integer('total')->default(0);
            $table->timestamps();
        });

        Schema::table('banco_preguntas', function (Blueprint $table) {
            $table->foreignId('leccion_id')->nullable()->after('materia_id')->constrained('lecciones_materia')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banco_preguntas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('leccion_id');
        });
        Schema::dropIfExists('notas_lecciones');
        Schema::dropIfExists('lecciones_materia');
    }
};
