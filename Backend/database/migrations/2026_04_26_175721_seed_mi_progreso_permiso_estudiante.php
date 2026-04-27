<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rol = DB::table('roles')->where('nombre', 'estudiante')->first();
        if (! $rol) return;

        DB::table('permisos')->updateOrInsert(
            ['rol_id' => $rol->id, 'modulo' => 'mi-progreso', 'accion' => 'acceso'],
            ['activo' => true, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        $rol = DB::table('roles')->where('nombre', 'estudiante')->first();
        if (! $rol) return;

        DB::table('permisos')
            ->where('rol_id', $rol->id)
            ->where('modulo', 'mi-progreso')
            ->where('accion', 'acceso')
            ->delete();
    }
};
