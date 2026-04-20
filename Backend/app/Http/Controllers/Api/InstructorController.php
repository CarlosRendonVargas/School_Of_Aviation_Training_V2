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
            'num_documento'    => 'required|string|max:20|unique:personas,num_documento',
            'fecha_nacimiento' => 'required|date',
            'num_licencia'     => 'required|string|max:30',
            'tipo_licencia'    => 'required|in:CPL,ATPL,CFI',
            'venc_licencia'    => 'required|date',
            'horas_totales_pic'=> 'nullable|numeric',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            // 1. Usuario
            $usuario = Usuario::create([
                'email'    => strtolower($data['nombres'].'.'.$data['apellidos'].'@aviation.com'),
                'password' => \Illuminate\Support\Facades\Hash::make($data['num_documento']),
                'rol_id'   => \App\Models\Rol::where('nombre', 'instructor')->first()?->id ?? 2,
                'activo'   => true
            ]);

            // 2. Persona
            $persona = \App\Models\Persona::create([
                'usuario_id'       => $usuario->id,
                'nombres'          => $data['nombres'],
                'apellidos'        => $data['apellidos'],
                'tipo_documento'   => $data['tipo_documento'],
                'num_documento'    => $data['num_documento'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
            ]);

            // 3. Instructor
            $instructor = \App\Models\Instructor::create([
                'persona_id'        => $persona->id,
                'num_licencia'      => $data['num_licencia'],
                'tipo_licencia'     => $data['tipo_licencia'],
                'venc_licencia'     => $data['venc_licencia'],
                'horas_totales_pic' => $data['horas_totales_pic'] ?? 0,
                'activo'            => true,
            ]);

            return response()->json(['ok'   => true, 'data' => $instructor], 201);
        });
    }

    public function update(Request $request, $id): JsonResponse
    {
        $instructor = \App\Models\Instructor::findOrFail($id);
        
        $data = $request->validate([
            'num_licencia'      => 'sometimes|string',
            'tipo_licencia'     => 'sometimes|in:CPL,ATPL,CFI',
            'venc_licencia'     => 'sometimes|date',
            'horas_totales_pic' => 'sometimes|numeric',
            'activo'            => 'sometimes|boolean',
        ]);

        $instructor->update($data);

        return response()->json(['ok' => true, 'data' => $instructor]);
    }
}
