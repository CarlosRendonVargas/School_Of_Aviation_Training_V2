<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\BitacoraVuelo;
use Illuminate\Support\Collection;

/**
 * HorasVueloService
 * ─────────────────
 * Calcula y verifica el cumplimiento de horas mínimas
 * exigidas por el RAC 61 para cada estudiante y programa.
 *
 * Usado por: Dashboard del estudiante, instructor y dir_ops.
 * Referencia: RAC 61.109 (PPL), 61.129 (CPL), 61.159 (ATPL)
 */
class HorasVueloService
{
    /**
     * Resumen completo de horas acumuladas vs. requeridas RAC 61.
     * Devuelve array con porcentaje de avance por categoría.
     */
    public function resumen(Estudiante $estudiante): array
    {
        $programa = $estudiante->programa ?? $estudiante->load('programa')->programa;

        // Sin programa asignado: devolver estructura vacía sin crashear
        if (! $programa) {
            return [
                'estudiante_id'     => $estudiante->id,
                'num_expediente'    => $estudiante->num_expediente,
                'programa'          => null,
                'total_vuelos'      => 0,
                'categorias'        => [],
                'listo_para_examen' => false,
                'tiene_medico'      => $estudiante->tieneMedicoVigente(),
            ];
        }

        $acumulado = BitacoraVuelo::where('estudiante_id', $estudiante->id)
            ->selectRaw('
                COALESCE(SUM(horas_totales), 0)    AS total,
                COALESCE(SUM(horas_dual), 0)       AS `dual`,
                COALESCE(SUM(horas_solo), 0)       AS solo,
                COALESCE(SUM(horas_noche), 0)      AS noche,
                COALESCE(SUM(horas_ifr), 0)        AS ifr,
                COALESCE(SUM(horas_simulador), 0)  AS simulador,
                COUNT(*)                           AS total_vuelos
            ')
            ->first();

        $categorias = [
            'vuelo_total' => [
                'label'     => 'Vuelo total',
                'acumulado' => (float) $acumulado->total,
                'requerido' => (float) $programa->horas_vuelo_min,
                'rac'       => $programa->rac_referencia,
            ],
            'dual' => [
                'label'     => 'Instrucción dual',
                'acumulado' => (float) $acumulado->dual,
                'requerido' => (float) $programa->horas_dual_min,
                'rac'       => $programa->rac_referencia,
            ],
            'solo' => [
                'label'     => 'Vuelo solo',
                'acumulado' => (float) $acumulado->solo,
                'requerido' => (float) $programa->horas_solo_min,
                'rac'       => $programa->rac_referencia,
            ],
            'noche' => [
                'label'     => 'Vuelo nocturno',
                'acumulado' => (float) $acumulado->noche,
                'requerido' => (float) ($programa->horas_noche_min ?? 0),
                'rac'       => $programa->rac_referencia,
            ],
            'ifr' => [
                'label'     => 'Instrumentos (IFR)',
                'acumulado' => (float) $acumulado->ifr,
                'requerido' => (float) ($programa->horas_ifr_min ?? 0),
                'rac'       => $programa->rac_referencia,
            ],
            'simulador' => [
                'label'     => 'Simulador (FTD/FFS)',
                'acumulado' => (float) $acumulado->simulador,
                'limite_max'=> (float) ($programa->horas_sim_max ?? 0),
                'rac'       => $programa->rac_referencia,
            ],
        ];

        // Calcular porcentaje y si está cumplido
        foreach ($categorias as $key => &$cat) {
            $requerido = $cat['requerido'] ?? ($cat['limite_max'] ?? 0);
            if ($requerido > 0) {
                $cat['porcentaje'] = min(100, round(($cat['acumulado'] / $requerido) * 100, 1));
                $cat['cumplido']   = $cat['acumulado'] >= $requerido;
                $cat['faltante']   = max(0, $requerido - $cat['acumulado']);
            } else {
                $cat['porcentaje'] = 100;
                $cat['cumplido']   = true;
                $cat['faltante']   = 0;
            }
        }

        $todoCumplido = collect($categorias)
            ->filter(fn($c) => ($c['requerido'] ?? 0) > 0)
            ->every(fn($c) => $c['cumplido']);

        return [
            'estudiante_id'   => $estudiante->id,
            'num_expediente'  => $estudiante->num_expediente,
            'programa'        => $programa->nombre,
            'total_vuelos'    => (int) $acumulado->total_vuelos,
            'categorias'      => $categorias,
            'listo_para_examen' => $todoCumplido && $estudiante->tieneMedicoVigente(),
            'tiene_medico'    => $estudiante->tieneMedicoVigente(),
        ];
    }

    /**
     * Historial de horas por mes para gráficas del dashboard.
     * Devuelve los últimos 12 meses.
     */
    public function historialMensual(Estudiante $estudiante): Collection
    {
        return BitacoraVuelo::where('estudiante_id', $estudiante->id)
            ->where('fecha', '>=', now()->subYear()->toDateString())
            ->selectRaw("
                DATE_FORMAT(fecha, '%Y-%m') AS mes,
                SUM(horas_totales)          AS horas,
                SUM(horas_dual)             AS `dual`,
                SUM(horas_solo)             AS solo,
                COUNT(*)                    AS vuelos
            ")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
    }

    /**
     * Valida si un estudiante cumple todos los requisitos
     * RAC 61 para presentarse al examen de la UAEAC.
     * Retorna array con validaciones y mensajes.
     */
    public function validarRequisitosExamen(Estudiante $estudiante): array
    {
        if (! $estudiante->programa) {
            return [
                'aprobado'     => false,
                'validaciones' => [['criterio' => 'Programa', 'cumplido' => false, 'mensaje' => '✘ El estudiante no tiene programa asignado.', 'rac' => 'RAC 141']],
                'programa'     => null,
                'expediente'   => $estudiante->num_expediente,
            ];
        }

        $resumen    = $this->resumen($estudiante);
        $validaciones = [];
        $aprobado   = true;

        foreach ($resumen['categorias'] as $key => $cat) {
            if (($cat['requerido'] ?? 0) > 0) {
                $ok = $cat['cumplido'];
                if (! $ok) $aprobado = false;

                $validaciones[] = [
                    'criterio'  => $cat['label'],
                    'cumplido'  => $ok,
                    'acumulado' => $cat['acumulado'],
                    'requerido' => $cat['requerido'],
                    'faltante'  => $cat['faltante'],
                    'rac'       => $cat['rac'],
                    'mensaje'   => $ok
                        ? "✔ {$cat['label']}: {$cat['acumulado']}h de {$cat['requerido']}h requeridas"
                        : "✘ Faltan {$cat['faltante']}h de {$cat['label']} ({$cat['acumulado']}h / {$cat['requerido']}h)",
                ];
            }
        }

        // Verificar médico vigente (RAC 67)
        $tieneMedico = $estudiante->tieneMedicoVigente();
        if (! $tieneMedico) $aprobado = false;
        $validaciones[] = [
            'criterio'  => 'Certificado médico aeronáutico',
            'cumplido'  => $tieneMedico,
            'rac'       => 'RAC 67',
            'mensaje'   => $tieneMedico
                ? '✔ Certificado médico vigente'
                : '✘ Sin certificado médico vigente (RAC 67)',
        ];

        return [
            'aprobado'      => $aprobado,
            'validaciones'  => $validaciones,
            'programa'      => $estudiante->programa->nombre,
            'expediente'    => $estudiante->num_expediente,
        ];
    }
}
