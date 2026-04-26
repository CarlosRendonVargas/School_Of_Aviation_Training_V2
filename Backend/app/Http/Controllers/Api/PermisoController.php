<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permiso;
use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    // Módulos del sistema con roles habilitados por defecto
    private const MODULOS = [
        'dashboard'                    => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'vencimientos'                 => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'mensajes'                     => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'normatividad'                 => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'cronograma'                   => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'reservas'                     => ['admin', 'dir_ops', 'instructor'],
        'calendario'                   => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'vuelo'                        => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'academico'                    => ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'],
        'aula-virtual'                 => ['estudiante'],
        'mi-progreso'                  => ['estudiante'],
        'estudiantes'                  => ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'],
        'certificados'                 => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'endorsements'                 => ['admin', 'dir_ops', 'instructor', 'auditor_uaeac'],
        'evaluaciones-instructor'      => ['admin', 'dir_ops', 'auditor_uaeac'],
        'sms'                          => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'sms-erg'                      => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'sms-capacitaciones'           => ['admin', 'dir_ops', 'instructor', 'estudiante', 'mantenimiento', 'auditor_uaeac'],
        'aeronaves'                    => ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'],
        'mantenimiento'                => ['admin', 'dir_ops', 'mantenimiento', 'auditor_uaeac'],
        'cumplimiento'                 => ['admin', 'dir_ops', 'auditor_uaeac'],
        'cumplimiento-enmiendas'       => ['admin', 'dir_ops', 'auditor_uaeac'],
        'cumplimiento-correspondencia' => ['admin', 'dir_ops', 'auditor_uaeac'],
        'cumplimiento-reportes'        => ['admin', 'dir_ops', 'auditor_uaeac'],
        'instructores'                 => ['admin', 'dir_ops', 'auditor_uaeac'],
        'financiero'                   => ['admin', 'dir_ops'],
        'matriculas'                   => ['admin', 'dir_ops'],
        'facturacion'                  => ['admin', 'dir_ops'],
        'prospectos'                   => ['admin', 'dir_ops'],
        'nomina'                       => ['admin', 'dir_ops'],
        'gastos'                       => ['admin', 'dir_ops'],
        'seguridad'                    => ['admin'],
    ];

    // Celdas bloqueadas que nunca se pueden deshabilitar
    private const BLOQUEADOS = [
        'seguridad' => ['admin'],
    ];

    /**
     * GET /api/v1/permisos/matrix
     * Retorna la matriz completa de módulo × rol con estado activo.
     * Auto-inicializa permisos faltantes con valores predeterminados.
     */
    public function matrix(): JsonResponse
    {
        $roles = Rol::all()->keyBy('nombre');

        // Inicializar permisos que no existen aún
        foreach (self::MODULOS as $modulo => $defaultRoles) {
            foreach ($roles as $rolNombre => $rol) {
                Permiso::firstOrCreate(
                    ['rol_id' => $rol->id, 'modulo' => $modulo, 'accion' => 'acceso'],
                    ['activo' => in_array($rolNombre, $defaultRoles)]
                );
            }
        }

        // Construir matriz [modulo][rol] = activo
        $permisos = Permiso::where('accion', 'acceso')->with('rol')->get();
        $matrix   = [];
        foreach ($permisos as $p) {
            if ($p->rol && isset(self::MODULOS[$p->modulo])) {
                $matrix[$p->modulo][$p->rol->nombre] = $p->activo;
            }
        }

        return response()->json([
            'ok'       => true,
            'roles'    => $roles->keys()->values(),
            'modulos'  => array_keys(self::MODULOS),
            'matrix'   => $matrix,
            'bloqueados' => self::BLOQUEADOS,
        ]);
    }

    /**
     * PUT /api/v1/permisos/matrix
     * Actualiza en bloque los permisos de acceso a módulos por rol.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate(['matrix' => 'required|array']);

        $roles  = Rol::all()->keyBy('nombre');
        $matrix = $request->matrix;

        foreach ($matrix as $modulo => $rolesActivos) {
            if (!array_key_exists($modulo, self::MODULOS)) continue;

            foreach ($roles as $rolNombre => $rol) {
                // Celdas bloqueadas siempre quedan activas
                $esBloqueado = isset(self::BLOQUEADOS[$modulo])
                    && in_array($rolNombre, self::BLOQUEADOS[$modulo]);

                $activo = $esBloqueado
                    ? true
                    : (bool) ($rolesActivos[$rolNombre] ?? false);

                Permiso::updateOrCreate(
                    ['rol_id' => $rol->id, 'modulo' => $modulo, 'accion' => 'acceso'],
                    ['activo' => $activo]
                );
            }
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Matriz de permisos de módulos actualizada correctamente.',
        ]);
    }
}
