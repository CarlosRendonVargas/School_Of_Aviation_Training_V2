<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\NotaAcademica;
use App\Models\BancoPregunta;
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

        // Obtener el registro de estudiante a partir de la persona vinculada al usuario
        $persona = $usuario->persona;
        if (!$persona) {
            return response()->json(['ok' => false, 'mensaje' => 'El usuario no tiene un perfil personal vinculado'], 403);
        }

        $estudiante = $persona->estudiante;
        if (!$estudiante) {
            return response()->json(['ok' => false, 'mensaje' => 'No es un perfil de estudiante'], 403);
        }

        // Traer materias de su programa organizado por etapas
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
        $materia = Materia::findOrFail($id);
        return response()->json(['ok' => true, 'data' => $materia]);
    }

    /**
     * Genera un examen aleatorio de 10 preguntas con validación de intentos y pagos.
     */
    public function generarExamen($id, Request $request): JsonResponse
    {
        $estudiante = $request->user()->persona?->estudiante;
        if (!$estudiante) return response()->json(['ok' => false, 'mensaje' => 'Perfil no encontrado'], 403);

        $materia = Materia::findOrFail($id);

        // 1. Verificar si ya aprobó
        $yaAprobo = NotaAcademica::where('estudiante_id', $estudiante->id)
            ->where('materia_id', $id)
            ->where('aprobado', true)
            ->exists();
        
        if ($yaAprobo) {
            return response()->json(['ok' => false, 'mensaje' => 'Ya has aprobado esta materia.'], 403);
        }

        // 2. Contar intentos realizados
        $intentos = NotaAcademica::where('estudiante_id', $estudiante->id)
            ->where('materia_id', $id)
            ->count();

        // 3. Regla: El primer intento es libre. Posteriores requieren "Pago de Habilitación" (marcado por admin)
        if ($intentos >= 1) {
            // Verificar si hay un registro que habilite el siguiente intento (pagado)
            // Lógica: Buscamos si existe algúna nota marcada como pagada pero que no tenga examen asociado? 
            // O mejor: simplemente validamos si el último intento fallido ha sido "habilitado" para repetición.
            // Para fines de esta demo: Si tiene 1 intento fallido y el costo es > 0, bloqueamos pidiendo pago.
            if ($materia->costo_reintento > 0) {
                return response()->json([
                    'ok' => false, 
                    'requiere_pago' => true,
                    'monto' => $materia->costo_reintento,
                    'mensaje' => "Has fallado tu primer intento. Para presentar el supletorio debes cancelar $" . number_format($materia->costo_reintento, 0) . " en tesorería."
                ], 402);
            }
        }

        if ($intentos >= ($materia->max_intentos ?? 3)) {
            return response()->json(['ok' => false, 'mensaje' => 'Límite de intentos alcanzado.'], 403);
        }

        $preguntas = BancoPregunta::where('materia_id', $id)
            ->where('activo', true)
            ->inRandomOrder()
            ->limit(10)
            ->get();

        if ($preguntas->isEmpty()) {
            return response()->json(['ok' => false, 'mensaje' => 'No hay preguntas cargadas para esta materia.'], 404);
        }

        return response()->json(['ok' => true, 'data' => $preguntas]);
    }

    /**
     * Procesa las respuestas del examen y guarda la nota.
     */
    public function calificarExamen(Request $request, $id): JsonResponse
    {
        $request->validate([
            'respuestas' => 'required|array', // [pregunta_id => respuesta_marcada]
        ]);

        $materia = Materia::findOrFail($id);
        $estudiante = $request->user()->persona?->estudiante;

        if (!$estudiante) {
             return response()->json(['ok' => false, 'mensaje' => 'No se encontró perfil de estudiante vinculado'], 403);
        }

        $totalPreguntas = count($request->respuestas);
        $aciertos = 0;

        foreach ($request->respuestas as $pregId => $respU) {
            $pregunta = BancoPregunta::find($pregId);
            if ($pregunta && $pregunta->respuesta_correcta == $respU) {
                $aciertos++;
            }
        }

        $notaFinal = ($aciertos / $totalPreguntas) * 100;
        $aprobado = $notaFinal >= ($materia->nota_minima ?? 75);

        // Guardar resultado
        $intentoNum = NotaAcademica::where('estudiante_id', $estudiante->id)
            ->where('materia_id', $id)
            ->count() + 1;

        $nota = NotaAcademica::create([
            'estudiante_id' => $estudiante->id,
            'materia_id' => $id,
            'nota' => $notaFinal,
            'aprobado' => $aprobado,
            'intento_num' => $intentoNum,
            'fecha_evaluacion' => now(),
            'observaciones' => 'Examen presentado en Aula Virtual'
        ]);

        return response()->json([
            'ok' => true,
            'resultado' => [
                'nota' => $notaFinal,
                'aprobado' => $aprobado,
                'aciertos' => $aciertos,
                'total' => $totalPreguntas
            ]
        ]);
    }
}
