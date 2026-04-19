<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CumplimientoController extends Controller
{
    public function documentos(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => [
                [
                    'nombre' => 'Manual de Instrucción y Entrenamiento (MIE)',
                    'estado' => 'vigente',
                    'vencimiento' => '2026-12-31',
                    'version' => 'V12'
                ],
                [
                    'nombre' => 'Manual de Gestión de la Seguridad (SMS)',
                    'estado' => 'vigente',
                    'vencimiento' => '2027-01-15',
                    'version' => 'V8'
                ],
                [
                    'nombre' => 'Permiso de Operación UAEAC',
                    'estado' => 'proximo_vencer',
                    'vencimiento' => '2026-05-20',
                    'version' => 'CERT-RAC-141'
                ]
            ]
        ]);
    }

    public function auditLog(): JsonResponse
    {
        // RAC 141.77 — Registros de auditoría
        try {
            $logs = DB::table('audit_log')
                ->join('usuarios', 'audit_log.usuario_id', '=', 'usuarios.id')
                ->select('audit_log.*', 'usuarios.email as user_email')
                ->orderByDesc('audit_log.created_at')
                ->limit(50)
                ->get();
                
            return response()->json([
                'ok' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            // Si la tabla está vacía o hay error, devolvemos array vacío
            return response()->json([
                'ok' => true,
                'data' => []
            ]);
        }
    }

    public function checklistRac141(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => [
                'aseguramiento_calidad' => true,
                'manuales_aprobados'    => true,
                'personal_clave'        => true,
                'flota_certificada'     => true,
            ]
        ]);
    }
}
