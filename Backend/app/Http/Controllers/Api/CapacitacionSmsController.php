<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CapacitacionSms;
use App\Models\RegistroCapacitacion;
use Illuminate\Http\Request;

class CapacitacionSmsController extends Controller
{
    public function index()
    {
        $caps = CapacitacionSms::withCount('registros')
            ->orderByDesc('fecha')
            ->get();

        return response()->json(['data' => $caps]);
    }

    public function store(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'titulo'            => 'required|string|max:200',
            'descripcion'       => 'nullable|string',
            'tipo'              => 'required|string',
            'fecha'             => 'required|date',
            'duracion_horas'    => 'required|integer|min:1',
            'instructor_nombre' => 'nullable|string|max:150',
            'obligatoria'       => 'boolean',
            'estado'            => 'required|string',
            'material_url'      => 'nullable|string',
        ]);

        $cap = CapacitacionSms::create($data);

        return response()->json(['data' => $cap, 'mensaje' => 'Capacitación creada.'], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);

        $cap  = CapacitacionSms::findOrFail($id);
        $data = $request->validate([
            'titulo'         => 'sometimes|string|max:200',
            'estado'         => 'sometimes|string',
            'fecha'          => 'sometimes|date',
            'duracion_horas' => 'sometimes|integer|min:1',
            'obligatoria'    => 'boolean',
        ]);
        $cap->update($data);

        return response()->json(['data' => $cap, 'mensaje' => 'Capacitación actualizada.']);
    }

    public function registros(int $id)
    {
        $cap  = CapacitacionSms::findOrFail($id);
        $regs = RegistroCapacitacion::with('usuario.persona')
            ->where('capacitacion_id', $id)
            ->get();

        return response()->json(['data' => ['capacitacion' => $cap, 'registros' => $regs]]);
    }

    public function registrarAsistencia(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'asistio'    => 'required|boolean',
            'nota'       => 'nullable|numeric|min:0|max:100',
            'aprobado'   => 'boolean',
        ]);

        $reg = RegistroCapacitacion::updateOrCreate(
            ['capacitacion_id' => $id, 'usuario_id' => $data['usuario_id']],
            $data
        );

        return response()->json(['data' => $reg, 'mensaje' => 'Asistencia registrada.']);
    }

    private function soloAdminODirOps(Request $request): void
    {
        if (!in_array($request->user()->rol?->nombre, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
