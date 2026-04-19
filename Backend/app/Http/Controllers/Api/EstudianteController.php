<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use App\Models\NotaAcademica;
use App\Models\CertMedico;
use App\Services\HorasVueloService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EstudianteController extends Controller
{
    public function __construct(private HorasVueloService $horasService) {}

    /* ─── Listado ─── */

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Estudiante::class);

        $query = Estudiante::with(['persona', 'programa:id,codigo,nombre,tipo'])
            ->when($request->estado,      fn($q, $v) => $q->where('estado', $v))
            ->when($request->programa_id, fn($q, $v) => $q->where('programa_id', $v))
            ->when($request->buscar, function ($q, $v) {
                $q->whereHas('persona', fn($p) =>
                    $p->where('nombres', 'like', "%{$v}%")
                      ->orWhere('apellidos', 'like', "%{$v}%")
                      ->orWhere('num_documento', 'like', "%{$v}%")
                );
            });

        return response()->json([
            'ok'   => true,
            'data' => $query->orderBy('id', 'desc')->paginate($request->per_page ?? 20),
        ]);
    }

    /* ─── Detalle ─── */

    public function show($id): JsonResponse
    {
        $estudiante = Estudiante::with([
            'persona', 'programa', 'etapaActual',
        ])->findOrFail($id);

        $this->authorize('view', $estudiante);

        return response()->json(['ok' => true, 'data' => $estudiante]);
    }

    /* ─── Crear ─── */

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Estudiante::class);

        // Required data for student + basic person
        $data = $request->validate([
            'persona_id'      => 'nullable|exists:personas,id|unique:estudiantes,persona_id',
            'nombres'         => 'required_without:persona_id|string',
            'apellidos'       => 'required_without:persona_id|string',
            'tipo_documento'  => 'required_without:persona_id|string',
            'num_documento'   => 'required_without:persona_id|string',
            'fecha_nacimiento'=> 'required_without:persona_id|date',
            'programa_id'     => 'required|exists:programas,id',
            'fecha_ingreso'   => 'required|date',
            'observaciones'   => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            if (!empty($data['persona_id'])) {
                $persona = \App\Models\Persona::findOrFail($data['persona_id']);
            } else {
                $persona = \App\Models\Persona::create([
                    'nombres'          => $data['nombres'],
                    'apellidos'        => $data['apellidos'],
                    'tipo_documento'   => $data['tipo_documento'],
                    'num_documento'    => $data['num_documento'],
                    'fecha_nacimiento' => $data['fecha_nacimiento'],
                ]);
            }

            $estudiante = Estudiante::create([
                'persona_id'     => $persona->id,
                'programa_id'    => $data['programa_id'],
                'fecha_ingreso'  => $data['fecha_ingreso'],
                'num_expediente' => $this->generarExpediente(),
                'estado'         => 'activo',
                'observaciones'  => $data['observaciones'] ?? null,
            ]);
            
            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'ok'      => true,
                'mensaje' => 'Estudiante registrado. Expediente: ' . $estudiante->num_expediente,
                'data'    => $estudiante->load(['persona', 'programa']),
            ], 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            throw $e;
        }
    }

    /* ─── Actualizar ─── */

    public function update(Request $request, $id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('update', $estudiante);

        $data = $request->validate([
            'estado'         => 'sometimes|in:activo,suspendido,graduado,retirado',
            'etapa_actual_id'=> 'sometimes|nullable|exists:etapas,id',
            'observaciones'  => 'sometimes|nullable|string',
        ]);

        $estudiante->update($data);

        return response()->json(['ok' => true, 'data' => $estudiante]);
    }

    /* ─── Expediente completo RAC 141.77 ─── */

    public function expediente($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('verExpediente', $estudiante);

        $estudiante->load([
            'persona', 'programa', 'etapaActual',
            'certMedicos',
            'notas.materia',
            'bitacoras' => fn($q) => $q->orderByDesc('fecha')->limit(50),
        ]);

        return response()->json([
            'ok'   => true,
            'data' => [
                'estudiante'    => $estudiante,
                'progreso'      => $this->horasService->resumen($estudiante),
                'validacion_examen' => $this->horasService->validarRequisitosExamen($estudiante),
            ],
        ]);
    }

    /* ─── Progreso de horas RAC 61 ─── */

    public function progresoHoras($id): JsonResponse
    {
        $estudiante = Estudiante::with('programa')->findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => $this->horasService->resumen($estudiante),
        ]);
    }

    /* ─── Historial mensual de horas ─── */

    public function historialHoras($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => $this->horasService->historialMensual($estudiante),
        ]);
    }

    /* ─── Validar requisitos examen UAEAC ─── */

    public function validarExamen($id): JsonResponse
    {
        $estudiante = Estudiante::with('programa')->findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => $this->horasService->validarRequisitosExamen($estudiante),
        ]);
    }

    /* ─── Notas académicas ─── */

    public function notas($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => NotaAcademica::where('estudiante_id', $id)
                ->with(['materia:id,codigo,nombre', 'instructor.persona:id,nombres,apellidos'])
                ->orderByDesc('fecha_evaluacion')
                ->get(),
        ]);
    }

    public function storeNota(Request $request): JsonResponse
    {
        $data = $request->validate([
            'estudiante_id'   => 'required|exists:estudiantes,id',
            'materia_id'      => 'required|exists:materias,id',
            'nota'            => 'required|numeric|min:0|max:100',
            'fecha_evaluacion'=> 'required|date',
            'observaciones'   => 'nullable|string',
        ]);

        $materia  = \App\Models\Materia::findOrFail($data['materia_id']);
        $instructor = $request->user()->persona?->instructor;

        $data['instructor_id'] = $instructor?->id;
        $data['aprobado']      = $data['nota'] >= $materia->nota_minima;
        $data['intento_num']   = NotaAcademica::where('estudiante_id', $data['estudiante_id'])
                                    ->where('materia_id', $data['materia_id'])
                                    ->count() + 1;

        $nota = NotaAcademica::create($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => $data['aprobado'] ? 'Materia aprobada.' : 'Materia no aprobada.',
            'data'    => $nota,
        ], 201);
    }

    /* ─── Certificados médicos RAC 67 ─── */

    public function certMedicos($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => CertMedico::where('estudiante_id', $id)
                ->orderByDesc('fecha_emision')->get(),
        ]);
    }

    public function storeCertMedico(Request $request, $id): JsonResponse
    {
        Estudiante::findOrFail($id);

        $data = $request->validate([
            'tipo'               => 'required|in:clase_1,clase_2,clase_3',
            'numero_certificado' => 'required|string',
            'fecha_emision'      => 'required|date',
            'fecha_vencimiento'  => 'required|date|after:fecha_emision',
            'centro_aeromedico'  => 'required|string',
            'restricciones'      => 'nullable|string',
            'archivo_url'        => 'nullable|url',
        ]);

        $data['estudiante_id'] = $id;
        $cert = CertMedico::create($data);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Certificado médico registrado (RAC 67).',
            'data'    => $cert,
        ], 201);
    }

    /* ─── Reservas del estudiante ─── */

    public function reservas($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => $estudiante->reservas()
                ->with(['aeronave:id,matricula,modelo', 'instructor.persona:id,nombres,apellidos'])
                ->orderByDesc('fecha')->orderByDesc('hora_inicio')
                ->paginate(20),
        ]);
    }

    /* ─── Bitácoras del estudiante ─── */

    public function bitacoras($id): JsonResponse
    {
        $estudiante = Estudiante::findOrFail($id);
        $this->authorize('view', $estudiante);

        return response()->json([
            'ok'   => true,
            'data' => $estudiante->bitacoras()
                ->with(['aeronave:id,matricula,modelo', 'instructor.persona:id,nombres,apellidos'])
                ->orderByDesc('fecha')
                ->paginate(20),
        ]);
    }

    /* ─── Helpers ─── */

    private function generarExpediente(): string
    {
        $anio     = now()->year;
        $ultimo   = Estudiante::where('num_expediente', 'like', "EXP-{$anio}-%")
                        ->count() + 1;
        return "EXP-{$anio}-" . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
    }
}
