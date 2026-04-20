<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgramaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $programas = Programa::with('etapas.materias')->get();
        return response()->json(['ok' => true, 'data' => $programas]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo'           => 'required|string|unique:programas,codigo',
            'nombre'           => 'required|string',
            'tipo'             => 'required|string',
            'horas_tierra_min' => 'required|numeric',
            'horas_vuelo_min'  => 'required|numeric',
            'horas_dual_min'   => 'required|numeric',
            'horas_solo_min'   => 'required|numeric',
            'horas_ifr_min'    => 'nullable|numeric',
            'horas_noche_min'  => 'nullable|numeric',
            'horas_sim_max'    => 'nullable|numeric',
            'rac_referencia'   => 'nullable|string',
            'activo'           => 'boolean',
        ]);

        $programa = Programa::create($data);

        return response()->json(['ok' => true, 'data' => $programa], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $programa = Programa::findOrFail($id);

        $data = $request->validate([
            'codigo'           => 'sometimes|string|unique:programas,codigo,' . $id,
            'nombre'           => 'sometimes|string',
            'tipo'             => 'sometimes|string',
            'horas_tierra_min' => 'sometimes|numeric',
            'horas_vuelo_min'  => 'sometimes|numeric',
            'horas_dual_min'   => 'sometimes|numeric',
            'horas_solo_min'   => 'sometimes|numeric',
            'horas_ifr_min'    => 'nullable|numeric',
            'horas_noche_min'  => 'nullable|numeric',
            'horas_sim_max'    => 'nullable|numeric',
            'rac_referencia'   => 'nullable|string',
            'activo'           => 'boolean',
        ]);

        $programa->update($data);

        return response()->json(['ok' => true, 'data' => $programa]);
    }

    /* ─── Gestión de Etapas ─── */

    public function storeEtapa(Request $request, $programaId): JsonResponse
    {
        $data = $request->validate([
            'numero' => 'required|integer',
            'nombre' => 'required|string',
            'horas_tierra' => 'nullable|numeric',
            'horas_vuelo' => 'nullable|numeric',
            'descripcion' => 'nullable|string'
        ]);

        $data['programa_id'] = $programaId;
        $etapa = \App\Models\Etapa::create($data);

        return response()->json(['ok' => true, 'data' => $etapa]);
    }

    public function updateEtapa(Request $request, $id): JsonResponse
    {
        $etapa = \App\Models\Etapa::findOrFail($id);
        $data = $request->validate([
            'numero' => 'sometimes|integer',
            'nombre' => 'sometimes|string',
            'horas_tierra' => 'nullable|numeric',
            'horas_vuelo' => 'nullable|numeric',
            'descripcion' => 'nullable|string'
        ]);

        $etapa->update($data);
        return response()->json(['ok' => true, 'data' => $etapa]);
    }

    public function destroyEtapa($id): JsonResponse
    {
        $etapa = \App\Models\Etapa::findOrFail($id);
        $etapa->delete();
        return response()->json(['ok' => true, 'mensaje' => 'Etapa eliminada']);
    }
}
