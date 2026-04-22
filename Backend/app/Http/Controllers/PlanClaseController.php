<?php

namespace App\Http\Controllers;

use App\Models\PlanClase;
use Illuminate\Http\Request;

class PlanClaseController extends Controller
{
    public function index()
    {
        $planes = PlanClase::with(['instructor.persona', 'materia'])->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $planes]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'instructor_id' => 'required|exists:instructores,id',
            'materia_id' => 'required|exists:materias,id',
            'fecha' => 'required|date',
            'duracion_min' => 'required|integer',
            'objetivos' => 'nullable|string',
            'contenido' => 'nullable|string',
            'recursos' => 'nullable|string',
            'estado' => 'nullable|string|in:planificada,realizada,cancelada',
        ]);

        $plan = PlanClase::create($validated);
        return response()->json(['data' => $plan, 'mensaje' => 'Plan de clase creado exitosamente'], 201);
    }

    public function update(Request $request, $id)
    {
        $plan = PlanClase::findOrFail($id);
        $validated = $request->validate([
            'instructor_id' => 'sometimes|exists:instructores,id',
            'materia_id' => 'sometimes|exists:materias,id',
            'fecha' => 'sometimes|date',
            'duracion_min' => 'sometimes|integer',
            'objetivos' => 'nullable|string',
            'contenido' => 'nullable|string',
            'recursos' => 'nullable|string',
            'estado' => 'nullable|string|in:planificada,realizada,cancelada',
        ]);

        $plan->update($validated);
        return response()->json(['data' => $plan, 'mensaje' => 'Plan de clase actualizado']);
    }

    public function destroy($id)
    {
        PlanClase::findOrFail($id)->delete();
        return response()->json(['mensaje' => 'Plan de clase eliminado'], 204);
    }
}
