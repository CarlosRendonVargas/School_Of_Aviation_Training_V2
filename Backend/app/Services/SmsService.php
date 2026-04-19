<?php

namespace App\Services;

use App\Models\ReporteSms;
use App\Models\AccionCorrectiva;
use Illuminate\Support\Facades\Auth;

/**
 * SmsService
 * ──────────
 * Gestiona el ciclo de vida del Sistema de Gestión de Seguridad Operacional.
 * Referencia: OACI Anexo 19 · RAC 141 · Circular AC UAEAC
 *
 * Matriz de riesgo OACI (severidad × probabilidad):
 *  - 1–4   : Aceptable (verde)
 *  - 5–9   : Tolerable (amarillo) → requiere acción
 *  - 10–25 : Inaceptable (rojo)   → acción inmediata + notificar UAEAC si > 15
 */
class SmsService
{
    // ── Clasificación de nivel de riesgo OACI ──────────────────────
    const RIESGO_ACEPTABLE    = [1, 4];
    const RIESGO_TOLERABLE    = [5, 9];
    const RIESGO_INACEPTABLE  = [10, 25];

    /**
     * Crea un nuevo reporte SMS.
     * Si es anónimo, no vincula usuario.
     */
    public function crearReporte(array $datos): ReporteSms
    {
        $nivel = $datos['severidad'] * $datos['probabilidad'];

        $reporte = ReporteSms::create([
            'reportante_id'    => $datos['anonimo'] ? null : Auth::id(),
            'anonimo'          => $datos['anonimo'] ?? false,
            'tipo'             => $datos['tipo'],
            'descripcion'      => $datos['descripcion'],
            'fecha_evento'     => $datos['fecha_evento'],
            'lugar'            => $datos['lugar'],
            'aeronave_id'      => $datos['aeronave_id'] ?? null,
            'severidad'        => $datos['severidad'],
            'probabilidad'     => $datos['probabilidad'],
            'nivel_riesgo'     => $nivel,
            'estado'           => 'nuevo',
            // Accidentes siempre deben notificarse (RAC / OACI Anexo 13)
            'notificado_uaeac' => $datos['tipo'] === 'accidente',
        ]);

        return $reporte;
    }

    /**
     * Calcula el nivel de riesgo según la matriz OACI.
     */
    public function nivelRiesgo(int $severidad, int $probabilidad): array
    {
        $valor = $severidad * $probabilidad;

        $clasificacion = match (true) {
            $valor <= 4  => 'aceptable',
            $valor <= 9  => 'tolerable',
            default      => 'inaceptable',
        };

        $color = match ($clasificacion) {
            'aceptable'   => 'verde',
            'tolerable'   => 'amarillo',
            'inaceptable' => 'rojo',
        };

        return [
            'valor'         => $valor,
            'clasificacion' => $clasificacion,
            'color'         => $color,
            'requiere_accion'       => $valor >= 5,
            'notificar_uaeac'       => $valor >= 15,
        ];
    }

    /**
     * Asigna acciones correctivas a un reporte.
     */
    public function asignarAcciones(ReporteSms $reporte, array $acciones): void
    {
        foreach ($acciones as $accion) {
            AccionCorrectiva::create([
                'reporte_sms_id' => $reporte->id,
                'descripcion'    => $accion['descripcion'],
                'responsable_id' => $accion['responsable_id'],
                'fecha_limite'   => $accion['fecha_limite'],
                'estado'         => 'pendiente',
            ]);
        }

        $reporte->update(['estado' => 'en_analisis']);
    }

    /**
     * Cierra un reporte SMS verificando que todas las
     * acciones correctivas estén cerradas o verificadas.
     */
    public function cerrarReporte(ReporteSms $reporte, string $observaciones = ''): bool
    {
        $pendientes = $reporte->accionesCorrectivas()
            ->whereNotIn('estado', ['cerrada', 'verificada'])
            ->count();

        if ($pendientes > 0) {
            return false; // No se puede cerrar con acciones pendientes
        }

        $reporte->update([
            'estado'      => 'cerrado',
        ]);

        return true;
    }

    /**
     * KPIs del SMS para el dashboard de dir_ops.
     */
    public function kpis(int $meses = 12): array
    {
        $desde = now()->subMonths($meses)->toDateString();

        $reportes = ReporteSms::where('created_at', '>=', $desde);

        return [
            'total_reportes'           => (clone $reportes)->count(),
            'por_tipo'                 => (clone $reportes)->groupBy('tipo')
                                            ->selectRaw('tipo, COUNT(*) as total')
                                            ->pluck('total', 'tipo'),
            'nivel_riesgo_promedio'    => round((clone $reportes)->avg('nivel_riesgo'), 1),
            'inaceptables'             => (clone $reportes)->where('nivel_riesgo', '>=', 10)->count(),
            'acciones_pendientes'      => AccionCorrectiva::where('estado', 'pendiente')->count(),
            'acciones_vencidas'        => AccionCorrectiva::where('estado', 'pendiente')
                                            ->where('fecha_limite', '<', now()->toDateString())
                                            ->count(),
            'notificados_uaeac'        => (clone $reportes)->where('notificado_uaeac', true)->count(),
            'periodo_meses'            => $meses,
        ];
    }
}
