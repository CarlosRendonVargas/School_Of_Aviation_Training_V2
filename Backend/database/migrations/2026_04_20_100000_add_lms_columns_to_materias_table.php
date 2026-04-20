<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->string('link_meet')->nullable()->after('rac_referencia');
            $table->string('documento_url')->nullable()->after('link_meet');
            $table->string('video_url')->nullable()->after('documento_url');
            $table->text('temario')->nullable()->after('video_url');
            $table->integer('max_intentos')->default(3)->after('temario');
            $table->decimal('costo_reintento', 10, 2)->default(0.00)->after('max_intentos');
            $table->integer('duracion_minutos')->default(60)->after('costo_reintento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materias', function (Blueprint $table) {
            $table->dropColumn([
                'link_meet',
                'documento_url',
                'video_url',
                'temario',
                'max_intentos',
                'costo_reintento',
                'duracion_minutos'
            ]);
        });
    }
};
