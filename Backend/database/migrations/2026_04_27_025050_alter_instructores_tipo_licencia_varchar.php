<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change ENUM('CPL','ATPL','CFI') to VARCHAR(30) to accept any license category
        DB::statement('ALTER TABLE instructores MODIFY tipo_licencia VARCHAR(30) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE instructores MODIFY tipo_licencia ENUM('CPL','ATPL','CFI') NULL");
    }
};
