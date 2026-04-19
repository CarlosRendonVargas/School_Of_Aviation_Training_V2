<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\BancoPregunta;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MateriaController extends Controller
{
    /**
     * Actualiza los campos de Aula Virtual de una materia.
     */
    public function updateLms(Request $request, $id): JsonResponse
    {
        $materia = Materia::findOrFail($id);
        
        $data = $request->validate([
            'link_meet' => 'nullable|string',
            'documento_url' => 'nullable|string',
            'temario' => 'nullable|string',
            'max_intentos' => 'nullable|integer',
            'costo_reintento' => 'nullable|numeric',
            'duracion_minutos' => 'nullable|integer',
        ]);

        $materia->update($data);

        return response()->json(['ok' => true, 'data' => $materia]);
    }

    /**
     * Lista todas las preguntas asociadas a una materia.
     */
    public function preguntas($materiaId): JsonResponse
    {
        $preguntas = BancoPregunta::where('materia_id', $materiaId)->get();
        return response()->json(['ok' => true, 'data' => $preguntas]);
    }

    /**
     * Guarda una nueva pregunta en el banco.
     */
    public function storePregunta(Request $request, $materiaId): JsonResponse
    {
        $materia = Materia::findOrFail($materiaId);
        
        $data = $request->validate([
            'pregunta' => 'required|string',
            'opciones' => 'required|array|min:2',
            'respuesta_correcta' => 'required|string',
            'nivel_dificultad' => 'nullable|integer',
            'rac_referencia' => 'nullable|string'
        ]);

        $data['materia_id'] = $materiaId;
        $data['activo'] = true;
        
        $pregunta = BancoPregunta::create($data);

        return response()->json(['ok' => true, 'data' => $pregunta]);
    }

    /**
     * Actualiza una pregunta existente.
     */
    public function updatePregunta(Request $request, $materiaId, $id): JsonResponse
    {
        $pregunta = BancoPregunta::where('materia_id', $materiaId)->findOrFail($id);
        
        $data = $request->validate([
            'pregunta' => 'required|string',
            'opciones' => 'required|array|min:2',
            'respuesta_correcta' => 'required|string',
            'nivel_dificultad' => 'nullable|integer',
            'rac_referencia' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $pregunta->update($data);

        return response()->json(['ok' => true, 'data' => $pregunta]);
    }

    /**
     * Elimina una pregunta.
     */
    public function destroyPregunta($materiaId, $id): JsonResponse
    {
        $pregunta = BancoPregunta::where('materia_id', $materiaId)->findOrFail($id);
        $pregunta->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Pregunta eliminada']);
    }
}
