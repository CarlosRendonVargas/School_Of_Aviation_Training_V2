<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\Mensaje;
use App\Services\ReservaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReservaController extends Controller
{
    public function __construct(private ReservaService $reservaService) {}

    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $rol     = $usuario->rol?->nombre;

        $query = Reserva::with([
            'aeronave:id,matricula,modelo',
            'estudiante.persona:id,nombres,apellidos',
            'instructor.persona:id,nombres,apellidos',
        ])->orderByDesc('fecha')->orderByDesc('hora_inicio');

        if ($rol === 'estudiante') {
            $query->where('estudiante_id', $usuario->persona?->estudiante?->id);
        } elseif ($rol === 'instructor') {
            $query->where('instructor_id', $usuario->persona?->instructor?->id);
        }

        $query
            ->when($request->fecha,       fn($q, $v) => $q->where('fecha', $v))
            ->when($request->aeronave_id, fn($q, $v) => $q->where('aeronave_id', $v))
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v));

        return response()->json(['ok' => true, 'data' => $query->paginate(20)]);
    }

    public function show($id): JsonResponse
    {
        $reserva = Reserva::with([
            'aeronave', 'estudiante.persona', 'instructor.persona',
        ])->findOrFail($id);

        return response()->json(['ok' => true, 'data' => $reserva]);
    }

    /**
     * Crea una reserva aplicando todas las validaciones RAC 141:
     * - Aeronave disponible, aeronavegabilidad vigente, sin MEL bloqueante
     * - Estudiante activo con médico vigente (RAC 67)
     * - Instructor con licencia vigente (RAC 65)
     * - Sin conflicto de horario
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'aeronave_id'   => 'required|exists:aeronaves,id',
            'estudiante_id' => 'required|exists:estudiantes,id',
            'instructor_id' => 'nullable|exists:instructores,id',
            'fecha'         => 'required|date|after_or_equal:today',
            'hora_inicio'   => 'required|date_format:H:i',
            'hora_fin'      => 'required|date_format:H:i|after:hora_inicio',
            'tipo'          => 'required|in:instruccion,solo,simulador',
            'objetivos'     => 'nullable|string|max:1000',
        ]);

        $resultado = $this->reservaService->crearReserva(
            array_merge($request->all(), [
                'confirmacion_estudiante' => 'pendiente',
                'confirmacion_expira'     => now()->addHours(24),
            ])
        );

        if (! $resultado['ok']) {
            return response()->json([
                'ok'      => false,
                'errores' => $resultado['errores'],
            ], 422);
        }

        $reserva = $resultado['reserva']->load([
            'aeronave:id,matricula,modelo',
            'estudiante.persona:id,nombres,apellidos',
            'instructor.persona:id,nombres,apellidos',
        ]);

        // Notificar al estudiante vía mensaje interno
        $usuarioEstudiante = $reserva->estudiante?->persona?->usuario;
        if ($usuarioEstudiante) {
            $fecha      = $reserva->fecha->format('d/m/Y');
            $aeronave   = $reserva->aeronave->matricula . ' (' . $reserva->aeronave->modelo . ')';
            $instructor = $request->user()->nombre_completo ?? $request->user()->email;

            Mensaje::create([
                'remitente_id'    => $request->user()->id,
                'destinatario_id' => $usuarioEstudiante->id,
                'asunto'          => "Plan de vuelo programado — {$fecha}",
                'cuerpo'          =>
                    "Tu instructor ha programado una actividad de vuelo:\n\n" .
                    "📅 Fecha: {$fecha}\n" .
                    "⏰ Hora: {$reserva->hora_inicio->format('H:i')} – {$reserva->hora_fin->format('H:i')}\n" .
                    "✈ Aeronave: {$aeronave}\n" .
                    "👨‍✈️ Instructor: {$instructor}\n" .
                    ($reserva->objetivos ? "\n🎯 Objetivos: {$reserva->objetivos}\n" : '') .
                    "\nTienes 24 horas para confirmar desde el sistema. Si no respondes, la reserva se cancelará automáticamente.",
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Reserva creada y estudiante notificado.',
            'data'    => $reserva,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $reserva = Reserva::findOrFail($id);

        if (! in_array($reserva->estado, ['pendiente'])) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Solo se pueden editar reservas en estado pendiente.',
            ], 422);
        }

        $data = $request->validate([
            'fecha'      => 'sometimes|date|after_or_equal:today',
            'hora_inicio'=> 'sometimes|date_format:H:i',
            'hora_fin'   => 'sometimes|date_format:H:i',
            'tipo'       => 'sometimes|in:instruccion,solo,simulador',
        ]);

        $reserva->update($data);

        return response()->json(['ok' => true, 'data' => $reserva]);
    }

    public function confirmar(Request $request, $id): JsonResponse
    {
        $reserva = Reserva::findOrFail($id);

        if ($reserva->estado !== 'pendiente') {
            return response()->json([
                'ok' => false, 'mensaje' => 'Solo se pueden confirmar reservas pendientes.',
            ], 422);
        }

        $reserva->update(['estado' => 'confirmada']);

        return response()->json(['ok' => true, 'mensaje' => 'Reserva confirmada.', 'data' => $reserva]);
    }

    public function cancelar(Request $request, $id): JsonResponse
    {
        $reserva = Reserva::findOrFail($id);
        $request->validate(['motivo' => 'required|string|min:5']);

        $reserva->update([
            'estado'              => 'cancelada',
            'motivo_cancelacion'  => $request->motivo,
        ]);

        return response()->json(['ok' => true, 'mensaje' => 'Reserva cancelada.', 'data' => $reserva]);
    }

    /**
     * GET /api/v1/reservas/cronograma
     * Devuelve las reservas pendientes de confirmación del estudiante autenticado.
     */
    public function cronograma(Request $request): JsonResponse
    {
        $user       = $request->user();
        $estudiante = $user->persona?->estudiante;

        if (! $estudiante) {
            return response()->json(['ok' => true, 'data' => []]);
        }

        // Auto-cancelar las expiradas antes de devolver
        Reserva::where('estudiante_id', $estudiante->id)
            ->where('confirmacion_estudiante', 'pendiente')
            ->where('confirmacion_expira', '<', now())
            ->update([
                'estado'               => 'cancelada',
                'confirmacion_estudiante' => 'rechazada',
                'motivo_cancelacion'   => 'Sin confirmación del estudiante en 24 horas.',
            ]);

        $reservas = Reserva::with([
            'aeronave:id,matricula,modelo',
            'instructor.persona:id,nombres,apellidos',
        ])
            ->where('estudiante_id', $estudiante->id)
            ->whereIn('estado', ['pendiente', 'confirmada'])
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return response()->json(['ok' => true, 'data' => $reservas]);
    }

    /**
     * POST /api/v1/reservas/{id}/aceptar
     * El estudiante acepta el plan de vuelo.
     */
    public function aceptarVuelo(Request $request, int $id): JsonResponse
    {
        $user       = $request->user();
        $estudiante = $user->persona?->estudiante;

        $reserva = Reserva::where('estudiante_id', $estudiante?->id)
            ->where('confirmacion_estudiante', 'pendiente')
            ->where('confirmacion_expira', '>', now())
            ->findOrFail($id);

        $reserva->update([
            'confirmacion_estudiante' => 'aceptada',
            'estado'                  => 'confirmada',
        ]);

        // Notificar al instructor
        $usuarioInstructor = $reserva->instructor?->persona?->usuario;
        if ($usuarioInstructor) {
            Mensaje::create([
                'remitente_id'    => $user->id,
                'destinatario_id' => $usuarioInstructor->id,
                'asunto'          => '✅ Plan de vuelo aceptado — ' . $reserva->fecha->format('d/m/Y'),
                'cuerpo'          =>
                    $user->nombre_completo . " ha aceptado el plan de vuelo programado para el " .
                    $reserva->fecha->format('d/m/Y') . ' a las ' . $reserva->hora_inicio->format('H:i') . '.',
            ]);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Plan de vuelo aceptado. ¡Hasta el vuelo!']);
    }

    /**
     * POST /api/v1/reservas/{id}/rechazar
     * El estudiante rechaza el plan de vuelo.
     */
    public function rechazarVuelo(Request $request, int $id): JsonResponse
    {
        $user       = $request->user();
        $estudiante = $user->persona?->estudiante;

        $request->validate(['motivo' => 'nullable|string|max:300']);

        $reserva = Reserva::where('estudiante_id', $estudiante?->id)
            ->where('confirmacion_estudiante', 'pendiente')
            ->findOrFail($id);

        $motivo = $request->motivo ?: 'El estudiante indicó que no puede asistir.';

        $reserva->update([
            'confirmacion_estudiante' => 'rechazada',
            'estado'                  => 'cancelada',
            'motivo_cancelacion'      => $motivo,
        ]);

        // Notificar al instructor
        $usuarioInstructor = $reserva->instructor?->persona?->usuario;
        if ($usuarioInstructor) {
            Mensaje::create([
                'remitente_id'    => $user->id,
                'destinatario_id' => $usuarioInstructor->id,
                'asunto'          => '❌ Plan de vuelo rechazado — ' . $reserva->fecha->format('d/m/Y'),
                'cuerpo'          =>
                    $user->nombre_completo . " no puede asistir al vuelo del " .
                    $reserva->fecha->format('d/m/Y') . " a las " . $reserva->hora_inicio->format('H:i') . ".\n\n" .
                    "Motivo: {$motivo}",
            ]);
        }

        return response()->json(['ok' => true, 'mensaje' => 'Plan de vuelo rechazado. Se notificó al instructor.']);
    }

    /**
     * GET /api/v1/reservas/disponibilidad?fecha=&hora_inicio=&hora_fin=
     * Devuelve aeronaves e instructores disponibles en ese slot.
     */
    public function disponibilidad(Request $request): JsonResponse
    {
        $request->validate([
            'fecha'      => 'required|date',
            'hora_inicio'=> 'required|date_format:H:i',
            'hora_fin'   => 'required|date_format:H:i',
        ]);

        $aeronaves   = $this->reservaService->aeronaveDisponible(
            $request->fecha, $request->hora_inicio, $request->hora_fin
        );

        return response()->json([
            'ok'   => true,
            'data' => [
                'aeronaves'  => $aeronaves,
                'fecha'      => $request->fecha,
                'hora_inicio'=> $request->hora_inicio,
                'hora_fin'   => $request->hora_fin,
            ],
        ]);
    }

    /**
     * GET /api/v1/reservas/calendario?mes=2025-06
     */
    public function calendario(Request $request): JsonResponse
    {
        $mes = $request->get('mes', now()->format('Y-m'));
        [$anio, $m] = explode('-', $mes);

        $reservas = Reserva::whereYear('fecha', $anio)
            ->whereMonth('fecha', $m)
            ->whereIn('estado', ['pendiente', 'confirmada', 'completada'])
            ->with([
                'aeronave:id,matricula,modelo',
                'estudiante.persona:id,nombres,apellidos',
                'instructor.persona:id,nombres,apellidos',
            ])
            ->orderBy('fecha')->orderBy('hora_inicio')
            ->get();

        $usuario = $request->user();
        $rol     = $usuario->rol?->nombre;

        $clasesQuery = \App\Models\Materia::whereNotNull('sesion_viva_inicio')
            ->whereYear('sesion_viva_inicio', $anio)
            ->whereMonth('sesion_viva_inicio', $m);

        // Si es estudiante, filtrar solo las materias de su programa
        if ($rol === 'estudiante') {
            $estudiante = $usuario->persona?->estudiante;
            if ($estudiante && $estudiante->programa_id) {
                $clasesQuery->whereHas('etapa', function($q) use ($estudiante) {
                    $q->where('programa_id', $estudiante->programa_id);
                });
            }
        }

        $clasesVirtuales = $clasesQuery->get()
            ->map(function($m) {
                return [
                    'id' => 'vc-' . $m->id,
                    'fecha' => $m->sesion_viva_inicio->toDateString(),
                    'hora_inicio' => $m->sesion_viva_inicio->toTimeString(),
                    'hora_fin' => $m->sesion_viva_fin ? $m->sesion_viva_fin->toTimeString() : null,
                    'estado' => 'virtual',
                    'aeronave' => ['matricula' => 'LIVE', 'modelo' => 'Aula Virtual'],
                    'estudiante' => ['persona' => ['nombres' => 'Clase:', 'apellidos' => $m->nombre]],
                    'tipo' => 'virtual'
                ];
            });

        $merge = $reservas->concat($clasesVirtuales);

        return response()->json(['ok' => true, 'data' => $merge]);
    }
}
