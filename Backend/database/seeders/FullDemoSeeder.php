<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * FullDemoSeeder — School Of Training Aviation
 * ─────────────────────────────────────────────
 * Pobla TODOS los módulos con datos realistas para demo/testing.
 * Ejecutar DESPUÉS de DatabaseSeeder.
 *
 * php artisan db:seed --class=FullDemoSeeder
 */
class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('🛫 FullDemoSeeder — School Of Training Aviation');
        $this->command->newLine();

        $this->seedReservas();
        $this->seedBitacorasVuelo();
        $this->seedNotasAcademicas();
        $this->seedBancoPreguntas();
        $this->seedMantenimiento();
        $this->seedMelItems();
        $this->seedAirworthinessDirectives();
        $this->seedReportesSMS();
        $this->seedFacturasExtra();
        $this->seedCertInstructores();
        $this->sincronizarVencimientos();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->newLine();
        $this->command->info('✅ FullDemoSeeder completado exitosamente.');
        $this->command->info('   Contraseña para todos los usuarios: Rac141*2025');
        $this->command->table(
            ['Rol', 'Email'],
            [
                ['Dir. Operaciones', 'dirops@escuelaviacion.co'],
                ['Admin',           'admin@escuelaviacion.co'],
                ['Instructor 1',    'instructor1@escuelaviacion.co'],
                ['Instructor 2',    'instructor2@escuelaviacion.co'],
                ['Estudiante 1',    'estudiante1@escuelaviacion.co'],
                ['Estudiante 2',    'estudiante2@escuelaviacion.co'],
                ['Mantenimiento',   'mx@escuelaviacion.co'],
                ['Auditor UAEAC',   'uaeac@aerocivil.gov.co'],
            ]
        );
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: RESERVAS Y DESPACHO
    // Columnas: aeronave_id, estudiante_id, instructor_id, fecha,
    //           hora_inicio, hora_fin, tipo(instruccion|solo|simulador),
    //           estado(pendiente|confirmada|cancelada|completada)
    // ══════════════════════════════════════════════════════════════
    private function seedReservas(): void
    {
        $this->command->info('  → Reservas de aeronaves...');

        $aeronaves    = DB::table('aeronaves')->where('estado', 'disponible')->pluck('id');
        $estudiantes  = DB::table('estudiantes')->pluck('id');
        $instructores = DB::table('instructores')->pluck('id');

        if ($aeronaves->isEmpty() || $estudiantes->isEmpty()) {
            $this->command->warn('    ⚠ Sin aeronaves/estudiantes. Saltando reservas.');
            return;
        }

        $estados = ['confirmada','confirmada','confirmada','pendiente','cancelada','completada'];
        $tipos   = ['instruccion','instruccion','instruccion','solo','simulador'];

        for ($i = 0; $i < 35; $i++) {
            $dia      = Carbon::now()->subDays(rand(-10, 60));
            $horaIni  = rand(7, 15);
            $duracion = rand(1, 3);
            $horaFin  = min($horaIni + $duracion, 18);
            $avId     = $aeronaves->random();

            // evitar duplicados en mismo slot
            $ocupado = DB::table('reservas')
                ->where('aeronave_id', $avId)
                ->where('fecha', $dia->toDateString())
                ->where('hora_inicio', sprintf('%02d:00', $horaIni))
                ->exists();
            if ($ocupado) continue;

            $estado = $estados[array_rand($estados)];
            $tipo   = $tipos[array_rand($tipos)];

            DB::table('reservas')->insert([
                'aeronave_id'       => $avId,
                'estudiante_id'     => $estudiantes->random(),
                'instructor_id'     => ($tipo !== 'solo' && $instructores->isNotEmpty())
                    ? $instructores->random() : null,
                'fecha'             => $dia->toDateString(),
                'hora_inicio'       => sprintf('%02d:00', $horaIni),
                'hora_fin'          => sprintf('%02d:00', $horaFin),
                'tipo'              => $tipo,
                'estado'            => $estado,
                'motivo_cancelacion'=> $estado === 'cancelada' ? 'Condiciones meteorológicas desfavorables.' : null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
        $this->command->info('    ✓ Reservas creadas.');
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: BITÁCORAS DE VUELO (RAC 91.417)
    // ══════════════════════════════════════════════════════════════
    private function seedBitacorasVuelo(): void
    {
        $this->command->info('  → Bitácoras de vuelo RAC 91.417...');

        $aeronaves    = DB::table('aeronaves')->pluck('id');
        $estudiantes  = DB::table('estudiantes')->pluck('id');
        $instructores = DB::table('instructores')->pluck('id');

        if ($aeronaves->isEmpty() || $estudiantes->isEmpty()) {
            $this->command->warn('    ⚠ Sin datos base. Saltando bitácoras.');
            return;
        }

        // Verificar cuántas ya existen
        $existentes = DB::table('bitacoras_vuelo')->count();
        if ($existentes >= 30) {
            $this->command->info("    ✓ Ya existen {$existentes} bitácoras. Añadiendo más...");
        }

        $rutas = [
            ['SKBO','SKMR'],['SKBO','SKVP'],['SKMR','SKBO'],
            ['SKVP','SKCL'],['SKCL','SKVP'],['SKBO','SKLT'],
            ['SKLT','SKBO'],['SKBO','SKMD'],['SKMD','SKBO'],
            ['SKBO','SKCG'],['SKCG','SKBO'],
        ];
        $tipos = ['local','local','local','navegacion','navegacion','noche','ifr','sim'];

        for ($i = 0; $i < 50; $i++) {
            $fecha     = Carbon::now()->subDays(rand(1, 180));
            $horaIni   = rand(7, 15);
            $durMin    = rand(60, 180);
            $horaFin   = $horaIni + intval($durMin / 60);
            $horasFrac = round($durMin / 60, 1);
            $tipo      = $tipos[array_rand($tipos)];
            $ruta      = $rutas[array_rand($rutas)];

            $horasDual  = in_array($tipo, ['local','navegacion','noche','ifr'])
                ? round($horasFrac * 0.6, 1) : 0;
            $horasSolo  = round(max($horasFrac - $horasDual, 0), 1);
            $horasNoche = $tipo === 'noche' ? $horasFrac : 0;
            $horasIfr   = $tipo === 'ifr'   ? $horasFrac : 0;
            $horasSim   = $tipo === 'sim'   ? $horasFrac : 0;
            $estId      = $estudiantes->random();
            $insId      = $instructores->isNotEmpty() ? $instructores->random() : null;
            $avId       = $aeronaves->random();

            DB::table('bitacoras_vuelo')->insert([
                'estudiante_id'   => $estId,
                'instructor_id'   => $insId,
                'aeronave_id'     => $avId,
                'reserva_id'      => null,
                'fecha'           => $fecha->toDateString(),
                'hora_salida'     => sprintf('%02d:00', $horaIni),
                'hora_llegada'    => sprintf('%02d:%02d', $horaFin, $durMin % 60),
                'origen_icao'     => $ruta[0],
                'destino_icao'    => $ruta[1],
                'horas_totales'   => $horasFrac,
                'horas_dual'      => $horasDual,
                'horas_solo'      => $horasSolo,
                'horas_noche'     => $horasNoche,
                'horas_ifr'       => $horasIfr,
                'horas_simulador' => $horasSim,
                'tipo_vuelo'      => $tipo,
                'condiciones_vmc' => rand(0, 9) < 8,
                'aterrizajes'     => rand(1, 4),
                'firma_instructor'=> ($insId && rand(0,1))
                    ? 'signed:' . $insId . ':' . now()->timestamp : null,
                'firma_estudiante' => rand(0,1)
                    ? 'signed:' . $estId . ':' . now()->timestamp : null,
                'observaciones'   => null,
                'created_at'      => $fecha,
                'updated_at'      => $fecha,
            ]);
        }
        $this->command->info('    ✓ 50 bitácoras de vuelo creadas.');
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: NOTAS ACADÉMICAS
    // Columnas: estudiante_id, materia_id, instructor_id, nota,
    //           aprobado, intento_num, fecha_evaluacion, observaciones
    // ══════════════════════════════════════════════════════════════
    private function seedNotasAcademicas(): void
    {
        $this->command->info('  → Notas académicas...');

        $estudiantesIds = DB::table('estudiantes')->pluck('id');
        $materias       = DB::table('materias')->pluck('id');
        $instructores   = DB::table('instructores')->pluck('id');

        if ($estudiantesIds->isEmpty() || $materias->isEmpty() || $instructores->isEmpty()) {
            $this->command->warn('    ⚠ Faltan datos base (estudiantes/materias/instructores). Saltando.');
            return;
        }

        foreach ($estudiantesIds as $estId) {
            foreach ($materias->take(8) as $materiaId) {
                $existe = DB::table('notas_academicas')
                    ->where('estudiante_id', $estId)
                    ->where('materia_id', $materiaId)
                    ->exists();
                if ($existe) continue;

                $nota         = rand(55, 100);
                $notaMinima   = DB::table('materias')->where('id', $materiaId)->value('nota_minima') ?? 70;

                DB::table('notas_academicas')->insert([
                    'estudiante_id'   => $estId,
                    'materia_id'      => $materiaId,
                    'instructor_id'   => $instructores->random(),
                    'nota'            => $nota,
                    'aprobado'        => $nota >= $notaMinima,
                    'intento_num'     => 1,
                    'fecha_evaluacion'=> Carbon::now()->subDays(rand(5, 120))->toDateString(),
                    'observaciones'   => $nota < $notaMinima
                        ? 'Calificación insuficiente. Se requiere refuerzo antes de segunda evaluación.'
                        : null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
        $this->command->info('    ✓ Notas académicas creadas.');
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: BANCO DE PREGUNTAS (RAC 141.73)
    // Columnas: materia_id, pregunta, tipo(multiple|verdadero_falso|abierta),
    //           opciones(JSON nullable), respuesta_correcta(text),
    //           nivel_dificultad(1=básico,2=medio,3=avanzado), rac_referencia, activo
    // ══════════════════════════════════════════════════════════════
    private function seedBancoPreguntas(): void
    {
        $this->command->info('  → Banco de preguntas RAC 141.73...');

        $materias = DB::table('materias')->pluck('id');
        if ($materias->isEmpty()) {
            $this->command->warn('    ⚠ Sin materias. Saltando.');
            return;
        }

        // [pregunta, [opA,opB,opC,opD], respuesta_correcta(texto), nivel(1-3), rac]
        $preguntas = [
            ['¿Qué produce sustentación en un ala?',
             ['La presión diferencial entre extradós e intradós','El peso de la aeronave','La velocidad del motor','El ángulo de declinación'],
             'La presión diferencial entre extradós e intradós', 1, 'RAC 61 / Aerodinámica'],
            ['¿Cuál es el ángulo de ataque crítico aproximado?',
             ['Entre 15° y 20°','5°','0°','45°'],
             'Entre 15° y 20°', 2, 'RAC 61'],
            ['¿Qué es Vs0?',
             ['Velocidad de pérdida en configuración de aterrizaje','Velocidad máxima en turbulencia','Velocidad de maniobra','Velocidad nunca exceder'],
             'Velocidad de pérdida en configuración de aterrizaje', 2, 'RAC 61'],
            ['¿Qué significa METAR?',
             ['METeorological Aerodrome Report','Maximum Elevation Terrain And Rocks','Meteorological Enroute Altitude Report','Minimum En Route Altitude'],
             'METeorological Aerodrome Report', 1, 'RAC 91 / Meteorología'],
            ['¿Qué es el QNH?',
             ['Presión reducida al nivel medio del mar','Presión en estación','Temperatura en ruta','Velocidad del viento corregida'],
             'Presión reducida al nivel medio del mar', 1, 'RAC 91'],
            ['¿Cuál es la visibilidad mínima VFR Colombia en espacio aéreo clase E?',
             ['5 km','1 km','3 km','8 km'],
             '5 km', 1, 'RAC 91'],
            ['¿Qué regula el RAC 141 en Colombia?',
             ['Escuelas de aviación civil y programas de instrucción','Aeronavegabilidad de aeronaves','Licencias de pilotos','Operaciones de línea aérea'],
             'Escuelas de aviación civil y programas de instrucción', 1, 'RAC 141'],
            ['¿Cuál es el mínimo de horas de vuelo para el PPL según RAC 61?',
             ['40 horas','20 horas','60 horas','80 horas'],
             '40 horas', 2, 'RAC 61.109'],
            ['¿Qué documento certifica que una aeronave es apta para volar?',
             ['Certificado de aeronavegabilidad','Matrícula','Libro de vuelo','Manual de vuelo'],
             'Certificado de aeronavegabilidad', 1, 'RAC 21'],
            ['¿Qué es un VOR?',
             ['VHF Omnidirectional Range – ayuda de radio navegación','Velocidad operacional de referencia','Vector de orientación radar','Visual Outbound Requirement'],
             'VHF Omnidirectional Range – ayuda de radio navegación', 2, 'RAC 91 / Navegación'],
            ['¿Qué es la hipoxia en aviación?',
             ['Deficiencia de oxígeno en el cerebro','Exceso de nitrógeno','Pérdida de presión de cabina','Visión doble por G'],
             'Deficiencia de oxígeno en el cerebro', 2, 'OACI Anexo 1 / FH'],
            ['¿Qué es CFIT?',
             ['Controlled Flight Into Terrain','Control de Fallas en Ingeniería de Turbinas','Chequeo de Fallos en Instrumentos de Tierra','Certificado de Formación Interoperable'],
             'Controlled Flight Into Terrain', 3, 'OACI / SMS'],
            ['¿Qué es la densidad de altitud?',
             ['Altitud de presión corregida por temperatura','Altitud indicada en el altímetro','Altura sobre el terreno','Altitud de vuelo crucero'],
             'Altitud de presión corregida por temperatura', 2, 'RAC 61 / Performance'],
            ['¿Cómo afecta la temperatura alta al rendimiento del motor?',
             ['Reduce la potencia disponible','Aumenta la eficiencia del motor','No tiene efecto','Reduce el consumo de combustible'],
             'Reduce la potencia disponible', 2, 'RAC 61'],
            ['¿Qué indica un SIGMET?',
             ['Fenómenos meteorológicos significativos para la aviación','Procedimiento especial de salida','Servicio de información de vuelo','Carta de zona terminal'],
             'Fenómenos meteorológicos significativos para la aviación', 2, 'RAC 91 / Meteorología'],
            ['¿Qué es el factor de carga en un viraje de 60°?',
             ['2g','1g','1.5g','3g'],
             '2g', 3, 'RAC 61 / Aerodinámica'],
            ['¿Cuántas millas náuticas equivale 1 kilómetro?',
             ['0.54 nm','1.85 nm','1.0 nm','0.62 nm'],
             '0.54 nm', 1, 'Navegación'],
            ['¿Qué es el sistema SMS en aeronáutica?',
             ['Safety Management System – sistema de gestión de seguridad','Satellite Monitoring System','Standard Meteorological Service','Sistema de Mantenimiento Supervisado'],
             'Safety Management System – sistema de gestión de seguridad', 2, 'OACI Anexo 19'],
        ];

        $totalCreados = 0;
        foreach ($materias->take(6) as $materiaId) {
            foreach ($preguntas as $p) {
                $existe = DB::table('banco_preguntas')
                    ->where('materia_id', $materiaId)
                    ->where('pregunta', $p[0])
                    ->exists();
                if ($existe) continue;

                DB::table('banco_preguntas')->insert([
                    'materia_id'         => $materiaId,
                    'pregunta'           => $p[0],
                    'tipo'               => 'multiple',
                    'opciones'           => json_encode($p[1]),
                    'respuesta_correcta' => $p[2],
                    'nivel_dificultad'   => $p[3],
                    'rac_referencia'     => $p[4],
                    'activo'             => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $totalCreados++;
            }
        }
        $this->command->info("    ✓ Banco de preguntas: {$totalCreados} preguntas creadas.");
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: REGISTROS DE MANTENIMIENTO (RAC 43)
    // ══════════════════════════════════════════════════════════════
    private function seedMantenimiento(): void
    {
        $this->command->info('  → Registros de mantenimiento RAC 43...');

        $aeronaves = DB::table('aeronaves')->get();
        if ($aeronaves->isEmpty()) {
            $this->command->warn('    ⚠ Sin aeronaves. Saltando.');
            return;
        }

        $tecnicos = [
            ['Jorge Luis Prada',      'AME-UAEAC-2845'],
            ['Carlos Méndez Romero',  'AME-UAEAC-3101'],
            ['Álvaro Suárez Niño',    'AME-UAEAC-1987'],
        ];

        $tiposObs = [
            ['inspeccion_50h',   'Inspección de 50 horas. Motor, tren de aterrizaje y aviónica verificados. Sin anomalías.'],
            ['inspeccion_100h',  'Inspección de 100 horas RAC 43. Cambio de filtro de aceite y bujías. Controles en límite.'],
            ['anual',            'Inspección anual completa. Certificado renovado. Todos los sistemas en condición aeronavegable.'],
            ['correctivo',       'Reemplazo de neumático principal derecho dañado por FOD. Rueda reinstalada y torqueada.'],
            ['preventivo',       'Cambio programado de aceite W100 y revisión de filtros. Niveles en especificaciones del fabricante.'],
            ['preventivo',       'Lubricación de mandos de vuelo, revisión hidráulica y prueba de radio VHF. Sin observaciones.'],
            ['AD',               'Cumplimiento AD FAA-2023-20-05: Inspección de horquilla del tren de nariz completada.'],
        ];

        $totalCreados = 0;
        foreach ($aeronaves as $av) {
            $numRegistros = rand(3, 6);
            for ($i = 0; $i < $numRegistros; $i++) {
                $tipoObs  = $tiposObs[array_rand($tiposObs)];
                $tecnico  = $tecnicos[array_rand($tecnicos)];
                $fechaReal = Carbon::now()->subDays(rand(10, 300));
                $horasAv   = max(round((float)$av->horas_celula_total - rand(10, 200), 1), 0);

                $existe = DB::table('registros_mantenimiento')
                    ->where('aeronave_id', $av->id)
                    ->where('tipo', $tipoObs[0])
                    ->where('fecha_realizado', $fechaReal->toDateString())
                    ->exists();
                if ($existe) continue;

                DB::table('registros_mantenimiento')->insert([
                    'aeronave_id'      => $av->id,
                    'tipo'             => $tipoObs[0],
                    'descripcion'      => $tipoObs[1],
                    'fecha_realizado'  => $fechaReal->toDateString(),
                    'horas_aeronave'   => $horasAv,
                    'realizado_por'    => $tecnico[0],
                    'licencia_tecnico' => $tecnico[1],
                    'proxima_fecha'    => $fechaReal->copy()->addDays(rand(90, 365))->toDateString(),
                    'proximas_horas'   => round($horasAv + rand(50, 200), 1),
                    'costo'            => rand(300000, 8000000),
                    'archivo_url'      => null,
                    'created_at'       => $fechaReal,
                    'updated_at'       => $fechaReal,
                ]);
                $totalCreados++;
            }
        }
        $this->command->info("    ✓ {$totalCreados} registros de mantenimiento creados.");
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: MEL ITEMS
    // ══════════════════════════════════════════════════════════════
    private function seedMelItems(): void
    {
        $this->command->info('  → Ítems MEL...');

        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($aeronaves->isEmpty()) return;

        $melData = [
            ['22-10-01', 'Sistema de piloto automático modo ALT HOLD inoperativo', 'B', 10],
            ['34-10-02', 'Indicador de horizonte de reserva con lag de 2 segundos', 'C', 30],
            ['27-20-01', 'Fricción en estabilizador horizontal al 15% de recorrido', 'C', 30],
            ['21-10-04', 'Válvula de calefacción de cabina atascada en posición abierta', 'D', 120],
            ['31-00-01', 'Reloj de cabina inoperativo', 'D', 120],
            ['28-10-02', 'Indicador de cantidad de combustible – tanque izq. ±5% descalibrado', 'B', 10],
        ];

        $totalCreados = 0;
        foreach ($aeronaves->take(4) as $avId) {
            $numMel = rand(1, 3);
            $indices = (array) array_rand($melData, min($numMel, count($melData)));

            foreach ($indices as $idx) {
                $mel      = $melData[$idx];
                $apertura = Carbon::now()->subDays(rand(1, max($mel[3] - 1, 1)));
                $limite   = $apertura->copy()->addDays($mel[3]);
                $cerrado  = rand(0, 10) > 6;

                $existe = DB::table('mel_items')
                    ->where('aeronave_id', $avId)
                    ->where('item_ata', $mel[0])
                    ->where('estado', 'abierto')
                    ->exists();
                if ($existe) continue;

                DB::table('mel_items')->insert([
                    'aeronave_id'     => $avId,
                    'item_ata'        => $mel[0],
                    'descripcion'     => $mel[1],
                    'categoria'       => $mel[2],
                    'fecha_apertura'  => $apertura->toDateString(),
                    'fecha_limite'    => $limite->toDateString(),
                    'estado'          => $cerrado ? 'cerrado' : 'abierto',
                    'procedimiento_o' => 'Operar sin equipo afectado según procedimiento O del MEL aprobado. Notificar AME antes de siguiente vuelo.',
                    'cerrado_por'     => $cerrado ? 'Jorge Luis Prada – AME-UAEAC-2845' : null,
                    'fecha_cierre'    => $cerrado ? $apertura->copy()->addDays(rand(1, 5))->toDateString() : null,
                    'created_at'      => $apertura,
                    'updated_at'      => now(),
                ]);
                $totalCreados++;
            }
        }
        $this->command->info("    ✓ {$totalCreados} ítems MEL creados.");
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: AIRWORTHINESS DIRECTIVES (ADs)
    // ══════════════════════════════════════════════════════════════
    private function seedAirworthinessDirectives(): void
    {
        $this->command->info('  → Directivas de Aeronavegabilidad (ADs)...');

        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($aeronaves->isEmpty()) return;

        // [numero_ad, titulo, fecha_emision, dias_limite_desde_hoy_o_null, cumplido]
        $ads = [
            ['FAA-2023-20-05', 'Inspeción horquilla empuje tren nariz – Cessna 172', '2023-08-15', 15, true],
            ['EASA-AD-2022-0145R1', 'Control cable timón de profundidad – PA-28', '2022-11-01', 30, false],
            ['UAEAC-2024-001', 'Revisión ELT – cumplimiento obligatorio aeronaves civiles', '2024-01-10', null, true],
            ['FAA-2024-01-02', 'Verificación rodamientos de escape – Lycoming IO-360', '2024-03-05', 60, false],
            ['FAA-2022-15-08', 'Inspección tapón combustible ala – Cessna 172SP', '2022-06-20', null, true],
        ];

        $totalCreados = 0;
        foreach ($aeronaves as $avId) {
            $numAds = rand(2, 4);
            $indices = (array) array_rand($ads, min($numAds, count($ads)));

            foreach ($indices as $idx) {
                $ad = $ads[$idx];
                $existe = DB::table('airworthiness_directives')
                    ->where('aeronave_id', $avId)
                    ->where('numero_ad', $ad[0])
                    ->exists();
                if ($existe) continue;

                $cumplido    = $ad[4];
                $fechaLimite = $ad[3] ? Carbon::now()->addDays($ad[3])->toDateString() : null;

                DB::table('airworthiness_directives')->insert([
                    'aeronave_id'        => $avId,
                    'numero_ad'          => $ad[0],
                    'titulo'             => $ad[1],
                    'fecha_emision'      => $ad[2],
                    'fecha_limite'       => $fechaLimite,
                    'estado'             => $cumplido ? 'cumplido' : 'pendiente',
                    'metodo_cumplimiento'=> $cumplido
                        ? 'Inspección realizada por AME-UAEAC-2845. Registro en libro de a bordo RAC 43.'
                        : null,
                    'archivo_url'        => null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
                $totalCreados++;
            }
        }
        $this->command->info("    ✓ {$totalCreados} directivas de aeronavegabilidad creadas.");
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: SMS (OACI Anexo 19)
    // Columnas: reportante_id(nullable), anonimo, tipo(peligro|incidente|accidente|near_miss),
    //           descripcion, fecha_evento, lugar, aeronave_id(nullable),
    //           severidad(1-5), probabilidad(1-5), nivel_riesgo(1-25),
    //           estado(nuevo|en_analisis|cerrado), notificado_uaeac
    // Acciones: reporte_sms_id, descripcion, responsable_id, fecha_limite,
    //           estado(pendiente|en_proceso|cerrada|verificada), fecha_cierre, observaciones_cierre
    // ══════════════════════════════════════════════════════════════
    private function seedReportesSMS(): void
    {
        $this->command->info('  → Reportes SMS (Seguridad Operacional)...');

        $usuarios  = DB::table('usuarios')->pluck('id');
        $aeronaves = DB::table('aeronaves')->pluck('id');
        if ($usuarios->isEmpty()) return;

        $reportes = [
            [
                'tipo'        => 'peligro',
                'descripcion' => 'FOD detectado en umbral de pista 31: fragmento metálico (tuerca M10) encontrado durante inspección pre-vuelo. Se retiró inmediatamente y se notificó a la torre.',
                'lugar'       => 'Pista 31 – Umbral',
                'severidad'   => 4,
                'probabilidad'=> 3,
                'estado'      => 'cerrado',
                'dias_atras'  => 45,
                'notif_uaeac' => false,
            ],
            [
                'tipo'        => 'peligro',
                'descripcion' => 'Extintores del hangar principal con indicador en zona roja. Requieren inspección y recarga urgente por empresa certificada AME.',
                'lugar'       => 'Hangar principal',
                'severidad'   => 3,
                'probabilidad'=> 2,
                'estado'      => 'en_analisis',
                'dias_atras'  => 20,
                'notif_uaeac' => false,
            ],
            [
                'tipo'        => 'incidente',
                'descripcion' => 'Incursión en pista: aeronave HK-4521 cruzó pista 13-31 sin autorización de la torre durante rodaje post-aterrizaje. Instructor a bordo corrigió inmediatamente. Sin consecuencias materiales.',
                'lugar'       => 'Pista 13-31 – Calles de rodaje',
                'severidad'   => 5,
                'probabilidad'=> 2,
                'estado'      => 'cerrado',
                'dias_atras'  => 60,
                'notif_uaeac' => true,
            ],
            [
                'tipo'        => 'near_miss',
                'descripcion' => 'Procedimiento de emergencia motor simulado: estudiante evidenció confusión en checklist Emergency Landing. Instructor intervino oportunamente. Se planificó sesión de remediación.',
                'lugar'       => 'En vuelo – Área local SKBO',
                'severidad'   => 3,
                'probabilidad'=> 3,
                'estado'      => 'en_analisis',
                'dias_atras'  => 10,
                'notif_uaeac' => false,
            ],
            [
                'tipo'        => 'peligro',
                'descripcion' => 'ATIS desactualizado no reflejaba presencia de CB en sector NW a 20nm del aeródromo. Aeronave HK-5103 realizó desvío preventivo al detectarlo por tráfico.',
                'lugar'       => 'Sector NW – 20nm SKBO',
                'severidad'   => 4,
                'probabilidad'=> 3,
                'estado'      => 'nuevo',
                'dias_atras'  => 5,
                'notif_uaeac' => false,
            ],
            [
                'tipo'        => 'peligro',
                'descripcion' => 'Baterías del ELT vencidas en aeronave HK-4892. Aeronave puesta en mantenimiento hasta reemplazo por técnico certificado AME.',
                'lugar'       => 'Hangar – Aeronave HK-4892',
                'severidad'   => 4,
                'probabilidad'=> 2,
                'estado'      => 'en_analisis',
                'dias_atras'  => 15,
                'notif_uaeac' => false,
            ],
            [
                'tipo'        => 'peligro',
                'descripcion' => 'Señalización nocturna insuficiente en calles de rodaje entre 18:00-06:00. Se propone instalación de luces LED en bordes de calles de rodaje para mejorar seguridad.',
                'lugar'       => 'Calles de rodaje – Área terminal',
                'severidad'   => 2,
                'probabilidad'=> 2,
                'estado'      => 'nuevo',
                'dias_atras'  => 3,
                'notif_uaeac' => false,
            ],
        ];

        foreach ($reportes as $r) {
            $existe = DB::table('reportes_sms')
                ->where('descripcion', 'like', substr($r['descripcion'], 0, 50) . '%')
                ->exists();
            if ($existe) continue;

            $fechaRep  = Carbon::now()->subDays($r['dias_atras']);
            $anonimo   = rand(0, 10) > 7;
            $nivelRiesgo = $r['severidad'] * $r['probabilidad'];

            $reporteId = DB::table('reportes_sms')->insertGetId([
                'reportante_id'  => $anonimo ? null : $usuarios->random(),
                'anonimo'        => $anonimo,
                'tipo'           => $r['tipo'],
                'descripcion'    => $r['descripcion'],
                'fecha_evento'   => Carbon::now()->subDays($r['dias_atras'] + rand(0, 2))->toDateTime(),
                'lugar'          => $r['lugar'],
                'aeronave_id'    => $aeronaves->isNotEmpty() ? $aeronaves->random() : null,
                'severidad'      => $r['severidad'],
                'probabilidad'   => $r['probabilidad'],
                'nivel_riesgo'   => $nivelRiesgo,
                'estado'         => $r['estado'],
                'notificado_uaeac' => $r['notif_uaeac'],
                'created_at'     => $fechaRep,
                'updated_at'     => now(),
            ]);

            // Acciones correctivas para reportes no nuevos
            if ($r['estado'] !== 'nuevo') {
                $responsableId = $usuarios->random();
                $estadoAccion  = $r['estado'] === 'cerrado' ? 'cerrada' : 'en_proceso';

                DB::table('acciones_correctivas')->insert([
                    'reporte_sms_id'      => $reporteId,
                    'descripcion'         => 'Investigación realizada. Se identificó causa raíz y se implementaron medidas preventivas. Caso documentado para próxima auditoría UAEAC.',
                    'responsable_id'      => $responsableId,
                    'fecha_limite'        => $fechaRep->copy()->addDays(rand(10, 20))->toDateString(),
                    'estado'              => $estadoAccion,
                    'evidencia_url'       => null,
                    'fecha_cierre'        => $estadoAccion === 'cerrada'
                        ? $fechaRep->copy()->addDays(rand(7, 30))->toDateString()
                        : null,
                    'observaciones_cierre'=> $estadoAccion === 'cerrada'
                        ? 'Medida efectiva verificada. Sin recurrencia registrada en los 30 días posteriores.'
                        : null,
                    'created_at'          => $fechaRep->copy()->addDays(1),
                    'updated_at'          => now(),
                ]);
            }
        }
        $this->command->info('    ✓ ' . count($reportes) . ' reportes SMS creados con acciones correctivas.');
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: FACTURAS ADICIONALES (Admin/Financiero)
    // ══════════════════════════════════════════════════════════════
    private function seedFacturasExtra(): void
    {
        $this->command->info('  → Facturas y pagos adicionales...');

        $matriculas = DB::table('matriculas')->get();
        if ($matriculas->isEmpty()) {
            $this->command->warn('    ⚠ Sin matrículas. Saltando facturas.');
            return;
        }

        $factMax = DB::table('facturas')->max(DB::raw("CAST(SUBSTRING_INDEX(numero_factura,'-',-1) AS UNSIGNED)")) ?? 2000;
        $factNum = $factMax + 1;
        $totalCreadas = 0;

        foreach ($matriculas as $mat) {
            $numCuotas  = max($mat->num_cuotas ?? 6, 1);
            $descuento  = $mat->descuento ?? 0;
            $valorCuota = round(($mat->valor_total - $descuento) / $numCuotas, 2);
            $existentes = DB::table('facturas')->where('matricula_id', $mat->id)->count();
            if ($existentes >= $numCuotas) continue;

            $cuotasACrear = min($numCuotas - $existentes, 5);
            for ($c = $existentes + 1; $c <= $existentes + $cuotasACrear; $c++) {
                $factNum++;
                $fechaFact = Carbon::parse($mat->fecha_matricula)->addMonths($c - 1);
                $esPasada  = $fechaFact->isPast();
                $estado    = $esPasada
                    ? (rand(0, 10) > 2 ? 'pagada' : 'vencida')
                    : 'pendiente';

                $factId = DB::table('facturas')->insertGetId([
                    'matricula_id'           => $mat->id,
                    'numero_factura'         => 'SETA-' . str_pad($factNum, 6, '0', STR_PAD_LEFT),
                    'fecha_factura'          => $fechaFact->toDateString(),
                    'fecha_vencimiento_pago' => $fechaFact->copy()->addDays(30)->toDateString(),
                    'concepto'               => "Cuota {$c} de {$numCuotas} – Instrucción aeronáutica RAC 141",
                    'subtotal'               => round($valorCuota / 1.19, 2),
                    'iva'                    => round($valorCuota - ($valorCuota / 1.19), 2),
                    'total'                  => $valorCuota,
                    'estado'                 => $estado,
                    'cufe'                   => strtoupper(md5($factNum . $mat->id . $c . now())),
                    'archivo_url'            => null,
                    'created_at'             => $fechaFact,
                    'updated_at'             => now(),
                ]);

                if ($estado === 'pagada') {
                    $adminId = DB::table('usuarios')
                        ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
                        ->whereIn('roles.nombre', ['admin', 'dir_ops'])
                        ->value('usuarios.id');

                    DB::table('pagos')->insert([
                        'factura_id'     => $factId,
                        'fecha_pago'     => $fechaFact->copy()->addDays(rand(1, 15))->toDateString(),
                        'valor'          => $valorCuota,
                        'metodo'         => ['transferencia','efectivo','tarjeta','cheque'][rand(0, 3)],
                        'referencia'     => 'REF-' . strtoupper(substr(md5(rand()), 0, 8)),
                        'registrado_por' => $adminId ?? DB::table('usuarios')->value('id'),
                        'comprobante_url'=> null,
                        'observaciones'  => null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
                $totalCreadas++;
            }
        }
        $this->command->info("    ✓ {$totalCreadas} facturas y pagos creados.");
    }

    // ══════════════════════════════════════════════════════════════
    // MÓDULO: CERTIFICACIONES INSTRUCTORES
    // ══════════════════════════════════════════════════════════════
    private function seedCertInstructores(): void
    {
        $this->command->info('  → Certificaciones de instructores...');

        $instructores = DB::table('instructores')->pluck('id');
        if ($instructores->isEmpty()) return;

        // [tipo_enum, descripcion, numero_prefix, venc_meses]
        $certs = [
            ['licencia',            'Licencia CPL – Piloto Comercial de Avión',                 'COL-CPL-',  36],
            ['habilitacion',        'Habilitación para Vuelo Instrumental CFII',                'COL-CFII-', 24],
            ['habilitacion',        'Habilitación Multi-Motor MEI',                             'COL-MEI-',  24],
            ['cheque_competencia',  'Cheque de competencia anual – Cessna 172',                 'CHQ-C172-', 12],
            ['medico',              'Certificado médico clase 2 – UAEAC',                       'MED-CL2-',  24],
        ];

        $totalCreados = 0;
        foreach ($instructores as $insId) {
            foreach ($certs as $cert) {
                $existe = DB::table('cert_instructores')
                    ->where('instructor_id', $insId)
                    ->where('tipo', $cert[0])
                    ->where('descripcion', $cert[1])
                    ->exists();
                if ($existe) continue;

                $emision = Carbon::now()->subMonths(rand(3, 18));
                DB::table('cert_instructores')->insert([
                    'instructor_id'    => $insId,
                    'tipo'             => $cert[0],
                    'descripcion'      => $cert[1],
                    'numero'           => $cert[2] . rand(10000, 99999),
                    'fecha_emision'    => $emision->toDateString(),
                    'fecha_vencimiento'=> $emision->copy()->addMonths($cert[3])->toDateString(),
                    'archivo_url'      => null,
                    'activo'           => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $totalCreados++;
            }
        }
        $this->command->info("    ✓ {$totalCreados} certificaciones de instructores creadas.");
    }

    // ══════════════════════════════════════════════════════════════
    // SINCRONIZAR VENCIMIENTOS CRÍTICOS
    // ══════════════════════════════════════════════════════════════
    private function sincronizarVencimientos(): void
    {
        $this->command->info('  → Sincronizando alertas de vencimiento...');
        try {
            $resultado = app(\App\Services\VencimientoService::class)->sincronizar();
            $total = DB::table('vencimientos_criticos')->count();
            $this->command->info(
                "    ✓ Vencimientos: {$resultado['creados']} nuevos, {$resultado['actualizados']} actualizados. Total: {$total}"
            );
        } catch (\Exception $e) {
            $this->command->error('    ✗ Error sincronizando: ' . $e->getMessage());
        }
    }
}
