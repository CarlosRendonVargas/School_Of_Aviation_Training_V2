<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstructoresSeeder extends Seeder
{
    public function run(): void
    {
        $instructores = [
            [
                'email'           => 'instructor1@escuelaviacion.co',
                'num_licencia'    => 'COL-CPL-12345',
                'tipo_licencia'   => 'CPL',
                'venc_licencia'   => Carbon::now()->addMonths(14)->toDateString(),
                'habilitaciones'  => [
                    ['tipo' => 'IFR',        'venc' => Carbon::now()->addMonths(12)->toDateString()],
                    ['tipo' => 'CFI',        'venc' => Carbon::now()->addMonths(18)->toDateString()],
                    ['tipo' => 'multimotor', 'venc' => Carbon::now()->addMonths(10)->toDateString()],
                ],
                'horas_totales_pic' => 3200.0,
                'horas_instruccion' => 1500.0,
            ],
            [
                'email'           => 'instructor2@escuelaviacion.co',
                'num_licencia'    => 'COL-ATPL-67890',
                'tipo_licencia'   => 'ATPL',
                'venc_licencia'   => Carbon::now()->addMonths(20)->toDateString(),
                'habilitaciones'  => [
                    ['tipo' => 'IFR',  'venc' => Carbon::now()->addMonths(15)->toDateString()],
                    ['tipo' => 'CFI',  'venc' => Carbon::now()->addMonths(22)->toDateString()],
                ],
                'horas_totales_pic' => 6500.0,
                'horas_instruccion' => 2800.0,
            ],
        ];

        foreach ($instructores as $data) {
            $personaId = DB::table('personas')
                ->join('usuarios', 'personas.usuario_id', '=', 'usuarios.id')
                ->where('usuarios.email', $data['email'])
                ->value('personas.id');

            if (! $personaId) continue;

            DB::table('instructores')->insert([
                'persona_id'        => $personaId,
                'num_licencia'      => $data['num_licencia'],
                'tipo_licencia'     => $data['tipo_licencia'],
                'venc_licencia'     => $data['venc_licencia'],
                'habilitaciones'    => json_encode($data['habilitaciones']),
                'horas_totales_pic' => $data['horas_totales_pic'],
                'horas_instruccion' => $data['horas_instruccion'],
                'activo'            => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }
}
