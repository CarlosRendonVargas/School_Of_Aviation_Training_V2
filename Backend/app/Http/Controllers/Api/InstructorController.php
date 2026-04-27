<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = \App\Models\Instructor::with('persona');

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->has('buscar')) {
            $busqueda = $request->buscar;
            $query->whereHas('persona', function ($q) use ($busqueda) {
                $q->where('nombres', 'like', "%$busqueda%")
                  ->orWhere('apellidos', 'like', "%$busqueda%");
            })->orWhere('num_licencia', 'like', "%$busqueda%");
        }

        $instructores = $query->get();

        return response()->json([
            'ok'   => true,
            'data' => $instructores
        ]);
    }

    public function show($id): JsonResponse
    {
        $instructor = \App\Models\Instructor::with('persona')->findOrFail($id);

        return response()->json([
            'ok'   => true,
            'data' => $instructor
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombres'          => 'required|string|max:100',
            'apellidos'        => 'required|string|max:100',
            'tipo_documento'   => 'required|string|max:5',
            'num_documento'    => 'required|string|max:20',
            'fecha_nacimiento' => 'nullable|date',
            'num_licencia'     => 'required|string|max:30',
            'tipo_licencia'    => 'nullable|string|max:30',
            'venc_licencia'    => 'required|date',
            'horas_totales_pic'=> 'nullable|numeric',
        ]);

        // Block if num_documento already belongs to another person in the system
        $personaExistente = \App\Models\Persona::with('usuario.rol')->where('num_documento', $data['num_documento'])->first();
        if ($personaExistente) {
            $nombre = trim($personaExistente->nombres . ' ' . $personaExistente->apellidos);
            $rol    = $personaExistente->usuario?->rol?->nombre ?? 'usuario';

            if (\App\Models\Instructor::where('persona_id', $personaExistente->id)->exists()) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => "El documento ya pertenece al instructor {$nombre}. Si desea actualizarlo, use el botón de editar en la lista.",
                ], 422);
            }
            return response()->json([
                'ok'      => false,
                'mensaje' => "El documento ya está registrado para {$nombre} (rol: {$rol}). Si esta persona también es instructor, cambie su rol en Gestión de Usuarios y luego intente de nuevo.",
            ], 422);
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // Generate unique email, append random suffix if needed to avoid duplicates
            $baseEmail = strtolower(
                preg_replace('/\s+/', '.', trim($data['nombres'])) . '.' .
                preg_replace('/\s+/', '.', trim($data['apellidos'])) . '@aviation.com'
            );
            $email = $baseEmail;
            $suffix = 1;
            while (Usuario::where('email', $email)->exists()) {
                $email = str_replace('@aviation.com', $suffix . '@aviation.com', $baseEmail);
                $suffix++;
            }

            $usuario = Usuario::create([
                'email'         => $email,
                'password_hash' => \Illuminate\Support\Facades\Hash::make($data['num_documento']),
                'rol_id'        => \App\Models\Rol::where('nombre', 'instructor')->first()?->id ?? 2,
                'activo'        => true,
            ]);

            $persona = \App\Models\Persona::create([
                'usuario_id'       => $usuario->id,
                'nombres'          => $data['nombres'],
                'apellidos'        => $data['apellidos'],
                'tipo_documento'   => $data['tipo_documento'],
                'num_documento'    => $data['num_documento'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
            ]);

            $instructor = \App\Models\Instructor::create([
                'persona_id'        => $persona->id,
                'num_licencia'      => $data['num_licencia'],
                'tipo_licencia'     => $data['tipo_licencia'],
                'venc_licencia'     => $data['venc_licencia'],
                'horas_totales_pic' => $data['horas_totales_pic'] ?? 0,
                'activo'            => true,
            ]);

            return response()->json(['ok' => true, 'data' => $instructor], 201);
        });
    }

    public function certificaciones($id): JsonResponse
    {
        \App\Models\Instructor::findOrFail($id);
        $certs = \App\Models\CertInstructor::where('instructor_id', $id)
            ->orderByDesc('fecha_vencimiento')
            ->get();
        return response()->json(['ok' => true, 'data' => $certs]);
    }

    public function storeCertificacion(Request $request, $id): JsonResponse
    {
        \App\Models\Instructor::findOrFail($id);
        $data = $request->validate([
            'tipo'               => 'required|string|max:60',
            'descripcion'        => 'nullable|string',
            'numero'             => 'nullable|string|max:50',
            'fecha_emision'      => 'nullable|date',
            'fecha_vencimiento'  => 'nullable|date',
            'archivo_url'        => 'nullable|string',
            'activo'             => 'boolean',
        ]);
        $data['instructor_id'] = $id;
        $cert = \App\Models\CertInstructor::create($data);
        return response()->json(['ok' => true, 'data' => $cert], 201);
    }

    public function planesClase($id): JsonResponse
    {
        \App\Models\Instructor::findOrFail($id);
        $planes = \App\Models\PlanClase::where('instructor_id', $id)
            ->with('materia')
            ->orderByDesc('fecha')
            ->get();
        return response()->json(['ok' => true, 'data' => $planes]);
    }

    public function storePlanClase(Request $request, $id): JsonResponse
    {
        \App\Models\Instructor::findOrFail($id);
        $data = $request->validate([
            'materia_id'   => 'nullable|exists:materias,id',
            'fecha'        => 'required|date',
            'duracion_min' => 'nullable|integer|min:1',
            'objetivos'    => 'nullable|string',
            'contenido'    => 'nullable|string',
            'recursos'     => 'nullable|string',
            'estado'       => 'nullable|string|max:30',
            'observaciones'=> 'nullable|string',
        ]);
        $data['instructor_id'] = $id;
        $plan = \App\Models\PlanClase::create($data);
        return response()->json(['ok' => true, 'data' => $plan->load('materia')], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $instructor = \App\Models\Instructor::with('persona')->findOrFail($id);

        $dataInstructor = $request->validate([
            'num_licencia'      => 'sometimes|string|max:50',
            'tipo_licencia'     => 'sometimes|string|max:30',
            'venc_licencia'     => 'sometimes|nullable|date',
            'horas_totales_pic' => 'sometimes|numeric|min:0',
            'horas_instruccion' => 'sometimes|numeric|min:0',
            'activo'            => 'sometimes|boolean',
        ]);

        $dataPersona = $request->validate([
            'nombres'          => 'sometimes|string|max:100',
            'apellidos'        => 'sometimes|string|max:100',
            'tipo_documento'   => 'sometimes|in:CC,CE,PA,PEP',
            'num_documento'    => 'sometimes|string|max:20',
            'telefono'         => 'sometimes|nullable|string|max:20',
            'ciudad'           => 'sometimes|nullable|string|max:80',
            'fecha_nacimiento' => 'sometimes|nullable|date',
            'nacionalidad'     => 'sometimes|nullable|string|max:60',
        ]);

        $instructor->update($dataInstructor);

        if ($instructor->persona && count($dataPersona)) {
            $instructor->persona->update($dataPersona);
        }

        return response()->json([
            'ok'   => true,
            'data' => $instructor->fresh(['persona']),
        ]);
    }
}
