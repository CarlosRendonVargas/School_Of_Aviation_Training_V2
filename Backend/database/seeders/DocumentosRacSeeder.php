<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
