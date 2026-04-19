<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    public function index(): JsonResponse
    {
        $usuarios = Usuario::with(['persona', 'rol'])->orderBy('id')->get();
        return response()->json(['ok' => true, 'data' => $usuarios]);
    }

    public function roles(): JsonResponse
    {
        return response()->json(['ok' => true, 'data' => Rol::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'          => 'required|email|unique:usuarios',
            'password'       => 'required|string|min:6',
            'rol_id'         => 'required|exists:roles,id',
            'nombres'        => 'required|string',
            'apellidos'      => 'required|string',
            'num_documento'  => 'required|string',
            'telefono'       => 'nullable|string',
        ]);

        $usuario = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $u = Usuario::create([
                'email'         => $data['email'],
                'password_hash' => \Illuminate\Support\Facades\Hash::make($data['password']),
                'rol_id'        => $data['rol_id'],
                'activo'        => true,
                'created_at'    => now(),
            ]);

            \App\Models\Persona::create([
                'usuario_id'       => $u->id,
                'nombres'          => $data['nombres'],
                'apellidos'        => $data['apellidos'],
                'num_documento'    => $data['num_documento'],
                'tipo_documento'   => 'CC',
                'fecha_nacimiento' => date('Y-m-d', strtotime('-18 years')), // Mayor de edad RAC
                'ciudad'           => 'No Definida',
                'telefono'         => $data['telefono'] ?? null,
            ]);

            return $u;
        });

        return response()->json(['ok' => true, 'data' => $usuario->load(['persona', 'rol'])]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        
        $request->validate([
            'rol_id'        => 'exists:roles,id',
            'activo'        => 'boolean',
            'nombres'       => 'nullable|string',
            'apellidos'     => 'nullable|string',
            'num_documento' => 'nullable|string',
            'telefono'      => 'nullable|string',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($usuario, $request) {
            $usuario->update($request->only('rol_id', 'activo'));
            if ($usuario->persona) {
                $usuario->persona->update($request->only('nombres', 'apellidos', 'num_documento', 'telefono'));
            }
        });

        return response()->json(['ok' => true, 'data' => $usuario->load(['persona', 'rol'])]);
    }

    public function resetPassword(Request $request, $id): JsonResponse
    {
        $usuario = Usuario::findOrFail($id);
        $request->validate(['password' => 'required|string|min:6']);
        $usuario->update(['password_hash' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        return response()->json(['ok' => true]);
    }

    public function auditoria(): JsonResponse
    {
        $logs = \App\Models\AuditLog::with('usuario.persona')->orderByDesc('created_at')->limit(100)->get();
        return response()->json(['ok' => true, 'data' => $logs]);
    }
}
