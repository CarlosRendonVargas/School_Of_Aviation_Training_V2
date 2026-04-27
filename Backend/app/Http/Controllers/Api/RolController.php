<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RolController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Rol::withCount('usuarios')->orderBy('nombre')->get();
        return response()->json(['ok' => true, 'data' => $roles]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $data['nombre'] = strtolower(trim(str_replace([' ', '-'], '_', $data['nombre'])));

        if (Rol::where('nombre', $data['nombre'])->exists()) {
            return response()->json(['ok' => false, 'mensaje' => 'Ya existe un perfil con ese nombre.'], 422);
        }

        $rol = Rol::create($data);
        return response()->json(['ok' => true, 'data' => $rol->loadCount('usuarios')], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $rol = Rol::findOrFail($id);
        $data = $request->validate([
            'descripcion' => 'nullable|string|max:255',
        ]);
        $rol->update($data);
        return response()->json(['ok' => true, 'data' => $rol->loadCount('usuarios')]);
    }

    public function destroy($id): JsonResponse
    {
        $rol = Rol::withCount('usuarios')->findOrFail($id);

        if ($rol->usuarios_count > 0) {
            return response()->json([
                'ok'      => false,
                'mensaje' => "No se puede eliminar: {$rol->usuarios_count} usuario(s) tienen este perfil asignado.",
            ], 422);
        }

        $rol->permisos()->delete();
        $rol->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Perfil eliminado correctamente.']);
    }
}
