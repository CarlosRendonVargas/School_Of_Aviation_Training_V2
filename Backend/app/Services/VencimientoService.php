<?php

namespace App\Services;

use App\Models\Aeronave;
use App\Models\CertMedico;
use App\Models\CertInstructor;
use App\Models\VencimientoCritico;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * VencimientoService
 * ─────────────────
 * Centraliza la lógica de alertas de vencimiento exigida por la RAC 141.
 *
 * Entidades monitoreadas:
 *  - Aeronaves     → aeronavegabilidad + seguro (RAC 141.51)
 *  - Instructores  → licencia + habilitaciones + médico (RAC 65)
 *  - Estudiantes   → certificado médico aeronáutico (RAC 67)
 *  - Documentos    → MOE, PIA, certificado de escuela (RAC 141.11 / 141.13)
 */
class VencimientoService
{
    /**
     * Días de anticipación por tipo de entidad.
     */
    const DIAS_ALERTA = [
        'aeronave_airworthiness' => 30,
        'aeronave_seguro'        => 30,
        'instructor_licencia'    => 90,
        'instructor_cert'        => 60,
        'estudiante_medico'      => 60,
        'documento_rac'          => 60,
    ];

    /**
     * Devuelve todos los vencimientos próximos en los
     * próximos $dias días, ordenados por urgencia.
     */
    public function proximosVencimientos(int $dias = 90): Collection
    {
        return VencimientoCritico::activos()
            ->whereRaw('fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$dias])
            ->where('fecha_vencimiento', '>=', now()->toDateString())
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    /**
     * Devuelve vencimientos ya vencidos (días_restantes < 0).
     * Estos bloquean operaciones según RAC.
     */
    public function vencidos(): Collection
    {
        return VencimientoCritico::activos()
            ->where('fecha_vencimiento', '<', now()->toDateString())
            ->orderByDesc('fecha_vencimiento')
            ->get();
    }

    /**
     * Sincroniza la tabla vencimientos_criticos con el estado
     * actual de la base de datos. Ejecutar como job diario.
     */
    public function sincronizar(): array
    {
        $creados    = 0;
        $actualizados = 0;

        // ── Aeronaves ──────────────────────────────────────────
        Aeronave::where('estado', '!=', 'baja')->each(function (Aeronave $a) use (&$creados, &$actualizados) {
            // Aeronavegabilidad
            $this->upsertVencimiento(
                'aeronave', $a->id,
                "Aeronavegabilidad {$a->matricula} ({$a->modelo})",
                $a->venc_airworthiness,
                self::DIAS_ALERTA['aeronave_airworthiness'],
                $creados, $actualizados
            );
            // Seguro
            $this->upsertVencimiento(
                'aeronave', $a->id,
                "Seguro aeronáutico {$a->matricula}",
                $a->venc_seguro,
                self::DIAS_ALERTA['aeronave_seguro'],
                $creados, $actualizados
            );
        });

        // ── Certificados médicos de estudiantes ────────────────
        CertMedico::whereDate('fecha_vencimiento', '>=', now()->subMonth())
            ->with('estudiante.persona')
            ->each(function (CertMedico $cm) use (&$creados, &$actualizados) {
                $nombre = $cm->estudiante?->persona?->nombre_completo ?? "Est. #{$cm->estudiante_id}";
                $this->upsertVencimiento(
                    'estudiante', $cm->estudiante_id,
                    "Cert. médico {$cm->tipo} — {$nombre}",
                    $cm->fecha_vencimiento,
                    self::DIAS_ALERTA['estudiante_medico'],
                    $creados, $actualizados
                );
            });

        // ── Certificaciones de instructores ────────────────────
        CertInstructor::where('activo', true)
            ->whereDate('fecha_vencimiento', '>=', now()->subMonth())
            ->with('instructor.persona')
            ->each(function (CertInstructor $ci) use (&$creados, &$actualizados) {
                $nombre = $ci->instructor?->persona?->nombre_completo ?? "Instr. #{$ci->instructor_id}";
                $this->upsertVencimiento(
                    'instructor', $ci->instructor_id,
                    "{$ci->descripcion} — {$nombre}",
                    $ci->fecha_vencimiento,
                    self::DIAS_ALERTA['instructor_cert'],
                    $creados, $actualizados
                );
            });

        // ── Documentos RAC (MOE, PIA, etc.) ─────────────────────
        \App\Models\DocumentoRac::where('vigente', true)
            ->whereNotNull('fecha_documento')
            ->each(function ($doc) use (&$creados, &$actualizados) {
                $this->upsertVencimiento(
                    'documento', $doc->id,
                    "Documento RAC: {$doc->titulo} ({$doc->numero})",
                    Carbon::parse($doc->fecha_documento)->addYears(2), // Estimación si no hay fecha_vencimiento
                    self::DIAS_ALERTA['documento_rac'],
                    $creados, $actualizados
                );
            });

        return [
            'creados'      => $creados,
            'actualizados' => $actualizados,
            'ejecutado_en' => now()->toDateTimeString(),
        ];
    }

    /* ─── Privado ─── */

    private function upsertVencimiento(
        string $tipoEntidad,
        int    $entidadId,
        string $descripcion,
        mixed  $fechaVencimiento,
        int    $diasAlerta,
        int    &$creados,
        int    &$actualizados
    ): void {
        $fecha = $fechaVencimiento instanceof Carbon
            ? $fechaVencimiento
            : Carbon::parse($fechaVencimiento);

        $diasRestantes = now()->diffInDays($fecha, false);

        $nivel = match (true) {
            $diasRestantes <= 0  => 'critico',
            $diasRestantes <= 15 => 'critico',
            $diasRestantes <= $diasAlerta => 'advertencia',
            default              => 'info',
        };

        $existing = VencimientoCritico::where('tipo_entidad', $tipoEntidad)
            ->where('entidad_id', $entidadId)
            ->where('descripcion', $descripcion)
            ->first();

        if ($existing) {
            $existing->update([
                'fecha_vencimiento' => $fecha->toDateString(),
                'nivel_critico'     => $nivel,
                'dias_alerta'       => $diasAlerta,
            ]);
            $actualizados++;
        } else {
            VencimientoCritico::create([
                'tipo_entidad'      => $tipoEntidad,
                'entidad_id'        => $entidadId,
                'descripcion'       => $descripcion,
                'fecha_vencimiento' => $fecha->toDateString(),
                'dias_alerta'       => $diasAlerta,
                'nivel_critico'     => $nivel,
                'activo'            => true,
            ]);
            $creados++;
        }
    }
}
