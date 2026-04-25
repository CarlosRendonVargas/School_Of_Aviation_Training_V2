<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prospecto;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    public function index(Request $request)
    {
        $this->soloAdminOAdmin($request);

        $query = Prospecto::with('asignado.persona')->orderByDesc('created_at');

        if ($request->filled('estado'))   $query->where('estado', $request->estado);
        if ($request->filled('programa')) $query->where('programa_interes', $request->programa);
        if ($request->filled('search'))   $query->where(fn($q) =>
            $q->where('nombres', 'like', "%{$request->search}%")
              ->orWhere('apellidos', 'like', "%{$request->search}%")
              ->orWhere('email', 'like', "%{$request->search}%")
        );

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->soloAdminOAdmin($request);

        $data = $request->validate([
            'nombres'               => 'required|string|max:100',
            'apellidos'             => 'required|string|max:100',
            'email'                 => 'nullable|email|max:150',
            'telefono'              => 'nullable|string|max:20',
            'ciudad'                => 'nullable|string|max:100',
            'programa_interes'      => 'required|string',
            'estado'                => 'required|string',
            'notas'                 => 'nullable|string',
            'fuente'                => 'nullable|string|max:100',
            'fecha_primer_contacto' => 'required|date',
            'proxima_accion'        => 'nullable|date',
            'asignado_a'            => 'nullable|exists:usuarios,id',
        ]);

        $p = Prospecto::create($data);

        return response()->json([
            'data'    => $p->load('asignado.persona'),
            'mensaje' => 'Prospecto registrado.',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->soloAdminOAdmin($request);

        $p = Prospecto::findOrFail($id);
        $data = $request->validate([
            'nombres'          => 'sometimes|string|max:100',
            'apellidos'        => 'sometimes|string|max:100',
            'email'            => 'nullable|email|max:150',
            'telefono'         => 'nullable|string|max:20',
            'ciudad'           => 'nullable|string|max:100',
            'programa_interes' => 'sometimes|string',
            'estado'           => 'sometimes|string',
            'notas'            => 'nullable|string',
            'fuente'           => 'nullable|string|max:100',
            'proxima_accion'   => 'nullable|date',
            'asignado_a'       => 'nullable|exists:usuarios,id',
        ]);

        $p->update($data);

        return response()->json(['data' => $p->fresh('asignado.persona'), 'mensaje' => 'Prospecto actualizado.']);
    }

    public function destroy(Request $request, int $id)
    {
        $this->soloAdminOAdmin($request);
        Prospecto::findOrFail($id)->delete();

        return response()->json(['mensaje' => 'Prospecto eliminado.']);
    }

    public function estadisticas(Request $request)
    {
        $this->soloAdminOAdmin($request);

        return response()->json(['data' => [
            'total'       => Prospecto::count(),
            'por_estado'  => Prospecto::selectRaw('estado, count(*) as total')->groupBy('estado')->pluck('total', 'estado'),
            'por_programa'=> Prospecto::selectRaw('programa_interes, count(*) as total')->groupBy('programa_interes')->pluck('total', 'programa_interes'),
            'proximos_7d' => Prospecto::whereBetween('proxima_accion', [now()->toDateString(), now()->addDays(7)->toDateString()])->count(),
        ]]);
    }

    private function soloAdminOAdmin(Request $request): void
    {
        $rol = $request->user()->rol?->nombre;
        if (!in_array($rol, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
