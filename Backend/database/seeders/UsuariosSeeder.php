<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $adminRolId = DB::table('roles')->where('nombre', 'admin')->value('id');

        $userId = DB::table('usuarios')->insertGetId([
            'email'         => 'admin@escuelaviacion.co',
            'password_hash' => Hash::make('Rac141*2025'),
            'rol_id'        => $adminRolId,
            'activo'        => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::table('personas')->insert([
            'usuario_id'     => $userId,
            'nombres'        => 'Administrador',
            'apellidos'      => 'Principal',
            'tipo_documento' => 'CC',
            'num_documento'  => '00000000',
            'fecha_nacimiento' => '1980-01-01',
            'nacionalidad'   => 'Colombiana',
            'ciudad'         => 'Bogotá',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}
