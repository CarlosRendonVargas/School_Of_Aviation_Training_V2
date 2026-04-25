<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReporteSms;
use App\Models\AccionCorrectiva;
use App\Models\CapacitacionSms;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SmsController extends Controller
{
    public function __construct(private SmsService $smsService) {}

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'ok'   => true,
            'data' => $this->smsService->kpis((int) $request->get('meses', 12)),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = ReporteSms::with(['aeronave:id,matricula,modelo'])
            ->when($request->tipo,   fn($q, $v) => $q->where('tipo', $v))
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->orderByDesc('nivel_riesgo')->orderByDesc('created_at');

        return response()->json(['ok' => true, 'data' => $query->paginate(20)]);
    }

    public function show($id): JsonResponse
    {
        $reporte = ReporteSms::with(['accionesCorrectivas.responsable.persona'])->findOrFail($id);
        return response()->json(['ok' => true, 'data' => $reporte]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'anonimo'      => 'required|boolean',
            'tipo'         => 'required|in:peligro,incidente,accidente,near_miss',
            'descripcion'  => 'required|string|min:20',
            'fecha_evento' => 'required|date',
            'lugar'        => 'required|string',
            'aeronave_id'  => 'nullable|exists:aeronaves,id',
            'severidad'    => 'required|integer|min:1|max:5',
            'probabilidad' => 'required|integer|min:1|max:5',
        ]);

        $reporte = $this->smsService->crearReporte($data);

        $nivelInfo = $this->smsService->nivelRiesgo($data['severidad'], $data['probabilidad']);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Reporte SMS registrado. ' . ucfirst($nivelInfo['clasificacion']) . '.',
            'nivel'   => $nivelInfo,
            'data'    => $reporte,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $reporte = ReporteSms::findOrFail($id);
        $data    = $request->validate([
            'estado'           => 'sometimes|in:nuevo,en_analisis,cerrado',
            'notificado_uaeac' => 'sometimes|boolean',
        ]);
        $reporte->update($data);
        return response()->json(['ok' => true, 'data' => $reporte]);
    }

    public function asignarAcciones(Request $request, $id): JsonResponse
    {
        $reporte = ReporteSms::findOrFail($id);
        $request->validate([
            'acciones'                  => 'required|array|min:1',
            'acciones.*.descripcion'    => 'required|string',
            'acciones.*.responsable_id' => 'required|exists:usuarios,id',
            'acciones.*.fecha_limite'   => 'required|date|after:today',
        ]);

        $this->smsService->asignarAcciones($reporte, $request->acciones);

        return response()->json(['ok' => true, 'mensaje' => 'Acciones asignadas correctamente.']);
    }

    public function cerrar(Request $request, $id): JsonResponse
    {
        $reporte = ReporteSms::findOrFail($id);
        $ok      = $this->smsService->cerrarReporte($reporte);

        if (! $ok) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No se puede cerrar: hay acciones correctivas pendientes.',
            ], 422);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Reporte SMS cerrado.']);
    }

    public function acciones(Request $request): JsonResponse
    {
        $query = AccionCorrectiva::with(['reporteSms:id,tipo,descripcion'])
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->orderBy('fecha_limite');

        return response()->json(['ok' => true, 'data' => $query->paginate(20)]);
    }

    public function updateAccion(Request $request, $id): JsonResponse
    {
        $accion = AccionCorrectiva::findOrFail($id);
        $data   = $request->validate([
            'estado'       => 'required|in:pendiente,en_proceso,cerrada,verificada',
            'observaciones_cierre' => 'nullable|string',
            'evidencia_url' => 'nullable|url',
        ]);

        if (in_array($data['estado'], ['cerrada', 'verificada'])) {
            $data['fecha_cierre'] = now()->toDateString();
        }

        $accion->update($data);
        return response()->json(['ok' => true, 'data' => $accion]);
    }

    public function cerrarAccion(Request $request, $id): JsonResponse
    {
        $accion = AccionCorrectiva::findOrFail($id);
        $request->validate(['observaciones' => 'required|string']);

        $accion->update([
            'estado'               => 'cerrada',
            'observaciones_cierre' => $request->observaciones,
            'fecha_cierre'         => now()->toDateString(),
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Acción correctiva cerrada.']);
    }

    public function matrizRiesgo(): JsonResponse
    {
        $matriz = [];
        for ($s = 1; $s <= 5; $s++) {
            for ($p = 1; $p <= 5; $p++) {
                $matriz[$s][$p] = $this->smsService->nivelRiesgo($s, $p);
            }
        }
        return response()->json(['ok' => true, 'data' => $matriz]);
    }

    public function exportarGriaa(Request $request): JsonResponse
    {
        $anio = $request->get('anio', date('Y'));

        $reportes = ReporteSms::selectRaw('
                id, tipo, descripcion, fecha_evento, lugar,
                severidad, probabilidad, nivel_riesgo, estado,
                notificado_uaeac, created_at
            ')
            ->whereYear('fecha_evento', $anio)
            ->orderBy('fecha_evento')
            ->get();

        $resumen = [
            'total'             => $reportes->count(),
            'por_tipo'          => $reportes->groupBy('tipo')->map->count(),
            'por_nivel_riesgo'  => $reportes->groupBy('nivel_riesgo')->map->count(),
            'notificados_uaeac' => $reportes->where('notificado_uaeac', true)->count(),
            'cerrados'          => $reportes->where('estado', 'cerrado')->count(),
        ];

        return response()->json([
            'ok'      => true,
            'anio'    => $anio,
            'resumen' => $resumen,
            'data'    => $reportes,
        ]);
    }

    public function kpisAaer(Request $request): JsonResponse
    {
        $meses = (int) $request->get('meses', 12);
        $desde = now()->subMonths($meses);

        $total          = ReporteSms::where('created_at', '>=', $desde)->count();
        $accidentes     = ReporteSms::where('tipo', 'accidente')->where('created_at', '>=', $desde)->count();
        $incidentes     = ReporteSms::where('tipo', 'incidente')->where('created_at', '>=', $desde)->count();
        $nearMiss       = ReporteSms::where('tipo', 'near_miss')->where('created_at', '>=', $desde)->count();
        $altoRiesgo     = ReporteSms::where('nivel_riesgo', 'alto')->where('created_at', '>=', $desde)->count();
        $tasaCierre     = $total > 0
            ? round(ReporteSms::where('estado', 'cerrado')->where('created_at', '>=', $desde)->count() / $total * 100, 1)
            : 0;

        $capacitaciones = CapacitacionSms::where('created_at', '>=', $desde)->count();
        $participantes  = DB::table('registros_capacitacion')
            ->whereHas = null; // compute via join
        $participantes  = DB::table('registros_capacitacion as rc')
            ->join('capacitaciones_sms as c', 'rc.capacitacion_id', '=', 'c.id')
            ->where('c.created_at', '>=', $desde)
            ->count();

        return response()->json([
            'ok'   => true,
            'data' => [
                'periodo_meses'        => $meses,
                'total_reportes'       => $total,
                'accidentes'           => $accidentes,
                'incidentes'           => $incidentes,
                'near_miss'            => $nearMiss,
                'alto_riesgo'          => $altoRiesgo,
                'tasa_cierre_pct'      => $tasaCierre,
                'capacitaciones_sms'   => $capacitaciones,
                'participantes_total'  => $participantes,
            ],
        ]);
    }
}
