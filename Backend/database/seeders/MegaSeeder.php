<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class MegaSeeder extends Seeder
{
    private $faker;

    public function run(): void
    {
        $this->faker = Faker::create('es_ES');

        $this->command->info('🚀 Iniciando MegaSeeder - School Of Training Aviation');

        $this->seedCertMedicos();
        $this->seedCertInstructores();
        $this->seedPlanesClase();
        $this->seedBriefingsDebriefings();
        $this->seedRegistrosMantenimiento();
        $this->seedMelItems();
        $this->seedAirworthinessDirectives();
        $this->seedDocumentosRac();
        $this->seedMatriculas();
        $this->seedFacturasYPagos();
        $this->seedBancoPreguntas();
        $this->sincronizarVencimientos();

        $this->command->info('✅ MegaSeeder completado exitosamente.');
    }

    // ══════════════════════════════════════════════════════════════════
    // MÓDULO ACADÉMICO
    // ══════════════════════════════════════════════════════════════════

    private function seedCertMedicos(): void
    {
        $this->command->info('  → Certificados médicos estudiantes...');
        $estudiantes = DB::table('estudiantes')->pluck('id');
        if ($estudiantes->isEmpty()) return;

        foreach ($estudiantes as $estId) {
            // Evitar duplicados
            if (DB::table('cert_medicos')->where('estudiante_id', $estId)->exists()) continue;

            $clase = $this->faker->randomElement(['clase_1', 'clase_2', 'clase_3']);
            $emision = Carbon::now()->subMonths(rand(1, 18));
            $mesesVigencia = $clase === 'clase_1' ? 12 : 24;
            $vencimiento = $emision->copy()->addMonths($mesesVigencia);

            DB::table('cert_medicos')->insert([
                'estudiante_id'      => $estId,
                'tipo'               => $clase,
                'numero_cert'        => 'CMA-' . strtoupper($this->faker->bothify('??####')),
                'fecha_emision'      => $emision->toDateString(),
                'fecha_vencimiento'  => $vencimiento->toDateString(),
                'medico_examinador'  => 'Dr. ' . $this->faker->name(),
                'centro_medico'      => $this->faker->randomElement(['Aeromédica Bogotá', 'Clínica Aeronáutica Medellín', 'Centro Médico Aeronáutico Cali']),
                'apto'               => true,
                'observaciones'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
        $this->command->info('    ✓ Certificados médicos creados.');
    }

    private function seedCertInstructores(): void
    {
        $this->command->info('  → Certificaciones instructores...');
        $instructores = DB::table('instructores')->pluck('id');
        if ($instructores->isEmpty()) return;

        $tipos = ['Instructor de Vuelo', 'Habilitación IFR', 'Habilitación Multi-Motor', 'Certificado UAEAC', 'Rating CFI'];

        foreach ($instructores as $insId) {
            if (DB::table('cert_instructores')->where('instructor_id', $insId)->exists()) continue;

            foreach ($this->faker->randomElements($tipos, rand(2, 3)) as $tipo) {
                $emision = Carbon::now()->subMonths(rand(6, 36));
                $vencimiento = $emision->copy()->addYears(rand(1, 2));
                DB::table('cert_instructores')->insert([
                    'instructor_id'    => $insId,
                    'descripcion'      => $tipo,
                    'numero_cert'      => 'CI-' . strtoupper($this->faker->bothify('??####')),
                    'fecha_emision'    => $emision->toDateString(),
                    'fecha_vencimiento'=> $vencimiento->toDateString(),
                    'entidad_emisora'  => 'UAEAC Colombia',
                    'activo'           => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
        $this->command->info('    ✓ Certificaciones instructores creadas.');
    }

    private function seedPlanesClase(): void
    {
        $this->command->info('  → Planes de clase...');
        $instructores = DB::table('instructores')->pluck('id');
        $materias = DB::table('materias')->pluck('id');
        if ($instructores->isEmpty() || $materias->isEmpty()) return;

        for ($i = 0; $i < 15; $i++) {
            DB::table('planes_clase')->insert([
                'instructor_id'  => $instructores->random(),
                'materia_id'     => $materias->random(),
                'fecha'          => Carbon::now()->addDays(rand(-30, 30))->toDateString(),
                'duracion_horas' => $this->faker->randomElement([1.0, 1.5, 2.0, 2.5]),
                'tema'           => $this->faker->randomElement([
                    'Navegación básica VFR', 'Meteorología aeronáutica', 'Regulaciones RAC',
                    'Procedimientos de emergencia', 'Performance y limitaciones', 'Comunicaciones ATC',
                    'Instrumentos de vuelo', 'Aerodinámica aplicada', 'Operaciones de pista',
                ]),
                'objetivos'      => $this->faker->paragraph(2),
                'recursos'       => $this->faker->randomElement(['Aula teórica', 'Simulador FNPT-II', 'Briefing Room']),
                'ejecutado'      => $this->faker->boolean(60),
                'observaciones'  => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        $this->command->info('    ✓ Planes de clase creados.');
    }

    private function seedBriefingsDebriefings(): void
    {
        $this->command->info('  → Briefings y debriefings...');
        $bitacoras = DB::table('bitacoras_vuelo')->pluck('id');
        if ($bitacoras->isEmpty()) return;

        foreach ($bitacoras->take(15) as $bitacoraId) {
            if (DB::table('briefings_debriefings')->where('bitacora_id', $bitacoraId)->exists()) continue;
            DB::table('briefings_debriefings')->insert([
                'bitacora_id'       => $bitacoraId,
                'tipo'              => $this->faker->randomElement(['briefing', 'debriefing']),
                'notas'             => $this->faker->paragraph(3),
                'calificacion'      => rand(3, 5),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
        $this->command->info('    ✓ Briefings creados.');
    }

    private function seedBancoPreguntas(): void
    {
        $this->command->info('  → Banco de preguntas...');
        $materias = DB::table('materias')->pluck('id');
        if ($materias->isEmpty()) return;

        $preguntas = [
            ['¿Cuál es la velocidad de pérdida en configuración limpia?', 'Vs0', 'Vs1', 'Va', 'Vno', 1],
            ['¿Qué significa METAR?', 'Meteorological Aerodrome Report', 'Minimum Enroute Altitude Route', 'Maximum Elevation Route', 'Meteorological Area Report', 0],
            ['¿Cuántas millas náuticas tiene 1 km?', '0.54', '1.85', '1.0', '0.62', 0],
            ['¿Qué es el QNH?', 'Presión atmosférica reducida al nivel del mar', 'Altitud de seguridad', 'Temperatura en ruta', 'Velocidad del viento', 0],
            ['¿Cuál es el ángulo de ataque crítico?', '16-18°', '5-8°', '20-25°', '0-2°', 0],
            ['¿Qué regula el RAC 141 en Colombia?', 'Escuelas de aviación', 'Aeronaves civiles', 'Tripulaciones aéreas', 'Mantenimiento', 0],
        ];

        foreach ($materias->take(4) as $matId) {
            foreach ($preguntas as $p) {
                DB::table('banco_preguntas')->insert([
                    'materia_id'      => $matId,
                    'enunciado'       => $p[0],
                    'opcion_a'        => $p[1],
                    'opcion_b'        => $p[2],
                    'opcion_c'        => $p[3],
                    'opcion_d'        => $p[4],
                    'respuesta_correcta' => $p[5],
                    'nivel'           => $this->faker->randomElement(['basico', 'intermedio', 'avanzado']),
                    'activo'          => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
        $this->command->info('    ✓ Banco de preguntas creado.');
    }

    // ══════════════════════════════════════════════════════════════════
    // MÓDULO MANTENIMIENTO Y FLOTA
    // ══════════════════════════════════════════════════════════════════

    private function seedRegistrosMantenimiento(): void
    {
        $this->command->info('  → Registros de mantenimiento...');
        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($aeronaves->isEmpty()) return;

        $tipos = ['inspeccion_50h', 'inspeccion_100h', 'anual', 'correctivo', 'preventivo'];

        foreach ($aeronaves as $aeronaveId) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $fechaRealizado = Carbon::now()->subDays(rand(10, 180));
                $tipo = $this->faker->randomElement($tipos);
                DB::table('registros_mantenimiento')->insert([
                    'aeronave_id'     => $aeronaveId,
                    'tipo'            => $tipo,
                    'descripcion'     => $this->faker->sentence(8),
                    'fecha_realizado' => $fechaRealizado->toDateString(),
                    'horas_aeronave'  => rand(500, 3000) + (rand(0, 9) / 10),
                    'realizado_por'   => $this->faker->name(),
                    'licencia_tecnico'=> 'UAEAC-' . rand(1000, 9999),
                    'proxima_fecha'   => $fechaRealizado->copy()->addDays(rand(90, 365))->toDateString(),
                    'proximas_horas'  => rand(3000, 5000) + (rand(0, 9) / 10),
                    'costo'           => rand(500000, 8000000),
                    'archivo_url'     => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
        $this->command->info('    ✓ Registros de mantenimiento creados.');
    }

    private function seedMelItems(): void
    {
        $this->command->info('  → MEL Items...');
        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($aeronaves->isEmpty()) return;

        $ataCodes = ['21-10-01', '34-10-02', '27-20-01', '31-00-01', '22-10-03'];
        $categorias = ['A', 'B', 'C'];
        $diasLimite  = ['A' => 3, 'B' => 10, 'C' => 30];

        foreach ($aeronaves->take(3) as $aeronaveId) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $apertura = Carbon::now()->subDays(rand(1, 20));
                $cat = $this->faker->randomElement($categorias);
                DB::table('mel_items')->insert([
                    'aeronave_id'     => $aeronaveId,
                    'item_ata'        => $this->faker->randomElement($ataCodes),
                    'descripcion'     => $this->faker->sentence(6),
                    'categoria'       => $cat,
                    'fecha_apertura'  => $apertura->toDateString(),
                    'fecha_limite'    => $apertura->copy()->addDays($diasLimite[$cat])->toDateString(),
                    'estado'          => $this->faker->randomElement(['abierto', 'cerrado']),
                    'procedimiento_o' => $this->faker->paragraph(2),
                    'cerrado_por'     => null,
                    'fecha_cierre'    => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
        $this->command->info('    ✓ MEL items creados.');
    }

    private function seedAirworthinessDirectives(): void
    {
        $this->command->info('  → Airworthiness Directives (ADs)...');
        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($aeronaves->isEmpty()) return;

        foreach ($aeronaves as $aeronaveId) {
            $adNums = ['2023-20-05', '2022-15-08', '2024-01-02'];
            foreach ($this->faker->randomElements($adNums, rand(1, 2)) as $adNum) {
                DB::table('airworthiness_directives')->insert([
                    'aeronave_id'        => $aeronaveId,
                    'numero_ad'          => $adNum,
                    'titulo'             => 'Directiva de Aeronavegabilidad ' . $adNum,
                    'autoridad_emisora'  => $this->faker->randomElement(['FAA', 'EASA', 'UAEAC']),
                    'fecha_emision'      => Carbon::now()->subMonths(rand(1, 24))->toDateString(),
                    'fecha_limite'       => Carbon::now()->addMonths(rand(1, 6))->toDateString(),
                    'descripcion'        => $this->faker->paragraph(3),
                    'cumplido'           => $this->faker->boolean(70),
                    'fecha_cumplimiento' => $this->faker->boolean(70) ? Carbon::now()->subDays(rand(1, 60))->toDateString() : null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
        $this->command->info('    ✓ ADs creadas.');
    }

    // ══════════════════════════════════════════════════════════════════
    // MÓDULO CUMPLIMIENTO / DOCUMENTOS RAC
    // ══════════════════════════════════════════════════════════════════

    private function seedDocumentosRac(): void
    {
        $this->command->info('  → Documentos RAC...');

        $docs = [
            ['MOE',  'MOE-001', 'v3.2', 'Manual de Operaciones de la Escuela',    30, true],
            ['PIA',  'PIA-001', 'v2.1', 'Programa de Instrucción Aprobado PPL(A)', 60, true],
            ['PIA',  'PIA-002', 'v1.5', 'Programa de Instrucción CPL(A)',          45, true],
            ['certificado', 'CERT-141-BOG', 'v1.0', 'Certificado de Escuela RAC 141', 90, true],
            ['enmienda', 'ENM-2024-01', 'v1.0', 'Enmienda MOE procedimientos SMS',  10, false],
            ['circular', 'CIR-2024-05', 'v1.0', 'Circular operativa seguridad en pista', 5, true],
        ];

        foreach ($docs as $d) {
            if (DB::table('documentos_rac')->where('numero', $d[1])->exists()) continue;

            $fecha = Carbon::now()->subDays($d[4]);
            DB::table('documentos_rac')->insert([
                'tipo'             => $d[0],
                'numero'           => $d[1],
                'version'          => $d[2],
                'titulo'           => $d[3],
                'fecha_documento'  => $fecha->toDateString(),
                'aprobado_uaeac'   => $d[5],
                'fecha_aprobacion' => $d[5] ? $fecha->copy()->addDays(5)->toDateString() : null,
                'archivo_url'      => 'https://storage.escuela.co/docs/' . $d[1] . '.pdf',
                'vigente'          => true,
                'observaciones'    => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
        $this->command->info('    ✓ Documentos RAC creados.');
    }

    // ══════════════════════════════════════════════════════════════════
    // MÓDULO ADMINISTRATIVO Y FINANCIERO
    // ══════════════════════════════════════════════════════════════════

    private function seedMatriculas(): void
    {
        $this->command->info('  → Matrículas...');
        $estudiantes = DB::table('estudiantes')->pluck('id');
        $programas   = DB::table('programas')->pluck('id');
        if ($estudiantes->isEmpty() || $programas->isEmpty()) return;

        foreach ($estudiantes as $estId) {
            if (DB::table('matriculas')->where('estudiante_id', $estId)->exists()) continue;

            $valor = $this->faker->randomElement([15000000, 18000000, 22000000, 30000000]);
            DB::table('matriculas')->insert([
                'estudiante_id'  => $estId,
                'programa_id'    => $programas->random(),
                'fecha_matricula'=> Carbon::now()->subMonths(rand(1, 12))->toDateString(),
                'valor_total'    => $valor,
                'descuento'      => $this->faker->boolean(30) ? rand(500000, 2000000) : 0,
                'forma_pago'     => $this->faker->randomElement(['contado', 'cuotas', 'financiado']),
                'num_cuotas'     => $this->faker->randomElement([1, 3, 6, 12]),
                'estado'         => $this->faker->randomElement(['activa', 'activa', 'activa', 'suspendida']),
                'contrato_url'   => null,
                'observaciones'  => null,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
        $this->command->info('    ✓ Matrículas creadas.');
    }

    private function seedFacturasYPagos(): void
    {
        $this->command->info('  → Facturas y pagos...');
        $matriculas = DB::table('matriculas')->get();
        if ($matriculas->isEmpty()) return;

        $factNum = 1000;
        foreach ($matriculas as $mat) {
            $numCuotas = $mat->num_cuotas ?? 1;
            $valorCuota = round(($mat->valor_total - $mat->descuento) / $numCuotas, 2);

            for ($c = 1; $c <= min($numCuotas, 3); $c++) {
                $factNum++;
                $fechaFact = Carbon::parse($mat->fecha_matricula)->addMonths($c - 1);
                $factura_id = DB::table('facturas')->insertGetId([
                    'matricula_id'           => $mat->id,
                    'numero_factura'         => 'SETA-' . str_pad($factNum, 6, '0', STR_PAD_LEFT),
                    'fecha_factura'          => $fechaFact->toDateString(),
                    'fecha_vencimiento_pago' => $fechaFact->copy()->addDays(30)->toDateString(),
                    'concepto'               => 'Cuota ' . $c . ' de ' . $numCuotas . ' - Programa de instrucción aeronáutica',
                    'subtotal'               => $valorCuota,
                    'iva'                    => 0,
                    'total'                  => $valorCuota,
                    'estado'                 => $fechaFact->isPast() ? 'pagada' : 'pendiente',
                    'cufe'                   => strtoupper(md5($factNum . $mat->id)),
                    'archivo_url'            => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                // Registrar pago si la factura está pagada
                if ($fechaFact->isPast()) {
                    DB::table('pagos')->insert([
                        'factura_id'      => $factura_id,
                        'fecha_pago'      => $fechaFact->copy()->addDays(rand(1, 20))->toDateString(),
                        'monto'           => $valorCuota,
                        'medio_pago'      => $this->faker->randomElement(['transferencia', 'efectivo', 'tarjeta']),
                        'referencia'      => 'REF-' . strtoupper($this->faker->bothify('??####')),
                        'observaciones'   => null,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }
        $this->command->info('    ✓ Facturas y pagos creados.');
    }

    // ══════════════════════════════════════════════════════════════════
    // SINCRONIZAR VENCIMIENTOS AL FINAL
    // ══════════════════════════════════════════════════════════════════

    private function sincronizarVencimientos(): void
    {
        $this->command->info('  → Sincronizando alertas de vencimiento...');
        try {
            app(\App\Services\VencimientoService::class)->sincronizar();
            $total = DB::table('vencimientos_criticos')->count();
            $this->command->info("    ✓ Vencimientos sincronizados. Total: {$total} registros.");
        } catch (\Exception $e) {
            $this->command->error('    ✗ Error sincronizando vencimientos: ' . $e->getMessage());
        }
    }
}
