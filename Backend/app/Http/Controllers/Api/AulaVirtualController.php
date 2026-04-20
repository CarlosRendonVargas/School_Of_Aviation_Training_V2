<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\NotaAcademica;
use App\Models\NotaLeccion;
use App\Models\BancoPregunta;
use App\Models\LeccionMateria;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AulaVirtualController extends Controller
{
    /**
     * Lista las materias del programa del estudiante logueado con su estatus de progreso.
     */
    public function misMaterias(Request $request): JsonResponse
    {
        $usuario = $request->user();
        if (!$usuario) {
            return response()->json(['ok' => false, 'mensaje' => 'No autenticado'], 401);
        }

        $persona = $usuario->persona;
        if (!$persona) {
            return response()->json(['ok' => false, 'mensaje' => 'El usuario no tiene un perfil personal vinculado'], 403);
        }

        $estudiante = $persona->estudiante;
        if (!$estudiante) {
            return response()->json(['ok' => false, 'mensaje' => 'No es un perfil de estudiante'], 403);
        }

        $materias = Materia::whereHas('etapa', function($q) use ($estudiante) {
            $q->where('programa_id', $estudiante->programa_id);
        })
        ->with(['etapa', 'notas' => function($q) use ($estudiante) {
            $q->where('estudiante_id', $estudiante->id);
        }])
        ->get()
        ->map(function($m) {
            $mejorNota = $m->notas->max('nota');
            $aprobado = $m->notas->where('aprobado', true)->count() > 0;
            return [
                'id' => $m->id,
                'codigo' => $m->codigo,
                'nombre' => $m->nombre,
                'horas' => $m->horas,
                'etapa' => $m->etapa->nombre,
                'nota_max' => $mejorNota,
                'aprobado' => $aprobado,
                'intentos' => $m->notas->count(),
                'link_meet' => $m->link_meet,
                'has_docs' => !empty($m->documento_url)
            ];
        });

        return response()->json(['ok' => true, 'data' => $materias]);
    }

    /**
     * Detalle de una materia para el aula virtual.
     */
    public function detalleMateria($id, Request $request): JsonResponse
    {
        $estudiante = $request->user()->persona?->estudiante;
        
        $materia = Materia::with(['lecciones' => function($q) use ($estudiante) {
            $q->where('activo', true)
              ->orderBy('orden')
              ->withCount('preguntas');
        }])->findOrFail($id);

        // Adjuntar notas de lecciones y calcular promedio parcial
        $promedioLecciones = 0;
        if ($estudiante) {
            foreach($materia->lecciones as $lec) {
                $lec->nota_estudiante = NotaLeccion::where('estudiante_id', $estudiante->id)
                    ->where('leccion_id', $lec->id)
                    ->orderBy('nota', 'desc')
                    ->first();
            }

            // Cálculo del promedio de lecciones (Mejor nota por lección)
            $mejorNotaPorLeccion = DB::table('notas_lecciones')
                ->join('lecciones_materia', 'notas_lecciones.leccion_id', '=', 'lecciones_materia.id')
                ->where('lecciones_materia.materia_id', $id)
                ->where('notas_lecciones.estudiante_id', $estudiante->id)
                ->select('notas_lecciones.leccion_id', DB::raw('MAX(notas_lecciones.nota) as nota_max'))
                ->groupBy('notas_lecciones.leccion_id')
                ->get();

            if ($mejorNotaPorLeccion->count() > 0) {
                $promedioLecciones = $mejorNotaPorLeccion->avg('nota_max');
            }
        }

        $res = $materia->toArray();
        $res['promedio_lecciones'] = round($promedioLecciones, 2);

        return response()->json(['ok' => true, 'data' => $res]);
    }

    /* ─── Quices de Lecciones ─── */

    public function generarQuizLeccion($id, Request $request): JsonResponse
    {
        $leccion = LeccionMateria::findOrFail($id);
        $estudiante = $request->user()->persona?->estudiante;
        
        if ($estudiante) {
            $notaPrevia = NotaLeccion::where('estudiante_id', $estudiante->id)
                ->where('leccion_id', $id)
                ->first();
            
            if ($notaPrevia && $notaPrevia->intentos >= ($leccion->max_intentos ?? 3)) {
                return response()->json([
                    'ok' => false, 
                    'mensaje' => 'Has alcanzado el límite de ' . ($leccion->max_intentos ?? 3) . ' intentos para esta lección.'
                ], 403);
            }
        }

        $preguntas = BancoPregunta::where('leccion_id', $id)
            ->where('activo', true)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        if ($preguntas->isEmpty()) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay preguntas para esta lección'], 404);
        }

        return response()->json(['ok' => true, 'data' => $preguntas]);
    }

    public function calificarQuizLeccion(Request $request, $id): JsonResponse
    {
        $request->validate(['respuestas' => 'required|array']);
        $leccion = LeccionMateria::findOrFail($id);
        $estudiante = $request->user()->persona?->estudiante;

        if (!$estudiante) return response()->json(['ok' => false], 403);

        $total = count($request->respuestas);
        $aciertos = 0;

        foreach ($request->respuestas as $pregId => $respU) {
            $pregunta = BancoPregunta::find($pregId);
            if ($pregunta && $pregunta->respuesta_correcta == $respU) {
                $aciertos++;
            }
        }

        $notaFinal = ($aciertos / $total) * 100;

        // Buscar si ya tiene un registro
        $notaInstancia = NotaLeccion::where('estudiante_id', $estudiante->id)
            ->where('leccion_id', $id)
            ->first();

        if ($notaInstancia) {
            $notaInstancia->intentos += 1;
            // Solo actualizamos la nota si la nueva es MAYOR
            if ($notaFinal > $notaInstancia->nota) {
                $notaInstancia->nota = $notaFinal;
                $notaInstancia->aciertos = $aciertos;
                $notaInstancia->total = $total;
            }
            $notaInstancia->save();
        } else {
            $notaInstancia = NotaLeccion::create([
                'estudiante_id' => $estudiante->id,
                'leccion_id' => $id,
                'nota' => $notaFinal,
                'aciertos' => $aciertos,
                'total' => $total,
                'intentos' => 1
            ]);
        }

        return response()->json(['ok' => true, 'resultado' => $notaInstancia]);
    }

    /**
     * Genera un examen aleatorio de 10 preguntas con validación de intentos y pagos.
     */
    public function generarExamen($id, Request $request): JsonResponse
    {
        $estudiante = $request->user()->persona?->estudiante;
        if (!$estudiante) return response()->json(['ok' => false, 'mensaje' => 'Perfil no encontrado'], 403);

        $materia = Materia::findOrFail($id);

        $yaAprobo = NotaAcademica::where('estudiante_id', $estudiante->id)
            ->where('materia_id', $id)
            ->where('aprobado', true)
            ->exists();
        
        if ($yaAprobo) {
            return response()->json(['ok' => false, 'mensaje' => 'Ya has aprobado esta materia.'], 403);
        }

        $intentos = NotaAcademica::where('estudiante_id', $estudiante->id)
            ->where('materia_id', $id)
            ->count();

        if ($intentos >= 1) {
            if ($materia->costo_reintento > 0) {
                // Verificar si tiene un pago pendiente/aprobado para el reintento (Lógica simplificada por ahora)
                // return response()->json(['ok' => false, 'requiere_pago' => true ...]);
            }
        }

        if ($intentos >= ($materia->max_intentos ?? 3)) {
            return response()->json(['ok' => false, 'mensaje' => 'Límite de intentos alcanzado.'], 403);
        }

        // SEPARAR CUIDADOSAMENTE: Solo preguntas generales (sin leccion_id)
        $preguntas = BancoPregunta::where('materia_id', $id)
            ->where(function($q) {
                $q->whereNull('leccion_id')->orWhere('leccion_id', 0);
            })
            ->where('activo', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        if ($preguntas->isEmpty()) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay preguntas generales disponibles para el examen final.'], 404);
        }

        return response()->json(['ok' => true, 'data' => $preguntas]);
    }

    /**
     * Procesa las respuestas del examen y guarda la nota.
     */
    public function calificarExamen(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'respuestas' => 'required|array', 
            ]);

            $materia = Materia::findOrFail($id);
            $estudiante = $request->user()->persona?->estudiante;

            if (!$estudiante) {
                 return response()->json(['ok' => false, 'mensaje' => 'No se encontró perfil de estudiante vinculado'], 403);
            }

            $totalPreguntas = count($request->respuestas);
            $aciertos = 0;

            if ($totalPreguntas > 0) {
                foreach ($request->respuestas as $pregId => $respU) {
                    $pregunta = BancoPregunta::find($pregId);
                    if ($pregunta && $pregunta->respuesta_correcta == $respU) {
                        $aciertos++;
                    }
                }
                $notaExamen = ($aciertos / $totalPreguntas) * 100;
            } else {
                $notaExamen = 0;
                $totalPreguntas = 1;
            }
            
            $mejorNotaPorLeccion = DB::table('notas_lecciones')
                ->join('lecciones_materia', 'notas_lecciones.leccion_id', '=', 'lecciones_materia.id')
                ->where('lecciones_materia.materia_id', $id)
                ->where('notas_lecciones.estudiante_id', $estudiante->id)
                ->select('notas_lecciones.leccion_id', DB::raw('MAX(notas_lecciones.nota) as nota_max'))
                ->groupBy('notas_lecciones.leccion_id')
                ->get();

            $promedioLecciones = $mejorNotaPorLeccion->count() > 0 ? floatval($mejorNotaPorLeccion->avg('nota_max')) : 0;

            // Lógica inteligente: Si no hay lecciones creadas, el examen vale el 100%
            $totalLeccionesMateria = LeccionMateria::where('materia_id', $id)->count();
            
            if ($totalLeccionesMateria > 0) {
                $notaFinalFinal = round(($promedioLecciones * 0.4) + ($notaExamen * 0.6), 2);
                $obs = "Calculado RAC 141 (Materia con lecciones): 40% Quices (".round($promedioLecciones,1)."%) y 60% Examen (".round($notaExamen,1)."%)";
            } else {
                $notaFinalFinal = round($notaExamen, 2);
                $obs = "Nota 100% examen final (Materia sin lecciones configuradas)";
            }
            
            $aprobado = $notaFinalFinal >= ($materia->nota_minima ?? 75);

            $intentoNum = NotaAcademica::where('estudiante_id', $estudiante->id)
                ->where('materia_id', $id)
                ->count() + 1;

            $notaInstancia = NotaAcademica::create([
                'estudiante_id' => $estudiante->id,
                'materia_id' => $id,
                'nota' => $notaFinalFinal,
                'aprobado' => $aprobado,
                'intento_num' => $intentoNum,
                'fecha_evaluacion' => now()->toDateString(),
                'observaciones' => $obs
            ]);

            return response()->json([
                'ok' => true,
                'resultado' => [
                    'nota' => $notaFinalFinal,
                    'aprobado' => $aprobado,
                    'aciertos' => $aciertos,
                    'total' => count($request->respuestas)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'Error en el servidor: ' . $e->getMessage(),
                'linea' => $e->getLine()
            ], 500);
        }
    }

    public function todasLasNotas(Request $request)
    {
        $notas = \App\Models\NotaAcademica::with(['estudiante.persona', 'materia'])
            ->when($request->estudiante_id, fn($q, $v) => $q->where('estudiante_id', $v))
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['data' => $notas]);
    }
}
