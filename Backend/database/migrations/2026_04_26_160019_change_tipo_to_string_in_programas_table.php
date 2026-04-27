<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE programas MODIFY tipo VARCHAR(60) NOT NULL DEFAULT 'PPL'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE programas MODIFY tipo ENUM('PPL','CPL','ATPL','HABILITACION') NOT NULL DEFAULT 'PPL'");
    }
};
