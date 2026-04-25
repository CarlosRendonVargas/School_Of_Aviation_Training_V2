<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificado;
use App\Models\Estudiante;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificadoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Certificado::with(['estudiante.persona', 'emisor.persona', 'etapa', 'programa'])
            ->orderByDesc('fecha_emision');

        if ($user->rol?->nombre === 'estudiante') {
            $est = Estudiante::where('usuario_id', $user->id)->first();
            $query->where('estudiante_id', $est?->id);
        }

        if ($request->filled('estudiante_id')) {
            $query->where('estudiante_id', $request->estudiante_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function store(Request $request)
    {
        $this->soloAdminODirOps($request);

        $data = $request->validate([
            'tipo'          => 'required|string',
            'estudiante_id' => 'required|exists:estudiantes,id',
            'etapa_id'      => 'nullable|exists:etapas,id',
            'programa_id'   => 'nullable|exists:programas,id',
            'fecha_emision' => 'required|date',
            'descripcion'   => 'nullable|string|max:500',
        ]);

        $data['emitido_por']        = $request->user()->id;
        $data['numero_certificado'] = $this->generarNumero($data['tipo']);

        $cert = Certificado::create($data);

        return response()->json([
            'data'    => $cert->load(['estudiante.persona', 'emisor.persona', 'etapa', 'programa']),
            'mensaje' => 'Certificado emitido correctamente.',
        ], 201);
    }

    public function show(int $id)
    {
        return response()->json([
            'data' => Certificado::with(['estudiante.persona', 'emisor.persona', 'etapa', 'programa'])
                ->findOrFail($id),
        ]);
    }

    public function pdf(int $id)
    {
        $cert = Certificado::with(['estudiante.persona', 'emisor.persona', 'etapa', 'programa'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.certificado', ['cert' => $cert])
            ->setPaper('letter', 'landscape');

        return $pdf->download("Certificado_{$cert->numero_certificado}.pdf");
    }

    public function anular(Request $request, int $id)
    {
        $this->soloAdminODirOps($request);
        $data = $request->validate(['motivo_anulacion' => 'required|string|max:300']);

        $cert = Certificado::findOrFail($id);
        $cert->update(['anulado' => true, 'motivo_anulacion' => $data['motivo_anulacion']]);

        return response()->json(['mensaje' => 'Certificado anulado.']);
    }

    private function generarNumero(string $tipo): string
    {
        $prefijos = [
            'finalizacion_etapa'    => 'FE',
            'finalizacion_programa' => 'FP',
            'constancia_horas'      => 'CH',
            'constancia_matricula'  => 'CM',
            'aprobacion_examen'     => 'AE',
            'endorsement'           => 'EN',
        ];
        $prefijo = $prefijos[$tipo] ?? 'CE';
        $anio    = now()->year;
        $ultimo  = DB::table('certificados_emitidos')
            ->where('numero_certificado', 'like', "{$prefijo}-{$anio}-%")
            ->count();

        return sprintf('%s-%d-%04d', $prefijo, $anio, $ultimo + 1);
    }

    private function soloAdminODirOps(Request $request): void
    {
        $rol = $request->user()->rol?->nombre;
        if (!in_array($rol, ['admin', 'dir_ops'])) {
            abort(403, 'Solo administrador o director de operaciones puede gestionar certificados.');
        }
    }
}
