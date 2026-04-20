<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MisionVuelo;
use Illuminate\Http\Request;

class MisionVueloController extends Controller
{
    public function index(Request $request)
    {
        $query = MisionVuelo::with(['estudiante.persona', 'instructor.persona']);
        
        if ($request->has('estudiante_id') && $request->estudiante_id) {
            $query->where('estudiante_id', $request->estudiante_id);
        }

        $vuelos = $query->orderBy('fecha', 'desc')->get();
        return response()->json(['data' => $vuelos]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'fecha' => 'required|date',
            'matricula' => 'required|string',
            'tipo_vuelo' => 'required|in:dual,solo,ftd,chequeo',
            'horas' => 'required|numeric',
            'despegues' => 'required|integer',
            'aterrizajes' => 'required|integer',
            'calificacion' => 'required|in:S,NI,D,NA',
            'observaciones' => 'nullable|string'
        ]);

        // Asignar instructor si no se envío uno
        if (!$request->instructor_id && auth()->check()) {
            $instruct = \App\Models\Instructor::where('user_id', auth()->id())->first();
            if ($instruct) $validated['instructor_id'] = $instruct->id;
        }

        $vuelo = MisionVuelo::create($validated);
        return response()->json(['message' => 'Misión de vuelo registrada con éxito', 'data' => $vuelo]);
    }

    public function destroy($id)
    {
        $vuelo = MisionVuelo::findOrFail($id);
        $vuelo->delete();
        return response()->json(['message' => 'Registro de vuelo eliminado']);
    }
}
