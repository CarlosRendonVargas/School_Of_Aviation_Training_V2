<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Programa;
use App\Models\Etapa;
use App\Models\Materia;

class ProgramasEscuelaSeeder extends Seeder
{
    public function run(): void
    {
        $programas = [

            // ══════════════════════════════════════════════════
            //  PILOTO PRIVADO DE AVIÓN — RAC 141 / Apéndice 1
            // ══════════════════════════════════════════════════
            [
                'codigo'          => 'PPL-141-AP1',
                'nombre'          => 'Piloto Privado de Avión',
                'tipo'            => 'PPL',
                'rac_referencia'  => 'RAC 141 / Apéndice 1',
                'horas_tierra_min'=> 153,
                'horas_vuelo_min' => 41,
                'horas_dual_min'  => 31,
                'horas_solo_min'  => 10,
                'horas_noche_min' => 3,
                'horas_ifr_min'   => 0,
                'horas_sim_max'   => 5,
                'activo'          => true,
                'etapas' => [
                    [
                        'nombre'      => 'Escuela de Tierra RAC 141 — Apéndice 1 Párrafo E',
                        'descripcion' => 'Formación teórica. Total: 153 horas académicas.',
                        'materias' => [
                            ['codigo'=>'PPL-T01','nombre'=>'Principio de Vuelo / Aerodinámica Básica',                          'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T02','nombre'=>'Derecho Aéreo',                                                     'horas'=>15,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T03','nombre'=>'Conocimiento General de Aeronaves',                                  'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T04','nombre'=>'Procedimientos Operacionales',                                       'horas'=>15,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T05','nombre'=>'Comunicaciones Aeronáuticas y Radiotelefónicas',                    'horas'=>15,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T06','nombre'=>'Performance y Planificación de Vuelo (Peso y Balance)',              'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T07','nombre'=>'Meteorología',                                                       'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T08','nombre'=>'Navegación y Aerodinámica',                                          'horas'=>15,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T09','nombre'=>'Factores Humanos',                                                   'horas'=> 8,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T10','nombre'=>'Mercancías Peligrosas',                                              'horas'=> 3,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                            ['codigo'=>'PPL-T11','nombre'=>'CRM (Crew Resource Management)',                                     'horas'=> 2,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.109'],
                        ],
                    ],
                    [
                        'nombre'      => 'Entrenamiento de Vuelo RAC 61 / 61.520',
                        'descripcion' => 'Total horas de vuelo: 41h. Simulador: 5h. Total vuelo + simulador: 46h.',
                        'materias' => [
                            ['codigo'=>'PPL-V01','nombre'=>'Presolo',       'horas'=>20,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                            ['codigo'=>'PPL-V02','nombre'=>'Maniobras',     'horas'=> 7,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                            ['codigo'=>'PPL-V03','nombre'=>'Nocturno',      'horas'=> 3,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                            ['codigo'=>'PPL-V04','nombre'=>'Crucero',       'horas'=>10,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                            ['codigo'=>'PPL-V05','nombre'=>'Chequeo Final', 'horas'=> 1,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                            ['codigo'=>'PPL-S01','nombre'=>'Simulador de Vuelo', 'horas'=>5,'tipo'=>'simulador','nota_minima'=>75,'rac_referencia'=>'RAC 61.520'],
                        ],
                    ],
                ],
            ],

            // ══════════════════════════════════════════════════
            //  PILOTO COMERCIAL DE AVIÓN — RAC 141 / Apéndice 1
            // ══════════════════════════════════════════════════
            [
                'codigo'          => 'CPL-141-AP1',
                'nombre'          => 'Piloto Comercial de Avión',
                'tipo'            => 'CPL',
                'rac_referencia'  => 'RAC 141 / Apéndice 1',
                'horas_tierra_min'=> 200,
                'horas_vuelo_min' => 104,
                'horas_dual_min'  => 44,
                'horas_solo_min'  => 60,
                'horas_noche_min' => 2,
                'horas_ifr_min'   => 5,
                'horas_sim_max'   => 5,
                'activo'          => true,
                'etapas' => [
                    [
                        'nombre'      => 'Escuela de Tierra RAC 141 — Apéndice 2 Párrafo E',
                        'descripcion' => 'Formación teórica. Total: 200 horas académicas.',
                        'materias' => [
                            ['codigo'=>'CPL-T01','nombre'=>'Principio de Vuelo / Aerodinámica Básica',                          'horas'=>25,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T02','nombre'=>'Derecho Aéreo',                                                     'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T03','nombre'=>'Conocimiento General de Aeronaves',                                  'horas'=>25,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T04','nombre'=>'Procedimientos Operacionales',                                       'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T05','nombre'=>'Comunicaciones Aeronáuticas y Radiotelefónicas',                    'horas'=>20,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T06','nombre'=>'Performance y Planificación de Vuelo (Peso y Balance)',              'horas'=>30,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T07','nombre'=>'Meteorología',                                                       'horas'=>25,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T08','nombre'=>'Navegación y Aerodinámica',                                          'horas'=>25,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-T09','nombre'=>'Factores Humanos',                                                   'horas'=>10,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                        ],
                    ],
                    [
                        'nombre'      => 'Entrenamiento de Vuelo RAC 61 / 61.620',
                        'descripcion' => 'Total horas de vuelo y simulador: 104h.',
                        'materias' => [
                            ['codigo'=>'CPL-V01','nombre'=>'Maniobras',          'horas'=>35,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-V02','nombre'=>'Nocturno',           'horas'=> 2,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-V03','nombre'=>'Crucero',            'horas'=>55,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-V04','nombre'=>'Instrumentos',       'horas'=> 5,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-S01','nombre'=>'Simulador de Vuelo', 'horas'=> 5,'tipo'=>'simulador', 'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                            ['codigo'=>'CPL-V05','nombre'=>'Chequeo Final',      'horas'=> 2,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.620'],
                        ],
                    ],
                ],
            ],

            // ══════════════════════════════════════════════════
            //  HABILITACIÓN POR INSTRUMENTOS — RAC 141 / Apéndice 4
            // ══════════════════════════════════════════════════
            [
                'codigo'          => 'IFR-141-AP4',
                'nombre'          => 'Habilitación por Instrumentos',
                'tipo'            => 'HABILITACION',
                'rac_referencia'  => 'RAC 141 / Apéndice 4',
                'horas_tierra_min'=> 45,
                'horas_vuelo_min' => 40,
                'horas_dual_min'  => 40,
                'horas_solo_min'  => 0,
                'horas_noche_min' => 0,
                'horas_ifr_min'   => 20,
                'horas_sim_max'   => 20,
                'activo'          => true,
                'etapas' => [
                    [
                        'nombre'      => 'Escuela de Tierra RAC 141 — Apéndice 4 Párrafo E',
                        'descripcion' => 'Formación teórica. Total: 45 horas académicas.',
                        'materias' => [
                            ['codigo'=>'IFR-T01','nombre'=>'Derecho Aéreo',                                                     'horas'=> 5,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T02','nombre'=>'Conocimiento General de Aeronaves',                                  'horas'=> 5,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T03','nombre'=>'Procedimientos Operacionales',                                       'horas'=> 5,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T04','nombre'=>'Comunicaciones Aeronáuticas y Radiotelefónicas',                    'horas'=> 5,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T05','nombre'=>'Performance y Planificación de Vuelo (Peso y Balance)',              'horas'=> 8,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T06','nombre'=>'Meteorología',                                                       'horas'=> 8,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T07','nombre'=>'Navegación',                                                         'horas'=> 8,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-T08','nombre'=>'Factores Humanos',                                                   'horas'=> 1,'tipo'=>'teorica','nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                        ],
                    ],
                    [
                        'nombre'      => 'Entrenamiento de Vuelo RAC 61 / 61.315',
                        'descripcion' => 'Total: 40 horas (20h instrumentos + 20h simulador).',
                        'materias' => [
                            ['codigo'=>'IFR-V01','nombre'=>'Vuelo por Instrumentos', 'horas'=>20,'tipo'=>'vuelo',     'nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                            ['codigo'=>'IFR-S01','nombre'=>'Simulador',              'horas'=>20,'tipo'=>'simulador', 'nota_minima'=>75,'rac_referencia'=>'RAC 61.315'],
                        ],
                    ],
                ],
            ],

            // ══════════════════════════════════════════════════
            //  PROGRAMA PLUS — Horas adicionales de refuerzo
            // ══════════════════════════════════════════════════
            [
                'codigo'          => 'PLUS-141',
                'nombre'          => 'Programa Plus — Horas de Refuerzo',
                'tipo'            => 'PPL',
                'rac_referencia'  => 'RAC 141',
                'horas_tierra_min'=> 0,
                'horas_vuelo_min' => 11,
                'horas_dual_min'  => 11,
                'horas_solo_min'  => 0,
                'horas_noche_min' => 0,
                'horas_ifr_min'   => 0,
                'horas_sim_max'   => 0,
                'activo'          => true,
                'etapas' => [
                    [
                        'nombre'      => 'Horas Adicionales de Vuelo',
                        'descripcion' => '11 horas adicionales de vuelo para reforzar las fases que el alumno necesite al final del programa.',
                        'materias' => [
                            ['codigo'=>'PLUS-V01','nombre'=>'Horas de Refuerzo en Vuelo','horas'=>11,'tipo'=>'vuelo','nota_minima'=>75,'rac_referencia'=>'RAC 141'],
                        ],
                    ],
                ],
            ],

        ];

        foreach ($programas as $progData) {
            $etapasData = $progData['etapas'];
            unset($progData['etapas']);

            $programa = Programa::updateOrCreate(
                ['codigo' => $progData['codigo']],
                $progData
            );

            $numEtapa = 1;
            foreach ($etapasData as $etapaData) {
                $materiasData = $etapaData['materias'];
                unset($etapaData['materias']);

                $etapa = Etapa::updateOrCreate(
                    ['programa_id' => $programa->id, 'numero' => $numEtapa],
                    array_merge($etapaData, ['numero' => $numEtapa])
                );

                foreach ($materiasData as $materiaData) {
                    Materia::updateOrCreate(
                        ['codigo' => $materiaData['codigo']],
                        array_merge($materiaData, ['etapa_id' => $etapa->id])
                    );
                }

                $numEtapa++;
            }
        }

        $this->command->info('✅ Programas School of Aviation Training cargados correctamente.');
        $this->command->info('   PPL-141-AP1 · CPL-141-AP1 · IFR-141-AP4 · PLUS-141');
    }
}
