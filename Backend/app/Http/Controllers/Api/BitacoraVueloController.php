<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BitacoraVuelo;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BitacoraVueloController extends Controller
{
    /* ─── Listado ─── */

    public function index(Request $request): JsonResponse
    {
        $usuario = $request->user();
        $rol     = $usuario->rol?->nombre;

        $query = BitacoraVuelo::with([
            'estudiante.persona:id,nombres,apellidos',
            'instructor.persona:id,nombres,apellidos',
            'aeronave:id,matricula,modelo',
        ])->orderByDesc('fecha');

        // Estudiante solo ve las suyas
        if ($rol === 'estudiante') {
            $estudianteId = $usuario->persona?->estudiante?->id;
            $query->where('estudiante_id', $estudianteId);
        }

        // Instructor ve las de sus estudiantes
        if ($rol === 'instructor') {
            $instructorId = $usuario->persona?->instructor?->id;
            $query->where('instructor_id', $instructorId);
        }

        // Filtros opcionales
        $query
            ->when($request->estudiante_id, fn($q, $v) => $q->where('estudiante_id', $v))
            ->when($request->aeronave_id,   fn($q, $v) => $q->where('aeronave_id', $v))
            ->when($request->fecha_desde,   fn($q, $v) => $q->where('fecha', '>=', $v))
            ->when($request->fecha_hasta,   fn($q, $v) => $q->where('fecha', '<=', $v))
            ->when($request->tipo_vuelo,    fn($q, $v) => $q->where('tipo_vuelo', $v));

        return response()->json([
            'ok'   => true,
            'data' => $query->paginate($request->per_page ?? 20),
        ]);
    }

    /* ─── Detalle ─── */

    public function show($id): JsonResponse
    {
        $bitacora = BitacoraVuelo::with([
            'estudiante.persona', 'instructor.persona', 'aeronave', 'reserva',
        ])->findOrFail($id);

        $this->authorize('view', $bitacora);

        return response()->json(['ok' => true, 'data' => $bitacora]);
    }

    /* ─── Crear entrada de bitácora ─── */

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BitacoraVuelo::class);

        $data = $request->validate([
            'estudiante_id'   => 'required|exists:estudiantes,id',
            'instructor_id'   => 'nullable|exists:instructores,id',
            'aeronave_id'     => 'required|exists:aeronaves,id',
            'reserva_id'      => 'nullable|exists:reservas,id',
            'fecha'           => 'required|date|before_or_equal:today',
            'hora_salida'     => 'required|date_format:H:i',
            'hora_llegada'    => 'required|date_format:H:i|after:hora_salida',
            'origen_icao'     => 'required|string|size:4',
            'destino_icao'    => 'required|string|size:4',
            'horas_totales'   => 'required|numeric|min:0.1|max:24',
            'horas_dual'      => 'required|numeric|min:0',
            'horas_solo'      => 'required|numeric|min:0',
            'horas_noche'     => 'required|numeric|min:0',
            'horas_ifr'       => 'required|numeric|min:0',
            'horas_simulador' => 'required|numeric|min:0',
            'tipo_vuelo'      => 'required|in:local,navegacion,noche,ifr,sim',
            'condiciones_vmc' => 'required|boolean',
            'aterrizajes'     => 'required|integer|min:0',
            'observaciones'   => 'nullable|string',
        ]);

        // Validar que horas parciales no superen el total
        $parciales = $data['horas_dual'] + $data['horas_solo'];
        if ($parciales > $data['horas_totales'] + 0.1) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'La suma de horas dual + solo no puede superar horas totales.',
            ], 422);
        }

        // Obtener firma del instructor si aplica
        $instructor = $request->user()->persona?->instructor;
        if ($instructor && $instructor->id === ($data['instructor_id'] ?? null)) {
            $data['firma_instructor'] = 'signed:' . $instructor->id . ':' . now()->timestamp;
        }

        DB::transaction(function () use ($data, &$bitacora) {
            $bitacora = BitacoraVuelo::create($data);

            // Actualizar horas de la aeronave
            $aeronave = \App\Models\Aeronave::find($data['aeronave_id']);
            $aeronave->increment('horas_celula_total', $data['horas_totales']);
            $aeronave->increment('horas_motor_total',  $data['horas_totales']);
            $aeronave->increment('horas_desde_oh',     $data['horas_totales']);

            // Actualizar horas del instructor
            if ($data['instructor_id'] ?? null) {
                \App\Models\Instructor::find($data['instructor_id'])
                    ->increment('horas_instruccion', $data['horas_totales']);
            }

            // Marcar reserva como completada
            if ($data['reserva_id'] ?? null) {
                \App\Models\Reserva::find($data['reserva_id'])
                    ?->update(['estado' => 'completada']);
            }
        });

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Bitácora registrada (RAC 91.417).',
            'data'    => $bitacora->load(['estudiante.persona', 'aeronave']),
        ], 201);
    }

    /* ─── Actualizar ─── */

    public function update(Request $request, $id): JsonResponse
    {
        $bitacora = BitacoraVuelo::findOrFail($id);
        $this->authorize('update', $bitacora);

        $data = $request->validate([
            'observaciones' => 'sometimes|nullable|string',
            'aterrizajes'   => 'sometimes|integer|min:0',
        ]);

        $bitacora->update($data);

        return response()->json(['ok' => true, 'data' => $bitacora]);
    }

    /* ─── Firma digital ─── */

    public function firmar(Request $request, $id): JsonResponse
    {
        $bitacora = BitacoraVuelo::findOrFail($id);
        $this->authorize('firmar', $bitacora);

        $usuario = $request->user();
        $rol     = $usuario->rol?->nombre;
        $ts      = now()->timestamp;

        if ($rol === 'instructor' || $rol === 'admin' || $rol === 'dir_ops') {
            $idFirma = ($rol === 'instructor') ? $usuario->persona?->instructor?->id : $usuario->id;
            $bitacora->update([
                'firma_instructor' => "signed:{$idFirma}:{$ts}",
            ]);
        } elseif ($rol === 'estudiante') {
            $estudianteId = $usuario->persona?->estudiante?->id;
            $bitacora->update([
                'firma_estudiante' => "signed:{$estudianteId}:{$ts}",
            ]);
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Bitácora firmada digitalmente.',
            'data'    => $bitacora->fresh(),
        ]);
    }

    /* ─── Exportar PDF RAC 141.77 ─── */

    public function exportarPdf($id): JsonResponse
    {
        $bitacora = BitacoraVuelo::with([
            'estudiante.persona', 'instructor.persona', 'aeronave',
        ])->findOrFail($id);

        $this->authorize('view', $bitacora);

        // TODO: Integrar con barryvdh/laravel-dompdf
        // return PDF::loadView('pdf.bitacora', compact('bitacora'))->download("bitacora-{$id}.pdf");

        return response()->json([
            'ok'      => true,
            'mensaje' => 'PDF listo para generación.',
            'data'    => $bitacora,
        ]);
    }
}
