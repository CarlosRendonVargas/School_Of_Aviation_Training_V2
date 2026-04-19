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
}
