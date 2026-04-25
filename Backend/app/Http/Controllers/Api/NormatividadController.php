<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NormatividadDocumento;
use Illuminate\Http\Request;

class NormatividadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $docs = NormatividadDocumento::ordenados()
            ->when($user->rol?->nombre !== 'admin', fn($q) => $q->activos())
            ->get();

        $agrupados = $docs->groupBy('categoria')->map(fn($items, $cat) => [
            'categoria' => $cat,
            'documentos' => $items->values(),
        ])->values();

        return response()->json(['data' => $agrupados]);
    }

    public function store(Request $request)
    {
        $this->soloAdmin($request);

        $data = $request->validate([
            'titulo'      => 'required|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'url_pdf'     => 'required|string|max:1000',
            'categoria'   => 'required|string|max:100',
            'orden'       => 'nullable|integer',
            'activo'      => 'boolean',
        ]);

        $doc = NormatividadDocumento::create($data);

        return response()->json(['data' => $doc, 'mensaje' => 'Documento creado correctamente.'], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->soloAdmin($request);

        $doc = NormatividadDocumento::findOrFail($id);

        $data = $request->validate([
            'titulo'      => 'sometimes|string|max:200',
            'descripcion' => 'nullable|string|max:500',
            'url_pdf'     => 'sometimes|string|max:1000',
            'categoria'   => 'sometimes|string|max:100',
            'orden'       => 'nullable|integer',
            'activo'      => 'boolean',
        ]);

        $doc->update($data);

        return response()->json(['data' => $doc, 'mensaje' => 'Documento actualizado.']);
    }

    public function destroy(Request $request, int $id)
    {
        $this->soloAdmin($request);

        NormatividadDocumento::findOrFail($id)->delete();

        return response()->json(['mensaje' => 'Documento eliminado.']);
    }

    private function soloAdmin(Request $request): void
    {
        if ($request->user()->rol?->nombre !== 'admin') {
            abort(403, 'Solo el administrador puede gestionar normatividad.');
        }
    }
}
