<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/* ═══════════════════════════════════════════════════════
   AeronavesSeeder
═══════════════════════════════════════════════════════ */
class AeronavesSeeder extends Seeder
{
    public function run(): void
    {
        $aeronaves = [
            [
                'matricula'           => 'HK-4521',
                'modelo'              => 'Cessna 172S Skyhawk',
                'fabricante'          => 'Cessna Aircraft Company',
                'serie'               => '172S12345',
                'anio'                => 2018,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 1240.5,
                'horas_motor_total'   => 1240.5,
                'horas_desde_oh'      => 240.5,
                'cert_airworthiness'  => 'CA-2023-HK4521',
                'venc_airworthiness'  => Carbon::now()->addMonths(8)->toDateString(),
                'venc_seguro'         => Carbon::now()->addMonths(6)->toDateString(),
                'estado'              => 'disponible',
            ],
            [
                'matricula'           => 'HK-4892',
                'modelo'              => 'Cessna 172SP',
                'fabricante'          => 'Cessna Aircraft Company',
                'serie'               => '172SP98765',
                'anio'                => 2015,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 3450.0,
                'horas_motor_total'   => 3450.0,
                'horas_desde_oh'      => 450.0,
                'cert_airworthiness'  => 'CA-2023-HK4892',
                'venc_airworthiness'  => Carbon::now()->addMonths(4)->toDateString(),
                'venc_seguro'         => Carbon::now()->addMonths(4)->toDateString(),
                'estado'              => 'disponible',
            ],
            [
                'matricula'           => 'HK-5103',
                'modelo'              => 'Piper PA-28-181 Archer III',
                'fabricante'          => 'Piper Aircraft',
                'serie'               => 'PA28181XYZ',
                'anio'                => 2019,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 890.0,
                'horas_motor_total'   => 890.0,
                'horas_desde_oh'      => 890.0,
                'cert_airworthiness'  => 'CA-2024-HK5103',
                'venc_airworthiness'  => Carbon::now()->addMonths(11)->toDateString(),
                'venc_seguro'         => Carbon::now()->addMonths(9)->toDateString(),
                'estado'              => 'disponible',
            ],
            [
                'matricula'           => 'HK-5281',
                'modelo'              => 'Piper PA-44-180 Seminole',
                'fabricante'          => 'Piper Aircraft',
                'serie'               => 'PA44180ABC',
                'anio'                => 2020,
                'categoria'           => 'normal',
                'clase'               => 'multimotor',
                'horas_celula_total'  => 650.0,
                'horas_motor_total'   => 650.0,
                'horas_desde_oh'      => 650.0,
                'cert_airworthiness'  => 'CA-2024-HK5281',
                'venc_airworthiness'  => Carbon::now()->addMonths(10)->toDateString(),
                'venc_seguro'         => Carbon::now()->addMonths(10)->toDateString(),
                'estado'              => 'disponible',
            ],
            [
                'matricula'           => 'HK-4765',
                'modelo'              => 'Cessna 172R',
                'fabricante'          => 'Cessna Aircraft Company',
                'serie'               => '172R55555',
                'anio'                => 2012,
                'categoria'           => 'normal',
                'clase'               => 'monomotor',
                'horas_celula_total'  => 5100.0,
                'horas_motor_total'   => 5100.0,
                'horas_desde_oh'      => 100.0,
                'cert_airworthiness'  => 'CA-2024-HK4765',
                'venc_airworthiness'  => Carbon::now()->addDays(25)->toDateString(), // ← próximo a vencer
                'venc_seguro'         => Carbon::now()->addMonths(2)->toDateString(),
                'estado'              => 'mantenimiento',
            ],
        ];

        foreach ($aeronaves as $av) {
            DB::table('aeronaves')->insert(array_merge($av, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}


/* ═══════════════════════════════════════════════════════
   InstructoresSeeder
═══════════════════════════════════════════════════════ */
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


/* ═══════════════════════════════════════════════════════
   EstudiantesSeeder
═══════════════════════════════════════════════════════ */
class EstudiantesSeeder extends Seeder
{
    public function run(): void
    {
        $pplId = DB::table('programas')->where('codigo', 'PPL-AV')->value('id');
        $cplId = DB::table('programas')->where('codigo', 'CPL-AV')->value('id');
        $etapa1Ppl = DB::table('etapas')
            ->where('programa_id', $pplId)
            ->orderBy('numero')
            ->value('id');

        $estudiantes = [
            [
                'email'         => 'estudiante1@escuelaviacion.co',
                'programa_id'   => $pplId,
                'expediente'    => 'EXP-2025-001',
                'fecha_ingreso' => Carbon::now()->subMonths(3)->toDateString(),
                'estado'        => 'activo',
                'etapa_id'      => $etapa1Ppl,
                'medico_tipo'   => 'clase_2',
                'medico_venc'   => Carbon::now()->addMonths(18)->toDateString(),
            ],
            [
                'email'         => 'estudiante2@escuelaviacion.co',
                'programa_id'   => $cplId,
                'expediente'    => 'EXP-2025-002',
                'fecha_ingreso' => Carbon::now()->subMonths(6)->toDateString(),
                'estado'        => 'activo',
                'etapa_id'      => null,
                'medico_tipo'   => 'clase_1',
                'medico_venc'   => Carbon::now()->addMonths(10)->toDateString(),
            ],
        ];

        foreach ($estudiantes as $data) {
            $personaId = DB::table('personas')
                ->join('usuarios', 'personas.usuario_id', '=', 'usuarios.id')
                ->where('usuarios.email', $data['email'])
                ->value('personas.id');

            if (! $personaId) continue;

            $estId = DB::table('estudiantes')->insertGetId([
                'persona_id'      => $personaId,
                'num_expediente'  => $data['expediente'],
                'programa_id'     => $data['programa_id'],
                'fecha_ingreso'   => $data['fecha_ingreso'],
                'estado'          => $data['estado'],
                'etapa_actual_id' => $data['etapa_id'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            // Certificado médico
            DB::table('cert_medicos')->insert([
                'estudiante_id'    => $estId,
                'tipo'             => $data['medico_tipo'],
                'numero_certificado' => 'CMED-' . strtoupper(substr($data['expediente'], -3)) . '-2025',
                'fecha_emision'    => Carbon::now()->subMonths(2)->toDateString(),
                'fecha_vencimiento' => $data['medico_venc'],
                'centro_aeromedico' => 'Centro Aeromédico Nacional – UAEAC Bogotá',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Matrícula
            $valorTotal = $data['programa_id'] === $pplId ? 28000000 : 75000000;
            $matriculaId = DB::table('matriculas')->insertGetId([
                'estudiante_id'  => $estId,
                'programa_id'    => $data['programa_id'],
                'fecha_matricula' => $data['fecha_ingreso'],
                'valor_total'    => $valorTotal,
                'descuento'      => 0,
                'forma_pago'     => 'cuotas',
                'num_cuotas'     => 6,
                'estado'         => 'activa',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // Primera factura
            DB::table('facturas')->insert([
                'matricula_id'   => $matriculaId,
                'numero_factura' => 'FE-' . date('Y') . '-' . str_pad($estId, 4, '0', STR_PAD_LEFT) . '-001',
                'fecha_factura'  => $data['fecha_ingreso'],
                'concepto'       => 'Cuota 1 de 6 – ' . DB::table('programas')->where('id', $data['programa_id'])->value('nombre'),
                'subtotal'       => round($valorTotal / 6 / 1.19, 2),
                'iva'            => round($valorTotal / 6 - ($valorTotal / 6 / 1.19), 2),
                'total'          => round($valorTotal / 6, 2),
                'estado'         => 'pagada',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}


/* ═══════════════════════════════════════════════════════
   DocumentosRacSeeder
═══════════════════════════════════════════════════════ */
class DocumentosRacSeeder extends Seeder
{
    public function run(): void
    {
        $docs = [
            [
                'tipo'             => 'MOE',
                'numero'           => 'MOE-ESC-2025-001',
                'version'          => '2.0',
                'titulo'           => 'Manual de Operaciones de la Escuela de Aviación',
                'fecha_documento'  => Carbon::now()->subMonths(3)->toDateString(),
                'aprobado_uaeac'   => true,
                'fecha_aprobacion' => Carbon::now()->subMonths(2)->toDateString(),
                'archivo_url'      => 'documentos/MOE_v2.pdf',
                'vigente'          => true,
            ],
            [
                'tipo'             => 'PIA',
                'numero'           => 'PIA-PPL-2025-001',
                'version'          => '1.3',
                'titulo'           => 'Programa de Instrucción Aprobado – Piloto Privado de Avión',
                'fecha_documento'  => Carbon::now()->subMonths(4)->toDateString(),
                'aprobado_uaeac'   => true,
                'fecha_aprobacion' => Carbon::now()->subMonths(3)->toDateString(),
                'archivo_url'      => 'documentos/PIA_PPL_v1.3.pdf',
                'vigente'          => true,
            ],
            [
                'tipo'             => 'PIA',
                'numero'           => 'PIA-CPL-2025-001',
                'version'          => '1.1',
                'titulo'           => 'Programa de Instrucción Aprobado – Piloto Comercial de Avión',
                'fecha_documento'  => Carbon::now()->subMonths(4)->toDateString(),
                'aprobado_uaeac'   => true,
                'fecha_aprobacion' => Carbon::now()->subMonths(3)->toDateString(),
                'archivo_url'      => 'documentos/PIA_CPL_v1.1.pdf',
                'vigente'          => true,
            ],
            [
                'tipo'             => 'certificado',
                'numero'           => 'CERT-RAC141-ESC-2025',
                'version'          => '1.0',
                'titulo'           => 'Certificado de Aprobación Escuela de Aviación RAC 141 – UAEAC',
                'fecha_documento'  => Carbon::create(2025, 1, 15)->toDateString(),
                'aprobado_uaeac'   => true,
                'fecha_aprobacion' => Carbon::create(2025, 1, 15)->toDateString(),
                'archivo_url'      => 'documentos/CERT_RAC141_2025.pdf',
                'vigente'          => true,
            ],
        ];

        foreach ($docs as $doc) {
            DB::table('documentos_rac')->insert(array_merge($doc, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Alertas de vencimiento críticas de ejemplo
        $vencimientos = [
            [
                'tipo_entidad'    => 'aeronave',
                'entidad_id'      => DB::table('aeronaves')->where('matricula', 'HK-4765')->value('id'),
                'descripcion'     => 'Certificado de aeronavegabilidad HK-4765',
                'fecha_vencimiento' => Carbon::now()->addDays(25)->toDateString(),
                'dias_alerta'     => 30,
                'nivel_critico'   => 'critico',
                'activo'          => true,
            ],
            [
                'tipo_entidad'    => 'aeronave',
                'entidad_id'      => DB::table('aeronaves')->where('matricula', 'HK-4892')->value('id'),
                'descripcion'     => 'Certificado de aeronavegabilidad HK-4892',
                'fecha_vencimiento' => Carbon::now()->addMonths(4)->toDateString(),
                'dias_alerta'     => 30,
                'nivel_critico'   => 'advertencia',
                'activo'          => true,
            ],
        ];

        foreach ($vencimientos as $v) {
            if (! $v['entidad_id']) continue;
            DB::table('vencimientos_criticos')->insert(array_merge($v, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
