<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Persona;
use App\Models\Estudiante;
use App\Models\Instructor;
use App\Models\Aeronave;
use App\Models\BitacoraVuelo;
use App\Models\Reserva;
use App\Models\ReporteSms;
use App\Models\NotaAcademica;
use App\Models\Materia;
use App\Models\Programa;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $aeronaves = Aeronave::all();
        $instructores = Instructor::all();
        $estudiantes = Estudiante::all();
        $materias = Materia::all();
        $usuarios = Usuario::all();

        if ($estudiantes->isEmpty() || $instructores->isEmpty()) {
            $this->command->error('No hay estudiantes o instructores suficientes.');
            return;
        }

        // 3. Crear Reservas
        for ($i = 0; $i < 20; $i++) {
            $est = $estudiantes->random();
            $ins = $instructores->random();
            $aero = $aeronaves->random();
            $fecha = Carbon::now()->addDays(rand(-10, 10));

            Reserva::create([
                'estudiante_id' => $est->id,
                'instructor_id' => $ins->id,
                'aeronave_id' => $aero->id,
                'tipo' => $faker->randomElement(['instruccion', 'solo', 'simulador']),
                'fecha' => $fecha->toDateString(),
                'hora_inicio' => '08:00:00',
                'hora_fin' => '10:00:00',
                'estado' => $fecha->isPast() ? 'completada' : 'confirmada',
            ]);
        }

        // 4. Crear Bitácoras
        for ($i = 0; $i < 20; $i++) {
            $est = $estudiantes->random();
            $ins = $instructores->random();
            $aero = $aeronaves->random();
            $fecha = Carbon::now()->subDays(rand(1, 40));

            BitacoraVuelo::create([
                'estudiante_id' => $est->id,
                'instructor_id' => $ins->id,
                'aeronave_id' => $aero->id,
                'fecha' => $fecha->toDateString(),
                'hora_salida' => '08:00:00',
                'hora_llegada' => '09:30:00',
                'origen_icao' => 'SKBO',
                'destino_icao' => 'SKMD',
                'horas_totales' => rand(10, 25) / 10,
                'tipo_vuelo' => $faker->randomElement(['local', 'navegacion', 'ifr']),
                'observaciones' => $faker->sentence,
                'firma_instructor' => 'signed',
                'firma_estudiante' => 'signed',
            ]);
        }

        // 5. Crear Reportes SMS
        for ($i = 0; $i < 10; $i++) {
            $sev = rand(1, 3);
            $prob = rand(1, 3);
            ReporteSms::create([
                'reportante_id' => $usuarios->random()->id,
                'anonimo' => false,
                'tipo' => $faker->randomElement(['peligro', 'incidente', 'near_miss']),
                'descripcion' => $faker->paragraph,
                'fecha_evento' => Carbon::now()->subDays(rand(1, 15))->toDateTimeString(),
                'lugar' => $faker->randomElement(['Rampa', 'Hangar']),
                'severidad' => $sev,
                'probabilidad' => $prob,
                'nivel_riesgo' => $sev * $prob,
                'estado' => 'cerrado',
            ]);
        }

        // 6. Crear Notas Académicas
        for ($i = 0; $i < 20; $i++) {
            $est = $estudiantes->random();
            $materia = $materias->random();
            $nota = rand(300, 500) / 100;

            NotaAcademica::create([
                'estudiante_id' => $est->id,
                'materia_id' => $materia->id,
                'instructor_id' => $instructores->random()->id,
                'nota' => $nota,
                'aprobado' => $nota >= 3.0,
                'intento_num' => 1,
                'fecha_evaluacion' => Carbon::now()->subMonths(rand(1, 3))->toDateString(),
            ]);
        }
    }
}
