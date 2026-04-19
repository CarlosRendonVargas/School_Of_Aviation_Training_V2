<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AeronavesSeeder extends Seeder
{
    public function run(): void
    {
        $aeronaves = [
            [
                'matricula'           => 'HK-4001',
                'modelo'              => 'Cessna 172S Skyhawk',
                'fabricante'          => 'Cessna Aircraft Company',
                'serie'               => '172S9999',
                'anio'                => 2018,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 1240.5,
                'horas_motor_total'   => 1240.5,
                'horas_desde_oh'      => 340.5,
                'cert_airworthiness'  => 'CA-2024-0001',
                'venc_airworthiness'  => now()->addMonths(8)->toDateString(),
                'venc_seguro'         => now()->addMonths(5)->toDateString(),
                'estado'              => 'disponible',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'matricula'           => 'HK-4002',
                'modelo'              => 'Cessna 172R Skyhawk',
                'fabricante'          => 'Cessna Aircraft Company',
                'serie'               => '172R8888',
                'anio'                => 2015,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 3560.0,
                'horas_motor_total'   => 3560.0,
                'horas_desde_oh'      => 160.0,
                'cert_airworthiness'  => 'CA-2024-0002',
                'venc_airworthiness'  => now()->addMonths(11)->toDateString(),
                'venc_seguro'         => now()->addMonths(3)->toDateString(),
                'estado'              => 'disponible',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'matricula'           => 'HK-4003',
                'modelo'              => 'Piper PA-28-161 Cherokee Warrior',
                'fabricante'          => 'Piper Aircraft',
                'serie'               => 'PA28161-7777',
                'anio'                => 2019,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 890.0,
                'horas_motor_total'   => 890.0,
                'horas_desde_oh'      => 890.0,
                'cert_airworthiness'  => 'CA-2024-0003',
                'venc_airworthiness'  => now()->addMonths(6)->toDateString(),
                'venc_seguro'         => now()->addMonths(9)->toDateString(),
                'estado'              => 'mantenimiento',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'matricula'           => 'HK-4010',
                'modelo'              => 'Piper PA-44 Seminole',
                'fabricante'          => 'Piper Aircraft',
                'serie'               => 'PA44-6666',
                'anio'                => 2020,
                'categoria'           => 'normal',
                'clase'               => 'multimotor',
                'horas_celula_total'  => 620.0,
                'horas_motor_total'   => 620.0,
                'horas_desde_oh'      => 620.0,
                'cert_airworthiness'  => 'CA-2024-0010',
                'venc_airworthiness'  => now()->addYear()->toDateString(),
                'venc_seguro'         => now()->addMonths(10)->toDateString(),
                'estado'              => 'disponible',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ];

        DB::table('aeronaves')->insertOrIgnore($aeronaves);

        $this->command->info('✔  Aeronaves de ejemplo insertadas (' . count($aeronaves) . ')');
    }
}
