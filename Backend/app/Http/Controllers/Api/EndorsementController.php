<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Endorsement;
use App\Models\Estudiante;
use Illuminate\Http\Request;

class EndorsementController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Endorsement::with(['estudiante.persona', 'instructor.persona'])
            ->orderByDesc('fecha');

        if ($user->rol?->nombre === 'estudiante') {
            $est = Estudiante::where('usuario_id', $user->id)->first();
            $query->where('estudiante_id', $est?->id);
        }

        if ($user->rol?->nombre === 'instructor') {
            $inst = \App\Models\Instructor::where('usuario_id', $user->id)->first();
            $query->where('instructor_id', $inst?->id);
        }

        if ($request->filled('estudiante_id')) {
            $query->where('estudiante_id', $request->estudiante_id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $rol = $request->user()->rol?->nombre;
        if (!in_array($rol, ['admin', 'dir_ops', 'instructor'])) {
            abort(403, 'Solo instructores y administradores pueden emitir endorsements.');
        }

        $data = $request->validate([
            'estudiante_id'    => 'required|exists:estudiantes,id',
            'instructor_id'    => 'required|exists:instructores,id',
            'tipo'             => 'required|string',
            'fecha'            => 'required|date',
            'aeronave_matricula' => 'required|string|max:10',
            'aeropuerto_icao'  => 'nullable|string|max:4',
            'observaciones'    => 'nullable|string|max:500',
            'firma_instructor' => 'nullable|string',
        ]);

        $end = Endorsement::create($data);

        return response()->json([
            'data'    => $end->load(['estudiante.persona', 'instructor.persona']),
            'mensaje' => 'Endorsement registrado correctamente.',
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'data' => Endorsement::with(['estudiante.persona', 'instructor.persona'])->findOrFail($id),
        ]);
    }
}
