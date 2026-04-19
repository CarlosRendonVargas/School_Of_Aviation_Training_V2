<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/* ═══════════════════════════════════════════════════════
   RolesSeeder
═══════════════════════════════════════════════════════ */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'estudiante',
                'descripcion' => 'Estudiante activo en un programa de formación aeronáutica',
            ],
            [
                'nombre'      => 'instructor',
                'descripcion' => 'Instructor de vuelo certificado RAC 65 / RAC 141.35',
            ],
            [
                'nombre'      => 'admin',
                'descripcion' => 'Personal administrativo y financiero de la escuela',
            ],
            [
                'nombre'      => 'dir_ops',
                'descripcion' => 'Director de Operaciones — máxima autoridad técnica RAC 141.31',
            ],
            [
                'nombre'      => 'mantenimiento',
                'descripcion' => 'Técnico certificado de mantenimiento aeronáutico RAC 65',
            ],
            [
                'nombre'      => 'auditor_uaeac',
                'descripcion' => 'Inspector o auditor de la UAEAC — acceso de solo lectura',
            ],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(['nombre' => $rol['nombre']], $rol);
        }

        // Permisos base por rol y módulo
        $matriz = [
            'estudiante'    => ['academico' => ['ver'], 'vuelo' => ['ver'], 'admin' => ['ver']],
            'instructor'    => ['academico' => ['ver', 'crear', 'editar'], 'vuelo' => ['ver', 'crear', 'editar', 'firmar'], 'sms' => ['ver', 'crear']],
            'admin'         => ['admin' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'], 'academico' => ['ver']],
            'dir_ops'       => ['academico' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'], 'vuelo' => ['ver', 'crear', 'editar', 'eliminar', 'exportar', 'firmar'], 'instructores' => ['ver', 'crear', 'editar', 'eliminar'], 'operaciones' => ['ver', 'crear', 'editar', 'eliminar'], 'sms' => ['ver', 'crear', 'editar', 'eliminar', 'exportar'], 'admin' => ['ver', 'crear', 'editar', 'exportar'], 'cumplimiento' => ['ver', 'crear', 'editar', 'exportar']],
            'mantenimiento' => ['operaciones' => ['ver', 'crear', 'editar'], 'vuelo' => ['ver']],
            'auditor_uaeac' => ['academico' => ['ver', 'exportar'], 'vuelo' => ['ver', 'exportar'], 'instructores' => ['ver', 'exportar'], 'operaciones' => ['ver', 'exportar'], 'sms' => ['ver', 'exportar'], 'cumplimiento' => ['ver', 'exportar']],
        ];

        foreach ($matriz as $rolNombre => $modulos) {
            $rolId = DB::table('roles')->where('nombre', $rolNombre)->value('id');
            foreach ($modulos as $modulo => $acciones) {
                foreach ($acciones as $accion) {
                    DB::table('permisos')->updateOrInsert(
                        ['rol_id' => $rolId, 'modulo' => $modulo, 'accion' => $accion],
                        ['activo' => true, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
}
