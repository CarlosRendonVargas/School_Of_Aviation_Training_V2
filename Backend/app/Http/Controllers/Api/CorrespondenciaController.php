<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorrespondenciaUaeac;
use Illuminate\Http\Request;

class CorrespondenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = CorrespondenciaUaeac::with('registrador:id,nombres,apellidos')
            ->orderBy('fecha_documento', 'desc');

        if ($request->tipo) $query->where('tipo', $request->tipo);
        if ($request->categoria) $query->where('categoria', $request->categoria);
        if ($request->estado) $query->where('estado', $request->estado);
        if ($request->desde) $query->where('fecha_documento', '>=', $request->desde);
        if ($request->hasta) $query->where('fecha_documento', '<=', $request->hasta);
        if ($request->buscar) {
            $q = $request->buscar;
            $query->where(function ($qb) use ($q) {
                $qb->where('asunto', 'like', "%$q%")
                   ->orWhere('numero_radicado', 'like', "%$q%");
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_radicado'           => 'nullable|string|max:30',
            'tipo'                      => 'required|in:entrada,salida',
            'categoria'                 => 'required|in:certificacion,inspeccion,enmienda,solicitud,respuesta,circular,resolucion,otro',
            'asunto'                    => 'required|string|max:300',
            'contenido'                 => 'nullable|string',
            'fecha_documento'           => 'required|date',
            'fecha_vencimiento_respuesta' => 'nullable|date',
            'estado'                    => 'nullable|in:recibido,en_revision,respondido,archivado',
            'archivo_url'               => 'nullable|string|max:500',
            'notas_internas'            => 'nullable|string',
        ]);

        $data['registrado_por'] = auth()->id();

        $correspondencia = CorrespondenciaUaeac::create($data);
        return response()->json($correspondencia->load('registrador:id,nombres,apellidos'), 201);
    }

    public function show($id)
    {
        $correspondencia = CorrespondenciaUaeac::with('registrador:id,nombres,apellidos')->findOrFail($id);
        return response()->json($correspondencia);
    }

    public function update(Request $request, $id)
    {
        $correspondencia = CorrespondenciaUaeac::findOrFail($id);

        $data = $request->validate([
            'numero_radicado'           => 'nullable|string|max:30',
            'tipo'                      => 'sometimes|in:entrada,salida',
            'categoria'                 => 'sometimes|in:certificacion,inspeccion,enmienda,solicitud,respuesta,circular,resolucion,otro',
            'asunto'                    => 'sometimes|string|max:300',
            'contenido'                 => 'nullable|string',
            'fecha_documento'           => 'sometimes|date',
            'fecha_vencimiento_respuesta' => 'nullable|date',
            'estado'                    => 'sometimes|in:recibido,en_revision,respondido,archivado',
            'archivo_url'               => 'nullable|string|max:500',
            'notas_internas'            => 'nullable|string',
        ]);

        $correspondencia->update($data);
        return response()->json($correspondencia->load('registrador:id,nombres,apellidos'));
    }

    public function estadisticas()
    {
        return response()->json([
            'total'           => CorrespondenciaUaeac::count(),
            'entrada'         => CorrespondenciaUaeac::where('tipo', 'entrada')->count(),
            'salida'          => CorrespondenciaUaeac::where('tipo', 'salida')->count(),
            'pendientes'      => CorrespondenciaUaeac::whereIn('estado', ['recibido', 'en_revision'])
                                    ->whereNotNull('fecha_vencimiento_respuesta')
                                    ->where('fecha_vencimiento_respuesta', '>=', now())
                                    ->count(),
            'vencidos'        => CorrespondenciaUaeac::whereIn('estado', ['recibido', 'en_revision'])
                                    ->whereNotNull('fecha_vencimiento_respuesta')
                                    ->where('fecha_vencimiento_respuesta', '<', now())
                                    ->count(),
            'por_categoria'   => CorrespondenciaUaeac::selectRaw('categoria, count(*) as total')
                                    ->groupBy('categoria')->get(),
        ]);
    }
}
