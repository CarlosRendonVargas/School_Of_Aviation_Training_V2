<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $this->call([
            RolesSeeder::class,
            UsuariosSeeder::class,
            ProgramasSeeder::class,
            AeronavesSeeder::class,
            InstructoresSeeder::class,
            EstudiantesSeeder::class,
            DocumentosRacSeeder::class,
            FullDemoSeeder::class,
            AulaVirtualSeeder::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
