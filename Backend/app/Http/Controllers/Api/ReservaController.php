<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
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
        ]);

        $resultado = $this->reservaService->crearReserva($request->all());

        if (! $resultado['ok']) {
            return response()->json([
                'ok'      => false,
                'errores' => $resultado['errores'],
            ], 422);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Reserva creada correctamente.',
            'data'    => $resultado['reserva']->load([
                'aeronave:id,matricula,modelo',
                'estudiante.persona:id,nombres,apellidos',
                'instructor.persona:id,nombres,apellidos',
            ]),
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

        return response()->json(['ok' => true, 'data' => $reservas]);
    }
}
