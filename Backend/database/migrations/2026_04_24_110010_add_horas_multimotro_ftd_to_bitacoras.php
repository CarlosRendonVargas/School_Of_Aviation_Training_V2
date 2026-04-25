<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bitacoras_vuelo', function (Blueprint $table) {
            $table->decimal('horas_multi_motor', 5, 1)->default(0)->after('horas_simulador');
            $table->decimal('horas_ftd', 5, 1)->default(0)->after('horas_multi_motor');
        });
    }

    public function down(): void
    {
        Schema::table('bitacoras_vuelo', function (Blueprint $table) {
            $table->dropColumn(['horas_multi_motor', 'horas_ftd']);
        });
    }
};
