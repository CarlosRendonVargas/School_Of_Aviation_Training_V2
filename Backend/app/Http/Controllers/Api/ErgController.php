<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlanErg;
use Illuminate\Http\Request;

class ErgController extends Controller
{
    public function index()
    {
        return response()->json(['data' => PlanErg::orderBy('tipo_emergencia')->get()]);
    }

    public function store(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'titulo'                => 'required|string|max:200',
            'tipo_emergencia'       => 'required|string',
            'procedimiento'         => 'required|string',
            'personal_responsable'  => 'required|string',
            'contactos_emergencia'  => 'required|string',
            'version'               => 'required|string|max:10',
            'fecha_revision'        => 'required|date',
            'proxima_revision'      => 'required|date',
            'activo'                => 'boolean',
        ]);

        $plan = PlanErg::create($data);

        return response()->json(['data' => $plan, 'mensaje' => 'Plan ERG creado.'], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);

        $plan = PlanErg::findOrFail($id);
        $plan->update($request->validate([
            'titulo'               => 'sometimes|string|max:200',
            'procedimiento'        => 'sometimes|string',
            'personal_responsable' => 'sometimes|string',
            'contactos_emergencia' => 'sometimes|string',
            'version'              => 'sometimes|string|max:10',
            'fecha_revision'       => 'sometimes|date',
            'proxima_revision'     => 'sometimes|date',
            'activo'               => 'boolean',
        ]));

        return response()->json(['data' => $plan, 'mensaje' => 'Plan ERG actualizado.']);
    }

    public function destroy(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);
        PlanErg::findOrFail($id)->delete();

        return response()->json(['mensaje' => 'Plan ERG eliminado.']);
    }

    private function soloAdminODirOps(Request $request): void
    {
        if (!in_array($request->user()->rol?->nombre, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
