<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'email'  => 'dirops@escuelaviacion.co',
                'rol'    => 'dir_ops',
                'nombre' => 'Carlos Alberto',
                'apellido' => 'Jiménez Ramos',
                'doc'    => '79512345',
                'ciudad' => 'Bogotá',
            ],
            [
                'email'  => 'admin@escuelaviacion.co',
                'rol'    => 'admin',
                'nombre' => 'Sandra Patricia',
                'apellido' => 'Torres Bernal',
                'doc'    => '52841230',
                'ciudad' => 'Medellín',
            ],
            [
                'email'  => 'instructor1@escuelaviacion.co',
                'rol'    => 'instructor',
                'nombre' => 'Miguel Ángel',
                'apellido' => 'Ríos Castillo',
                'doc'    => '80234567',
                'ciudad' => 'Bogotá',
            ],
            [
                'email'  => 'instructor2@escuelaviacion.co',
                'rol'    => 'instructor',
                'nombre' => 'Laura Fernanda',
                'apellido' => 'Morales Vega',
                'doc'    => '43671234',
                'ciudad' => 'Cali',
            ],
            [
                'email'  => 'estudiante1@escuelaviacion.co',
                'rol'    => 'estudiante',
                'nombre' => 'Andrés Felipe',
                'apellido' => 'Gómez Herrera',
                'doc'    => '1023456789',
                'ciudad' => 'Bogotá',
            ],
            [
                'email'  => 'estudiante2@escuelaviacion.co',
                'rol'    => 'estudiante',
                'nombre' => 'Valentina',
                'apellido' => 'Ospina López',
                'doc'    => '1098765432',
                'ciudad' => 'Pereira',
            ],
            [
                'email'  => 'mx@escuelaviacion.co',
                'rol'    => 'mantenimiento',
                'nombre' => 'Jorge Luis',
                'apellido' => 'Prada Niño',
                'doc'    => '91234567',
                'ciudad' => 'Bogotá',
            ],
            [
                'email'  => 'uaeac@aerocivil.gov.co',
                'rol'    => 'auditor_uaeac',
                'nombre' => 'Inspector',
                'apellido' => 'Aerocivil UAEAC',
                'doc'    => '00000001',
                'ciudad' => 'Bogotá',
            ],
        ];

        foreach ($usuarios as $u) {
            $rolId = DB::table('roles')->where('nombre', $u['rol'])->value('id');

            $userId = DB::table('usuarios')->insertGetId([
                'email'        => $u['email'],
                'password_hash' => Hash::make('Rac141*2025'),
                'rol_id'       => $rolId,
                'activo'       => true,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            DB::table('personas')->insert([
                'usuario_id'       => $userId,
                'nombres'          => $u['nombre'],
                'apellidos'        => $u['apellido'],
                'tipo_documento'   => 'CC',
                'num_documento'    => $u['doc'],
                'fecha_nacimiento' => Carbon::now()->subYears(rand(25, 45))->toDateString(),
                'nacionalidad'     => 'Colombiana',
                'ciudad'           => $u['ciudad'],
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }
}
