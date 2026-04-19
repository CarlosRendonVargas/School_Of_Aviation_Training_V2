<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramasSeeder extends Seeder
{
    public function run(): void
    {
        // Programas basados en mínimos RAC 61 Colombia / OACI Anexo 1
        $programas = [
            [
                'codigo'          => 'PPL-AV',
                'nombre'          => 'Piloto Privado de Avión',
                'tipo'            => 'PPL',
                'horas_tierra_min' => 40.0,
                'horas_vuelo_min'  => 40.0,
                'horas_dual_min'   => 20.0,
                'horas_solo_min'   => 10.0,
                'horas_ifr_min'    => 3.0,
                'horas_noche_min'  => 5.0,
                'horas_sim_max'    => 5.0,
                'rac_referencia'  => 'RAC 61.109',
                'activo'          => true,
            ],
            [
                'codigo'          => 'CPL-AV',
                'nombre'          => 'Piloto Comercial de Avión',
                'tipo'            => 'CPL',
                'horas_tierra_min' => 200.0,
                'horas_vuelo_min'  => 200.0,
                'horas_dual_min'   => 100.0,
                'horas_solo_min'   => 100.0,
                'horas_ifr_min'    => 10.0,
                'horas_noche_min'  => 20.0,
                'horas_sim_max'    => 30.0,
                'rac_referencia'  => 'RAC 61.129',
                'activo'          => true,
            ],
            [
                'codigo'          => 'ATPL-AV',
                'nombre'          => 'Piloto de Transporte de Línea Aérea',
                'tipo'            => 'ATPL',
                'horas_tierra_min' => 400.0,
                'horas_vuelo_min'  => 1500.0,
                'horas_dual_min'   => 250.0,
                'horas_solo_min'   => 500.0,
                'horas_ifr_min'    => 75.0,
                'horas_noche_min'  => 100.0,
                'horas_sim_max'    => 100.0,
                'rac_referencia'  => 'RAC 61.159',
                'activo'          => true,
            ],
            [
                'codigo'          => 'HB-IFR',
                'nombre'          => 'Habilitación de Vuelo por Instrumentos (IFR)',
                'tipo'            => 'HABILITACION',
                'horas_tierra_min' => 40.0,
                'horas_vuelo_min'  => 50.0,
                'horas_dual_min'   => 40.0,
                'horas_solo_min'   => 10.0,
                'horas_ifr_min'    => 50.0,
                'horas_noche_min'  => 0.0,
                'horas_sim_max'    => 20.0,
                'rac_referencia'  => 'RAC 61.65',
                'activo'          => true,
            ],
            [
                'codigo'          => 'HB-MM',
                'nombre'          => 'Habilitación Multi-Motor Tierra',
                'tipo'            => 'HABILITACION',
                'horas_tierra_min' => 10.0,
                'horas_vuelo_min'  => 10.0,
                'horas_dual_min'   => 10.0,
                'horas_solo_min'   => 0.0,
                'horas_ifr_min'    => null,
                'horas_noche_min'  => null,
                'horas_sim_max'    => 5.0,
                'rac_referencia'  => 'RAC 61.63',
                'activo'          => true,
            ],
        ];

        foreach ($programas as $prog) {
            $progId = DB::table('programas')->insertGetId(
                array_merge($prog, ['created_at' => now(), 'updated_at' => now()])
            );

            // Crear etapas y materias para el PPL
            if ($prog['codigo'] === 'PPL-AV') {
                $this->crearEtapasPPL($progId);
            }
            if ($prog['codigo'] === 'CPL-AV') {
                $this->crearEtapasCPL($progId);
            }
        }
    }

    private function crearEtapasPPL(int $programaId): void
    {
        $etapas = [
            [
                'numero'       => 1,
                'nombre'       => 'Instrucción Pre-Solo',
                'horas_tierra' => 15.0,
                'horas_vuelo'  => 15.0,
                'descripcion'  => 'Maniobras básicas, procedimientos de emergencia y primer vuelo solo',
                'materias'     => [
                    ['codigo' => 'AERO-01', 'nombre' => 'Aerodinámica básica', 'horas' => 10.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61 / OACI Anexo 1'],
                    ['codigo' => 'NAV-01',  'nombre' => 'Navegación básica y cartas', 'horas' => 8.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61'],
                    ['codigo' => 'METEO-01','nombre' => 'Meteorología general', 'horas' => 8.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61'],
                    ['codigo' => 'REG-01',  'nombre' => 'Reglamentos aéreos RAC', 'horas' => 6.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 91'],
                    ['codigo' => 'FH-01',   'nombre' => 'Factores humanos nivel 1', 'horas' => 4.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'OACI Anexo 1'],
                ],
            ],
            [
                'numero'       => 2,
                'nombre'       => 'Vuelo Solo y Navegación',
                'horas_tierra' => 10.0,
                'horas_vuelo'  => 15.0,
                'descripcion'  => 'Vuelos solos, navegación de área y preparación para examen',
                'materias'     => [
                    ['codigo' => 'NAV-02',  'nombre' => 'Navegación de área y planeación', 'horas' => 8.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61'],
                    ['codigo' => 'METEO-02','nombre' => 'Meteorología operacional', 'horas' => 6.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61'],
                    ['codigo' => 'REG-02',  'nombre' => 'Espacio aéreo colombiano y ATC', 'horas' => 6.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 91'],
                    ['codigo' => 'SIM-01',  'nombre' => 'Procedimientos en simulador FTD', 'horas' => 5.0, 'tipo' => 'simulador', 'nota_minima' => 70, 'rac' => 'RAC 141.71'],
                ],
            ],
            [
                'numero'       => 3,
                'nombre'       => 'Preparación Examen UAEAC',
                'horas_tierra' => 15.0,
                'horas_vuelo'  => 10.0,
                'descripcion'  => 'Repaso general, vuelo de noche y check ride UAEAC',
                'materias'     => [
                    ['codigo' => 'PREPS-01','nombre' => 'Repaso integral y banco de preguntas', 'horas' => 10.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'RAC 61.109'],
                    ['codigo' => 'NOCHE-01','nombre' => 'Procedimientos de vuelo nocturno', 'horas' => 5.0, 'tipo' => 'practica', 'nota_minima' => 70, 'rac' => 'RAC 61.109'],
                    ['codigo' => 'FH-02',   'nombre' => 'CRM y toma de decisiones', 'horas' => 5.0, 'tipo' => 'teorica', 'nota_minima' => 70, 'rac' => 'OACI Anexo 1'],
                ],
            ],
        ];

        $anteriorId = null;
        foreach ($etapas as $etapa) {
            $materias = $etapa['materias'];
            unset($etapa['materias']);

            $etapaId = DB::table('etapas')->insertGetId(array_merge([
                'programa_id'    => $programaId,
                'prereq_etapa_id' => $anteriorId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ], $etapa));

            foreach ($materias as $m) {
                DB::table('materias')->insert([
                    'etapa_id'      => $etapaId,
                    'codigo'        => $m['codigo'],
                    'nombre'        => $m['nombre'],
                    'horas'         => $m['horas'],
                    'tipo'          => $m['tipo'],
                    'nota_minima'   => $m['nota_minima'],
                    'rac_referencia' => $m['rac'],
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $anteriorId = $etapaId;
        }
    }

    private function crearEtapasCPL(int $programaId): void
    {
        $etapas = [
            ['numero' => 1, 'nombre' => 'Consolidación vuelo básico', 'horas_tierra' => 50.0, 'horas_vuelo' => 50.0, 'descripcion' => 'Maniobras avanzadas y vuelo de precisión'],
            ['numero' => 2, 'nombre' => 'Navegación avanzada y área', 'horas_tierra' => 50.0, 'horas_vuelo' => 60.0, 'descripcion' => 'Navegación larga distancia, planificación de vuelo'],
            ['numero' => 3, 'nombre' => 'Instrumentos y meteorología', 'horas_tierra' => 60.0, 'horas_vuelo' => 50.0, 'descripcion' => 'Vuelo IFR inicial y meteorología operacional avanzada'],
            ['numero' => 4, 'nombre' => 'Preparación CPL UAEAC', 'horas_tierra' => 40.0, 'horas_vuelo' => 40.0, 'descripcion' => 'Repaso integral y check ride comercial'],
        ];

        $anteriorId = null;
        foreach ($etapas as $etapa) {
            $etapaId = DB::table('etapas')->insertGetId(array_merge([
                'programa_id'    => $programaId,
                'prereq_etapa_id' => $anteriorId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ], $etapa));
            $anteriorId = $etapaId;
        }
    }
}
