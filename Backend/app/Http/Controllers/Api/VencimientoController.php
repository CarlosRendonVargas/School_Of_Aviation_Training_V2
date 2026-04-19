<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VencimientoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VencimientoController extends Controller
{
    public function __construct(private VencimientoService $service) {}

    public function index(Request $request): JsonResponse
    {
        $dias = (int) $request->get('dias', 90);
        return response()->json([
            'ok'   => true,
            'data' => $this->service->proximosVencimientos($dias),
        ]);
    }

    public function criticos(): JsonResponse
    {
        return response()->json([
            'ok'   => true,
            'data' => $this->service->proximosVencimientos(15),
        ]);
    }

    public function vencidos(): JsonResponse
    {
        return response()->json([
            'ok'   => true,
            'data' => $this->service->vencidos(),
        ]);
    }

    public function sincronizar(Request $request): JsonResponse
    {
        // dir_ops y admin pueden forzar sincronización manual
        $rol = $request->user()->rol?->nombre;
        if (!in_array($rol, ['dir_ops', 'admin'])) {
            return response()->json(['ok' => false, 'mensaje' => 'Sin permiso.'], 403);
        }

        $resultado = $this->service->sincronizar();

        return response()->json(['ok' => true, 'data' => $resultado]);
    }
}
