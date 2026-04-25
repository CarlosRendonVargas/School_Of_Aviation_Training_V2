<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NominaPeriodo;
use App\Models\NominaItem;
use Illuminate\Http\Request;

class NominaController extends Controller
{
    public function periodos(Request $request)
    {
        $this->soloAdminODirOps($request);

        return response()->json([
            'data' => NominaPeriodo::withCount('items')
                ->orderByDesc('periodo')
                ->get(),
        ]);
    }

    public function storePeriodo(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'periodo'      => 'required|string|size:7',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after:fecha_inicio',
        ]);

        $periodo = NominaPeriodo::create($data);

        return response()->json(['data' => $periodo, 'mensaje' => 'Período de nómina creado.'], 201);
    }

    public function itemsPeriodo(Request $request, int $periodoId)
    {
        $this->soloAdminODirOps($request);

        return response()->json([
            'data' => NominaItem::with('usuario.persona')
                ->where('periodo_id', $periodoId)
                ->get(),
        ]);
    }

    public function storeItem(Request $request, int $periodoId)
    {
        $this->soloAdminODirOps($request);

        NominaPeriodo::findOrFail($periodoId);

        $data = $request->validate([
            'usuario_id'       => 'required|exists:usuarios,id',
            'cargo'            => 'required|string|max:100',
            'salario_base'     => 'required|numeric|min:0',
            'horas_extra'      => 'nullable|numeric|min:0',
            'valor_hora_extra' => 'nullable|numeric|min:0',
            'bonificaciones'   => 'nullable|numeric|min:0',
            'deducciones'      => 'nullable|numeric|min:0',
        ]);

        $data['periodo_id']  = $periodoId;
        $data['salud']       = round($data['salario_base'] * 0.04, 2);
        $data['pension']     = round($data['salario_base'] * 0.04, 2);
        $horasExtra          = ($data['horas_extra'] ?? 0) * ($data['valor_hora_extra'] ?? 0);
        $data['neto_pagar']  = $data['salario_base']
            + $horasExtra
            + ($data['bonificaciones'] ?? 0)
            - ($data['deducciones'] ?? 0)
            - $data['salud']
            - $data['pension'];

        $item = NominaItem::create($data);

        // Recalcular total del período
        $total = NominaItem::where('periodo_id', $periodoId)->sum('neto_pagar');
        NominaPeriodo::where('id', $periodoId)->update(['total_pagado' => $total]);

        return response()->json([
            'data'    => $item->load('usuario.persona'),
            'mensaje' => 'Empleado agregado a nómina.',
        ], 201);
    }

    public function cerrarPeriodo(Request $request, int $periodoId)
    {
        $this->soloAdminODirOps($request);

        $periodo = NominaPeriodo::findOrFail($periodoId);
        $periodo->update(['estado' => 'procesado']);

        return response()->json(['mensaje' => 'Período procesado.']);
    }

    private function soloAdminODirOps(Request $request): void
    {
        if (!in_array($request->user()->rol?->nombre, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
