<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VencimientoService;
use App\Services\HorasVueloService;
use App\Services\SmsService;
use App\Models\Estudiante;
use App\Models\Instructor;
use App\Models\Aeronave;
use App\Models\Reserva;
use App\Models\ReporteSms;
use App\Models\Certificado;
use App\Models\BitacoraVuelo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private VencimientoService $vencimientoService,
        private HorasVueloService  $horasService,
        private SmsService         $smsService,
    ) {}

    /**
     * GET /api/v1/dashboard
     * Responde con datos adaptados al rol del usuario autenticado.
     */
    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $rol     = $usuario->rol?->nombre;

        $data = match ($rol) {
            'estudiante'    => $this->dashboardEstudiante($usuario),
            'instructor'    => $this->dashboardInstructor($usuario),
            'admin'         => $this->dashboardAdmin(),
            'dir_ops'       => $this->dashboardDirOps(),
            'mantenimiento' => $this->dashboardMantenimiento(),
            'auditor_uaeac' => $this->dashboardAuditor(),
            default         => [],
        };

        return response()->json(['ok' => true, 'data' => $data]);
    }

    /* ─── Dashboard Estudiante ─────────────────────────────────────── */

    private function dashboardEstudiante($usuario): array
    {
        $estudiante = $usuario->persona?->estudiante;
        if (! $estudiante) return ['error' => 'Sin expediente de estudiante'];

        $estudiante->load('programa');

        return [
            'expediente'      => $estudiante->num_expediente,
            'programa'        => $estudiante->programa->nombre,
            'estado'          => $estudiante->estado,
            'progreso_horas'  => $this->horasService->resumen($estudiante),
            'tiene_medico'    => $estudiante->tieneMedicoVigente(),
            'proxima_reserva' => Reserva::where('estudiante_id', $estudiante->id)
                ->where('fecha', '>=', now()->toDateString())
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->orderBy('fecha')->orderBy('hora_inicio')
                ->with(['aeronave:id,matricula,modelo', 'instructor.persona:id,nombres,apellidos'])
                ->first(),
            'vencimientos'    => $this->vencimientoService
                ->proximosVencimientos(60)
                ->where('tipo_entidad', 'estudiante')
                ->where('entidad_id', $estudiante->id)
                ->values(),
        ];
    }

    /* ─── Dashboard Instructor ─────────────────────────────────────── */

    private function dashboardInstructor($usuario): array
    {
        $instructor = $usuario->persona?->instructor;
        if (! $instructor) return ['error' => 'Sin perfil de instructor'];

        $hoy = now()->toDateString();

        return [
            'instructor'          => [
                'nombre'          => $instructor->persona->nombre_completo,
                'licencia'        => $instructor->num_licencia,
                'venc_licencia'   => $instructor->venc_licencia->format('Y-m-d'),
                'dias_venc'       => $instructor->diasParaVencerLicencia(),
                'licencia_ok'     => $instructor->licenciaVigente(),
            ],
            'vuelos_hoy'          => Reserva::where('instructor_id', $instructor->id)
                ->where('fecha', $hoy)
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->with(['estudiante.persona:id,nombres,apellidos', 'aeronave:id,matricula,modelo'])
                ->get(),
            'estudiantes_asignados' => Estudiante::activos()
                ->whereHas('reservas', fn($q) =>
                    $q->where('instructor_id', $instructor->id)
                      ->where('fecha', '>=', now()->subMonth()->toDateString())
                )
                ->with('persona:id,nombres,apellidos')
                ->get(),
            'mis_vencimientos'    => $this->vencimientoService
                ->proximosVencimientos(90)
                ->where('tipo_entidad', 'instructor')
                ->where('entidad_id', $instructor->id)
                ->values(),
            'horas_mes'           => $instructor->bitacoras()
                ->whereMonth('fecha', now()->month)
                ->sum('horas_totales'),
        ];
    }

    /* ─── Dashboard Admin ──────────────────────────────────────────── */

    private function dashboardAdmin(): array
    {
        // Datos para el gráfico de flujo de matrículas (últimos 6 meses)
        $flujoMatriculas = collect(range(5, 0))->map(function ($i) {
            $fecha = now()->subMonths($i);
            return [
                'mes'   => $fecha->translatedFormat('M'),
                'total' => \App\Models\Matricula::whereYear('fecha_matricula', $fecha->year)
                            ->whereMonth('fecha_matricula', $fecha->month)
                            ->count()
            ];
        });

        return [
            'estudiantes_activos'    => Estudiante::activos()->count(),
            'aeronaves_activas'      => Aeronave::where('estado', 'activa')->count(),
            'horas_vuelo_anio'       => round(BitacoraVuelo::whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'horas_vuelo_mes'        => round(BitacoraVuelo::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'certificados_emitidos'  => Certificado::whereYear('fecha_emision', now()->year)->where('anulado', false)->count(),
            'matriculas_activas'     => \App\Models\Matricula::where('estado', 'activa')->count(),
            'documentos_vigentes'    => \App\Models\DocumentoRac::vigentes()->count(),
            'ingresos_mes'           => \App\Models\Pago::whereMonth('fecha_pago', now()->month)->sum('valor'),
            'facturas_pendientes'    => \App\Models\Factura::where('estado', 'pendiente')->count(),
            'facturas_vencidas'      => \App\Models\Factura::where('estado', 'vencida')->count(),
            'nuevas_matriculas_mes'  => \App\Models\Matricula::whereMonth('fecha_matricula', now()->month)->count(),
            'flujo_matriculas'       => $flujoMatriculas,
        ];
    }

    /* ─── Dashboard Dir. Operaciones ───────────────────────────────── */

    private function dashboardDirOps(): array
    {
        return [
            'estudiantes_activos'    => Estudiante::activos()->count(),
            'aeronaves_activas'      => Aeronave::where('estado', 'activa')->count(),
            'horas_vuelo_anio'       => round(BitacoraVuelo::whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'horas_vuelo_mes'        => round(BitacoraVuelo::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'certificados_emitidos'  => Certificado::whereYear('fecha_emision', now()->year)->where('anulado', false)->count(),
            'matriculas_activas'     => \App\Models\Matricula::where('estado', 'activa')->count(),
            'documentos_vigentes'    => \App\Models\DocumentoRac::vigentes()->count(),
            'resumen_escuela' => [
                'estudiantes_activos'  => Estudiante::activos()->count(),
                'instructores_activos' => Instructor::activos()->count(),
                'aeronaves_disponibles'=> Aeronave::disponibles()->count(),
                'vuelos_hoy'           => Reserva::where('fecha', now()->toDateString())
                                            ->whereIn('estado', ['pendiente', 'confirmada'])->count(),
            ],
            'vencimientos_criticos' => $this->vencimientoService->proximosVencimientos(30),
            'vencidos'              => $this->vencimientoService->vencidos(),
            'sms_kpis'              => $this->smsService->kpis(3),
            'reportes_sms_nuevos'   => ReporteSms::where('estado', 'nuevo')->count(),
            'aeronaves_mantenimiento'=> Aeronave::where('estado', 'mantenimiento')
                                        ->get(['id', 'matricula', 'modelo', 'estado']),
        ];
    }

    /* ─── Dashboard Mantenimiento ──────────────────────────────────── */

    private function dashboardMantenimiento(): array
    {
        return [
            'aeronaves'               => Aeronave::where('estado', '!=', 'baja')
                ->get(['id', 'matricula', 'modelo', 'estado',
                       'horas_celula_total', 'venc_airworthiness']),
            'mantenimientos_proximos' => \App\Models\RegistroMantenimiento::whereNotNull('proxima_fecha')
                ->orderBy('proxima_fecha')
                ->limit(5)->get(),
            'mel_abiertos'            => \App\Models\MelItem::where('estado', 'abierto')->get(),
            'ads_pendientes'          => \App\Models\AirworthinessDirective::where('estado', 'pendiente')->get(),
        ];
    }

    /* ─── Dashboard Auditor UAEAC ──────────────────────────────────── */

    private function dashboardAuditor(): array
    {
        return [
            'estudiantes_activos'    => Estudiante::activos()->count(),
            'aeronaves_activas'      => Aeronave::where('estado', 'activa')->count(),
            'horas_vuelo_anio'       => round(BitacoraVuelo::whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'horas_vuelo_mes'        => round(BitacoraVuelo::whereMonth('fecha', now()->month)->whereYear('fecha', now()->year)->sum('horas_totales'), 1),
            'certificados_emitidos'  => Certificado::whereYear('fecha_emision', now()->year)->where('anulado', false)->count(),
            'matriculas_activas'     => \App\Models\Matricula::where('estado', 'activa')->count(),
            'documentos_vigentes'    => \App\Models\DocumentoRac::vigentes()->count(),
            'totales' => [
                'estudiantes'   => Estudiante::count(),
                'instructores'  => Instructor::count(),
                'aeronaves'     => Aeronave::count(),
                'egresados'     => Estudiante::where('estado', 'graduado')->count(),
            ],
            'vencidos_criticos'  => $this->vencimientoService->vencidos(),
            'documentos_rac'     => \App\Models\DocumentoRac::vigentes()->get(),
            'sms_resumen'        => $this->smsService->kpis(12),
            'instructores_vencidos' => Instructor::where('venc_licencia', '<', now()->toDateString())
                ->with('persona:id,nombres,apellidos')
                ->get(['id', 'persona_id', 'num_licencia', 'venc_licencia']),
        ];
    }
}
