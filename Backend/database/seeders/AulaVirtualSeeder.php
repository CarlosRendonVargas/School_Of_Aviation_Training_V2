<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use App\Models\LeccionMateria;
use App\Models\BancoPregunta;
use Illuminate\Support\Facades\DB;

class AulaVirtualSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 Poblando Aula Virtual (Lecciones y Quices)...');

        $materias = Materia::take(3)->get();

        foreach ($materias as $materia) {
            // Crear 3 lecciones por materia
            for ($i = 1; $i <= 3; $i++) {
                $leccion = LeccionMateria::create([
                    'materia_id' => $materia->id,
                    'titulo' => "Lección {$i}: Conceptos Fundamentales de " . $materia->nombre,
                    'descripcion' => "En esta lección exploraremos los fundamentos de " . $materia->nombre . " aplicados a la aviación civil RAC 141.",
                    'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Link de ejemplo
                    'documento_url' => 'https://www.aerocivil.gov.co/normatividad/RAC/RAC%20141.pdf',
                    'orden' => $i,
                    'activo' => true,
                    'max_intentos' => 3
                ]);

                // Asignar algunas preguntas existentes a esta lección
                BancoPregunta::where('materia_id', $materia->id)
                    ->whereNull('leccion_id')
                    ->inRandomOrder()
                    ->take(3)
                    ->update(['leccion_id' => $leccion->id]);
            }

            // Actualizar datos generales de la materia para el LMS
            $materia->update([
                'link_meet' => 'https://meet.google.com/abc-defg-hij',
                'temario' => "1. Fundamentos\n2. Normatividad\n3. Procedimientos Operativos\n4. Examen Final",
                'max_intentos' => 3,
                'costo_reintento' => 50000,
                'duracion_minutos' => 120
            ]);
        }

        $this->command->info('✅ Aula Virtual poblada con éxito.');
    }
}
