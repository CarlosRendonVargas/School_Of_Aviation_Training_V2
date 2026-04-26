<?php

namespace App\Services;

use App\Models\Aeronave;
use App\Models\Estudiante;
use App\Models\Instructor;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * ReservaService
 * ──────────────
 * Gestiona la creación y validación de reservas de aeronaves e instructores.
 * Aplica reglas RAC 141 antes de confirmar cualquier reserva.
 */
class ReservaService
{
    /**
     * Valida y crea una reserva.
     * Retorna ['ok' => true, 'reserva' => Reserva] o ['ok' => false, 'errores' => [...]]
     */
    public function crearReserva(array $datos): array
    {
        $errores = $this->validar($datos);

        if (! empty($errores)) {
            return ['ok' => false, 'errores' => $errores];
        }

        $reserva = Reserva::create([
            'aeronave_id'             => $datos['aeronave_id'],
            'estudiante_id'           => $datos['estudiante_id'],
            'instructor_id'           => $datos['instructor_id'] ?? null,
            'fecha'                   => $datos['fecha'],
            'hora_inicio'             => $datos['hora_inicio'],
            'hora_fin'                => $datos['hora_fin'],
            'tipo'                    => $datos['tipo'],
            'objetivos'               => $datos['objetivos'] ?? null,
            'estado'                  => 'pendiente',
            'confirmacion_estudiante' => $datos['confirmacion_estudiante'] ?? 'pendiente',
            'confirmacion_expira'     => $datos['confirmacion_expira'] ?? null,
        ]);

        return ['ok' => true, 'reserva' => $reserva];
    }

    /**
     * Validaciones RAC antes de crear la reserva.
     */
    public function validar(array $datos): array
    {
        $errores = [];

        $aeronave   = Aeronave::find($datos['aeronave_id']);
        $estudiante = Estudiante::find($datos['estudiante_id']);
        $instructor = isset($datos['instructor_id']) ? Instructor::find($datos['instructor_id']) : null;
        $fecha      = Carbon::parse($datos['fecha']);

        // 1. Aeronave disponible
        if (! $aeronave) {
            $errores[] = 'Aeronave no encontrada.';
        } else {
            if (! $aeronave->estaDisponible()) {
                $errores[] = "La aeronave {$aeronave->matricula} no está disponible (estado: {$aeronave->estado}).";
            }
            if (! $aeronave->airworthinessVigente()) {
                $errores[] = "Certificado de aeronavegabilidad de {$aeronave->matricula} vencido (RAC 141.51).";
            }
            if (! $aeronave->seguroVigente()) {
                $errores[] = "Seguro aeronáutico de {$aeronave->matricula} vencido.";
            }
            if ($aeronave->melAbiertos()->exists()) {
                $errores[] = "La aeronave {$aeronave->matricula} tiene ítems MEL abiertos. Verificar limitaciones operacionales.";
            }
        }

        // 2. Estudiante activo con médico vigente (RAC 67)
        if (! $estudiante) {
            $errores[] = 'Estudiante no encontrado.';
        } else {
            if ($estudiante->estado !== 'activo') {
                $errores[] = "El estudiante no está activo en el sistema (estado: {$estudiante->estado}).";
            }
            if (! $estudiante->tieneMedicoVigente()) {
                $errores[] = 'El estudiante no tiene certificado médico aeronáutico vigente (RAC 67).';
            }
        }

        // 3. Instructor con licencia y habilitaciones vigentes (RAC 65)
        if ($instructor) {
            if (! $instructor->activo) {
                $errores[] = 'El instructor no está activo.';
            }
            if (! $instructor->licenciaVigente()) {
                $errores[] = "La licencia del instructor {$instructor->persona?->nombre_completo} está vencida (RAC 65).";
            }
        }

        // 4. Sin conflicto de horario para la aeronave
        if ($aeronave && empty($errores)) {
            $conflicto = Reserva::where('aeronave_id', $datos['aeronave_id'])
                ->where('fecha', $datos['fecha'])
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where(function ($q) use ($datos) {
                    $q->whereBetween('hora_inicio', [$datos['hora_inicio'], $datos['hora_fin']])
                      ->orWhereBetween('hora_fin', [$datos['hora_inicio'], $datos['hora_fin']])
                      ->orWhere(function ($q2) use ($datos) {
                          $q2->where('hora_inicio', '<=', $datos['hora_inicio'])
                             ->where('hora_fin', '>=', $datos['hora_fin']);
                      });
                })
                ->exists();

            if ($conflicto) {
                $errores[] = "La aeronave {$aeronave->matricula} ya tiene una reserva en ese horario.";
            }
        }

        // 5. Sin conflicto de horario para el instructor
        if ($instructor && empty(array_filter($errores, fn($e) => str_contains($e, 'instructor')))) {
            $conflictoInst = Reserva::where('instructor_id', $datos['instructor_id'])
                ->where('fecha', $datos['fecha'])
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->where(function ($q) use ($datos) {
                    $q->whereBetween('hora_inicio', [$datos['hora_inicio'], $datos['hora_fin']])
                      ->orWhereBetween('hora_fin', [$datos['hora_inicio'], $datos['hora_fin']]);
                })
                ->exists();

            if ($conflictoInst) {
                $errores[] = 'El instructor ya tiene una reserva asignada en ese horario.';
            }
        }

        return $errores;
    }

    /**
     * Aeronaves disponibles para un slot de tiempo dado.
     */
    public function aeronaveDisponible(string $fecha, string $horaInicio, string $horaFin): Collection
    {
        $ocupadas = Reserva::where('fecha', $fecha)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->where(function ($q) use ($horaInicio, $horaFin) {
                $q->whereBetween('hora_inicio', [$horaInicio, $horaFin])
                  ->orWhereBetween('hora_fin', [$horaInicio, $horaFin]);
            })
            ->pluck('aeronave_id');

        return Aeronave::disponibles()
            ->conAirworthinessVigente()
            ->whereNotIn('id', $ocupadas)
            ->get();
    }
}
