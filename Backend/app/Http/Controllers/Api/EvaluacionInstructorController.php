<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvaluacionInstructor;
use Illuminate\Http\Request;

class EvaluacionInstructorController extends Controller
{
    public function index(Request $request)
    {
        $query = EvaluacionInstructor::with(['instructor.persona', 'evaluador.persona'])
            ->orderByDesc('fecha');

        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $rol = $request->user()->rol?->nombre;
        if (!in_array($rol, ['admin', 'dir_ops'])) {
            abort(403, 'Solo administrador o director de operaciones puede registrar evaluaciones.');
        }

        $data = $request->validate([
            'instructor_id'      => 'required|exists:instructores,id',
            'tipo'               => 'required|string',
            'fecha'              => 'required|date',
            'puntaje'            => 'required|numeric|min:0|max:100',
            'resultado'          => 'required|string',
            'areas_evaluadas'    => 'nullable|array',
            'observaciones'      => 'nullable|string',
            'acciones_requeridas'=> 'nullable|string',
            'proxima_evaluacion' => 'nullable|date',
        ]);

        $data['evaluado_por'] = $request->user()->id;
        $ev = EvaluacionInstructor::create($data);

        return response()->json([
            'data'    => $ev->load(['instructor.persona', 'evaluador.persona']),
            'mensaje' => 'Evaluación registrada.',
        ], 201);
    }

    public function historialInstructor(int $instructorId)
    {
        $evals = EvaluacionInstructor::with('evaluador.persona')
            ->where('instructor_id', $instructorId)
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['data' => $evals]);
    }
}
