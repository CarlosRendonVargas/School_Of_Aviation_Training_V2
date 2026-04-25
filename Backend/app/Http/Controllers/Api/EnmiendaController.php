<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnmiendaDocumento;
use App\Models\DocumentoRac;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnmiendaController extends Controller
{
    public function index(Request $request)
    {
        $query = EnmiendaDocumento::with(['documento', 'elaborador.persona'])
            ->orderByDesc('created_at');

        if ($request->filled('documento_id')) {
            $query->where('documento_id', $request->documento_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'documento_id'    => 'required|exists:documentos_rac,id',
            'descripcion'     => 'required|string|max:300',
            'contenido_cambio'=> 'required|string',
            'estado'          => 'required|string',
        ]);

        $data['elaborado_por']   = $request->user()->id;
        $data['numero_enmienda'] = $this->generarNumero($data['documento_id']);

        $en = EnmiendaDocumento::create($data);

        return response()->json([
            'data'    => $en->load(['documento', 'elaborador.persona']),
            'mensaje' => 'Enmienda creada.',
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);

        $en   = EnmiendaDocumento::findOrFail($id);
        $data = $request->validate([
            'estado'           => 'sometimes|string',
            'fecha_envio'      => 'nullable|date',
            'fecha_respuesta'  => 'nullable|date',
            'respuesta_uaeac'  => 'nullable|string',
            'descripcion'      => 'sometimes|string|max:300',
            'contenido_cambio' => 'sometimes|string',
        ]);

        $en->update($data);

        return response()->json(['data' => $en->fresh('documento'), 'mensaje' => 'Enmienda actualizada.']);
    }

    private function generarNumero(int $docId): string
    {
        $anio  = now()->year;
        $count = DB::table('enmiendas_documentos')
            ->where('documento_id', $docId)
            ->whereYear('created_at', $anio)
            ->count();

        return sprintf('ENM-%d-%03d', $anio, $count + 1);
    }

    private function soloAdminODirOps(Request $request): void
    {
        if (!in_array($request->user()->rol?->nombre, ['admin', 'dir_ops'])) {
            abort(403);
        }
    }
}
