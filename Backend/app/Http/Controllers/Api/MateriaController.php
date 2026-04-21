<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\BancoPregunta;
use App\Models\LeccionMateria;
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
            'video_url' => 'nullable|string',
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
        $preguntas = BancoPregunta::where('materia_id', $materiaId)->get()->makeVisible('respuesta_correcta');
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
            'leccion_id' => 'nullable|integer',
            'opciones' => 'required|array|min:2',
            'respuesta_correcta' => 'required|string',
            'nivel_dificultad' => 'nullable|integer',
            'rac_referencia' => 'nullable|string'
        ]);

        $data['materia_id'] = $materiaId;
        $data['activo'] = true;
        
        $pregunta = BancoPregunta::create($data)->makeVisible('respuesta_correcta');

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
            'leccion_id' => 'nullable|integer',
            'opciones' => 'required|array|min:2',
            'respuesta_correcta' => 'required|string',
            'nivel_dificultad' => 'nullable|integer',
            'rac_referencia' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $pregunta->update($data);
        $pregunta->makeVisible('respuesta_correcta');

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

    /* ─── Lecciones ─── */

    public function lecciones($materiaId): JsonResponse
    {
        $lecciones = LeccionMateria::where('materia_id', $materiaId)->orderBy('orden')->get();
        return response()->json(['ok' => true, 'data' => $lecciones]);
    }

    public function storeLeccion(Request $request, $materiaId): JsonResponse
    {
        $data = $request->validate([
            'titulo' => 'required|string',
            'descripcion' => 'nullable|string',
            'video_url' => 'nullable|string',
            'documento_url' => 'nullable|string',
            'orden' => 'nullable|integer',
            'max_intentos' => 'nullable|integer'
        ]);
        $data['materia_id'] = $materiaId;
        $leccion = LeccionMateria::create($data);
        return response()->json(['ok' => true, 'data' => $leccion]);
    }

    public function updateLeccion(Request $request, $materiaId, $id): JsonResponse
    {
        $leccion = LeccionMateria::where('materia_id', $materiaId)->findOrFail($id);
        $data = $request->validate([
            'titulo' => 'sometimes|string',
            'descripcion' => 'nullable|string',
            'video_url' => 'nullable|string',
            'documento_url' => 'nullable|string',
            'orden' => 'sometimes|integer',
            'activo' => 'sometimes|boolean',
            'max_intentos' => 'nullable|integer'
        ]);
        $leccion->update($data);
        return response()->json(['ok' => true, 'data' => $leccion]);
    }

    public function destroyLeccion($materiaId, $id): JsonResponse
    {
        $leccion = LeccionMateria::where('materia_id', $materiaId)->findOrFail($id);
        $leccion->delete();
        return response()->json(['ok' => true, 'mensaje' => 'Lección eliminada']);
    }

    /* ─── CRUD Básico de Materias ─── */

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'etapa_id' => 'required|exists:etapas,id',
            'codigo' => 'required|string',
            'nombre' => 'required|string',
            'horas' => 'required|numeric',
            'tipo' => 'nullable|string',
            'nota_minima' => 'nullable|numeric',
            'duracion_minutos' => 'nullable|integer'
        ]);

        $materia = Materia::create($data);
        return response()->json(['ok' => true, 'data' => $materia], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $materia = Materia::findOrFail($id);
        $data = $request->validate([
            'etapa_id' => 'sometimes|exists:etapas,id',
            'codigo' => 'sometimes|string',
            'nombre' => 'sometimes|string',
            'horas' => 'sometimes|numeric',
            'tipo' => 'nullable|string',
            'nota_minima' => 'nullable|numeric',
            'duracion_minutos' => 'nullable|integer'
        ]);

        $materia->update($data);
        return response()->json(['ok' => true, 'data' => $materia]);
    }

    public function destroy($id): JsonResponse
    {
        $materia = Materia::findOrFail($id);
        $materia->delete();
        return response()->json(['ok' => true, 'mensaje' => 'Materia eliminada']);
    }
}
