<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BriefingDebriefing;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BriefingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = BriefingDebriefing::with('instructor.persona')
            ->orderBy('fecha_hora');

        if ($request->filled('reserva_id')) {
            $query->where('reserva_id', $request->reserva_id);
        }

        $briefings = $query->get()->map(fn($b) => $this->conAlias($b));

        return response()->json(['ok' => true, 'data' => $briefings]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reserva_id'       => 'required|exists:reservas,id',
            'pos_vuelo'        => 'required|in:pre_vuelo,post_vuelo',
            'contenido'        => 'required|string|max:3000',
            'areas_debiles'    => 'nullable|string|max:1000',
        ]);

        $user       = $request->user();
        $instructor = $user->persona?->instructor;

        $briefing = BriefingDebriefing::create([
            'reserva_id'       => $data['reserva_id'],
            'instructor_id'    => $instructor?->id,
            'tipo'             => $data['pos_vuelo'],
            'fecha_hora'       => now(),
            'contenido'        => $data['contenido'],
            'areas_debiles'    => $data['areas_debiles'] ?? null,
            'firma_instructor' => $user->email,
        ]);

        return response()->json(['ok' => true, 'data' => $this->conAlias($briefing)], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $briefing = BriefingDebriefing::findOrFail($id);

        $data = $request->validate([
            'contenido'     => 'sometimes|string|max:3000',
            'areas_debiles' => 'nullable|string|max:1000',
        ]);

        $briefing->update($data);

        return response()->json(['ok' => true, 'data' => $this->conAlias($briefing->fresh())]);
    }

    private function conAlias(BriefingDebriefing $b): BriefingDebriefing
    {
        $b->pos_vuelo = $b->tipo;
        return $b;
    }
}
