<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matricula;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MatriculaController extends Controller
{
    public function index(): JsonResponse
    {
        $matriculas = Matricula::with(['estudiante.persona', 'programa', 'facturas'])->orderByDesc('fecha_matricula')->get();
        // Add dynamic calculated fields easily by mapping
        $res = $matriculas->map(function ($m) {
            $m->valor_neto = $m->valor_neto;
            $m->total_pagado = $m->total_pagado;
            $m->saldo_pendiente = $m->saldo_pendiente;
            return $m;
        });
        return response()->json(['ok' => true, 'data' => $res]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'persona_id'      => 'required|exists:personas,id',
            'programa_id'     => 'required|exists:programas,id',
            'fecha_matricula' => 'required|date',
            'valor_total'     => 'required|numeric|min:0',
            'descuento'       => 'nullable|numeric|min:0',
            'forma_pago'      => 'required|string',
            'num_cuotas'      => 'nullable|integer|min:1',
            'observaciones'   => 'nullable|string',
        ]);

        // Find or create Estudiante record
        $estudiante = \App\Models\Estudiante::firstOrCreate(
            ['persona_id' => $data['persona_id']],
            [
                'programa_id' => $data['programa_id'],
                'fecha_ingreso' => $data['fecha_matricula'],
                'estado' => 'activo',
                'num_expediente' => 'EXP-' . now()->year . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
            ]
        );

        $matriculaData = [
            'estudiante_id' => $estudiante->id,
            'programa_id' => $data['programa_id'],
            'fecha_matricula' => $data['fecha_matricula'],
            'valor_total' => $data['valor_total'],
            'descuento' => $data['descuento'] ?? 0,
            'forma_pago' => $data['forma_pago'],
            'num_cuotas' => $data['num_cuotas'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
            'estado' => 'activa'
        ];

        $matricula = Matricula::create($matriculaData);
        return response()->json(['ok' => true, 'data' => $matricula->load(['estudiante.persona', 'programa', 'facturas'])]);
    }

    public function show($id): JsonResponse
    {
         $matricula = Matricula::with(['estudiante.persona', 'programa', 'facturas.pagos'])->findOrFail($id);
         return response()->json(['ok' => true, 'data' => $matricula]);
    }

    public function update(Request $request, $id): JsonResponse
    {
         $matricula = Matricula::findOrFail($id);
         $matricula->update($request->only('estado', 'observaciones', 'descuento', 'forma_pago'));
         return response()->json(['ok' => true, 'data' => $matricula]);
    }

    public function facturas($id): JsonResponse
    {
        $matricula = Matricula::findOrFail($id);
        $facturas = $matricula->facturas()->with('pagos')->get();
        return response()->json(['ok' => true, 'data' => $facturas]);
    }
}
