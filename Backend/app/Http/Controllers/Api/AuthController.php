<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\AuditLog;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $usuario = Usuario::with(['rol', 'persona'])
            ->where('email', $request->email)
            ->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password_hash)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Credenciales incorrectas.',
            ], 401);
        }

        if (! $usuario->activo) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Usuario inactivo. Contacte al administrador.',
            ], 403);
        }

        // Revocar tokens anteriores y crear uno nuevo
        $usuario->tokens()->delete();
        $token = $usuario->createToken('api-token')->plainTextToken;

        $usuario->update(['ultimo_acceso' => now()]);

        return response()->json([
            'ok'     => true,
            'token'  => $token,
            'usuario' => [
                'id'             => $usuario->id,
                'email'          => $usuario->email,
                'nombre_completo'=> $usuario->nombre_completo,
                'rol'            => $usuario->rol?->nombre,
                'foto_url'       => $usuario->persona?->foto_url,
            ],
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Sesión cerrada.']);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user()->load(['rol', 'persona']);

        return response()->json([
            'ok'     => true,
            'data'   => [
                'id'              => $usuario->id,
                'email'           => $usuario->email,
                'nombre_completo' => $usuario->nombre_completo,
                'rol'             => $usuario->rol?->nombre,
                'foto_url'        => $usuario->persona?->foto_url,
                'ultimo_acceso'   => $usuario->ultimo_acceso?->toDateTimeString(),
                'permisos'        => $usuario->rol?->permisos()
                                        ->where('activo', true)
                                        ->get(['modulo', 'accion']),
            ],
        ]);
    }

    /**
     * PUT /api/v1/auth/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nuevo'  => 'required|string|min:8|confirmed',
        ]);

        $usuario = $request->user();

        if (! Hash::check($request->password_actual, $usuario->password_hash)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'La contraseña actual no es correcta.',
            ], 422);
        }

        $usuario->update([
            'password_hash' => Hash::make($request->password_nuevo),
        ]);

        // Revocar todos los tokens (forzar re-login)
        $usuario->tokens()->delete();

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Contraseña actualizada. Por favor inicie sesión nuevamente.',
        ]);
    }

    /**
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $usuario = Usuario::where('email', $request->email)->first();

        // Siempre responder igual para no revelar si el email existe
        if ($usuario) {
            $token = Str::random(64);
            $usuario->update([
                'token_reset'        => Hash::make($token),
                'token_reset_expira' => Carbon::now()->addHour(),
            ]);

            $nombre = $usuario->persona
                ? trim($usuario->persona->nombres . ' ' . $usuario->persona->apellidos)
                : $usuario->email;

            try {
                Mail::to($usuario->email)
                    ->send(new ResetPasswordMail($token, $usuario->email, $nombre));
            } catch (\Throwable $e) {
                Log::error('Error enviando email reset-password: ' . $e->getMessage());
            }
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Si el correo existe, recibirá instrucciones para restablecer su contraseña.',
        ]);
    }

    /**
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'        => 'required|email',
            'token'        => 'required|string',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $usuario = Usuario::where('email', $request->email)
            ->whereNotNull('token_reset')
            ->where('token_reset_expira', '>', now())
            ->first();

        if (! $usuario || ! Hash::check($request->token, $usuario->token_reset)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Token inválido o expirado.',
            ], 422);
        }

        $usuario->update([
            'password_hash'       => Hash::make($request->password),
            'token_reset'         => null,
            'token_reset_expira'  => null,
        ]);

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Contraseña restablecida exitosamente.',
        ]);
    }
}
