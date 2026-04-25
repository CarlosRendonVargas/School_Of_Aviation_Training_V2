<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GastoOperativo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoOperativoController extends Controller
{
    public function index(Request $request)
    {
        $this->soloAdminODirOps($request);

        $query = GastoOperativo::with('registrador.persona')->orderByDesc('fecha');

        if ($request->filled('tipo'))    $query->where('tipo', $request->tipo);
        if ($request->filled('fecha_desde')) $query->where('fecha', '>=', $request->fecha_desde);
        if ($request->filled('fecha_hasta')) $query->where('fecha', '<=', $request->fecha_hasta);

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'tipo'             => 'required|string',
            'concepto'         => 'required|string|max:200',
            'valor'            => 'required|numeric|min:0',
            'fecha'            => 'required|date',
            'responsable'      => 'required|string|max:150',
            'comprobante_url'  => 'nullable|string|max:500',
            'observaciones'    => 'nullable|string',
        ]);

        $data['registrado_por'] = $request->user()->id;
        $gasto = GastoOperativo::create($data);

        return response()->json([
            'data'    => $gasto->load('registrador.persona'),
            'mensaje' => 'Gasto registrado.',
        ], 201);
    }

    public function resumen(Request $request)
    {
        $this->soloAdminODirOps($request);

        $mes  = $request->get('mes', now()->format('Y-m'));
        $anio = substr($mes, 0, 4);
        $m    = substr($mes, 5, 2);

        $totales = GastoOperativo::whereYear('fecha', $anio)
            ->whereMonth('fecha', $m)
            ->selectRaw('tipo, SUM(valor) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return response()->json(['data' => [
            'mes'    => $mes,
            'total'  => $totales->sum(),
            'tipos'  => $totales,
        ]]);
    }

    private function soloAdminODirOps(Request $request): void
    {
        if (!in_array($request->user()->rol?->nombre, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
