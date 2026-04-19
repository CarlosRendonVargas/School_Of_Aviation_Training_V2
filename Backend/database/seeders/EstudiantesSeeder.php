<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
